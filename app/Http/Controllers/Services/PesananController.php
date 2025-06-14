<?php
namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\CatatanPesanan;
use App\Models\PesananRevisi;
use App\Models\RevisiUser;
use App\Models\Transaksi;
use App\Models\Jasa;
use App\Models\PaketJasa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Editor;
use App\Models\User;

class PesananController extends Controller
{
    /**
     * ADMIN: Get all pesanan with filters
     */
    public function getAllPesanan(Request $request)
    {
        try {
            $status = $request->get('status', 'all');
            $search = $request->get('search');
            $perPage = $request->get('per_page', 15);

            $query = Pesanan::with(['toUser', 'toJasa', 'toPaketJasa']);

            // Filter by status
            if ($status !== 'all') {
                $query->where('status', $status);
            }

            // Search by user name or pesanan UUID
            if ($search) {
                $query->whereHas('toUser', function($q) use ($search) {
                    $q->where('nama_user', 'like', "%{$search}%");
                })->orWhere('uuid', 'like', "%{$search}%");
            }

            $pesanan = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $pesanan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ADMIN: Get pesanan detail
     */
    public function getPesananDetail($uuid)
    {
        try {
            $pesanan = Pesanan::with([
                'toUser',
                'toJasa',
                'toPaketJasa', 
                'fromCatatanPesanan',
                'revisions.userFiles',
                'revisions.editorFiles.editor'
            ])->where('uuid', $uuid)->first();

            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak ditemukan'
                ], 404);
            }

            // Get editors who worked on this pesanan
            $workingEditors = $pesanan->editorFiles()->with('editor')->get()
                ->pluck('editor')->unique('id_editor')->values();

            // Add editors info to response
            $pesananData = $pesanan->toArray();
            $pesananData['working_editors'] = $workingEditors;

            return response()->json([
                'status' => 'success',
                'data' => $pesananData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ADMIN: Update pesanan status
     */
    public function updateStatus(Request $request, $uuid)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:menunggu,proses,dikerjakan,revisi,selesai,dibatalkan'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $pesanan = Pesanan::where('uuid', $uuid)->first();

            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak ditemukan'
                ], 404);
            }

            $updateData = ['status' => $request->status];

            // Set timestamps based on status
            switch ($request->status) {
                case 'proses':
                    $updateData['confirmed_at'] = now();
                    break;
                case 'dikerjakan':
                    $updateData['assigned_at'] = now();
                    break;
                case 'selesai':
                    $updateData['completed_at'] = now();
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
    public function deletePesanan($uuid)
    {
        try {
            $pesanan = Pesanan::where('uuid', $uuid)->first();

            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak ditemukan'
                ], 404);
            }

            // Check if pesanan can be deleted
            if (in_array($pesanan->status, ['dikerjakan', 'selesai'])) {
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

    /**
     * ADMIN: Get pesanan statistics
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total_pesanan' => Pesanan::count(),
                'menunggu' => Pesanan::where('status', 'menunggu')->count(),
                'proses' => Pesanan::where('status', 'proses')->count(),
                'dikerjakan' => Pesanan::where('status', 'dikerjakan')->count(),
                'revisi' => Pesanan::where('status', 'revisi')->count(),
                'selesai' => Pesanan::where('status', 'selesai')->count(),
                'dibatalkan' => Pesanan::where('status', 'dibatalkan')->count(),
                'total_revenue' => Pesanan::where('status_pembayaran', 'lunas')->sum('total_harga'),
                'pending_payment' => Pesanan::where('status_pembayaran', 'belum_bayar')->count()
            ];

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ADMIN: Bulk update pesanan status
     */
    public function bulkUpdateStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'pesanan_ids' => 'required|array',
                'pesanan_ids.*' => 'exists:pesanan,uuid',
                'status' => 'required|in:menunggu,proses,dikerjakan,revisi,selesai,dibatalkan'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $updated = Pesanan::whereIn('uuid', $request->pesanan_ids)
                ->update(['status' => $request->status]);

            return response()->json([
                'status' => 'success',
                'message' => "{$updated} pesanan berhasil diupdate",
                'data' => ['updated_count' => $updated]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal bulk update: ' . $e->getMessage()
            ], 500);
        }
    }
}