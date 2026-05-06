<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Factura;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class FacturacionService
{
    /**
     * Genera factura local (interna) para control
     */
    public function generarFacturaLocal(Sale $sale, $datos_cliente = null)
    {
        try {
            $factura = new Factura();
            $factura->sale_id = $sale->id;
            $factura->tipo = 'local';
            $factura->numero = $this->generarNumeroFacturaLocal();
            $factura->fecha_emision = now();
            $factura->datos_cliente = json_encode($datos_cliente);
            $factura->subtotal = $sale->subtotal ?? $sale->total;
            $factura->total = $sale->total;
            $factura->estado = 'emitida';

            $factura->save();

            return $factura;

        } catch (\Exception $e) {
            Log::error('Error generando factura local: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Genera factura ARCA (oficial) con CAE
     */
    public function generarFacturaARCA(Sale $sale, $datos_cliente, $tipo_comprobante = 'B')
    {
        try {
            // TODO: Implementar integración con ARCA
            $factura = new Factura();
            $factura->sale_id = $sale->id;
            $factura->tipo = 'arca';
            $factura->tipo_comprobante = $tipo_comprobante; // A, B, C
            $factura->punto_venta = config('facturacion.punto_venta_arca');
            $factura->numero = $this->obtenerProximoNumeroARCA($tipo_comprobante);
            $factura->fecha_emision = now();
            $factura->datos_cliente = json_encode($datos_cliente);
            $factura->subtotal = $this->calcularSubtotal($sale);
            $factura->iva = $this->calcularIVA($sale, $tipo_comprobante);
            $factura->total = $sale->total;
            $factura->estado = 'pendiente_arca';

            // Solicitar CAE a ARCA
            $cae_response = $this->solicitarCAE($factura);

            if ($cae_response['success']) {
                $factura->cae = $cae_response['cae'];
                $factura->fecha_vto_cae = $cae_response['fecha_vto'];
                $factura->qr_arca = $this->generarQRArca($factura);
                $factura->estado = 'autorizada';
            } else {
                $factura->estado = 'rechazada';
                $factura->observaciones = $cae_response['error'];
            }

            $factura->save();

            return $factura;

        } catch (\Exception $e) {
            Log::error('Error generando factura ARCA: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Genera número correlativo para facturas locales
     */
    private function generarNumeroFacturaLocal()
    {
        $ultimo = Factura::where('tipo', 'local')
                         ->where('punto_venta_id', auth()->user()->punto_venta_id)
                         ->orderBy('numero', 'desc')
                         ->first();

        return $ultimo ? $ultimo->numero + 1 : 1;
    }

    /**
     * Obtiene próximo número de ARCA
     */
    private function obtenerProximoNumeroARCA($tipo_comprobante)
    {
        // TODO: Consultar a ARCA el próximo número disponible
        return 1; // Placeholder
    }

    /**
     * Calcula subtotal sin IVA
     */
    private function calcularSubtotal(Sale $sale)
    {
        // Si es consumidor final (tipo B), el subtotal es igual al total
        return $sale->total / 1.21; // Asumiendo 21% IVA
    }

    /**
     * Calcula IVA según tipo de comprobante
     */
    private function calcularIVA(Sale $sale, $tipo_comprobante)
    {
        if ($tipo_comprobante === 'B' || $tipo_comprobante === 'C') {
            return 0; // IVA incluido o no corresponde
        }

        return $sale->total - $this->calcularSubtotal($sale);
    }

    /**
     * Solicita CAE a ARCA
     */
    private function solicitarCAE(Factura $factura)
    {
        // TODO: Implementar conexión real con ARCA
        return [
            'success' => true,
            'cae' => '70123456789012',
            'fecha_vto' => Carbon::now()->addDays(10)->format('Y-m-d')
        ];
    }

    /**
     * Genera QR para verificación en ARCA
     */
    private function generarQRArca(Factura $factura)
    {
        $datos_qr = [
            'ver' => 1,
            'fecha' => $factura->fecha_emision->format('Y-m-d'),
            'cuit' => config('facturacion.cuit_emisor'),
            'ptoVta' => $factura->punto_venta,
            'tipoCmp' => $this->getTipoComprobanteNumero($factura->tipo_comprobante),
            'nroCmp' => $factura->numero,
            'importe' => $factura->total,
            'moneda' => 'PES',
            'ctz' => 1,
            'tipoCodAut' => 'E',
            'codAut' => $factura->cae
        ];

        return 'https://www.afip.gob.ar/fe/qr/?p=' . base64_encode(json_encode($datos_qr));
    }

    /**
     * Obtiene código numérico del tipo de comprobante
     */
    private function getTipoComprobanteNumero($tipo)
    {
        $tipos = [
            'A' => 1,  // Factura A
            'B' => 6,  // Factura B
            'C' => 11, // Factura C
        ];

        return $tipos[$tipo] ?? 6;
    }
}
