<?php
namespace App\Http\Controllers\Page;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Pesanan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class AdminController extends Controller
{
    public function showDashboard(Request $request){
        $monthlyTotals = Pesanan::where('status', 'selesai')
            ->whereYear('updated_at', Carbon::now()->year)
            ->select(
                DB::raw('MONTH(updated_at) as month'),
                DB::raw('YEAR(updated_at) as year'),
                DB::raw('SUM(total_harga) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $salesData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthData = $monthlyTotals->first(function($item) use ($month) {
                return $item->month == $month;
            });
            $salesData[] = $monthData ? $monthData->total : 0;
        }
        $dataShow = [
            'total_pesanan' => Pesanan::where('status', 'selesai')->count(),
            'list_pesanan' => Pesanan::where('status', 'selesai')
                ->join('users', 'pesanan.id_user', '=', 'users.id_user')
                ->join('jasa', 'pesanan.id_jasa', '=', 'jasa.id_jasa')
                ->select(
                    'users.nama_user as pelanggan',
                    DB::raw("CONCAT(
                        DATE_FORMAT(pesanan.updated_at, '%d '),
                        CASE DATE_FORMAT(pesanan.updated_at, '%M')
                            WHEN 'January' THEN 'Januari'
                            WHEN 'February' THEN 'Februari'
                            WHEN 'March' THEN 'Maret'
                            WHEN 'April' THEN 'April'
                            WHEN 'May' THEN 'Mei'
                            WHEN 'June' THEN 'Juni'
                            WHEN 'July' THEN 'Juli'
                            WHEN 'August' THEN 'Agustus'
                            WHEN 'September' THEN 'September'
                            WHEN 'October' THEN 'Oktober'
                            WHEN 'November' THEN 'November'
                            WHEN 'December' THEN 'Desember'
                        END,
                        DATE_FORMAT(pesanan.updated_at, ' %Y')
                    ) as selesai_pada"),
                    'jasa.kategori as jenis_jasa',
                    'pesanan.total_harga as pendapatan'
                )
                ->orderBy('pesanan.updated_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($item) {
                    return [
                        'pelanggan' => $item->pelanggan,
                        'selesai_pada' => $item->selesai_pada,
                        'jenis_jasa' => ucfirst($item->jenis_jasa),
                        'pendapatan' => 'Rp ' . number_format($item->pendapatan, 0, ',', '.')
                    ];
                }),
            'monthly_sales' => $salesData,
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
        ];
        return view('page.dashboard',$dataShow);
    }
    public function showProfile(Request $request){
        $dataShow = [
            'userAuth' => $request->user(),
        ];
        return view('page.profile',$dataShow);
    }
    //only admin
    public function showAll(Request $request){
        $adminData = Admin::select('admin.uuid', 'admin.nama_admin', 'auth.email', 'auth.role')->join('auth', 'admin.id_auth', '=', 'auth.id_auth')->whereNotIn('auth.role', ['admin', 'super admin'])->whereNotIn('auth.id_auth', $request->user()['id_auth'])->get();
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'adminData' => $adminData ?? [],
        ];
        return view('page.admin.data',$dataShow);
    }
    public function showTambah(Request $request){
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
        ];
        return view('page.admin.tambah',$dataShow);
    }
    public function showEdit(Request $request, $uuid){
        $adminData = Admin::select('uuid','nama_lengkap', 'jenis_kelamin', 'no_telpon','role', 'email', 'foto')->whereNotIn('role', ['admin'])->whereRaw("BINARY uuid = ?",[$uuid])->first();
        if(is_null($adminData)){
            return redirect('/admin')->with('error', 'Data Admin tidak ditemukan');
        }
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'adminData' => $adminData,
        ];
        return view('page.admin.edit',$dataShow);
    }
}