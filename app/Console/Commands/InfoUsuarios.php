<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class InfoUsuarios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usuarios:info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mostrar información de usuarios para testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== INFORMACIÓN DE USUARIOS PARA TESTING ===');
        $this->newLine();

        $usuarios = User::with('puntoVenta')->get();

        foreach ($usuarios as $usuario) {
            $this->info("👤 {$usuario->name}");
            $this->line("   Email: {$usuario->email}");
            $this->line("   Username: {$usuario->username}");
            $this->line("   Rol: {$usuario->role}");

            if ($usuario->puntoVenta) {
                $this->line("   Punto de Venta: {$usuario->puntoVenta->nombre}");
            } else {
                $this->line("   Punto de Venta: Sin asignar (Admin)");
            }

            // Mostrar contraseña para testing
            $passwords = [
                'admin' => 'admin123',
                'caja1_box' => 'caja123',
                'caja2_box' => 'caja123',
                'sec_postgrado' => 'postgrado123',
                'recep1_odonto' => 'odonto123',
                'recep2_odonto' => 'odonto123'
            ];

            $password = $passwords[$usuario->username] ?? 'N/A';
            $this->line("   Password: {$password}");
            $this->newLine();
        }

        $this->info('✅ Para probar el sistema:');
        $this->info('1. Ve a http://127.0.0.1:8000/login');
        $this->info('2. Usa cualquiera de los usuarios de arriba');
        $this->info('3. Nota cómo cada usuario ve solo su punto de venta');
        $this->info('4. El admin ve todos los puntos de venta');
    }
}
