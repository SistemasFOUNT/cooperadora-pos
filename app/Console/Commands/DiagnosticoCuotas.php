<?php

namespace App\Console\Commands;

use App\Models\CareerFeeConfig;
use App\Models\CuotaEstudiantil;
use App\Models\Student;
use Illuminate\Console\Command;

class DiagnosticoCuotas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:diagnostico-cuotas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnóstico rápido de estudiantes, cuotas y configuración de carreras';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== DIAGNÓSTICO DE CUOTAS ESTUDIANTILES ===');

        $this->info("\n1. ESTUDIANTES:");
        $totalEstudiantes = Student::query()->count();
        $this->info("   Total: $totalEstudiantes");

        $this->info("\n   Carreras únicas en BD:");
        $carreras = Student::query()->distinct()->pluck('carrera')->sort();
        foreach ($carreras as $c) {
            $count = Student::query()->where('carrera', $c)->count();
            $this->info("   - '$c': $count estudiantes");
        }

        $cuotas2026 = CuotaEstudiantil::query()->where('anio', 2026)->count();
        $cuotasMayo = CuotaEstudiantil::query()->where('anio', 2026)->where('numero_cuota', 5)->count();
        $this->info("\n2. CUOTAS:");
        $this->info("   Total 2026: $cuotas2026");
        $this->info("   Cuotas Mayo 2026: $cuotasMayo");

        $this->info("\n3. CONFIGURACIÓN DE CARRERAS:");
        $carreras = CareerFeeConfig::query()->orderBy('nombre_carrera')->get();
        foreach ($carreras as $c) {
            $vence = $c->dia_vencimiento ?? 'no configurado';
            $recargo = $c->porcentaje_recargo ?? 'no configurado';
            $this->info("   tipo_carrera='$c->tipo_carrera' ← nombre: {$c->nombre_carrera} (vence día $vence, recargo $recargo%)");
        }

        if ($cuotas2026 === 0 && $totalEstudiantes > 0) {
            $this->warn("\n⚠️  NO HAY CUOTAS GENERADAS PARA 2026 pero hay estudiantes!");
            $this->info("Sugerencia: ejecutar 'php artisan app:generar-cuotas' para tecnicaturas");
        }

        if ($cuotasMayo === 0) {
            $this->warn("\n⚠️  NO HAY CUOTAS DE MAYO para tecnicaturas");
        } else {
            $muestra = CuotaEstudiantil::query()->where('anio', 2026)->where('numero_cuota', 5)->first();
            if ($muestra) {
                $this->info("\nMuestra de cuota mayo:");
                $this->info("   Estudiante: {$muestra->estudiante_id}");
                $this->info("   Monto: {$muestra->monto_cuota}");
                $this->info("   Vencimiento: {$muestra->fecha_vencimiento}");
                $this->info("   Estado: {$muestra->estado}");
                $this->info("   Recargo: {$muestra->recargo_mora}");
            }
        }

        return self::SUCCESS;
    }
}
