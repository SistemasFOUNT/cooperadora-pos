<?php

namespace Tests\Feature\Reportes;

use App\Models\PaymentMethod;
use App\Models\PuntoVenta;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PuntosVentaReportesFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_libro_caja_admin_postgrado_filtra_por_periodo_y_punto_venta(): void
    {
        $postgrado = PuntoVenta::create([
            'codigo' => 'POSTGRADO',
            'nombre' => 'Postgrado',
            'activo' => true,
        ]);

        $odonto = PuntoVenta::create([
            'codigo' => 'ODONTO',
            'nombre' => 'Odonto',
            'activo' => true,
        ]);

        DB::table('sucursales')->insert([
            [
                'id' => $postgrado->id,
                'name' => 'Sucursal Postgrado',
                'code' => 'POS',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $odonto->id,
                'name' => 'Sucursal Odonto',
                'code' => 'ODO',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $admin = User::factory()->createOne([
            'role' => 'admin',
        ]);

        $metodoPago = PaymentMethod::create([
            'name' => 'Efectivo',
            'code' => 'EFE',
            'type' => 'cash',
            'requires_authorization' => false,
            'commission_percentage' => 0,
            'settlement_days' => 0,
            'is_active' => true,
        ]);

        $ventaPostgrado = Sale::create([
            'sale_number' => 'PG-TEST-001',
            'punto_venta_id' => $postgrado->id,
            'usuario_id' => $admin->id,
            'payment_method_id' => $metodoPago->id,
            'fecha_venta' => '2026-05-10 10:00:00',
            'type' => 'student_fee',
            'subtotal' => 3000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => 3000,
            'status' => 'completed',
        ]);
        $ventaPostgrado->timestamps = false;
        $ventaPostgrado->created_at = Carbon::parse('2026-05-10 10:00:00');
        $ventaPostgrado->updated_at = Carbon::parse('2026-05-10 10:00:00');
        $ventaPostgrado->save();

        $ventaOdonto = Sale::create([
            'sale_number' => 'OD-TEST-001',
            'punto_venta_id' => $odonto->id,
            'usuario_id' => $admin->id,
            'payment_method_id' => $metodoPago->id,
            'fecha_venta' => '2026-05-12 10:00:00',
            'type' => 'service_sale',
            'subtotal' => 3000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => 3000,
            'status' => 'completed',
        ]);
        $ventaOdonto->timestamps = false;
        $ventaOdonto->created_at = Carbon::parse('2026-05-12 10:00:00');
        $ventaOdonto->updated_at = Carbon::parse('2026-05-12 10:00:00');
        $ventaOdonto->save();

        $response = $this->actingAs($admin)
            ->get(route('admin.libro-caja.postgrado', [
                'fecha_desde' => '2026-05-01',
                'fecha_hasta' => '2026-05-31',
            ]));

        $response->assertOk();
        $response->assertViewHas('movimientos_caja', function (array $movimientosCaja) {
            if ((float) data_get($movimientosCaja, 'resumen_periodo.total_ingresos') !== 3000.0) {
                return false;
            }

            if ((float) data_get($movimientosCaja, 'resumen_periodo.total_egresos') !== 0.0) {
                return false;
            }

            return (float) data_get($movimientosCaja, 'resumen_periodo.saldo_periodo') === 3000.0;
        });
    }

    public function test_libro_caja_admin_odonto_filtra_por_periodo_y_punto_venta(): void
    {
        $odonto = PuntoVenta::create([
            'codigo' => 'ODONTO',
            'nombre' => 'Odonto',
            'activo' => true,
        ]);

        $postgrado = PuntoVenta::create([
            'codigo' => 'POSTGRADO',
            'nombre' => 'Postgrado',
            'activo' => true,
        ]);

        DB::table('sucursales')->insert([
            [
                'id' => $odonto->id,
                'name' => 'Sucursal Odonto',
                'code' => 'ODO',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $postgrado->id,
                'name' => 'Sucursal Postgrado',
                'code' => 'POS',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $admin = User::factory()->createOne([
            'role' => 'admin',
        ]);

        $metodoPago = PaymentMethod::create([
            'name' => 'Tarjeta',
            'code' => 'TDC',
            'type' => 'card',
            'requires_authorization' => false,
            'commission_percentage' => 0,
            'settlement_days' => 0,
            'is_active' => true,
        ]);

        $ventaOdonto = Sale::create([
            'sale_number' => 'OD-TEST-002',
            'punto_venta_id' => $odonto->id,
            'usuario_id' => $admin->id,
            'payment_method_id' => $metodoPago->id,
            'fecha_venta' => '2026-05-15 10:00:00',
            'type' => 'service_sale',
            'subtotal' => 5000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => 5000,
            'status' => 'completed',
        ]);
        $ventaOdonto->timestamps = false;
        $ventaOdonto->created_at = Carbon::parse('2026-05-15 10:00:00');
        $ventaOdonto->updated_at = Carbon::parse('2026-05-15 10:00:00');
        $ventaOdonto->save();

        $ventaPostgrado = Sale::create([
            'sale_number' => 'PG-TEST-002',
            'punto_venta_id' => $postgrado->id,
            'usuario_id' => $admin->id,
            'payment_method_id' => $metodoPago->id,
            'fecha_venta' => '2026-05-20 10:00:00',
            'type' => 'student_fee',
            'subtotal' => 2000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => 2000,
            'status' => 'completed',
        ]);
        $ventaPostgrado->timestamps = false;
        $ventaPostgrado->created_at = Carbon::parse('2026-05-20 10:00:00');
        $ventaPostgrado->updated_at = Carbon::parse('2026-05-20 10:00:00');
        $ventaPostgrado->save();

        $response = $this->actingAs($admin)
            ->get(route('admin.libro-caja.odonto', [
                'fecha_desde' => '2026-05-01',
                'fecha_hasta' => '2026-05-31',
            ]));

        $response->assertOk();
        $response->assertViewHas('movimientos_caja', function (array $movimientosCaja) {
            if ((float) data_get($movimientosCaja, 'resumen_periodo.total_ingresos') !== 5000.0) {
                return false;
            }

            if ((float) data_get($movimientosCaja, 'resumen_periodo.total_egresos') !== 0.0) {
                return false;
            }

            return (float) data_get($movimientosCaja, 'resumen_periodo.saldo_periodo') === 5000.0;
        });
    }
}
