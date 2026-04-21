<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateStudentAcademicYears extends Command
{
    protected $signature = 'students:update-academic-years {--dry-run}';

    protected $description = 'Actualizar academic_year de enteros a valores correctos del plan de estudio';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn("MODO DRY-RUN - No se realizarán cambios en la base de datos");
        }

        $this->info("Actualizando academic_year según carrera...");

        $students = Student::all();
        $updated = 0;

        DB::beginTransaction();

        try {
            foreach ($students as $student) {
                $newAcademicYear = null;

                // Determinar el plan correcto según la carrera
                switch ($student->career_type) {
                    case 'tecnicatura_protesis':
                        $newAcademicYear = '78919';
                        break;

                    case 'tecnicatura_asistencia':
                        $newAcademicYear = '20_TECAD';
                        break;

                    case 'grado_odontologia':
                        // Mantener el valor actual si es numérico, sino asignar por defecto
                        $newAcademicYear = is_numeric($student->academic_year) ? (string) $student->academic_year : '2023';
                        break;

                    case 'postgrado':
                        $newAcademicYear = is_numeric($student->academic_year) ? (string) $student->academic_year : '2024';
                        break;

                    default:
                        $newAcademicYear = (string) $student->academic_year;
                        break;
                }

                if ($student->academic_year !== $newAcademicYear) {
                    $this->line("📝 Actualizando {$student->last_name}, {$student->first_name} ({$student->career_type}): {$student->academic_year} → {$newAcademicYear}");

                    if (!$dryRun) {
                        $student->update(['academic_year' => $newAcademicYear]);
                    }
                    $updated++;
                }
            }

            if (!$dryRun) {
                DB::commit();
                $this->info("✅ Actualización completada exitosamente");
            } else {
                DB::rollBack();
                $this->info("✅ Análisis completado (dry-run)");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error durante la actualización: " . $e->getMessage());
            return 1;
        }

        $this->info("📊 RESUMEN:");
        $this->info("  • Total estudiantes: " . $students->count());
        $this->info("  • Actualizados: {$updated}");

        return 0;
    }
}
