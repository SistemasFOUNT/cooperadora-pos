<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class AdminLTEServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Personalizar información del usuario en AdminLTE
        $this->app['events']->listen(BuildingMenu::class, function (BuildingMenu $event) {
            $user = request()->user();

            if (!$user) {
                return;
            }

            // Obtener información del punto de venta
            $puntoVentaInfo = '';
            if ($user->isAdmin()) {
                $puntoVentaInfo = 'Administrador del Sistema';
            } else if ($user->puntoVenta) {
                $puntoVentaInfo = $user->puntoVenta->nombre;
            }

            // Agregar información del usuario
            $event->menu->addIn('ACCOUNT_MENU_ITEMS', [
                'type' => 'user-panel',
                'name' => $user->name,
                'desc' => $puntoVentaInfo ?: $user->getRoleNameAttribute(),
                'image' => asset('vendor/adminlte/dist/img/user-default.png'),
            ]);

            // Filtrar menú según el rol del usuario
            if (!$user->isAdmin()) {
                // Para usuarios no admin, ocultar algunos elementos del menú
                $this->filterMenuForUser($event->menu, $user);
            }
        });
    }

    /**
     * Filtrar el menú según el tipo de usuario
     */
    private function filterMenuForUser($menu, $user)
    {
        // Agregar elementos específicos según el rol
        switch ($user->role) {
            case 'usuario_box':
                $menu->addAfter('GESTIÓN', [
                    'header' => 'BOX COOPERADORA'
                ]);
                $menu->addAfter('BOX COOPERADORA', [
                    'text' => 'Mi Punto de Venta: BOX',
                    'url' => 'contabilidad/puntos-venta',
                    'icon' => 'fas fa-fw fa-store-alt',
                    'classes' => 'bg-primary text-white',
                ]);
                break;

            case 'usuario_postgrado':
                $menu->addAfter('GESTIÓN', [
                    'header' => 'POSTGRADO'
                ]);
                $menu->addAfter('POSTGRADO', [
                    'text' => 'Mi Punto de Venta: Postgrado',
                    'url' => 'contabilidad/puntos-venta',
                    'icon' => 'fas fa-fw fa-graduation-cap',
                    'classes' => 'bg-success text-white',
                ]);
                break;

            case 'usuario_odonto':
                $menu->addAfter('GESTIÓN', [
                    'header' => 'CENTRO ODONTOLÓGICO'
                ]);
                $menu->addAfter('CENTRO ODONTOLÓGICO', [
                    'text' => 'Mi Punto de Venta: Odonto',
                    'url' => 'contabilidad/puntos-venta',
                    'icon' => 'fas fa-fw fa-tooth',
                    'classes' => 'bg-warning text-white',
                ]);
                break;
        }

        // Para usuarios no admin, ocultar algunos elementos avanzados
        // Esto se puede implementar según las necesidades específicas
    }
}
