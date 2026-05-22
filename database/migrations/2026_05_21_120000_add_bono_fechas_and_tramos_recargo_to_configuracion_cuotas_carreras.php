<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion_cuotas_carreras', function (Blueprint $table) {
            $table->date('bono_inicio_cobro')->nullable()->after('cuota_bono')
                ->comment('Fecha de inicio de cobro del bono estudiantil');
            $table->date('bono_fin_cobro')->nullable()->after('bono_inicio_cobro')
                ->comment('Fecha de fin de cobro del bono estudiantil');

            $table->tinyInteger('dia_vencimiento_1')->nullable()->after('dia_vencimiento')
                ->comment('Primer corte de vencimiento mensual para intereses (1-28)');
            $table->tinyInteger('dia_vencimiento_2')->nullable()->after('dia_vencimiento_1')
                ->comment('Segundo corte de vencimiento mensual para intereses (1-31)');

            $table->decimal('porcentaje_recargo_1', 5, 2)->nullable()->after('porcentaje_recargo')
                ->comment('Interes del tramo 1 (hasta primer vencimiento)');
            $table->decimal('porcentaje_recargo_2', 5, 2)->nullable()->after('porcentaje_recargo_1')
                ->comment('Interes del tramo 2 (entre primer y segundo vencimiento)');
            $table->decimal('porcentaje_recargo_3', 5, 2)->nullable()->after('porcentaje_recargo_2')
                ->comment('Interes del tramo 3 (despues del segundo vencimiento)');
        });

        DB::table('configuracion_cuotas_carreras')->update([
            'dia_vencimiento_1' => DB::raw('COALESCE(dia_vencimiento, 15)'),
            'dia_vencimiento_2' => DB::raw('COALESCE(dia_vencimiento, 15)'),
            'porcentaje_recargo_1' => DB::raw('COALESCE(porcentaje_recargo, 0)'),
            'porcentaje_recargo_2' => DB::raw('COALESCE(porcentaje_recargo, 0)'),
            'porcentaje_recargo_3' => DB::raw('COALESCE(porcentaje_recargo, 0)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('configuracion_cuotas_carreras', function (Blueprint $table) {
            $table->dropColumn([
                'bono_inicio_cobro',
                'bono_fin_cobro',
                'dia_vencimiento_1',
                'dia_vencimiento_2',
                'porcentaje_recargo_1',
                'porcentaje_recargo_2',
                'porcentaje_recargo_3',
            ]);
        });
    }
};
