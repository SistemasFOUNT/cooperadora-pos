<?php

namespace App\Listeners;

use App\Events\SaleCreated;
use App\Services\ContabilidadService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class GenerarAsientoVenta implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private ContabilidadService $contabilidad)
    {
    }

    public function handle(SaleCreated $event): void
    {
        // Generar el asiento contable automáticamente
        $this->contabilidad->generarAsientoVenta($event->sale);
    }
}
