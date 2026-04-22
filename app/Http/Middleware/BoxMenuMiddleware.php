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
        // Si el usuario está autenticado y es usuario BOX
        if (Auth::check() && Auth::user()->role === 'usuario_box') {
            // Cargar el menú específico de BOX
            $boxMenu = config('box-menu.menu');
            Config::set('adminlte.menu', $boxMenu);
        }

        return $next($request);
    }
}
