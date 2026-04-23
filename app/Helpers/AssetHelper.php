<?php

namespace App\Helpers;

class AssetHelper
{
    /**
     * Genera una URL de asset con cache busting basado en la fecha de modificación del archivo
     *
     * @param string $path Ruta del asset relativa a public/
     * @return string URL del asset con parámetro de versión
     */
    public static function versioned($path)
    {
        $fullPath = public_path($path);

        // Si el archivo existe, usar la fecha de modificación como versión
        if (file_exists($fullPath)) {
            $version = filemtime($fullPath);
            return asset($path . '?v=' . $version);
        }

        // Si el archivo no existe, retornar la URL sin versión
        return asset($path);
    }

    /**
     * Genera una URL relativa con cache busting para usar en CSS
     *
     * @param string $path Ruta del asset relativa a public/
     * @return string URL relativa con parámetro de versión
     */
    public static function versionedPath($path)
    {
        $fullPath = public_path($path);

        // Si el archivo existe, usar la fecha de modificación como versión
        if (file_exists($fullPath)) {
            $version = filemtime($fullPath);
            return '/' . $path . '?v=' . $version;
        }

        // Si el archivo no existe, retornar la ruta sin versión
        return '/' . $path;
    }
}
