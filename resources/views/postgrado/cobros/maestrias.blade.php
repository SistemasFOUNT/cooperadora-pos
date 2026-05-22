@php
    $titulo = 'Cobros - Maestrias';
    $subtitulo = 'Cobro de cuotas y conceptos academicos de maestrias';
    $programa = 'Maestrias';
    $conceptos = [
        ['id' => 101, 'codigo' => 'MAE-INSC', 'nombre' => 'Inscripcion Maestria', 'descripcion' => 'Derecho de inscripcion anual', 'precio' => 95000],
        ['id' => 102, 'codigo' => 'MAE-CUO-01', 'nombre' => 'Cuota Mensual Maestria', 'descripcion' => 'Arancel mensual regular', 'precio' => 120000],
        ['id' => 103, 'codigo' => 'MAE-TES', 'nombre' => 'Derecho de Tesis', 'descripcion' => 'Presentacion y defensa de tesis', 'precio' => 180000],
    ];
@endphp

@include('postgrado.cobros._template-cobro')
