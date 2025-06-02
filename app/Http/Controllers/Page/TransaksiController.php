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
            'transaksiData' => Transaksi::select('order_id', 'users.nama_user', 'metode_pembayaran.nama_metode_pembayaran', 'transaksi.status')
                ->join('pesanan', 'transaksi.id_pesanan', '=', 'pesanan.id_pesanan')
                ->join('users', 'pesanan.id_user', '=', 'users.id_user')
                ->join('metode_pembayaran', 'transaksi.id_metode_pembayaran', '=', 'metode_pembayaran.id_metode_pembayaran')
                ->orderBy('transaksi.created_at', 'desc')
                ->get()
                ->map(function($item) {
                    $item->status = ucwords(str_replace('_', ' ', $item->status));
                    return $item;
                }),
            'totalPending' => Transaksi::where('status', 'menunggu_konfirmasi')->count(),
            'totalCompleted' => Transaksi::where('status', 'lunas')->count(),
        ];
        return view('page.transaksi.data',$dataShow);
    }
    public function showDetail(Request $request, $orderId){
        $transaksi = Transaksi::select('order_id', 'transaksi.status', 'bukti_pembayaran', 'waktu_pembayaran', 'expired_at', 'transaksi.created_at', 'transaksi.updated_at', 'users.nama_user', 'metode_pembayaran.nama_metode_pembayaran')
            ->where('order_id', $orderId)
            ->join('pesanan', 'transaksi.id_pesanan', '=', 'pesanan.id_pesanan')
            ->join('users', 'pesanan.id_user', '=', 'users.id_user')
            ->join('metode_pembayaran', 'transaksi.id_metode_pembayaran', '=', 'metode_pembayaran.id_metode_pembayaran')
            ->first();
        // echo json_encode($transaksi);
        // exit();
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
            'transaksiData' => $transaksi,
        ];
        // echo json_encode($dataShow);
        // exit();
        return view('page.transaksi.detail', $dataShow);
    }

    // public function showTambah(Request $request){
    //     $dataShow = [
    //         'headerData' => UtilityController::getHeaderData(),
    //         'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
    //         'metodePembayaran' => MetodePembayaran::all(),
    //         'pesanan' => Pesanan::where('status_pembayaran', 'belum_bayar')->get(),
    //     ];
        
    //     return view('page.transaksi.tambah', $dataShow);
    // }
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