<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashEntryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('masuk', [AuthController::class, 'show'])->name('login');
    Route::post('masuk', [AuthController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('keluar', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('kasir', [SaleController::class, 'create'])->name('kasir.create');
    Route::post('kasir', [SaleController::class, 'store'])->name('kasir.store');

    Route::get('transaksi', [SaleController::class, 'index'])->name('transaksi.index');
    Route::get('transaksi/{sale}', [SaleController::class, 'show'])->name('transaksi.show');

    Route::get('barang', [ProductController::class, 'index'])->name('barang.index');
    Route::get('barang/baru', [ProductController::class, 'create'])->name('barang.create');
    Route::post('barang', [ProductController::class, 'store'])->name('barang.store');
    Route::get('barang/{product}/ubah', [ProductController::class, 'edit'])->name('barang.edit');
    Route::put('barang/{product}', [ProductController::class, 'update'])->name('barang.update');
    Route::delete('barang/{product}', [ProductController::class, 'destroy'])->name('barang.destroy');

    Route::get('stok', [StockController::class, 'index'])->name('stok.index');
    Route::post('stok', [StockController::class, 'store'])->name('stok.store');

    Route::get('kas', [CashEntryController::class, 'index'])->name('kas.index');
    Route::post('kas', [CashEntryController::class, 'store'])->name('kas.store');
    Route::delete('kas/{cashEntry}', [CashEntryController::class, 'destroy'])->name('kas.destroy');

    Route::get('laporan', ReportController::class)->name('laporan.index');
});
