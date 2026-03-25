<?php

use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CashController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('pos.index');
});

Route::middleware(['auth'])->group(function () {
    // POS Routes
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/search-products', [PosController::class, 'searchProducts'])->name('search.products');
        Route::get('/search-student', [PosController::class, 'searchStudent'])->name('search.student');
        Route::post('/process-sale', [PosController::class, 'processSale'])->name('process.sale');
    });

    // Products Routes
    Route::resource('products', ProductController::class);

    // Students Routes
    Route::resource('students', StudentController::class);

    // Sales Routes
    Route::resource('sales', SaleController::class);

    // Cash Management Routes
    Route::prefix('cash')->name('cash.')->group(function () {
        Route::get('/', [CashController::class, 'index'])->name('index');
        Route::post('/open', [CashController::class, 'open'])->name('open');
        Route::post('/close', [CashController::class, 'close'])->name('close');
        Route::get('/movements', [CashController::class, 'movements'])->name('movements');
    });
});
