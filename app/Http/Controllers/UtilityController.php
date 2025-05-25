<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\RiwayatEditor;
use App\Models\User;
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
        return RiwayatEditor::select('nama_editor', 'deskripsi_pengerjaan')->orderBy('id_riwayat_editor', 'desc')->limit(5)->get();
    }
}