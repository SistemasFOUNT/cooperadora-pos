<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ContabilidadController;
use App\Http\Controllers\PuntoVentaController;
use App\Http\Controllers\BoxController;
use App\Http\Controllers\PostgradoController;
use App\Http\Controllers\OdontoController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = request()->user();

    if (!$user) {
        return redirect()->route('login');
    }

    // Redirigir automáticamente al dashboard específico del punto de venta
    if ($user->isAdmin()) {
        // Admin va a dashboard administrativo general con acceso a todo
        return view('admin.dashboard', [
            'user' => $user,
            'isAdmin' => true,
            'estadisticas_generales' => [
                'total_productos' => \App\Models\Product::count(),
                'total_estudiantes' => \App\Models\Student::count(),
                'total_puntos_venta' => \App\Models\PuntoVenta::count(),
                'usuarios_activos' => \App\Models\User::where('role', '!=', 'admin')->count(),
            ]
        ]);
    } else {
        // Usuarios específicos van a su dashboard correspondiente
        switch ($user->role) {
            case 'usuario_box':
                return redirect()->route('box.dashboard');
            case 'usuario_postgrado':
                return redirect()->route('postgrado.dashboard');
            case 'usuario_odonto':
                return redirect()->route('odonto.dashboard');
            default:
                // Fallback para casos no contemplados
                return view('dashboard', [
                    'user' => $user,
                    'puntoVenta' => $user->puntoVenta,
                    'isAdmin' => false
                ]);
        }
    }
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Rutas específicas para Admin (superusuario)
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');

        // Estadísticas y reportes generales
        Route::get('/estadisticas-generales', function () {
            return view('admin.estadisticas', [
                'estadisticas' => [
                    'ventas_box' => \App\Models\Sale::whereHas('user', function($q) {
                        $q->where('role', 'usuario_box');
                    })->count(),
                    'estudiantes_postgrado' => \App\Models\Student::whereHas('user', function($q) {
                        $q->where('role', 'usuario_postgrado');
                    })->count(),
                    'pacientes_odonto' => \App\Models\User::where('role', 'usuario_odonto')->count(),
                    'total_ingresos' => \App\Models\Sale::sum('total'),
                ]
            ]);
        })->name('estadisticas');

        // Supervisión general de puntos de venta
        Route::get('/box-supervision', [BoxController::class, 'adminSupervision'])->name('box.supervision');
        Route::get('/postgrado-supervision', [PostgradoController::class, 'adminSupervision'])->name('postgrado.supervision');
        Route::get('/odonto-supervision', [OdontoController::class, 'adminSupervision'])->name('odonto.supervision');

        // Ingresos y Egresos por punto de venta
        Route::get('/box/ingresos-egresos', [BoxController::class, 'adminIngresosEgresos'])->name('box.ingresos-egresos');
        Route::get('/postgrado/ingresos-egresos', [PostgradoController::class, 'adminIngresosEgresos'])->name('postgrado.ingresos-egresos');
        Route::get('/odonto/ingresos-egresos', [OdontoController::class, 'adminIngresosEgresos'])->name('odonto.ingresos-egresos');

        // Libro Caja por punto de venta
        Route::get('/box/libro-caja', [BoxController::class, 'adminLibroCaja'])->name('box.libro-caja');
        Route::get('/postgrado/libro-caja', [PostgradoController::class, 'adminLibroCaja'])->name('postgrado.libro-caja');
        Route::get('/odonto/libro-caja', [OdontoController::class, 'adminLibroCaja'])->name('odonto.libro-caja');

        // Herramientas administrativas generales
        Route::get('/cuentas/general', function () {
            return view('admin.cuentas.general');
        })->name('cuentas.general');

        Route::get('/libro-caja-general', function () {
            return view('admin.libro-caja-general');
        })->name('libro-caja-general');

        // Autorizaciones de pago
        Route::get('/autorizaciones-pagos', function () {
            return view('admin.autorizaciones.index');
        })->name('autorizaciones-pagos');

        Route::post('/autorizaciones-pagos/{id}/autorizar', function ($id) {
            // Lógica para autorizar pago
            return back()->with('success', 'Pago autorizado correctamente');
        })->name('autorizaciones.autorizar');

        Route::post('/autorizaciones-pagos/{id}/rechazar', function ($id) {
            // Lógica para rechazar pago
            return back()->with('error', 'Pago rechazado');
        })->name('autorizaciones.rechazar');

        // Gestión de usuarios
        Route::get('/usuarios', function () {
            return view('admin.usuarios.index');
        })->name('usuarios');
    });

    // POS Routes - aplicar middleware punto_venta
    Route::middleware('punto_venta')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/search/products', [ProductController::class, 'search'])->name('pos.search.products');
        Route::get('/pos/search/student', [StudentController::class, 'search'])->name('pos.search.student');
        Route::post('/pos/process/sale', [SaleController::class, 'store'])->name('pos.process.sale');

        // Sales Routes
        Route::post('/pos/sales', [SaleController::class, 'store'])->name('pos.sales.store');
    });

    // Product Routes
    Route::resource('products', ProductController::class);
    Route::get('/api/products/search', [ProductController::class, 'search'])->name('products.search');

    // Student Routes
    Route::resource('students', StudentController::class);
    Route::get('/api/students/search', [StudentController::class, 'search'])->name('students.search');

    // Contabilidad Routes - aplicar middleware punto_venta
    Route::prefix('contabilidad')->middleware('punto_venta')->group(function () {
        Route::get('/plan-cuentas', [ContabilidadController::class, 'planCuentas'])->name('contabilidad.plan-cuentas');
        Route::get('/buscar-cuentas', [ContabilidadController::class, 'buscarCuentas'])->name('contabilidad.buscar-cuentas');

        // Puntos de Venta
        Route::get('/puntos-venta', [PuntoVentaController::class, 'index'])->name('contabilidad.puntos-venta.index');
        Route::get('/puntos-venta/{id}', [PuntoVentaController::class, 'show'])->name('contabilidad.puntos-venta.show');
        Route::get('/puntos-venta/{id}/asiento-demo', [PuntoVentaController::class, 'asientoDemo'])->name('contabilidad.puntos-venta.asiento-demo');
        Route::get('/puntos-venta/estadisticas', [PuntoVentaController::class, 'estadisticas'])->name('contabilidad.puntos-venta.estadisticas');
    });

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ===== RUTAS ESPECÍFICAS POR PUNTO DE VENTA =====

    // BOX COOPERADORA - Rutas específicas
    Route::prefix('box')->middleware('punto_venta')->group(function () {
        Route::get('/dashboard', [BoxController::class, 'dashboard'])->name('box.dashboard');
        Route::get('/pos', [BoxController::class, 'pos'])->name('box.pos');
        Route::get('/productos', [BoxController::class, 'productos'])->name('box.productos');
        Route::get('/ventas-del-dia', [BoxController::class, 'ventasDelDia'])->name('box.ventas-del-dia');
        Route::get('/reportes', [BoxController::class, 'reportes'])->name('box.reportes');
        Route::get('/configuracion', [BoxController::class, 'configuracion'])->name('box.configuracion');
    });

    // POSTGRADO - Rutas específicas
    Route::prefix('postgrado')->middleware('punto_venta')->group(function () {
        Route::get('/dashboard', [PostgradoController::class, 'dashboard'])->name('postgrado.dashboard');
        Route::get('/estudiantes', [PostgradoController::class, 'estudiantes'])->name('postgrado.estudiantes');
        Route::get('/pos', [PostgradoController::class, 'pos'])->name('postgrado.pos');
        Route::get('/matriculas', [PostgradoController::class, 'matriculas'])->name('postgrado.matriculas');
        Route::get('/cursos', [PostgradoController::class, 'cursos'])->name('postgrado.cursos');
        Route::get('/certificados', [PostgradoController::class, 'certificados'])->name('postgrado.certificados');
        Route::get('/reportes', [PostgradoController::class, 'reportes'])->name('postgrado.reportes');
        Route::get('/configuracion', [PostgradoController::class, 'configuracion'])->name('postgrado.configuracion');
    });

    // CENTRO ODONTOLÓGICO - Rutas específicas
    Route::prefix('odonto')->middleware('punto_venta')->group(function () {
        Route::get('/dashboard', [OdontoController::class, 'dashboard'])->name('odonto.dashboard');
        Route::get('/pacientes', [OdontoController::class, 'pacientes'])->name('odonto.pacientes');
        Route::get('/agenda', [OdontoController::class, 'agenda'])->name('odonto.agenda');
        Route::get('/pos', [OdontoController::class, 'pos'])->name('odonto.pos');
        Route::get('/tratamientos', [OdontoController::class, 'tratamientos'])->name('odonto.tratamientos');
        Route::get('/inventario', [OdontoController::class, 'inventario'])->name('odonto.inventario');
        Route::get('/historiales', [OdontoController::class, 'historiales'])->name('odonto.historiales');
        Route::get('/facturacion', [OdontoController::class, 'facturacion'])->name('odonto.facturacion');
        Route::get('/reportes', [OdontoController::class, 'reportes'])->name('odonto.reportes');
        Route::get('/configuracion', [OdontoController::class, 'configuracion'])->name('odonto.configuracion');
    });

    // ===== RUTAS ADMINISTRATIVAS =====
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // Dashboard administrativo
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Supervisión general y por puntos
        Route::prefix('supervision')->name('supervision.')->group(function () {
            Route::get('/general', [AdminController::class, 'supervisionGeneral'])->name('general');
            Route::get('/box', [BoxController::class, 'adminSupervision'])->name('box');
            Route::get('/postgrado', [PostgradoController::class, 'adminSupervision'])->name('postgrado');
            Route::get('/odonto', [OdontoController::class, 'adminSupervision'])->name('odonto');
        });

        // Control de Ingresos y Egresos
        Route::prefix('ingresos-egresos')->name('ingresos-egresos.')->group(function () {
            Route::get('/consolidado', [AdminController::class, 'ingresosEgresosConsolidado'])->name('consolidado');
            Route::get('/box', [BoxController::class, 'adminIngresosEgresos'])->name('box');
            Route::get('/postgrado', [PostgradoController::class, 'adminIngresosEgresos'])->name('postgrado');
            Route::get('/odonto', [OdontoController::class, 'adminIngresosEgresos'])->name('odonto');
        });

        // Libro Caja
        Route::prefix('libro-caja')->name('libro-caja.')->group(function () {
            Route::get('/consolidado', [AdminController::class, 'libroCajaConsolidado'])->name('consolidado');
            Route::get('/box', [BoxController::class, 'adminLibroCaja'])->name('box');
            Route::get('/postgrado', [PostgradoController::class, 'adminLibroCaja'])->name('postgrado');
            Route::get('/odonto', [OdontoController::class, 'adminLibroCaja'])->name('odonto');
        });

        // Autorizaciones
        Route::prefix('autorizaciones')->name('autorizaciones.')->group(function () {
            Route::get('/', [AdminController::class, 'autorizacionesIndex'])->name('index');
            Route::get('/historial', [AdminController::class, 'autorizacionesHistorial'])->name('historial');
            Route::post('/{id}/aprobar', [AdminController::class, 'aprobarAutorizacion'])->name('aprobar');
            Route::post('/{id}/rechazar', [AdminController::class, 'rechazarAutorizacion'])->name('rechazar');
        });

        // Estados de Cuenta
        Route::prefix('cuentas')->name('cuentas.')->group(function () {
            Route::get('/estado-general', [AdminController::class, 'estadoGeneral'])->name('estado-general');
            Route::get('/particular', [AdminController::class, 'estadoParticular'])->name('particular');
            Route::post('/particular/buscar', [AdminController::class, 'buscarEstadoParticular'])->name('buscar-particular');
        });

        // Reportes
        Route::get('/reportes/consolidado', [AdminController::class, 'reportesConsolidado'])->name('reportes.consolidado');
    });

    // ===== RUTAS CRUD EN ESPAÑOL =====

    // Rutas para Estudiantes
    Route::prefix('estudiantes')->name('estudiantes.')->group(function () {
        Route::get('/', [App\Http\Controllers\EstudianteController::class, 'index'])->name('index');
        Route::get('/crear', [App\Http\Controllers\EstudianteController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\EstudianteController::class, 'store'])->name('store');
        Route::get('/{estudiante}', [App\Http\Controllers\EstudianteController::class, 'show'])->name('show');
        Route::get('/{estudiante}/editar', [App\Http\Controllers\EstudianteController::class, 'edit'])->name('edit');
        Route::put('/{estudiante}', [App\Http\Controllers\EstudianteController::class, 'update'])->name('update');
        Route::delete('/{estudiante}', [App\Http\Controllers\EstudianteController::class, 'destroy'])->name('destroy');

        // Rutas adicionales
        Route::get('/importar', [App\Http\Controllers\EstudianteController::class, 'importar'])->name('importar');
        Route::post('/procesar-importacion', [App\Http\Controllers\EstudianteController::class, 'procesarImportacion'])->name('procesar-importacion');
        Route::post('/{estudiante}/toggle-estado', [App\Http\Controllers\EstudianteController::class, 'toggleEstado'])->name('toggle-estado');

        // API
        Route::get('/api/buscar', [App\Http\Controllers\EstudianteController::class, 'buscar'])->name('buscar');
    });

    // Rutas para Carreras
    Route::prefix('carreras')->name('carreras.')->group(function () {
        Route::get('/', [App\Http\Controllers\CarreraController::class, 'index'])->name('index');
        Route::get('/crear', [App\Http\Controllers\CarreraController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\CarreraController::class, 'store'])->name('store');
        Route::get('/{carrera}', [App\Http\Controllers\CarreraController::class, 'show'])->name('show');
        Route::get('/{carrera}/editar', [App\Http\Controllers\CarreraController::class, 'edit'])->name('edit');
        Route::put('/{carrera}', [App\Http\Controllers\CarreraController::class, 'update'])->name('update');
        Route::delete('/{carrera}', [App\Http\Controllers\CarreraController::class, 'destroy'])->name('destroy');

        // Rutas adicionales
        Route::get('/cuotas', [App\Http\Controllers\CarreraController::class, 'cuotas'])->name('cuotas');
        Route::post('/actualizar-cuotas', [App\Http\Controllers\CarreraController::class, 'actualizarCuotas'])->name('actualizar-cuotas');
        Route::post('/{carrera}/toggle-activa', [App\Http\Controllers\CarreraController::class, 'toggleActiva'])->name('toggle-activa');
    });

    // Rutas para Productos
    Route::prefix('productos')->name('productos.')->group(function () {
        Route::get('/', [App\Http\Controllers\ProductoController::class, 'index'])->name('index');
        Route::get('/crear', [App\Http\Controllers\ProductoController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\ProductoController::class, 'store'])->name('store');
        Route::get('/{producto}', [App\Http\Controllers\ProductoController::class, 'show'])->name('show');
        Route::get('/{producto}/editar', [App\Http\Controllers\ProductoController::class, 'edit'])->name('edit');
        Route::put('/{producto}', [App\Http\Controllers\ProductoController::class, 'update'])->name('update');
        Route::delete('/{producto}', [App\Http\Controllers\ProductoController::class, 'destroy'])->name('destroy');

        // Rutas adicionales
        Route::get('/categorias', [App\Http\Controllers\ProductoController::class, 'categorias'])->name('categorias');
        Route::get('/inventario', [App\Http\Controllers\ProductoController::class, 'inventario'])->name('inventario');
        Route::post('/{producto}/actualizar-stock', [App\Http\Controllers\ProductoController::class, 'actualizarStock'])->name('actualizar-stock');
        Route::post('/{producto}/toggle-activo', [App\Http\Controllers\ProductoController::class, 'toggleActivo'])->name('toggle-activo');
        // API
        Route::get('/api/buscar', [App\Http\Controllers\ProductoController::class, 'buscar'])->name('buscar');
        Route::get('/api/generar-codigo-barras', [App\Http\Controllers\ProductoController::class, 'generarCodigoBarras'])->name('generar-codigo-barras');
    });
});

require __DIR__.'/auth.php';
