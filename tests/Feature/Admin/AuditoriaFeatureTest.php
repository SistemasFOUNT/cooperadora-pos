<?php

namespace Tests\Feature\Admin;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditoriaFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_filtrar_registros_de_auditoria_por_evento_y_busqueda(): void
    {
        $admin = User::factory()->createOne([
            'role' => 'admin',
        ]);

        DB::table('audits')->insert([
            [
                'user_type' => User::class,
                'user_id' => $admin->id,
                'event' => 'created',
                'auditable_type' => Sale::class,
                'auditable_id' => 100,
                'old_values' => json_encode([]),
                'new_values' => json_encode(['total' => 1000]),
                'url' => '/postgrado/procesar-venta',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'tags' => 'postgrado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_type' => User::class,
                'user_id' => $admin->id,
                'event' => 'updated',
                'auditable_type' => Sale::class,
                'auditable_id' => 200,
                'old_values' => json_encode(['total' => 900]),
                'new_values' => json_encode(['total' => 950]),
                'url' => '/odonto/reportes',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'tags' => 'odonto',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.auditoria.index', [
                'evento' => 'created',
                'buscar' => 'postgrado',
            ]));

        $response->assertOk();
        $response->assertSee('CREATED');
        $response->assertViewHas('audits', function ($audits) {
            return $audits->total() === 1;
        });
    }

    public function test_admin_puede_ver_detalle_de_un_registro_de_auditoria(): void
    {
        $admin = User::factory()->createOne([
            'role' => 'admin',
        ]);

        $auditId = DB::table('audits')->insertGetId([
            'user_type' => User::class,
            'user_id' => $admin->id,
            'event' => 'deleted',
            'auditable_type' => Sale::class,
            'auditable_id' => 300,
            'old_values' => json_encode(['status' => 'completed']),
            'new_values' => json_encode([]),
            'url' => '/admin/auditoria',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'tags' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.auditoria.show', $auditId));

        $response->assertOk();
        $response->assertSee((string) $auditId);
        $response->assertSee('DELETED');
        $response->assertSee(Sale::class);
    }
}
