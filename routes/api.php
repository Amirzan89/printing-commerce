<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mobile\UserController;
use App\Http\Controllers\Mobile\Page\HomeController;
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['prefix'=>'/mobile','middleware'=>'authMobile','authorized'],function(){
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