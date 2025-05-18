<?php
namespace App\Http\Controllers\Page;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class ReviewController extends Controller
{
    public function showData(Request $request){
        $dataShow = [
            'dataDisi' => app()->make(ServiceDisiController::class)->dataCacheFile(null, 'get_limit',null, ['uuid', 'judul','rentang_usia']),
            'userAuth' => $request->input('user_auth'),
        ];
        return view('page.Disi.data',$dataShow);
    }
}