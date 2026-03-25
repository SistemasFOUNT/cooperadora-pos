<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('pos.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // POS Routes
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/search/products', [ProductController::class, 'search'])->name('pos.search.products');
    Route::get('/pos/search/student', [StudentController::class, 'search'])->name('pos.search.student');
    Route::post('/pos/process/sale', [SaleController::class, 'store'])->name('pos.process.sale');

    // Sales Routes
    Route::post('/pos/sales', [SaleController::class, 'store'])->name('pos.sales.store');

    // Product Routes
    Route::resource('products', ProductController::class);
    Route::get('/api/products/search', [ProductController::class, 'search'])->name('products.search');

    // Student Routes
    Route::resource('students', StudentController::class);
    Route::get('/api/students/search', [StudentController::class, 'search'])->name('students.search');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
