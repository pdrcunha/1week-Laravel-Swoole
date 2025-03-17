<?php

use App\Http\Controllers\AdminManager\CompanyManagerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;

/**
 * PING
 */
Route::get('v1/ping', function () {
    return response()->json(['message' => 'pong']);
});

Route::prefix('v1/auth')->group(function () {
    Route::post('/', [AuthController::class, 'login']);
});


Route::middleware(['api.auth'])->prefix('v1/auth')->group(function () {
    Route::put('me', [AuthController::class, 'updateMe']);
    Route::get('me', [AuthController::class, 'me']);
});

/**
 * Companies Admin
 */
Route::apiResource('v1/companies-admin', CompanyManagerController::class)->middleware(['api.auth']);

/**
 * Companies
 */
Route::apiResource('v1/companies', CompanyController::class)->except(['store'])->middleware(['api.auth']);

/**
 * Users
 */
Route::apiResource('v1/users', UserController::class)->middleware(['api.auth']);

Route::apiResource('v1/products', ProductController::class)->middleware(['api.auth']);
