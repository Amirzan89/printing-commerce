<?php
namespace App\Http\Controllers\Page;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\MetodePembayaran;
class MetodePembayaranController extends Controller
{
    public function showAll(Request $request){
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'metodePembayaranData' => MetodePembayaran::select('uuid','nama_metode_pembayaran')->get(),
        ];
        return view('page.metode-pembayaran.data',$dataShow);
    }
    public function showTambah(Request $request){
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
        ];
        return view('page.metode-pembayaran.tambah',$dataShow);
    }
    public function showEdit(Request $request, $uuid){
        $metodePembayaranData = MetodePembayaran::select('uuid','nama_metode_pembayaran', '')->whereRaw("BINARY uuid = ?",[$uuid])->first();
        if(is_null($metodePembayaranData)){
            return redirect('/metode-pembayaran')->with('error', 'Data Metode Pembayaran tidak ditemukan');
        }
        $dataShow = [
            'userAuth' => array_merge(MetodePembayaran::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'metodePembayaranData' => $metodePembayaranData,
        ];
        return view('page.metode-pembayaran.edit',$dataShow);
    }
}