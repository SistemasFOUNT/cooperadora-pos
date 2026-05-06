<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class PostgradoMenuMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // DEBUG - verificar que el usuario correcto está accediendo
        if (Auth::check()) {
            \Log::info('PostgradoMenuMiddleware - Usuario: ' . Auth::user()->username);
            \Log::info('PostgradoMenuMiddleware - Role: ' . Auth::user()->role);
        }

        // La carga del menú ahora es manejada por AdminLTEServiceProvider
        // durante el evento BuildingMenu para resolver problemas de timing

        return $next($request);
    }
}
