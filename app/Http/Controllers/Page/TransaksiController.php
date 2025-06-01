<?php
namespace App\Http\Controllers\Page;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Http\Controllers\UtilityController;
use App\Models\Transaksi;
use App\Models\Pesanan;
use App\Models\MetodePembayaran;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    public function showAll(Request $request){
        $dataShow = [
            'headerData' => UtilityController::getHeaderData(),
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'transaksiData' => Transaksi::with(['toMetodePembayaran', 'toPesanan'])
                ->orderBy('created_at', 'desc')
                ->get(),
            'totalPending' => Transaksi::where('status', 'menunggu_konfirmasi')->count(),
            'totalCompleted' => Transaksi::where('status', 'lunas')->count(),
        ];
        return view('page.transaksi.data',$dataShow);
    }
    
    public function showDetail(Request $request, $orderId){
        $transaksi = Transaksi::with(['toMetodePembayaran', 'toPesanan'])
            ->where('order_id', $orderId)
            ->first();
            
        if (!$transaksi) {
            return redirect('/transaksi')->with('error', 'Data Transaksi tidak ditemukan');
        }
        
        // Get order and user information
        $pesanan = $transaksi->toPesanan;
        $user = null;
        if ($pesanan && $pesanan->toUser) {
            $user = $pesanan->toUser;
        }
        
        $dataShow = [
            'headerData' => UtilityController::getHeaderData(),
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'transaksi' => $transaksi,
            'pesanan' => $pesanan,
            'user' => $user,
            'metodePembayaran' => $transaksi->toMetodePembayaran,
            'isExpired' => Carbon::now()->isAfter($transaksi->expired_at),
            'timeSincePayment' => $transaksi->waktu_pembayaran ? Carbon::parse($transaksi->waktu_pembayaran)->diffForHumans() : null,
        ];
        
        return view('page.transaksi.detail', $dataShow);
    }

    public function showEdit(Request $request, $orderId){
        $transaksi = Transaksi::with(['toMetodePembayaran', 'toPesanan'])
            ->where('order_id', $orderId)
            ->first();
            
        if (!$transaksi) {
            return redirect('/transaksi')->with('error', 'Data Transaksi tidak ditemukan');
        }
        
        $dataShow = [
            'headerData' => UtilityController::getHeaderData(),
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'transaksi' => $transaksi,
            'pesanan' => $transaksi->toPesanan,
            'metodePembayaran' => MetodePembayaran::all(),
        ];
        
        return view('page.transaksi.edit', $dataShow);
    }
    
    public function showTambah(Request $request){
        $dataShow = [
            'headerData' => UtilityController::getHeaderData(),
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'metodePembayaran' => MetodePembayaran::all(),
            'pesanan' => Pesanan::where('status_pembayaran', 'belum_bayar')->get(),
        ];
        
        return view('page.transaksi.tambah', $dataShow);
    }
    
    public function showDashboard(Request $request){
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        // Get transaction statistics
        $stats = [
            'total_transactions' => Transaksi::count(),
            'pending_confirmation' => Transaksi::where('status', 'menunggu_konfirmasi')->count(),
            'confirmed_today' => Transaksi::where('status', 'lunas')
                ->whereDate('updated_at', $today)
                ->count(),
            'monthly_revenue' => Transaksi::where('status', 'lunas')
                ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                ->sum('jumlah'),
            'expired_transactions' => Transaksi::where('status', 'belum_bayar')
                ->where('expired_at', '<', Carbon::now())
                ->count()
        ];
        
        // Get recent transactions
        $recentTransactions = Transaksi::with(['toMetodePembayaran', 'toPesanan'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Get pending transactions
        $pendingTransactions = Transaksi::with(['toMetodePembayaran', 'toPesanan'])
            ->where('status', 'menunggu_konfirmasi')
            ->orderBy('waktu_pembayaran', 'asc')
            ->limit(5)
            ->get();
        
        $dataShow = [
            'headerData' => UtilityController::getHeaderData(),
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
            'pendingTransactions' => $pendingTransactions,
        ];
        
        return view('page.transaksi.dashboard', $dataShow);
    }
    
    public function showReports(Request $request){
        // Get filter parameters
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $status = $request->input('status', 'all');
        
        // Base query
        $query = Transaksi::with(['toMetodePembayaran', 'toPesanan']);
        
        // Apply filters
        if ($status != 'all') {
            $query->where('status', $status);
        }
        
        $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        // Get transactions
        $transactions = $query->orderBy('created_at', 'desc')->get();
        
        // Calculate summary stats
        $summary = [
            'total_count' => $transactions->count(),
            'total_amount' => $transactions->sum('jumlah'),
            'confirmed_count' => $transactions->where('status', 'lunas')->count(),
            'confirmed_amount' => $transactions->where('status', 'lunas')->sum('jumlah'),
            'pending_count' => $transactions->where('status', 'menunggu_konfirmasi')->count(),
            'pending_amount' => $transactions->where('status', 'menunggu_konfirmasi')->sum('jumlah'),
            'unpaid_count' => $transactions->where('status', 'belum_bayar')->count(),
            'unpaid_amount' => $transactions->where('status', 'belum_bayar')->sum('jumlah'),
        ];
        
        $dataShow = [
            'headerData' => UtilityController::getHeaderData(),
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'transactions' => $transactions,
            'summary' => $summary,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
        ];
        
        return view('page.transaksi.reports', $dataShow);
    }
}