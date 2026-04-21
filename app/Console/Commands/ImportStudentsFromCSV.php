<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\CareerFeeConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportStudentsFromCSV extends Command
{
    protected $signature = 'students:import-csv {file} {career_type} {--dry-run} {--delimiter=;}';

    protected $description = 'Importar estudiantes desde archivo CSV con configuración de carrera específica';

    public function handle()
    {
        $file = $this->argument('file');
        $careerType = $this->argument('career_type');
        $dryRun = $this->option('dry-run');
        $delimiter = $this->option('delimiter');

        // Validar que el archivo exista
        if (!file_exists($file)) {
            $this->error("El archivo {$file} no existe.");
            return 1;
        }

        // Validar que la configuración de carrera exista
        $careerConfig = CareerFeeConfig::obtenerConfigCarrera($careerType);
        if (!$careerConfig) {
            $this->error("No existe configuración para la carrera: {$careerType}");
            $this->info("Carreras disponibles:");
            CareerFeeConfig::activo()->each(function($career) {
                $this->line("  - {$career->tipo_carrera}: {$career->nombre_carrera}");
            });
            return 1;
        }

        $this->info("Importando estudiantes de: {$file}");
        $this->info("Carrera: {$careerConfig->nombre_carrera} ({$careerType})");
        $this->info("Cuota mensual: $" . number_format($careerConfig->cuota_mensual, 2));

        if ($dryRun) {
            $this->warn("MODO DRY-RUN - No se realizarán cambios en la base de datos");
        }

        $csvData = $this->readCSV($file, $delimiter);

        if (empty($csvData)) {
            $this->error("No se pudieron leer datos del archivo CSV");
            return 1;
        }

        $this->info("Total de registros en CSV: " . count($csvData));

        $imported = 0;
        $errors = 0;
        $duplicates = 0;

        DB::beginTransaction();

        try {
            foreach ($csvData as $index => $row) {
                $lineNumber = $index + 2; // +2 porque empezamos en 0 y hay header

                try {
                    $studentData = $this->mapCSVToStudent($row, $careerConfig);

                    if (!$studentData) {
                        $this->warn("Línea {$lineNumber}: Datos insuficientes, saltando registro");
                        $errors++;
                        continue;
                    }

                    // Verificar duplicados por número de documento
                    if (Student::where('dni', $studentData['dni'])->exists()) {
                        $this->warn("Línea {$lineNumber}: Estudiante ya existe (DNI: {$studentData['dni']})");
                        $duplicates++;
                        continue;
                    }

                    if (!$dryRun) {
                        Student::create($studentData);
                    }

                    $imported++;

                    if ($imported % 50 == 0) {
                        $this->info("Procesados: {$imported} estudiantes...");
                    }

                } catch (\Exception $e) {
                    $this->error("Línea {$lineNumber}: Error - " . $e->getMessage());
                    $errors++;
                }
            }

            if (!$dryRun) {
                DB::commit();
                $this->info("✅ Importación completada exitosamente");
            } else {
                DB::rollBack();
                $this->info("✅ Simulación completada (dry-run)");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error durante la importación: " . $e->getMessage());
            return 1;
        }

        $this->info("📊 RESUMEN:");
        $this->info("  • Importados: {$imported}");
        $this->info("  • Duplicados: {$duplicates}");
        $this->info("  • Errores: {$errors}");

        return 0;
    }

    private function readCSV($file, $delimiter)
    {
        $data = [];

        if (($handle = fopen($file, 'r')) !== FALSE) {
            // Leer header
            $header = fgetcsv($handle, 1000, $delimiter);

            if (!$header) {
                $this->error("No se pudo leer el encabezado del CSV");
                return [];
            }

            // Limpiar BOM y caracteres especiales de los headers
            $header = array_map(function($field) {
                // Remover BOM UTF-8
                $field = str_replace("\xEF\xBB\xBF", '', $field);
                // Remover espacios y caracteres invisibles
                return trim($field);
            }, $header);

            $this->info("Columnas detectadas: " . implode(', ', $header));

            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (count($row) === count($header)) {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }

    private function mapCSVToStudent($row, $careerConfig)
    {
        // Mapeo directo con los nombres de campos en español
        $studentData = [
            // Campos del CSV
            'apellido' => isset($row['apellido']) ? trim($row['apellido']) : '',
            'nombre' => isset($row['nombre']) ? trim($row['nombre']) : '',
            'dni' => isset($row['dni']) ? trim($row['dni']) : '',
            'email' => isset($row['email']) ? trim($row['email']) : null,
            'telefono' => isset($row['telefono']) ? trim($row['telefono']) : null,
            'legajo' => isset($row['legajo']) ? trim($row['legajo']) : $this->generateStudentNumber($careerConfig->tipo_carrera),
            'plan' => isset($row['plan']) ? trim($row['plan']) : '',
            'ingreso' => isset($row['ingreso']) && !empty($row['ingreso']) ? (int) trim($row['ingreso']) : date('Y'),
            'reinscripcion' => isset($row['reinscripcion']) && !empty($row['reinscripcion']) ? (int) trim($row['reinscripcion']) : date('Y'),

            // Campos adicionales del sistema (en español)
            'carrera' => $careerConfig->tipo_carrera, // Solo la referencia
            'fecha_inscripcion' => $this->parseEnrollmentDate($row),
            'estado' => $this->determineStatus($row),
            'activo' => true,
        ];

        // Validar campos requeridos
        $required = ['apellido', 'nombre', 'dni'];
        foreach ($required as $field) {
            if (empty($studentData[$field])) {
                $this->warn("Campo requerido faltante: {$field}");
                return null;
            }
        }

        return $studentData;
    }

    private function parseEnrollmentDate($row)
    {
        // Usar año de ingreso directamente
        if (isset($row['ingreso']) && !empty($row['ingreso'])) {
            $year = (int) trim($row['ingreso']);
            if ($year >= 1990 && $year <= date('Y')) {
                return Carbon::create($year, 3, 1); // 1 de marzo del año de ingreso
            }
        }

        return Carbon::now();
    }

    private function determineStatus($row)
    {
        // Verificar año de reinscripción para determinar estado
        if (isset($row['reinscripcion']) && !empty($row['reinscripcion'])) {
            $reinscripcionYear = (int) trim($row['reinscripcion']);
            $currentYear = (int) date('Y');

            // Si la reinscripción es del año actual, está activo
            if ($reinscripcionYear === $currentYear) {
                return 'activo';
            }

            // Si la reinscripción es del año anterior, podría estar inactivo
            if ($reinscripcionYear === $currentYear - 1) {
                return 'inactivo';
            }

            // Si la reinscripción es muy antigua, probablemente abandono
            if ($reinscripcionYear < $currentYear - 2) {
                return 'abandono';
            }
        }

        // Por defecto activo
        return 'activo';
    }

    private function generateStudentNumber($careerType)
    {
        $prefix = match($careerType) {
            'tecnicatura_protesis' => 'TP',
            'tecnicatura_asistencia' => 'TA',
            'grado_odontologia' => 'OD',
            'postgrado' => 'PG',
            default => 'ST'
        };

        $year = date('Y');
        $lastNumber = Student::where('student_number', 'LIKE', "{$prefix}{$year}%")->count() + 1;

        return $prefix . $year . str_pad($lastNumber, 4, '0', STR_PAD_LEFT);
    }
}
