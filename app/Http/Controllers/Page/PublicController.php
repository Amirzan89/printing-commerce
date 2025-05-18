<?php
namespace App\Http\Controllers\Page;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
class PublicController extends Controller
{
    public function showHome(Request $request){
        $dataShow = [
            'kalender' => app()->make(ServiceEventController::class)->dataCacheFile(null, 'get_limit', null, ['nama_event', 'tanggal_awal', 'tanggal_akhir']),
            'artikel' => array_map(function($item){
                $item['created_at'] = Carbon::parse($item['created_at'])->translatedFormat('l, d F Y');
                return $item;
            }, app()->make(ServiceArtikelController::class)->dataCacheFile(null, 'get_limit', 3, ['judul', 'foto', 'created_at'], null, true) ?? []),
        ];
        return view('page.home',$dataShow);
    }
}