<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\CareerFeeConfig;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /**
     * Mostrar lista de estudiantes
     */
    public function index(): View
    {
        $estudiantes = Student::with('configuracionCarrera')
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->paginate(15);

        return view('estudiantes.index', compact('estudiantes'));
    }

    /**
     * Mostrar formulario para crear nuevo estudiante
     */
    public function create(): View
    {
        $carreras = CareerFeeConfig::where('activa', true)
            ->orderBy('nombre_carrera')
            ->get();

        return view('estudiantes.create', compact('carreras'));
    }

    /**
     * Almacenar nuevo estudiante
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'apellido' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'dni' => 'required|string|unique:estudiantes',
            'email' => 'nullable|email|unique:estudiantes',
            'telefono' => 'nullable|string|max:20',
            'carrera_id' => 'required|exists:configuracion_cuotas_carreras,id',
            'anio_academico' => 'required|string|max:10',
            'fecha_inscripcion' => 'required|date',
            'estado' => 'required|in:activo,inactivo,graduado,abandonado',
            'direccion' => 'nullable|string|max:500',
            'fecha_nacimiento' => 'nullable|date',
        ]);

        Student::create($validated);

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante creado exitosamente.');
    }

    /**
     * Mostrar estudiante específico
     */
    public function show(Student $estudiante): View
    {
        $estudiante->load('configuracionCarrera');

        return view('estudiantes.show', compact('estudiante'));
    }

    /**
     * Mostrar formulario para editar estudiante
     */
    public function edit(Student $estudiante): View
    {
        $carreras = CareerFeeConfig::where('activa', true)
            ->orderBy('nombre_carrera')
            ->get();

        return view('estudiantes.edit', compact('estudiante', 'carreras'));
    }

    /**
     * Actualizar estudiante
     */
    public function update(Request $request, Student $estudiante): RedirectResponse
    {
        $validated = $request->validate([
            'apellido' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'dni' => 'required|string|unique:estudiantes,dni,' . $estudiante->id,
            'email' => 'nullable|email|unique:estudiantes,email,' . $estudiante->id,
            'telefono' => 'nullable|string|max:20',
            'carrera_id' => 'required|exists:configuracion_cuotas_carreras,id',
            'anio_academico' => 'required|string|max:10',
            'fecha_inscripcion' => 'required|date',
            'estado' => 'required|in:activo,inactivo,graduado,abandonado',
            'direccion' => 'nullable|string|max:500',
            'fecha_nacimiento' => 'nullable|date',
        ]);

        $estudiante->update($validated);

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante actualizado exitosamente.');
    }

    /**
     * Eliminar estudiante
     */
    public function destroy(Student $estudiante): RedirectResponse
    {
        $estudiante->delete();

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante eliminado exitosamente.');
    }

    /**
     * Mostrar formulario de importación desde CSV
     */
    public function importar(): View
    {
        return view('estudiantes.importar');
    }

    /**
     * Procesar importación desde CSV
     */
    public function procesarImportacion(Request $request): RedirectResponse
    {
        $request->validate([
            'archivo_csv' => 'required|file|mimes:csv,txt|max:2048',
            'carrera_default' => 'required|exists:configuracion_cuotas_carreras,id',
        ]);

        $archivo = $request->file('archivo_csv');
        $carrera_id = $request->carrera_default;

        // Guardar archivo temporalmente
        $path = $archivo->store('temp-csv');

        try {
            // Ejecutar comando de importación
            \Artisan::call('estudiantes:importar-csv', [
                'archivo' => storage_path('app/' . $path),
                'carrera_id' => $carrera_id,
            ]);

            // Eliminar archivo temporal
            Storage::delete($path);

            $output = \Artisan::output();

            return redirect()->route('estudiantes.index')
                ->with('success', 'Importación completada. ' . $output);

        } catch (\Exception $e) {
            // Eliminar archivo temporal en caso de error
            Storage::delete($path);

            return redirect()->route('estudiantes.importar')
                ->with('error', 'Error durante la importación: ' . $e->getMessage());
        }
    }

    /**
     * Activar/desactivar estudiante
     */
    public function toggleEstado(Student $estudiante): RedirectResponse
    {
        $nuevoEstado = $estudiante->estado === 'activo' ? 'inactivo' : 'activo';
        $estudiante->update(['estado' => $nuevoEstado]);

        return redirect()->route('estudiantes.index')
            ->with('success', "Estado del estudiante cambiado a: {$nuevoEstado}");
    }

    /**
     * Búsqueda AJAX de estudiantes
     */
    public function buscar(Request $request)
    {
        $term = $request->get('term');

        $estudiantes = Student::where('apellido', 'LIKE', "%{$term}%")
            ->orWhere('nombre', 'LIKE', "%{$term}%")
            ->orWhere('dni', 'LIKE', "%{$term}%")
            ->with('configuracionCarrera')
            ->limit(10)
            ->get();

        return response()->json($estudiantes->map(function($estudiante) {
            return [
                'id' => $estudiante->id,
                'text' => "{$estudiante->apellido}, {$estudiante->nombre} - {$estudiante->dni}",
                'apellido' => $estudiante->apellido,
                'nombre' => $estudiante->nombre,
                'dni' => $estudiante->dni,
                'carrera' => $estudiante->configuracionCarrera->nombre_carrera ?? 'Sin carrera',
            ];
        }));
    }
}
