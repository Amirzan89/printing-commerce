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

class TransaksiController extends Controller
{
    public function createTransaction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_pesanan' => 'required|exists:pesanan,id_pesanan',
            'id_metode_pembayaran' => 'required|exists:metode_pembayaran,id_metode_pembayaran',
            'jumlah' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if pesanan belongs to authenticated user
        $pesanan = Pesanan::where('id_pesanan', $request->id_pesanan)
            ->where('id_user', auth()->user()->id_user)
            ->first();
            
        if (!$pesanan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found or not authorized'
            ], 404);
        }

        // Generate unique order ID
        $orderId = 'TRX-' . time() . '-' . Str::random(6);
        
        // Set expiration time (24 hours from now)
        $expiredAt = Carbon::now()->addHours(24);
        
        try {
            $transaksi = Transaksi::create([
                'order_id' => $orderId,
                'jumlah' => $request->jumlah,
                'status' => 'belum_bayar',
                'bukti_pembayaran' => null,
                'waktu_pembayaran' => null,
                'expired_at' => $expiredAt,
                'id_metode_pembayaran' => $request->id_metode_pembayaran,
                'id_pesanan' => $request->id_pesanan
            ]);
            
            // Update pesanan payment status
            $pesanan->update(['status_pembayaran' => 'belum_bayar']);
            
            // Get payment method details
            $metodePembayaran = MetodePembayaran::find($request->id_metode_pembayaran);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Transaction created successfully',
                'data' => [
                    'transaction' => $transaksi,
                    'payment_method' => $metodePembayaran,
                    'expired_at' => $expiredAt->format('Y-m-d H:i:s')
                ]
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Upload payment proof
     */
    public function uploadPaymentProof(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:transaksi,order_id',
            'bukti_pembayaran' => 'required|image|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
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
                    'message' => 'Transaction not found or not authorized'
                ], 404);
            }
            
            // Check if transaction is still valid (not expired)
            if (Carbon::now()->isAfter($transaksi->expired_at)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaction has expired'
                ], 400);
            }
            
            // Check if transaction is in valid state
            if ($transaksi->status !== 'belum_bayar') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment proof already submitted or payment completed'
                ], 400);
            }
            
            // Store the uploaded file
            $file = $request->file('bukti_pembayaran');
            $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
            $path = Storage::disk('transaksi')->putFileAs('/', $file, $fileName);
            
            // Update transaction
            $transaksi->update([
                'bukti_pembayaran' => '/' . $fileName,
                'status' => 'menunggu_konfirmasi',
                'waktu_pembayaran' => Carbon::now()
            ]);
            
            // Update pesanan status
            $pesanan->update(['status_pembayaran' => 'menunggu_konfirmasi']);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Payment proof uploaded successfully',
                'data' => [
                    'transaction' => $transaksi
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload payment proof',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get transaction details
     */
    public function getTransactionDetails(Request $request, $orderId): JsonResponse
    {
        try {
            $transaksi = Transaksi::with('toMetodePembayaran')
                ->where('order_id', $orderId)
                ->first();
                
            if (!$transaksi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaction not found'
                ], 404);
            }
            
            // Check if transaction belongs to user's order
            $pesanan = Pesanan::where('id_pesanan', $transaksi->id_pesanan)
                ->where('id_user', auth()->user()->id_user)
                ->first();
                
            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaction not found or not authorized'
                ], 404);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Transaction details retrieved successfully',
                'data' => [
                    'transaction' => $transaksi,
                    'order' => $pesanan,
                    'payment_method' => $transaksi->toMetodePembayaran,
                    'is_expired' => Carbon::now()->isAfter($transaksi->expired_at)
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get transaction details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get user's transaction history
     */
    public function getUserTransactions(Request $request): JsonResponse
    {
        try {
            // Get user's orders
            $pesananIds = Pesanan::where('id_user', auth()->user()->id_user)
                ->pluck('id_pesanan');
                
            // Get transactions for those orders
            $transactions = Transaksi::with(['toMetodePembayaran', 'toPesanan'])
                ->whereIn('id_pesanan', $pesananIds)
                ->orderBy('created_at', 'desc')
                ->get();
                
            return response()->json([
                'status' => 'success',
                'message' => 'Transactions retrieved successfully',
                'data' => $transactions
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Cancel a transaction (only if status is belum_bayar)
     */
    public function cancelTransaction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:transaksi,order_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
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
                    'message' => 'Transaction not found or not authorized'
                ], 404);
            }
            
            // Can only cancel if status is belum_bayar
            if ($transaksi->status !== 'belum_bayar') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot cancel transaction with current status: ' . $transaksi->status
                ], 400);
            }
            
            // Delete transaction
            $transaksi->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Transaction cancelled successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}