<?php

namespace Tests\Feature\Box;

use App\Models\PuntoVenta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoxAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_no_box_no_puede_acceder_a_dashboard_box(): void
    {
        PuntoVenta::create([
            'codigo' => 'BOX',
            'nombre' => 'BOX',
            'activo' => true,
        ]);

        $postgrado = PuntoVenta::create([
            'codigo' => 'POS',
            'nombre' => 'Postgrado',
            'activo' => true,
        ]);

        /** @var User $usuario */
        $usuario = User::factory()->createOne([
            'role' => 'usuario_postgrado',
            'punto_venta_id' => $postgrado->id,
        ]);

        $this->actingAs($usuario)
            ->get('/box/dashboard')
            ->assertForbidden();
    }

    public function test_usuario_box_puede_acceder_a_dashboard_box(): void
    {
        $box = PuntoVenta::create([
            'codigo' => 'BOX',
            'nombre' => 'BOX',
            'activo' => true,
        ]);

        /** @var User $usuario */
        $usuario = User::factory()->createOne([
            'role' => 'usuario_box',
            'punto_venta_id' => $box->id,
        ]);

        $this->actingAs($usuario)
            ->get('/box/dashboard')
            ->assertOk();
    }

    public function test_endpoint_generar_factura_box_responde_validacion_si_falta_venta_id(): void
    {
        $box = PuntoVenta::create([
            'codigo' => 'BOX',
            'nombre' => 'BOX',
            'activo' => true,
        ]);

        /** @var User $usuario */
        $usuario = User::factory()->createOne([
            'role' => 'usuario_box',
            'punto_venta_id' => $box->id,
        ]);

        $this->actingAs($usuario)
            ->postJson(route('box.facturas.generar'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['venta_id']);
    }
}
