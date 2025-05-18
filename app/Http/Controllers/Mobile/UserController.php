<?php
namespace App\Http\Controllers\Mobile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Auth;
use App\Models\User;
class UserController extends Controller
{
    public function register(Request $request){
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        return response()->json(['status' => 'success', 'message' => 'User registered successfully. Please login to continue.']);
    }
    public function login(Request $request){
        $credentials = $request->validate([
            'email' => ['required'],
            'password' => ['required'],
        ]);
        $auth = Auth::where('email', $credentials['email'])->first();
        if(!$auth || !Hash::check($credentials['password'],$auth->password)){
            return response()->json(['status' => 'error', 'message' => 'Invalid Credentials'], 401);
        }
        $token = $auth->createToken($auth->name.'-AuthToken')->plainTextToken;
        return response()->json(['status'=>'success', 'message' => 'berhasil login', 'access_token' => $token]);
    }
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json(['status' => 'success', 'message' => 'Logged out from current device']);
    }
    public function logoutAll(Request $request){
        $request->user()->tokens()->delete();
        return response()->json(['status' => 'success', 'message' => 'Logged out from all devices']);
    }
}