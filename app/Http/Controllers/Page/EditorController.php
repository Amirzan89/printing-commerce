<?php
namespace App\Http\Controllers\Page;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\UtilityController;
use App\Models\Admin;
use App\Models\Jasa;
class EditorController extends Controller
{
    public function showAll(Request $request){
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'jasaData' => Jasa::select('')->get(),
            'headerData' => UtilityController::getHeaderData(),
        ];
        return view('page.jasa.data',$dataShow);
    }
    public function showTambah(Request $request){
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'headerData' => UtilityController::getHeaderData(),
        ];
        return view('page.jasa.tambah',$dataShow);
    }
    public function showEdit(Request $request, $uuid){
        $jasaData = jasa::select('uuid','nama_lengkap', '')->whereNotIn('role', ['jasa'])->whereRaw("BINARY uuid = ?",[$uuid])->first();
        if(is_null($jasaData)){
            return redirect('/jasa')->with('error', 'Data jasa tidak ditemukan');
        }
        $dataShow = [
            'userAuth' => array_merge(jasa::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'jasaData' => $jasaData,
            'headerData' => UtilityController::getHeaderData(),
        ];
        return view('page.jasa.edit',$dataShow);
    }
}
?>