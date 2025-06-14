<?php
namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\PesananRevisi;
use App\Models\RevisiUser;
use App\Models\RevisiEditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RevisiController extends Controller
{
    // User creates revision request
    public function createRevision(Request $request, $pesananId)
    {
        try {
            $pesanan = Pesanan::findOrFail($pesananId);
            
            // Check if user owns this pesanan
            if ($pesanan->id_user !== auth()->id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            // Check revision limit
            if ($pesanan->revisi_used >= $pesanan->maksimal_revisi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jumlah revisi sudah habis'
                ], 422);
            }
            
            $validator = Validator::make($request->all(), [
                'catatan_revisi' => 'required|string|max:1000',
                'files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Create revision record
            $revisionNumber = $pesanan->revisi_used + 1;
            $revision = PesananRevisi::create([
                'urutan_revisi' => $revisionNumber,
                'catatan_user' => $request->catatan_revisi,
                'id_pesanan' => $pesanan->id_pesanan
            ]);
            
            // Handle file uploads
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
            
            // Update pesanan status
            $pesanan->update(['status' => 'revisi']);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Revisi berhasil dibuat',
                'data' => [
                    'revision' => $revision->load(['userFiles', 'editorFiles']),
                    'revisi_tersisa' => $pesanan->revisi_tersisa,
                    'urutan_revisi' => $revisionNumber
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Editor responds to revision
    public function respondToRevision(Request $request, $revisionId)
    {
        try {
            $revision = PesananRevisi::findOrFail($revisionId);
            $pesanan = $revision->pesanan;
            
            // Check if editor is assigned to this pesanan
            if ($pesanan->id_editor !== auth()->guard('editor')->id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:preview,final',
                'notes' => 'nullable|string|max:1000',
                'files.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Handle file uploads
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $filename = 'editor_' . $revision->urutan_revisi . '_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/revisi_editor'), $filename);
                    
                    RevisiEditor::create([
                        'nama_file' => $filename,
                        'type' => $request->type,
                        'editor_notes' => $request->notes,
                        'uploaded_at' => now(),
                        'id_editor' => auth()->guard('editor')->id(),
                        'id_revisi' => $revision->id_revisi
                    ]);
                }
            }
            
            // Update pesanan status based on file type
            if ($request->type === 'final') {
                $pesanan->update(['status' => 'selesai', 'completed_at' => now()]);
            } else {
                $pesanan->update(['status' => 'dikerjakan']);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Response berhasil dikirim',
                'data' => [
                    'revision' => $revision->load(['userFiles', 'editorFiles']),
                    'pesanan_status' => $pesanan->status
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Get revision details
    public function getRevision($revisionId)
    {
        try {
            $revision = PesananRevisi::with(['pesanan', 'userFiles', 'editorFiles'])
                ->findOrFail($revisionId);
            
            // Check authorization
            $user = auth()->user();
            $editor = auth()->guard('editor')->user();
            
            if (!$user && !$editor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            if ($user && $revision->pesanan->id_user !== $user->id_user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            if ($editor && $revision->pesanan->id_editor !== $editor->id_editor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'revision' => $revision,
                    'communication_status' => $revision->status
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Get all revisions for a pesanan
    public function getPesananRevisions($pesananId)
    {
        try {
            $pesanan = Pesanan::with(['revisions.userFiles', 'revisions.editorFiles'])
                ->findOrFail($pesananId);
            
            // Check authorization
            $user = auth()->user();
            $editor = auth()->guard('editor')->user();
            
            if ($user && $pesanan->id_user !== $user->id_user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            if ($editor && $pesanan->id_editor !== $editor->id_editor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'pesanan' => $pesanan,
                    'revisions' => $pesanan->revisions,
                    'revisi_used' => $pesanan->revisi_used,
                    'revisi_tersisa' => $pesanan->revisi_tersisa
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
} 