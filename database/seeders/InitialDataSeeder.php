<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\PaymentMethod;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear sucursales
        $cooperadora = Branch::create([
            'name' => 'Cooperadora',
            'code' => 'COP',
            'description' => 'Sucursal principal de la cooperadora',
            'fiscal_data' => [
                'cuit' => '30-12345678-9',
                'razon_social' => 'Cooperadora Facultad de Odontología',
                'condicion_iva' => 'Responsable Inscripto'
            ],
            'is_active' => true,
        ]);

        $postgrado = Branch::create([
            'name' => 'Postgrado',
            'code' => 'POS',
            'description' => 'Punto de venta de postgrado',
            'fiscal_data' => [
                'cuit' => '30-12345678-9',
                'razon_social' => 'Cooperadora Facultad de Odontología',
                'condicion_iva' => 'Responsable Inscripto'
            ],
            'is_active' => true,
        ]);

        $centroOdontologico = Branch::create([
            'name' => 'Centro Odontológico',
            'code' => 'COD',
            'description' => 'Centro odontológico para tratamientos',
            'fiscal_data' => [
                'cuit' => '30-12345678-9',
                'razon_social' => 'Cooperadora Facultad de Odontología',
                'condicion_iva' => 'Responsable Inscripto'
            ],
            'is_active' => true,
        ]);

        // Crear métodos de pago
        PaymentMethod::create([
            'name' => 'Efectivo',
            'code' => 'EFE',
            'type' => 'cash',
            'requires_authorization' => false,
            'commission_percentage' => 0,
            'settlement_days' => 0,
            'is_active' => true,
        ]);

        PaymentMethod::create([
            'name' => 'Tarjeta de Débito',
            'code' => 'TDB',
            'type' => 'card',
            'requires_authorization' => true,
            'commission_percentage' => 2.5,
            'settlement_days' => 1,
            'is_active' => true,
        ]);

        PaymentMethod::create([
            'name' => 'Tarjeta de Crédito',
            'code' => 'TDC',
            'type' => 'card',
            'requires_authorization' => true,
            'commission_percentage' => 3.5,
            'settlement_days' => 30,
            'is_active' => true,
        ]);

        PaymentMethod::create([
            'name' => 'Transferencia Bancaria',
            'code' => 'TRA',
            'type' => 'transfer',
            'requires_authorization' => false,
            'commission_percentage' => 0,
            'settlement_days' => 1,
            'is_active' => true,
        ]);

        // Crear roles y permisos
        $adminRole = Role::create(['name' => 'admin']);
        $supervisorRole = Role::create(['name' => 'supervisor']);
        $cajeroRole = Role::create(['name' => 'cajero']);
        $auditorRole = Role::create(['name' => 'auditor']);

        // Crear permisos
        $permissions = [
            'view_sales', 'create_sales', 'edit_sales', 'delete_sales',
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'view_students', 'create_students', 'edit_students', 'delete_students',
            'view_employees', 'create_employees', 'edit_employees', 'delete_employees',
            'view_reports', 'generate_reports',
            'view_audit', 'manage_users', 'manage_branches',
            'open_cash', 'close_cash', 'view_cash_movements',
            'manage_stock', 'view_stock_movements',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Asignar permisos a roles
        $adminRole->givePermissionTo(Permission::all());
        $supervisorRole->givePermissionTo([
            'view_sales', 'create_sales', 'edit_sales',
            'view_products', 'create_products', 'edit_products',
            'view_students', 'create_students', 'edit_students',
            'view_reports', 'generate_reports',
            'open_cash', 'close_cash', 'view_cash_movements',
            'manage_stock', 'view_stock_movements',
        ]);

        $cajeroRole->givePermissionTo([
            'view_sales', 'create_sales',
            'view_products',
            'view_students',
            'open_cash', 'close_cash', 'view_cash_movements',
        ]);

        $auditorRole->givePermissionTo([
            'view_sales', 'view_products', 'view_students',
            'view_reports', 'view_audit',
        ]);

        // Crear usuario administrador
        $admin = User::create([
            'name' => 'Administrador Sistema',
            'username' => 'admin',
            'email' => 'admin@cooperadora.edu.ar',
            'password' => Hash::make('admin123'),
            'branch_id' => $cooperadora->id,
            'employee_number' => 'EMP001',
            'status' => 'active',
        ]);

        $admin->assignRole('admin');
    }
}
