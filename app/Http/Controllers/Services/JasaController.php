<?php
namespace App\Http\Controllers\Services;
use App\Http\Controllers\Controller;
class JasaController extends Controller
{
    public function get(Request $request){
        $ins = User::insert([
            'uuid' =>  Str::uuid(),
            'nama_lengkap' => $request->input('nama_lengkap'),
            'no_telpon' => $request->input('no_telpon'),
            'jenis_kelamin' => $request->input('jenis_kelamin'),
            'role'=>$request->input('role'),
            'email' => $request->input('email_admin'),
            'password' => Hash::make($request->input('password')),
            'foto' => $request->hasFile('foto') ? $fotoName : '',
            'verifikasi' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        if(!$ins){
            return response()->json(['status'=>'error','message'=>'Gagal menambahkan data Admin'], 500);
        }
        return response()->json(['status'=>'success','message'=>'Data Admin berhasil ditambahkan']);
    }
}