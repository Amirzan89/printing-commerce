<?php
namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Editor;
use Carbon\Carbon;

class PesananController extends Controller
{
    /**
     * ADMIN: Update pesanan status
     */
    public function updateStatus(Request $request){
        try {
            $validator = Validator::make($request->only('id_pesanan', 'status_pesanan', 'editor_id'), [
                'id_pesanan' => 'required',
                'status_pesanan' => 'required|in:pending,diproses,menunggu_editor,dikerjakan,revisi,selesai,dibatalkan',
                'editor_id' => 'nullable|exists:editor,id_editor'
            ], [
                'id_pesanan.required' => 'ID pesanan harus diisi',
                'status_pesanan.required' => 'Status pesanan harus diisi',
                'status_pesanan.in' => 'Status pesanan tidak valid',
                'editor_id.exists' => 'Editor tidak ditemukan'
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $errorMessages){
                    $errors[$field] = $errorMessages[0];
                    break;
                }
                return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
            }

            $pesanan = Pesanan::where('uuid', $request->input('id_pesanan'))->first();

            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak ditemukan'
                ], 404);
            }

            $updateData = ['status_pesanan' => $request->input('status_pesanan')];

            // Set timestamps based on status
            switch ($request->input('status_pesanan')) {
                case 'diproses':
                    $updateData['confirmed_at'] = Carbon::now();
                    break;
                case 'dikerjakan':
                    $updateData['assigned_at'] = Carbon::now();
                    // If editor_id is provided, assign the editor
                    if (!$request->has('editor_id') || is_null($request->input('editor_id'))) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Silahkan pilih editor terlebih dahulu'
                        ], 400);
                    }
                    $editor = Editor::where('id_editor', $request->input('editor_id'))->first();
                    if (!$editor) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Editor tidak ditemukan'
                        ], 404);
                    }
                    $updateData['id_editor'] = $request->input('editor_id');
                    break;
                case 'selesai':
                    $updateData['completed_at'] = Carbon::now();
                    break;
            }
            $pesanan->update($updateData);
            return response()->json([
                'status' => 'success',
                'message' => 'Status pesanan berhasil diupdate',
                'data' => $pesanan->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupdate status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ADMIN: Delete pesanan (hard delete)
     */
    public function deletePesanan(Request $request)
    {
        try {
            $validator = Validator::make($request->only('id_pesanan'), [
                'id_pesanan' => 'required|exists:pesanan,uuid',
            ], [
                'id_pesanan.required' => 'ID pesanan harus diisi',
                'id_pesanan.exists' => 'ID pesanan tidak ditemukan'
            ]);
            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $errorMessages){
                    $errors[$field] = $errorMessages[0];
                    break;
                }
                return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
            }
            $pesanan = Pesanan::where('uuid', $request->input('id_pesanan'))->first();

            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak ditemukan'
                ], 404);
            }

            // Check if pesanan can be deleted
            if (in_array($pesanan->status_pesanan, ['dikerjakan', 'selesai'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan yang sedang dikerjakan atau selesai tidak dapat dihapus'
                ], 422);
            }

            // Delete related records
            $pesanan->fromCatatanPesanan()->delete();
            $pesanan->revisions()->delete();
            $pesanan->fromTransaksi()->delete();
            
            $pesanan->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus pesanan: ' . $e->getMessage()
            ], 500);
        }
    }
}