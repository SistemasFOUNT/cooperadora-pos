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
                
                // Modificar URLs específicas para postgrado
                $this->modifyPostgradoMenuUrls($menu);
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

    /**
     * Modificar URLs del menú específicamente para usuarios de postgrado
     */
    private function modifyPostgradoMenuUrls($menu)
    {
        // Para usuarios de postgrado, reemplazar TODAS las URLs globales con versiones específicas de postgrado
        
        // 1. Dashboard - cambiar de "/" a "postgrado/dashboard" 
        if ($menu->itemKeyExists('dashboard')) {
            $menu->remove('dashboard');
            $menu->addAfter('MENÚ PRINCIPAL', [
                'key' => 'dashboard-postgrado',
                'text' => 'Dashboard',
                'url' => 'postgrado/dashboard',
                'icon' => 'fas fa-fw fa-tachometer-alt',
                'classes' => 'text-warning',
            ]);
        }

        // 2. Configurar Carreras - ya hecho anteriormente pero asegurar que esté
        if ($menu->itemKeyExists('configurar-carreras')) {
            $menu->remove('configurar-carreras');
            $menu->addIn('carreras-cuotas', [
                'key' => 'configurar-carreras-postgrado',
                'text' => 'Configurar Carreras',
                'url' => 'postgrado/carreras',
                'icon' => 'fas fa-fw fa-cogs',
                'classes' => 'text-warning',
            ]);
        }

        // 3. Lista de Estudiantes - ya hecho anteriormente pero asegurar que esté
        if ($menu->itemKeyExists('lista-estudiantes')) {
            $menu->remove('lista-estudiantes');
            $menu->addIn('estudiantes', [
                'key' => 'lista-estudiantes-postgrado',
                'text' => 'Lista de Estudiantes',
                'url' => 'postgrado/estudiantes',
                'icon' => 'fas fa-fw fa-list',
                'classes' => 'text-warning',
            ]);
        }

        // 4. Agregar Estudiante
        if ($menu->itemKeyExists('agregar-estudiante')) {
            $menu->remove('agregar-estudiante');
            $menu->addIn('estudiantes', [
                'key' => 'agregar-estudiante-postgrado',
                'text' => 'Agregar Estudiante',
                'url' => 'postgrado/estudiantes/crear',
                'icon' => 'fas fa-fw fa-plus',
                'classes' => 'text-warning',
            ]);
        }

        // 5. Importar Estudiantes
        if ($menu->itemKeyExists('importar-estudiantes')) {
            $menu->remove('importar-estudiantes');
            $menu->addIn('estudiantes', [
                'key' => 'importar-estudiantes-postgrado',
                'text' => 'Importar desde CSV',
                'url' => 'postgrado/estudiantes/importar',
                'icon' => 'fas fa-fw fa-upload',
                'classes' => 'text-warning',
            ]);
        }

        // 6. Gestionar Cuotas
        if ($menu->itemKeyExists('gestionar-cuotas')) {
            $menu->remove('gestionar-cuotas');
            $menu->addIn('carreras-cuotas', [
                'key' => 'gestionar-cuotas-postgrado',
                'text' => 'Gestionar Cuotas',
                'url' => 'postgrado/carreras/cuotas',
                'icon' => 'fas fa-fw fa-dollar-sign',
                'classes' => 'text-warning',
            ]);
        }
    }
}
