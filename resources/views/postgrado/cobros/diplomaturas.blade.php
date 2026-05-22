@php
    $titulo = 'Cobros - Diplomaturas';
    $subtitulo = 'Cobro de diplomaturas y derechos academicos';
    $programa = 'Diplomaturas';
    $conceptos = [
        ['id' => 401, 'codigo' => 'DIP-INSC', 'nombre' => 'Inscripcion Diplomatura', 'descripcion' => 'Inscripcion al programa', 'precio' => 68000],
        ['id' => 402, 'codigo' => 'DIP-CUO-01', 'nombre' => 'Cuota Mensual Diplomatura', 'descripcion' => 'Arancel mensual regular', 'precio' => 76000],
        ['id' => 403, 'codigo' => 'DIP-MAT', 'nombre' => 'Material Academico', 'descripcion' => 'Material de apoyo y practicas', 'precio' => 22000],
    ];
@endphp

@include('postgrado.cobros._template-cobro')
