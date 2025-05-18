<?php
namespace App\Http\Controllers\Pages;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class TransaksiController extends Controller
{
    public function showData(Request $request){
        $dataShow = [
            'dataDisi' => app()->make(ServiceDisiController::class)->dataCacheFile(null, 'get_limit',null, ['uuid', 'judul','rentang_usia']),
            'userAuth' => $request->input('user_auth'),
        ];
        return view('page.Disi.data',$dataShow);
    }
    public function showTambah(Request $request){
        $dataShow = [
            'userAuth' => $request->input('user_auth'),
        ];
        return view('page.Disi.tambah',$dataShow);
    }
    public function showEdit(Request $request, $uuid){
        $disi = app()->make(ServiceDisiController::class)->dataCacheFile(['uuid' => $uuid], 'get_limit', 1, ['uuid', 'judul', 'deskripsi', 'rentang_usia', 'foto', 'link_video']);
        if (is_null($disi)) {
            return redirect('/disi')->with('error', 'Data Digital Literasi tidak ditemukan');
        }
        $dataShow = [
            'disi' => $disi[0],
            'userAuth' => $request->input('user_auth'),
        ];
        return view('page.Disi.edit',$dataShow);
    }
}