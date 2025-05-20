<?php
namespace App\Http\Controllers\Page;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class TransaksiController extends Controller
{
    public function showData(Request $request){
        $dataShow = [
            'dataTransaksi' => app()->make(ServiceTransaksiController::class)->dataCacheFile(null, 'get_limit',null, ['uuid', 'judul','rentang_usia']),
            'userAuth' => $request->input('user_auth'),
        ];
        return view('page.transaksi.data',$dataShow);
    }
    public function showTambah(Request $request){
        $dataShow = [
            'userAuth' => $request->input('user_auth'),
        ];
        return view('page.transaksi.tambah',$dataShow);
    }
    public function showEdit(Request $request, $uuid){
        $disi = app()->make(ServiceTransaksiController::class)->dataCacheFile(['uuid' => $uuid], 'get_limit', 1, ['uuid', 'judul', 'deskripsi', 'rentang_usia', 'foto', 'link_video']);
        if (is_null($disi)) {
            return redirect('/disi')->with('error', 'Data Transaksi tidak ditemukan');
        }
        $dataShow = [
            'disi' => $disi[0],
            'userAuth' => $request->input('user_auth'),
        ];
        return view('page.transaksi.edit',$dataShow);
    }
}