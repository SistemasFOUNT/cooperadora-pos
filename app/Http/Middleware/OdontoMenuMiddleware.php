<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class OdontoMenuMiddleware
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

            // Aislamiento estricto: solo admin o usuario_odonto pueden entrar a rutas Odonto.
            if (!$user->isAdmin() && $user->role !== 'usuario_odonto') {
                abort(403, 'No tienes permisos para acceder al módulo Odonto.');
            }

            if ($user->role === 'usuario_odonto') {
                $odontoMenu = config('odonto-menu.menu');
                Config::set('adminlte.menu', $odontoMenu);
            }
        }

        return $next($request);
    }
}
