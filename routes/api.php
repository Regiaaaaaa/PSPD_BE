<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\OtpController;
use App\Http\Controllers\Api\ProfilePetugasController;

use App\Http\Controllers\Api\Admin\ManageUserController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\BookController;


use App\Http\Controllers\Api\Operator\VerifikasiController;
use App\Http\Controllers\Api\Operator\PengembalianController;
use App\Http\Controllers\Api\Operator\DendaController;
use App\Http\Controllers\Api\Operator\LaporanController;


use App\Http\Controllers\Api\Users\ProfileController;
use App\Http\Controllers\Api\Users\TransaksiController;
use App\Http\Controllers\Api\Users\katalogController;

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

        // Profile Admin
        Route::put('/profile', [ProfilePetugasController::class, 'update']);
        Route::post('/profile/change-password', [ProfilePetugasController::class, 'changePassword']);

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


Route::middleware(['auth:sanctum', 'role:operator'])->prefix('operator')->group(function () {

        // Profile Operator
        Route::put('/profile', [ProfilePetugasController::class, 'update']);
        Route::post('/profile/change-password', [ProfilePetugasController::class, 'changePassword']);

        // Verifikasi
        Route::get('/verifikasi', [VerifikasiController::class, 'index']);
        Route::patch('/verifikasi/{id}/approve', [VerifikasiController::class, 'approve']);
        Route::patch('/verifikasi/{id}/reject', [VerifikasiController::class, 'reject']);

        // Pengembalian 
        Route::get('/pengembalian', [PengembalianController::class, 'index']);
        Route::patch('/pengembalian/{id}/terima', [PengembalianController::class, 'terima']);

        // Denda
        Route::get('/denda', [DendaController::class, 'index']);
        Route::patch('/denda/{id}/bayar', [DendaController::class, 'bayar']);

        // Laporan 
        Route::get('/laporan/transaksi', [LaporanController::class, 'transaksi']);
        Route::get('/laporan/denda', [LaporanController::class, 'denda']);
        Route::get('/laporan/summary', [LaporanController::class, 'summary']);

        Route::get('/laporan/transaksi/export/excel',[LaporanController::class,'exportTransaksiExcel']);
        Route::get('/laporan/transaksi/export/pdf',[LaporanController::class,'exportTransaksiPdf']);

        Route::get('/laporan/denda/export/excel',[LaporanController::class,'exportDendaExcel']);
        Route::get('/laporan/denda/export/pdf',[LaporanController::class,'exportDendaPdf']);

        Route::get('/laporan/summary/export/excel',[LaporanController::class,'exportSummaryExcel']);
        Route::get('/laporan/summary/export/pdf',[LaporanController::class,'exportSummaryPdf']);
    });
    



// Route Users ( Staff & Siswa )
Route::middleware(['auth:sanctum', 'role:staff,siswa'])->prefix('user')->group(function () {

        // Profile Users
        Route::put('/profile', [ProfileController::class, 'updateProfile']);
        Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);


        // Transaksi 
        Route::get('/transaksi', [TransaksiController::class, 'index']);
        Route::post('/transaksi/{buku}', [TransaksiController::class, 'store']);
        Route::get('/transaksi/{id}', [TransaksiController::class, 'show']);
        Route::patch('/transaksi/{id}/cancel', [TransaksiController::class, 'cancel']);

        // Katalog Buku
        Route::get('/katalog', [KatalogController::class, 'index']);
        Route::get('/katalog/{id}', [KatalogController::class, 'show']);
        
    });




