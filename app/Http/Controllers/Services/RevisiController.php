<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\ChatMessage;
use App\Models\Editor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RevisiController extends Controller
{
    /**
     * ADMIN: Get all pesanan that have revision requests (chat-based)
     */
    public function getAllRevisionRequests(Request $request)
    {
        try {
            $status = $request->get('status', 'all');
            $search = $request->get('search');
            $perPage = $request->get('per_page', 15);

            // Get pesanan that have revision-related chat messages
            $query = Pesanan::with(['toUser', 'toJasa', 'toPaketJasa', 'toEditor'])
                ->whereHas('chatMessages', function($q) {
                    $q->where('message', 'like', '%revisi%')
                      ->orWhere('message', 'like', '%revision%')
                      ->orWhere('message', 'like', '%perbaikan%');
                });

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

            $pesanan = $query->orderBy('updated_at', 'desc')->paginate($perPage);

            // Add latest revision message for each pesanan
            $pesanan->getCollection()->transform(function ($item) {
                $latestRevisionMessage = $item->chatMessages()
                    ->where(function($q) {
                        $q->where('message', 'like', '%revisi%')
                          ->orWhere('message', 'like', '%revision%')
                          ->orWhere('message', 'like', '%perbaikan%');
                    })
                    ->latest()
                    ->first();
                
                $item->latest_revision_message = $latestRevisionMessage;
                return $item;
            });

            return response()->json([
                'status' => 'success',
                'data' => $pesanan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data revisi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ADMIN: Get revision detail for specific pesanan
     */
    public function getRevisionDetail($uuid)
    {
        try {
            $pesanan = Pesanan::with([
                'toUser',
                'toJasa',
                'toPaketJasa',
                'toEditor',
                'chatMessages' => function($query) {
                    $query->orderBy('created_at', 'asc');
                }
            ])->where('uuid', $uuid)->first();

            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak ditemukan'
                ], 404);
            }

            // Filter revision-related messages
            $revisionMessages = $pesanan->chatMessages->filter(function($message) {
                return stripos($message->message, 'revisi') !== false ||
                       stripos($message->message, 'revision') !== false ||
                       stripos($message->message, 'perbaikan') !== false;
            });

            // Get available editors
            $availableEditors = Editor::all();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'pesanan' => $pesanan,
                    'revision_messages' => $revisionMessages->values(),
                    'all_messages' => $pesanan->chatMessages,
                    'available_editors' => $availableEditors,
                    'revision_count' => $revisionMessages->count(),
                    'max_revisions' => $pesanan->maksimal_revisi
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail revisi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ADMIN: Assign editor to handle revision
     */
    public function assignEditor(Request $request, $uuid)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_editor' => 'required|exists:editor,id_editor',
                'notes' => 'nullable|string|max:500'
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

            $editor = Editor::find($request->id_editor);

            // Update pesanan with assigned editor
            $pesanan->update([
                'id_editor' => $request->id_editor,
                'status_pesanan' => 'dikerjakan',
                'assigned_at' => now()
            ]);

            // Send notification message to chat
            $assignmentMessage = "Editor {$editor->nama_editor} telah ditugaskan untuk menangani revisi Anda.";
            if ($request->notes) {
                $assignmentMessage .= " Catatan: " . $request->notes;
            }

            ChatMessage::create([
                'uuid' => Str::uuid(),
                'message' => $assignmentMessage,
                'sender_type' => 'admin',
                'sender_id' => auth()->id(),
                'id_pesanan' => $pesanan->id_pesanan,
                'created_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Editor berhasil ditugaskan',
                'data' => [
                    'pesanan' => $pesanan->fresh(),
                    'assigned_editor' => $editor
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menugaskan editor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ADMIN: Send revision response via chat
     */
    public function sendRevisionResponse(Request $request, $uuid)
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:1000',
                'action' => 'required|in:accept,reject,request_clarification'
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

            // Send response message
            ChatMessage::create([
                'uuid' => Str::uuid(),
                'message' => $request->message,
                'sender_type' => 'admin',
                'sender_id' => auth()->id(),
                'id_pesanan' => $pesanan->id_pesanan,
                'created_at' => now()
            ]);

            // Update pesanan status based on action
            $statusUpdate = [];
            switch ($request->action) {
                case 'accept':
                    $statusUpdate['status'] = 'dikerjakan';
                    break;
                case 'reject':
                    $statusUpdate['status'] = 'selesai';
                    break;
                case 'request_clarification':
                    // Status remains the same, just asking for clarification
                    break;
            }

            if (!empty($statusUpdate)) {
                $pesanan->update($statusUpdate);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Respon revisi berhasil dikirim',
                'data' => [
                    'pesanan' => $pesanan->fresh(),
                    'action_taken' => $request->action
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim respon: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ADMIN: Get revision statistics
     */
    public function getRevisionStatistics()
    {
        try {
            // Count pesanan with revision requests
            $totalRevisionRequests = Pesanan::whereHas('chatMessages', function($q) {
                $q->where('message', 'like', '%revisi%')
                  ->orWhere('message', 'like', '%revision%')
                  ->orWhere('message', 'like', '%perbaikan%');
            })->count();

            // Count by status
            $pendingRevisions = Pesanan::where('status_pesanan', 'revisi')->count();
            $inProgressRevisions = Pesanan::where('status_pesanan', 'dikerjakan')
                ->whereHas('chatMessages', function($q) {
                    $q->where('message', 'like', '%revisi%')
                      ->orWhere('message', 'like', '%revision%')
                      ->orWhere('message', 'like', '%perbaikan%');
                })->count();

            // Average revision count per pesanan
            $avgRevisionsPerOrder = ChatMessage::where('message', 'like', '%revisi%')
                ->orWhere('message', 'like', '%revision%')
                ->orWhere('message', 'like', '%perbaikan%')
                ->count() / max($totalRevisionRequests, 1);

            // Most active editors in revisions
            $activeEditors = Editor::withCount(['chatMessages' => function($q) {
                $q->where('message', 'like', '%revisi%')
                  ->orWhere('message', 'like', '%revision%')
                  ->orWhere('message', 'like', '%perbaikan%');
            }])->orderBy('chat_messages_count', 'desc')->take(5)->get();

            $stats = [
                'total_revision_requests' => $totalRevisionRequests,
                'pending_revisions' => $pendingRevisions,
                'in_progress_revisions' => $inProgressRevisions,
                'avg_revisions_per_order' => round($avgRevisionsPerOrder, 2),
                'active_editors' => $activeEditors
            ];

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil statistik revisi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ADMIN: Mark revision as completed
     */
    public function markRevisionCompleted(Request $request, $uuid)
    {
        try {
            $validator = Validator::make($request->all(), [
                'completion_notes' => 'nullable|string|max:500'
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

            // Update pesanan status
            $pesanan->update([
                'status_pesanan' => 'selesai',
                'completed_at' => now()
            ]);

            // Send completion message
            $completionMessage = "Revisi telah selesai dikerjakan dan pesanan Anda sudah siap.";
            if ($request->completion_notes) {
                $completionMessage .= " Catatan: " . $request->completion_notes;
            }

            ChatMessage::create([
                'uuid' => Str::uuid(),
                'message' => $completionMessage,
                'sender_type' => 'admin',
                'sender_id' => auth()->id(),
                'id_pesanan' => $pesanan->id_pesanan,
                'created_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Revisi berhasil ditandai selesai',
                'data' => $pesanan->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menandai revisi selesai: ' . $e->getMessage()
            ], 500);
        }
    }
} 