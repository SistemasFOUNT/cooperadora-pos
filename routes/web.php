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
use App\Http\Controllers\CashController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\ConceptosController;
use Illuminate\Support\Facades\Route;

// Ruta para CSS dinámico con cache busting
Route::get('/css/custom-images.css', [AssetController::class, 'customImages'])
    ->name('assets.custom-images-css');

Route::get('/', function () {
    return redirect()->route('login');
});


Route::get('/dashboard', function () {
    $user = request()->user();
    if (!$user) {
        return redirect()->route('login');
    }
    // Si es admin, forzar middleware admin_menu y redirigir a ruta admin.dashboard
    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
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
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Rutas específicas para Admin (superusuario)
    Route::middleware(['admin', 'admin_menu'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');

        // Rutas canónicas del panel admin (alineadas con config/admin-menu.php)
        Route::get('/supervision/general', [AdminController::class, 'supervisionGeneral'])->name('supervision.general');
        Route::get('/ingresos-egresos/consolidado', [AdminController::class, 'ingresosEgresosConsolidado'])->name('ingresos-egresos.consolidado');
        Route::get('/libro-caja/consolidado', [AdminController::class, 'libroCajaConsolidado'])->name('libro-caja.consolidado');
        Route::get('/autorizaciones', [AdminController::class, 'autorizacionesIndex'])->name('autorizaciones.index');
        Route::get('/autorizaciones/historial', [AdminController::class, 'autorizacionesHistorial'])->name('autorizaciones.historial');
        Route::get('/auditoria', [AdminController::class, 'auditoriaIndex'])->name('auditoria.index');
        Route::get('/auditoria/{id}', [AdminController::class, 'auditoriaShow'])->name('auditoria.show');
        Route::get('/cuentas/estado-general', [AdminController::class, 'estadoGeneral'])->name('cuentas.estado-general');
        Route::get('/cuentas/particular', [AdminController::class, 'estadoParticular'])->name('cuentas.particular');
        Route::get('/reportes/consolidado', [AdminController::class, 'reportesConsolidado'])->name('reportes.consolidado');

        // Alias por punto de venta para mantener consistencia de nombres del menú
        Route::get('/supervision/box', [BoxController::class, 'adminSupervision'])->name('supervision.box');
        Route::get('/supervision/postgrado', [PostgradoController::class, 'adminSupervision'])->name('supervision.postgrado');
        Route::get('/supervision/odonto', [OdontoController::class, 'adminSupervision'])->name('supervision.odonto');
        Route::get('/ingresos-egresos/box', [BoxController::class, 'adminIngresosEgresos'])->name('ingresos-egresos.box');
        Route::get('/ingresos-egresos/postgrado', [PostgradoController::class, 'adminIngresosEgresos'])->name('ingresos-egresos.postgrado');
        Route::get('/ingresos-egresos/odonto', [OdontoController::class, 'adminIngresosEgresos'])->name('ingresos-egresos.odonto');
        Route::get('/libro-caja/box', [BoxController::class, 'adminLibroCaja'])->name('libro-caja.box');
        Route::get('/libro-caja/postgrado', [PostgradoController::class, 'adminLibroCaja'])->name('libro-caja.postgrado');
        Route::get('/libro-caja/odonto', [OdontoController::class, 'adminLibroCaja'])->name('libro-caja.odonto');

        // Estadísticas y reportes generales
        Route::get('/estadisticas-generales', function () {
            return view('admin.estadisticas', [
                'estadisticas' => [
                    'ventas_box' => \App\Models\Sale::whereHas('user', function ($q) {
                        $q->where('role', 'usuario_box');
                    })->count(),
                    'estudiantes_postgrado' => \App\Models\Student::whereHas('user', function ($q) {
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

        // Arqueo de Caja
        Route::prefix('arqueo')->name('arqueo.')->group(function () {
            Route::get('/', [CashController::class, 'index'])->name('index');
            Route::get('/{codigo}', [CashController::class, 'caja'])->name('caja');
            Route::get('/{codigo}/nuevo', [CashController::class, 'crear'])->name('crear');
            Route::post('/{codigo}/guardar', [CashController::class, 'guardar'])->name('guardar');
            Route::get('/{codigo}/{id}', [CashController::class, 'show'])->name('show');
            Route::post('/{codigo}/{id}/cerrar', [CashController::class, 'cerrar'])->name('cerrar');
        });

        // Contabilidad - Libros contables (Diario, Caja, Banco)
        Route::prefix('contable')->name('contable.')->group(function () {
            require __DIR__.'/contable.php';
        });
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
    Route::prefix('box')->middleware(['punto_venta', 'box_menu'])->group(function () {
        Route::get('/dashboard', [BoxController::class, 'dashboard'])->name('box.dashboard');
        Route::get('/pos', [BoxController::class, 'pos'])->name('box.pos');
        Route::get('/productos', [BoxController::class, 'productos'])->name('box.productos');
        Route::get('/ventas-del-dia', [BoxController::class, 'ventasDelDia'])->name('box.ventas-del-dia');
        Route::get('/reportes', [BoxController::class, 'reportes'])->name('box.reportes');
        Route::get('/configuracion', [BoxController::class, 'configuracion'])->name('box.configuracion');

        // Nuevas rutas para el menú específico de BOX
        Route::prefix('cobros')->group(function () {
            Route::get('/productos', [BoxController::class, 'cobrosProductos'])->name('box.cobros.productos');
            Route::post('/productos/ticket-pdf', [BoxController::class, 'generarTicketPDF'])->name('box.cobros.ticket-pdf');
            Route::get('/odontologia', [BoxController::class, 'cobrosOdontologia'])->name('box.cobros.odontologia');
            Route::get('/cuotas', [BoxController::class, 'cobrosCuotas'])->name('box.cobros.cuotas');
            Route::get('/cuotas/buscar', [BoxController::class, 'buscarCuotasPorEstudiante'])->name('box.cobros.cuotas.buscar');
            Route::post('/cuotas/registrar', [BoxController::class, 'registrarPagoCuota'])->name('box.cobros.cuotas.registrar');
            Route::get('/bonos', [BoxController::class, 'cobrosBonos'])->name('box.cobros.bonos');
            Route::get('/otros', [BoxController::class, 'cobrosOtros'])->name('box.cobros.otros');
        });

        // Rutas comunes de pago y tickets
        Route::post('/generar-ticket', [BoxController::class, 'generarTicketGeneral'])->name('box.generar-ticket');
        Route::post('/procesar-venta', [BoxController::class, 'procesarVenta'])->name('box.procesar-venta');

        Route::prefix('inventario')->group(function () {
            Route::get('/ingresos',                             [BoxController::class, 'inventarioIngresos'])->name('box.inventario.ingresos');
            Route::get('/productos',                            [ConceptosController::class, 'editarProductos'])->name('box.inventario.productos');
            Route::put('/producto/{product}',                   [ConceptosController::class, 'actualizarProductoCompleto'])->name('box.inventario.producto.update');
            Route::delete('/producto/{product}',                [ConceptosController::class, 'eliminarProducto'])->name('box.inventario.producto.destroy');
        });

        Route::prefix('pagos')->group(function () {
            Route::get('/proveedores', [BoxController::class, 'pagosProveedores'])->name('box.pagos.proveedores');
            Route::post('/proveedores/registrar', [BoxController::class, 'registrarPagoProveedor'])->name('box.pagos.proveedores.store');
            Route::post('/proveedores/alta', [BoxController::class, 'registrarProveedor'])->name('box.pagos.proveedores.proveedor.store');
            Route::get('/proveedores/{pago}/comprobante', [BoxController::class, 'descargarComprobantePagoProveedor'])->name('box.pagos.proveedores.comprobante');
            Route::get('/asignaciones', [BoxController::class, 'pagosAsignaciones'])->name('box.pagos.asignaciones');
        });

        Route::prefix('reportes')->group(function () {
            Route::get('/diario', [BoxController::class, 'reportesDiario'])->name('box.reportes.diario');
            Route::get('/movimientos', [BoxController::class, 'reportesMovimientos'])->name('box.reportes.movimientos');
            Route::get('/ventas', [BoxController::class, 'reportesVentas'])->name('box.reportes.ventas');
            Route::get('/inventario', [BoxController::class, 'reportesInventario'])->name('box.reportes.inventario');
        });

        // ===== RUTAS DE FACTURACIÓN =====
        Route::prefix('facturas')->group(function () {
            // Generar facturas (método unificado)
            Route::post('/generar', [BoxController::class, 'generarFactura'])->name('box.facturas.generar');

            // Ver y gestionar facturas
            Route::get('/lista', [BoxController::class, 'listarFacturas'])->name('box.facturas.lista');
            Route::get('/ver/{factura}', [BoxController::class, 'verFactura'])->name('box.facturas.ver');
            Route::post('/anular/{factura}', [BoxController::class, 'anularFactura'])->name('box.facturas.anular');

            // Modal para datos del cliente
            Route::get('/modal-cliente/{ventaId}', [BoxController::class, 'modalCliente'])->name('box.facturas.modal-cliente');

            // Procesar pago con factura directa (mejora UX)
            Route::post('/procesar-pago-con-factura', [BoxController::class, 'procesarPagoConFactura'])->name('box.facturas.procesar-pago-factura');
        });

        // ===== RUTAS DE CONCEPTOS Y PRECIOS =====
        Route::prefix('conceptos')->name('box.conceptos.')->group(function () {
            Route::get('/',                   [ConceptosController::class, 'index'])->name('index');
            Route::put('/carrera/{carrera}',  [ConceptosController::class, 'actualizarCarrera'])->name('carrera.update');
            Route::put('/producto/{product}', [ConceptosController::class, 'actualizarProducto'])->name('producto.update');
            Route::put('/productos/lote',     [ConceptosController::class, 'actualizarProductosLote'])->name('productos.lote');
        });
    });

    // POSTGRADO - Rutas específicas COMPLETAS
    Route::prefix('postgrado')->middleware(['punto_venta', 'postgrado_menu'])->group(function () {
        // Dashboard principal
        Route::get('/dashboard', [PostgradoController::class, 'dashboard'])->name('postgrado.dashboard');

        // Gestión de carreras y programas académicos
        Route::prefix('carreras')->name('carreras.')->group(function () {
            Route::get('/', [PostgradoController::class, 'carreras'])->name('index');
            Route::get('/crear', [PostgradoController::class, 'carrerasCrear'])->name('crear');
            Route::get('/cuotas', [PostgradoController::class, 'carrerasCuotas'])->name('cuotas');
        });

        // Alias para mantener compatibilidad
        Route::get('/carreras', [PostgradoController::class, 'carreras'])->name('postgrado.carreras');
        Route::get('/carreras/crear', [PostgradoController::class, 'carrerasCrear'])->name('postgrado.carreras.crear');
        Route::get('/carreras/cuotas', [PostgradoController::class, 'carrerasCuotas'])->name('postgrado.carreras.cuotas');

        // Programas específicos
        Route::get('/maestrias', [PostgradoController::class, 'maestrias'])->name('postgrado.maestrias');
        Route::get('/doctorados', [PostgradoController::class, 'doctorados'])->name('postgrado.doctorados');
        Route::get('/especialidades', [PostgradoController::class, 'especialidades'])->name('postgrado.especialidades');
        Route::get('/diplomaturas', [PostgradoController::class, 'diplomaturas'])->name('postgrado.diplomaturas');

        // Gestión de estudiantes
        Route::get('/estudiantes', [PostgradoController::class, 'estudiantes'])->name('postgrado.estudiantes');
        Route::get('/estudiantes/crear', [PostgradoController::class, 'estudiantesCrear'])->name('postgrado.estudiantes.crear');
        Route::get('/estudiantes/importar', [PostgradoController::class, 'estudiantesImportar'])->name('postgrado.estudiantes.importar');
        Route::get('/estudiantes/maestrias', [PostgradoController::class, 'estudiantesMaestrias'])->name('postgrado.estudiantes.maestrias');
        Route::get('/estudiantes/doctorados', [PostgradoController::class, 'estudiantesDoctorados'])->name('postgrado.estudiantes.doctorados');
        Route::get('/estudiantes/especialidades', [PostgradoController::class, 'estudiantesEspecialidades'])->name('postgrado.estudiantes.especialidades');
        Route::get('/estudiantes/diplomaturas', [PostgradoController::class, 'estudiantesDiplomaturas'])->name('postgrado.estudiantes.diplomaturas');
        Route::get('/estudiantes/cursos', [PostgradoController::class, 'estudiantesCursos'])->name('postgrado.estudiantes.cursos');

        // Inscripciones
        Route::get('/inscripciones/crear', [PostgradoController::class, 'inscripcionesCrear'])->name('postgrado.inscripciones.crear');
        Route::get('/inscripciones/importar', [PostgradoController::class, 'inscripcionesImportar'])->name('postgrado.inscripciones.importar');
        Route::get('/inscripciones/estado', [PostgradoController::class, 'inscripcionesEstado'])->name('postgrado.inscripciones.estado');

        // Punto de venta
        Route::get('/pos', [PostgradoController::class, 'pos'])->name('postgrado.pos');
        Route::post('/procesar-venta', [PostgradoController::class, 'procesarVenta'])->name('postgrado.procesar-venta');
        Route::get('/ventas/{sale}/ticket', [PostgradoController::class, 'descargarTicket'])->name('postgrado.ticket');

        // Cobros por programa
        Route::get('/cobros/maestrias', [PostgradoController::class, 'cobrosMaestrias'])->name('postgrado.cobros.maestrias');
        Route::get('/cobros/doctorados', [PostgradoController::class, 'cobrosDoctorados'])->name('postgrado.cobros.doctorados');
        Route::get('/cobros/especialidades', [PostgradoController::class, 'cobrosEspecialidades'])->name('postgrado.cobros.especialidades');
        Route::get('/cobros/diplomaturas', [PostgradoController::class, 'cobrosDiplomaturas'])->name('postgrado.cobros.diplomaturas');
        Route::get('/cobros/cursos', [PostgradoController::class, 'cobrosCursos'])->name('postgrado.cobros.cursos');

        // Derechos y matrículas
        Route::get('/derechos/inscripcion', [PostgradoController::class, 'derechosInscripcion'])->name('postgrado.derechos.inscripcion');
        Route::get('/derechos/examenes', [PostgradoController::class, 'derechosExamenes'])->name('postgrado.derechos.examenes');
        Route::get('/derechos/titulos', [PostgradoController::class, 'derechosTitulos'])->name('postgrado.derechos.titulos');

        // Gestión académica
        Route::get('/matriculas', [PostgradoController::class, 'matriculas'])->name('postgrado.matriculas');
        Route::get('/cursos', [PostgradoController::class, 'cursos'])->name('postgrado.cursos');

        // Certificados y títulos
        Route::get('/certificados', [PostgradoController::class, 'certificados'])->name('postgrado.certificados');
        Route::get('/certificados/emitir', [PostgradoController::class, 'certificadosEmitir'])->name('postgrado.certificados.emitir');
        Route::get('/certificados/historial', [PostgradoController::class, 'certificadosHistorial'])->name('postgrado.certificados.historial');
        Route::get('/certificados/plantillas', [PostgradoController::class, 'certificadosPlantillas'])->name('postgrado.certificados.plantillas');
        Route::get('/titulos', [PostgradoController::class, 'titulos'])->name('postgrado.titulos');

        // Reportes
        Route::get('/reportes', [PostgradoController::class, 'reportes'])->name('postgrado.reportes');
        Route::get('/reportes/estudiantes', [PostgradoController::class, 'reportesEstudiantes'])->name('postgrado.reportes.estudiantes');
        Route::get('/reportes/recaudacion', [PostgradoController::class, 'reportesRecaudacion'])->name('postgrado.reportes.recaudacion');
        Route::get('/reportes/matriculas', [PostgradoController::class, 'reportesMatriculas'])->name('postgrado.reportes.matriculas');
        Route::get('/reportes/inscripciones', [PostgradoController::class, 'reportesInscripciones'])->name('postgrado.reportes.inscripciones');
        Route::get('/reportes/certificados', [PostgradoController::class, 'reportesCertificados'])->name('postgrado.reportes.certificados');
        Route::get('/reportes/pagos', [PostgradoController::class, 'reportesPagos'])->name('postgrado.reportes.pagos');
        Route::get('/reportes/titulos', [PostgradoController::class, 'reportesTitulos'])->name('postgrado.reportes.titulos');

        // Configuración
        Route::get('/configuracion', [PostgradoController::class, 'configuracion'])->name('postgrado.configuracion');

        // API y AJAX
        Route::post('/venta-rapida', [PostgradoController::class, 'ventaRapida'])->name('postgrado.venta-rapida');
        Route::get('/api/buscar-estudiantes', [PostgradoController::class, 'buscarEstudiantes'])->name('postgrado.api.buscar-estudiantes');
    });

    // CENTRO ODONTOLÓGICO - Rutas específicas
    Route::prefix('odonto')->middleware(['punto_venta', 'odonto_menu'])->group(function () {
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
    Route::middleware(['admin', 'admin_menu'])->prefix('admin')->name('admin.')->group(function () {
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
            Route::get('/particular/exportar-pdf', [AdminController::class, 'exportarEstadoParticularPdf'])->name('particular.pdf');
            Route::get('/particular/exportar-excel', [AdminController::class, 'exportarEstadoParticularExcel'])->name('particular.excel');
            Route::post('/particular/buscar', [AdminController::class, 'buscarEstadoParticular'])->name('buscar-particular');
        });

        // Reportes
        Route::get('/reportes/consolidado', [AdminController::class, 'reportesConsolidado'])->name('reportes.consolidado');
    });

    // ===== RUTAS CRUD EN ESPAÑOL =====

    // Rutas para Estudiantes
    Route::prefix('estudiantes')->middleware('generic_crud_access:estudiantes')->name('estudiantes.')->group(function () {
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
    Route::prefix('carreras')->middleware('generic_crud_access:carreras')->name('carreras.')->group(function () {
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
    Route::prefix('productos')->middleware('generic_crud_access:productos')->name('productos.')->group(function () {
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

// =============================================================================
// RUTAS API PARA SISTEMA DE FINANCIAMIENTO INTERNO
// =============================================================================
Route::prefix('api')->middleware(['auth'])->group(function () {
    // Clientes deudores
    Route::post('/clientes-deudores/buscar', [App\Http\Controllers\ClienteDeudorController::class, 'buscar']);
    Route::get('/clientes-deudores/{id}', [App\Http\Controllers\ClienteDeudorController::class, 'obtener']);

    // Financiamientos
    Route::post('/financiamientos/crear', [App\Http\Controllers\FinanciamientoController::class, 'crear']);
    Route::get('/financiamientos/{id}', [App\Http\Controllers\FinanciamientoController::class, 'obtener']);
    Route::post('/financiamientos/{id}/pagar-cuota', [App\Http\Controllers\FinanciamientoController::class, 'pagarCuota']);
    Route::get('/financiamientos/{id}/estado', [App\Http\Controllers\FinanciamientoController::class, 'obtenerEstado']);
});

// Rutas específicas para documentos legales
Route::prefix('documentos')->middleware(['auth'])->group(function () {
    Route::get('/compromiso-pago/{financiamientoId}', [App\Http\Controllers\DocumentoLegalController::class, 'generarCompromisoPago']);
    Route::post('/registrar-impresion/{documentoId}', [App\Http\Controllers\DocumentoLegalController::class, 'registrarImpresion']);
    Route::get('/verificar-integridad/{documentoId}', [App\Http\Controllers\DocumentoLegalController::class, 'verificarIntegridad']);
});

// Rutas para gestión de clientes deudores (CRUD completo)
Route::prefix('clientes-deudores')->middleware(['auth'])->name('clientes-deudores.')->group(function () {
    Route::get('/', [App\Http\Controllers\ClienteDeudorController::class, 'index'])->name('index');
    Route::get('/crear', [App\Http\Controllers\ClienteDeudorController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\ClienteDeudorController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\ClienteDeudorController::class, 'show'])->name('show');
    Route::get('/{id}/editar', [App\Http\Controllers\ClienteDeudorController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\ClienteDeudorController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\ClienteDeudorController::class, 'destroy'])->name('destroy');
});

// Rutas para gestión de financiamientos (interfaz administrativa)
Route::prefix('financiamientos')->middleware(['auth'])->name('financiamientos.')->group(function () {
    Route::get('/', [App\Http\Controllers\FinanciamientoController::class, 'index'])->name('index');
    Route::get('/{id}/detalles', [App\Http\Controllers\FinanciamientoController::class, 'show'])->name('show');
    Route::get('/vencimientos', [App\Http\Controllers\FinanciamientoController::class, 'vencimientos'])->name('vencimientos');
    Route::get('/reportes', [App\Http\Controllers\FinanciamientoController::class, 'reportes'])->name('reportes');
    Route::put('/{id}/cancelar', [App\Http\Controllers\FinanciamientoController::class, 'cancelar'])->name('cancelar');
});

require __DIR__ . '/auth.php';
