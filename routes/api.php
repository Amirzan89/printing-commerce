<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mobile\UserController;
use App\Http\Controllers\Mobile\JasaController;
use App\Http\Controllers\Mobile\PesananController;
use App\Http\Controllers\Mobile\TransaksiController;
use App\Http\Controllers\Mobile\MailController;
use App\Http\Controllers\Mobile\PengerjaanController;
use App\Http\Controllers\Mobile\MetodePembayaranController;
Route::middleware('auth:sanctum')->get('/user', function (Request $request){
    return $request->user();
});
Route::group(['prefix'=>'/mobile'], function(){
    Route::group(['middleware'=>'auth.mobile'], function(){
        //API only jasa route
        Route::group(['prefix'=>'/jasa'], function(){
            Route::get('/',[JasaController::class,'showAll']);
            Route::get('/detail/{any}',[JasaController::class,'showDetail']);
        });

        //API only pesanan route
        Route::group(['prefix'=>'/pesanan'], function(){
            Route::get('/', [PesananController::class, 'getAll']);
            Route::get('/detail/{uuid}', [PesananController::class, 'getDetail']);
            Route::post('/create', [PesananController::class, 'create']);
            Route::post('/cancel', [PesananController::class, 'cancel']);
        });
        
        //API only pengerjaan route
        Route::group(['prefix'=>'/pengerjaan'], function(){
            Route::get('/', [PengerjaanController::class, 'getAll']);
            Route::get('/{id_revisi}', [PengerjaanController::class, 'getDetail']);
            Route::post('/{id_revisi}/request-revisi', [PengerjaanController::class, 'requestRevision']);
            Route::get('/{id_revisi}/download', [PengerjaanController::class, 'downloadFiles']);
            Route::get('/{id_revisi}/history', [PengerjaanController::class, 'getRevisionHistory']);
            Route::get('/{id_revisi}/{revisionUuid}', [PengerjaanController::class, 'getDetail']);
            Route::post('/{id_revisi}/accept-work', [PengerjaanController::class, 'acceptWork']);
            Route::post('/download', [PengerjaanController::class, 'downloadFiles']);
        });

        //API only metode pembayaran route
        Route::group(['prefix'=>'/metode-pembayaran'], function(){
            Route::get('/', [MetodePembayaranController::class, 'showAll']);
            Route::get('/{uuid}', [MetodePembayaranController::class, 'showDetail']);
        });

        //API only transaksi route
        Route::group(['prefix'=>'/transaksi'], function(){
            Route::get('/', [TransaksiController::class, 'getAll']);
            Route::get('/{order_id}', [TransaksiController::class, 'getDetail']);
            Route::post('/create', [TransaksiController::class, 'createTransaction']);
            Route::post('/upload-payment', [TransaksiController::class, 'uploadPaymentProof']);
            Route::get('/details/{orderId}', [TransaksiController::class, 'getTransactionDetails']);
            Route::get('/user-transactions', [TransaksiController::class, 'getUserTransactions']);
            Route::post('/cancel', [TransaksiController::class, 'cancelTransaction']);
        });

        Route::group(['prefix'=>'/users'],function(){
            Route::group(['prefix'=>'/profile'],function(){
                Route::post('/', [UserController::class, 'getProfile']);
                Route::post('/update', [UserController::class, 'updateProfile']);
                Route::post('/foto', [UserController::class, 'checkFotoProfile']);
            });
            // Auth routes for logout
            Route::post('/logout', [UserController::class, 'logout']);
            Route::post('/logout-all', [UserController::class, 'logoutAll']);
        });
        Route::get('/dashboard',[UserController::class, 'dashboard']);
    });

    Route::group(['middleware' => 'user.guest'], function(){
        Route::group(['prefix'=>'/users'],function(){
            Route::post('/login', [UserController::class,'login']);
            Route::post('/register', [UserController::class,'register']);
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
});