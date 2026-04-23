<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DocumentoLegal extends Model
{
    protected $table = 'documentos_legales';

    protected $fillable = [
        'cliente_deudor_id',
        'financiamiento_id',
        'tipo_documento',
        'numero_documento',
        'archivo_pdf_path',
        'archivo_pdf_size',
        'hash_documento',
        'fecha_emision',
        'fecha_firma',
        'fecha_vencimiento',
        'testigo_1_nombre',
        'testigo_1_dni',
        'testigo_2_nombre',
        'testigo_2_dni',
        'empleado_presente_id',
        'estado',
        'observaciones',
        'motivo_anulacion',
        'cantidad_impresiones',
        'ultima_impresion',
        'usuario_generacion_id'
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'fecha_firma' => 'datetime',
        'fecha_vencimiento' => 'date',
        'ultima_impresion' => 'datetime',
        'archivo_pdf_size' => 'integer',
        'cantidad_impresiones' => 'integer'
    ];

    /**
     * Relación con cliente deudor
     */
    public function clienteDeudor(): BelongsTo
    {
        return $this->belongsTo(ClienteDeudor::class);
    }

    /**
     * Relación con financiamiento
     */
    public function financiamiento(): BelongsTo
    {
        return $this->belongsTo(FinanciamientoOdontologia::class, 'financiamiento_id');
    }

    /**
     * Relación con empleado presente
     */
    public function empleadoPresente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'empleado_presente_id');
    }

    /**
     * Relación con usuario que generó el documento
     */
    public function usuarioGeneracion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_generacion_id');
    }

    /**
     * Generar número de documento único
     */
    public static function generarNumero($tipoDocumento)
    {
        $fecha = Carbon::now();
        $count = self::where('tipo_documento', $tipoDocumento)
            ->whereDate('created_at', $fecha->toDateString())
            ->count() + 1;

        $prefijo = match($tipoDocumento) {
            'compromiso_pago' => 'BOX-CP',
            'pagare' => 'BOX-PG',
            'actualizacion_datos' => 'BOX-AD',
            'cancelacion' => 'BOX-CN',
            'novacion' => 'BOX-NV',
            default => 'BOX-DOC'
        };

        return $prefijo . '-' . $fecha->format('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Verificar integridad del archivo
     */
    public function verificarIntegridad()
    {
        if (!Storage::exists($this->archivo_pdf_path)) {
            return false;
        }

        $hashActual = hash_file('sha256', Storage::path($this->archivo_pdf_path));
        return $hashActual === $this->hash_documento;
    }

    /**
     * Registrar impresión del documento
     */
    public function registrarImpresion()
    {
        $this->increment('cantidad_impresiones');
        $this->update(['ultima_impresion' => now()]);

        if ($this->estado === 'generado') {
            $this->update(['estado' => 'impreso']);
        }
    }

    /**
     * Marcar documento como firmado
     */
    public function marcarComoFirmado($testigo1Nombre, $testigo1Dni, $testigo2Nombre, $testigo2Dni)
    {
        $this->update([
            'estado' => 'firmado',
            'fecha_firma' => now(),
            'testigo_1_nombre' => $testigo1Nombre,
            'testigo_1_dni' => $testigo1Dni,
            'testigo_2_nombre' => $testigo2Nombre,
            'testigo_2_dni' => $testigo2Dni
        ]);
    }

    /**
     * Anular documento
     */
    public function anular($motivo)
    {
        $this->update([
            'estado' => 'anulado',
            'motivo_anulacion' => $motivo
        ]);
    }

    /**
     * Obtener la URL del archivo PDF
     */
    public function getUrlArchivoAttribute()
    {
        return Storage::url($this->archivo_pdf_path);
    }

    /**
     * Verificar si el documento está vencido
     */
    public function getEstaVencidoAttribute()
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento < now();
    }

    /**
     * Scope para documentos pendientes de firma
     */
    public function scopePendientesFirma($query)
    {
        return $query->where('estado', 'pendiente_firma');
    }

    /**
     * Scope para documentos firmados
     */
    public function scopeFirmados($query)
    {
        return $query->where('estado', 'firmado');
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($documento) {
            if (empty($documento->numero_documento)) {
                $documento->numero_documento = self::generarNumero($documento->tipo_documento);
            }

            if (empty($documento->fecha_emision)) {
                $documento->fecha_emision = now();
            }
        });

        static::deleting(function ($documento) {
            // Eliminar archivo físico al eliminar el registro
            if (Storage::exists($documento->archivo_pdf_path)) {
                Storage::delete($documento->archivo_pdf_path);
            }
        });
    }
}
