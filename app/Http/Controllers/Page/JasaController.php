<?php
namespace App\Http\Controllers\Page;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Jasa;
use App\Models\PaketJasa;
class JasaController extends Controller
{
    public function showAll(Request $request){
        $dataShow = [
            'jasaData' => Jasa::select('nama_jasa', 'kategori')->get(),
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
        ];
        return view('page.jasa.data',$dataShow);
    }
    public function showTambah(Request $request){
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
        ];
        return view('page.jasa.tambah',$dataShow);
    }
    public function showEdit(Request $request, $uuid){
        $jasaData = PaketJasa::select('jasa.uuid', 'jasa.nama_jasa', 'auth.email', 'auth.role')->join('jasa', 'jasa.id_jasa', '=', 'jasa.id_jasa')->get();
        if(is_null($jasaData)){
            return redirect('/jasa')->with('error', 'Data Jasa tidak ditemukan');
        }
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'jasaData' => $jasaData,
        ];
        return view('page.jasa.edit',$dataShow);
    }
}
?>