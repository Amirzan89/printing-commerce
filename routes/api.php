<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mobile\UserController;
use App\Http\Controllers\Mobile\Page\HomeController;
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['prefix'=>'/mobile','middleware'=>'authMobile','authorized'],function(){
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

    //API only metode pembayaran route
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
        Route::middleware('auth:sanctum')->post('/logout-all', [AuthController::class, 'logoutAll']);
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