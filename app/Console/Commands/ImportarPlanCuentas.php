<?php

namespace App\Console\Commands;

use App\Models\CuentaContable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportarPlanCuentas extends Command
{
    protected $signature = 'plan-cuentas:importar {archivo?}';
    protected $description = 'Importa el plan de cuentas desde un archivo CSV';

    public function handle()
    {
        $archivo = $this->argument('archivo') ?? base_path('Plan de cuentas.csv');

        if (!file_exists($archivo)) {
            $this->error("El archivo {$archivo} no existe.");
            return 1;
        }

        $this->info("Importando plan de cuentas desde: {$archivo}");

        // Leer el archivo CSV
        $handle = fopen($archivo, 'r');
        $header = fgetcsv($handle, 0, ';'); // Primera línea con headers

        $cuentas = [];
        $contador = 0;

        while (($data = fgetcsv($handle, 0, ';')) !== FALSE) {
            if (count($data) >= 2) {
                $codigo = trim($data[0]);
                $nombre = trim($data[1]);

                if (!empty($codigo) && !empty($nombre)) {
                    $cuentas[] = [
                        'codigo' => $codigo,
                        'nombre' => $nombre
                    ];
                    $contador++;
                }
            }
        }
        fclose($handle);

        $this->info("Se encontraron {$contador} cuentas en el archivo.");

        // Procesar e insertar cuentas
        DB::beginTransaction();

        try {
            // Limpiar tabla actual
            if ($this->confirm('¿Desea limpiar las cuentas existentes antes de importar?')) {
                CuentaContable::truncate();
                $this->info('Tabla de cuentas limpiada.');
            }

            $barra = $this->output->createProgressBar(count($cuentas));
            $barra->start();

            foreach ($cuentas as $cuentaData) {
                $cuenta = new CuentaContable();
                $cuenta->codigo = $cuentaData['codigo'];
                $cuenta->nombre = $cuentaData['nombre'];

                // Determinar nivel basado en los puntos en el código
                $cuenta->nivel = substr_count($cuentaData['codigo'], '.') + 1;

                // Determinar tipo automáticamente
                $cuenta->tipo = $cuenta->determinarTipo();

                // Determinar naturaleza
                $cuenta->naturaleza = $cuenta->determinarNaturaleza();

                // Determinar si es imputable (las cuentas de último nivel son imputables)
                $cuenta->es_imputable = $this->esUltimoNivel($cuentaData['codigo'], $cuentas);

                $cuenta->activa = true;
                $cuenta->saldo_inicial = 0;

                $cuenta->save();

                $barra->advance();
            }

            // Establecer relaciones padre-hijo
            $this->establecerRelacionesPadreHijo();

            $barra->finish();
            $this->line('');

            DB::commit();
            $this->info("✅ Plan de cuentas importado exitosamente. {$contador} cuentas procesadas.");

        } catch (\Exception $e) {
            DB::rollback();
            $this->error("❌ Error al importar: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function esUltimoNivel($codigo, $todasLasCuentas)
    {
        // Una cuenta es del último nivel si no hay otras cuentas que empiecen con su código seguido de un punto
        foreach ($todasLasCuentas as $cuenta) {
            if (str_starts_with($cuenta['codigo'], $codigo . '.')) {
                return false; // Hay cuentas hijas
            }
        }
        return true; // No hay cuentas hijas, es del último nivel
    }

    private function establecerRelacionesPadreHijo()
    {
        $this->info('Estableciendo relaciones padre-hijo...');

        $cuentas = CuentaContable::all();

        foreach ($cuentas as $cuenta) {
            $codigoPadre = $this->obtenerCodigoPadre($cuenta->codigo);

            if ($codigoPadre) {
                $padre = CuentaContable::where('codigo', $codigoPadre)->first();
                if ($padre) {
                    $cuenta->cuenta_padre_id = $padre->id;
                    $cuenta->save();
                }
            }
        }
    }

    private function obtenerCodigoPadre($codigo)
    {
        // Para 1.1.1.01.001, el padre sería 1.1.1.01.000
        $partes = explode('.', $codigo);

        if (count($partes) <= 1) {
            return null; // Es cuenta raíz
        }

        // Buscar el padre más específico disponible
        for ($i = count($partes) - 1; $i > 0; $i--) {
            $partesTemp = array_slice($partes, 0, $i);
            $partesTemp[] = str_repeat('0', strlen($partes[$i]));

            for ($j = $i + 1; $j < count($partes); $j++) {
                $partesTemp[] = str_repeat('0', strlen($partes[$j]));
            }

            $codigoCandidato = implode('.', $partesTemp);

            if (CuentaContable::where('codigo', $codigoCandidato)->exists()) {
                return $codigoCandidato;
            }
        }

        return null;
    }
}
