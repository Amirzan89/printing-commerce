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
use App\Models\User;

class TransaksiController extends Controller
{
    public function getAll(Request $request){
        $transaksi = Transaksi::select('order_id')->join('pesanan', 'pesanan.id_pesanan', '=', 'transaksi.id_pesanan')
            ->where('id_user', User::select('id_user')->where('id_auth', $request->user()->id_auth)->first()->id_user)
            ->orderBy('transaksi.created_at', 'desc')
            ->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi berhasil diambil',
            'data' => $transaksi
        ]);
    }

    public function getDetail(Request $request, $order_id){
        try{
            $transaksi = Transaksi::join('pesanan', 'pesanan.id_pesanan', '=', 'transaksi.id_pesanan')
                ->where('order_id', $order_id)->where('id_user', User::select('id_user')->where('id_auth', $request->user()->id_auth)->first()->id_user)
                ->first();
            if (!$transaksi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }
            // Get payment status label
            $paymentStatusLabel = $this->getPaymentStatusLabel($transaksi->status);
            return response()->json([
                'status' => 'success',
                'message' => 'Detail transaksi berhasil diambil',
                'data' => [
                    'transaction' => $transaksi,
                    'payment_status_label' => $paymentStatusLabel
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
     * Create transaction (Step 2 in manual payment flow)
     */
    public function createTransaction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->only('id_pesanan', 'id_metode_pembayaran'), [
            'id_pesanan' => 'required|exists:pesanan,uuid',
            'id_metode_pembayaran' => 'required'
        ], [
            'id_pesanan.required' => 'UUID pesanan wajib diisi',
            'id_pesanan.exists' => 'Pesanan tidak ditemukan',
            'id_metode_pembayaran.required' => 'Metode pembayaran wajib dipilih'
        ]);

        if ($validator->fails()){
            $errors = [];
            foreach ($validator->errors()->toArray() as $field => $errorMessages){
                $errors[$field] = $errorMessages[0];
                break;
            }
            return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }

        try {
            // Get pesanan by UUID and check ownership
            $pesanan = Pesanan::where('uuid', $request->input('id_pesanan'))
            ->where('id_user', User::select('id_user')->where('id_auth', $request->user()->id_auth)->first()->id_user)
            ->first();
            
            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak ditemukan'
                ], 404);
            }
            // Get metode pembayaran by UUID
            $metodePembayaran = MetodePembayaran::where('uuid', $request->input('id_metode_pembayaran'))->first();
            if (!$metodePembayaran) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Metode pembayaran tidak ditemukan'
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
                'id_metode_pembayaran' => $metodePembayaran->id_metode_pembayaran,
                'id_pesanan' => $pesanan->id_pesanan
            ]);
            
            // Update pesanan payment status to menunggu_konfirmasi for UI flow
            $pesanan->update([
                'status' => 'pending',
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil dibuat. Silakan lakukan pembayaran dan upload bukti transfer.',
                'data' => [
                    'transaksi' => $transaksi,
                    'payment_method' => $metodePembayaran,
                    'expired_at' => $expiredAt->format('Y-m-d H:i:s'),
                    'payment_instructions' => [
                        'step1' => 'Transfer ke rekening: ' . $metodePembayaran->no_metode_pembayaran,
                        'step2' => 'Atas nama: ' . $metodePembayaran->deskripsi_1,
                        'step2-2' => 'Atas nama: ' . $metodePembayaran->deskripsi_2,
                        'step3' => 'Nominal: Rp ' . number_format($pesanan->total_harga, 0, ',', '.'),
                        'step4' => 'Upload bukti transfer untuk konfirmasi'
                    ]
                ]
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error creating transaction: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat transaksi',
                'data' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Upload payment proof (Step 3 in manual payment flow)
     */
    public function uploadPaymentProof(Request $request)
    {
        $validator = Validator::make($request->only('order_id', 'bukti_pembayaran', 'catatan'), [
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
            $transaksi = Transaksi::join('pesanan', 'pesanan.id_pesanan', '=', 'transaksi.id_pesanan')
                ->where('id_user', User::select('id_user')->where('id_auth', $request->user()->id_auth)->first()->id_user)
                ->where('order_id', $request->input('order_id'))
                ->first();
            
            // Check if transaction belongs to user's order
            $pesanan = Pesanan::where('id_pesanan', $transaksi->id_pesanan)
                ->where('id_user', User::select('id_user')->where('id_auth', $request->user()->id_auth)->first()->id_user)
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
                    'message' => 'Transaksi sudah kadaluarsa. Silakan lakukan pembayaran.'
                ], 400);
            }
            
            // Check if transaction is in valid state
            if ($transaksi->status == 'dibatalkan') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi sudah dibatalkan. Silakan buat transaksi baru.'
                ], 400);
            }
            if ($transaksi->status == 'menunggu_editor') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bukti pembayaran sudah diupload. Silakan tunggu konfirmasi admin.'
                ], 400);
            }
            if ($transaksi->status == 'lunas') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi sudah lunas. Silakan tunggu konfirmasi admin.'
                ], 400);
            }
            
            // Store the uploaded file
            $file = $request->file('bukti_pembayaran');
            $fileName = 'bukti_' . $transaksi->order_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('bukti_pembayaran', $fileName, 'public');
            
            // Update transaction
            $transaksi->update([
                'bukti_pembayaran' => $filePath,
                'status' => 'menunggu_editor',
                'waktu_pembayaran' => Carbon::now(),
                'catatan_transaksi' => $request->input('catatan')
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
     * Cancel transaction (only if belum_bayar)
     */
    public function cancelTransaction(Request $request)
    {
        $validator = Validator::make($request->only('order_id', 'reason'), [
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
                ->where('id_user', User::select('id_user')->where('id_auth', $request->user()->id_auth)->first()->id_user)
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
                'catatan_transaksi' => $request->reason ?? 'Dibatalkan oleh user'
            ]);
            
            // Reset pesanan status to pending for new transaction
            $pesanan->update([
                'status' => 'pending',
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
}