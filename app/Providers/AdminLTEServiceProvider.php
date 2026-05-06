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
        // PRIMER HOOK: Modificar configuración del menú antes de que se construya
        $this->app->booted(function () {
            $user = request()->user();

            if ($user && $user->role === 'usuario_postgrado') {
                // Reemplazar completamente el menú con la configuración de postgrado
                $postgradoMenu = config('postgrado-menu.menu');
                if ($postgradoMenu) {
                    config(['adminlte.menu' => $postgradoMenu]);
                }
            }
        });

        // SEGUNDO HOOK: Personalizar información del usuario en AdminLTE
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
        });
    }
}
