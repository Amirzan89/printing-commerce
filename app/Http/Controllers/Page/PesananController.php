<?php
namespace App\Http\Controllers\Page;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin;
use App\Models\Pesanan;
class PesananController extends Controller
{
    public function showAll(Request $request){
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'mepeData' => Pesanan::get(),
        ];
        return view('page.pesanan.data',$dataShow);
    }
    public function showTambah(Request $request){
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
        ];
        return view('page.pesanan.tambah',$dataShow);
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
        return view('page.pesanan.edit',$dataShow);
    }
}
?>