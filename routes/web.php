<?php
use Illuminate\Support\Facades\Route;
Route::group(['middleware' => 'auth:sanctum'],function(){
    //artikel public route
    Route::group(['prefix'=>'/artikel', 'middleware'=>'throttle:artikel'],function(){
        Route::get('/',[ShowHomeController::class,'showArtikel']);
        Route::get('/{any}',[ShowHomeController::class,'showDetailArtikel']);
    });
    //download only for admin
    Route::group(['prefix' => '/public', 'middleware'=>'throttle:global'],function(){
        Route::group(['prefix'=>'/download'],function(){
            Route::group(['prefix'=>'/foto'],function(){
                Route::get('/',[AdminController::class,'getFotoProfile'])->name('download.foto');
                Route::get('/default',[AdminController::class,'getDefaultFoto'])->name('download.foto.default');
                Route::get('/{id}',[AdminController::class,'getFotoAdmin'])->name('download.foto.admin');
            });
        });
    });
    //API only admin route
    Route::group(['prefix'=>'/admin', 'middleware'=>'throttle:admin'],function(){
        //page admin
        Route::get('/',[ShowAdminController::class,'showAdmin']);
        Route::get('/tambah',[ShowAdminController::class,'showAdminTambah']);
        Route::get('/edit/{any}',[ShowAdminController::class,'showAdminEdit']);
        Route::get('/edit', function () {
            return view('page.admin.data');
        });
        // route for admin
        Route::post('/tambah',[AdminController::class,'tambahAdmin']);
        Route::put('/update',[AdminController::class,'editAdmin']);
        Route::delete('/delete',[AdminController::class,'hapusAdmin']);
        Route::post('/login',[LoginController::class,'Login']);
        Route::post('/logout',[AdminController::class,'logout']);
        Route::group(['prefix'=>'/update'],function(){
            Route::put('/profile', [AdminController::class, 'updateProfile']);
            Route::put('/password', [AdminController::class, 'updatePassword']);
        });
    });
    Route::group(["prefix"=>"/verify", 'middleware'=>'throttle:global'],function(){
        Route::group(['prefix'=>'/create'],function(){
            Route::post('/password',[MailController::class, 'createForgotPassword']);
        });
        Route::group(['prefix'=>'/password'],function(){
            Route::get('/{any?}',[AdminController::class, 'getChangePass'])->where('any','.*');
            Route::post('/',[AdminController::class, 'changePassEmail']);
        });
        Route::group(['prefix'=>'/otp'],function(){
            Route::post('/password',[AdminController::class, 'getChangePass']);
        });
    });
    Route::get('/password/reset',function (){
        return view('page.forgotPassword', ['title'=>'Lupa password']);
    })->middleware('throttle:global');
    Route::get('/login', function () {
        return view('page.login');
    })->middleware('throttle:global');
    // Route::get('/testing', function () {
    //     return view('page.testing');
    // });
    // Route::get('/template', function(){
    //     return view('page.template');
    // });
    Route::get('/dashboard',[ShowAdminController::class,'showDashboard'])->middleware('throttle:global');
    Route::get('/profile',[ShowAdminController::class,'showProfile'])->middleware('throttle:global');
    Route::get('/',[ShowHomeController::class,'showHome'])->middleware('throttle:global');
});