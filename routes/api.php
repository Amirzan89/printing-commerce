<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mobile\UserController;
use App\Http\Controllers\Mobile\JasaController;
use App\Http\Controllers\Mobile\PesananController;
use App\Http\Controllers\Mobile\RevisiController;
use App\Http\Controllers\Mobile\TransaksiController;
use App\Http\Controllers\Mobile\MetodePembayaranController;
use App\Http\Controllers\Mobile\MailController;

// Services (Admin) routes
use App\Http\Controllers\Services\PesananController as AdminPesananController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request){
    return $request->user();
});

// ========================================
// ADMIN ROUTES (Services Controller)
// ========================================
Route::group(['prefix' => '/admin', 'middleware' => 'auth.admin'], function() {
    
    // Admin Pesanan Management
    Route::prefix('pesanan')->group(function () {
        Route::get('/', [AdminPesananController::class, 'getAllPesanan']);
        Route::get('/detail/{uuid}', [AdminPesananController::class, 'getPesananDetail']);
        Route::put('/status/{uuid}', [AdminPesananController::class, 'updateStatus']);
        Route::post('/assign-editor/{uuid}', [AdminPesananController::class, 'assignEditor']);
        Route::delete('/delete/{uuid}', [AdminPesananController::class, 'deletePesanan']);
        Route::get('/statistics', [AdminPesananController::class, 'getStatistics']);
        Route::post('/bulk-update', [AdminPesananController::class, 'bulkUpdateStatus']);
    });
    
    // Admin Payment Management
    Route::prefix('payments')->group(function () {
        Route::get('/pending', [TransaksiController::class, 'getPendingPayments']);
        Route::post('/confirm', [TransaksiController::class, 'confirmPayment']);
        Route::post('/reject', [TransaksiController::class, 'rejectPayment']);
    });
});

// ========================================
// USER ROUTES (Mobile Controller)
// ========================================
Route::group(['prefix'=>'/mobile'], function(){
    Route::group(['middleware'=>'auth.mobile'], function(){
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
                return redirect('/pesanan');
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
                return redirect('/transaksi');
            });
            Route::post('/create',[TransaksiController::class,'createTransaction']);
            Route::put('/update',[TransaksiController::class,'updateTransaction']);
            Route::delete('/delete',[TransaksiController::class,'deleteTransaction']);
        });

        //API only metode pembayaran route
        Route::group(['prefix'=>'/metode-pembayaran'], function(){
            Route::get('/',[MetodePembayaranController::class,'showAll']);
            Route::get('/detail/{any}',[MetodePembayaranController::class,'showDetail']);
            Route::get('/tambah',[MetodePembayaranController::class,'showTambah']);
            Route::get('/edit/{any}',[MetodePembayaranController::class,'showEdit']);
            Route::get('/edit', function(){
                return redirect('/metode-pembayaran');
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
            // Auth routes for logout
            Route::post('/logout', [UserController::class, 'logout']);
            Route::post('/logout-all', [UserController::class, 'logoutAll']);
        });
        Route::get('/dashboard',[UserController::class, 'dashboard']);
        
        // USER: Enhanced Pesanan routes for manual payment flow
        Route::prefix('orders')->group(function () {
            Route::get('/', [PesananController::class, 'getAll']);
            Route::get('/detail/{uuid}', [PesananController::class, 'getDetail']);
            Route::post('/create', [PesananController::class, 'create']);
            Route::post('/cancel/{uuid}', [PesananController::class, 'cancel']);
            Route::post('/accept-work/{uuid}', [PesananController::class, 'acceptWork']);
            Route::get('/download/{uuid}', [PesananController::class, 'downloadFiles']);
            Route::get('/revisions/{uuid}', [PesananController::class, 'getRevisionHistory']);
            Route::post('/request-revision/{uuid}', [PesananController::class, 'requestRevision']);
            Route::post('/approve-revision/{uuid}/{revisionUuid}', [PesananController::class, 'approveRevision']);
        });

        // USER: Enhanced Transaction routes for manual payment flow
        Route::prefix('transactions')->group(function () {
            Route::post('/create', [TransaksiController::class, 'createTransaction']);
            Route::post('/upload-payment', [TransaksiController::class, 'uploadPaymentProof']);
            Route::get('/details/{orderId}', [TransaksiController::class, 'getTransactionDetails']);
            Route::get('/user-transactions', [TransaksiController::class, 'getUserTransactions']);
            Route::post('/cancel', [TransaksiController::class, 'cancelTransaction']);
        });
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