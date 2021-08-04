<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerification;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Unauthenticated routes
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/signup', [AuthController::class, 'signup'] );

// Authenticated routes
Route::middleware(['auth:api'])->group(function () {
    Route::get('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/refresh', [AuthController::class, 'refresh']);
    Route::get('email/notice', [EmailVerification::class, 'notice'])->name('verification.notice');
    Route::get('email/send', [EmailVerification::class, 'send'])->name('verification.send');
});

// Authenticated but not verified routes
Route::middleware(['auth:api', 'signed'])->group(function () {
    Route::get('email/verify/{id}/{hash}', [EmailVerification::class, 'verify'])->name('verification.verify');
});

// Authenticated and verified routes
Route::middleware(['auth:api', 'verified'])->group(function () {
    Route::get('auth/profile', [AuthController::class, 'profile']);
});

// Admin routes
Route::middleware(['auth:api', 'verified', 'role:admin'])->group(function (){
    Route::get('user', [UserController::class, 'getAll']);
    Route::get('user/{id}', [UserController::class, 'getById']);
    Route::post('user/by', [UserController::class, 'getBy']);
    Route::post('user', [UserController::class, 'create']);
    Route::put('user', [UserController::class, 'update']);
    Route::delete('user/{id}', [UserController::class, 'delete']);
});