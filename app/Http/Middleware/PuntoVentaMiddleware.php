<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PuntoVentaMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $puntoVentaRequerido = null): Response
    {
        $user = $request->user();

        // Si no hay usuario autenticado, redirigir al login
        if (!$user) {
            return redirect()->route('login');
        }

        // Si es admin, permitir acceso a todo
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Si se especifica un punto de venta requerido, verificar acceso
        if ($puntoVentaRequerido && !$user->canAccessPuntoVenta($puntoVentaRequerido)) {
            abort(403, 'No tienes permisos para acceder a este punto de venta.');
        }

        // Agregar el punto de venta del usuario a la request
        $request->merge([
            'user_punto_venta_id' => $user->punto_venta_id,
            'user_role' => $user->role
        ]);

        return $next($request);
    }
}
