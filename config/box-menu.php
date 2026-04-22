<?php

return [
    // Configuración del menú específico para usuarios BOX
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

        // Sidebar items específicos para BOX:
        ['header' => 'PUNTO DE VENTA'],
        [
            'text' => 'Dashboard BOX',
            'url' => 'box',
            'icon' => 'fas fa-fw fa-tachometer-alt',
        ],

        ['header' => 'OPERACIONES DE CAJA'],
        [
            'text' => 'Cobros',
            'icon' => 'fas fa-fw fa-hand-holding-usd',
            'submenu' => [
                [
                    'text' => 'Productos',
                    'url' => 'box/cobros/productos',
                    'icon' => 'fas fa-fw fa-shopping-bag',
                ],
                [
                    'text' => 'Servicios Odontológicos',
                    'url' => 'box/cobros/odontologia',
                    'icon' => 'fas fa-fw fa-tooth',
                ],
                [
                    'text' => 'Cuotas Estudiantiles',
                    'url' => 'box/cobros/cuotas',
                    'icon' => 'fas fa-fw fa-user-graduate',
                ],
                [
                    'text' => 'Bonos Estudiantiles',
                    'url' => 'box/cobros/bonos',
                    'icon' => 'fas fa-fw fa-graduation-cap',
                ],
                [
                    'text' => 'Otros Cobros',
                    'url' => 'box/cobros/otros',
                    'icon' => 'fas fa-fw fa-plus-circle',
                ],
            ],
        ],

        ['header' => 'GESTIÓN DE INVENTARIO'],
        [
            'text' => 'Ingreso de Productos',
            'url' => 'box/inventario/ingresos',
            'icon' => 'fas fa-fw fa-truck-loading',
        ],

        ['header' => 'PAGOS Y EGRESOS'],
        [
            'text' => 'Pagos a Proveedores',
            'url' => 'box/pagos/proveedores',
            'icon' => 'fas fa-fw fa-file-invoice-dollar',
        ],
        [
            'text' => 'Pagos de Asignaciones',
            'url' => 'box/pagos/asignaciones',
            'icon' => 'fas fa-fw fa-money-check-alt',
            'label' => 'Sueldos',
            'label_color' => 'success',
        ],

        ['header' => 'REPORTES'],
        [
            'text' => 'Reportes de Caja',
            'icon' => 'fas fa-fw fa-chart-bar',
            'submenu' => [
                [
                    'text' => 'Resumen Diario',
                    'url' => 'box/reportes/diario',
                    'icon' => 'fas fa-fw fa-calendar-day',
                ],
                [
                    'text' => 'Movimientos de Caja',
                    'url' => 'box/reportes/movimientos',
                    'icon' => 'fas fa-fw fa-exchange-alt',
                ],
                [
                    'text' => 'Ventas por Período',
                    'url' => 'box/reportes/ventas',
                    'icon' => 'fas fa-fw fa-chart-line',
                ],
                [
                    'text' => 'Inventario',
                    'url' => 'box/reportes/inventario',
                    'icon' => 'fas fa-fw fa-boxes',
                ],
            ],
        ],

        ['header' => 'MI CUENTA'],
        [
            'text' => 'Mi Perfil',
            'url' => 'profile',
            'icon' => 'fas fa-fw fa-user',
        ],
    ]
];
