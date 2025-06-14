<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Editor;
use App\Models\Revisi;
use Illuminate\Http\Request;

class RevisiController extends Controller
{
    /**
     * Show all revision requests page
     */
    public function showAll()
    {
        $title = 'Manajemen Revisi';
        
        // Get basic stats for the page
        $totalRevisions = Revisi::count();
        
        $pendingRevisions = Revisi::where('status', 'revisi')->count();
        $inProgressRevisions = Revisi::where('status', 'dikerjakan')->count();

        return view('page.revisi.index', compact(
            'title',
            'totalRevisions',
            'pendingRevisions', 
            'inProgressRevisions'
        ));
    }

    /**
     * Show revision detail page
     */
    public function showDetail($uuid)
    {
        $revisi = Revisi::join('pesanan', 'pesanan.id_pesanan', '=', 'revisi.id_pesanan')
            ->where('revisi.uuid', $uuid)
            ->first();

        if (!$revisi) {
            return redirect('/revisi')->with('error', 'Revisi tidak ditemukan');
        }

        // Get available editors
        $availableEditors = Editor::all();

        // Filter revision-related messages
        $revisionMessages = $revisi->chatMessages->filter(function($message) {
            return stripos($message->message, 'revisi') !== false ||
                   stripos($message->message, 'revision') !== false;
        });

        $title = 'Detail Revisi - ' . $revisi->uuid;

        return view('page.revisi.detail', compact(
            'title',
            'revisi',
            'availableEditors',
            'revisionMessages'
        ));
    }

    /**
     * Show revision statistics page
     */
    public function showStatistics()
    {
        $title = 'Statistik Revisi';

        // Get comprehensive statistics
        $totalRevisionRequests = Revisi::count();

        $pendingRevisions = Revisi::where('status', 'revisi')->count();
        $inProgressRevisions = Revisi::where('status', 'dikerjakan')->count();

        $completedRevisions = Revisi::where('status', 'selesai')->count();

        // Most active editors in revisions
        $activeEditors = Editor::withCount(['revisi' => function($q) {
            $q->where('status', 'dikerjakan');
        }])->orderBy('revisi_count', 'desc')->take(10)->get();

        // Recent revision activities
        $recentRevisions = Revisi::orderBy('created_at', 'desc')->take(10)->get();

        return view('page.revisi.statistics', compact(
            'title',
            'totalRevisionRequests',
            'pendingRevisions',
            'inProgressRevisions', 
            'completedRevisions',
            'activeEditors',
            'recentRevisions'
        ));
    }
} 