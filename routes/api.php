<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\StaffController;
use App\Http\Controllers\Api\Admin\KelolaUserController;
use App\Http\Controllers\Api\Auth\SiswaController;
use App\Http\Controllers\Api\Auth\UmumController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Login Staff ( Admin & Operator )
Route::post('/staff/login', [StaffController::class, 'login']);

// Login Regiter Siswa
Route::post('/siswa/register', [SiswaController::class, 'register']);
Route::post('/siswa/login', [SiswaController::class, 'login']);

// Login Register Umum
Route::post('umum/register', [UmumController::class, 'register']);
Route::post('umum/login', [UmumController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware(['auth:sanctum', 'isAdmin'])->prefix('admin')->group(function () {

        // List user
        Route::get('/users', [KelolaUserController::class, 'index']);

        // Detail user
        Route::get('/users/{id}', [KelolaUserController::class, 'show']);

        // Create user
        Route::post('/users/operator', [KelolaUserController::class, 'createOperator']);
        Route::post('/users/siswa', [KelolaUserController::class, 'createSiswa']);
        Route::post('/users/umum', [KelolaUserController::class, 'createUmum']);

        // Update user
        Route::put('/users/{id}', [KelolaUserController::class, 'update']);

        // Delete user
        Route::delete('/users/{id}', [KelolaUserController::class, 'destroy']);
});