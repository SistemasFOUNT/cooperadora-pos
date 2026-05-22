@php
    $titulo = 'Cobros - Especialidades';
    $subtitulo = 'Cobro de especialidades con flujo unificado';
    $programa = 'Especialidades';
    $conceptos = [
        ['id' => 301, 'codigo' => 'ESP-INSC', 'nombre' => 'Inscripcion Especialidad', 'descripcion' => 'Alta inicial al trayecto', 'precio' => 87000],
        ['id' => 302, 'codigo' => 'ESP-CUO-01', 'nombre' => 'Cuota Mensual Especialidad', 'descripcion' => 'Arancel mensual regular', 'precio' => 98000],
        ['id' => 303, 'codigo' => 'ESP-CERT', 'nombre' => 'Derecho de Certificacion', 'descripcion' => 'Emision de certificado final', 'precio' => 54000],
    ];
@endphp

@include('postgrado.cobros._template-cobro')
