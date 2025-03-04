<?php

use App\Http\Controllers\AdminManager\CompanyManagerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\JwtMiddleware;


Route::prefix('api/v1/auth')->group(function () {
    Route::post('/', [AuthController::class, 'login']);

    Route::middleware([JwtMiddleware::class])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/me', [AuthController::class, 'updateMe']);
    });
});

/**
 * Companies Admin
 */

Route::prefix('api/v1/companies-admin')->middleware([JwtMiddleware::class])->group(function () {
    Route::get('/', [CompanyManagerController::class, 'index']);
    Route::post('/', [CompanyManagerController::class, 'store']);
    Route::get('/{id}', [CompanyManagerController::class, 'show']);
    Route::put('/{id}', [CompanyManagerController::class, 'update']);
    Route::delete('/{id}', [CompanyManagerController::class, 'destroy']);
});

/**
 * Companies
 */

 Route::prefix('api/v1/companies')->middleware([JwtMiddleware::class])->group(function () {
    Route::get('/', [CompanyController::class, 'index']);
    Route::post('/', [CompanyController::class, 'store']);
    Route::get('/', [CompanyController::class, 'show']);
    Route::put('/', [CompanyController::class, 'update']);
    Route::delete('/', [CompanyController::class, 'destroy']);
});

/**
 * Users
 */

Route::prefix('api/v1/users')->middleware([JwtMiddleware::class])->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});