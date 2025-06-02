<?php
namespace App\Http\Controllers\Mobile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\UtilityController;
use App\Models\User;
use Illuminate\Support\Str;
class UserController extends Controller
{
    /**
     * Mobile app login - generates API token for Sanctum authentication
     */
    public function login(Request $request){
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
        
        $auth = Auth::where('email', $request->input('email'))->first();
        if(!$auth || !Hash::check($request->input('password'), $auth->password)){
            return response()->json(['status' => 'error', 'message' => 'Invalid Credentials'], 401);
        }
        
        // Get user details
        $user = User::where('id_auth', $auth->id_auth)->first();
        
        // Create token with abilities for mobile app
        $token = $auth->createToken('mobile-auth-token', ['mobile-access'])->plainTextToken;
        
        // Return token with user info
        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->uuid,
                    'name' => $user->nama_user,
                    'email' => $auth->email,
                    'role' => $auth->role
                ]
            ]
        ]);
    }

    public function register(Request $request){
        $validator = Validator::make($request->only('email', 'nama_user', 'password', 'password_confirmation'), [
            'email'=>'required|email',
            'nama_user' => 'required|min:3|max:50',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:25',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\p{P}\p{S}])[\p{L}\p{N}\p{P}\p{S}]+$/u',
            ],
            'password_confirmation' => 'required|same:password',
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
            'password_confirmation.required'=>'Password confirmation wajib di isi',
            'password_confirmation.same'=>'Password confirmation tidak sama dengan password',
        ]);
        if($validator->fails()){
            $errors = [];
            foreach($validator->errors()->toArray() as $field => $errorMessages){
                $errors[$field] = $errorMessages[0];
                break;
            }
            return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }
        if(Auth::select("email")->whereRaw("BINARY email = ?",[$request->input('email')])->exists()){
            return response()->json(['status'=>'error','message'=>'Email sudah digunakan'],400);
        }
        $idAuth = Auth::insertGetId([
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role'=> 'user',
        ]);
        $ins = User::insert([
            'uuid' =>  Str::uuid(),
            'nama_user' => $request->input('nama_user'),
            'id_auth' => $idAuth,
        ]);
        if(!$ins){
            return response()->json(['status'=>'error','message'=>'Gagal register'], 500);
        }
        return response()->json(['status' => 'success', 'message' => 'Register berhasil. Silahkan login untuk melanjutkan.']);
    }
    public function dashboard(Request $request){
        $dataShow = [
            'userAuth' => array_merge(User::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
        ];
        return response()->json($dataShow);
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
        $validator = Validator::make($rt->only('email', 'nama_lengkap', 'jenis_kelamin', 'no_telpon', 'password', 'foto'), [
            'email'=>'required|email',
            'nama_lengkap' => 'required|min:3|max:50',
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
            'nama_lengkap.required' => 'Nama user wajib di isi',
            'nama_lengkap.min'=>'Nama user minimal 3 karakter',
            'nama_lengkap.max' => 'Nama user maksimal 50 karakter',
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
            'nama_user' => $rt->input('nama_lengkap'),
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
        return response()->json(['status' => 'success', 'message' => 'Logout berhasil silahkan login kembali']);
    }
    
    public function logoutAll(Request $request){
        $request->user()->tokens()->delete();
        return response()->json(['status' => 'success', 'message' => 'Berhasil logout dari semua perangkat']);
    }
}