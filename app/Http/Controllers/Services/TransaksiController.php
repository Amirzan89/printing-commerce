<?php

namespace App\Http\Controllers\Services;
use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use DataTables;
use Excel;
class TransaksiController extends Controller
{
    /**
     * Display a listing of transactions
     */
    public function index()
    {
        return view('admin.transaksi.index');
    }

    /**
     * Get transactions data for DataTables
     */
    public function getTransaksiData(Request $request)
    {
        $query = Transaksi::with(['metodePembayaran', 'pesanan.user'])
            ->select('transaksi.*');

        return DataTables::of($query)
            ->addColumn('customer_name', function ($transaksi) {
                return $transaksi->pesanan->user->name ?? 'N/A';
            })
            ->addColumn('total_amount', function ($transaksi) {
                return 'Rp ' . number_format($transaksi->jumlah, 0, ',', '.');
            })
            ->addColumn('status_badge', function ($transaksi) {
                $badges = [
                    'belum_bayar' => '<span class="badge bg-warning">Belum Bayar</span>',
                    'menunggu_konfirmasi' => '<span class="badge bg-info">Menunggu Konfirmasi</span>',
                    'lunas' => '<span class="badge bg-success">Lunas</span>'
                ];
                return $badges[$transaksi->status] ?? '';
            })
            ->addColumn('payment_date', function ($transaksi) {
                return $transaksi->waktu_pembayaran ? Carbon::parse($transaksi->waktu_pembayaran)->format('d M Y H:i') : '-';
            })
            ->addColumn('actions', function ($transaksi) {
                return view('admin.transaksi.actions', compact('transaksi'))->render();
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show transaction details
     */
    public function show($id)
    {
        $transaksi = Transaksi::with(['metodePembayaran', 'pesanan.user'])
            ->findOrFail($id);

        return view('admin.transaksi.show', compact('transaksi'));
    }

    /**
     * Show transaction edit form
     */
    public function edit($id)
    {
        $transaksi = Transaksi::with(['metodePembayaran', 'pesanan.user'])
            ->findOrFail($id);

        return view('admin.transaksi.edit', compact('transaksi'));
    }

    /**
     * Update transaction status and details
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:belum_bayar,menunggu_konfirmasi,lunas',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $transaksi = Transaksi::findOrFail($id);
        
        // Record previous status for notification
        $oldStatus = $transaksi->status;

        $transaksi->status = $request->status;
        $transaksi->admin_notes = $request->admin_notes;
        
        // Update payment time if status changed to lunas
        if ($request->status === 'lunas' && $oldStatus !== 'lunas') {
            $transaksi->waktu_pembayaran = Carbon::now();
        }

        $transaksi->save();

        // Send notification to user about status change
        if ($oldStatus !== $request->status) {
            $this->sendStatusChangeNotification($transaksi);
        }

        return redirect()
            ->route('admin.transaksi.show', $id)
            ->with('success', 'Transaksi berhasil diperbarui');
    }

    /**
     * View payment proof
     */
    public function viewBuktiPembayaran($id)
    {
        $transaksi = Transaksi::findOrFail($id);
        
        if (!$transaksi->bukti_pembayaran) {
            return back()->with('error', 'Bukti pembayaran tidak tersedia');
        }

        return view('admin.transaksi.bukti-pembayaran', compact('transaksi'));
    }

    /**
     * Export transactions to Excel
     */
    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:belum_bayar,menunggu_konfirmasi,lunas'
        ]);

        $query = Transaksi::with(['metodePembayaran', 'pesanan.user']);

        // Apply filters
        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $transactions = $query->get();

        // Generate Excel file
        return Excel::download(new TransaksiExport($transactions), 'transaksi-report.xlsx');
    }

    /**
     * Get transaction statistics
     */
    public function getStatistics()
    {
        $today = Carbon::today();
        
        $stats = [
            'total_today' => Transaksi::whereDate('created_at', $today)->count(),
            'pending_confirmation' => Transaksi::where('status', 'menunggu_konfirmasi')->count(),
            'completed_today' => Transaksi::where('status', 'lunas')
                ->whereDate('waktu_pembayaran', $today)
                ->count(),
            'total_amount_today' => Transaksi::where('status', 'lunas')
                ->whereDate('waktu_pembayaran', $today)
                ->sum('jumlah')
        ];

        return response()->json($stats);
    }

    /**
     * Send notification about status change
     */
    private function sendStatusChangeNotification($transaksi)
    {
        $user = $transaksi->pesanan->user;
        
        $statusMessages = [
            'belum_bayar' => 'Status pembayaran Anda telah direset ke Belum Bayar',
            'menunggu_konfirmasi' => 'Pembayaran Anda sedang dalam proses konfirmasi',
            'lunas' => 'Pembayaran Anda telah dikonfirmasi dan lunas'
        ];

        $message = $statusMessages[$transaksi->status] ?? 'Status transaksi Anda telah diperbarui';

        // Send notification (implement based on your notification system)
        // Example: $user->notify(new TransactionStatusChanged($transaksi, $message));
    }
} 