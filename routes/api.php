<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\ToppingController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\KategoriMenuController;
use App\Http\Controllers\Api\KategoriToppingController;

/*
|--------------------------------------------------------------------------
| API Routes - Seblak Say Cafe
|--------------------------------------------------------------------------
*/

// ==================== PUBLIC ROUTES (PELANGGAN) ====================

// Katalog Pelanggan (Data Menu & Topping)
Route::get('/menu', [MenuController::class, 'index']);
Route::get('/topping', [ToppingController::class, 'index']);
Route::get('/kategorimenu', [KategoriMenuController::class, 'index']);
Route::get('/kategoritopping', [KategoriToppingController::class, 'index']);

// Transaksi Pelanggan (Checkout)
Route::post('/checkout', [TransactionController::class, 'store']);

Route::get('/transactions/{id}', [TransactionController::class, 'show']);


// ==================== PROTECTED ROUTES (ADMIN / SANCTUM) ====================

// Auth Admin (Akses Awal)
Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/admin/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    
    // Auth Revoke
    Route::post('/logout', [AuthController::class, 'logout']);

    // Grouping Fitur Khusus Internal Admin
    Route::prefix('admin')->group(function () {
        
        // Kelola Menu & Topping (CRUD Admin)
        Route::apiResource('menu', MenuController::class)->except(['index', 'show']);
        Route::apiResource('topping', ToppingController::class)->except(['index']);

        // Rute Dashboard, Statistik, & Laporan Cetak Struk
        Route::get('/stats', [AdminDashboardController::class, 'stats']);
        Route::get('/history', [AdminDashboardController::class, 'history']);
        Route::get('/print/{id}', [AdminDashboardController::class, 'printReceipt']);

        // Kelola Validasi Transaksi Masuk (Sesuai Controller Baru)
        Route::prefix('transactions')->group(function () {
            // 1. Ambil detail pesanan & URL Bukti Transfer QRIS
            Route::get('/{id}', [TransactionController::class, 'show']);
            
            // 2. Setujui pesanan masuk (Ubah status ke 'selesai')
            Route::post('/{id}/apply', [TransactionController::class, 'apply']);
            
            // 3. Tolak pesanan masuk (Ubah status ke 'reject' & restock otomatis)
            Route::post('/{id}/reject', [TransactionController::class, 'reject']);
        });
        
    }); // Akhir dari prefix admin
    
}); // Akhir dari middleware sanctum    