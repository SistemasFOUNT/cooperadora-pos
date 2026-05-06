<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura {{ $factura->numero_completo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .document-type {
            font-size: 16px;
            font-weight: bold;
            background: #f0f0f0;
            padding: 10px;
            margin: 10px 0;
            text-align: center;
            border: 2px solid #000;
        }

        .info-section {
            margin: 15px 0;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .info-column {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            vertical-align: top;
            border: 1px solid #ddd;
        }

        .info-title {
            font-weight: bold;
            font-size: 11px;
            background: #f8f8f8;
            padding: 5px;
            margin-bottom: 10px;
            text-align: center;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .detail-table th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-section {
            float: right;
            width: 300px;
            margin: 20px 0;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 5px 10px;
            border-bottom: 1px solid #ddd;
        }

        .total-final {
            font-weight: bold;
            font-size: 14px;
            background: #f0f0f0;
            border: 2px solid #000;
        }

        .qr-section {
            float: left;
            width: 150px;
            text-align: center;
            margin: 20px 0;
        }

        .qr-code {
            width: 120px;
            height: 120px;
            border: 1px solid #ddd;
        }

        .footer {
            clear: both;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            text-align: center;
            color: #666;
        }

        .fiscal-data {
            font-size: 10px;
            line-height: 1.3;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">{{ config('facturacion.emisor.razon_social') }}</div>
        <div class="fiscal-data">
            {{ config('facturacion.emisor.domicilio') }}<br>
            {{ config('facturacion.emisor.localidad') }}, {{ config('facturacion.emisor.provincia') }}<br>
            CUIT: {{ config('facturacion.emisor.cuit') }} - {{ config('facturacion.emisor.condicion_iva') }}
        </div>
    </div>

    <!-- Document Type -->
    <div class="document-type">
        @if($factura->tipo == 'arca')
            FACTURA {{ $factura->tipo_comprobante }} - ORIGINAL
        @else
            FACTURA LOCAL - ORIGINAL
        @endif
    </div>

    <!-- Information Grid -->
    <div class="info-grid">
        <div class="info-column">
            <div class="info-title">DATOS DEL COMPRADOR</div>
            <strong>{{ $factura->razon_social_cliente ?? $factura->datos_cliente['nombre'] ?? 'Consumidor Final' }}</strong><br>
            @if($factura->cuit_cliente)
                <strong>CUIT:</strong> {{ $factura->cuit_cliente }}<br>
            @endif
            @if(isset($factura->datos_cliente['domicilio']) && $factura->datos_cliente['domicilio'])
                <strong>Domicilio:</strong> {{ $factura->datos_cliente['domicilio'] }}<br>
            @endif
            @if(isset($factura->datos_cliente['condicion_iva']))
                <strong>Condición IVA:</strong> {{ $factura->datos_cliente['condicion_iva'] }}
            @endif
        </div>

        <div class="info-column">
            <div class="info-title">DATOS DE LA FACTURA</div>
            <strong>Número:</strong> {{ $factura->numero_completo }}<br>
            <strong>Fecha:</strong> {{ $factura->formatearFecha('d/m/Y') }}<br>
            <strong>Hora:</strong> {{ $factura->fecha_emision->format('H:i:s') }}<br>
            @if($factura->tipo == 'arca')
                <strong>Pto. Venta:</strong> {{ $factura->punto_venta }}<br>
            @endif
            <strong>Venta #:</strong> {{ $factura->sale_id }}
        </div>

        <div class="info-column">
            @if($factura->tipo == 'arca' && $factura->cae)
            <div class="info-title">DATOS AFIP</div>
            <strong>CAE:</strong> {{ $factura->cae }}<br>
            <strong>Vto. CAE:</strong> {{ $factura->fecha_vto_cae->format('d/m/Y') }}<br>
            @else
            <div class="info-title">DATOS DE VENTA</div>
            @endif
            <strong>Cajero:</strong> {{ $factura->sale->user->name ?? 'N/A' }}<br>
            <strong>Método Pago:</strong> {{ $factura->sale->paymentMethod->name ?? 'Efectivo' }}
        </div>
    </div>

    <!-- Items Detail -->
    <table class="detail-table">
        <thead>
            <tr>
                <th style="width: 15%;">Código</th>
                <th style="width: 45%;">Descripción</th>
                <th style="width: 10%;">Cant.</th>
                <th style="width: 15%;">Precio Unit.</th>
                <th style="width: 15%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($factura->sale->items as $item)
            <tr>
                <td>{{ $item->product_code ?? $item->product->code ?? 'N/A' }}</td>
                <td>{{ $item->product_name ?? $item->product->name ?? 'Producto' }}</td>
                <td class="text-center">{{ $item->quantity ?? 1 }}</td>
                <td class="text-right">${{ number_format($item->unit_price ?? 0, 2, ',', '.') }}</td>
                <td class="text-right">${{ number_format($item->total ?? 0, 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">No hay items disponibles</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- QR Code and Totals -->
    <div class="clearfix">
        @if($factura->tipo == 'arca' && $factura->qr_arca)
        <div class="qr-section">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($factura->qr_arca) }}"
                 alt="QR AFIP" class="qr-code">
            <div style="font-size: 10px; margin-top: 5px;">
                Código de verificación AFIP
            </div>
        </div>
        @endif

        <div class="totals-section">
            <table class="totals-table">
                @if($factura->tipo_comprobante == 'A' && $factura->iva > 0)
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">${{ number_format($factura->subtotal, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>IVA (21%):</td>
                    <td class="text-right">${{ number_format($factura->iva, 2, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="total-final">
                    <td><strong>TOTAL:</strong></td>
                    <td class="text-right"><strong>${{ number_format($factura->total, 2, ',', '.') }}</strong></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div style="margin-bottom: 10px;">
            @if($factura->estado == 'anulada')
                <strong style="color: red; font-size: 14px;">*** FACTURA ANULADA ***</strong><br>
                @if($factura->observaciones)
                    Motivo: {{ $factura->observaciones }}<br>
                @endif
            @endif
        </div>

        <div>
            Factura generada electrónicamente el {{ $factura->created_at->format('d/m/Y H:i:s') }}
            @if($factura->createdBy)
                por {{ $factura->createdBy->name }}
            @endif
        </div>

        @if($factura->tipo == 'arca')
        <div style="margin-top: 10px;">
            Para verificar la validez de esta factura ingrese a www.afip.gob.ar
        </div>
        @endif
    </div>
</body>
</html>
