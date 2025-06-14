<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\PesananFile;
use App\Models\Transaksi;
use App\Models\Jasa;
use App\Models\PaketJasa;
use App\Models\PesananRevisi;
use App\Models\RevisiUser;
use App\Models\CatatanPesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PesananController extends Controller
{
    /**
     * Get all orders for the authenticated user with pagination
     */
    public function getAll(Request $request)
    {
        try {
            $status = $request->query('status'); // filter by status
            $query = Pesanan::with(['toJasa', 'toPaketJasa', 'toEditor', 'fromTransaksi'])
                ->where('id_user', $request->user()->id_user);
            
            if ($status) {
                $query->where('status', $status);
            }
            
            $pesanan = $query->orderBy('created_at', 'desc')
                ->paginate(10);

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
    public function getDetail($uuid)
    {
        try {
            $pesanan = Pesanan::with([
                'fromPesananFile', 
                'fromCatatanPesanan', 
                'fromTransaksi.toMetodePembayaran',
                'toJasa',
                'toPaketJasa',
                'toEditor'
            ])
                ->where('uuid', $uuid)
                ->where('id_user', request()->user()->id_user)
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

    /**
     * Create new order (Step 1 in flow)
     */
    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'catatan_brief' => 'required|string|max:1000',
                'id_jasa' => 'required|exists:jasa,id_jasa',
                'id_paket_jasa' => 'required|exists:paket_jasa,id_paket_jasa',
                'gambar_referensi' => 'nullable|file|mimes:jpeg,png,jpg|max:5120', // 5MB max for image
                'maksimal_revisi' => 'nullable|integer|min:0|max:5'
            ], [
                'catatan_brief.required' => 'Catatan brief wajib diisi',
                'catatan_brief.max' => 'Catatan brief maksimal 1000 karakter',
                'id_jasa.required' => 'Pilih jasa terlebih dahulu',
                'id_jasa.exists' => 'Jasa tidak valid',
                'id_paket_jasa.required' => 'Pilih paket jasa terlebih dahulu',
                'id_paket_jasa.exists' => 'Paket jasa tidak valid',
                'gambar_referensi.mimes' => 'Format gambar harus jpeg, png, atau jpg',
                'gambar_referensi.max' => 'Ukuran gambar maksimal 5MB'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->first()
                ], 422);
            }

            // Get jasa and paket details for pricing
            $jasa = Jasa::find($request->id_jasa);
            $paketJasa = PaketJasa::find($request->id_paket_jasa);
            
            if (!$jasa || !$paketJasa) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jasa atau paket tidak ditemukan'
                ], 404);
            }

            // Calculate total price and estimation
            $totalHarga = $paketJasa->harga;
            $estimasiWaktu = Carbon::now()->addDays($paketJasa->estimasi_hari);
            $jumlahRevisi = $request->maksimal_revisi ?? $paketJasa->maksimal_revisi;

            $pesanan = Pesanan::create([
                'uuid' => Str::uuid(),
                'deskripsi' => $request->catatan_brief, // Use brief as main description
                'status' => 'pending',
                'status_pembayaran' => 'belum_bayar',
                'total_harga' => $totalHarga,
                'estimasi_waktu' => $estimasiWaktu,
                'maksimal_revisi' => $jumlahRevisi,
                'id_user' => $request->user()->id_user,
                'id_jasa' => $request->id_jasa,
                'id_paket_jasa' => $request->id_paket_jasa
            ]);

            // Handle image upload for brief
            $gambarFilename = null;
            if ($request->hasFile('gambar_referensi')) {
                $file = $request->file('gambar_referensi');
                $gambarFilename = 'brief_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/brief'), $gambarFilename);
            }

            // Create brief record
            CatatanPesanan::create([
                'catatan_pesanan' => $request->catatan_brief,
                'gambar_referensi' => $gambarFilename,
                'uploaded_at' => now(),
                'id_pesanan' => $pesanan->id_pesanan,
                'id_user' => $request->user()->id_user
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibuat. Silahkan lanjutkan ke pembayaran.',
                'data' => [
                    'pesanan' => $pesanan->load(['fromCatatanPesanan', 'toJasa', 'toPaketJasa']),
                    'next_step' => 'payment',
                    'payment_url' => route('mobile.payment.create', ['order_uuid' => $pesanan->uuid])
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating order: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat pesanan',
                'data' => null
            ], 500);
        }
    }

    /**
     * Cancel order (only if pending or belum_bayar)
     */
    public function cancel(Request $request, $uuid)
    {
        try {
            $pesanan = Pesanan::where('uuid', $uuid)
                ->where('id_user', $request->user()->id_user)
                ->first();

            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak ditemukan'
                ], 404);
            }

            if (!in_array($pesanan->status, ['pending', 'menunggu_konfirmasi'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak dapat dibatalkan pada status ini'
                ], 422);
            }

            // Cancel related transactions
            Transaksi::where('id_pesanan', $pesanan->id_pesanan)
                ->update(['status' => 'dibatalkan']);

            $pesanan->update([
                'status' => 'dibatalkan',
                'status_pembayaran' => 'dibatalkan'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibatalkan'
            ]);
        } catch (\Exception $e) {
            Log::error('Error canceling order: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membatalkan pesanan'
            ], 500);
        }
    }

    /**
     * Request revision (only if status is dikerjakan) - Enhanced Version
     */
    public function requestRevision(Request $request, $uuid)
    {
        try {
            $validator = Validator::make($request->all(), [
                'catatan_revisi' => 'required|string|max:500',
                'files.*' => 'sometimes|file|mimes:jpeg,png,jpg,pdf,doc,docx|max:10240'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $pesanan = Pesanan::with(['revisions'])->where('uuid', $uuid)
                ->where('id_user', $request->user()->id_user)
                ->first();

            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak ditemukan'
                ], 404);
            }

            if ($pesanan->status !== 'dikerjakan') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Revisi hanya dapat diminta pada pesanan yang sedang dikerjakan'
                ], 422);
            }

            if ($pesanan->revisi_used >= $pesanan->maksimal_revisi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jumlah revisi sudah habis'
                ], 422);
            }

            // Check if pesanan is in correct status for revision
            if (!in_array($pesanan->status, ['dikerjakan', 'revisi'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak dapat direvisi pada status ini'
                ], 422);
            }

            // Create new revision record
            $revisionNumber = $pesanan->revisi_used + 1;
            $revision = PesananRevisi::create([
                'urutan_revisi' => $revisionNumber,
                'catatan_user' => $request->catatan_revisi,
                'id_pesanan' => $pesanan->id_pesanan
            ]);

            // Handle file uploads for revision
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $filename = 'revision_' . $revisionNumber . '_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/revisi_user'), $filename);

                    RevisiUser::create([
                        'nama_file' => $filename,
                        'type' => 'revisi',
                        'user_notes' => $request->catatan_revisi,
                        'uploaded_at' => now(),
                        'id_user' => auth()->id(),
                        'id_revisi' => $revision->id_revisi
                    ]);
                }
            }

            // Update pesanan - no more revisi_used field!
            $pesanan->update([
                'status' => 'revisi'
            ]);

            // Add revision note to catatan_pesanan for backward compatibility
            CatatanPesanan::create([
                'catatan' => "Revisi #$revisionNumber: " . $request->catatan_revisi,
                'tanggal' => now(),
                'id_pesanan' => $pesanan->id_pesanan
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Permintaan revisi berhasil dikirim',
                'data' => [
                    'revision' => $revision->load(['userFiles', 'editorFiles']),
                    'revisi_tersisa' => $pesanan->revisi_tersisa, // Using accessor
                    'urutan_revisi' => $revisionNumber
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error requesting revision: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal meminta revisi'
            ], 500);
        }
    }

    /**
     * Accept final work (mark as completed)
     */
    public function acceptWork(Request $request, $uuid)
    {
        try {
            $pesanan = Pesanan::where('uuid', $uuid)
                ->where('id_user', $request->user()->id_user)
                ->first();

            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak ditemukan'
                ], 404);
            }

            if ($pesanan->status !== 'dikerjakan') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan belum dapat diterima'
                ], 422);
            }

            $pesanan->update([
                'status' => 'selesai',
                'completed_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan telah diterima dan selesai',
                'data' => [
                    'next_step' => 'review',
                    'download_url' => route('mobile.pesanan.download', ['uuid' => $uuid])
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error accepting work: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menerima pekerjaan'
            ], 500);
        }
    }

    /**
     * Download final files
     */
    public function downloadFiles($uuid)
    {
        try {
            $pesanan = Pesanan::where('uuid', $uuid)
                ->where('id_user', request()->user()->id_user)
                ->first();

            if (!$pesanan || $pesanan->status !== 'selesai') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File belum dapat didownload'
                ], 403);
            }

            $finalFiles = PesananFile::where('id_pesanan', $pesanan->id_pesanan)
                ->where('status', 'final')
                ->get();

            if ($finalFiles->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File final belum tersedia'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'File siap didownload',
                'data' => [
                    'files' => $finalFiles->map(function ($file) {
                        return [
                            'name' => $file->nama_file,
                            'url' => $file->url, // Using accessor
                            'uploaded_at' => $file->uploaded_at
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error downloading files: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil file'
            ], 500);
        }
    }

    /**
     * Get revision history for a pesanan
     */
    public function getRevisionHistory(Request $request, $uuid)
    {
        try {
            $pesanan = Pesanan::with([
                'revisions.userFiles',
                'revisions.editorFiles'
            ])->where('uuid', $uuid)
                ->where('id_user', $request->user()->id_user)
                ->first();

            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Riwayat revisi berhasil diambil',
                'data' => [
                    'pesanan_info' => [
                        'uuid' => $pesanan->uuid,
                        'status' => $pesanan->status,
                        'maksimal_revisi' => $pesanan->maksimal_revisi,
                        'revisi_used' => $pesanan->revisi_used,
                        'revisi_tersisa' => $pesanan->revisi_tersisa
                    ],
                    'revisions' => $pesanan->revisions->map(function ($revision) {
                        return [
                            'uuid' => $revision->uuid,
                            'urutan_revisi' => $revision->urutan_revisi,
                            'status' => $revision->status,
                            'user_notes' => $revision->user_notes,
                            'editor_notes' => $revision->editor_notes,
                            'requested_at' => $revision->requested_at,
                            'started_at' => $revision->started_at,
                            'completed_at' => $revision->completed_at,
                            'user_files' => $revision->userFiles->map(function ($file) {
                                return [
                                    'nama_file' => $file->nama_file,
                                    'file_url' => url($file->file_path),
                                    'file_size' => $file->file_size,
                                    'uploaded_at' => $file->uploaded_at
                                ];
                            }),
                            'editor_files' => $revision->editorFiles->map(function ($file) {
                                return [
                                    'nama_file' => $file->nama_file,
                                    'file_url' => url($file->file_path),
                                    'file_size' => $file->file_size,
                                    'uploaded_at' => $file->uploaded_at
                                ];
                            })
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting revision history: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil riwayat revisi'
            ], 500);
        }
    }

    /**
     * Approve revision result (user accepts editor's revision work)
     */
    public function approveRevision(Request $request, $uuid, $revisionUuid)
    {
        try {
            $pesanan = Pesanan::where('uuid', $uuid)
                ->where('id_user', $request->user()->id_user)
                ->first();

            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak ditemukan'
                ], 404);
            }

            $revision = PesananRevisi::where('uuid', $revisionUuid)
                ->where('id_pesanan', $pesanan->id_pesanan)
                ->first();

            if (!$revision) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Revisi tidak ditemukan'
                ], 404);
            }

            if ($revision->status !== 'in_progress') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Revisi tidak dapat di-approve pada status ini'
                ], 422);
            }

            // Update revision status
            $revision->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            // Update pesanan back to dikerjakan
            $pesanan->update([
                'status' => 'dikerjakan'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Revisi berhasil di-approve',
                'data' => [
                    'revision' => $revision->fresh(),
                    'pesanan_status' => $pesanan->fresh()->status
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving revision: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal approve revisi'
            ], 500);
        }
    }
}