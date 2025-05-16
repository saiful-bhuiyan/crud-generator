<?php

require base_path('routes/channels.php');

use App\Http\Controllers\GeneralSettingController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Middleware\TrackUserActivity;


// Route::get('/', function () {
//     return view('admin.layouts.master');
// });

Auth::routes();
Broadcast::routes(['middleware' => ['auth']]);

Route::get('logout',function(){
    Auth::logout();
    return redirect('/');
});

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');

Route::middleware(['auth',TrackUserActivity::class])->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('users', UserController::class);
    Route::resource('menus', MenuController::class);
    Route::resource('profile', ProfileController::class)->only('index','store');
    Route::resource('general-settings', GeneralSettingController::class)->only('index','store');

    
});
