<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerarCuotas2026 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generar-cuotas2026';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera cuotas mensuales para estudiantes de tecnicaturas en 2026';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generando cuotas para 2026...');

        // Map carrera DB = tipo_carrera (son iguales)
        $mapCarreras = [
            'tecnicatura_protesis' => 'tecnicatura_protesis',
            'tecnicatura_asistencia' => 'tecnicatura_asistencia',
        ];

        $anio = 2026;
        $totalCreadas = 0;

        foreach ($mapCarreras as $carreraDB => $tipoCarrera) {
            $config = \App\Models\CareerFeeConfig::where('tipo_carrera', $tipoCarrera)->first();

            if (!$config) {
                $this->error("   No existe configuración para: $tipoCarrera");
                continue;
            }

            $estudiantes = \App\Models\Student::where('carrera', $carreraDB)->get();
            $this->info("   $carreraDB: {$estudiantes->count()} estudiantes");

            foreach ($estudiantes as $est) {
                $creadas = \App\Models\CuotaEstudiantil::generarParaEstudiante($est, $anio);
                $totalCreadas += count($creadas);
            }
        }

        $this->info("\n✅ Total cuotas creadas: $totalCreadas");

        // Verificar
        $cuotas = \App\Models\CuotaEstudiantil::where('anio', 2026)->count();
        $mayo = \App\Models\CuotaEstudiantil::where('anio', 2026)->where('numero_cuota', 5)->count();

        $this->info("Cuotas totales 2026: $cuotas");
        $this->info("Cuotas Mayo 2026: $mayo");
    }
}
