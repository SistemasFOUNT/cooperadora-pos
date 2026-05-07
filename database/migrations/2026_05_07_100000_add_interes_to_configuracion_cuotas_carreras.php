<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion_cuotas_carreras', function (Blueprint $table) {
            $table->tinyInteger('dia_vencimiento')->default(15)->after('duracion_meses')
                ->comment('Día del mes en que vence la cuota (1-28)');
            $table->tinyInteger('dias_gracia')->default(5)->after('dia_vencimiento')
                ->comment('Días de gracia antes de aplicar recargo');
            $table->decimal('porcentaje_recargo', 5, 2)->default(10.00)->after('dias_gracia')
                ->comment('Porcentaje de recargo por mora');
            $table->decimal('cuota_bono', 12, 2)->default(0.00)->after('porcentaje_recargo')
                ->comment('Monto del bono estudiantil');
        });
    }

    public function down(): void
    {
        Schema::table('configuracion_cuotas_carreras', function (Blueprint $table) {
            $table->dropColumn(['dia_vencimiento', 'dias_gracia', 'porcentaje_recargo', 'cuota_bono']);
        });
    }
};
