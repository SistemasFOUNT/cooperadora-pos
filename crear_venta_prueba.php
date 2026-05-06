<?php

// Script para crear datos de prueba para facturación
use App\Models\Sale;
use App\Models\Product;
use App\Models\User;
use App\Models\PuntoVenta;
use App\Models\SaleItem;

// Obtener el punto de venta BOX existente
$puntoVenta = PuntoVenta::where('codigo', 'BOX')->first();

// Obtener primer usuario y primer producto
$usuario = User::first();
$producto = Product::first();

if ($usuario && $producto && $puntoVenta) {
    // Crear venta de prueba
    $venta = Sale::create([
        'usuario_id' => $usuario->id,
        'punto_venta_id' => $puntoVenta->id,
        'fecha_venta' => now()->toDateString(), // Fecha de venta requerida
        'type' => 'product_sale', // Valor válido según check constraint
        'subtotal' => 150.00, // Subtotal requerido
        'total' => 150.00,
        'payment_method_id' => 1, // 1 = Efectivo
        'sale_number' => 'V-' . str_pad(1, 6, '0', STR_PAD_LEFT),
        'created_at' => now(),
        'updated_at' => now()
    ]);

    // Crear item de venta
    SaleItem::create([
        'sale_id' => $venta->id,
        'product_id' => $producto->id,
        'quantity' => 1,
        'price' => 150.00
    ]);

    echo "✅ Venta de prueba creada:\n";
    echo "- ID: {$venta->id}\n";
    echo "- Total: $150.00\n";
    echo "- Usuario: {$usuario->name}\n";
    echo "- Producto: {$producto->name}\n";
    echo "- Punto de Venta: {$puntoVenta->nombre} (ID: {$puntoVenta->id})\n";
    echo "\n🧪 Ahora puedes probar la facturación en: /box/ventas-del-dia\n";
} else {
    echo "❌ Error: Datos faltantes\n";
    echo "- Usuario: " . ($usuario ? "✅" : "❌") . "\n";
    echo "- Producto: " . ($producto ? "✅" : "❌") . "\n";
    echo "- Punto Venta BOX: " . ($puntoVenta ? "✅" : "❌") . "\n";
}
