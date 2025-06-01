<?php
namespace App\Http\Controllers\Page;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\UtilityController;
use App\Models\Admin;
use App\Models\Pesanan;
class PesananController extends Controller
{
    public function showAll(Request $request){
        $status = $request->query('status', 'menunggu');
        $validStatuses = ['menunggu', 'proses', 'dikerjakan', 'revisi', 'selesai', 'dibatalkan'];
        if (!in_array($status, $validStatuses)) {
            $status = 'menunggu';
        }
        if (!$request->has('status')) {
            return redirect('/pesanan?status='.$status);
        }
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'dataPesanan' => Pesanan::select('pesanan.uuid', 'nama_jasa', 'status')->join('jasa', 'jasa.id_jasa', '=', 'pesanan.id_jasa')->where('status', $status)->get(),
            'headerData' => UtilityController::getHeaderData(),
            'currentStatus' => $status,
        ];
        
        return view('page.pesanan.data',$dataShow);
    }
    public function showTambah(Request $request){
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'headerData' => UtilityController::getHeaderData(),
        ];
        return view('page.pesanan.tambah',$dataShow);
    }
    public function showEdit(Request $request, $uuid){
        $pesananData = Pesanan::select('uuid','nama_jasa', 'status')->whereNotIn('role', ['pesanan'])->whereRaw("BINARY uuid = ?",[$uuid])->first();
        if(is_null($pesananData)){
            return redirect('/pesanan')->with('error', 'Data Pesanan tidak ditemukan');
        }
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'pesananData' => $pesananData,
            'headerData' => UtilityController::getHeaderData(),
        ];
        return view('page.pesanan.edit',$dataShow);
    }
}
?>