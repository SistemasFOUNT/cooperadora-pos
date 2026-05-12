<?php

use App\Http\Controllers\ContabilidadController;

// Dashboard contable
Route::get('/dashboard', [ContabilidadController::class, 'dashboard'])
    ->name('contable.dashboard');

// Plan de cuentas
Route::get('/plan-cuentas', [ContabilidadController::class, 'planCuentas'])
    ->name('contable.plan-cuentas');

// Búsqueda de cuentas
Route::get('/buscar-cuentas', [ContabilidadController::class, 'buscarCuentas'])
    ->name('contable.buscar-cuentas');

// Asientos contables
Route::get('/asientos', [ContabilidadController::class, 'asientos'])
    ->name('contable.asientos');

Route::get('/asientos/{asiento}', [ContabilidadController::class, 'verAsiento'])
    ->name('contable.ver-asiento');

// Reportes contables
Route::get('/balance-comprobacion', [ContabilidadController::class, 'balanceComprobacion'])
    ->name('contable.balance-comprobacion');

Route::get('/libro-mayor', [ContabilidadController::class, 'libroMayor'])
    ->name('contable.libro-mayor');

Route::get('/estado-cuentas', [ContabilidadController::class, 'estadoCuentas'])
    ->name('contable.estado-cuentas');

Route::get('/reporte-ventas', [ContabilidadController::class, 'reporteVentas'])
    ->name('contable.reporte-ventas');

// LIBROS CONTABLES
Route::get('/libro-diario', [ContabilidadController::class, 'libroDiario'])
    ->name('contable.libro-diario');

Route::get('/libro-caja', [ContabilidadController::class, 'libroCaja'])
    ->name('contable.libro-caja');

Route::get('/libro-banco', [ContabilidadController::class, 'libroBanco'])
    ->name('contable.libro-banco');

Route::get('/resumen-caja', [ContabilidadController::class, 'resumenCaja'])
    ->name('contable.resumen-caja');
