<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Pesanan;
use App\Models\MetodePembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TransaksiController extends Controller
{
    /**
     * Create transaction (Step 2 in manual payment flow)
     */
    public function createTransaction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_uuid' => 'required|exists:pesanan,uuid',
            'id_metode_pembayaran' => 'required|exists:metode_pembayaran,id_metode_pembayaran'
        ], [
            'order_uuid.required' => 'UUID pesanan wajib diisi',
            'order_uuid.exists' => 'Pesanan tidak ditemukan',
            'id_metode_pembayaran.required' => 'Metode pembayaran wajib dipilih',
            'id_metode_pembayaran.exists' => 'Metode pembayaran tidak valid'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // Get pesanan by UUID and check ownership
            $pesanan = Pesanan::where('uuid', $request->order_uuid)
            ->where('id_user', auth()->user()->id_user)
            ->first();
            
        if (!$pesanan) {
            return response()->json([
                'status' => 'error',
                    'message' => 'Pesanan tidak ditemukan atau tidak memiliki akses'
            ], 404);
        }

            // Check if pesanan status allows payment
            if ($pesanan->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak dapat dibayar pada status ini'
                ], 422);
            }

            // Check if there's already active transaction
            $existingTransaction = Transaksi::where('id_pesanan', $pesanan->id_pesanan)
                ->whereIn('status', ['belum_bayar', 'menunggu_konfirmasi'])
                ->first();

            if ($existingTransaction) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sudah ada transaksi aktif untuk pesanan ini',
                    'data' => [
                        'existing_transaction' => $existingTransaction
                    ]
                ], 422);
            }

        // Generate unique order ID
            $orderId = 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(8));
        
        // Set expiration time (24 hours from now)
        $expiredAt = Carbon::now()->addHours(24);
        
            $transaksi = Transaksi::create([
                'order_id' => $orderId,
                'jumlah' => $pesanan->total_harga,
                'status' => 'belum_bayar',
                'bukti_pembayaran' => null,
                'waktu_pembayaran' => null,
                'expired_at' => $expiredAt,
                'id_metode_pembayaran' => $request->id_metode_pembayaran,
                'id_pesanan' => $pesanan->id_pesanan
            ]);
            
            // Update pesanan payment status to menunggu_konfirmasi for UI flow
            $pesanan->update([
                'status' => 'menunggu_konfirmasi',
                'status_pembayaran' => 'belum_bayar'
            ]);
            
            // Get payment method details
            $metodePembayaran = MetodePembayaran::find($request->id_metode_pembayaran);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil dibuat. Silakan lakukan pembayaran dan upload bukti transfer.',
                'data' => [
                    'transaction' => $transaksi,
                    'payment_method' => $metodePembayaran,
                    'expired_at' => $expiredAt->format('Y-m-d H:i:s'),
                    'payment_instructions' => [
                        'step1' => 'Transfer ke rekening: ' . $metodePembayaran->no_rekening,
                        'step2' => 'Atas nama: ' . $metodePembayaran->atas_nama,
                        'step3' => 'Nominal: Rp ' . number_format($pesanan->total_harga, 0, ',', '.'),
                        'step4' => 'Upload bukti transfer untuk konfirmasi'
                    ]
                ]
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error creating transaction: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat transaksi'
            ], 500);
        }
    }
    
    /**
     * Upload payment proof (Step 3 in manual payment flow)
     */
    public function uploadPaymentProof(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:transaksi,order_id',
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'catatan' => 'nullable|string|max:200'
        ], [
            'order_id.required' => 'Order ID wajib diisi',
            'order_id.exists' => 'Transaksi tidak ditemukan',
            'bukti_pembayaran.required' => 'Bukti pembayaran wajib diupload',
            'bukti_pembayaran.image' => 'Bukti pembayaran harus berupa gambar',
            'bukti_pembayaran.mimes' => 'Format gambar harus jpeg, png, atau jpg',
            'bukti_pembayaran.max' => 'Ukuran file maksimal 2MB',
            'catatan.max' => 'Catatan maksimal 200 karakter'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $transaksi = Transaksi::where('order_id', $request->order_id)->first();
            
            // Check if transaction belongs to user's order
            $pesanan = Pesanan::where('id_pesanan', $transaksi->id_pesanan)
                ->where('id_user', auth()->user()->id_user)
                ->first();
                
            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak ditemukan atau tidak memiliki akses'
                ], 404);
            }
            
            // Check if transaction is still valid (not expired)
            if (Carbon::now()->isAfter($transaksi->expired_at)) {
                $transaksi->update(['status' => 'expired']);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi sudah kadaluarsa. Silakan buat transaksi baru.'
                ], 400);
            }
            
            // Check if transaction is in valid state
            if ($transaksi->status !== 'belum_bayar') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bukti pembayaran sudah diupload atau pembayaran sudah dikonfirmasi'
                ], 400);
            }
            
            // Store the uploaded file
            $file = $request->file('bukti_pembayaran');
            $fileName = 'bukti_' . $transaksi->order_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('bukti_pembayaran', $fileName, 'public');
            
            // Update transaction
            $transaksi->update([
                'bukti_pembayaran' => $filePath,
                'status' => 'menunggu_konfirmasi',
                'waktu_pembayaran' => Carbon::now(),
                'admin_notes' => $request->catatan
            ]);
            
            // Update pesanan status
            $pesanan->update([
                'status_pembayaran' => 'menunggu_konfirmasi'
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Bukti pembayaran berhasil diupload. Menunggu konfirmasi admin.',
                'data' => [
                    'transaction' => $transaksi->fresh(),
                    'estimated_confirmation' => '1-24 jam',
                    'next_step' => 'Tunggu konfirmasi admin via notifikasi atau chat'
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error uploading payment proof: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupload bukti pembayaran'
            ], 500);
        }
    }
    
    /**
     * Get transaction details
     */
    public function getTransactionDetails(Request $request, $orderId): JsonResponse
    {
        try {
            $transaksi = Transaksi::with(['toMetodePembayaran', 'toPesanan.toJasa'])
                ->where('order_id', $orderId)
                ->first();
                
            if (!$transaksi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }
            
            // Check if transaction belongs to user's order
            $pesanan = Pesanan::where('id_pesanan', $transaksi->id_pesanan)
                ->where('id_user', auth()->user()->id_user)
                ->first();
                
            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak ditemukan atau tidak memiliki akses'
                ], 404);
            }

            // Add time remaining if not expired
            $timeRemaining = null;
            if ($transaksi->status === 'belum_bayar' && Carbon::now()->isBefore($transaksi->expired_at)) {
                $timeRemaining = Carbon::now()->diffInMinutes($transaksi->expired_at);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Detail transaksi berhasil diambil',
                'data' => [
                    'transaction' => $transaksi,
                    'time_remaining_minutes' => $timeRemaining,
                    'can_upload_proof' => $transaksi->status === 'belum_bayar' && $timeRemaining > 0,
                    'payment_status_label' => $this->getPaymentStatusLabel($transaksi->status)
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting transaction details: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail transaksi'
            ], 500);
        }
    }
    
    /**
     * Get user transactions with filtering
     */
    public function getUserTransactions(Request $request): JsonResponse
    {
        try {
            $status = $request->query('status');
            $limit = $request->query('limit', 10);
            
            $query = Transaksi::with(['toMetodePembayaran', 'toPesanan.toJasa'])
                ->whereHas('toPesanan', function ($query) {
                    $query->where('id_user', auth()->user()->id_user);
                });
            
            if ($status && in_array($status, ['belum_bayar', 'menunggu_konfirmasi', 'lunas', 'dibatalkan', 'expired'])) {
                $query->where('status', $status);
            }
            
            $transactions = $query->orderBy('created_at', 'desc')
                ->paginate($limit);
                
            return response()->json([
                'status' => 'success',
                'message' => 'Riwayat transaksi berhasil diambil',
                'data' => $transactions
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting user transactions: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil riwayat transaksi'
            ], 500);
        }
    }
    
    /**
     * Cancel transaction (only if belum_bayar)
     */
    public function cancelTransaction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:transaksi,order_id',
            'reason' => 'nullable|string|max:200'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $transaksi = Transaksi::where('order_id', $request->order_id)->first();
            
            // Check ownership
            $pesanan = Pesanan::where('id_pesanan', $transaksi->id_pesanan)
                ->where('id_user', auth()->user()->id_user)
                ->first();
                
            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak ditemukan atau tidak memiliki akses'
                ], 404);
            }
            
            // Can only cancel if belum_bayar
            if ($transaksi->status !== 'belum_bayar') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak dapat dibatalkan pada status ini'
                ], 422);
            }
            
            $transaksi->update([
                'status' => 'dibatalkan',
                'admin_notes' => $request->reason ?? 'Dibatalkan oleh user'
            ]);
            
            // Reset pesanan status to pending for new transaction
            $pesanan->update([
                'status' => 'pending',
                'status_pembayaran' => 'belum_bayar'
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil dibatalkan'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error canceling transaction: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membatalkan transaksi'
            ], 500);
        }
    }

    /**
     * Get payment status label for display
     */
    private function getPaymentStatusLabel($status)
    {
        $labels = [
            'belum_bayar' => 'Belum Bayar',
            'menunggu_konfirmasi' => 'Menunggu Konfirmasi Admin',
            'lunas' => 'Lunas',
            'dibatalkan' => 'Dibatalkan', 
            'expired' => 'Kadaluarsa'
        ];
        
        return $labels[$status] ?? $status;
    }

    /**
     * Admin: Confirm payment (Step 4 in manual payment flow)
     * This should be called from admin panel
     */
    public function confirmPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:transaksi,order_id',
            'admin_notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $transaksi = Transaksi::where('order_id', $request->order_id)->first();
            
            if ($transaksi->status !== 'menunggu_konfirmasi') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak dalam status menunggu konfirmasi'
                ], 422);
            }

            // Update transaction to lunas
            $transaksi->update([
                'status' => 'lunas',
                'confirmed_at' => Carbon::now(),
                'admin_notes' => $request->admin_notes ?? 'Pembayaran dikonfirmasi oleh admin'
            ]);

            // Update pesanan status
            $pesanan = Pesanan::find($transaksi->id_pesanan);
            $pesanan->update([
                'status_pembayaran' => 'lunas',
                'confirmed_at' => Carbon::now()
            ]);

            // TODO: Send FCM notification to user
            // TODO: Create chat notification

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran berhasil dikonfirmasi',
                'data' => [
                    'transaction' => $transaksi->fresh(),
                    'order' => $pesanan->fresh(),
                    'next_step' => 'Assign editor ke pesanan'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error confirming payment: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengkonfirmasi pembayaran'
            ], 500);
        }
    }

    /**
     * Admin: Reject payment (Step 4 alternative in manual payment flow)
     */
    public function rejectPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:transaksi,order_id',
            'reject_reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $transaksi = Transaksi::where('order_id', $request->order_id)->first();
            
            if ($transaksi->status !== 'menunggu_konfirmasi') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak dalam status menunggu konfirmasi'
                ], 422);
            }

            // Update transaction back to belum_bayar
            $transaksi->update([
                'status' => 'belum_bayar',
                'bukti_pembayaran' => null,
                'waktu_pembayaran' => null,
                'reject_reason' => $request->reject_reason,
                'expired_at' => Carbon::now()->addHours(24) // Extend expiry
            ]);

            // Update pesanan status
            $pesanan = Pesanan::find($transaksi->id_pesanan);
            $pesanan->update([
                'status_pembayaran' => 'belum_bayar',
                'alasan_reject' => $request->reject_reason
            ]);

            // TODO: Send FCM notification to user
            // TODO: Create chat notification with reject reason

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran berhasil ditolak',
                'data' => [
                    'transaction' => $transaksi->fresh(),
                    'reject_reason' => $request->reject_reason,
                    'next_step' => 'User perlu upload ulang bukti pembayaran'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error rejecting payment: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menolak pembayaran'
            ], 500);
        }
    }

    /**
     * Admin: Get pending payments for review
     */
    public function getPendingPayments(Request $request): JsonResponse
    {
        try {
            $limit = $request->query('limit', 20);
            
            $pendingPayments = Transaksi::with(['toPesanan.toUser.toAuth', 'toMetodePembayaran'])
                ->where('status', 'menunggu_konfirmasi')
                ->whereNotNull('bukti_pembayaran')
                ->orderBy('waktu_pembayaran', 'asc')
                ->paginate($limit);

            return response()->json([
                'status' => 'success',
                'message' => 'Daftar pembayaran pending berhasil diambil',
                'data' => $pendingPayments
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting pending payments: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil daftar pembayaran pending'
            ], 500);
        }
    }
}