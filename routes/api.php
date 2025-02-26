<?php

use App\Http\Controllers\authController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip()); 
});

Route::controller(authController::class)->group(function () {
    Route::post('/auth/register', 'register');
    Route::post('/auth/login', 'login');
    Route::post('auth/logout', 'logout')->middleware('auth:sanctum');
})->middleware('throttle:login');