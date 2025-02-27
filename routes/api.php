<?php

use App\Http\Controllers\Api\cartController;
use App\Http\Controllers\Api\productsController;
use App\Http\Controllers\Api\storeController;
use App\Http\Controllers\authController;
use App\Http\Middleware\CheckTypeCompany;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});

RateLimiter::for('user_actions', function (Request $request) {
    return Limit::perMinute(60)->by($request->ip()); // Máximo 60 peticiones por IP en 1 minuto
});

Route::controller(authController::class)->group(function () {
    Route::post('/auth/register', 'register');
    Route::post('/auth/login', 'login');
    Route::post('auth/logout', 'logout')->middleware('auth:sanctum');
})->middleware('throttle:login');

Route::middleware(['auth:sanctum', 'throttle:user_actions'])->group(function () {
    Route::controller(storeController::class)
        ->middleware(CheckTypeCompany::class . ':2') // Middleware agregado correctamente
        ->group(function () {
            Route::get('/stores', 'allBySeller');
            Route::get('/stores/{id}', 'show');
            Route::post('/stores', 'store');
            Route::put('/stores/{id}', 'update');
            Route::patch('/stores/{id}/status', 'updateStatus');
        });

    Route::controller(productsController::class)
        ->middleware(CheckTypeCompany::class . ':2') // Middleware agregado correctamente con parámetro
        ->group(function () {
            Route::get('/products', 'allBySeller');
            Route::get('/products/store/{id}', 'allByStore');
            Route::get('/products/{id}', 'show');
            Route::post('/products', 'store');
            Route::put('/products/{id}', 'update');
            Route::patch('/products/{id}/status', 'updateStatus');
        });

    Route::controller(cartController::class)
        ->middleware(CheckTypeCompany::class . ':1') // Middleware agregado correctamente con parámetro
        ->group(function () {
            Route::get('/carts/all', 'allByCustomers');
            Route::get('/carts', 'current');
            Route::post('/carts', 'addProduct');
            Route::delete('/carts', 'deleteProduct');
        });
});
