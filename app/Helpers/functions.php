<?php

use App\Helpers\AssetHelper;

if (!function_exists('asset_versioned')) {
    /**
     * Genera una URL de asset con cache busting automático
     *
     * @param string $path
     * @return string
     */
    function asset_versioned($path)
    {
        return AssetHelper::versioned($path);
    }
}

if (!function_exists('asset_versioned_path')) {
    /**
     * Genera una ruta relativa de asset con cache busting para CSS
     *
     * @param string $path
     * @return string
     */
    function asset_versioned_path($path)
    {
        return AssetHelper::versionedPath($path);
    }
}
