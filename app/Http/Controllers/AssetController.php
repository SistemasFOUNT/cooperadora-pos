<?php

namespace App\Http\Controllers;

/*
 * ========================================================================
 * SISTEMA DE CACHE BUSTING PARA IMÁGENES - DOCUMENTACIÓN
 * ========================================================================
 *
 * Este sistema resuelve el problema del caché del navegador cuando cambias
 * imágenes pero mantienen el mismo nombre/ruta.
 *
 * CÓMO USAR EL SISTEMA:
 *
 * 1. EN BLADE TEMPLATES (para cualquier imagen):
 *    <img src="{{ asset_versioned('images/users/avatar.jpg') }}" alt="Avatar">
 *    <img src="{{ asset_versioned('images/logos/postgrado-logo.png') }}" alt="Logo">
 *    <img src="{{ asset_versioned('images/system/cualquier-imagen.png') }}" alt="Imagen">
 *
 * 2. EN CSS DINÁMICO (este archivo):
 *    Agregar variables como $logoVersion y usar en CSS:
 *    background-image: url('/images/mi-imagen.png{$versionVariable}');
 *
 * 3. EN JAVASCRIPT:
 *    const scriptUrl = "{{ asset_versioned('js/mi-script.js') }}";
 *    const imageUrl = "{{ asset_versioned('images/mi-imagen.png') }}";
 *
 * 4. PARA ARCHIVOS EN CONFIG (como AdminLTE):
 *    'path' => 'images/system/fount-logo.png?v=' . filemtime(public_path('images/system/fount-logo.png')),
 *
 * FUNCIONES DISPONIBLES:
 * - asset_versioned($path) → URL completa con cache busting
 * - asset_versioned_path($path) → Ruta relativa con cache busting
 *
 * CÓMO FUNCIONA:
 * - Usa la fecha de modificación del archivo (filemtime) como versión
 * - Cuando cambias una imagen, automáticamente cambia la URL
 * - El navegador detecta la nueva URL y descarga la imagen actualizada
 *
 * ARCHIVOS RELACIONADOS:
 * - app/Helpers/AssetHelper.php → Lógica principal
 * - app/Helpers/functions.php → Funciones helper globales
 * - app/Providers/HelperServiceProvider.php → Registro del sistema
 * - config/adminlte.php → Implementación en AdminLTE
 * - routes/web.php → Ruta para CSS dinámico
 * ========================================================================
 */

class AssetController
{
    /**
     * Genera CSS dinámico con URLs versionadas para imágenes
     */
    public function customImages()
    {
        $logoVersion = file_exists(public_path('images/system/fount-logo.png'))
            ? '?v=' . filemtime(public_path('images/system/fount-logo.png'))
            : '';

        $logoBackVersion = file_exists(public_path('images/system/fount-logo-back.png'))
            ? '?v=' . filemtime(public_path('images/system/fount-logo-back.png'))
            : '';

        $css = "
/* ==============================================
   CUSTOM IMAGES CSS - Cache Busting Enabled
   ============================================== */

/* Logo Principal */
.brand-link .brand-image {
    background-image: url('/images/system/fount-logo.png{$logoVersion}');
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    width: auto;
    max-width: 150px;
}

/* Logo de respaldo */
.logo-backup {
    background-image: url('/images/system/fount-logo-back.png{$logoBackVersion}');
}

/* Estilos adicionales del sistema */
.brand-link {
    display: flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    text-decoration: none;
    color: rgba(255,255,255,.8);
    font-weight: 300;
    font-size: 1.25rem;
    line-height: 1.5;
    transition: color .15s ease-in-out;
}

.brand-link:hover {
    color: #fff;
    text-decoration: none;
}

/* Responsive logo */
@media (max-width: 768px) {
    .brand-link .brand-image {
        max-width: 120px;
    }
}

@media (max-width: 576px) {
    .brand-link .brand-image {
        max-width: 100px;
    }
}
";

        return response($css)
            ->header('Content-Type', 'text/css')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
