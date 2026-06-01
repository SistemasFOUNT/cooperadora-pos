<?php

namespace Tests\Feature\Genericos;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GenericCrudAccessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_usuario_box_no_puede_acceder_a_estudiantes(): void
    {
        /** @var User $usuario */
        $usuario = User::factory()->createOne([
            'role' => 'usuario_box',
        ]);

        $this->actingAs($usuario)
            ->get('/estudiantes')
            ->assertForbidden();
    }

    public function test_usuario_odonto_no_puede_acceder_a_carreras(): void
    {
        /** @var User $usuario */
        $usuario = User::factory()->createOne([
            'role' => 'usuario_odonto',
        ]);

        $this->actingAs($usuario)
            ->get('/carreras')
            ->assertForbidden();
    }

    public function test_usuario_postgrado_no_puede_acceder_a_productos(): void
    {
        /** @var User $usuario */
        $usuario = User::factory()->createOne([
            'role' => 'usuario_postgrado',
        ]);

        $this->actingAs($usuario)
            ->get('/productos')
            ->assertForbidden();
    }

    public function test_usuario_postgrado_puede_acceder_a_estudiantes(): void
    {
        /** @var User $usuario */
        $usuario = User::factory()->createOne([
            'role' => 'usuario_postgrado',
        ]);

        $this->actingAs($usuario)
            ->get('/estudiantes')
            ->assertOk();
    }

    public function test_usuario_postgrado_puede_acceder_a_carreras(): void
    {
        /** @var User $usuario */
        $usuario = User::factory()->createOne([
            'role' => 'usuario_postgrado',
        ]);

        $this->actingAs($usuario)
            ->get('/carreras')
            ->assertOk();
    }

    public function test_usuario_box_puede_acceder_a_productos(): void
    {
        /** @var User $usuario */
        $usuario = User::factory()->createOne([
            'role' => 'usuario_box',
        ]);

        $this->actingAs($usuario)
            ->get('/productos')
            ->assertOk();
    }

    public function test_usuario_odonto_no_puede_acceder_a_productos(): void
    {
        /** @var User $usuario */
        $usuario = User::factory()->createOne([
            'role' => 'usuario_odonto',
        ]);

        $this->actingAs($usuario)
            ->get('/productos')
            ->assertForbidden();
    }

    public function test_admin_puede_acceder_a_estudiantes_carreras_y_productos(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/estudiantes')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/carreras')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/productos')
            ->assertOk();
    }
}
