@php
    $titulo = 'Cobros - Cursos';
    $subtitulo = 'Cobro de cursos cortos y actividades academicas';
    $programa = 'Cursos';
    $conceptos = [
        ['id' => 501, 'codigo' => 'CUR-INSC', 'nombre' => 'Inscripcion Curso', 'descripcion' => 'Pago unico de acceso al curso', 'precio' => 45000],
        ['id' => 502, 'codigo' => 'CUR-TALL', 'nombre' => 'Taller Practico', 'descripcion' => 'Actividad complementaria opcional', 'precio' => 28000],
        ['id' => 503, 'codigo' => 'CUR-CERT', 'nombre' => 'Certificado de Aprobacion', 'descripcion' => 'Emision de certificado final', 'precio' => 15000],
    ];
@endphp

@include('postgrado.cobros._template-cobro')
