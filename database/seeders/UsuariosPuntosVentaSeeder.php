<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PuntoVenta;
use Illuminate\Support\Facades\Hash;

class UsuariosPuntosVentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener los puntos de venta
        $puntoBox = PuntoVenta::where('codigo', 'BOX')->first();
        $puntoPostgrado = PuntoVenta::where('codigo', 'POSTGRADO')->first();
        $puntoOdonto = PuntoVenta::where('codigo', 'ODONTO')->first();

        // Actualizar usuario administrador existente o crear uno nuevo
        $admin = User::where('username', 'admin')->first();
        if ($admin) {
            $admin->update([
                'role' => 'admin',
                'punto_venta_id' => null, // Admin no tiene punto de venta específico
                'permisos' => [
                    'ver_todo' => true,
                    'gestionar_usuarios' => true,
                    'reportes_completos' => true,
                    'configuracion_sistema' => true
                ]
            ]);
            $this->command->info('✅ Usuario admin actualizado');
        } else {
            User::create([
                'name' => 'Administrador del Sistema',
                'username' => 'admin_sistema',
                'email' => 'admin@cooperadora.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => 'active',
                'punto_venta_id' => null,
                'permisos' => [
                    'ver_todo' => true,
                    'gestionar_usuarios' => true,
                    'reportes_completos' => true,
                    'configuracion_sistema' => true
                ]
            ]);
        }

        // Usuarios del BOX Cooperadora
        if ($puntoBox) {
            User::firstOrCreate(
                ['email' => 'caja1@cooperadora.com'],
                [
                    'name' => 'Cajero 1 BOX',
                    'username' => 'caja1_box',
                    'password' => Hash::make('caja123'),
                    'role' => 'usuario_box',
                    'status' => 'active',
                    'punto_venta_id' => $puntoBox->id,
                    'permisos' => [
                        'ventas' => true,
                        'consultar_productos' => true,
                        'reportes_ventas_propias' => true
                    ]
                ]
            );

            User::firstOrCreate(
                ['email' => 'caja2@cooperadora.com'],
                [
                    'name' => 'Cajero 2 BOX',
                    'username' => 'caja2_box',
                    'password' => Hash::make('caja123'),
                    'role' => 'usuario_box',
                    'status' => 'active',
                    'punto_venta_id' => $puntoBox->id,
                    'permisos' => [
                        'ventas' => true,
                        'consultar_productos' => true,
                        'reportes_ventas_propias' => true
                    ]
                ]
            );
        }

        // Usuario de Postgrado (único)
        if ($puntoPostgrado) {
            User::firstOrCreate(
                ['email' => 'secretaria@postgrado.com'],
                [
                    'name' => 'Secretaria Postgrado',
                    'username' => 'sec_postgrado',
                    'password' => Hash::make('postgrado123'),
                    'role' => 'usuario_postgrado',
                    'status' => 'active',
                    'punto_venta_id' => $puntoPostgrado->id,
                    'permisos' => [
                        'ventas' => true,
                        'consultar_productos' => true,
                        'reportes_ventas_propias' => true,
                        'gestionar_estudiantes' => true
                    ]
                ]
            );
        }

        // Usuarios del Centro Odontológico
        if ($puntoOdonto) {
            User::firstOrCreate(
                ['email' => 'recepcion1@odonto.com'],
                [
                    'name' => 'Recepcionista 1 Odonto',
                    'username' => 'recep1_odonto',
                    'password' => Hash::make('odonto123'),
                    'role' => 'usuario_odonto',
                    'status' => 'active',
                    'punto_venta_id' => $puntoOdonto->id,
                    'permisos' => [
                        'ventas' => true,
                        'consultar_productos' => true,
                        'reportes_ventas_propias' => true,
                        'agenda_citas' => true
                    ]
                ]
            );

            User::firstOrCreate(
                ['email' => 'recepcion2@odonto.com'],
                [
                    'name' => 'Recepcionista 2 Odonto',
                    'username' => 'recep2_odonto',
                    'password' => Hash::make('odonto123'),
                    'role' => 'usuario_odonto',
                    'status' => 'active',
                    'punto_venta_id' => $puntoOdonto->id,
                    'permisos' => [
                        'ventas' => true,
                        'consultar_productos' => true,
                        'reportes_ventas_propias' => true
                    ]
                ]
            );
        }

        $this->command->info('✅ Usuarios por puntos de venta creados correctamente');
        $this->command->info('👤 Admin: admin@cooperadora.com / admin123');
        $this->command->info('📦 BOX: caja1@cooperadora.com, caja2@cooperadora.com / caja123');
        $this->command->info('🎓 Postgrado: secretaria@postgrado.com / postgrado123');
        $this->command->info('🦷 Odonto: recepcion1@odonto.com, recepcion2@odonto.com / odonto123');
    }
}
