<?php

namespace App\Http\Controllers;

use App\Models\DocumentoLegal;
use App\Models\FinanciamientoOdontologia;
use App\Models\ConfiguracionOrganizacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class DocumentoLegalController extends Controller
{
    /**
     * Generar compromiso de pago
     */
    public function generarCompromisoPago($financiamientoId)
    {
        try {
            $financiamiento = FinanciamientoOdontologia::with([
                'clienteDeudor',
                'cuotas' => function($query) {
                    $query->orderBy('numero_cuota');
                }
            ])->findOrFail($financiamientoId);

            $organizacion = ConfiguracionOrganizacion::obtener();
            $empleado = Auth::user();

            // Generar número de documento
            $numeroDocumento = DocumentoLegal::generarNumero('compromiso_pago');

            // TODO: Implementar librería PDF (dompdf o similar)
            // Por ahora preparamos los datos para generar el documento

            $datosDocumento = [
                'organizacion' => $organizacion,
                'financiamiento' => $financiamiento,
                'cliente' => $financiamiento->clienteDeudor,
                'cuotas' => $financiamiento->cuotas,
                'empleado' => $empleado,
                'numero_documento' => $numeroDocumento,
                'fecha_emision' => now()
            ];

            // Simular archivo PDF por ahora
            $nombreArchivo = "compromiso_pago_{$financiamiento->id}_" . time() . ".pdf";
            $rutaArchivo = "documentos_legales/{$nombreArchivo}";

            // Guardar datos como JSON temporalmente hasta configurar PDF
            Storage::put(str_replace('.pdf', '.json', $rutaArchivo), json_encode($datosDocumento));

            // Registrar en BD
            $documento = DocumentoLegal::create([
                'cliente_deudor_id' => $financiamiento->cliente_deudor_id,
                'financiamiento_id' => $financiamiento->id,
                'tipo_documento' => 'compromiso_pago',
                'numero_documento' => $numeroDocumento,
                'archivo_pdf_path' => $rutaArchivo,
                'archivo_pdf_size' => strlen(json_encode($datosDocumento)),
                'hash_documento' => hash('sha256', json_encode($datosDocumento)),
                'fecha_emision' => now(),
                'empleado_presente_id' => Auth::id(),
                'usuario_generacion_id' => Auth::id(),
                'estado' => 'generado'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Compromiso de pago generado (JSON temporal)',
                'documento_id' => $documento->id,
                'datos' => $datosDocumento
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar compromiso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar impresión de documento
     */
    public function registrarImpresion($id)
    {
        try {
            $documento = DocumentoLegal::findOrFail($id);
            $documento->registrarImpresion();

            return response()->json([
                'success' => true,
                'message' => 'Impresión registrada',
                'cantidad_impresiones' => $documento->cantidad_impresiones
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar impresión: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar documento como firmado
     */
    public function marcarComoFirmado(Request $request, $id)
    {
        $request->validate([
            'testigo_1_nombre' => 'required|string|max:100',
            'testigo_1_dni' => 'required|string|max:10',
            'testigo_2_nombre' => 'required|string|max:100',
            'testigo_2_dni' => 'required|string|max:10',
            'observaciones' => 'nullable|string'
        ]);

        try {
            $documento = DocumentoLegal::findOrFail($id);

            $documento->marcarComoFirmado(
                $request->testigo_1_nombre,
                $request->testigo_1_dni,
                $request->testigo_2_nombre,
                $request->testigo_2_dni
            );

            if ($request->observaciones) {
                $documento->update(['observaciones' => $request->observaciones]);
            }

            // Activar el financiamiento si estaba pendiente de documentación
            if ($documento->financiamiento && $documento->financiamiento->estado === 'pendiente_documentacion') {
                $documento->financiamiento->update(['estado' => 'activo']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Documento marcado como firmado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar documento como firmado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Anular documento
     */
    public function anular(Request $request, $id)
    {
        $request->validate([
            'motivo_anulacion' => 'required|string|max:255'
        ]);

        try {
            $documento = DocumentoLegal::findOrFail($id);
            $documento->anular($request->motivo_anulacion);

            return response()->json([
                'success' => true,
                'message' => 'Documento anulado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al anular documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar documentos legales
     */
    public function index(Request $request)
    {
        $query = DocumentoLegal::with(['clienteDeudor', 'financiamiento', 'empleadoPresente']);

        // Filtros
        if ($request->has('tipo_documento')) {
            $query->where('tipo_documento', $request->tipo_documento);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('cliente_dni')) {
            $query->whereHas('clienteDeudor', function($q) use ($request) {
                $q->where('dni', 'like', '%' . $request->cliente_dni . '%');
            });
        }

        if ($request->has('numero_documento')) {
            $query->where('numero_documento', 'like', '%' . $request->numero_documento . '%');
        }

        $documentos = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($documentos);
    }

    /**
     * Obtener detalles del documento
     */
    public function show($id)
    {
        $documento = DocumentoLegal::with([
            'clienteDeudor',
            'financiamiento.cuotas',
            'empleadoPresente',
            'usuarioGeneracion'
        ])->findOrFail($id);

        return response()->json([
            'documento' => $documento,
            'integridad_verificada' => $documento->verificarIntegridad()
        ]);
    }

    /**
     * Descargar archivo PDF
     */
    public function descargar($id)
    {
        try {
            $documento = DocumentoLegal::findOrFail($id);

            if (!Storage::exists($documento->archivo_pdf_path)) {
                throw new \Exception('Archivo no encontrado');
            }

            if (!$documento->verificarIntegridad()) {
                throw new \Exception('Archivo corrupto - integridad comprometida');
            }

            return Storage::download($documento->archivo_pdf_path, basename($documento->archivo_pdf_path));

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al descargar archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar integridad de todos los documentos
     */
    public function verificarIntegridad()
    {
        $documentos = DocumentoLegal::all();
        $corruptos = [];

        foreach ($documentos as $documento) {
            if (!$documento->verificarIntegridad()) {
                $corruptos[] = [
                    'id' => $documento->id,
                    'numero_documento' => $documento->numero_documento,
                    'tipo_documento' => $documento->tipo_documento
                ];
            }
        }

        return response()->json([
            'total_documentos' => $documentos->count(),
            'documentos_corruptos' => count($corruptos),
            'detalles_corruptos' => $corruptos
        ]);
    }
}
