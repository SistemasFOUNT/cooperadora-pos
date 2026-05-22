<?php

return [
    // Configuración del menú específico para el usuario ADMIN
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

        // Sidebar items específicos para ADMIN:
        ['header' => 'PANEL ADMINISTRATIVO'],
        [
            'text' => 'Dashboard Admin',
            'route' => 'admin.dashboard',
            'icon' => 'fas fa-fw fa-tachometer-alt',
        ],

        ['header' => 'SUPERVISIÓN PUNTOS DE VENTA'],
        [
            'text' => 'Supervisión',
            'icon' => 'fas fa-fw fa-eye',
            'submenu' => [
                [
                    'text' => 'General',
                    'route' => 'admin.supervision.general',
                    'icon' => 'far fa-fw fa-circle',
                ],
                [
                    'text' => 'BOX Cooperadora',
                    'route' => 'admin.supervision.box',
                    'icon' => 'far fa-fw fa-circle',
                ],
                [
                    'text' => 'Postgrado',
                    'route' => 'admin.supervision.postgrado',
                    'icon' => 'far fa-fw fa-circle',
                ],
                [
                    'text' => 'Centro Odontológico',
                    'route' => 'admin.supervision.odonto',
                    'icon' => 'far fa-fw fa-circle',
                ],
            ],
        ],

        ['header' => 'INFORMES FINANCIEROS'],
        [
            'text' => 'Ingresos y Egresos',
            'icon' => 'fas fa-fw fa-money-bill-wave',
            'submenu' => [
                [
                    'text' => 'Consolidado',
                    'route' => 'admin.ingresos-egresos.consolidado',
                    'icon' => 'far fa-fw fa-circle',
                ],
                [
                    'text' => 'BOX Cooperadora',
                    'route' => 'admin.ingresos-egresos.box',
                    'icon' => 'far fa-fw fa-circle',
                ],
                [
                    'text' => 'Postgrado',
                    'route' => 'admin.ingresos-egresos.postgrado',
                    'icon' => 'far fa-fw fa-circle',
                ],
                [
                    'text' => 'Centro Odontológico',
                    'route' => 'admin.ingresos-egresos.odonto',
                    'icon' => 'far fa-fw fa-circle',
                ],
            ],
        ],
        [
            'text' => 'Libro Caja',
            'icon' => 'fas fa-fw fa-book',
            'submenu' => [
                [
                    'text' => 'Consolidado',
                    'route' => 'admin.libro-caja.consolidado',
                    'icon' => 'far fa-fw fa-circle',
                ],
                [
                    'text' => 'BOX Cooperadora',
                    'route' => 'admin.libro-caja.box',
                    'icon' => 'far fa-fw fa-circle',
                ],
                [
                    'text' => 'Postgrado',
                    'route' => 'admin.libro-caja.postgrado',
                    'icon' => 'far fa-fw fa-circle',
                ],
                [
                    'text' => 'Centro Odontológico',
                    'route' => 'admin.libro-caja.odonto',
                    'icon' => 'far fa-fw fa-circle',
                ],
            ],
        ],
        [
            'text' => 'Reportes Consolidados',
            'route' => 'admin.reportes.consolidado',
            'icon' => 'fas fa-fw fa-chart-bar',
        ],
        [
            'text' => 'Estadísticas Generales',
            'route' => 'admin.estadisticas',
            'icon' => 'fas fa-fw fa-chart-line',
        ],

        ['header' => 'GESTIÓN DE CUENTAS'],
        [
            'text' => 'Estado de Cuentas',
            'icon' => 'fas fa-fw fa-calculator',
            'submenu' => [
                [
                    'text' => 'General',
                    'route' => 'admin.cuentas.estado-general',
                    'icon' => 'far fa-fw fa-circle',
                ],
                [
                    'text' => 'Particular',
                    'route' => 'admin.cuentas.particular',
                    'icon' => 'far fa-fw fa-circle',
                ],
            ],
        ],

        ['header' => 'OPERACIONES DIARIAS'],
        [
            'text' => 'Arqueo de Caja',
            'route' => 'admin.arqueo.index',
            'icon' => 'fas fa-fw fa-cash-register',
        ],
        [
            'text' => 'Auditoría Interna',
            'route' => 'admin.auditoria.index',
            'icon' => 'fas fa-fw fa-clipboard-check',
        ],
        [
            'text' => 'Autorizaciones',
            'icon' => 'fas fa-fw fa-check-circle',
            'submenu' => [
                [
                    'text' => 'Pendientes',
                    'route' => 'admin.autorizaciones.index',
                    'icon' => 'far fa-fw fa-circle',
                ],
                [
                    'text' => 'Historial',
                    'route' => 'admin.autorizaciones.historial',
                    'icon' => 'far fa-fw fa-circle',
                ],
            ],
        ],

        ['header' => 'GESTIÓN GENERAL'],
        [
            'text' => 'Gestión Productos',
            'route' => 'products.index',
            'icon' => 'fas fa-fw fa-boxes',
        ],
        [
            'text' => 'Gestión Estudiantes',
            'route' => 'students.index',
            'icon' => 'fas fa-fw fa-user-graduate',
        ],
        [
            'text' => 'Gestión Usuarios',
            'route' => 'admin.usuarios',
            'icon' => 'fas fa-fw fa-users-cog',
        ],

        ['header' => 'CONFIGURACIÓN'],
        [
            'text' => 'Mi Perfil',
            'route' => 'admin.profile',
            'icon' => 'fas fa-fw fa-user-cog',
        ],
    ],
];
