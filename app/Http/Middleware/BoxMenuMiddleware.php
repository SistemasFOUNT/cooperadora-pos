<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class BoxMenuMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Aislamiento estricto: solo admin o usuario_box pueden entrar a rutas BOX.
            if (!$user->isAdmin() && $user->role !== 'usuario_box') {
                abort(403, 'No tienes permisos para acceder al módulo BOX.');
            }

            // Si es usuario BOX, cargar menú específico.
            if ($user->role === 'usuario_box') {
                $boxMenu = config('box-menu.menu');
                Config::set('adminlte.menu', $boxMenu);
            }
        }

        return $next($request);
    }
}
