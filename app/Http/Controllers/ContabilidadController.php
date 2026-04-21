<?php

namespace App\Http\Controllers;

use App\Models\CuentaContable;
use Illuminate\Http\Request;

class ContabilidadController extends Controller
{
    public function planCuentas()
    {
        $cuentas = CuentaContable::with('padre', 'hijos')
                                ->orderBy('codigo')
                                ->get();

        // Organizar cuentas en estructura jerárquica
        $cuentasJerarquicas = $this->organizarJerarquia($cuentas);

        return view('contabilidad.plan-cuentas', compact('cuentasJerarquicas'));
    }

    private function organizarJerarquia($cuentas)
    {
        $cuentasPorNivel = $cuentas->groupBy('nivel');
        $resultado = [];

        // Empezar con las cuentas de nivel 1
        foreach ($cuentasPorNivel[1] ?? [] as $cuenta) {
            $resultado[] = [
                'cuenta' => $cuenta,
                'hijos' => $this->obtenerHijos($cuenta, $cuentas)
            ];
        }

        return $resultado;
    }

    private function obtenerHijos($cuentaPadre, $todasLasCuentas)
    {
        $hijos = [];

        foreach ($todasLasCuentas as $cuenta) {
            if ($cuenta->cuenta_padre_id == $cuentaPadre->id) {
                $hijos[] = [
                    'cuenta' => $cuenta,
                    'hijos' => $this->obtenerHijos($cuenta, $todasLasCuentas)
                ];
            }
        }

        return $hijos;
    }

    public function buscarCuentas(Request $request)
    {
        $busqueda = $request->get('q');

        $cuentas = CuentaContable::where('codigo', 'like', "%{$busqueda}%")
                                ->orWhere('nombre', 'like', "%{$busqueda}%")
                                ->activas()
                                ->imputables()
                                ->limit(20)
                                ->get();

        return response()->json($cuentas);
    }
}
