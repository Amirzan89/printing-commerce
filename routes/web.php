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
use App\Http\Controllers\Services\EditorController;
use App\Http\Controllers\Mobile\UserController;

use App\Http\Controllers\Page\PublicController AS ShowHomeController;
use App\Http\Controllers\Page\JasaController AS ShowJasaController;
use App\Http\Controllers\Page\PesananController AS ShowPesananController;
use App\Http\Controllers\Page\MetodePembayaranController AS ShowMetodePembayaranController;
use App\Http\Controllers\Page\TransaksiController AS ShowTransaksiController;
use App\Http\Controllers\Page\ReviewController AS ShowReviewController;
use App\Http\Controllers\Page\AdminController AS ShowAdminController;
use App\Http\Controllers\Page\EditorController AS ShowEditorController;
use App\Http\Controllers\Page\UserController AS ShowUserController;

Route::group(['middleware'=>['auth:sanctum','authorize']], function(){
    //API only jasa route
    Route::group(['prefix'=>'/jasa'], function(){
        //page jasa
        Route::get('/',[ShowJasaController::class,'showAll'])->name('jasa.index');
        Route::get('/tambah',[ShowJasaController::class,'showTambah'])->name('jasa.create');
        Route::get('/edit/{any}',[ShowJasaController::class,'showEdit'])->name('jasa.edit');
        Route::get('/edit', function(){
            return redirect('/jasa');
        });
        // route for jasa
        Route::post('/create',[JasaController::class,'createJasa'])->name('api.jasa.store');
        Route::put('/update',[JasaController::class,'updateJasa'])->name('api.jasa.update');
        Route::delete('/delete',[JasaController::class,'deleteJasa'])->name('api.jasa.destroy');
    });

    //API only pesanan route
    Route::group(['prefix'=>'/pesanan'], function(){
        //page pesanan
        Route::get('/',[ShowPesananController::class,'showAll']);
        Route::get('/detail/{any}',[ShowPesananController::class,'showDetail']);
        Route::get('/tambah',[ShowPesananController::class,'showTambah']);
        Route::get('/edit/{any}',[ShowPesananController::class,'showEdit']);
        Route::get('/edit', function(){
            return redirect('/pesanan');
        });
        // route for pesanan
        Route::post('/create',[PesananController::class,'createPesanan']);
        Route::put('/update',[PesananController::class,'updatePesanan']);
        Route::delete('/delete',[PesananController::class,'deletePesanan']);
    });


    //API only metode pembayaran route
    Route::group(['prefix'=>'/metode-pembayaran'], function(){
        //page metode pembayaran
        Route::get('/',[ShowMetodePembayaranController::class,'showAll']);
        Route::get('/detail/{any}',[ShowMetodePembayaranController::class,'showDetail']);
        Route::get('/tambah',[ShowMetodePembayaranController::class,'showTambah']);
        Route::get('/edit/{any}',[ShowMetodePembayaranController::class,'showEdit']);
        Route::get('/edit', function(){
            return redirect('/metode-pembayaran');
        });
        // route for metode pembayaran
        Route::post('/create',[MetodePembayaranController::class,'createMPembayaran']);
        Route::put('/update',[MetodePembayaranController::class,'updateMPembayaran']);
        Route::delete('/delete',[MetodePembayaranController::class,'deleteMPembayaran']);
    });

    //API only transaksi route
    Route::group(['prefix'=>'/transaksi'], function(){
        //page transaksi
        Route::get('/',[ShowTransaksiController::class,'showAll']);
        Route::get('/detail/{any}',[ShowTransaksiController::class,'showDetail']);
        Route::get('/detail', function(){
            return redirect('/transaksi');
        });
        // route for transaksi
        Route::post('/update',[ShowTransaksiController::class,'validateTransaksi']);
    });

    //API only user route
    Route::group(['prefix'=>'/user'], function(){
        //page user
        Route::get('/',[ShowUserController::class,'showAll']);
        Route::get('/detail/{any}',[ShowUserController::class,'showDetail']);
        Route::get('/tambah',[ShowUserController::class,'showTambah']);
        Route::get('/edit', function(){
            return redirect('/user');
        });
        // route for user
        Route::post('/create',[UserController::class,'createUser']);
        Route::delete('/delete',[UserController::class,'deleteUser']);
    });

    //API only editor route
    Route::group(['prefix'=>'/editor'], function(){
        //page editor
        Route::get('/',[ShowEditorController::class,'showAll']);
        Route::get('/detail/{any}',[ShowEditorController::class,'showDetail']);
        Route::get('/tambah',[ShowEditorController::class,'showTambah']);
        Route::get('/edit/{any}',[ShowEditorController::class,'showEdit']);
        Route::get('/edit', function(){
            return redirect('/editor');
        });
        // route for editor
        Route::post('/create',[EditorController::class,'createEditor']);
        Route::put('/update',[EditorController::class,'updateEditor']);
        Route::delete('/delete',[EditorController::class,'deleteEditor']);
    });

    Route::group(['prefix'=>'/admin'], function(){
        //page admin
        Route::get('/',[ShowAdminController::class,'showAll']);
        Route::get('/tambah',[ShowAdminController::class,'showTambah']);
        Route::get('/edit/{any}',[ShowAdminController::class,'showEdit']);
        Route::get('/edit', function(){
            return redirect('/admin');
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
    Route::get('/', function(){
        return redirect('/login');
    });
});