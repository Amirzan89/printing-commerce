<?php
namespace App\Http\Controllers\Page;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Services\EventController;
use App\Http\Controllers\Services\DisiController;
use App\Http\Controllers\Services\EmotalController;
use App\Http\Controllers\Services\NutrisiController;
use App\Http\Controllers\Services\PengasuhanController;
use App\Http\Controllers\Services\KonsultasiController;
use App\Http\Controllers\Services\ArtikelController;
use Illuminate\Http\Request;
use App\Models\Admin;
use Carbon\Carbon;
use DateTime;
class AdminController extends Controller
{
    private function changeMonth($inpDate){
        $inpDate = json_decode($inpDate, true);
        $monthTranslations = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
        // Check if it's an associative array (single data)
        if (array_keys($inpDate) !== range(0, count($inpDate) - 1)) {
            foreach (['tanggal_lahir', 'tanggal_awal', 'tanggal_akhir'] as $dateField) {
                if (isset($inpDate[$dateField]) && $inpDate[$dateField] !== null) {
                    $date = new DateTime($inpDate[$dateField]);
                    $monthNumber = $date->format('m');
                    $indonesianMonth = $monthTranslations[$monthNumber];
                    $formattedDate = $date->format('d') . ' ' . $indonesianMonth . ' ' . $date->format('Y');
                    $inpDate[$dateField] = $formattedDate;
                }
            }
        } else {
            $processedData = [];
            foreach ($inpDate as $inpDateRow) {
                $processedRow = $inpDateRow;
                foreach (['tanggal', 'tanggal_awal', 'tanggal_akhir'] as $dateField) {
                    if (isset($processedRow[$dateField]) && $processedRow[$dateField] !== null) {
                        $date = new DateTime($processedRow[$dateField]);
                        $monthNumber = $date->format('m');
                        $indonesianMonth = $monthTranslations[$monthNumber];
                        $formattedDate = $date->format('d') . ' ' . $indonesianMonth . ' ' . $date->format('Y');
                        $processedRow[$dateField] = $formattedDate;
                    }
                }
                $processedData[] = $processedRow;
            }
            $inpDate = $processedData;
        }
        return $inpDate;
    }
    public function showDashboard(Request $request){
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
        ];
        return view('page.dashboard',$dataShow);
    }
    // public function showRiwayat(Request $request){
    //     $disi = app()->make(DisiController::class)->dataCacheFile(null, 'get_riwayat', 3, ['uuid', 'judul', 'created_at']);
    //     $emotal = app()->make(EmotalController::class)->dataCacheFile(null, 'get_riwayat', 3, ['uuid', 'judul', 'created_at']);
    //     $nutrisi = app()->make(NutrisiController::class)->dataCacheFile(null, 'get_riwayat', 3, ['uuid', 'judul', 'created_at']);
    //     $pengasuhan = app()->make(PengasuhanController::class)->dataCacheFile(null, 'get_riwayat', 3, ['uuid', 'judul', 'created_at']);
    //     $artikel = app()->make(ArtikelController::class)->dataCacheFile(null, 'get_riwayat', 3, ['uuid', 'judul', 'created_at']);
    //     $dataAll = array_merge($disi, $emotal, $nutrisi, $pengasuhan, $artikel);
    //     usort($dataAll, function($a, $b) {
    //         return strtotime($b['created_at']) - strtotime($a['created_at']);
    //     });
    //     $dataAll = array_map(function($item){
    //         $item['created_at'] = Carbon::parse($item['created_at'])->translatedFormat('l, d F Y');
    //         return $item;
    //     }, $dataAll ?? []);
    //     return view('page.riwayat',['userAuth' => $request->input('admin_auth'), 'dataRiwayat' => $dataAll]);
    // }
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