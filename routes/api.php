<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
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

// Unauthenticated functions
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/signup', [AuthController::class, 'signup'] );

// Authenticated functions
Route::middleware(['auth:api'])->group(function () {
    Route::get('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/refresh', [AuthController::class, 'refresh']);
    Route::get('auth/whoami', [AuthController::class, 'whoami']);
});

// Admin functions
Route::middleware(['auth:api', 'role:admin'])->group(function (){

    // User functions
    Route::get('user', [UserController::class, 'getAll']);
    Route::get('user/{id}', [UserController::class, 'getById']);
    Route::post('user/by', [UserController::class, 'getBy']);
    Route::post('user', [UserController::class, 'create']);
    Route::put('user', [UserController::class, 'update']);
    Route::delete('user/{id}', [UserController::class, 'delete']);
});