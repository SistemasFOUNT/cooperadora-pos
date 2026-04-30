<?php

namespace App\Services;

require_once(base_path('vendor/setasign/fpdf/fpdf.php'));

class PDFTicket extends FPDF
{
    private $datosTicket;
    private $fecha;
    private $numeroTicket;
    private $carrito;
    private $total;
    private $metodoPago;

    public function generar($datosTicket)
    {
        // Validar que existan los datos del carrito
        if (!isset($datosTicket['carrito']) || empty($datosTicket['carrito'])) {
            throw new \Exception('No hay items en el carrito para generar el ticket');
        }

        // Asignar datos
        $this->datosTicket = $datosTicket;
        $this->fecha = now();
        $this->numeroTicket = $datosTicket['numero_ticket'] ?? 'BOX-' . time();
        $this->carrito = $datosTicket['carrito'];
        $this->total = $datosTicket['total'] ?? 0;
        $this->metodoPago = $datosTicket['metodo_pago'] ?? 'efectivo';

        // Configurar PDF
        $this->AddPage();
        $this->SetFont('Arial', 'B', 12);

        // Generar contenido
        $this->generarHeader();
        $this->generarDetalleItems();
        $this->generarTotales();
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
        $this->Cell(0, 5, 'ASOCIACIÓN COOPERADORA', 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 4, 'Facultad de Odontología - UNT', 0, 1, 'C');

        $this->Ln(3);

        // Información del ticket
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 4, 'Fecha: ' . $this->fecha->format('d/m/Y H:i:s'), 0, 1, 'C');
        $this->Cell(0, 4, 'Ticket: ' . $this->numeroTicket, 0, 1, 'C');

        // Método de pago
        $metodoPagoTexto = ucfirst($this->metodoPago);
        if ($this->metodoPago == 'tarjeta' && isset($this->datosTicket['detalles_pago']['tipo'])) {
            $metodoPagoTexto .= ' ' . ucfirst($this->datosTicket['detalles_pago']['tipo']);
        }
        $this->Cell(0, 4, 'Método: ' . $metodoPagoTexto, 0, 1, 'C');

        if ($this->metodoPago == 'tarjeta' && isset($this->datosTicket['detalles_pago']['autorizacion'])) {
            $this->Cell(0, 4, 'Autorización: ' . $this->datosTicket['detalles_pago']['autorizacion'], 0, 1, 'C');
        }

        // Línea separadora
        $this->Ln(2);
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 0, str_repeat('-', 40), 0, 1, 'C');
        $this->Ln(3);
    }

    /**
     * Detalle de items
     */
    private function generarDetalleItems()
    {
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 5, 'SERVICIOS:', 0, 1);
        $this->Ln(2);

        $this->SetFont('Arial', '', 8);

        foreach ($this->carrito as $item) {
            $nombre = $item['nombre'];
            $cantidad = $item['cantidad'];
            $precio = $item['precio'];
            $subtotal = $precio * $cantidad;

            // Nombre del servicio
            $this->Cell(0, 4, $nombre, 0, 1);

            // Cantidad x precio = subtotal
            $linea = $cantidad . ' x $' . number_format($precio, 2, ',', '.') . ' = $' . number_format($subtotal, 2, ',', '.');
            $this->Cell(0, 4, $linea, 0, 1, 'R');
            $this->Ln(1);
        }
    }

    /**
     * Totales
     */
    private function generarTotales()
    {
        $this->Ln(2);
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 0, str_repeat('-', 40), 0, 1, 'C');
        $this->Ln(3);

        // Mostrar descuento si existe
        if (isset($this->datosTicket['descuento']) && $this->datosTicket['descuento'] > 0) {
            $this->SetFont('Arial', '', 9);
            $this->Cell(25, 5, 'Subtotal:', 0, 0);
            $this->Cell(0, 5, '$' . number_format($this->datosTicket['subtotal'], 2, ',', '.'), 0, 1, 'R');

            $this->Cell(25, 5, 'Descuento:', 0, 0);
            $this->Cell(0, 5, '-$' . number_format($this->datosTicket['descuento'], 2, ',', '.'), 0, 1, 'R');
        }

        // Total final
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(25, 6, 'TOTAL:', 0, 0);
        $this->Cell(0, 6, '$' . number_format($this->total, 2, ',', '.'), 0, 1, 'R');

        // Vuelto para efectivo
        if ($this->metodoPago == 'efectivo' && isset($this->datosTicket['detalles_pago']['vuelto'])) {
            $vuelto = $this->datosTicket['detalles_pago']['vuelto'];
            if ($vuelto > 0) {
                $this->SetFont('Arial', '', 9);
                $this->Cell(25, 5, 'Vuelto:', 0, 0);
                $this->Cell(0, 5, '$' . number_format($vuelto, 2, ',', '.'), 0, 1, 'R');
            }
        }
    }

    /**
     * Footer del ticket
     */
    private function generarFooter()
    {
        $this->Ln(5);
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 0, str_repeat('-', 40), 0, 1, 'C');
        $this->Ln(3);

        $this->Cell(0, 4, '¡Gracias por su visita!', 0, 1, 'C');
        $this->Cell(0, 4, 'Conserve este ticket', 0, 1, 'C');
        $this->Ln(2);
        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 3, 'Sistema BOX - Punto de Venta', 0, 1, 'C');
    }

    /**
     * Devuelve el PDF para streaming
     */
    public function stream($filename = 'ticket.pdf')
    {
        return response($this->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }
}
