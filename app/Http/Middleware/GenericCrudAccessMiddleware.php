<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GenericCrudAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Matriz de acceso por recurso CRUD generico:
     * - estudiantes: admin, usuario_postgrado
     * - carreras: admin, usuario_postgrado
    * - productos: admin, usuario_box
     */
    public function handle(Request $request, Closure $next, string $recurso): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        $allowedRoles = [
            'estudiantes' => ['usuario_postgrado'],
            'carreras' => ['usuario_postgrado'],
            'productos' => ['usuario_box'],
        ];

        $roles = $allowedRoles[$recurso] ?? [];

        if (!in_array((string) $user->role, $roles, true)) {
            abort(403, 'No tienes permisos para acceder a este recurso.');
        }

        return $next($request);
    }
}
