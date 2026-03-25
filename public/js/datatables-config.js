// Configuración estándar de DataTables para el proyecto
// Este archivo debe ser incluido después de jQuery y DataTables

window.DataTableConfig = {
    // Configuración base para todas las tablas
    getDefaultConfig: function() {
        return {
            responsive: true,
            pageLength: 20,
            lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, "Todos"]],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            columnDefs: [
                {
                    targets: -1, // Última columna (Acciones)
                    orderable: false,
                    searchable: false
                }
            ],
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rtip',
            drawCallback: function() {
                // Reinicializar tooltips después de cada redraw
                if (typeof bootstrap !== 'undefined') {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            },
            initComplete: function() {
                // Personalizar elementos después de la inicialización
                $('.dataTables_filter input')
                    .addClass('form-control')
                    .attr('placeholder', 'Buscar...');

                $('.dataTables_length select').addClass('form-select');

                // Aplicar estilos adicionales
                this.api().columns.adjust().responsive.recalc();
            }
        };
    },

    // Configuración específica para productos
    getProductsConfig: function() {
        const config = this.getDefaultConfig();
        config.order = [[1, 'asc']]; // Ordenar por nombre por defecto
        config.columnDefs.push({
            targets: [3], // Columna de precio
            type: 'currency'
        });
        return config;
    },

    // Configuración específica para estudiantes
    getStudentsConfig: function() {
        const config = this.getDefaultConfig();
        config.order = [[1, 'asc']]; // Ordenar por nombre por defecto
        return config;
    },

    // Configuración específica para ventas
    getSalesConfig: function() {
        const config = this.getDefaultConfig();
        config.order = [[0, 'desc']]; // Ordenar por fecha más reciente
        config.pageLength = 25; // Más registros por página para ventas
        return config;
    },

    // Función para inicializar una tabla con configuración personalizada
    initTable: function(selector, configType = 'default', customOptions = {}) {
        let config;

        switch (configType) {
            case 'products':
                config = this.getProductsConfig();
                break;
            case 'students':
                config = this.getStudentsConfig();
                break;
            case 'sales':
                config = this.getSalesConfig();
                break;
            default:
                config = this.getDefaultConfig();
        }

        // Combinar con opciones personalizadas
        const finalConfig = $.extend(true, {}, config, customOptions);

        return $(selector).DataTable(finalConfig);
    },

    // Función para aplicar estilos estándar después de la inicialización
    applyStandardStyles: function() {
        // Estilos CSS que se aplican globalmente
        const styles = `
        <style id="datatables-standard-styles">
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_length {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_info {
            padding-top: 0.75rem;
        }

        .dataTables_wrapper .dataTables_paginate {
            padding-top: 0.75rem;
        }

        .dataTables_wrapper .row {
            margin-bottom: 1rem;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            background-color: #f8f9fa;
        }

        /* Responsive para dispositivos móviles */
        @media (max-width: 768px) {
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_length {
                text-align: center;
                margin-bottom: 0.5rem;
            }

            .dataTables_wrapper .dataTables_paginate {
                text-align: center;
            }

            .badge {
                font-size: 0.7em;
            }
        }
        </style>
        `;

        // Agregar estilos solo si no existen ya
        if (!$('#datatables-standard-styles').length) {
            $('head').append(styles);
        }
    }
};

// Auto-aplicar estilos cuando se carga el script
$(document).ready(function() {
    if (typeof window.DataTableConfig !== 'undefined') {
        window.DataTableConfig.applyStandardStyles();
    }
});
