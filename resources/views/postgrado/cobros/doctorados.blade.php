@php
    $titulo = 'Cobros - Doctorados';
    $subtitulo = 'Cobro de conceptos y aranceles de doctorado';
    $programa = 'Doctorados';
    $conceptos = [
        ['id' => 201, 'codigo' => 'DOC-INSC', 'nombre' => 'Inscripcion Doctorado', 'descripcion' => 'Alta al programa doctoral', 'precio' => 130000],
        ['id' => 202, 'codigo' => 'DOC-CUO-01', 'nombre' => 'Cuota Mensual Doctorado', 'descripcion' => 'Arancel mensual regular', 'precio' => 150000],
        ['id' => 203, 'codigo' => 'DOC-EVAL', 'nombre' => 'Evaluacion de Avance', 'descripcion' => 'Instancia de evaluacion parcial', 'precio' => 65000],
    ];
@endphp

@include('postgrado.cobros._template-cobro')
