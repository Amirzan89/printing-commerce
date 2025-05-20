<?php
namespace App\Http\Controllers\Services;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Jasa;
use App\Models\PaketJasa;
class JasaController extends Controller
{
    public function createJasa(Request $request){
        $idJasa = Jasa::insertGetId([
            'nama_jasa' => $request->input('nama_jasa'),
            'thumbnail_jasa' => $request->input('thumbnail_jasa'),
            'kategori' => $request->input('kategori'),
        ]);
        $ins = Jasa::insert([
            'nama_paket_jasa' => $request->input('nama_paket_jasa'),
            'deskripsi_paket_jasa' => $request->input('deskripsi_paket_jasa'),
            'harga_paket_jasa' => $request->input('harga_paket_jasa'),
            'waktu_pengerjaan' => $request->input('waktu_pengerjaan'),
            'maksimal_revisi' => $request->input('maksimal_revisi'),
            'fitur' => $request->input('fitur'),
            'id_jasa' => $idJasa,
        ]);
        if(!$ins){
            return response()->json(['status'=>'error','message'=>'Gagal menambahkan data Jasa'], 500);
        }
        return response()->json(['status'=>'success','message'=>'Data Jasa berhasil ditambahkan']);
    }
    public function updateJasa(Request $request){
        $idJasa = Jasa::insertGetId([
            'nama_jasa' => $request->input('nama_jasa'),
            'thumbnail_jasa' => $request->input('thumbnail_jasa'),
            'kategori' => $request->input('kategori'),
        ]);
        $ins = Jasa::insert([
            'nama_paket_jasa' => $request->input('nama_paket_jasa'),
            'deskripsi_paket_jasa' => $request->input('deskripsi_paket_jasa'),
            'harga_paket_jasa' => $request->input('harga_paket_jasa'),
            'waktu_pengerjaan' => $request->input('waktu_pengerjaan'),
            'maksimal_revisi' => $request->input('maksimal_revisi'),
            'fitur' => $request->input('fitur'),
            'id_jasa' => $idJasa,
        ]);
        if(!$ins){
            return response()->json(['status'=>'error','message'=>'Gagal menambahkan data Jasa'], 500);
        }
        return response()->json(['status'=>'success','message'=>'Data Jasa berhasil ditambahkan']);
    }
    public function deleteJasa(Request $request){
        $idJasa = Jasa::insertGetId([
            'nama_jasa' => $request->input('nama_jasa'),
            'thumbnail_jasa' => $request->input('thumbnail_jasa'),
            'kategori' => $request->input('kategori'),
        ]);
        $ins = Jasa::insert([
            'nama_paket_jasa' => $request->input('nama_paket_jasa'),
            'deskripsi_paket_jasa' => $request->input('deskripsi_paket_jasa'),
            'harga_paket_jasa' => $request->input('harga_paket_jasa'),
            'waktu_pengerjaan' => $request->input('waktu_pengerjaan'),
            'maksimal_revisi' => $request->input('maksimal_revisi'),
            'fitur' => $request->input('fitur'),
            'id_jasa' => $idJasa,
        ]);
        if(!$ins){
            return response()->json(['status'=>'error','message'=>'Gagal menambahkan data Jasa'], 500);
        }
        return response()->json(['status'=>'success','message'=>'Data Jasa berhasil ditambahkan']);
    }
}