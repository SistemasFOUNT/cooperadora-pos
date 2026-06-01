<?php

namespace Tests\Feature\Postgrado;

use App\Models\PuntoVenta;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PostgradoAccessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_usuario_no_postgrado_no_puede_acceder_a_dashboard_postgrado(): void
    {
        PuntoVenta::create([
            'codigo' => 'POSTGRADO',
            'nombre' => 'Postgrado',
            'activo' => true,
        ]);

        $odonto = PuntoVenta::create([
            'codigo' => 'ODONTO',
            'nombre' => 'Odonto',
            'activo' => true,
        ]);

        /** @var User $usuario */
        $usuario = User::factory()->createOne([
            'role' => 'usuario_odonto',
            'punto_venta_id' => $odonto->id,
        ]);

        $this->actingAs($usuario)
            ->get('/postgrado/dashboard')
            ->assertForbidden();
    }

    public function test_usuario_postgrado_puede_acceder_a_dashboard_postgrado(): void
    {
        $postgrado = PuntoVenta::create([
            'codigo' => 'POSTGRADO',
            'nombre' => 'Postgrado',
            'activo' => true,
        ]);

        /** @var User $usuario */
        $usuario = User::factory()->createOne([
            'role' => 'usuario_postgrado',
            'punto_venta_id' => $postgrado->id,
        ]);

        $this->actingAs($usuario)
            ->get('/postgrado/dashboard')
            ->assertOk();
    }

    public function test_admin_puede_acceder_a_dashboard_postgrado(): void
    {
        PuntoVenta::create([
            'codigo' => 'POSTGRADO',
            'nombre' => 'Postgrado',
            'activo' => true,
        ]);

        /** @var User $admin */
        $admin = User::factory()->createOne([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/postgrado/dashboard')
            ->assertOk();
    }
}
