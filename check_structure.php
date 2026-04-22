<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

try {
    // Verificar estructura de tabla productos
    echo "=== Estructura de la tabla productos ===" . PHP_EOL;
    $product = new Product();
    $productTable = $product->getTable();
    echo "Tabla: $productTable" . PHP_EOL;

    $columns = DB::getSchemaBuilder()->getColumnListing($productTable);
    echo "Columnas: " . implode(', ', $columns) . PHP_EOL . PHP_EOL;

    // Verificar estructura de tabla users
    echo "=== Estructura de la tabla users ===" . PHP_EOL;
    $user = new User();
    $userTable = $user->getTable();
    echo "Tabla: $userTable" . PHP_EOL;

    $columns = DB::getSchemaBuilder()->getColumnListing($userTable);
    echo "Columnas: " . implode(', ', $columns) . PHP_EOL;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
