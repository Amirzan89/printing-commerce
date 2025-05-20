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
    //API only jasa route
    Route::group(['prefix'=>'/jasa'], function(){
        //page jasa
        Route::get('/',[ShowJasaController::class,'showAll']);
        Route::get('/detail/{any}',[ShowJasaController::class,'showDetail']);
        Route::get('/tambah',[ShowJasaController::class,'showTambah']);
        Route::get('/edit/{any}',[ShowJasaController::class,'showEdit']);
        Route::get('/edit', function(){
            return view('page.admin.data');
        });
        // route for jasa
        Route::post('/create',[JasaController::class,'createJasa']);
        Route::put('/update',[JasaController::class,'updateJasa']);
        Route::delete('/delete',[JasaController::class,'deleteJasa']);
    });

    //API only pesanan route
    Route::group(['prefix'=>'/pesanan'], function(){
        //page pesanan
        Route::get('/',[ShowPesananController::class,'showAll']);
        Route::get('/detail/{any}',[ShowPesananController::class,'showDetail']);
        Route::get('/tambah',[ShowPesananController::class,'showTambah']);
        Route::get('/edit/{any}',[ShowPesananController::class,'showEdit']);
        Route::get('/edit', function(){
            return view('page.admin.data');
        });
        // route for pesanan
        Route::post('/create',[PesananController::class,'createPesanan']);
        Route::put('/update',[PesananController::class,'updatePesanan']);
        Route::delete('/delete',[PesananController::class,'deletePesanan']);
    });

    //API only transaksi route
    Route::group(['prefix'=>'/transaksi'], function(){
        //page transaksi
        Route::get('/',[ShowTransaksiController::class,'showAll']);
        Route::get('/detail/{any}',[ShowTransaksiController::class,'showDetail']);
        Route::get('/tambah',[ShowTransaksiController::class,'showTambah']);
        Route::get('/edit/{any}',[ShowTransaksiController::class,'showEdit']);
        Route::get('/edit', function(){
            return view('page.admin.data');
        });
        // route for transaksi
        Route::post('/create',[TransaksiController::class,'createTransaksi']);
        Route::put('/update',[TransaksiController::class,'updateTransaksi']);
        Route::delete('/delete',[TransaksiController::class,'deleteTransaksi']);
    });

    //API only metode pembayaran route
    Route::group(['prefix'=>'/metode-pembayaran'], function(){
        //page metode pembayaran
        Route::get('/',[ShowMetodePembayaranController::class,'showAll']);
        Route::get('/detail/{any}',[ShowMetodePembayaranController::class,'showDetail']);
        Route::get('/tambah',[ShowMetodePembayaranController::class,'showTambah']);
        Route::get('/edit/{any}',[ShowMetodePembayaranController::class,'showEdit']);
        Route::get('/edit', function(){
            return view('page.admin.data');
        });
        // route for metode pembayaran
        Route::post('/create',[MetodePembayaranController::class,'createMepe']);
        Route::put('/update',[MetodePembayaranController::class,'updateMepe']);
        Route::delete('/delete',[MetodePembayaranController::class,'deleteMepe']);
    });

    Route::group(['prefix'=>'/admin'], function(){
        //page admin
        Route::get('/',[ShowAdminController::class,'showAll']);
        Route::get('/tambah',[ShowAdminController::class,'showTambah']);
        Route::get('/edit/{any}',[ShowAdminController::class,'showEdit']);
        Route::get('/edit', function(){
            return view('page.admin.data');
        });
        // route for admin
        Route::post('/create',[AdminController::class,'createAdmin']);
        Route::delete('/delete',[AdminController::class,'deleteAdmin']);
        Route::group(['prefix'=>'/update'],function(){
            Route::put('/',[AdminController::class,'updateAdmin']);
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
    })->name('login');
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