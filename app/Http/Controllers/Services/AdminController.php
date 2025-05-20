<?php
namespace App\Http\Controllers\Services;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Auth;
use App\Models\Admin;
use App\Models\RefreshToken;
use Carbon\Carbon;
class AdminController extends Controller
{
    public function tambahAdmin(Request $request){
        //check email
        if (Auth::select("email")->whereRaw("BINARY email = ?",[$request->input('email_admin')])->limit(1)->exists()){
            return response()->json(['status'=>'error','message'=>'Email sudah digunakan'],400);
        }
        //process file foto
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            if(!($file->isValid() && in_array($file->extension(), ['jpeg', 'png', 'jpg']))){
                return response()->json(['status'=>'error','message'=>'Format Foto tidak valid. Gunakan format jpeg, png, jpg'], 400);
            }
            
            $fotoName = $file->hashName();
            // $fotoName = $file->hashName();
            // $fileData = Crypt::encrypt(file_get_contents($file));
            // Storage::disk('admin')->put('foto/' . $fotoName, $fileData);
        }
        $idAuth = Auth::insertGetId([
            'nama_admin' => $request->input('nama_admin'),
            'email' => $request->input('email_admin'),
            'password' => Hash::make($request->input('password')),
            'role'=>$request->input('role'),
        ]);
        $ins = Admin::insert([
            'uuid' =>  Str::uuid(),
            'foto' => $request->hasFile('foto') ? $fotoName : '',
            'auth' => $idAuth,
        ]);
        if(!$ins){
            return response()->json(['status'=>'error','message'=>'Gagal menambahkan data Admin'], 500);
        }
        return response()->json(['status'=>'success','message'=>'Data Admin berhasil ditambahkan']);
    }
    public function editAdmin(Request $request){
        //check data admin
        $admin = Admin::select('password','foto')->whereRaw("BINARY email = ?",[$request->input('email_admin_lama')])->first();
        if (is_null($admin)) {
            return response()->json(['status' => 'error', 'message' => 'Data Admin tidak ditemukan'], 404);
        }
        //check email on table user
        if (Admin::select('email')->whereRaw("BINARY email = ?",[$request->input('email_admin')])->first() && $request->input('email_admin') != $request->input('email_admin_lama')) {
            return response()->json(['status' => 'error', 'message' => 'Email sudah digunakan'], 400);
        }
        //process file foto
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            if(!($file->isValid() && in_array($file->extension(), ['jpeg', 'png', 'jpg']))){
                return response()->json(['status'=>'error','message'=>'Format Foto tidak valid. Gunakan format jpeg, png, jpg'], 400);
            }
            $destinationPath = storage_path('app/admin/');
            $fileToDelete = $destinationPath . $admin['foto'];
            if (file_exists($fileToDelete) && !is_dir($fileToDelete)) {
                unlink($fileToDelete);
            }
            Storage::disk('admin')->delete('foto/'.$admin['foto']);
            $fotoName = $file->hashName();
            $fileData = Crypt::encrypt(file_get_contents($file));
            Storage::disk('admin')->put('foto/' . $fotoName, $fileData);
        }
        //update admin
        $updatedAdmin = Admin::whereRaw("BINARY email = ?",[$request->input('email_admin_lama')])->update([
            'nama_admin'=>$request->input('nama_admin'),
            'jenis_kelamin'=>$request->input('jenis_kelamin'),
            'no_telpon'=>$request->input('no_telpon'),
            'role'=>$request->input('role'),
            'email'=> (empty($request->input('email_admin')) || is_null($request->input('email_admin'))) ? $request->input('email_admin_lama') : $request->input('email_admin'),
            'password'=> (empty($request->input('password')) || is_null($request->input('password'))) ? $admin['password']: Hash::make($request->input('password')),
            'foto' => $request->hasFile('foto') ? $fotoName : $admin['foto'],
            'verifikasi'=>true,
            'updated_at' => Carbon::now(),
        ]);
        if (!$updatedAdmin) {
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui data Admin'], 500);
        }
        return response()->json(['status'=>'success','message'=>'Data Admin berhasil diperbarui']);
    }
    public function updateProfile(Request $request){
        //check email
        $user = Admin::select('email','foto')->whereRaw("BINARY email = ?",[$request->input('user_auth')['email']])->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Admin tidak ditemukan'], 400);
        }
        //check email on table user
        if(!is_null($request->input('email_new') || !empty($request->input('email_new'))) &&Admin::select('role')->whereRaw("BINARY email = ?",[$request->input('email_new')])->first() && $request->input('email_new') != $request->input('user_auth')['email']){
            return response()->json(['status' => 'error', 'message' => 'Email sudah digunakan'], 400);
        }
        //process file foto
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            if(!($file->isValid() && in_array($file->extension(), ['jpeg', 'png', 'jpg']))){
                return response()->json(['status'=>'error','message'=>'Format Foto tidak valid. Gunakan format jpeg, png, jpg'], 400);
            }
            $destinationPath = storage_path('app/admin/');
            $fileToDelete = $destinationPath . $user['foto'];
            if (file_exists($fileToDelete) && !is_dir($fileToDelete)) {
                unlink($fileToDelete);
            }
            Storage::disk('admin')->delete('foto/'.$user['foto']);
            $fotoName = $file->hashName();
            $fileData = Crypt::encrypt(file_get_contents($file));
            Storage::disk('admin')->put('foto/' . $fotoName, $fileData);
        }
        //update profile
        $updateProfile = Admin::whereRaw("BINARY email = ?",[$request->input('user_auth')['email']])->update([
            'email'=> (empty($request->input('email_new')) || is_null($request->input('email_new'))) ? $request->input('user_auth')['email'] : $request->input('email_new'),
            'nama_admin' => $request->input('nama_admin'),
            'jenis_kelamin' => $request->input('jenis_kelamin'),
            'no_telpon' => $request->input('no_telpon'),
            'foto' => $request->hasFile('foto') ? $fotoName : $user['foto'],
            'updated_at'=> Carbon::now()
        ]);
        if (!$updateProfile) {
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui Profile'], 500);
        }
        //update cookie
        $jwtController = new JWTController();
        $email = $request->has('email_new') ? $request->input('email_new') : $request->input('user_auth')['email'];
        $data = $jwtController->createJWTWebsite($email,new RefreshToken());
        if(is_null($data)){
            return response()->json(['status'=>'error','message'=>'create token error'],500);
        }else{
            if($data['status'] == 'error'){
                return response()->json(['status'=>'error','message'=>$data['message']],400);
            }else{
                $data1 = ['email'=>$email,'number'=>$data['number']];
                $encoded = base64_encode(json_encode($data1));
                return response()->json(['status'=>'success','message'=>'Profile Anda berhasil di perbarui'])
                ->cookie('token1',$encoded,time()+intval(env('JWT_ACCESS_TOKEN_EXPIRED')))
                ->cookie('token2',$data['data']['token'],time() + intval(env('JWT_ACCESS_TOKEN_EXPIRED')))
                ->cookie('token3',$data['data']['refresh'],time() + intval(env('JWT_REFRESH_TOKEN_EXPIRED')));
            }
        }
    }
    //update from profile
    public function updatePassword(Request $request){
        $passOld = $request->input('password_old');
        $pass = $request->input('password');
        $passConfirm = $request->input('password_confirm');
        if($pass !== $passConfirm){
            return response()->json(['status'=>'error','message'=>'Password Harus Sama'],400);
        }
        //check email
        $user = Admin::select('password')->whereRaw("BINARY email = ?",[$request->input('user_auth')['email']])->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Admin tidak ditemukan'], 400);
        }
        if(!password_verify($passOld,$user->password)){
            return response()->json(['status'=>'error','message'=>'Password salah'],400);
        }
        //update password
        $updatePassword = Admin::whereRaw("BINARY email = ?",[$request->input('user_auth')['email']])->update([
            'password'=>Hash::make($pass),
            'updated_at'=> Carbon::now()
        ]);
        if (!$updatePassword) {
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui password admin'], 500);
        }
        return response()->json(['status' =>'success','message'=>'Password Admin berhasil di perbarui']);
    }
    public function hapusAdmin(Request $request){
        //check data Admin
        $admin = Admin::select('foto')->where('uuid',$request->input('uuid'))->first();
        if (is_null($admin)) {
            return response()->json(['status' => 'error', 'message' => 'Data Admin tidak ditemukan'], 404);
        }
        //delete foto
        $destinationPath = storage_path('app/admin/');
        $fileToDelete = $destinationPath . $admin->foto;
        if (file_exists($fileToDelete) && !is_dir($fileToDelete)) {
            unlink($fileToDelete);
        }
        Storage::disk('admin')->delete('foto/'.$admin->foto);

        if (!Admin::where('uuid',$request->input('uuid'))->delete()) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus data Admin'], 500);
        }
        return response()->json(['status' => 'success', 'message' => 'Data Admin berhasil dihapus']);
    }
    public function logout(Request $request){
        $email = $request->input('email');
        $number = $request->input('number');
        if(empty($email) || is_null($email)){
            return response()->json(['status'=>'error','message'=>'email empty'],400);
        }else if(empty($number) || is_null($number)){
            return response()->json(['status'=>'error','message'=>'token empty'],400);
        }else{
            $deleted = $jwtController->deleteRefreshToken($email,$number, 'website');
            if($deleted['status'] == 'error'){
                return redirect("/login")->withCookies([Cookie::forget('token1'),Cookie::forget('token2'), Cookie::forget('token3')]);
            }else{
                return redirect("/login")->withCookies([Cookie::forget('token1'),Cookie::forget('token2'), Cookie::forget('token3')]);
            }
        }
    }
}
?>