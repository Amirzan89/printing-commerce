<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Services\JasaController;
use App\Http\Controllers\Services\PesananController;
use App\Http\Controllers\Services\MetodePembayaranController;
use App\Http\Controllers\Services\TransaksiController;
use App\Http\Controllers\Services\ReviewController;
use App\Http\Controllers\Services\AdminController;
use App\Http\Controllers\Services\LoginController;

use App\Http\Controllers\Page\PublicController AS ShowHomeController;
use App\Http\Controllers\Page\JasaController AS ShowJasaController;
use App\Http\Controllers\Page\PesananController AS ShowPesananController;
use App\Http\Controllers\Page\MetodePembayaranController AS ShowMetodePembayaranController;
use App\Http\Controllers\Page\TransaksiController AS ShowTransaksiController;
use App\Http\Controllers\Page\ReviewController AS ShowReviewController;
use App\Http\Controllers\Page\AdminController AS ShowAdminController;

Route::group(['middleware'=>['auth:sanctum','authorize']], function(){
    //artikel public route
    //API only admin route
    Route::group(['prefix'=>'/metode-pembayaran'], function(){
        //page metode-pembayaran
        Route::get('/',[ShowAdminController::class,'showAdmin']);
        Route::get('/tambah',[ShowAdminController::class,'showAdminTambah']);
        Route::get('/edit/{any}',[ShowAdminController::class,'showAdminEdit']);
        Route::get('/edit', function(){
            return view('page.admin.data');
        });
        // route for admin
        Route::post('/tambah',[AdminController::class,'tambahAdmin']);
        Route::put('/update',[AdminController::class,'editAdmin']);
        Route::delete('/delete',[AdminController::class,'hapusAdmin']);
        Route::group(['prefix'=>'/update'],function(){
            Route::put('/profile', [AdminController::class, 'updateProfile']);
            Route::put('/password', [AdminController::class, 'updatePassword']);
        });
    });
    Route::group(['prefix'=>'/admin'], function(){
        //page admin
        Route::get('/',[ShowAdminController::class,'showAdmin']);
        Route::get('/tambah',[ShowAdminController::class,'showAdminTambah']);
        Route::get('/edit/{any}',[ShowAdminController::class,'showAdminEdit']);
        Route::get('/edit', function(){
            return view('page.admin.data');
        });
        // route for admin
        Route::post('/tambah',[AdminController::class,'tambahAdmin']);
        Route::put('/update',[AdminController::class,'editAdmin']);
        Route::delete('/delete',[AdminController::class,'hapusAdmin']);
        Route::group(['prefix'=>'/update'],function(){
            Route::put('/profile', [AdminController::class, 'updateProfile']);
            Route::put('/password', [AdminController::class, 'updatePassword']);
        });
    });
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/dashboard',[ShowAdminController::class,'showDashboard']);
    Route::get('/profile',[ShowAdminController::class,'showProfile']);
});
Route::group(['middleware' => 'admin.guest'], function(){
    Route::get('/login', function(){
        return view('page.login');
    });
    Route::group(['prefix'=>'/admin'], function(){
        Route::post('/login', function(Request $request){
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
            if(!Auth::attempt($request->only('email','password'))){
                return response()->json(['status'=>'error', 'message'=>'Invalid credentials'], 401);
            }
            $request->session()->regenerate();
            return response()->json(['status'=>'success', 'message'=>'Login successful']);
        });
        Route::post('/logout', function(Request $request){
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        });
    });
    Route::get('/password/reset', function(){
        return view('page.forgotPassword', ['title'=>'Lupa password']);
    });
    // Route::get('/testing', function () {
    //     return view('page.testing');
    // });
    // Route::get('/template', function(){
    //     return view('page.template');
    // });
    Route::get('/',[ShowHomeController::class,'showHome']);
});