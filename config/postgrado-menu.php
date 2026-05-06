<?php

return [
    // Configuración del menú específico para usuarios POSTGRADO
    'menu' => [
        // Navbar items:
        [
            'type' => 'navbar-search',
            'text' => 'search',
            'topnav_right' => true,
        ],
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        // Sidebar items específicos para POSTGRADO:
        ['header' => 'POSTGRADO'],
        [
            'text' => 'Dashboard Postgrado',
            'route' => 'postgrado.dashboard',
            'icon' => 'fas fa-fw fa-graduation-cap',
            'classes' => 'bg-primary',
        ],

        ['header' => 'PROGRAMAS ACADÉMICOS'],
        [
            'text' => 'Carreras de Postgrado',
            'icon' => 'fas fa-fw fa-university',
            'submenu' => [
                [
                    'text' => 'Maestrías',
                    'route' => 'postgrado.maestrias',
                    'icon' => 'fas fa-fw fa-graduation-cap',
                ],
                [
                    'text' => 'Doctorados',
                    'route' => 'postgrado.doctorados',
                    'icon' => 'fas fa-fw fa-user-graduate',
                ],
                [
                    'text' => 'Especialidades',
                    'route' => 'postgrado.especialidades',
                    'icon' => 'fas fa-fw fa-medal',
                ],
                [
                    'text' => 'Diplomaturas',
                    'route' => 'postgrado.diplomaturas',
                    'icon' => 'fas fa-fw fa-certificate',
                ],
                [
                    'text' => 'Cursos de Extensión',
                    'route' => 'postgrado.cursos',
                    'icon' => 'fas fa-fw fa-chalkboard-teacher',
                ],
            ],
        ],

        ['header' => 'ESTUDIANTES POSTGRADO'],
        [
            'text' => 'Gestión de Estudiantes',
            'icon' => 'fas fa-fw fa-user-graduate',
            'submenu' => [
                [
                    'text' => 'Estudiantes de Maestría',
                    'route' => 'postgrado.estudiantes.maestrias',
                    'icon' => 'fas fa-fw fa-graduation-cap',
                    'label' => 'Maestrías',
                    'label_color' => 'primary',
                ],
                [
                    'text' => 'Estudiantes de Doctorado',
                    'route' => 'postgrado.estudiantes.doctorados',
                    'icon' => 'fas fa-fw fa-user-graduate',
                    'label' => 'PhD',
                    'label_color' => 'success',
                ],
                [
                    'text' => 'Estudiantes de Especialidad',
                    'route' => 'postgrado.estudiantes.especialidades',
                    'icon' => 'fas fa-fw fa-medal',
                ],
                [
                    'text' => 'Estudiantes de Diplomatura',
                    'route' => 'postgrado.estudiantes.diplomaturas',
                    'icon' => 'fas fa-fw fa-certificate',
                ],
                [
                    'text' => 'Participantes de Cursos',
                    'route' => 'postgrado.estudiantes.cursos',
                    'icon' => 'fas fa-fw fa-chalkboard-teacher',
                ],
            ],
        ],
        [
            'text' => 'Inscripciones',
            'icon' => 'fas fa-fw fa-user-plus',
            'submenu' => [
                [
                    'text' => 'Nueva Inscripción',
                    'route' => 'postgrado.inscripciones.crear',
                    'icon' => 'fas fa-fw fa-plus',
                ],
                [
                    'text' => 'Importar Estudiantes',
                    'route' => 'postgrado.inscripciones.importar',
                    'icon' => 'fas fa-fw fa-upload',
                    'label' => 'CSV',
                    'label_color' => 'info',
                ],
                [
                    'text' => 'Estado de Inscripciones',
                    'route' => 'postgrado.inscripciones.estado',
                    'icon' => 'fas fa-fw fa-clipboard-check',
                ],
            ],
        ],

        ['header' => 'COBROS POSTGRADO'],
        [
            'text' => 'Punto de Venta Académico',
            'route' => 'postgrado.pos',
            'icon' => 'fas fa-fw fa-cash-register',
            'classes' => 'bg-success',
            'label' => 'Activo',
            'label_color' => 'success',
        ],
        [
            'text' => 'Cobros por Programa',
            'icon' => 'fas fa-fw fa-money-bill',
            'submenu' => [
                [
                    'text' => 'Cuotas de Maestría',
                    'route' => 'postgrado.cobros.maestrias',
                    'icon' => 'fas fa-fw fa-graduation-cap',
                    'label' => 'Mensual',
                    'label_color' => 'primary',
                ],
                [
                    'text' => 'Cuotas de Doctorado',
                    'route' => 'postgrado.cobros.doctorados',
                    'icon' => 'fas fa-fw fa-user-graduate',
                    'label' => 'Semestral',
                    'label_color' => 'success',
                ],
                [
                    'text' => 'Aranceles de Especialidad',
                    'route' => 'postgrado.cobros.especialidades',
                    'icon' => 'fas fa-fw fa-medal',
                ],
                [
                    'text' => 'Cuotas de Diplomatura',
                    'route' => 'postgrado.cobros.diplomaturas',
                    'icon' => 'fas fa-fw fa-certificate',
                ],
                [
                    'text' => 'Pagos de Cursos',
                    'route' => 'postgrado.cobros.cursos',
                    'icon' => 'fas fa-fw fa-chalkboard-teacher',
                    'label' => 'Único',
                    'label_color' => 'warning',
                ],
            ],
        ],
        [
            'text' => 'Matrículas y Derechos',
            'icon' => 'fas fa-fw fa-file-invoice-dollar',
            'submenu' => [
                [
                    'text' => 'Derechos de Inscripción',
                    'route' => 'postgrado.derechos.inscripcion',
                    'icon' => 'fas fa-fw fa-file-signature',
                ],
                [
                    'text' => 'Derechos de Examen',
                    'route' => 'postgrado.derechos.examenes',
                    'icon' => 'fas fa-fw fa-clipboard-list',
                ],
                [
                    'text' => 'Derechos de Título',
                    'route' => 'postgrado.derechos.titulos',
                    'icon' => 'fas fa-fw fa-award',
                ],
            ],
        ],

        ['header' => 'GESTIÓN ACADÉMICA'],
        [
            'text' => 'Certificados y Títulos',
            'icon' => 'fas fa-fw fa-certificate',
            'submenu' => [
                [
                    'text' => 'Emitir Certificados',
                    'route' => 'postgrado.certificados.emitir',
                    'icon' => 'fas fa-fw fa-file-pdf',
                ],
                [
                    'text' => 'Títulos de Postgrado',
                    'route' => 'postgrado.titulos',
                    'icon' => 'fas fa-fw fa-scroll',
                    'label' => 'Oficiales',
                    'label_color' => 'warning',
                ],
                [
                    'text' => 'Historial de Emisión',
                    'route' => 'postgrado.certificados.historial',
                    'icon' => 'fas fa-fw fa-history',
                ],
            ],
        ],

        ['header' => 'REPORTES POSTGRADO'],
        [
            'text' => 'Reportes Académicos',
            'icon' => 'fas fa-fw fa-chart-bar',
            'submenu' => [
                [
                    'text' => 'Estudiantes por Programa',
                    'route' => 'postgrado.reportes.estudiantes',
                    'icon' => 'fas fa-fw fa-users',
                ],
                [
                    'text' => 'Recaudación Académica',
                    'route' => 'postgrado.reportes.recaudacion',
                    'icon' => 'fas fa-fw fa-money-bill-wave',
                ],
                [
                    'text' => 'Inscripciones por Período',
                    'route' => 'postgrado.reportes.inscripciones',
                    'icon' => 'fas fa-fw fa-calendar-check',
                ],
                [
                    'text' => 'Títulos Otorgados',
                    'route' => 'postgrado.reportes.titulos',
                    'icon' => 'fas fa-fw fa-award',
                ],
            ],
        ],

        ['header' => 'CONFIGURACIÓN'],
        [
            'text' => 'Config. Programas',
            'route' => 'postgrado.configuracion',
            'icon' => 'fas fa-fw fa-cogs',
            'label' => 'Admin',
            'label_color' => 'danger',
        ],
    ]
];
