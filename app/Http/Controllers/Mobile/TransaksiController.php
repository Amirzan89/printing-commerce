<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class TransaksiController extends Controller
{
    /**
     * Get all transactions
     *
     * @return JsonResponse
     */
    public function getAll(): JsonResponse
    {
        try {
            $transactions = Transaksi::with(['metodePembayaran', 'pesanan'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'status' => 'success',
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaction detail
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getDetail($id): JsonResponse
    {
        try {
            $transaction = Transaksi::with(['metodePembayaran', 'pesanan'])
                ->findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create new transaction
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            // Validation rules based on migration
            $validator = Validator::make($request->all(), [
                'jumlah' => 'required|integer|min:1',
                'status' => 'required|in:belum_bayar,menunggu_konfirmasi,lunas',
                'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'id_metode_pembayaran' => 'required|exists:metode_pembayaran,id_metode_pembayaran',
                'id_pesanan' => 'required|exists:pesanan,id_pesanan'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Sanitize and prepare data
            $data = $validator->validated();
            
            // Handle file upload
            if ($request->hasFile('bukti_pembayaran')) {
                $file = $request->file('bukti_pembayaran');
                $fileName = time() . '_' . Str::slug($file->getClientOriginalName());
                $file->storeAs('public/bukti_pembayaran', $fileName);
                $data['bukti_pembayaran'] = $fileName;
            }

            // Generate unique order ID
            $data['order_id'] = 'TRX-' . time() . '-' . Str::random(6);
            
            // Set timestamps
            $data['waktu_pembayaran'] = Carbon::now();
            $data['expired_at'] = Carbon::now()->addHours(24); // Set 24 hours expiry

            $transaction = Transaksi::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction created successfully',
                'data' => $transaction
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $transaction = Transaksi::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'jumlah' => 'integer|min:1',
                'status' => 'in:belum_bayar,menunggu_konfirmasi,lunas',
                'bukti_pembayaran' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'id_metode_pembayaran' => 'exists:metode_pembayaran,id_metode_pembayaran',
                'id_pesanan' => 'exists:pesanan,id_pesanan'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            $data = $validator->validated();
            if ($request->hasFile('bukti_pembayaran')) {
                if ($transaction->bukti_pembayaran) {
                    Storage::delete('public/bukti_pembayaran/' . $transaction->bukti_pembayaran);
                }
                $file = $request->file('bukti_pembayaran');
                $fileName = time() . '_' . Str::slug($file->getClientOriginalName());
                $file->storeAs('public/bukti_pembayaran', $fileName);
                $data['bukti_pembayaran'] = $fileName;
            }

            if (isset($data['status']) && $data['status'] === 'lunas') {
                $data['waktu_pembayaran'] = Carbon::now();
            }
            $transaction->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction updated successfully',
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            $transaction = Transaksi::findOrFail($id);            
            if ($transaction->bukti_pembayaran) {
                Storage::delete('public/bukti_pembayaran/' . $transaction->bukti_pembayaran);
            }
            $transaction->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
