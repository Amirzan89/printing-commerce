<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pesanan;
class UtilityController extends Controller
{
    public static function checkEmail($email){
        $userDB = User::select('id_user', 'role')->whereRaw("BINARY email = ?", [$email])->first();
        if(is_null($userDB)){
            return ['status'=>'error','message'=>'Account not found','code'=>404];
        }
        return ['status'=>'success', 'data' => $userDB->toArray()];
    }
    public static function getHeaderData(){
        return Pesanan::with(['toUser', 'toJasa'])->join('catatan_pesanan', 'catatan_pesanan.id_pesanan', '=', 'pesanan.id_pesanan')
            ->select('pesanan.id_pesanan', 'catatan_pesanan', 'status_pesanan', 'total_harga', 'pesanan.id_user', 'id_jasa', 'created_at')
            ->where('status_pesanan', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }
}