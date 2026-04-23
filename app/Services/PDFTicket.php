<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf as PDF;

class PDFTicket
{
    public function generar($datosTicket)
    {
        // Validar que existan los datos del carrito
        if (!isset($datosTicket['carrito']) || empty($datosTicket['carrito'])) {
            throw new \Exception('No hay items en el carrito para generar el ticket');
        }

        // Preparar datos para la vista
        $datos = [
            'ticket' => $datosTicket,
            'fecha' => now(),
            'numero_ticket' => $datosTicket['numero_ticket'] ?? 'BOX-' . time(),
            'carrito' => $datosTicket['carrito'],
            'total' => $datosTicket['total'] ?? 0,
            'metodo_pago' => $datosTicket['metodo_pago'] ?? 'efectivo'
        ];

        // Generar PDF
        $pdf = PDF::loadView('tickets.ticket-venta', $datos);
        
        // Configurar el PDF
        $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait'); // 80mm de ancho
        
        return $pdf;
    }
}