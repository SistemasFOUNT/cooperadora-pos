@echo off
echo ========================================
echo  Sistema POS Cooperadora - Setup Windows
echo ========================================
echo.

REM Verificar que estamos en el directorio correcto
if not exist composer.json (
    echo ERROR: Este script debe ejecutarse desde el directorio raiz del proyecto Laravel
    echo Presiona cualquier tecla para salir...
    pause >nul
    exit /b 1
)

echo ✓ Verificando directorio del proyecto...

REM Verificar PHP
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PHP no esta instalado o no esta en el PATH
    echo Por favor instala PHP 8.2 o superior
    echo Presiona cualquier tecla para salir...
    pause >nul
    exit /b 1
)

echo ✓ PHP encontrado

REM Verificar Composer
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Composer no esta instalado o no esta en el PATH
    echo Por favor instala Composer desde https://getcomposer.org/
    echo Presiona cualquier tecla para salir...
    pause >nul
    exit /b 1
)

echo ✓ Composer encontrado

REM Verificar Node.js
node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Node.js no esta instalado o no esta en el PATH
    echo Por favor instala Node.js LTS desde https://nodejs.org/
    echo Presiona cualquier tecla para salir...
    pause >nul
    exit /b 1
)

echo ✓ Node.js encontrado

echo.
echo ========================================
echo  Instalando dependencias PHP...
echo ========================================
composer install --no-dev --optimize-autoloader

if %errorlevel% neq 0 (
    echo ERROR: Error al instalar dependencias PHP
    echo Presiona cualquier tecla para salir...
    pause >nul
    exit /b 1
)

echo.
echo ========================================
echo  Configurando archivo de entorno...
echo ========================================

if not exist .env (
    echo Copiando .env.example a .env...
    copy .env.example .env
    echo ✓ Archivo .env creado
    echo.
    echo IMPORTANTE: Debes configurar la conexion a PostgreSQL en .env
    echo Edita el archivo .env y configura:
    echo - DB_DATABASE=cooperadora_pos
    echo - DB_USERNAME=tu_usuario
    echo - DB_PASSWORD=tu_password
    echo.
) else (
    echo ✓ Archivo .env ya existe
)

echo.
echo ========================================
echo  Generando clave de aplicacion...
echo ========================================
php artisan key:generate

echo.
echo ========================================
echo  Instalando dependencias Node.js...
echo ========================================
npm install

if %errorlevel% neq 0 (
    echo ERROR: Error al instalar dependencias Node.js
    echo Presiona cualquier tecla para salir...
    pause >nul
    exit /b 1
)

echo.
echo ========================================
echo  Configuracion de Base de Datos
echo ========================================
echo.
echo Antes de continuar, asegurate de que:
echo 1. PostgreSQL este instalado y ejecutandose
echo 2. Hayas creado la base de datos 'cooperadora_pos'
echo 3. Hayas configurado el usuario y password en .env
echo.
set /p continue="¿Continuar con las migraciones? (s/n): "

if /i "%continue%"=="s" (
    echo.
    echo ========================================
    echo  Ejecutando migraciones y seeders...
    echo ========================================
    php artisan migrate:fresh --seed
    
    if %errorlevel% neq 0 (
        echo ERROR: Error al ejecutar migraciones
        echo Verifica la configuracion de base de datos en .env
        echo Presiona cualquier tecla para salir...
        pause >nul
        exit /b 1
    )
    
    echo ✓ Base de datos configurada correctamente
) else (
    echo.
    echo Migraciones omitidas. Recuerda ejecutar:
    echo php artisan migrate:fresh --seed
)

echo.
echo ========================================
echo  Compilando assets...
echo ========================================
npm run build

if %errorlevel% neq 0 (
    echo WARNING: Error al compilar assets, pero el proyecto deberia funcionar
)

echo.
echo ========================================
echo  ¡Setup completado!
echo ========================================
echo.
echo Para iniciar el servidor de desarrollo ejecuta:
echo   php artisan serve
echo.
echo Para compilar assets en modo desarrollo ejecuta:
echo   npm run dev
echo.
echo Usuarios por defecto:
echo   Username: admin
echo   Password: admin123
echo.
echo Para acceder al sistema visita:
echo   http://localhost:8000
echo.
echo Presiona cualquier tecla para salir...
pause >nul