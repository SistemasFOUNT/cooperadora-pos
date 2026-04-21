<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizeStudentNames extends Command
{
    protected $signature = 'students:normalize-names {--dry-run}';

    protected $description = 'Normalizar el formato de nombres y apellidos de estudiantes (Primera letra mayúscula, resto minúscula)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn("MODO DRY-RUN - No se realizarán cambios en la base de datos");
        }

        $this->info("Analizando estudiantes para normalizar nombres y apellidos...");

        $students = Student::all();
        $totalStudents = $students->count();
        $updated = 0;
        $changes = [];

        $this->info("Total de estudiantes a procesar: {$totalStudents}");

        DB::beginTransaction();

        try {
            foreach ($students as $student) {
                $originalNombre = $student->nombre;
                $originalApellido = $student->apellido;

                // Normalizar nombres usando mb_convert_case para UTF-8
                $normalizedNombre = $this->normalizeNameUTF8($originalNombre);
                $normalizedApellido = $this->normalizeNameUTF8($originalApellido);

                $hasChanges = false;

                // Verificar si hay cambios necesarios
                if ($originalNombre !== $normalizedNombre) {
                    $changes[] = [
                        'type' => 'nombre',
                        'student_id' => $student->id,
                        'dni' => $student->dni,
                        'original' => $originalNombre,
                        'normalized' => $normalizedNombre
                    ];
                    $hasChanges = true;
                }

                if ($originalApellido !== $normalizedApellido) {
                    $changes[] = [
                        'type' => 'apellido',
                        'student_id' => $student->id,
                        'dni' => $student->dni,
                        'original' => $originalApellido,
                        'normalized' => $normalizedApellido
                    ];
                    $hasChanges = true;
                }

                // Aplicar cambios si no es dry-run
                if ($hasChanges && !$dryRun) {
                    $student->update([
                        'nombre' => $normalizedNombre,
                        'apellido' => $normalizedApellido
                    ]);
                    $updated++;
                }

                if ($hasChanges) {
                    $this->line("✏️  {$originalApellido}, {$originalNombre} → {$normalizedApellido}, {$normalizedNombre} (DNI: {$student->dni})");
                }
            }

            if (!$dryRun) {
                DB::commit();
                $this->info("✅ Normalización completada exitosamente");
            } else {
                DB::rollBack();
                $this->info("✅ Análisis completado (dry-run)");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error durante la normalización: " . $e->getMessage());
            return 1;
        }

        $this->info("📊 RESUMEN:");
        $this->info("  • Total estudiantes: {$totalStudents}");
        $this->info("  • Con cambios necesarios: " . collect($changes)->groupBy('student_id')->count());
        $this->info("  • Campos modificados: " . count($changes));

        if (!$dryRun) {
            $this->info("  • Registros actualizados: {$updated}");
        }

        return 0;
    }

    private function normalizeNameUTF8($name)
    {
        if (empty($name)) {
            return $name;
        }

        // Convertir a minúsculas manteniendo UTF-8
        $name = mb_strtolower(trim($name), 'UTF-8');

        // Capitalizar la primera letra de cada palabra
        $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');

        // Manejar casos especiales como "de", "del", "la", etc.
        $name = preg_replace_callback('/\b(de|del|la|los|las|y|e|da|das|do|dos)\b/iu', function($matches) {
            return mb_strtolower($matches[0], 'UTF-8');
        }, $name);

        // Volver a capitalizar la primera letra después de guiones o apostrofes
        $name = preg_replace_callback('/([\'"-])\s*([a-záéíóúñüà-ÿ])/iu', function($matches) {
            return $matches[1] . mb_strtoupper($matches[2], 'UTF-8');
        }, $name);

        return $name;
    }
}
