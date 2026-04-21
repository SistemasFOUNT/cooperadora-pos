# IMPORTACIÓN DE ESTUDIANTES DESDE CSV

## ✅ Estado Actual de Importaciones

### Completadas:
- **✅ Tecnicatura en Asistencia Odontológica**: 70 estudiantes importados exitosamente desde `TecUniAsistenciaDental.csv`
- **✅ Tecnicatura en Prótesis Dental**: 205 estudiantes importados exitosamente desde `TecUniProtesisDental.csv` (1 duplicado detectado)

### Normalizaciones aplicadas:
- **✅ Formato de nombres**: 98 estudiantes con 183 campos normalizados (Primera letra mayúscula, preposiciones en minúscula)
- **✅ Plan de estudio como string**: 275 estudiantes actualizados para manejar planes alfanuméricos (78919 para Prótesis, 20_TECAD para Asistencia)

### Pendientes:
- **⏳ Grado en Odontología**: Pendiente  
- **⏳ Postgrado**: Pendiente

**Total estudiantes importados: 275** (con formato uniforme y planes correctos)

## Configuración de Carreras y Cuotas

El sistema maneja diferentes tecnicaturas con sus respectivas cuotas según los archivos CSV proporcionados.

## Formato del CSV

Los archivos CSV deben estar separados por `;` y contener exactamente estas columnas:

### Columnas requeridas:
- **apellido**: Apellido del estudiante
- **nombre**: Nombre del estudiante  
- **dni**: Número de documento
- **email**: Correo electrónico
- **telefono**: Número de teléfono
- **legajo**: Número de legajo del estudiante
- **plan**: Plan de estudios/año académico
- **ingreso**: Año de ingreso (ej: 2023)
- **reinscripcion**: Año de última reinscripción (debe coincidir con año actual para estudiantes activos)

### Ejemplo de CSV:
```csv
apellido;nombre;dni;email;telefono;legajo;plan;ingreso;reinscripcion
Pérez;Juan;12345678;juan@email.com;1234567890;TP2023001;1;2023;2026
González;María;23456789;maria@email.com;0987654321;TA2024002;2;2024;2026
```

## Lógica Automática del Sistema

### Estado del Estudiante (basado en reinscripción):
- **Activo**: Si reinscripción = año actual (2026)
- **Inactivo**: Si reinscripción = año anterior (2025)  
- **Dropout**: Si reinscripción < año actual - 2

### Fecha de Inscripción:
- Se usa el año de **ingreso** para establecer la fecha de matrícula
- Formato: 1 de marzo del año de ingreso

### Número de Legajo:
- Si el CSV incluye **legajo**, se usa ese número
- Si no tiene legajo, se genera automáticamente: `[PREFIJO][AÑO][SECUENCIA]`

## Comandos de Importación por Carrera

Cada archivo CSV debe ser específico de una carrera:

### Tecnicatura en Prótesis Dental:
```bash
php artisan students:import-csv protesis_dental_2026.csv tecnicatura_protesis
```

### Tecnicatura en Asistencia Odontológica:
```bash
php artisan students:import-csv asistencia_dental_2026.csv tecnicatura_asistencia
```

### Grado en Odontología:
```bash
php artisan students:import-csv grado_odontologia_2026.csv grado_odontologia
```

### Postgrado:
```bash
php artisan students:import-csv postgrado_2026.csv postgrado
```

## Preparación del Sistema

### 1. Ejecutar migraciones:
```bash
php artisan migrate
php artisan db:seed --class=CareerFeeConfigSeeder
```

### 2. Simulación antes de importar:
```bash
php artisan students:import-csv archivo.csv tecnicatura_protesis --dry-run
```

## Reporte de Importación

El comando proporciona un reporte detallado:
- **Importados**: Estudiantes procesados exitosamente
- **Duplicados**: Estudiantes que ya existen (por DNI)
- **Errores**: Registros con datos faltantes o inválidos
- **Estado detectado**: Activo/Inactivo/Dropout basado en reinscripción

## Carreras Configuradas

### Tecnicatura en Prótesis Dental (tecnicatura_protesis):
- Cuota mensual: $25,000
- Matrícula: $15,000
- Duración: 24 meses

### Tecnicatura en Asistencia Odontológica (tecnicatura_asistencia):
- Cuota mensual: $22,000  
- Matrícula: $12,000
- Duración: 24 meses

### Grado en Odontología (grado_odontologia):
- Cuota mensual: $45,000
- Matrícula: $30,000
- Duración: 60 meses

### Postgrado (postgrado):
- Cuota mensual: $35,000
- Matrícula: $20,000
- Duración: 24 meses

## Características del Sistema

### ✅ **Automático**:
- Genera números de estudiante únicos por carrera (TP2026001, TA2026001, etc.)
- Asigna cuotas según configuración de carrera
- Detecta duplicados por DNI

### ✅ **Flexible**:
- Mapeo automático de nombres de columnas
- Soporte para diferentes formatos de CSV
- Delimitadores configurables

### ✅ **Seguro**:
- Modo dry-run para simular importación
- Validación de datos requeridos
- Rollback automático en caso de error
- Reporte detallado de resultados

### ✅ **Mantenible**:
- Configuración de cuotas centralizada
- Fácil agregar nuevas carreras
- Logs detallados de importación

## Gestión de Cuotas

Cada carrera tiene configuración individual:
- **Cuota mensual**: Monto regular por mes
- **Matrícula**: Pago inicial de inscripción  
- **Certificado**: Costo del título/certificado
- **Aranceles adicionales**: Laboratorio, materiales, etc.

## Comandos Útiles

### Ver configuración de carreras:
```bash
php artisan tinker
>>> App\Models\CareerFeeConfig::all();
```

### Verificar estudiantes importados:
```bash
php artisan tinker
>>> App\Models\Student::where('career_type', 'tecnicatura_protesis')->count();
```

### Actualizar cuotas de una carrera:
```bash
php artisan tinker
>>> $career = App\Models\CareerFeeConfig::where('career_type', 'tecnicatura_protesis')->first();
>>> $career->update(['monthly_fee' => 28000]);
```
