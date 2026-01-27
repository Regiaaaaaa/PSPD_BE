<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\OtpController;

use App\Http\Controllers\Api\Admin\ManageUserController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\BookController;

use App\Http\Controllers\Api\Users\ProfileController;

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


// Auth 
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register/siswa', [AuthController::class, 'registerSiswa']);
Route::post('/register/staff', [AuthController::class, 'registerStaff']);

// Logout 
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


// OTP Forgot Password
Route::post('/send-otp', [OtpController::class, 'sendOtp']);
Route::post('/verify-otp', [OtpController::class, 'verifyOtp']);
Route::post('/reset-password', [OtpController::class, 'resetPassword']);

// Auth Cek
Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});


// Route Admin
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {

        // Kelola Akun User
        Route::get('/users', [ManageUserController::class, 'index']);
        Route::get('/users/{id}', [ManageUserController::class, 'show']);
        Route::post('/users/operator', [ManageUserController::class, 'createOperator']);
        Route::post('/users/staff', [ManageUserController::class, 'createStaff']);
        Route::post('/users/siswa', [ManageUserController::class, 'createSiswa']);
        Route::put('/users/{id}', [ManageUserController::class, 'update']);
        Route::delete('/users/{id}', [ManageUserController::class, 'destroy']);
        Route::post('/users/{id}/reset-password', [ManageUserController::class, 'resetPassword']);

        // Kelola Kategori
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::get('/categories/{id}', [CategoryController::class, 'show']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        // Kelola Buku
        Route::get('/books', [BookController::class, 'index']);
        Route::post('/books', [BookController::class, 'store']);
        Route::get('/books/{id}', [BookController::class, 'show']);
        Route::put('/books/{id}', [BookController::class, 'update']);
        Route::delete('/books/{id}', [BookController::class, 'destroy']);
    });


// Route Users ( Staff & Siswa )
Route::middleware(['auth:sanctum', 'role:staff,siswa'])->prefix('user')->group(function () {

        // Update Profile 
        Route::put('/profile', [ProfileController::class, 'updateProfile']);
        Route::put('/profile/change-password', [ProfileController::class, 'changePassword']);
    });



Route::middleware(['auth:sanctum', 'role:operator'])->prefix('operator')->group(function () {
    });
