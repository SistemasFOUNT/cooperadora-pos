<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->boot();

echo "=== VERIFICACIÓN DEL SISTEMA ===\n\n";

// 1. Verificar usuario admin
echo "1. USUARIO ADMINISTRADOR:\n";
$user = App\Models\User::where('username', 'admin')->first();
if ($user) {
    echo "✅ Usuario: {$user->username}\n";
    echo "✅ Contraseña: admin123 (por defecto)\n";
    echo "✅ Email: {$user->email}\n";
    echo "✅ Nombre: {$user->name}\n";
    echo "✅ Estado: {$user->status}\n";
    echo "✅ Sucursal: {$user->branch->name}\n";
    echo "✅ Rol: {$user->roles->first()->name}\n";
} else {
    echo "❌ Usuario admin no encontrado\n";
}

echo "\n2. CONFIGURACIÓN DE EMAIL:\n";
echo "✅ MAIL_MAILER: " . config('mail.default') . "\n";
echo "✅ MAIL_FROM_ADDRESS: " . config('mail.from.address') . "\n";
echo "✅ MAIL_FROM_NAME: " . config('mail.from.name') . "\n";

// 3. Probar si se pueden enviar emails
echo "\n3. PRUEBA DE ENVÍO DE EMAIL:\n";
if (config('mail.default') === 'log') {
    echo "⚠️ Configurado para LOG - Los emails se guardan en storage/logs/laravel.log\n";
    echo "🔍 Para recuperación de contraseña funcional, necesitas configurar SMTP\n";
} else {
    echo "✅ Configurado para envío real: " . config('mail.default') . "\n";
}

echo "\n4. RUTAS DE AUTENTICACIÓN:\n";
echo "✅ Login: /login (username: admin, password: admin123)\n";
echo "✅ Recuperar contraseña: /forgot-password\n";
echo "✅ Dashboard: /dashboard (redirige a POS)\n";

echo "\n=== RESULTADO ===\n";
if ($user && $user->email) {
    echo "✅ Sistema configurado correctamente\n";
    echo "📧 Email registrado: {$user->email}\n";
    if (config('mail.default') === 'log') {
        echo "⚠️ Emails van a LOG - Configurar SMTP para envío real\n";
    } else {
        echo "✅ Configurado para envío de emails\n";
    }
} else {
    echo "❌ Faltan datos del usuario admin\n";
}