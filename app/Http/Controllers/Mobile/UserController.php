<?php
namespace App\Http\Controllers\Mobile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
class UserController extends Controller
{
    public function register(Request $request){
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        $auth = Auth::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        $user->id_auth = $auth->id_auth;
        $user->save();
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
    public function update(Request $request){
        $user = User::where('id_auth', $request->user()->id_auth)->first();
        $user->name = $request->name;
        $user->save();
        return response()->json(['status' => 'success', 'message' => 'User updated successfully']);
    }
    public function delete(Request $request){
        $user = User::where('id_auth', $request->user()->id_auth)->first();
        $user->delete();
        return response()->json(['status' => 'success', 'message' => 'User deleted successfully']);
    }

    //from admin
    public function createUser(Request $rt){
        $validator = Validator::make($rt->only('email', 'nama_user', 'jenis_kelamin', 'no_telpon', 'password', 'foto'), [
            'email'=>'required|email',
            'nama_user' => 'required|min:3|max:50',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
            'no_telpon' => 'required|max:15',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:25',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\p{P}\p{S}])[\p{L}\p{N}\p{P}\p{S}]+$/u',
            ],
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ],[
            'email.required'=>'Email wajib di isi',
            'email.email'=>'Email yang anda masukkan invalid',
            'nama_user.required' => 'Nama user wajib di isi',
            'nama_user.min'=>'Nama user minimal 3 karakter',
            'nama_user.max' => 'Nama user maksimal 50 karakter',
            'password.required'=>'Password wajib di isi',
            'password.min'=>'Password minimal 8 karakter',
            'password.max'=>'Password maksimal 25 karakter',
            'password.regex'=>'Password terdiri dari 1 huruf besar, huruf kecil, angka dan karakter unik',
            'foto.image' => 'Foto user harus berupa gambar',
            'foto.mimes' => 'Format foto user tidak valid. Gunakan format jpeg, png, jpg',
            'foto.max' => 'Ukuran foto user tidak boleh lebih dari 5MB',
        ]);
        if($validator->fails()){
            $errors = [];
            foreach($validator->errors()->toArray() as $field => $errorMessages){
                $errors[$field] = $errorMessages[0];
                break;
            }
            return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }
        if(Auth::select("email")->whereRaw("BINARY email = ?",[$rt->input('email')])->exists()){
            return response()->json(['status'=>'error','message'=>'Email sudah digunakan'],400);
        }
        if($rt->hasFile('foto')){
            $fi = $rt->file('foto');
            if(!($fi->isValid() && in_array($fi->extension(), ['jpeg', 'png', 'jpg']))){
                return response()->json(['status'=>'error','message'=>'Format Foto tidak valid. Gunakan format jpeg, png, jpg'], 400);
            }
            $fh = $fi->hashName();
            $fi->move(public_path('assets3/img/user/'), $fh);
        }
        $idAuth = Auth::insertGetId([
            'email' => $rt->input('email'),
            'password' => Hash::make($rt->input('password')),
            'role' => 'user',
        ]);
        $ins = User::insert([
            'uuid' =>  Str::uuid(),
            'nama_user' => $rt->input('nama_user'),
            'jenis_kelamin' => $rt->input('jenis_kelamin'),
            'no_telpon' => $rt->input('no_telpon'),
            'foto' => $rt->hasFile('foto') ? $fh : '',
            'id_auth' => $idAuth,
        ]);
        if(!$ins){
            return response()->json(['status'=>'error','message'=>'Gagal menambahkan data User'], 500);
        }
        return response()->json(['status' => 'success', 'message' => 'Data User berhasil ditambahkan']);
    }
    //from admin
    public function deleteUser(Request $rt){
        $validator = Validator::make($rt->only('uuid'), [
            'uuid' => 'required',
        ], [
            'uuid.required' => 'User ID wajib di isi',
        ]);
        if ($validator->fails()){
            $errors = [];
            foreach ($validator->errors()->toArray() as $field => $errorMessages){
                $errors[$field] = $errorMessages[0];
                break;
            }
            return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }
        $user = User::where('uuid', $rt->input('uuid'))->firstOrFail();
        $ftd = public_path('assets3/img/user/') . $user['foto'];
        if (file_exists($ftd) && !is_dir($ftd)){
            unlink($ftd);
        }
        if(!$user->delete()){
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus data User'], 500);
        }
        return response()->json(['status' => 'success', 'message' => 'Data User berhasil dihapus']);
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