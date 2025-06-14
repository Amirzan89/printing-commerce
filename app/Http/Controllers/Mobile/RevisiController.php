<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Revisi;
use App\Models\RevisiUser;
use App\Models\RevisiEditor;
use App\Models\CatatanPesanan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RevisiController extends Controller
{
    /**
     * Get all revisi for the authenticated user with pagination
     */
    public function getAll(Request $request){
        try {
            $revisi = Revisi::join('pesanan', 'pesanan.id_pesanan', '=', 'revisi.id_pesanan')
                ->where('pesanan.id_user', User::select('id_user')->where('id_auth', $request->user()->id_auth)->first()->id_user)
                ->orderBy('revisi.created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Revisi berhasil diambil',
                'data' => $revisi
            ], 200);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil revisi: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil revisi',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed revisi information
     */
    public function getDetail(Request $request, $id_revisi){
        try {
            $pesanan = Revisi::with(['userFiles','editorFiles'])->where('id_revisi', $id_revisi)->first();
            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Revisi tidak ditemukan',
                    'data' => null
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Detail revisi berhasil diambil',
                'data' => $pesanan
            ], 200);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil detail revisi: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail revisi',
                'data' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Request revision (only if status is dikerjakan) - Enhanced Version
     */
    public function requestRevision(Request $request, $id_revisi)
    {
        try {
            $validator = Validator::make($request->only('catatan_revisi', 'files'), [
                'catatan_revisi' => 'required|string|max:500',
                'files.*' => 'sometimes|file|mimes:jpeg,png,jpg,pdf,doc,docx|max:10240'
            ], [
                'catatan_revisi.required' => 'Catatan revisi wajib di isi',
                'catatan_revisi.string' => 'Catatan revisi harus berupa string',
                'catatan_revisi.max' => 'Catatan revisi maksimal 500 karakter',
                'files.*.file' => 'File harus berupa file',
                'files.*.mimes' => 'File harus berupa gambar (jpeg, png, jpg) atau dokumen (pdf, doc, docx)',
                'files.*.max' => 'File maksimal 10MB'
            ]);

            if ($validator->fails()){
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $errorMessages){
                    $errors[$field] = $errorMessages[0];
                    break;
                }
                return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
            }
            $revisi = Revisi::join('pesanan', 'pesanan.id_pesanan', '=', 'revisi.id_pesanan')
                ->where('revisi.id_revisi', $id_revisi)
                ->where('pesanan.id_user', User::select('id_user')->where('id_auth', $request->user()->id_auth)->first()->id_user)
                ->first();
            if (!$revisi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Revisi tidak ditemukan'
                ], 404);
            }

            // Check if pesanan is in correct status for revision
            if (!in_array($revisi->status, ['dikerjakan', 'revisi'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Revisi hanya dapat diminta pada pesanan yang sedang dikerjakan atau dalam proses revisi'
                ], 422);
            }

            // Create new revision record
            $revisionNumber = $revisi->urutan_revisi + 1;
            $revision = Revisi::create([
                'urutan_revisi' => $revisionNumber,
                'catatan_user' => $request->catatan_revisi,
                'id_pesanan' => $revisi->id_pesanan
            ]);

            // Handle file uploads for revision
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $filename = 'revision_' . $revisionNumber . '_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/revisi_user'), $filename);

                    RevisiUser::create([
                        'nama_file' => $filename,
                        'type' => 'revisi',
                        'catatan_user' => $request->catatan_revisi,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'id_revisi' => $revision->id_revisi
                    ]);
                }
            }

            // Update revisi - no more revisi_used field!
            $revisi->update([
                'status' => 'revisi'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Permintaan revisi berhasil dikirim',
                'data' => [
                    'revision' => $revision->load(['userFiles', 'editorFiles']),
                    'revisi_tersisa' => $revisi->revisi_tersisa,
                    'urutan_revisi' => $revisionNumber
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error requesting revision: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal meminta revisi',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accept final work (mark as completed)
     */
    public function acceptWork(Request $request, $id_revisi){
        try {
            $validator = Validator::make($request->only('id_pesanan'), [
                'id_pesanan' => 'required',
            ], [
                'id_pesanan.required' => 'ID pesanan wajib di isi',
            ]);
            if ($validator->fails()){
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $errorMessages){
                    $errors[$field] = $errorMessages[0];
                    break;
                }
                return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
            }
            $revisi = Revisi::join('pesanan', 'pesanan.id_pesanan', '=', 'revisi.id_pesanan')
                ->where('revisi.id_revisi', $id_revisi)
                ->where('pesanan.id_user', User::select('id_user')->where('id_auth', $request->user()->id_auth)->first()->id_user)
                ->first();

            if (!$revisi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Revisi tidak ditemukan'
                ], 404);
            }

            if ($revisi->status !== 'dikerjakan') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Revisi belum dapat diterima'
                ], 422);
            }

            $revisi->update([
                'status' => 'selesai',
                'completed_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Revisi telah diterima dan selesai',
                'data' => [
                    'next_step' => 'review',
                    'download_url' => route('mobile.pesanan.download', ['uuid' => $revisi->uuid])
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error accepting work: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menerima revisi',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download final files
     */
    public function downloadFiles(Request $request, $id_revisi){
        try {
            $validator = Validator::make($request->only('id_pesanan'), [
                'id_pesanan' => 'required',
            ], [
                'id_pesanan.required' => 'ID pesanan wajib di isi',
            ]);
            if ($validator->fails()){
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $errorMessages){
                    $errors[$field] = $errorMessages[0];
                    break;
                }
                return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
            }
            $revisi = Revisi::join('pesanan', 'pesanan.id_pesanan', '=', 'revisi.id_pesanan')
                ->where('revisi.id_revisi', $id_revisi)
                ->where('pesanan.id_user', User::select('id_user')->where('id_auth', $request->user()->id_auth)->first()->id_user)
                ->first();

            if (!$revisi || $revisi->status !== 'selesai') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File belum dapat didownload'
                ], 403);
            }

            $finalFiles = RevisiEditor::where('id_revisi', $revisi->id_revisi)
                ->where('type', 'final')
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
                'message' => 'Gagal mengambil file',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get revision history for a pesanan
     */
    public function getRevisionHistory(Request $request, $id_pesanan){
        try {
            $validator = Validator::make($request->only('id_pesanan'), [
                'id_pesanan' => 'required',
            ], [
                'id_pesanan.required' => 'ID pesanan wajib di isi',
            ]);
            if ($validator->fails()){
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $errorMessages){
                    $errors[$field] = $errorMessages[0];
                    break;
                }
                return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
            }
            $pesanan = Pesanan::join('revisi', 'revisi.id_pesanan', '=', 'pesanan.id_pesanan')
                ->where('pesanan.uuid', $id_pesanan)
                ->where('id_user', User::select('id_user')->where('id_auth', $request->user()->id_auth)->first()->id_user)
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
                            'catatan_user' => $revision->catatan_user,
                            'catatan_editor' => $revision->catatan_editor,
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
    public function approveRevision(Request $request){
        try {
            $validator = Validator::make($request->only('id_pesanan', 'id_revisi'), [
                'id_pesanan' => 'required',
                'id_revisi' => 'required',
            ], [
                'id_pesanan.required' => 'ID pesanan wajib di isi',
                'id_revisi.required' => 'ID revisi wajib di isi',
            ]);
            if ($validator->fails()){
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $errorMessages){
                    $errors[$field] = $errorMessages[0];
                    break;
                }
                return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
            }
            $pesanan = Pesanan::where('uuid', $request->input('id_pesanan'))
                ->where('id_user', User::select('id_user')->where('id_auth', $request->user()->id_auth)->first()->id_user)
                ->first();

            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak ditemukan'
                ], 404);
            }

            $revision = Revisi::where('uuid', $request->input('id_revisi'))
                ->where('id_pesanan', $pesanan->id_pesanan)
                ->first();

            if (!$revision) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Revisi tidak ditemukan'
                ], 404);
            }

            if ($revision->status !== 'dikerjakan') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Revisi tidak dapat di-approve pada status ini'
                ], 422);
            }

            // Update revision status
            $revision->update([
                'status' => 'selesai',
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
                'message' => 'Gagal approve revisi',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}