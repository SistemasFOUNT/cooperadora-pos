<?php

namespace App\Console\Commands;

use App\Models\Factura;
use Illuminate\Console\Command;

class BackfillFacturaSnapshots extends Command
{
    protected $signature = 'app:backfill-factura-snapshots
                            {--numero= : Numero de factura local (ej: 00000005 o 5)}
                            {--dry-run : Solo muestra cambios sin guardar}
                            {--force : Reescribe snapshot aunque ya exista}';

    protected $description = 'Completa/normaliza snapshots de comprobantes locales para reimpresion exacta (incluye descuentos)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $numeroFiltro = $this->option('numero');

        $query = Factura::query()
            ->where('tipo', 'local')
            ->with(['sale', 'sale.paymentMethod', 'sale.items', 'sale.items.product']);

        if (!empty($numeroFiltro)) {
            $numeroNormalizado = (int) ltrim((string) $numeroFiltro, '0');
            $query->where('numero', $numeroNormalizado);
        }

        $facturas = $query->orderBy('id')->get();

        if ($facturas->isEmpty()) {
            $this->warn('No se encontraron facturas para procesar.');
            return self::SUCCESS;
        }

        $this->info('Facturas encontradas: ' . $facturas->count());

        $procesadas = 0;
        $actualizadas = 0;
        $omitidas = 0;

        foreach ($facturas as $factura) {
            $procesadas++;

            if (!$factura->sale) {
                $omitidas++;
                $this->line("- Omitida {$factura->numero_completo}: sin venta asociada");
                continue;
            }

            $additionalData = is_array($factura->sale->additional_data ?? null) ? $factura->sale->additional_data : [];
            $origen = $additionalData['origen'] ?? null;

            $snapshotActual = is_array($additionalData['comprobante_snapshot'] ?? null)
                ? $additionalData['comprobante_snapshot']
                : null;

            $descuento = $this->resolverDescuento($factura, $additionalData, $snapshotActual);
            $recargoTotal = $this->resolverRecargoTotal($factura, $descuento, $additionalData, $snapshotActual);

            $items = [];
            if (is_array($snapshotActual['items'] ?? null) && count($snapshotActual['items']) > 0) {
                $items = $snapshotActual['items'];
            } elseif ($factura->sale->items->count() > 0) {
                $items = $factura->sale->items->map(function ($item) {
                    return [
                        'codigo' => $item->product_code ?? ($item->product->code ?? 'N/A'),
                        'descripcion' => $item->product_name ?? ($item->product->name ?? 'Producto'),
                        'cantidad' => (int) ($item->quantity ?? 1),
                        'precio_unitario' => (float) ($item->unit_price ?? 0),
                        'total' => (float) ($item->total ?? 0),
                    ];
                })->values()->toArray();
            } elseif (is_array($additionalData['detalle_comprobante'] ?? null) && count($additionalData['detalle_comprobante']) > 0) {
                $items = $additionalData['detalle_comprobante'];
            } elseif ($origen === 'cuotas_estudiantiles') {
                $items = $this->reconstruirItemsDesdePeriodos($additionalData, (float) ($factura->subtotal ?? 0), $recargoTotal);
            } else {
                $subtotal = (float) ($factura->subtotal ?? 0);
                $items = [[
                    'codigo' => 'ITEM',
                    'descripcion' => 'Cobro general',
                    'cantidad' => 1,
                    'precio_unitario' => $subtotal,
                    'total' => $subtotal,
                ]];
            }

            if (empty($items)) {
                $omitidas++;
                $this->warn("- Omitida {$factura->numero_completo}: no se pudo reconstruir detalle");
                continue;
            }

            $metodoPago = $snapshotActual['metodo_pago']
                ?? ($additionalData['metodo_pago'] ?? $this->resolverMetodoPagoDesdeVenta($factura));

            $snapshotNuevo = [
                'numero_comprobante' => $factura->numero_completo,
                'fecha_emision' => optional($factura->fecha_emision)->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                'tipo_comprobante' => $this->resolverTipoComprobante($factura, $additionalData, $snapshotActual),
                'metodo_pago' => $metodoPago,
                'detalles_pago' => is_array($additionalData['detalles_pago'] ?? null) ? $additionalData['detalles_pago'] : [],
                'cliente' => [
                    'nombre' => $snapshotActual['cliente']['nombre'] ?? ($factura->datos_cliente['nombre'] ?? 'CONSUMIDOR FINAL'),
                    'documento' => $snapshotActual['cliente']['documento'] ?? ($factura->datos_cliente['documento'] ?? '00000000'),
                    'direccion' => $snapshotActual['cliente']['direccion'] ?? ($factura->datos_cliente['direccion'] ?? 'TUCUMAN'),
                    'condicion_iva' => $snapshotActual['cliente']['condicion_iva'] ?? ($factura->datos_cliente['condicion_iva'] ?? 'consumidor_final'),
                ],
                'subtotal' => (float) ($factura->subtotal ?? 0),
                'recargo_total' => (float) $recargoTotal,
                'descuento' => (float) $descuento,
                'total' => (float) ($factura->total ?? 0),
                'items' => array_values($items),
            ];

            $snapshotIncompleto = !$snapshotActual
                || !array_key_exists('descuento', $snapshotActual)
                || !isset($snapshotActual['metodo_pago'])
                || !is_array($snapshotActual['items'] ?? null)
                || count($snapshotActual['items']) === 0;

            if (!$force && !$snapshotIncompleto) {
                $omitidas++;
                $this->line("- Omitida {$factura->numero_completo}: snapshot existente completo");
                continue;
            }

            $this->line("- " . ($dryRun ? 'DRY' : 'OK') . " {$factura->numero_completo}: descuento={$descuento}, items=" . count($items));

            if ($dryRun) {
                continue;
            }

            $additionalData['metodo_pago'] = $metodoPago;
            $additionalData['descuento'] = (float) $descuento;
            $additionalData['detalle_comprobante'] = array_values($items);
            $additionalData['comprobante_snapshot'] = $snapshotNuevo;

            $factura->sale->additional_data = $additionalData;
            $factura->sale->discount_amount = (float) $descuento;
            $factura->sale->save();

            $actualizadas++;
        }

        $this->newLine();
        $this->info('Procesadas: ' . $procesadas);
        $this->info('Actualizadas: ' . $actualizadas);
        $this->info('Omitidas: ' . $omitidas);

        return self::SUCCESS;
    }

    private function resolverTipoComprobante(Factura $factura, array $additionalData, ?array $snapshotActual): string
    {
        if (isset($snapshotActual['tipo_comprobante'])) {
            return (string) $snapshotActual['tipo_comprobante'];
        }

        if (!empty($additionalData['detalles_pago']['tipo_comprobante'])) {
            return (string) $additionalData['detalles_pago']['tipo_comprobante'];
        }

        return empty($factura->tipo_comprobante) ? 'ticket' : 'factura_local';
    }

    private function resolverMetodoPagoDesdeVenta(Factura $factura): string
    {
        $code = strtoupper((string) ($factura->sale->paymentMethod->code ?? ''));

        return match ($code) {
            'TDC' => 'tarjeta',
            'TRA' => 'transferencia',
            default => 'efectivo',
        };
    }

    private function resolverDescuento(Factura $factura, array $additionalData, ?array $snapshotActual): float
    {
        if (is_numeric($snapshotActual['descuento'] ?? null)) {
            return round((float) $snapshotActual['descuento'], 2);
        }

        if (is_numeric($additionalData['descuento'] ?? null)) {
            return round((float) $additionalData['descuento'], 2);
        }

        if (is_numeric($factura->sale->discount_amount ?? null)) {
            return round((float) $factura->sale->discount_amount, 2);
        }

        $subtotal = (float) ($factura->subtotal ?? 0);
        $total = (float) ($factura->total ?? 0);

        // Se asume recargo >= 0, por lo tanto no puede haber total mayor al subtotal por descuento.
        return round(max(0, $subtotal - $total), 2);
    }

    private function resolverRecargoTotal(Factura $factura, float $descuento, array $additionalData, ?array $snapshotActual): float
    {
        if (is_numeric($snapshotActual['recargo_total'] ?? null)) {
            return round((float) $snapshotActual['recargo_total'], 2);
        }

        $subtotal = (float) ($factura->subtotal ?? 0);
        $total = (float) ($factura->total ?? 0);
        $derivado = round(max(0, ($total + $descuento) - $subtotal), 2);

        if ($derivado > 0) {
            return $derivado;
        }

        $items = $additionalData['detalle_comprobante'] ?? null;
        if (is_array($items) && count($items) > 0) {
            $recargo = collect($items)
                ->filter(fn ($item) => str_starts_with(strtoupper((string) ($item['codigo'] ?? '')), 'RECARGO'))
                ->sum(fn ($item) => (float) ($item['total'] ?? 0));

            return round(max(0, (float) $recargo), 2);
        }

        return 0.0;
    }

    private function reconstruirItemsDesdePeriodos(array $additionalData, float $subtotal, float $recargoTotal): array
    {
        $periodos = collect($additionalData['periodos'] ?? [])
            ->filter(fn ($periodo) => is_string($periodo) && trim($periodo) !== '')
            ->values();

        if ($periodos->isEmpty()) {
            return [];
        }

        $cantidad = $periodos->count();
        $cuotaBase = round($subtotal / $cantidad, 2);
        $recargoBase = round($recargoTotal / $cantidad, 2);

        $acumCuota = 0.0;
        $acumRecargo = 0.0;
        $items = [];

        foreach ($periodos as $idx => $periodo) {
            $ultima = $idx === ($cantidad - 1);

            $montoCuota = $ultima ? round($subtotal - $acumCuota, 2) : $cuotaBase;
            $montoRecargo = $ultima ? round($recargoTotal - $acumRecargo, 2) : $recargoBase;

            $acumCuota += $montoCuota;
            $acumRecargo += $montoRecargo;

            $items[] = [
                'codigo' => 'CUOTA',
                'descripcion' => 'Cuota de ' . $periodo,
                'cantidad' => 1,
                'precio_unitario' => $montoCuota,
                'total' => $montoCuota,
            ];

            if ($montoRecargo > 0) {
                $items[] = [
                    'codigo' => 'RECARGO',
                    'descripcion' => 'Recargo por mora cuota ' . $periodo,
                    'cantidad' => 1,
                    'precio_unitario' => $montoRecargo,
                    'total' => $montoRecargo,
                ];
            }
        }

        return $items;
    }
}
