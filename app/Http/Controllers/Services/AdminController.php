<?php
namespace App\Http\Controllers\Services;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Auth;
use App\Models\Admin;
class AdminController extends Controller
{
    public function createAdmin(Request $rt){
        if(Auth::select("email")->whereRaw("BINARY email = ?",[$rt->input('email')])->limit(1)->exists()){
            return response()->json(['status'=>'error','message'=>'Email sudah digunakan'],400);
        }
        if($rt->hasFile('foto')){
            $fi = $rt->file('foto');
            if(!($fi->isValid() && in_array($fi->extension(), ['jpeg', 'png', 'jpg']))){
                return response()->json(['status'=>'error','message'=>'Format Foto tidak valid. Gunakan format jpeg, png, jpg'], 400);
            }
            $fh = $fi->hashName();
            $fi->move(public_path('assets3/img/admin/'), $fh);
        }
        $idAuth = Auth::insertGetId([
            'email' => $rt->input('email'),
            'password' => Hash::make($rt->input('password')),
            'role'=>$rt->input('role'),
        ]);
        $ins = Admin::insert([
            'uuid' =>  Str::uuid(),
            'nama_admin' => $rt->input('nama_admin'),
            'foto' => $rt->hasFile('foto') ? $fh : '',
            'id_auth' => $idAuth,
        ]);
        if(!$ins){
            return response()->json(['status'=>'error','message'=>'Gagal menambahkan data Admin'], 500);
        }
        return response()->json(['status'=>'success','message'=>'Data Admin berhasil ditambahkan']);
    }
    public function updateAdmin(Request $rt){
        $admin = Admin::select('auth.id_auth', 'auth.password', 'auth.role', 'admin.foto')->whereRaw("BINARY email = ?",[$rt->input('email_old')])->join('auth', 'admin.id_auth', '=', 'auth.id_auth')->first();
        if(is_null($admin)){
            return response()->json(['status'=>'error', 'message'=>'Data Admin tidak ditemukan'], 404);
        }
        if(!is_null($rt->input('email') || !empty($rt->input('email'))) && $rt->input('email') != $rt->input('email_old') && Auth::whereRaw("BINARY email = ?",[$rt->input('email')])->exists()){
            return response()->json(['status' => 'error', 'message' => 'Email sudah digunakan'], 400);
        }
        if(!is_null($rt->input('role')) && !empty($rt->input('role')) && in_array($rt->input('role'), ['super_admin', 'admin_chat', 'admin_pemesanan'])){
            return response()->json(['status' => 'error', 'message' => 'Invalid Role'], 400);
        }
        if($rt->hasFile('foto')){
            $fi = $rt->file('foto');
            if(!($fi->isValid() && in_array($fi->extension(), ['jpeg', 'png', 'jpg']))){
                return response()->json(['status'=>'error','message'=>'Format Foto tidak valid. Gunakan format jpeg, png, jpg'], 400);
            }
            $ftd = public_path('assets3/img/admin/') . $admin['foto'];
            if (file_exists($ftd) && !is_dir($ftd)) {
                unlink($ftd);
            }
            $fh = $fi->hashName();
            $fi->move(public_path('assets3/img/admin/'), $fh);
        }
        $uT = Auth::where('id_auth', $admin['id_auth'])->update([
            'email' => (empty($rt->input('email')) || is_null($rt->input('email'))) ? $rt->input('email_old') : $rt->input('email'),
            'password' => (empty($rt->input('password')) || is_null($rt->input('password'))) ? $admin['password']: Hash::make($rt->input('password')),
            'role' => (empty($rt->input('role')) || is_null($rt->input('role'))) ? $admin['role'] : $rt->input('role'),
        ]);
        $uA = Admin::where('id_auth', $admin['id_auth'])->update([
            'nama_admin'=>$rt->input('nama_admin'),
            'foto' => $rt->hasFile('foto') ? $fh : $admin['foto'],
        ]);
        if(!$uT || !$uA){
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui data Admin'], 500);
        }
        return response()->json(['status'=>'success','message'=>'Data Admin berhasil diperbarui']);
    }
    public function updateProfile(Request $rt){
        $profile = Admin::select('auth.id_auth', 'auth.password', 'admin.foto')->where('auth.id_auth',$rt->user()['id_auth'])->join('auth', 'admin.id_auth', '=', 'auth.id_auth')->first();
        if(is_null($profile)){
            return response()->json(['status' => 'error', 'message' => 'Admin tidak ditemukan'], 400);
        }
        if(!is_null($rt->input('email') || !empty($rt->input('email'))) && $rt->input('email') != $rt->user()['email'] && Admin::whereRaw("BINARY email = ?",[$rt->input('email')])->exists()){
            return response()->json(['status' => 'error', 'message' => 'Email sudah digunakan'], 400);
        }
        if($rt->hasFile('foto')){
            $fi = $rt->file('foto');
            if(!($fi->isValid() && in_array($fi->extension(), ['jpeg', 'png', 'jpg']))){
                return response()->json(['status'=>'error','message'=>'Format Foto tidak valid. Gunakan format jpeg, png, jpg'], 400);
            }
            $ftd = public_path('assets3/img/admin/') . $profile['foto'];
            if (file_exists($ftd) && !is_dir($ftd)) {
                unlink($ftd);
            }
            $fh = $fi->hashName();
            $fi->move(public_path('assets3/img/admin/'), $fh);
        }
        $updatedAuthProfile = Auth::where('id_auth',$rt->user()['id_auth'])->update([
            'email'=>(is_null($rt->input('email')) || empty($rt->input('email'))) ? $rt->user()['email'] : $rt->input('email'),
        ]);
        $updateProfile = Admin::where('id_auth',$rt->user()['id_auth'])->update([
            'nama_admin'=>$rt->input('nama_admin'),
            'foto' => $rt->hasFile('foto') ? $fh : $profile['foto'],
        ]);
        if(!$updatedAuthProfile || !$updateProfile){
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui data Admin'], 500);
        }
        $rt->session()->regenerate();
        return response()->json(['status'=>'success','message'=>'Profile Anda berhasil di perbarui']);
    }
    public function updatePassword(Request $rt){
        $passOld = $rt->input('password_old');
        $pass = $rt->input('password');
        $passConfirm = $rt->input('password_confirm');
        if($pass !== $passConfirm){
            return response()->json(['status'=>'error','message'=>'Password Harus Sama'],400);
        }
        $profile = Auth::select('password')->where('id_auth',$rt->user()['id_auth'])->first();
        if(is_null($profile)){
            return response()->json(['status' => 'error', 'message' => 'Admin tidak ditemukan'], 400);
        }
        if(!password_verify($passOld,$profile->password)){
            return response()->json(['status'=>'error','message'=>'Password salah'],400);
        }
        $updatePassword = Auth::where('id_auth',$rt->user()['id_auth'])->update([
            'password' => Hash::make($pass),
        ]);
        if(!$updatePassword){
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui password admin'], 500);
        }
        return response()->json(['status' =>'success','message'=>'Password Admin berhasil di perbarui']);
    }
    public function deleteAdmin(Request $rt){
        $admin = Admin::select('foto')->where('uuid',$rt->input('uuid'))->first();
        if(is_null($admin)){
            return response()->json(['status' => 'error', 'message' => 'Data Admin tidak ditemukan'], 404);
        }
        $ftd = public_path('assets3/img/admin/') . $admin['foto'];
        if (file_exists($ftd) && !is_dir($ftd)) {
            unlink($ftd);
        }
        if(!Admin::where('uuid',$rt->input('uuid'))->delete()){
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus data Admin'], 500);
        }
        return response()->json(['status' => 'success', 'message' => 'Data Admin berhasil dihapus']);
    }
    public function logout(Request $rt){
        $rt->user();
        return response()->json(['status' => 'success', 'message' => '']);
    }
}
?>