# PROTOCOLO DE ANÁLISIS - SISTEMA COOPERADORA
## Múltiples Puntos de Venta Aislados

---

## 🎯 OBJETIVO
Establecer metodología rigurosa para análisis y mantenimiento del sistema de la Cooperadora con 3 puntos de venta completamente separados (BOX, Postgrado, Centro Odontológico).

---

## 📋 ARQUITECTURA ACTUAL DEL SISTEMA

### 🏗️ Estructura Implementada (21/04/2026)
```
Sistema Cooperadora
├── BOX Cooperadora (/box/*)
├── Postgrado (/postgrado/*)  
└── Centro Odontológico (/odonto/*)
```

### 🛡️ Principio de Aislamiento
**CRÍTICO**: Cada punto de venta debe operar independientemente sin interferir con otros.

### 🖥️ Entornos del Sistema

#### Entorno de Desarrollo:
- **Sistema Operativo**: Windows
- **Base de Datos**: PostgreSQL
- **Servidor Web**: Laravel Development Server (php artisan serve)
- **Comandos**: PowerShell/CMD compatible

#### Entorno de Producción:
- **Sistema Operativo**: Linux Ubuntu
- **Base de Datos**: PostgreSQL
- **Servidor Web**: Nginx/Apache + PHP-FPM
- **Comandos**: Bash/Shell compatible

#### Configuración de Base de Datos:
- **Motor**: PostgreSQL (desarrollo y producción)
- **Conexiones**: Configuradas por punto de venta según sea necesario
- **Migraciones**: Compatibles entre Windows y Linux
- **Backup**: Procedimientos específicos por entorno

---

## FASE 1: RECONNAISSANCE DEL SISTEMA

### 1.1 Verificación de Estructura de Archivos

#### Comandos para Desarrollo (Windows):
```powershell
# Verificar controladores específicos
Get-ChildItem app\Http\Controllers\ -Filter "*Controller.php" | Where-Object { $_.Name -match "(Box|Postgrado|Odonto)" }

# Verificar servicios especializados  
Get-ChildItem app\Services\ -Filter "*Service.php" | Where-Object { $_.Name -match "(Box|Postgrado|Odonto)" }

# Verificar estructura de vistas
tree resources\views\box resources\views\postgrado resources\views\odonto /F

# Verificar middleware personalizado
Select-String -Path "app\Http\Middleware\*.php" -Pattern "punto_venta"
```

#### Comandos para Producción (Linux Ubuntu):
```bash
# Verificar controladores específicos
ls -la app/Http/Controllers/{Box,Postgrado,Odonto}Controller.php

# Verificar servicios especializados  
ls -la app/Services/{Box,Postgrado,Odonto}Service.php

# Verificar estructura de vistas
tree resources/views/{box,postgrado,odonto}/

# Verificar middleware personalizado
grep -r "punto_venta" app/Http/Middleware/
```

### 1.2 Análisis de Rutas y URLs
```bash
# Extraer rutas por punto de venta
php artisan route:list | grep -E "box\.|postgrado\.|odonto\."

# Verificar prefijos en web.php
grep -A 10 -B 5 "Route::prefix" routes/web.php

# Verificar redirección automática
grep -A 5 "/dashboard" routes/web.php
```

### 1.3 Inventario de Funcionalidades por Punto de Venta

#### BOX Cooperadora
**Operaciones de Ingresos:**
- [ ] Venta de productos elaborados por el Laboratorio de Insumos
- [ ] Cobro de cuotas a alumnos de tecnicaturas (Técnicatura Universitaria en Prótesis Dental y Técnicatura Universitaria en Asistencia Dental)
- [ ] Cobro de bonos a alumnos de grado (carrera de Odontología)
- [ ] Venta de kits especializados (cirugía, semiología, clínica de operatoria, clínica de prótesis)
- [ ] Cobros por otros rubros varios
- [ ] Cobros por prestaciones odontológicas de cátedras clínicas (Clínica de Operatoria, Cirugía I, Cirugía II, Clínica de Prótesis I, Clínica de Prótesis II)
- [ ] Cobros de trabajos odontológicos no clínicos (prótesis, implantes realizados por terceros - mecánicos dentales o Laboratorio de Prótesis)

**Operaciones de Egresos:**
- [ ] Pagos de sueldos a contratados
- [ ] Pagos a proveedores
- [ ] Otros pagos operativos

**Funcionalidades del Sistema:**
- [ ] Dashboard con estadísticas financieras y de ventas
- [ ] Sistema POS (Point of Sale) para múltiples tipos de transacciones
- [ ] Gestión de productos e inventario del Laboratorio
- [ ] Control de cuotas y bonos estudiantiles
- [ ] Reportes financieros y análisis por tipo de operación
- [ ] **Ruta principal**: `/box`

**Nota**: *Amplio espectro de operaciones financieras que requieren categorización y control específico*

#### Postgrado
**Operaciones de Ingresos:**
- [ ] Cobro de cuotas de las carreras de postgrado
- [ ] Cobro de cursos especializados

**Operaciones de Egresos:**
- [ ] Pagos de honorarios a dictantes y conferencistas
- [ ] Pagos por gastos varios operativos
- [ ] Pagos a proveedores (productos y/o servicios académicos)

**Funcionalidades del Sistema:**
- [ ] Dashboard académico con estadísticas de postgrados
- [ ] Gestión de estudiantes de carreras de postgrado
- [ ] Control de matrículas y certificaciones
- [ ] Administración de cursos y honorarios docentes
- [ ] Gestión financiera de ingresos/egresos académicos
- [ ] Reportes académicos y financieros especializados
- [ ] **Ruta principal**: `/postgrado`

**Nota**: *Sistema especializado en la gestión académica y financiera de programas de postgrado*

#### Centro Odontológico
**Prestaciones Odontológicas:**
- [ ] Cobros por prestaciones odontológicas (cirugías, endodoncias, operatoria dental, etc.)
- [ ] Tarifario diferenciado por ubicación:
  - [ ] Prestaciones en el centro odontológico
  - [ ] Prestaciones en guardia (precios diferenciados)

**Estudios Radiográficos:**
- [ ] Cobros por estudios radiográficos (panorámicas, Rx, tomografías, etc.)
- [ ] Tarifario diferenciado por tipo de paciente:
  - [ ] Estudios para pacientes internos
  - [ ] Estudios para profesionales externos

**Funcionalidades del Sistema:**
- [ ] Dashboard clínico con estadísticas de prestaciones
- [ ] Gestión de pacientes y historiales clínicos
- [ ] Agenda de citas médicas y estudios
- [ ] Control de tratamientos y procedimientos realizados
- [ ] Inventario de equipamiento e insumos médicos
- [ ] Facturación clínica con tarifarios diferenciados
- [ ] Sistema de historiales médicos digitales
- [ ] Reportes de prestaciones y estudios por ubicación/tipo
- [ ] **Ruta principal**: `/odonto`

**Nota**: *Sistema clínico especializado con gestión de tarifarios diferenciados según ubicación y tipo de paciente*

---

## FASE 2: ANÁLISIS DE DEPENDENCIAS

### 2.1 Verificación de Middleware y Seguridad

#### Comandos para Desarrollo (Windows):
```powershell
# Verificar middleware punto_venta
Get-Content app\Http\Middleware\PuntoVentaMiddleware.php

# Verificar aplicación en rutas
Select-String -Path "routes\*.php" -Pattern "punto_venta:"

# Verificar roles de usuario en PostgreSQL
php artisan tinker
>>> App\Models\User::select('name', 'punto_venta')->get()
```

#### Comandos para Producción (Linux Ubuntu):
```bash
# Verificar middleware punto_venta
cat app/Http/Middleware/PuntoVentaMiddleware.php

# Verificar aplicación en rutas
grep -r "punto_venta:" routes/

# Verificar roles de usuario en PostgreSQL
php artisan tinker
>>> App\Models\User::select('name', 'punto_venta')->get()
```

### 2.2 Mapeo de Modelos Compartidos
```bash
# Verificar modelos base del sistema
ls -la app/Models/{User,PuntoVenta,Sale,Product,Student}.php

# Buscar filtros por punto de venta en modelos
grep -r "punto_venta" app/Models/
grep -r "where.*punto" app/Models/
```

### 2.3 Análisis de Servicios Especializados
```bash
# Verificar lógica de negocio específica
grep -A 10 "class BoxService" app/Services/BoxService.php
grep -A 10 "class PostgradoService" app/Services/PostgradoService.php  
grep -A 10 "class OdontoService" app/Services/OdontoService.php
```

---

## FASE 3: DOCUMENTACIÓN DEL ESTADO ACTUAL

### 3.1 Snapshot de Configuración
```bash
# Estado de rutas por punto de venta
php artisan route:list --compact | grep -E "(box|postgrado|odonto)"

# Variables de entorno críticas  
grep -E "(DB_|APP_)" .env

# Verificar configuración de sesiones y auth
cat config/auth.php | grep -A 5 -B 5 "guards\|providers"
```

### 3.2 Mapeo de URLs Generadas
```bash
# URLs en vistas por punto de venta
grep -r "route\|url" resources/views/box/ --include="*.blade.php"
grep -r "route\|url" resources/views/postgrado/ --include="*.blade.php"
grep -r "route\|url" resources/views/odonto/ --include="*.blade.php"
```

### 3.3 Documentación de Flujo de Autenticación
```php
// Flujo actual de login y redirección:
// 1. Login → /login
// 2. Auth exitoso → /dashboard  
// 3. Middleware analiza user->punto_venta
// 4. Redirección automática → /{punto_venta}
```

---

## FASE 4: ANÁLISIS DE RIESGOS

### 4.1 Puntos Críticos de Conflicto
- [ ] **Rutas superpuestas**: Verificar que no hay solapamiento entre prefijos
- [ ] **Middleware bypass**: Asegurar que no se puede acceder sin autorización
- [ ] **Modelos compartidos**: Verificar filtros correctos por punto de venta
- [ ] **Sesiones cruzadas**: Verificar aislamiento de datos de usuario

### 4.2 Dependencias Frágiles Identificadas
- [ ] **Redirección automática**: Depende del campo `punto_venta` en users
- [ ] **Filtros de datos**: Deben aplicarse consistentemente en todos los queries
- [ ] **Middleware personalizado**: Crítico para la seguridad del sistema

### 4.3 Validación de Integridad
```bash
# Verificar que no hay rutas sin protección
php artisan route:list | grep -v "middleware" | grep -E "(box|postgrado|odonto)"

# Verificar consistencia de naming
grep -r "punto.venta" . --include="*.php" | head -20
```

---

## FASE 5: PROTOCOLO DE MODIFICACIONES

### 5.1 Antes de Cualquier Cambio

#### Para Entorno de Desarrollo (Windows):
```powershell
# OBLIGATORIO: Backup del estado actual
Copy-Item routes\web.php "routes\web.php.backup.$(Get-Date -Format 'yyyyMMdd_HHmmss')"
Copy-Item app\Http\Middleware\PuntoVentaMiddleware.php "app\Http\Middleware\PuntoVentaMiddleware.php.backup"

# Backup de base de datos PostgreSQL (Windows)
pg_dump -h localhost -U postgres -d cooperadora_db > "backup_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"

# Testing del estado actual
php artisan test --group=integration
Start-Process -NoNewWindow php -ArgumentList "artisan", "serve", "--host=127.0.0.1", "--port=8000"
Start-Sleep 3
Invoke-WebRequest -Uri "http://127.0.0.1:8000/box" -Method HEAD
Invoke-WebRequest -Uri "http://127.0.0.1:8000/postgrado" -Method HEAD
Invoke-WebRequest -Uri "http://127.0.0.1:8000/odonto" -Method HEAD
```

#### Para Entorno de Producción (Linux Ubuntu):
```bash
# OBLIGATORIO: Backup del estado actual
cp routes/web.php routes/web.php.backup.$(date +%Y%m%d_%H%M%S)
cp app/Http/Middleware/PuntoVentaMiddleware.php app/Http/Middleware/PuntoVentaMiddleware.php.backup

# Backup de base de datos PostgreSQL (Linux)
pg_dump -h localhost -U postgres cooperadora_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Testing del estado actual
php artisan test --group=integration
php artisan serve --host=127.0.0.1 --port=8000 &
sleep 3
curl -I "http://127.0.0.1:8000/box"
curl -I "http://127.0.0.1:8000/postgrado"  
curl -I "http://127.0.0.1:8000/odonto"
```

#### Verificaciones Específicas de PostgreSQL:
```sql
-- Verificar conexiones activas
SELECT application_name, client_addr, state, query 
FROM pg_stat_activity 
WHERE datname = 'cooperadora_db';

-- Verificar tablas del sistema
\dt
\d+ users
\d+ punto_ventas

-- Verificar datos de prueba
SELECT punto_venta, COUNT(*) FROM users GROUP BY punto_venta;
```

### 5.2 Modificaciones Incrementales por Punto de Venta

#### Para cambios en BOX Cooperadora:
1. **Verificar aislamiento**: Cambios no afectan postgrado/odonto
2. **Testear BoxController**: Verificar métodos específicos
3. **Validar BoxService**: Verificar lógica de negocio
4. **Comprobar vistas**: Verificar resources/views/box/

#### Para cambios en Postgrado:
1. **Verificar aislamiento académico**: No afecta operaciones clínicas/ventas
2. **Testear PostgradoController**: Verificar gestión académica
3. **Validar PostgradoService**: Verificar lógica de matrículas
4. **Comprobar vistas**: Verificar resources/views/postgrado/

#### Para cambios en Centro Odontológico:
1. **Verificar aislamiento clínico**: No afecta operaciones académicas/ventas  
2. **Testear OdontoController**: Verificar gestión clínica
3. **Validar OdontoService**: Verificar lógica médica
4. **Comprobar vistas**: Verificar resources/views/odonto/

### 5.3 Verificación Post-Cambio
```bash
# Después de CADA modificación
php artisan route:clear
php artisan config:clear
php artisan view:clear

# Verificar que redirección automática funciona
# (simular login con usuarios de diferentes puntos de venta)

# Verificar que no hay regresiones
php artisan test
```

---

## FASE 6: TESTING Y VALIDACIÓN

### 6.1 Testing Funcional por Punto de Venta

#### BOX Cooperadora
```bash
# Test de acceso y funcionalidad
curl -X GET "http://127.0.0.1:8000/box" -H "Authorization: Bearer {token}"
# Verificar: dashboard, productos, ventas, reportes
```

#### Postgrado  
```bash
# Test de acceso académico
curl -X GET "http://127.0.0.1:8000/postgrado" -H "Authorization: Bearer {token}"
# Verificar: estudiantes, matriculas, cursos, certificados
```

#### Centro Odontológico
```bash
# Test de acceso clínico  
curl -X GET "http://127.0.0.1:8000/odonto" -H "Authorization: Bearer {token}"
# Verificar: pacientes, agenda, tratamientos, facturación
```

### 6.2 Testing de Aislamiento
```php
// Verificar que usuarios de un punto de venta NO pueden acceder a otros
// Usuario BOX → intentar acceder a /postgrado → debe fallar
// Usuario Postgrado → intentar acceder a /odonto → debe fallar  
// Usuario Odonto → intentar acceder a /box → debe fallar
```

---

## ERRORES CRÍTICOS A EVITAR

### ❌ NUNCA hacer:
1. **Modificar middleware** sin verificar impacto en los 3 puntos de venta
2. **Cambiar estructura de rutas** sin actualizar redirección automática
3. **Mezclar funcionalidades** entre puntos de venta
4. **Ignorar filtros** por punto de venta en queries
5. **Compartir vistas** entre puntos de venta diferentes

### ✅ SIEMPRE hacer:
1. **Verificar aislamiento** después de cada cambio
2. **Testear los 3 puntos** de venta independientemente  
3. **Mantener servicios** especializados separados
4. **Documentar cambios** específicos por punto de venta
5. **Respetar la arquitectura** de separación implementada

---

## TEMPLATE DE ANÁLISIS PARA NUEVAS FUNCIONALIDADES

```markdown
# NUEVA FUNCIONALIDAD: [NOMBRE]

## Punto de Venta Afectado
- [ ] BOX Cooperadora únicamente
- [ ] Postgrado únicamente  
- [ ] Centro Odontológico únicamente
- [ ] Funcionalidad compartida (REVISAR NECESIDAD)

## Análisis de Impacto
- Controlador afectado: [BoxController/PostgradoController/OdontoController]
- Servicio afectado: [BoxService/PostgradoService/OdontoService]  
- Vistas afectadas: [resources/views/{punto_venta}/]
- Rutas nuevas: [listar con prefijo correcto]

## Verificaciones de Aislamiento
- [ ] No interfiere con otros puntos de venta
- [ ] Respeta middleware punto_venta
- [ ] Usa servicio especializado correcto
- [ ] Mantiene consistencia de nomenclatura

## Plan de Testing
1. [Test específico del punto de venta]
2. [Test de no regresión en otros puntos]
3. [Test de middleware y seguridad]
```

---

## � FLEXIBILIDAD Y ESCALABILIDAD DEL SISTEMA

### 🎯 Principios de Diseño Adaptativo

**IMPORTANTE**: Las funcionalidades de cada punto de venta pueden modificarse y ampliarse en el futuro. El sistema debe mantener criterio amplio y arquitectura flexible.

### 💡 Consideraciones para Futuras Expansiones

#### Para BOX Cooperadora:
- **Nuevos tipos de productos**: El sistema debe permitir fácil adición de categorías de productos del Laboratorio de Insumos
- **Nuevos rubros de cobro**: Estructura flexible para incorporar otros tipos de ingresos operativos
- **Ampliación de kits**: Capacidad de agregar nuevos tipos de kits especializados
- **Nuevas cátedras clínicas**: Escalabilidad para incorporar prestaciones de nuevas cátedras

#### Para Postgrado:
- **Nuevos programas académicos**: Flexibilidad para incorporar nuevas carreras de postgrado
- **Modalidades de curso**: Capacidad de manejar diferentes tipos de cursos (presencial, virtual, híbrido)
- **Nuevos tipos de honorarios**: Estructura extensible para diferentes tipos de pagos académicos

#### Para Centro Odontológico:
- **Nuevas prestaciones médicas**: Escalabilidad para incorporar nuevos tipos de tratamientos
- **Nuevos estudios**: Flexibilidad para agregar nuevos tipos de estudios radiográficos
- **Tarifarios dinámicos**: Sistema que permita fácil modificación de precios y categorías

### 🏗️ Arquitectura Preparada para Cambios

#### Estructura Modular:
- **Servicios especializados** permiten modificar lógica de negocio sin afectar otros componentes
- **Controladores separados** facilitan la adición de nuevas funcionalidades por punto de venta
- **Vistas independientes** permiten personalización específica sin conflictos

#### Base de Datos Flexible:
- **Modelos extensibles** que permiten agregar nuevas propiedades sin romper funcionalidad existente
- **Relaciones bien definidas** entre entidades para mantener consistencia
- **Campos configurables** para diferentes tipos de operaciones por punto de venta

#### Sistema de Configuración:
- **Variables de entorno** para parámetros que pueden cambiar
- **Archivos de configuración** específicos por punto de venta
- **Middleware adaptable** para nuevos tipos de validaciones

### 📋 Protocolo de Ampliación

```markdown
# AMPLIACIÓN DE FUNCIONALIDAD: [DESCRIPCIÓN]

## Análisis de Requisitos
- Punto de venta afectado: [BOX/Postgrado/Centro Odontológico]
- Tipo de ampliación: [Nueva funcionalidad/Modificación existente]
- Impacto esperado: [Bajo/Medio/Alto]

## Verificación de Compatibilidad
- [ ] La nueva funcionalidad respeta el aislamiento
- [ ] No requiere cambios en otros puntos de venta
- [ ] Es compatible con la estructura existente
- [ ] Mantiene los principios de seguridad

## Plan de Implementación Escalable
1. [Identificar componentes a modificar/crear]
2. [Verificar impacto en modelos existentes]
3. [Actualizar servicios especializados]
4. [Implementar vistas específicas]
5. [Agregar rutas con middleware apropiado]

## Testing de Escalabilidad
- [ ] La funcionalidad funciona independientemente
- [ ] No afecta el rendimiento de otros puntos de venta
- [ ] Es fácilmente configurable y modificable
- [ ] Mantiene la consistencia del sistema
```

### 🚀 Futuras Mejoras Planificadas

#### Corto Plazo (v1.x):
- **Reportes avanzados** por tipo de operación en cada punto de venta
- **Dashboard personalizable** con métricas específicas
- **Sistema de notificaciones** internas entre puntos de venta
- **Integración con sistemas contables** externos

#### Mediano Plazo (v2.x):
- **API REST** para integración con aplicaciones móviles
- **Sistema de auditoría** completo de transacciones
- **Herramientas de análisis** predictivo por punto de venta
- **Módulo de configuración** dinámica de tarifarios

#### Largo Plazo (v3.x):
- **Inteligencia artificial** para optimización de operaciones
- **Sistema de recomendaciones** personalizadas
- **Integración blockchain** para trazabilidad de transacciones
- **Módulos adicionales** según necesidades emergentes

---

**🌟 FILOSOFÍA DEL SISTEMA**: Mantener siempre equilibrio entre estabilidad actual y flexibilidad futura, permitiendo crecimiento orgánico sin comprometer el aislamiento y la integridad del sistema.

---

## �📞 CONTACTOS TÉCNICOS Y RESPONSABILIDADES

### Responsable por Punto de Venta
- **BOX Cooperadora**: [Responsable] - Ventas y suministros
- **Postgrado**: [Responsable] - Operaciones académicas  
- **Centro Odontológico**: [Responsable] - Operaciones clínicas

### Responsable de Arquitectura
- **Sistema completo**: [Responsable] - Aislamiento y middleware

---

## 🌍 CONSIDERACIONES DE DEPLOYMENT Y COMPATIBILIDAD

### 🔄 Compatibilidad entre Entornos

#### Diferencias Críticas a Considerar:
- **Rutas de archivos**: Windows usa `\`, Linux usa `/` (Laravel maneja automáticamente)
- **Comandos de sistema**: PowerShell vs Bash
- **Permisos de archivos**: Windows vs Linux (storage/, bootstrap/cache/)
- **Variables de entorno**: Configuración específica por entorno

#### Archivos de Configuración por Entorno:
```
.env (desarrollo en Windows)
.env.production (producción en Linux Ubuntu)
.env.example (template para nuevos entornos)
```

### 🚀 Procedimiento de Deployment

#### Configuración de Variables de Entorno:

**Desarrollo (Windows) - archivo `.env`:**
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de datos PostgreSQL local
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cooperadora_pos
DB_USERNAME=cooperadora_user
DB_PASSWORD=your_password_here
```

**Producción (Linux Ubuntu) - archivo `.env.production`:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Base de datos PostgreSQL producción
DB_CONNECTION=pgsql
DB_HOST=10.100.2.4
DB_PORT=5432
DB_DATABASE=cooperadora_pos
DB_USERNAME=postgres
DB_PASSWORD=Sistemas4137
```

#### Pre-deployment (Desarrollo → Producción):
```bash
# En entorno de desarrollo (Windows)
# Verificar archivo de configuración de desarrollo
if (Test-Path .env) { Get-Content .env | Select-String "DB_" }

# Verificar que existe configuración de producción
if (Test-Path .env.production) { 
    Write-Host "Archivo .env.production encontrado"
    Get-Content .env.production | Select-String "DB_"
}

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Verificar compatibilidad de migraciones con PostgreSQL
php artisan migrate:status
php artisan migrate --pretend

# Generar backup antes del deploy
pg_dump -h localhost -U postgres cooperadora_pos > pre_deploy_backup.sql
```

#### Deployment en Producción (Linux Ubuntu):
```bash
# En servidor de producción
git pull origin main
composer install --optimize-autoloader --no-dev

# Configurar variables de entorno para producción
if [ ! -f .env ]; then
    echo "Copiando configuración de producción..."
    cp .env.production .env
else
    echo "Verificando configuración existente..."
    diff .env .env.production
fi

# Verificar configuración de base de datos
grep "DB_" .env

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# Verificar permisos críticos
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/

# Verificar funcionamiento de los 3 puntos de venta
curl -I "https://cooperadora.universidad.edu.ar/box"
curl -I "https://cooperadora.universidad.edu.ar/postgrado"
curl -I "https://cooperadora.universidad.edu.ar/odonto"
```

### 🔧 Configuración Específica de PostgreSQL

### 🔧 Configuración Específica de PostgreSQL

#### Verificaciones de Base de Datos:

#### Logs a Supervisar:
- **Laravel logs**: `storage/logs/laravel.log`
- **PostgreSQL logs**: 
  - Windows: `%PGDATA%\log\`
  - Linux: `/var/log/postgresql/`
- **Servidor web logs**: Nginx/Apache error logs (producción)

#### Métricas Críticas:
- **Conexiones PostgreSQL**: Monitorear conexiones concurrentes
- **Tiempo de respuesta**: Por punto de venta
- **Errores 403/404**: Problemas de middleware o rutas
- **Memoria y CPU**: Especialmente en producción

---

## 🔄 VERSIONADO Y CHANGELOG

### Versión Actual: v1.0.0 (21/04/2026)
#### Especificaciones Técnicas:
- ✅ **Desarrollo**: Windows + PostgreSQL + Laravel Development Server
- ✅ **Producción**: Linux Ubuntu + PostgreSQL + Nginx/Apache
- ✅ **Framework**: Laravel 11 con arquitectura modular
- ✅ **Base de Datos**: PostgreSQL (compatible en ambos entornos)

#### Funcionalidades Implementadas:
- ✅ Separación completa de 3 puntos de venta con funcionalidades específicas
- ✅ **BOX Cooperadora**: Sistema completo de ventas, cobros académicos y prestaciones clínicas
- ✅ **Postgrado**: Gestión académica y financiera de programas de postgrado  
- ✅ **Centro Odontológico**: Sistema clínico con tarifarios diferenciados
- ✅ Middleware de protección implementado (`punto_venta`)
- ✅ Controladores especializados (BoxController, PostgradoController, OdontoController)
- ✅ Servicios de negocio específicos (BoxService, PostgradoService, OdontoService)
- ✅ Redirección automática por rol de usuario
- ✅ Estructura de vistas completamente independientes
- ✅ Arquitectura preparada para escalabilidad y modificaciones futuras
- ✅ **Compatibilidad multi-entorno** (Windows desarrollo / Linux producción)

### Funcionalidades Detalladas por Versión:

#### v1.0.0 - Funcionalidades BOX Cooperadora:
- ✅ Venta de productos del Laboratorio de Insumos
- ✅ Cobro de cuotas de tecnicaturas (Prótesis Dental, Asistencia Dental)
- ✅ Cobro de bonos a estudiantes de grado (Odontología)
- ✅ Venta de kits especializados (cirugía, semiología, clínicas)
- ✅ Gestión de prestaciones de cátedras clínicas
- ✅ Control de trabajos odontológicos de terceros
- ✅ Sistema de pagos (sueldos, proveedores, otros)

#### v1.0.0 - Funcionalidades Postgrado:
- ✅ Cobro de cuotas de carreras de postgrado
- ✅ Gestión de cursos especializados
- ✅ Control de honorarios docentes y conferencistas
- ✅ Gestión de gastos operativos y proveedores académicos

#### v1.0.0 - Funcionalidades Centro Odontológico:
- ✅ Prestaciones odontológicas con tarifario diferenciado (centro/guardia)
- ✅ Estudios radiográficos con precios diferenciados (internos/externos)
- ✅ Sistema de gestión clínica integral

### Próximas Versiones Planificadas:

#### v1.1.0 - Optimizaciones y Reportes:
- [ ] Reportes financieros avanzados por tipo de operación (BOX)
- [ ] Reportes académicos especializados (Postgrado)
- [ ] Reportes clínicos y de prestaciones (Centro Odontológico)
- [ ] Dashboard personalizable por punto de venta
- [ ] Optimizaciones de performance del sistema

#### v1.2.0 - Funcionalidades Específicas Adicionales:
- [ ] Sistema de descuentos configurables (BOX)
- [ ] Gestión de becas y financiamientos (Postgrado)
- [ ] Historia clínica digital avanzada (Centro Odontológico)
- [ ] Integración con sistemas contables externos
- [ ] Sistema de notificaciones entre puntos de venta

#### v1.3.0 - Expansión de Capacidades:
- [ ] Nuevas categorías de productos del Laboratorio de Insumos
- [ ] Modalidades de cursos virtuales e híbridos (Postgrado)
- [ ] Nuevos tipos de estudios radiográficos (Centro Odontológico)
- [ ] API REST para aplicaciones móviles
- [ ] Sistema de auditoría completo

### Flexibilidad de Modificaciones:
- **🔄 Modificaciones permitidas**: Las funcionalidades específicas pueden ampliarse según necesidades operativas
- **📈 Escalabilidad**: Sistema preparado para incorporar nuevos tipos de operaciones en cada punto de venta
- **🛡️ Principio de aislamiento**: Cualquier modificación debe respetar la separación entre puntos de venta

---

**🚨 RECORDATORIO CRÍTICO**: Este sistema funciona por aislamiento completo entre puntos de venta. Cualquier modificación que rompa este principio puede afectar la estabilidad de todo el sistema.

**💡 FLEXIBILIDAD OPERATIVA**: Las funcionalidades específicas de cada punto de venta pueden modificarse y ampliarse según necesidades operativas futuras, manteniendo siempre el principio de aislamiento y la arquitectura modular implementada.

**🌍 ENTORNOS DEL SISTEMA**:
- **Desarrollo**: Windows + PostgreSQL (archivo `.env`)
- **Producción**: Linux Ubuntu + PostgreSQL (archivo `.env.production`)
- **Deployment**: Copia `.env.production` a `.env` en servidor de producción
- **Compatibilidad**: Laravel maneja automáticamente las diferencias entre entornos

**📋 OPERACIONES ACTUALES**:
- **BOX**: Ventas, cobros académicos, prestaciones clínicas, pagos operativos
- **Postgrado**: Gestión académica y financiera de programas de postgrado
- **Centro Odontológico**: Prestaciones clínicas con tarifarios diferenciados

---

*Protocolo creado: 21 de abril de 2026*  
*Última actualización: 21 de abril de 2026 - Funcionalidades específicas y entornos multi-plataforma detallados*
