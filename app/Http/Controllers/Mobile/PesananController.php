<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\PesananFile;
use App\Models\CatatanPesanan;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PesananController extends Controller
{
    /**
     * Get all orders for the authenticated user
     */
    public function getAll(Request $request)
    {
        try {
            $pesanan = Pesanan::with(['pesananFiles', 'catatanPesanan', 'transaksi'])
                ->where('id_user', $request->user()->id_user)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Orders retrieved successfully',
                'data' => $pesanan
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving orders: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve orders',
                'data' => null
            ], 500);
        }
    }

    /**
     * Get detailed order information
     */
    public function getDetail($id)
    {
        try {
            $pesanan = Pesanan::with(['pesananFiles', 'catatanPesanan', 'transaksi'])
                ->where('id_pesanan', $id)
                ->first();

            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Order details retrieved successfully',
                'data' => $pesanan
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving order detail: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve order details',
                'data' => null
            ], 500);
        }
    }

    public function create(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'deskripsi' => 'required|string|max:500',
                'total_harga' => 'required|integer|min:0',
                'estimasi_waktu' => 'required|date',
                'jumlah_revisi' => 'required|integer|min:0|max:255',
                'id_jasa' => 'required|exists:jasa,id_jasa',
                'id_paket_jasa' => 'required|exists:paket_jasa,id_paket_jasa',
                'files.*' => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240'
            ], [
                'deskripsi.required' => 'Description is required',
                'deskripsi.max' => 'Description cannot exceed 500 characters',
                'total_harga.required' => 'Total price is required',
                'total_harga.integer' => 'Total price must be a number',
                'total_harga.min' => 'Total price cannot be negative',
                'estimasi_waktu.required' => 'Estimated time is required',
                'estimasi_waktu.date' => 'Invalid date format for estimated time',
                'jumlah_revisi.required' => 'Number of revisions is required',
                'jumlah_revisi.integer' => 'Number of revisions must be a number',
                'jumlah_revisi.min' => 'Number of revisions cannot be negative',
                'jumlah_revisi.max' => 'Number of revisions cannot exceed 255',
                'id_jasa.required' => 'Service ID is required',
                'id_jasa.exists' => 'Invalid service ID',
                'id_paket_jasa.required' => 'Service package ID is required',
                'id_paket_jasa.exists' => 'Invalid service package ID',
                'files.*.required' => 'Files are required',
                'files.*.file' => 'Invalid file format',
                'files.*.mimes' => 'File must be jpeg, png, jpg, or pdf',
                'files.*.max' => 'File size cannot exceed 10MB'
            ]);
            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $errorMessages) {
                    $errors[$field] = $errorMessages[0];
                    break;
                }
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $errors
                ], 422);
            }
            $pesanan = Pesanan::create([
                'uuid' => Str::uuid(),
                'deskripsi' => $request->deskripsi,
                'status' => 'pending',
                'status_pembayaran' => 'belum_bayar',
                'total_harga' => $request->total_harga,
                'estimasi_waktu' => $request->estimasi_waktu,
                'jumlah_revisi' => $request->jumlah_revisi,
                'id_user' => $request->user()->id_user,
                'id_jasa' => $request->id_jasa,
                'id_paket_jasa' => $request->id_paket_jasa
            ]);
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('uploads/pesanan'), $filename);

                    PesananFile::create([
                        'file_path' => 'uploads/pesanan/' . $filename,
                        'status' => 'preview',
                        'uploaded_at' => now(),
                        'id_pesanan' => $pesanan->id_pesanan
                    ]);
                }
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Order created successfully',
                'data' => $pesanan->load(['pesananFiles'])
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating order: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create order',
                'data' => null
            ], 500);
        }
    }
    public function update(Request $request, $id){
        try {
            $pesanan = Pesanan::find($id);
            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order not found',
                    'data' => null
                ], 404);
            }
            if (!in_array($pesanan->status, ['pending', 'revisi'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order cannot be updated in its current status',
                    'data' => null
                ], 422);
            }
            $validator = Validator::make($request->all(), [
                'deskripsi' => 'sometimes|string|max:500',
                'files.*' => 'sometimes|file|mimes:jpeg,png,jpg,pdf|max:10240'
            ]);
            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $errorMessages) {
                    $errors[$field] = $errorMessages[0];
                    break;
                }
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $errors
                ], 422);
            }
            if ($request->has('deskripsi')) {
                $pesanan->update(['deskripsi' => $request->deskripsi]);
            }
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('uploads/pesanan'), $filename);

                    PesananFile::create([
                        'file_path' => 'uploads/pesanan/' . $filename,
                        'status' => 'revisi',
                        'uploaded_at' => now(),
                        'id_pesanan' => $pesanan->id_pesanan
                    ]);
                }
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Order updated successfully',
                'data' => $pesanan->load(['pesananFiles'])
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating order: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update order',
                'data' => null
            ], 500);
        }
    }
    public function delete($id){
        try {
            $pesanan = Pesanan::find($id);
            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order not found',
                    'data' => null
                ], 404);
            }
            if(!in_array($pesanan->status, ['pending'])){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order cannot be cancelled in its current status',
                    'data' => null
                ], 422);
            }
            $pesanan->update(['status' => 'dibatalkan']);
            return response()->json([
                'status' => 'success',
                'message' => 'Order cancelled successfully',
                'data' => null
            ], 200);
        }catch (\Exception $e){
            Log::error('Error cancelling order: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel order',
                'data' => null
            ], 500);
        }
    }
}