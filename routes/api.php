<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mobile\UserController;
use App\Http\Controllers\Mobile\Page\HomeController;
use App\Http\Controllers\Mobile\JasaController;
use App\Http\Controllers\Mobile\PesananController;
use App\Http\Controllers\Mobile\TransaksiController;
use App\Http\Controllers\Mobile\MetodePembayaranController;
Route::middleware('auth:sanctum')->get('/user', function (Request $request){
    return $request->user();
});
Route::group(['prefix'=>'/mobile','middleware'=>'authMobile','authorized'],function(){
    //API only jasa route
    Route::group(['prefix'=>'/jasa'], function(){
        Route::get('/',[JasaController::class,'showAll']);
        Route::get('/detail/{any}',[JasaController::class,'showDetail']);
        Route::get('/tambah',[JasaController::class,'showTambah']);
        Route::get('/edit/{any}',[JasaController::class,'showEdit']);
        Route::post('/create',[JasaController::class,'createJasa']);
        Route::put('/update',[JasaController::class,'updateJasa']);
        Route::delete('/delete',[JasaController::class,'deleteJasa']);
    });

    //API only pesanan route
    Route::group(['prefix'=>'/pesanan'], function(){
        Route::get('/',[PesananController::class,'showAll']);
        Route::get('/detail/{any}',[PesananController::class,'showDetail']);
        Route::get('/tambah',[PesananController::class,'showTambah']);
        Route::get('/edit/{any}',[PesananController::class,'showEdit']);
        Route::get('/edit', function(){
            return view('page.admin.data');
        });
        Route::post('/create',[PesananController::class,'createPesanan']);
        Route::put('/update',[PesananController::class,'updatePesanan']);
        Route::delete('/delete',[PesananController::class,'deletePesanan']);
    });

    //API only transaksi route
    Route::group(['prefix'=>'/transaksi'], function(){
        Route::get('/',[TransaksiController::class,'showAll']);
        Route::get('/detail/{any}',[TransaksiController::class,'showDetail']);
        Route::get('/tambah',[TransaksiController::class,'showTambah']);
        Route::get('/edit/{any}',[TransaksiController::class,'showEdit']);
        Route::get('/edit', function(){
            return view('page.admin.data');
        });
        Route::post('/create',[TransaksiController::class,'createTransaksi']);
        Route::put('/update',[TransaksiController::class,'updateTransaksi']);
        Route::delete('/delete',[TransaksiController::class,'deleteTransaksi']);
    });

    //API only metode pembayaran route
    Route::group(['prefix'=>'/metode-pembayaran'], function(){
        Route::get('/',[MetodePembayaranController::class,'showAll']);
        Route::get('/detail/{any}',[MetodePembayaranController::class,'showDetail']);
        Route::get('/tambah',[MetodePembayaranController::class,'showTambah']);
        Route::get('/edit/{any}',[MetodePembayaranController::class,'showEdit']);
        Route::get('/edit', function(){
            return view('page.admin.data');
        });
        Route::post('/create',[MetodePembayaranController::class,'createMPembayaran']);
        Route::put('/update',[MetodePembayaranController::class,'updateMPembayaran']);
        Route::delete('/delete',[MetodePembayaranController::class,'deleteMPembayaran']);
    });

    Route::group(['prefix'=>'/users'],function(){
        Route::group(['prefix'=>'/profile'],function(){
            Route::post('/', [UserController::class, 'getProfile']);
            Route::post('/update', [UserController::class, 'updateProfile']);
            Route::post('/foto', [UserController::class, 'checkFotoProfile']);
        });
    });
    Route::post('/dashboard',[HomeController::class, 'dashboard']);
});
Route::group(['middleware' => 'user.guest'], function(){
    Route::group(['prefix'=>'/users'],function(){
        Route::post('/login', [UserController::class,'login']);
        Route::post('/register', [UserController::class,'register']);
        Route::post('/logout', [UserController::class,'logout']);
        Route::middleware('auth:sanctum')->post('/logout-all', [UserController::class, 'logoutAll']);
    });
    Route::group(['prefix'=>'/verify'],function(){
        Route::group(['prefix'=>'/create'],function(){
            Route::post('/password',[MailController::class, 'createForgotPassword']);
            Route::post('/email',[MailController::class, 'createVerifyEmail']);
        });
        Route::group(['prefix'=>'/password'],function(){
            Route::get('/{any?}',[UserController::class, 'getChangePass'])->where('any','.*');
            Route::post('/',[UserController::class, 'changePassEmail']);
        });
        Route::group(['prefix'=>'/email'],function(){
            Route::get('/{any?}',[UserController::class, 'verifyEmail'])->where('any','.*');
            Route::post('/',[UserController::class, 'verifyEmail'])->where('any','.*');
        });
        Route::group(['prefix'=>'/otp'],function(){
            Route::post('/password',[UserController::class, 'getChangePass']);
            Route::post('/email',[UserController::class, 'verifyEmail']);
        });
    });
});