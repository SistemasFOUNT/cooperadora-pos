<?php

namespace App\Services;

use FPDF;

class PDFTicket extends FPDF
{
    private $datos;

    public function __construct($datos)
    {
        parent::__construct('P', 'mm', [80, 150]);
        $this->datos = $datos;
        $this->SetAutoPageBreak(false);
        $this->SetMargins(5, 5, 5);
    }

    /**
     * Convierte texto UTF-8 a ISO-8859-1 para compatibilidad con FPDF
     */
    private function convertirTexto($texto)
    {
        return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    }

    /**
     * Genera el ticket completo
     */
    public function generar()
    {
        $this->AddPage();
        $this->generarHeader();
        $this->generarDetalleProductos();
        $this->generarTotales();
        $this->generarMetodoPago();
        $this->generarObservaciones();
        $this->generarFooter();

        return $this;
    }

    /**
     * Header del ticket
     */
    private function generarHeader()
    {
        // Título principal
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 5, $this->convertirTexto('Asociación Cooperadora'), 0, 1, 'C');

        // Subtítulo
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 3, $this->convertirTexto('Facultad de Odontología - UNT'), 0, 1, 'C');
        $this->Ln(1);

        // Información del punto de venta
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 4, 'BOX - Punto de Venta', 0, 1, 'C');

        // Fecha y hora en zona horaria de Argentina
        $fechaArgentina = now()->setTimezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i:s');
        $this->Cell(0, 4, $fechaArgentina, 0, 1, 'C');

        // Cajero
        $cajero = \Illuminate\Support\Facades\Auth::check()
            ? \Illuminate\Support\Facades\Auth::user()->name
            : 'Sistema';
        $this->Cell(0, 4, $this->convertirTexto('Cajero: ' . $cajero), 0, 1, 'C');

        // Punto de venta
        $punto = session('punto_venta_nombre', 'BOX Principal');
        $this->Cell(0, 4, $this->convertirTexto('Punto: ' . $punto), 0, 1, 'C');
        $this->Ln(2);

        // Línea separadora
        $this->Cell(0, 0.5, '', 'T', 1);
        $this->Ln(2);
    }

    /**
     * Detalle de productos
     */
    private function generarDetalleProductos()
    {
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 4, 'DETALLE DE PRODUCTOS:', 0, 1);
        $this->Ln(1);

        $this->SetFont('Arial', '', 7);
        foreach ($this->datos['carrito'] as $item) {
            $subtotal = $item['price'] * $item['quantity'];

            // Nombre del producto (truncar si es muy largo y convertir encoding)
            $nombreProducto = strlen($item['name']) > 25
                ? substr($item['name'], 0, 25) . '...'
                : $item['name'];
            $this->Cell(0, 3, $this->convertirTexto($nombreProducto), 0, 1);

            // Cantidad y precio unitario (sin código)
            $detalleLinea = 'x' . $item['quantity'] . ' @ $' . number_format($item['price'], 2);
            $this->Cell(45, 3, $this->convertirTexto($detalleLinea), 0, 0);
            $this->Cell(0, 3, '$' . number_format($subtotal, 2), 0, 1, 'R');
            $this->Ln(1);
        }

        // Línea separadora
        $this->Ln(2);
        $this->Cell(0, 0.5, '', 'T', 1);
        $this->Ln(2);
    }

    /**
     * Sección de totales
     */
    private function generarTotales()
    {
        $this->SetFont('Arial', '', 8);

        // Subtotal
        $this->Cell(40, 4, 'Subtotal:', 0, 0);
        $this->Cell(0, 4, '$' . number_format($this->datos['subtotal'], 2), 0, 1, 'R');

        // Descuento si existe
        if ($this->datos['descuento'] > 0) {
            $this->Cell(40, 4, 'Descuento:', 0, 0);
            $this->Cell(0, 4, '-$' . number_format($this->datos['descuento'], 2), 0, 1, 'R');
        }

        // Total final
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(40, 5, 'TOTAL:', 0, 0);
        $this->Cell(0, 5, '$' . number_format($this->datos['totalFinal'], 2), 0, 1, 'R');
        $this->Ln(3);

        // Línea separadora
        $this->Cell(0, 0.5, '', 'T', 1);
        $this->Ln(2);
    }

    /**
     * Método de pago
     */
    private function generarMetodoPago()
    {
        $this->SetFont('Arial', 'B', 9);
        $metodoPago = strtoupper($this->datos['metodoPago']);
        $this->Cell(0, 4, 'PAGO: ' . $metodoPago, 0, 1, 'C');
        $this->Ln(1);

        if ($this->datos['metodoPago'] === 'efectivo') {
            $this->SetFont('Arial', '', 8);

            // Monto recibido
            $montoRecibido = $this->datos['montoRecibido'] ?? 0;
            $this->Cell(40, 4, 'Recibido:', 0, 0);
            $this->Cell(0, 4, '$' . number_format($montoRecibido, 2), 0, 1, 'R');

            // Vuelto
            $vuelto = $this->datos['vuelto'] ?? 0;
            if ($vuelto > 0) {
                $this->SetFont('Arial', 'B', 8);
                $this->Cell(40, 4, 'VUELTO:', 0, 0);
                $this->Cell(0, 4, '$' . number_format($vuelto, 2), 0, 1, 'R');
            } else {
                $this->SetFont('Arial', 'I', 7);
                $this->Cell(0, 4, 'Pago exacto - Sin vuelto', 0, 1, 'C');
            }
        }
    }

    /**
     * Observaciones si existen
     */
    private function generarObservaciones()
    {
        if (!empty($this->datos['observaciones'])) {
            $this->Ln(2);
            $this->Cell(0, 0.5, '', 'T', 1);
            $this->Ln(1);

            $this->SetFont('Arial', 'B', 7);
            $this->Cell(0, 3, 'Observaciones:', 0, 1);

            $this->SetFont('Arial', '', 7);
            $observaciones = wordwrap($this->datos['observaciones'], 35, "\n", true);
            $lineasObs = explode("\n", $observaciones);

            foreach ($lineasObs as $linea) {
                $this->Cell(0, 3, $this->convertirTexto($linea), 0, 1);
            }
        }
    }

    /**
     * Footer del ticket
     */
    private function generarFooter()
    {
        $this->Ln(3);
        $this->Cell(0, 0.5, '', 'T', 1);
        $this->Ln(2);

        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 3, $this->convertirTexto('¡Gracias por su compra!'), 0, 1, 'C');
        $this->Cell(0, 3, 'Conserve este ticket', 0, 1, 'C');
        $this->Cell(0, 3, 'Ticket #' . time(), 0, 1, 'C');
    }

    /**
     * Obtiene el PDF como string
     */
    public function obtenerPDF()
    {
        return $this->Output('S');
    }

    /**
     * Genera el nombre del archivo
     */
    public function obtenerNombreArchivo()
    {
        return 'ticket_' . date('YmdHis') . '.pdf';
    }
}
