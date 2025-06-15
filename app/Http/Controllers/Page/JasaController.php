<?php
namespace App\Http\Controllers\Page;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Jasa;
use App\Models\PaketJasa;
use App\Models\JasaImage;
use App\Http\Controllers\UtilityController;
class JasaController extends Controller
{
    public function showAll(Request $request){
        $dataShow = [
            'jasaData' => Jasa::select('uuid','kategori')->get(),
            'headerData' => UtilityController::getHeaderData(),
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
        ];
        return view('page.jasa.data',$dataShow);
    }
    public function showTambah(Request $request){
        $dataShow = [
            'headerData' => UtilityController::getHeaderData(),
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
        ];
        return view('page.jasa.tambah',$dataShow);
    }
    public function showEdit(Request $request, $uuid){
        $jasa = Jasa::where('uuid', $uuid)->first();
        
        if(is_null($jasa)){
            return redirect('/jasa')->with('error', 'Data Jasa tidak ditemukan');
        }
        
        $paketJasa = PaketJasa::where('id_jasa', $jasa->id_jasa)->first();
        $jasaImages = JasaImage::where('id_jasa', $jasa->id_jasa)->get();
        
        if(is_null($paketJasa)){
            return redirect('/jasa')->with('error', 'Data Paket Jasa tidak ditemukan');
        }
        
        $jasaData = [
            'uuid' => $jasa->uuid,
            'thumbnail_jasa' => $jasa->thumbnail_jasa,
            'kategori' => $jasa->kategori,
            'kelas_jasa' => $paketJasa->kelas_jasa,
            'deskripsi_paket_jasa' => $paketJasa->deskripsi_paket_jasa,
            'harga_paket_jasa' => $paketJasa->harga_paket_jasa,
            'waktu_pengerjaan' => $paketJasa->waktu_pengerjaan,
            'maksimal_revisi' => $paketJasa->maksimal_revisi,
            'fitur' => $paketJasa->fitur,
            'images' => $jasaImages
        ];
        
        $dataShow = [
            'headerData' => UtilityController::getHeaderData(),
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'jasa' => $jasaData,
        ];
        return view('page.jasa.edit',$dataShow);
    }
}
?>