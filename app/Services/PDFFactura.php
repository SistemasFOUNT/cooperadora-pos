<?php

namespace App\Services;

require_once(base_path('vendor/setasign/fpdf/fpdf.php'));

class PDFFactura extends \FPDF
{
    private $factura;
    private $ventaItems;

    public function __construct()
    {
        parent::__construct('L', 'mm', 'A5');
    }

    /**
     * Convertir caracteres especiales para FPDF usando mb_convert_encoding
     */
    private function convertirTexto($texto)
    {
        if (empty($texto)) {
            return $texto;
        }

        // Convertir de UTF-8 a ISO-8859-1 (Latin-1) que FPDF maneja perfectamente
        return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    }

    /**
     * Obtener un dato de cliente priorizando facturas persistidas (datos_cliente JSON).
     */
    private function obtenerDatoCliente(string $clave, $default = null)
    {
        $datosCliente = $this->factura->datos_cliente ?? null;

        if (is_array($datosCliente) && array_key_exists($clave, $datosCliente) && $datosCliente[$clave] !== null && $datosCliente[$clave] !== '') {
            return $datosCliente[$clave];
        }

        return $default;
    }

    public function generar($factura)
    {
        $this->factura = $factura;

        // Solo cargar relaciones si es un modelo Eloquent
        if (method_exists($factura, 'load')) {
            $factura->load(['sale', 'sale.items', 'sale.items.product']);
        }

        $this->ventaItems = $factura->sale->items ?? collect();

        // Configurar PDF
        $this->AddPage();
        $this->SetMargins(8, 8, 8);
        $this->SetAutoPageBreak(false);

        // Generar contenido según el formato exacto
        $this->generarEncabezadoCompleto();
        $this->generarDatosCliente();
        $this->generarTablaProductos();
        $this->generarTotales();
        $this->generarPieFactura();

        return $this;
    }

    /**
     * Encabezado completo con formato específico
     */
    private function generarEncabezadoCompleto()
    {
        $y_inicial = 10;

        // Marco izquierdo - Datos de la Asociación
        $this->SetXY(10, $y_inicial);
        $this->SetLineWidth(0.3);
        $this->Rect(10, $y_inicial, 70, 25);

        $this->SetFont('Arial', 'B', 10);
        $this->SetXY(12, $y_inicial + 1);
        $this->Cell(66, 4, $this->convertirTexto('Asociacion'), 0, 1, 'L');
        $this->SetX(12);
        $this->Cell(66, 4, $this->convertirTexto('Cooperadora'), 0, 1, 'L');

        $this->SetFont('Arial', '', 8);
        $this->SetX(12);
        $this->Cell(66, 3, $this->convertirTexto('Facultad de Odontologia UNT'), 0, 1, 'L');

        $this->SetX(12);
        $this->Cell(66, 3, $this->convertirTexto('Av. Benjamin Araoz 800'), 0, 1, 'L');
        $this->SetX(12);
        $this->Cell(66, 3, $this->convertirTexto('S M Tucuman - Tucuman'), 0, 1, 'L');

        // Marco central - Letra de factura
        $this->SetXY(83, $y_inicial);
        $this->Rect(83, $y_inicial, 25, 25);

        $this->SetFont('Arial', 'B', 36);
        $this->SetXY(83, $y_inicial + 5);
        $letra = $this->factura->tipo_comprobante ?? 'C';
        $this->Cell(25, 10, $letra, 0, 0, 'C');

        $this->SetFont('Arial', '', 7);
        $this->SetXY(83, $y_inicial + 19);
        $this->Cell(25, 3, 'Codigo 11', 0, 0, 'C');

        // Marco derecho - Datos de la factura
        $this->SetXY(111, $y_inicial);
        $this->Rect(111, $y_inicial, 84, 25);

        $this->SetFont('Arial', 'B', 12);
        $this->SetXY(113, $y_inicial + 1);
        $this->Cell(80, 4, $this->convertirTexto('FACTURA'), 0, 1, 'L');

        $this->SetFont('Arial', '', 8);
        $this->SetX(113);
        $numero = (string) ($this->factura->numero_factura
            ?? $this->factura->numero_completo
            ?? '00000000');
        $this->Cell(80, 3, $this->convertirTexto('Numero.: ' . $numero), 0, 1, 'L');
        $this->SetX(113);
        $fecha = $this->factura->fecha_emision ?? $this->factura->created_at ?? now();
        $fechaFormateada = is_string($fecha) ? $fecha : $fecha->format('d/m/Y');
        $this->Cell(80, 3, $this->convertirTexto('Fecha Fact.: ' . $fechaFormateada), 0, 1, 'L');
        $this->SetX(113);
        $this->Cell(80, 3, $this->convertirTexto('C.U.I.T.:30-70822363-4'), 0, 1, 'L');
        $this->SetX(113);
        $this->Cell(80, 3, $this->convertirTexto('Ing.Brutos: 30-70822363-4'), 0, 1, 'L');
        $this->SetX(113);
        $this->Cell(80, 3, $this->convertirTexto('IVA Responsable Excento'), 0, 1, 'L');
    }

    /**
     * Datos del cliente
     */
    private function generarDatosCliente()
    {
        $y = 38;

        // Marco completo para datos del cliente
        $this->SetXY(10, $y);
        $this->SetLineWidth(0.3);
        $this->Rect(10, $y, 185, 18);

        // Datos del cliente - lado izquierdo
        $this->SetFont('Arial', '', 7);
        $this->SetXY(12, $y + 1);
        $cliente_nombre = $this->obtenerDatoCliente('nombre', $this->factura->cliente_nombre ?? 'CONSUMIDOR FINAL');
        $this->Cell(90, 3, $this->convertirTexto('Cliente : (0001)-' . strtoupper($cliente_nombre)), 0, 1, 'L');

        $this->SetX(12);
        $direccion = $this->obtenerDatoCliente('direccion', $this->factura->cliente_direccion ?? 'TUCUMAN');
        $this->Cell(90, 3, $this->convertirTexto('Direccion: ' . strtoupper($direccion)), 0, 1, 'L');

        $this->SetX(12);
        $localidad = $this->factura->cliente_localidad ?? 'TUCUMAN';
        $this->Cell(90, 3, $this->convertirTexto('Localidad: ' . strtoupper($localidad)), 0, 1, 'L');

        $this->SetX(12);
        $condicion_iva = $this->obtenerCondicionIVA();
        $this->Cell(90, 3, $this->convertirTexto('Condicion de IVA: ' . $condicion_iva), 0, 1, 'L');

        // Datos del cliente - lado derecho
        $this->SetXY(125, $y + 3);
        $documento = $this->obtenerDatoCliente('documento', $this->factura->cliente_cuit ?? $this->factura->cliente_documento ?? '20111111112');
        $this->Cell(65, 3, $this->convertirTexto('DNI/CUIT: ' . $documento), 0, 1, 'L');

        $this->SetX(125);
        $condiciones_venta = $this->obtenerCondicionesVenta();
        $this->Cell(65, 3, $this->convertirTexto('Condiciones: ' . $condiciones_venta), 0, 1, 'L');
    }

    /**
     * Tabla de productos
     */
    private function generarTablaProductos()
    {
        $y = 59;

        // Marco completo de la tabla
        $this->SetXY(10, $y);
        $this->SetLineWidth(0.3);
        $this->Rect(10, $y, 185, 40);

        // Encabezado de la tabla
        $this->SetXY(10, $y);
        $this->SetLineWidth(0.15);
        $this->Rect(10, $y, 185, 6);

        // Líneas verticales de separación de columnas
        $this->SetLineWidth(0.15);
        $this->Line(35, $y, 35, $y + 40);  // Después de Codigo
        $this->Line(120, $y, 120, $y + 40); // Después de Descripcion
        $this->Line(145, $y, 145, $y + 40); // Después de Cantidad
        $this->Line(170, $y, 170, $y + 40); // Después de P.Unit

        $this->SetFont('Arial', 'B', 8);
        $this->SetXY(12, $y + 1);
        $this->Cell(23, 3, $this->convertirTexto('Codigo'), 0, 0, 'L');
        $this->Cell(85, 3, $this->convertirTexto('Descripcion'), 0, 0, 'L');
        $this->Cell(25, 3, $this->convertirTexto('Cantidad'), 0, 0, 'C');
        $this->Cell(25, 3, $this->convertirTexto('P.Unit.'), 0, 0, 'C');
        $this->Cell(25, 3, $this->convertirTexto('Importe'), 0, 0, 'C');

        // Items de la venta (sin rectángulos individuales)
        $y_item = $y + 6;
        $total_items = 0;

        if ($this->ventaItems->count() > 0) {
            foreach ($this->ventaItems as $index => $item) {
                $this->SetFont('Arial', '', 8);
                $this->SetXY(12, $y_item + 1);

                // Usar la misma lógica que la vista HTML
                $codigo = $item->product_code ?? $item->product->code ?? 'N/A';
                $this->Cell(23, 4, $this->convertirTexto($codigo), 0, 0, 'L');

                // Mantener el texto original como en la vista HTML (sin convertir a mayúsculas)
                $descripcion = $item->product_name ?? $item->product->name ?? 'PRODUCTO';
                $this->Cell(85, 4, $this->convertirTexto($descripcion), 0, 0, 'L');

                $cantidad = number_format($item->quantity ?? 1, 2);
                $this->Cell(25, 4, $cantidad, 0, 0, 'C');

                // Usar unit_price como en la vista HTML
                $precio_unitario = number_format($item->unit_price ?? $item->price ?? 0, 2);
                $this->Cell(25, 4, $precio_unitario, 0, 0, 'C');

                // Usar total calculado como en la vista HTML
                $importe = number_format($item->total ?? (($item->unit_price ?? $item->price ?? 0) * ($item->quantity ?? 1)), 2);
                $this->Cell(25, 4, $importe, 0, 0, 'C');

                $y_item += 5;
                $total_items++;
            }
        } else {
            // Item por defecto si no hay items
            $this->SetFont('Arial', '', 8);
            $this->SetXY(12, $y_item + 1);
            $this->Cell(23, 4, '13', 0, 0, 'L');
            $this->Cell(85, 4, $this->convertirTexto('AGUA ULTRA PURA X 5 LTS'), 0, 0, 'L');
            $this->Cell(25, 4, '1.00', 0, 0, 'C');
            $this->Cell(25, 4, number_format($this->factura->total, 2), 0, 0, 'C');
            $this->Cell(25, 4, number_format($this->factura->total, 2), 0, 0, 'C');

            $y_item += 5;
        }

        // Sin líneas adicionales - solo el marco exterior
    }

    /**
     * Sección de totales
     */
    private function generarTotales()
    {
        $y = 102;

        // Marco de totales
        $this->SetXY(10, $y);
        $this->SetLineWidth(0.3);
        $this->Rect(10, $y, 185, 10);

        // Etiquetas de totales
        $this->SetFont('Arial', '', 8);
        $this->SetXY(120, $y + 1);
        $this->Cell(25, 3, $this->convertirTexto('Subtotal'), 0, 0, 'C');
        $this->Cell(25, 3, $this->convertirTexto('Dto./Int.'), 0, 0, 'C');

        $this->SetFont('Arial', 'B', 10);
        $this->Cell(25, 3, $this->convertirTexto('T O T A L'), 0, 0, 'C');

        // Valores de totales
        $this->SetFont('Arial', 'B', 10);
        $this->SetXY(120, $y + 5);
        $this->Cell(25, 3, '', 0, 0, 'C');
        $this->Cell(25, 3, '', 0, 0, 'C');
        $this->Cell(25, 3, number_format($this->factura->total, 2), 0, 0, 'C');
    }

    /**
     * Pie de la factura con QR y datos ARCA
     */
    private function generarPieFactura()
    {
        $y = 115;

        // Marco del pie
        $this->SetXY(10, $y);
        $this->SetLineWidth(0.3);
        $this->Rect(10, $y, 185, 20);

        // Todo el contenido ARCA alineado a la derecha
        $x_arca = 115; // Posición derecha para toda la sección ARCA

        // QR Code primero (a la izquierda en la sección derecha)
        $qr_size = 16;
        $qr_x = $x_arca;
        $qr_y = $y + 2; // 2mm desde arriba del marco

        $this->SetXY($qr_x, $qr_y);
        $this->SetLineWidth(0.15);
        $this->Rect($qr_x, $qr_y, $qr_size, $qr_size);
        $this->SetFont('Arial', '', 6);
        $this->SetXY($qr_x, $qr_y + 7);
        $this->Cell($qr_size, 3, 'QR CODE', 0, 0, 'C');

        // Textos ARCA a la derecha del QR
        $x_texto = $x_arca + $qr_size + 5; // 5mm de separación del QR

        // Logo ARCA
        $this->SetFont('Arial', 'B', 12);
        $this->SetXY($x_texto, $y + 1);
        $this->Cell(50, 4, 'ARCA', 0, 0, 'L');

        // Datos CAE
        $this->SetFont('Arial', '', 6);
        $this->SetXY($x_texto, $y + 5);
        $cae = $this->factura->cae ?? '86095890742590';
        $this->Cell(50, 2, $this->convertirTexto('C.A.E. N° : ' . $cae), 0, 1, 'L');

        $this->SetX($x_texto);
        $fechaVenc = $this->factura->fecha_vencimiento_cae ?? null;
        $vencimiento = $fechaVenc
            ? (is_string($fechaVenc) ? $fechaVenc : $fechaVenc->format('d/m/Y'))
            : '12/03/2026';
        $this->Cell(50, 2, $this->convertirTexto('Vencimiento: ' . $vencimiento), 0, 1, 'L');

        // Textos ARCA oficiales
        $this->SetX($x_texto);
        $this->Cell(50, 2, $this->convertirTexto('AGENCIA DE RECAUDACION'), 0, 1, 'L');
        $this->SetX($x_texto);
        $this->Cell(50, 2, $this->convertirTexto('COMPROBANTE AUTORIZADO'), 0, 1, 'L');
        $this->SetX($x_texto);
        $this->Cell(50, 2, $this->convertirTexto('Cinagos V.3.0'), 0, 1, 'L');
    }

    /**
     * Obtener condición de IVA
     */
    private function obtenerCondicionIVA()
    {
        $condicion = $this->obtenerDatoCliente('condicion_iva', $this->factura->condicion_iva ?? 'CF');

        $equivalencias = [
            'consumidor_final' => 'CF',
            'responsable_inscripto' => 'RI',
            'exento' => 'EX',
            'monotributo' => 'MT',
        ];

        $condicion = $equivalencias[$condicion] ?? $condicion;

        $condiciones = [
            'CF' => 'Consumidor Final',
            'RI' => 'Responsable Inscripto',
            'EX' => 'Exento',
            'MT' => 'Monotributista'
        ];

        return $condiciones[$condicion] ?? 'Consumidor Final';
    }

    /**
     * Obtener condiciones de venta
     */
    private function obtenerCondicionesVenta()
    {
        if ($this->factura->sale && $this->factura->sale->paymentMethod) {
            return 'Tarj.:' . strtoupper($this->factura->sale->paymentMethod->name) . ' -';
        }

        return 'Tarj.:DEBITO VISA -';
    }

    public function Output($dest = '', $name = '', $isUTF8 = false)
    {
        return parent::Output($dest, $name, $isUTF8);
    }

    /**
     * Generar PDF SIMPLE con datos directos usando el formato OFICIAL
     */
    public function generarSimple($datos)
    {
        // Mapear método de pago para el PDF
        $metodoPagoTexto = [
            'efectivo' => 'EFECTIVO',
            'tarjeta' => 'TARJETA DEBITO/CREDITO',
            'transferencia' => 'TRANSFERENCIA',
            'mixto' => 'PAGO MIXTO'
        ][$datos['metodo_pago']] ?? 'EFECTIVO';

        // Mapear condición IVA
        $condicionIvaTexto = [
            'consumidor_final' => 'CONSUMIDOR FINAL',
            'responsable_inscripto' => 'RESPONSABLE INSCRIPTO',
            'exento' => 'EXENTO',
            'monotributo' => 'MONOTRIBUTO'
        ][$datos['cliente_condicion_iva']] ?? 'CONSUMIDOR FINAL';

        // Crear estructura compatible con el método generar() existente
        $numeroFactura = (string) ($datos['numero_factura']
            ?? $datos['numero_completo']
            ?? ('0010-' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT)));

        $this->factura = (object) [
            'numero_factura' => $numeroFactura,
            'numero_completo' => $numeroFactura,
            'fecha_emision' => now()->format('d/m/Y'),
            'created_at' => now(), // Para compatibilidad
            'tipo_comprobante' => 'C',
            'cliente_nombre' => $datos['cliente_nombre'],
            'cliente_documento' => $datos['cliente_documento'],
            'cliente_cuit' => $datos['cliente_documento'], // Usar documento como CUIT
            'cliente_direccion' => $datos['cliente_direccion'] ?: 'TUCUMAN',
            'cliente_localidad' => 'TUCUMAN',
            'condicion_iva' => substr($condicionIvaTexto, 0, 2),
            'total' => $datos['total'],
            'subtotal' => $datos['subtotal'] ?: $datos['total'],
            'descuento' => $datos['descuento'] ?: 0,
            'cae' => '86095890742590',
            'fecha_vencimiento_cae' => '12/03/2026',
            'sale' => (object) [
                'items' => collect($datos['productos'])->map(function($prod) {
                    return (object) [
                        'product' => (object) [
                            'code' => substr(strtoupper(str_replace(' ', '', $prod['nombre'])), 0, 6),
                            'name' => $prod['nombre']
                        ],
                        'quantity' => $prod['cantidad'],
                        'unit_price' => $prod['precio'],
                        'total' => $prod['total']
                    ];
                }),
                'paymentMethod' => (object) [
                    'name' => $metodoPagoTexto
                ],
                'total' => $datos['total'],
                'observaciones' => $datos['observaciones'] ?? ''
            ]
        ];

        $this->ventaItems = $this->factura->sale->items;

        // Usar el formato oficial existente
        $this->AddPage();
        $this->SetMargins(8, 8, 8);
        $this->SetAutoPageBreak(false);

        // Generar contenido con formato oficial
        $this->generarEncabezadoCompleto();
        $this->generarDatosCliente();
        $this->generarTablaProductos();
        $this->generarTotales();
        $this->generarPieFactura();

        // Retornar respuesta HTTP directamente
        return response($this->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="factura-' . $this->factura->numero_factura . '.pdf"',
            'Cache-Control' => 'private, max-age=0',
            'Pragma' => 'public'
        ]);
    }

    /**
     * Descargar el PDF (compatibilidad con código existente)
     */
    public function descargar($nombre = null)
    {
        // Si no hay contenido generado, usar el formato oficial completo
        if ($this->PageNo() === 0) {
            // Regenerar usando formato oficial si no se había generado antes
            $this->AddPage();
            $this->SetMargins(8, 8, 8);
            $this->SetAutoPageBreak(false);

            // Usar siempre el formato oficial completo
            $this->generarEncabezadoCompleto();
            $this->generarDatosCliente();
            $this->generarTablaProductos();
            $this->generarTotales();
            $this->generarPieFactura();
        }

        $nombre = $nombre ?? 'factura-' . ($this->factura->numero_factura ?? date('YmdHis')) . '.pdf';

        return response($this->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $nombre . '"',
            'Cache-Control' => 'private, max-age=0',
            'Pragma' => 'public'
        ]);
    }

    /**
     * Generar PDF simple para pruebas
     */
    private function generarPDFSimple()
    {
        $this->AddPage();
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, $this->convertirTexto('FACTURA LOCAL'), 0, 1, 'C');

        $this->Ln(10);
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 8, $this->convertirTexto('Fecha: ' . date('d/m/Y H:i')), 0, 1);
        $this->Cell(0, 8, $this->convertirTexto('Cliente: Cliente Genérico'), 0, 1);

        $this->Ln(10);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 8, $this->convertirTexto('DETALLE DE LA VENTA'), 0, 1);

        $this->SetFont('Arial', '', 10);
        $this->Cell(80, 8, $this->convertirTexto('Producto'), 1, 0, 'C');
        $this->Cell(30, 8, $this->convertirTexto('Cantidad'), 1, 0, 'C');
        $this->Cell(40, 8, $this->convertirTexto('Precio Unit.'), 1, 0, 'C');
        $this->Cell(40, 8, $this->convertirTexto('Subtotal'), 1, 1, 'C');

        $this->Cell(80, 8, $this->convertirTexto('Producto de prueba'), 1, 0);
        $this->Cell(30, 8, '1', 1, 0, 'C');
        $this->Cell(40, 8, '$100.00', 1, 0, 'R');
        $this->Cell(40, 8, '$100.00', 1, 1, 'R');

        $this->Ln(5);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 8, $this->convertirTexto('TOTAL: $100.00'), 0, 1, 'R');
    }
}
