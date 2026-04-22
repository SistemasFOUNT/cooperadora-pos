<?php

namespace App\Services;

use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;
use Illuminate\Support\Facades\Auth;

class RoleBasedMenuFilter implements FilterInterface
{
    public function transform($item, $key = null)
    {
        if (!Auth::check()) {
            return $item;
        }

        $user = Auth::user();

        // Si el usuario es admin, mostrar todo el menú
        if ($user->role === 'admin') {
            return $item;
        }

        // Si el usuario es de BOX, mostrar solo el menú de BOX
        if ($user->role === 'usuario_box') {
            return $this->getBoxMenu($item);
        }

        // Para otros roles, devolver el item sin cambios
        return $item;
    }

    private function getBoxMenu($item)
    {
        // Lista de rutas permitidas para usuarios BOX
        $boxRoutes = [
            'box',
            'box/cobros',
            'box/productos',
            'box/pagos',
            'box/reportes',
            'profile'
        ];

        // Si es un item con URL, verificar si está permitido
        if (isset($item['url'])) {
            foreach ($boxRoutes as $route) {
                if (str_contains($item['url'], $route)) {
                    return $item;
                }
            }
            // Si no está permitido, ocultarlo
            return false;
        }

        // Si es un header o tiene submenu, procesarlo
        if (isset($item['header']) || isset($item['submenu'])) {
            return $item;
        }

        return $item;
    }
}
