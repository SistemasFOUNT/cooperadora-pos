<?php

namespace App\Http\Controllers;

use App\Models\PuntoVenta;
use App\Models\CuentaContable;
use Illuminate\Http\Request;

class PuntoVentaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Si es admin, mostrar todos los puntos de venta
        if ($user->isAdmin()) {
            $puntosVenta = PuntoVenta::with([
                'cuentaCaja',
                'cuentaVentas',
                'cuentaDeudores',
                'cuentaFondoFijo'
            ])->get();
        } else {
            // Si no es admin, mostrar solo su punto de venta
            $puntosVenta = PuntoVenta::with([
                'cuentaCaja',
                'cuentaVentas',
                'cuentaDeudores',
                'cuentaFondoFijo'
            ])->where('id', $user->punto_venta_id)->get();
        }

        return view('contabilidad.puntos-venta.index', compact('puntosVenta'));
    }

    public function show($id, Request $request)
    {
        $user = $request->user();

        // Verificar si el usuario puede acceder a este punto de venta
        if (!$user->isAdmin() && $user->punto_venta_id != $id) {
            abort(403, 'No tienes permisos para acceder a este punto de venta.');
        }

        $puntoVenta = PuntoVenta::with([
            'cuentaCaja',
            'cuentaVentas',
            'cuentaDeudores',
            'cuentaFondoFijo'
        ])->findOrFail($id);

        return view('contabilidad.puntos-venta.show', compact('puntoVenta'));
    }

    public function asientoDemo($id)
    {
        $puntoVenta = PuntoVenta::findOrFail($id);

        // Simular una venta de ejemplo
        $asiento = $puntoVenta->generarAsientoVenta(
            500, // $500 de venta
            'Venta de materiales odontológicos',
            'efectivo'
        );

        return response()->json($asiento);
    }

    public function estadisticas()
    {
        $estadisticas = [
            'total_puntos' => PuntoVenta::count(),
            'puntos_activos' => PuntoVenta::activo()->count(),
            'cuentas_configuradas' => PuntoVenta::whereNotNull('cuenta_caja_id')->count(),
        ];

        return response()->json($estadisticas);
    }
}
