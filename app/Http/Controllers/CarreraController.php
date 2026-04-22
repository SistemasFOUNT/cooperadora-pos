<?php

namespace App\Http\Controllers;

use App\Models\CareerFeeConfig;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CarreraController extends Controller
{
    /**
     * Mostrar lista de carreras
     */
    public function index(): View
    {
        $carreras = CareerFeeConfig::orderBy('nombre_carrera')->paginate(15);

        return view('carreras.index', compact('carreras'));
    }

    /**
     * Mostrar formulario para crear nueva carrera
     */
    public function create(): View
    {
        return view('carreras.create');
    }

    /**
     * Almacenar nueva carrera
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_carrera' => 'required|string|max:255',
            'tipo_carrera' => 'required|in:Tecnicatura,Grado,Postgrado',
            'cuota_mensual' => 'required|numeric|min:0',
            'cuota_inscripcion' => 'nullable|numeric|min:0',
            'descuento_hermanos' => 'nullable|numeric|between:0,100',
            'descuento_empleados' => 'nullable|numeric|between:0,100',
            'activa' => 'boolean',
            'modalidad' => 'required|in:Presencial,Virtual,Mixta',
            'duracion_anios' => 'required|integer|min:1|max:10',
        ]);

        $validated['activa'] = $request->has('activa');

        CareerFeeConfig::create($validated);

        return redirect()->route('carreras.index')
            ->with('success', 'Carrera creada exitosamente.');
    }

    /**
     * Mostrar carrera específica
     */
    public function show(CareerFeeConfig $carrera): View
    {
        $estudiantes = $carrera->estudiantes()->paginate(10);

        return view('carreras.show', compact('carrera', 'estudiantes'));
    }

    /**
     * Mostrar formulario para editar carrera
     */
    public function edit(CareerFeeConfig $carrera): View
    {
        return view('carreras.edit', compact('carrera'));
    }

    /**
     * Actualizar carrera
     */
    public function update(Request $request, CareerFeeConfig $carrera): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_carrera' => 'required|string|max:255',
            'tipo_carrera' => 'required|in:Tecnicatura,Grado,Postgrado',
            'cuota_mensual' => 'required|numeric|min:0',
            'cuota_inscripcion' => 'nullable|numeric|min:0',
            'descuento_hermanos' => 'nullable|numeric|between:0,100',
            'descuento_empleados' => 'nullable|numeric|between:0,100',
            'activa' => 'boolean',
            'modalidad' => 'required|in:Presencial,Virtual,Mixta',
            'duracion_anios' => 'required|integer|min:1|max:10',
        ]);

        $validated['activa'] = $request->has('activa');

        $carrera->update($validated);

        return redirect()->route('carreras.index')
            ->with('success', 'Carrera actualizada exitosamente.');
    }

    /**
     * Eliminar carrera
     */
    public function destroy(CareerFeeConfig $carrera): RedirectResponse
    {
        // Verificar si hay estudiantes asociados
        if ($carrera->estudiantes()->exists()) {
            return redirect()->route('carreras.index')
                ->with('error', 'No se puede eliminar la carrera porque tiene estudiantes asociados.');
        }

        $carrera->delete();

        return redirect()->route('carreras.index')
            ->with('success', 'Carrera eliminada exitosamente.');
    }

    /**
     * Gestionar cuotas - Vista para configurar cuotas especiales
     */
    public function cuotas(): View
    {
        $carreras = CareerFeeConfig::where('activa', true)->orderBy('nombre_carrera')->get();

        return view('carreras.cuotas', compact('carreras'));
    }

    /**
     * Actualizar configuración de cuotas
     */
    public function actualizarCuotas(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'carrera_id' => 'required|exists:configuracion_cuotas_carreras,id',
            'cuota_mensual' => 'required|numeric|min:0',
            'cuota_inscripcion' => 'nullable|numeric|min:0',
            'descuento_hermanos' => 'nullable|numeric|between:0,100',
            'descuento_empleados' => 'nullable|numeric|between:0,100',
        ]);

        $carrera = CareerFeeConfig::findOrFail($validated['carrera_id']);
        unset($validated['carrera_id']);

        $carrera->update($validated);

        return redirect()->route('carreras.cuotas')
            ->with('success', 'Configuración de cuotas actualizada exitosamente.');
    }

    /**
     * Activar/desactivar carrera
     */
    public function toggleActiva(CareerFeeConfig $carrera): RedirectResponse
    {
        $carrera->update(['activa' => !$carrera->activa]);

        $status = $carrera->activa ? 'activada' : 'desactivada';

        return redirect()->route('carreras.index')
            ->with('success', "Carrera {$status} exitosamente.");
    }
}
