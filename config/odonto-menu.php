<?php

return [
    'menu' => [
        [
            'type' => 'navbar-search',
            'text' => 'search',
            'topnav_right' => true,
        ],
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        ['header' => 'CENTRO ODONTOLÓGICO'],
        [
            'text' => 'Dashboard',
            'route' => 'odonto.dashboard',
            'icon' => 'fas fa-fw fa-tachometer-alt',
        ],

        ['header' => 'PACIENTES'],
        [
            'text' => 'Lista de Pacientes',
            'route' => 'odonto.pacientes',
            'icon' => 'fas fa-fw fa-users',
        ],
        [
            'text' => 'Agenda de Citas',
            'route' => 'odonto.agenda',
            'icon' => 'fas fa-fw fa-calendar-check',
        ],

        ['header' => 'CLÍNICA'],
        [
            'text' => 'Tratamientos',
            'route' => 'odonto.tratamientos',
            'icon' => 'fas fa-fw fa-procedures',
        ],
        [
            'text' => 'Historiales',
            'route' => 'odonto.historiales',
            'icon' => 'fas fa-fw fa-file-medical',
        ],

        ['header' => 'OPERACIONES'],
        [
            'text' => 'POS',
            'route' => 'odonto.pos',
            'icon' => 'fas fa-fw fa-cash-register',
        ],
        [
            'text' => 'Facturación',
            'route' => 'odonto.facturacion',
            'icon' => 'fas fa-fw fa-file-invoice-dollar',
        ],
        [
            'text' => 'Inventario',
            'route' => 'odonto.inventario',
            'icon' => 'fas fa-fw fa-boxes',
        ],

        ['header' => 'REPORTES'],
        [
            'text' => 'Reportes',
            'route' => 'odonto.reportes',
            'icon' => 'fas fa-fw fa-chart-line',
        ],

        ['header' => 'CONFIGURACIÓN'],
        [
            'text' => 'Configuración',
            'route' => 'odonto.configuracion',
            'icon' => 'fas fa-fw fa-cogs',
        ],

        ['header' => 'MI CUENTA'],
        [
            'text' => 'Mi Perfil',
            'route' => 'profile.edit',
            'icon' => 'fas fa-fw fa-user',
        ],
    ]
];
