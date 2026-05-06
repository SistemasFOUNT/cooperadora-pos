<?php

namespace App\Services;

require_once(base_path('vendor/setasign/fpdf/fpdf.php'));

class PDFFactura extends \FPDF
{
    private $factura;
    private $datosEmisor;
    private $datosCliente;
    private $items;
    private $configuracion;

    public function __construct()
    {
        parent::__construct('P', 'mm', 'A4');
        $this->configuracion = config('facturacion');
    }

    public function generar($factura)
    {
        $this->factura = $factura;
        $this->datosEmisor = $this->configuracion['emisor'];
        $this->datosCliente = $this->obtenerDatosCliente();
        $this->items = $factura->sale->items;

        // Configurar PDF
        $this->AddPage();
        $this->SetMargins(15, 15, 15);
        $this->SetAutoPageBreak(true, 25);

        // Generar contenido
        $this->generarEncabezado();
        $this->generarDatosFactura();
        $this->generarDatosPartes();
        $this->generarDetalleItems();
        $this->generarTotales();
        $this->generarPie();

        return $this;
    }

    /**
     * Encabezado de la factura
     */
    private function generarEncabezado()
    {
        // Logo si existe
        if (file_exists(public_path($this->datosEmisor['logo']))) {
            $this->Image(public_path($this->datosEmisor['logo']), 15, 15, 30);
        }

        // Razón Social
        $this->SetFont('Arial', 'B', 16);
        $this->SetXY(50, 15);
        $this->Cell(0, 8, utf8_decode($this->datosEmisor['razon_social']), 0, 1);

        // Datos del emisor
        $this->SetFont('Arial', '', 10);
        $this->SetX(50);
        $this->Cell(0, 5, utf8_decode($this->datosEmisor['domicilio']), 0, 1);
        $this->SetX(50);
        $this->Cell(0, 5, utf8_decode($this->datosEmisor['localidad'] . ', ' . $this->datosEmisor['provincia']), 0, 1);
        
        if ($this->datosEmisor['telefono']) {
            $this->SetX(50);
            $this->Cell(0, 5, 'Tel: ' . $this->datosEmisor['telefono'], 0, 1);
        }

        // Tipo de documento (centrado)
        $this->SetFont('Arial', 'B', 14);
        $this->SetY(15);
        
        if ($this->factura->tipo == 'arca') {
            $tipoDoc = 'FACTURA ' . $this->factura->tipo_comprobante;
            $this->Cell(0, 12, $tipoDoc, 1, 1, 'C');
            $this->SetFont('Arial', '', 10);
            $this->Cell(0, 5, 'DOCUMENTO NO VALIDO COMO FACTURA', 0, 1, 'C');
        } else {
            $this->Cell(0, 12, 'FACTURA LOCAL', 1, 1, 'C');
            $this->SetFont('Arial', '', 10);
            $this->Cell(0, 5, 'DOCUMENTO INTERNO', 0, 1, 'C');
        }

        $this->Ln(10);
    }

    /**
     * Datos de la factura (número, fecha, CAE)
     */
    private function generarDatosFactura()
    {
        $y_inicial = $this->GetY();
        
        // Marco para datos de factura
        $this->SetDrawColor(0, 0, 0);
        $this->Rect(15, $y_inicial, 180, 25);

        $this->SetY($y_inicial + 3);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 6, 'DATOS DE LA FACTURA', 0, 1, 'C');
        
        $this->SetFont('Arial', '', 10);
        $this->SetX(20);
        $this->Cell(85, 5, 'Número: ' . $this->factura->numero_completo, 0, 0);
        $this->Cell(85, 5, 'Fecha: ' . $this->factura->formatearFecha(), 0, 1);
        
        if ($this->factura->tipo == 'arca' && $this->factura->cae) {
            $this->SetX(20);
            $this->Cell(85, 5, 'CAE: ' . $this->factura->cae, 0, 0);
            $this->Cell(85, 5, 'Vto. CAE: ' . $this->factura->fecha_vto_cae->format('d/m/Y'), 0, 1);
        }
        
        $this->SetX(20);
        $this->Cell(85, 5, 'CUIT: ' . $this->datosEmisor['cuit'], 0, 0);
        $this->Cell(85, 5, 'Condición IVA: ' . $this->datosEmisor['condicion_iva'], 0, 1);

        $this->Ln(5);
    }

    /**
     * Datos del emisor y cliente
     */
    private function generarDatosPartes()
    {
        $y_inicial = $this->GetY();

        // Emisor (izquierda)
        $this->SetDrawColor(0, 0, 0);
        $this->Rect(15, $y_inicial, 85, 35);
        
        $this->SetY($y_inicial + 2);
        $this->SetX(20);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 5, 'DATOS DEL EMISOR', 0, 1);
        
        $this->SetFont('Arial', '', 9);
        $this->SetX(20);
        $this->Cell(0, 4, utf8_decode($this->datosEmisor['razon_social']), 0, 1);
        $this->SetX(20);
        $this->Cell(0, 4, utf8_decode($this->datosEmisor['domicilio']), 0, 1);
        $this->SetX(20);
        $this->Cell(0, 4, utf8_decode($this->datosEmisor['localidad'] . ', ' . $this->datosEmisor['provincia']), 0, 1);
        $this->SetX(20);
        $this->Cell(0, 4, 'Email: ' . $this->datosEmisor['email'], 0, 1);

        // Cliente (derecha)
        $this->Rect(110, $y_inicial, 85, 35);
        
        $this->SetY($y_inicial + 2);
        $this->SetX(115);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 5, 'DATOS DEL CLIENTE', 0, 1);
        
        $this->SetFont('Arial', '', 9);
        $this->SetX(115);
        $this->Cell(0, 4, utf8_decode($this->datosCliente['nombre']), 0, 1);
        
        if ($this->datosCliente['cuit']) {
            $this->SetX(115);
            $this->Cell(0, 4, 'CUIT: ' . $this->datosCliente['cuit'], 0, 1);
        }
        
        if ($this->datosCliente['domicilio']) {
            $this->SetX(115);
            $this->Cell(0, 4, utf8_decode($this->datosCliente['domicilio']), 0, 1);
        }
        
        $this->SetX(115);
        $this->Cell(0, 4, $this->datosCliente['condicion_iva'], 0, 1);

        $this->SetY($y_inicial + 40);
    }

    /**
     * Detalle de productos/servicios
     */
    private function generarDetalleItems()
    {
        $y_inicial = $this->GetY();
        
        // Cabecera de la tabla
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(240, 240, 240);
        
        // Anchos de columnas
        $w = array(25, 80, 20, 25, 25);
        $headers = array('Código', 'Descripción', 'Cant.', 'Precio Unit.', 'Subtotal');
        
        for($i = 0; $i < count($headers); $i++) {
            $this->Cell($w[$i], 8, $headers[$i], 1, 0, 'C', true);
        }
        $this->Ln();

        // Datos de la tabla
        $this->SetFont('Arial', '', 9);
        $this->SetFillColor(255, 255, 255);
        
        foreach ($this->items as $item) {
            $codigo = $item->product_code ?? $item->product->code ?? 'N/A';
            $descripcion = $item->product_name ?? $item->product->name ?? 'Producto';
            $cantidad = $item->quantity ?? 1;
            $precio = $item->unit_price ?? 0;
            $subtotal = $item->total ?? 0;

            // Truncar descripción si es muy larga
            if (strlen($descripcion) > 35) {
                $descripcion = substr($descripcion, 0, 32) . '...';
            }

            $this->Cell($w[0], 6, $codigo, 1, 0, 'C');
            $this->Cell($w[1], 6, utf8_decode($descripcion), 1, 0, 'L');
            $this->Cell($w[2], 6, $cantidad, 1, 0, 'C');
            $this->Cell($w[3], 6, '$' . number_format($precio, 2, ',', '.'), 1, 0, 'R');
            $this->Cell($w[4], 6, '$' . number_format($subtotal, 2, ',', '.'), 1, 1, 'R');
        }

        $this->Ln(5);
    }

    /**
     * Sección de totales
     */
    private function generarTotales()
    {
        // QR Code para facturas ARCA (lado izquierdo)
        if ($this->factura->tipo == 'arca' && $this->factura->qr_arca) {
            // Generar QR usando un servicio externo
            $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=" . urlencode($this->factura->qr_arca);
            
            try {
                $this->Image($qr_url, 15, $this->GetY(), 25, 25);
                $this->SetXY(15, $this->GetY() + 27);
                $this->SetFont('Arial', '', 7);
                $this->Cell(25, 3, 'Verificar en AFIP', 0, 1, 'C');
            } catch (Exception $e) {
                // Si falla el QR, continuar sin él
            }
        }

        // Totales (lado derecho)
        $this->SetXY(140, $this->GetY() - 30);
        $this->SetFont('Arial', '', 10);
        
        // Si es factura A, mostrar subtotal e IVA
        if ($this->factura->tipo_comprobante == 'A' && $this->factura->iva > 0) {
            $this->Cell(30, 6, 'Subtotal:', 0, 0, 'R');
            $this->Cell(25, 6, '$' . number_format($this->factura->subtotal, 2, ',', '.'), 1, 1, 'R');
            $this->SetX(140);
            $this->Cell(30, 6, 'IVA (21%):', 0, 0, 'R');
            $this->Cell(25, 6, '$' . number_format($this->factura->iva, 2, ',', '.'), 1, 1, 'R');
            $this->SetX(140);
        }
        
        // Total final
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(30, 8, 'TOTAL:', 0, 0, 'R');
        $this->Cell(25, 8, '$' . number_format($this->factura->total, 2, ',', '.'), 1, 1, 'R');

        $this->Ln(10);
    }

    /**
     * Pie de la factura
     */
    private function generarPie()
    {
        // Información de venta
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 4, 'Venta #' . $this->factura->sale_id . ' - Cajero: ' . ($this->factura->sale->user->name ?? 'N/A'), 0, 1);
        $this->Cell(0, 4, 'Método de pago: ' . ($this->factura->sale->paymentMethod->name ?? 'Efectivo'), 0, 1);
        
        if ($this->factura->estado == 'anulada') {
            $this->Ln(5);
            $this->SetFont('Arial', 'B', 14);
            $this->SetTextColor(255, 0, 0);
            $this->Cell(0, 8, '*** FACTURA ANULADA ***', 0, 1, 'C');
            if ($this->factura->observaciones) {
                $this->SetFont('Arial', '', 10);
                $this->Cell(0, 6, 'Motivo: ' . utf8_decode($this->factura->observaciones), 0, 1, 'C');
            }
            $this->SetTextColor(0, 0, 0);
        }

        // Pie final
        $this->Ln(5);
        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 3, 'Factura generada el ' . $this->factura->created_at->format('d/m/Y H:i:s'), 0, 1, 'C');
        
        if ($this->factura->tipo == 'arca') {
            $this->Cell(0, 3, 'Para verificar esta factura ingrese a www.afip.gob.ar', 0, 1, 'C');
        }
    }

    /**
     * Obtener datos del cliente desde la factura
     */
    private function obtenerDatosCliente()
    {
        return [
            'nombre' => $this->factura->razon_social_cliente ?? 
                       ($this->factura->datos_cliente['nombre'] ?? 'Consumidor Final'),
            'cuit' => $this->factura->cuit_cliente ?? 
                     ($this->factura->datos_cliente['cuit'] ?? ''),
            'domicilio' => $this->factura->datos_cliente['domicilio'] ?? '',
            'condicion_iva' => $this->factura->datos_cliente['condicion_iva'] ?? 'Consumidor Final'
        ];
    }

    /**
     * Generar y descargar PDF
     */
    public function descargar($nombre_archivo = null)
    {
        if (!$nombre_archivo) {
            $nombre_archivo = 'factura_' . $this->factura->numero_completo . '.pdf';
        }

        return $this->Output($nombre_archivo, 'D');
    }

    /**
     * Mostrar PDF en el navegador
     */
    public function mostrar()
    {
        return $this->Output('factura_' . $this->factura->numero_completo . '.pdf', 'I');
    }
}