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
        if (Auth::check()) {
            $user = Auth::user();

            // Aislamiento estricto: solo admin o usuario_postgrado pueden entrar a rutas Postgrado.
            if (!$user->isAdmin() && $user->role !== 'usuario_postgrado') {
                abort(403, 'No tienes permisos para acceder al módulo Postgrado.');
            }

            if ($user->role === 'usuario_postgrado') {
                $postgradoMenu = config('postgrado-menu.menu');
                Config::set('adminlte.menu', $postgradoMenu);
            }
        }

        return $next($request);
    }
}
