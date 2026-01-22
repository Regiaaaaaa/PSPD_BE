<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\PetugasController;
use App\Http\Controllers\Api\Admin\KelolaUserController;
use App\Http\Controllers\Api\Auth\SiswaController;
use App\Http\Controllers\Api\Auth\StaffController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\BookController;


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

// Login Petugas ( Admin & Operator )
Route::post('/petugas/login', [PetugasController::class, 'login']);

// Login Regiter Siswa
Route::post('/siswa/register', [SiswaController::class, 'register']);
Route::post('/siswa/login', [SiswaController::class, 'login']);

// Login Register Staff
Route::post('staff/register', [StaffController::class, 'register']);
Route::post('staff/login', [StaffController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware(['auth:sanctum', 'isAdmin'])->prefix('admin')->group(function () {

        // Kelola Akun User ( Pengguna )
        Route::get('/users', [KelolaUserController::class, 'index']);
        Route::get('/users/{id}', [KelolaUserController::class, 'show']);
        Route::post('/users/operator', [KelolaUserController::class, 'createOperator']);
        Route::post('/users/staff', [KelolaUserController::class, 'createStaff']);
        Route::post('/users/siswa', [KelolaUserController::class, 'createSiswa']);
        Route::put('/users/{id}', [KelolaUserController::class, 'update']);
        Route::delete('/users/{id}', [KelolaUserController::class, 'destroy']);

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