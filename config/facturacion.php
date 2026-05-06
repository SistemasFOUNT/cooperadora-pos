<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Facturación
    |--------------------------------------------------------------------------
    |
    | Configuración para emisión de facturas locales y ARCA
    |
    */

    'emisor' => [
        'razon_social' => env('FACTURA_RAZON_SOCIAL', 'Cooperadora Facultad de Odontología'),
        'cuit' => env('FACTURA_CUIT', '00-00000000-0'),
        'domicilio' => env('FACTURA_DOMICILIO', 'Dirección de la Facultad'),
        'localidad' => env('FACTURA_LOCALIDAD', 'Ciudad'),
        'provincia' => env('FACTURA_PROVINCIA', 'Provincia'),
        'telefono' => env('FACTURA_TELEFONO', ''),
        'email' => env('FACTURA_EMAIL', 'cooperadora@facultad.edu.ar'),
        'condicion_iva' => env('FACTURA_CONDICION_IVA', 'Exento'), // Exento, Monotributista, Responsable Inscripto
        'logo' => env('FACTURA_LOGO', '/images/logo-facultad.png'),
    ],

    'arca' => [
        'habilitado' => env('ARCA_HABILITADO', false),
        'certificado' => env('ARCA_CERTIFICADO', ''),
        'clave_privada' => env('ARCA_CLAVE_PRIVADA', ''),
        'punto_venta' => env('ARCA_PUNTO_VENTA', 1),
        'ambiente' => env('ARCA_AMBIENTE', 'testing'), // testing, production
        'url_testing' => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx',
        'url_production' => 'https://servicios1.afip.gov.ar/wsfev1/service.asmx',
        'url_auth_testing' => 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms',
        'url_auth_production' => 'https://wsaa.afip.gov.ar/ws/services/LoginCms',
    ],

    'local' => [
        'habilitado' => true,
        'numeracion_por_punto_venta' => true, // Numeración independiente por punto de venta
        'prefijo_numero' => env('FACTURA_LOCAL_PREFIJO', 'FL-'),
        'longitud_numero' => 8, // Cantidad de dígitos para el número
    ],

    'pdf' => [
        'orientacion' => 'portrait', // portrait, landscape
        'tamaño' => 'A4',
        'margenes' => [
            'top' => 15,
            'right' => 15,
            'bottom' => 15,
            'left' => 15
        ],
        'fuente' => 'Arial',
        'tamaño_fuente_titulo' => 14,
        'tamaño_fuente_normal' => 10,
        'tamaño_fuente_pequeña' => 8,
    ],

    'tipos_comprobante' => [
        'A' => [
            'codigo' => 1,
            'descripcion' => 'Factura A',
            'discrimina_iva' => true,
            'requiere_cuit' => true
        ],
        'B' => [
            'codigo' => 6,
            'descripcion' => 'Factura B',
            'discrimina_iva' => false,
            'requiere_cuit' => false
        ],
        'C' => [
            'codigo' => 11,
            'descripcion' => 'Factura C',
            'discrimina_iva' => false,
            'requiere_cuit' => true
        ],
    ],

    'conceptos' => [
        1 => 'Productos',
        2 => 'Servicios',
        3 => 'Productos y Servicios'
    ],

    'monedas' => [
        'PES' => [
            'codigo' => 'PES',
            'descripcion' => 'Pesos Argentinos',
            'cotizacion' => 1
        ],
        'USD' => [
            'codigo' => 'USD',
            'descripcion' => 'Dólares Estadounidenses',
            'cotizacion' => null // Se obtiene dinámicamente
        ]
    ],

    'iva' => [
        'general' => 21.00,
        'reducido' => 10.50,
        'exento' => 0.00
    ],

    'defaults' => [
        'tipo_comprobante' => 'B', // Tipo por defecto para consumidor final
        'concepto' => 3, // Productos y servicios
        'moneda' => 'PES',
        'condicion_pago' => 'Contado',
        'vencimiento_pago' => null // null = sin vencimiento
    ]
];
