<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\ToppingController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\AdminDashboardController;

    // Auth Admin
    Route::post('/admin/login', [AuthController::class, 'login']);

    // Katalog pelanggan
    Route::get('/menu', [MenuController::class, 'index']);
    Route::get('/topping', [ToppingController::class, 'index']);

    // Transaksi Pelanggan
    Route::prefix('checkout')->group(function () {
        Route::post('/', [TransactionController::class, 'store']); 
        Route::patch('/{id}/pay', [TransactionController::class, 'payQris']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('admin')->group(function () {
        // Kelola Menu & Topping (CRUD Admin)
        Route::apiResource('menu', MenuController::class)->except(['index', 'show']);
        Route::apiResource('topping', ToppingController::class)->except(['index']);

        // Rute Khusus Dashboard & Laporan
        Route::get('/stats', [AdminDashboardController::class, 'stats']);
        Route::get('/history', [AdminDashboardController::class, 'history']);
        Route::get('/print/{id}', [AdminDashboardController::class, 'printReceipt']);

        // Kelola Transaksi (Apply/Konfirmasi Selesai)
        Route::patch('/transactions/{id}/apply', [TransactionController::class, 'apply']);
    });
});