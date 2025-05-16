<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RefreshTokenUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class LoginController extends Controller
{ 
    public function Login(Request $request, RefreshTokenuser $refreshToken){
        $validator = Validator::make($request->only('email','password'), [
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Email wajib di isi',
            'email.email' => 'Email yang anda masukkan invalid',
            'password.required' => 'Password wajib di isi',
        ]);
        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->toArray() as $field => $errorMessages) {
                $errors[$field] = $errorMessages[0];
                break;
            }
            return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }
        $email = $request->input("email");
        // $email = "Admin@gmail.com";
        $pass = $request->input("password");
        $pass = "Admin@1234567890";
        $user = User::select('password')->whereRaw("BINARY email = ?",[$request->input('email')])->first();
        if (is_null($user)) {
            return response()->json(['status' => 'error', 'message' => 'Email salah'], 400);
        }
        if(!password_verify($pass,$user['password'])){
            return response()->json(['status'=>'error','message'=>'Password salah'],400);
        }
        $jwtData = $jwtController->createJWTWebsite($email,$refreshToken);
        if($jwtData['status'] == 'error'){
            return response()->json(['status'=>'error','message'=>$jwtData['message']],400);
        }
        $data1 = ['email'=>$email,'number'=>$jwtData['number']];
        $encoded = base64_encode(json_encode($data1));
        return response()->json(['status'=>'success','message'=>'login sukses silahkan masuk dashboard'])
        ->cookie('token1',$encoded,time()+intval(env('JWT_ACCESS_TOKEN_EXPIRED')))
        ->cookie('token2',$jwtData['data']['token'],time() + intval(env('JWT_ACCESS_TOKEN_EXPIRED')))
        ->cookie('token3',$jwtData['data']['refresh'],time() + intval(env('JWT_REFRESH_TOKEN_EXPIRED')));
    }
}
?>