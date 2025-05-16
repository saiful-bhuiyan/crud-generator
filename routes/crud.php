<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\TrackUserActivity;

Route::middleware(['auth',TrackUserActivity::class])->group(function () {
    
});