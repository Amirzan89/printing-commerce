<?php
namespace App\Http\Controllers\Page;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Http\Controllers\UtilityController;
use App\Models\Transaksi;
class TransaksiController extends Controller
{
    public function showAll(Request $request){
        $dataShow = [
            'headerData' => UtilityController::getHeaderData(),
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'transaksiData' => Transaksi::select('uuid','nama_transaksi')->get(),
        ];
        return view('page.transaksi.data',$dataShow);
    }
    public function showEdit(Request $request, $uuid){
        $transaksiData = Transaksi::select('uuid','nama_transaksi', '')->whereRaw("BINARY uuid = ?",[$uuid])->first();
        if(is_null($transaksiData)){
            return redirect('/transaksi')->with('error', 'Data Transaksi tidak ditemukan');
        }
        $dataShow = [
            'headerData' => UtilityController::getHeaderData(),
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'transaksiData' => $transaksiData,
        ];
        return view('page.transaksi.edit',$dataShow);
    }
}