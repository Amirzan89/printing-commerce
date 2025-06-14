<?php
namespace App\Http\Controllers\Mobile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\MetodePembayaran;
use App\Http\Controllers\UtilityController;
class MetodePembayaranController extends Controller
{
    public function showAll(){
        $metodePembayaranData = MetodePembayaran::select('uuid','nama_metode_pembayaran')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Data Metode Pembayaran berhasil diambil',
            'data' => $metodePembayaranData
        ], 200);
    }
    public function showDetail($uuid){
        $metodePembayaranData = MetodePembayaran::select('uuid','nama_metode_pembayaran', 'no_metode_pembayaran', 'deskripsi_1', 'deskripsi_2', 'thumbnail', 'icon')->whereRaw("BINARY uuid = ?",[$uuid])->first();
        if(is_null($metodePembayaranData)){
            return response()->json([
                'status' => 'error',
                'message' => 'Data Metode Pembayaran tidak ditemukan',
                'data' => null
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Data Metode Pembayaran berhasil diambil',
            'data' => $metodePembayaranData
        ], 200);
    }
}