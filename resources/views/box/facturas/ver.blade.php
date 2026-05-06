@extends('adminlte::page')

@section('title', "Factura {$factura->numero_completo} - BOX Cooperadora")

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>
        <i class="fas fa-file-invoice"></i>
        Factura {{ $factura->numero_completo }}
        @if($factura->tipo == 'arca')
            <small class="text-muted">(ARCA {{ $factura->tipo_comprobante }})</small>
        @else
            <small class="text-muted">(Local)</small>
        @endif
    </h1>
    <div>
        <a href="{{ route('box.facturas.lista') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Lista
        </a>
        <a href="{{ route('box.facturas.ver', [$factura, 'formato' => 'pdf']) }}"
           class="btn btn-primary" target="_blank">
            <i class="fas fa-download"></i> Descargar PDF
        </a>
        <button type="button" class="btn btn-info" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Factura -->
        <div class="card" id="factura-content">
            <div class="card-body">
                <div class="invoice p-3 mb-3">
                    <!-- Encabezado -->
                    <div class="row">
                        <div class="col-12">
                            <h4>
                                @if($factura->tipo == 'arca')
                                    <i class="fas fa-stamp"></i> FACTURA {{ $factura->tipo_comprobante }}
                                @else
                                    <i class="fas fa-receipt"></i> FACTURA LOCAL
                                @endif
                                <small class="float-right">{{ $factura->formatearFecha() }}</small>
                            </h4>
                        </div>
                    </div>

                    <!-- Información del Emisor y Cliente -->
                    <div class="row invoice-info">
                        <div class="col-sm-4 invoice-col">
                            <strong>EMISOR</strong>
                            <address>
                                <strong>{{ config('facturacion.emisor.razon_social') }}</strong><br>
                                {{ config('facturacion.emisor.domicilio') }}<br>
                                {{ config('facturacion.emisor.localidad') }}, {{ config('facturacion.emisor.provincia') }}<br>
                                @if(config('facturacion.emisor.telefono'))
                                    Teléfono: {{ config('facturacion.emisor.telefono') }}<br>
                                @endif
                                Email: {{ config('facturacion.emisor.email') }}
                            </address>
                        </div>

                        <div class="col-sm-4 invoice-col">
                            <strong>DATOS FISCALES</strong>
                            <address>
                                <strong>CUIT:</strong> {{ config('facturacion.emisor.cuit') }}<br>
                                <strong>Condición IVA:</strong> {{ config('facturacion.emisor.condicion_iva') }}<br>
                                @if($factura->tipo == 'arca')
                                    <strong>Punto de Venta:</strong> {{ $factura->punto_venta }}<br>
                                @endif
                                <strong>Inicio Actividades:</strong> 01/01/2020
                            </address>
                        </div>

                        <div class="col-sm-4 invoice-col">
                            <strong>CLIENTE</strong>
                            <address>
                                <strong>
                                    {{ $factura->razon_social_cliente ?? $factura->datos_cliente['nombre'] ?? 'Consumidor Final' }}
                                </strong><br>
                                @if($factura->cuit_cliente)
                                    CUIT: {{ $factura->cuit_cliente }}<br>
                                @endif
                                @if(isset($factura->datos_cliente['domicilio']) && $factura->datos_cliente['domicilio'])
                                    {{ $factura->datos_cliente['domicilio'] }}<br>
                                @endif
                                @if(isset($factura->datos_cliente['condicion_iva']) && $factura->datos_cliente['condicion_iva'])
                                    {{ $factura->datos_cliente['condicion_iva'] }}
                                @endif
                            </address>
                        </div>
                    </div>

                    <!-- Información de la Factura -->
                    <div class="row">
                        <div class="col-12 table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <td><strong>Número de Factura:</strong></td>
                                        <td>{{ $factura->numero_completo }}</td>
                                        <td><strong>Fecha de Emisión:</strong></td>
                                        <td>{{ $factura->formatearFecha('d/m/Y H:i') }}</td>
                                    </tr>
                                    @if($factura->tipo == 'arca' && $factura->cae)
                                    <tr>
                                        <td><strong>CAE:</strong></td>
                                        <td>{{ $factura->cae }}</td>
                                        <td><strong>Vto. CAE:</strong></td>
                                        <td>{{ $factura->fecha_vto_cae ? $factura->fecha_vto_cae->format('d/m/Y') : 'N/A' }}</td>
                                    </tr>
                                    @endif
                                </thead>
                            </table>
                        </div>
                    </div>

                    <!-- Detalle de Productos/Servicios -->
                    <div class="row">
                        <div class="col-12 table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unit.</th>
                                        <th>Subtotal</th>
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
                                        <td colspan="5" class="text-center text-muted">No hay items disponibles</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Totales -->
                    <div class="row">
                        <div class="col-6">
                            @if($factura->tipo == 'arca' && $factura->qr_arca)
                            <div class="well well-sm bg-light p-3">
                                <div style="display: flex; align-items: flex-start; gap: 15px;">
                                    <div style="flex-shrink: 0;">
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($factura->qr_arca) }}"
                                             alt="QR AFIP" class="img-fluid">
                                    </div>
                                    <div style="flex: 1;">
                                        <h5><i class="fas fa-qrcode"></i> Código QR AFIP</h5>
                                        <p class="text-muted small">
                                            Escaneá este código para verificar la factura en el sitio de AFIP
                                        </p>
                                        <p class="small text-muted">
                                            <a href="{{ $factura->qr_arca }}" target="_blank">Verificar en AFIP</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="col-6">
                            <div class="table-responsive">
                                <table class="table">
                                    <tbody>
                                        @if($factura->tipo_comprobante == 'A' && $factura->iva > 0)
                                        <tr>
                                            <th style="width:50%">Subtotal:</th>
                                            <td class="text-right">${{ number_format($factura->subtotal, 2, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <th>IVA (21%):</th>
                                            <td class="text-right">${{ number_format($factura->iva, 2, ',', '.') }}</td>
                                        </tr>
                                        @endif
                                        <tr class="bg-light">
                                            <th style="width:50%">TOTAL:</th>
                                            <td class="text-right">
                                                <h4><strong>${{ number_format($factura->total, 2, ',', '.') }}</strong></h4>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Información Adicional -->
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><strong>Información de la Venta</strong></h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Venta #:</strong> {{ $factura->sale_id }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Cajero:</strong> {{ $factura->sale->user->name ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Método de Pago:</strong> {{ $factura->sale->paymentMethod->name ?? 'Efectivo' }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Estado:</strong>
                                        @switch($factura->estado)
                                            @case('emitida')
                                                <span class="badge badge-success">Emitida</span>
                                                @break
                                            @case('autorizada')
                                                <span class="badge badge-success">Autorizada</span>
                                                @break
                                            @case('anulada')
                                                <span class="badge badge-danger">Anulada</span>
                                                @break
                                        @endswitch
                                    </div>
                                </div>

                                @if($factura->estado == 'anulada' && $factura->observaciones)
                                <hr>
                                <strong>Motivo de Anulación:</strong> {{ $factura->observaciones }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Pie de Factura -->
                    <div class="row">
                        <div class="col-12 text-center">
                            <hr>
                            <p class="text-muted small">
                                Esta factura fue generada electrónicamente el {{ $factura->created_at->format('d/m/Y H:i:s') }}
                                @if($factura->createdBy)
                                    por {{ $factura->createdBy->name }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
@media print {
    .content-wrapper, .right-side, .main-footer {
        margin-left: 0 !important;
    }

    .content-header, .btn, .card-header {
        display: none !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }

    .invoice {
        background: white !important;
    }

    /* Bordes más finos para todas las tablas */
    .table-bordered,
    .table-bordered > tbody > tr > td,
    .table-bordered > tbody > tr > th,
    .table-bordered > tfoot > tr > td,
    .table-bordered > tfoot > tr > th,
    .table-bordered > thead > tr > td,
    .table-bordered > thead > tr > th {
        border: 0.5px solid #dee2e6 !important;
    }

    /* Bordes mucho más finos para tabla de productos */
    .table-striped,
    .table-striped > tbody > tr > td,
    .table-striped > tbody > tr > th,
    .table-striped > thead > tr > td,
    .table-striped > thead > tr > th {
        border: 0.25px solid #dee2e6 !important;
    }

    /* Ajuste del QR - más junto al texto ARCA */
    .well.bg-light {
        padding: 10px !important;
        margin-left: 0 !important;
    }

    .well.bg-light div[style*="display: flex"] {
        gap: 10px !important;
    }

    .well.bg-light img {
        width: 100px !important;
        height: 100px !important;
    }

    .well.bg-light h5 {
        margin-top: 0 !important;
        font-size: 14px !important;
    }

    .well.bg-light p {
        font-size: 10px !important;
        margin-bottom: 5px !important;
    }
}

.invoice {
    background: #fff;
    border: 1px solid #f4f4f4;
    padding: 20px;
}

.invoice-info {
    margin: 20px 0;
}
</style>
@stop
