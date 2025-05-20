<?php
namespace App\Http\Controllers\Page;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
class AdminController extends Controller
{
    public function showDashboard(Request $request){
        $dataShow = [
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