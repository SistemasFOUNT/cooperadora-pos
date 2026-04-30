<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket de Venta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 10px;
            width: 80mm;
            color: #000;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 12px;
            font-weight: normal;
        }
        .info {
            margin-bottom: 10px;
        }
        .info div {
            margin-bottom: 2px;
        }
        .items {
            margin: 10px 0;
        }
        .item {
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
        }
        .totales {
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .total-final {
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            border-top: 1px dashed #000;
            padding-top: 10px;
            font-size: 10px;
        }
        .texto-derecha {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ASOCIACIÓN COOPERADORA</h1>
        <h2>Facultad de Odontología - UNT</h2>
        <div>{{ $fecha->format('d/m/Y H:i:s') }}</div>
        <div>Ticket: {{ $numero_ticket }}</div>
    </div>

    <div class="info">
        <div><strong>Método de Pago:</strong>
            @if($ticket['metodo_pago'] == 'efectivo')
                Efectivo
                @if(isset($ticket['detalles_pago']['vuelto']) && $ticket['detalles_pago']['vuelto'] > 0)
                    <br><small>Vuelto: ${{ number_format($ticket['detalles_pago']['vuelto'], 2, ',', '.') }}</small>
                @endif
            @elseif($ticket['metodo_pago'] == 'tarjeta')
                Tarjeta {{ ucfirst($ticket['detalles_pago']['tipo'] ?? '') }}
                @if(isset($ticket['detalles_pago']['autorizacion']))
                    <br><small>Autorización: {{ $ticket['detalles_pago']['autorizacion'] }}</small>
                @endif
            @else
                {{ ucfirst($ticket['metodo_pago']) }}
            @endif
        </div>
    </div>

    <div class="items">
        <div style="font-weight: bold; margin-bottom: 8px;">SERVICIOS:</div>
        @foreach($carrito as $item)
            <div class="item">
                <div>
                    {{ $item['nombre'] }}<br>
                    <small>{{ $item['cantidad'] }} x ${{ number_format($item['precio'], 2, ',', '.') }}</small>
                </div>
                <div class="texto-derecha">
                    ${{ number_format($item['precio'] * $item['cantidad'], 2, ',', '.') }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="totales">
        @if(isset($ticket['subtotal']) && isset($ticket['descuento']) && $ticket['descuento'] > 0)
            <div class="total-line">
                <span>Subtotal:</span>
                <span>${{ number_format($ticket['subtotal'], 2, ',', '.') }}</span>
            </div>
            <div class="total-line">
                <span>Descuento:</span>
                <span>-${{ number_format($ticket['descuento'], 2, ',', '.') }}</span>
            </div>
        @endif
        <div class="total-line total-final">
            <span>TOTAL:</span>
            <span>${{ number_format($total, 2, ',', '.') }}</span>
        </div>
    </div>

    <div class="footer">
        <div>¡Gracias por su visita!</div>
        <div>Conserve este ticket</div>
        <div style="margin-top: 5px;">
            Sistema BOX - Punto de Venta
        </div>
    </div>
</body>
</html>
