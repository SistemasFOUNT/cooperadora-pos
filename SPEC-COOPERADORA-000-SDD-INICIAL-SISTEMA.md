# SPEC-COOPERADORA-000
## SDD inicial del sistema (baseline tecnico y visual)

---

## 1) Encabezado

ID Spec: SPEC-COOPERADORA-000  
Titulo: SDD inicial del sistema Cooperadora  
Fecha: 22-05-2026  
Autor: Equipo Cooperadora (asistido por IA)  
Estado: approved  
Modulo principal: TRANSVERSAL  
Modulos potencialmente impactados: BOX, Postgrado, Odonto, Admin  
Relacion con cobro unificado: si (por estandar transversal)

---

## 2) Contexto y objetivo

Situacion actual:
- El proyecto opera con arquitectura multi-modulo (BOX, Postgrado, Odonto) con aislamiento por punto de venta.
- Existe protocolo Skill-driven + Spec-driven para evolucion segura del sistema.
- Hay coexistencia de componentes visuales AdminLTE y layouts Bootstrap con DataTables.

Problema concreto:
- Falta una especificacion base unica que deje explicitado el stack oficial por entorno y el estandar visual obligatorio para nuevas implementaciones.

Objetivo de esta spec:
- Definir el baseline tecnico y de UI para desarrollar de forma consistente entre desarrollo y produccion.
- Establecer guardrails de codigo para evitar errores por diferencias de entorno.
- Estandarizar estilos de tablas, dashboards, iconografia, CSS y tipografia.

---

## 3) Alcance

### Incluye
- Sistemas y herramientas de desarrollo.
- Sistemas y herramientas de produccion.
- Diferencias criticas dev/prod que impactan codigo.
- Estandar visual vigente (AdminLTE, DataTables, Font Awesome, CSS principal, fuentes).
- Reglas obligatorias para nuevas funcionalidades.

### No incluye
- Refactor tecnico masivo de vistas legacy.
- Migracion completa de Bootstrap 4/5 en una sola iteracion.
- Rediseño UI total.

---

## 4) Sistemas utilizados - Desarrollo

Entorno objetivo de desarrollo:
- SO: Windows.
- Framework: Laravel 11.
- PHP: 8.4.18 (paridad definida en composer config.platform.php).
- Base de datos: PostgreSQL (local).
- Servidor local: `php artisan serve`.
- Frontend build: Vite + Tailwind + PostCSS + Alpine.
- Logs: nivel debug en entorno local.
- Queue: `sync` en desarrollo local.
- Cache/Sesion: `file` en desarrollo local (segun `.env.example`).

Referencias internas:
- `DESARROLLO-WINDOWS.md`
- `ENTORNOS-CONFIGURADOS.md`
- `.env.example`
- `composer.json`
- `package.json`

---

## 5) Sistemas utilizados - Produccion

Entorno objetivo de produccion:
- SO: Ubuntu Server 20.04+.
- Web server: Apache 2.4.
- PHP: 8.4.18.
- Base de datos: PostgreSQL 14.
- SSL: Let's Encrypt.
- CI/CD: GitHub Actions + scripts de `deployment/`.
- Sesiones: `database`.
- Queue: `database` (en documentacion tambien se contempla redis segun escenario).
- Cache: `database` (en documentacion general tambien se contempla redis segun optimizacion).
- Logs: nivel `error`.

Referencias internas:
- `deployment/DEPLOYMENT-GUIDE.md`
- `ENTORNOS-CONFIGURADOS.md`
- `.env.production`
- `deployment/*.sh`

---

## 6) Variaciones dev/prod que deben condicionar el codigo

Reglas obligatorias de implementacion:
- RV-01 (SO y filesystem): no asumir comportamiento case-insensitive de Windows. En codigo, nombres de clases/archivos/rutas deben respetar mayusculas exactas para Linux.
- RV-02 (rutas): evitar hardcode de separadores de path. Usar helpers de Laravel (`storage_path`, `public_path`, etc.).
- RV-03 (DB): escribir consultas compatibles con PostgreSQL (sin funciones MySQL-especificas).
- RV-04 (colas): no depender de ejecucion inmediata de jobs. En produccion pueden correr asincronos.
- RV-05 (cache/sesion): no codificar logica que dependa de `file` solamente; soportar `database` y opcion redis.
- RV-06 (logging): mensajes utiles en debug, pero sin exponer datos sensibles en produccion.
- RV-07 (assets): cualquier recurso nuevo debe pasar por pipeline Vite o estar correctamente publicado en `public/`.
- RV-08 (config): no hardcodear secretos/hosts/credenciales en codigo.

---

## 7) Estilos utilizados (baseline visual)

### 7.1 Dashboard y layout administrativo
- Framework principal de paneles: AdminLTE (Laravel-AdminLTE).
- Patron de vistas recomendado para modulos administrativos: `@extends('adminlte::page')`.
- Sidebar/topnav: segun configuracion de `config/adminlte.php`.

### 7.2 Tablas en formato DataTables
- Estandar global: `public/js/datatables-config.js`.
- Defaults obligatorios para nuevas tablas:
  - `responsive: true`
  - `pageLength: 20`
  - idioma espanol (`es-ES`)
  - columna de acciones no ordenable/no searchable cuando corresponda.
- Debe usarse `window.DataTableConfig.initTable(...)` en lugar de inicializaciones dispersas cuando sea posible.

### 7.3 Iconografia
- Estandar de iconos: Font Awesome (`fas fa-*` en vistas AdminLTE).
- Mantener consistencia semantica (ej.: acciones de ver/editar/eliminar con iconos homogeneos).

### 7.4 CSS principal y capas
- CSS base compilado: `resources/css/app.css` (entrada Vite/Tailwind).
- Capa de mejoras UI transversales: `public/css/ui-improvements.css`.
- Capa de imagenes/logos: `public/css/custom-images.css`.
- Evitar estilos inline salvo casos justificados (tickets/plantillas puntuales).

### 7.5 Tipografia (titulos y texto normal)
- Base del panel AdminLTE: `Source Sans Pro` con fallbacks del sistema (definido por AdminLTE).
- En layouts Vite/Tailwind puede aparecer `Figtree`; para modulos administrativos prevalece stack de AdminLTE.
- Titulos: usar jerarquia `h1/h2/h3` de AdminLTE/Bootstrap, evitando tamanos arbitrarios por vista.
- Texto normal: mantener tipografia base del layout y pesos estandar (400/500) para legibilidad.

---

## 8) Requisitos funcionales del baseline

- RF-01: Todo desarrollo nuevo debe declarar su modulo principal (BOX/Postgrado/Odonto/Transversal).
- RF-02: Toda tabla de listado nueva debe aplicar estandar DataTables del proyecto.
- RF-03: Todo dashboard/vista administrativa nueva debe integrarse con AdminLTE salvo excepcion documentada.
- RF-04: Toda iconografia nueva en panel administrativo debe usar Font Awesome de forma consistente.
- RF-05: Toda decision tecnica que dependa de entorno debe ser compatible con Windows dev y Ubuntu prod.

---

## 9) Requisitos no funcionales

- RNF-01 (consistencia visual): componentes y estilos deben seguir baseline de esta spec.
- RNF-02 (mantenibilidad): centralizar configuraciones (DataTables/CSS compartido) y evitar duplicaciones.
- RNF-03 (seguridad): sin credenciales hardcodeadas ni leaks en vistas/logs.
- RNF-04 (portabilidad): paridad funcional entre dev/prod para rutas, assets y jobs.
- RNF-05 (observabilidad): errores rastreables en ambos entornos con nivel de log apropiado.

---

## 10) Criterios de aceptacion (Given/When/Then)

1. Given una nueva pantalla administrativa, When se implementa, Then usa layout AdminLTE y estilos del baseline.
2. Given una nueva tabla de gestion, When se renderiza, Then usa DataTables con `pageLength=20`, responsive e idioma espanol.
3. Given un nuevo cambio backend, When se despliega en Ubuntu, Then no falla por supuestos de Windows (rutas, mayusculas, colas, cache/sesion).
4. Given una nueva feature transversal, When se revisa, Then cumple esta spec y el protocolo de aislamiento por punto de venta.

---

## 11) Checklist tecnico para nuevas implementaciones

- [ ] Defini modulo principal y posibles modulos impactados.
- [ ] Verifique compatibilidad Windows/Ubuntu en codigo nuevo.
- [ ] Evite hardcodes de secretos/rutas dependientes de SO.
- [ ] Aplique DataTables estandar para listados.
- [ ] Use iconos Font Awesome consistentes.
- [ ] Reuse CSS principal (`app.css`, `ui-improvements.css`, `custom-images.css`) sin duplicar reglas.
- [ ] Respete jerarquia de tipografia y componentes del layout.

---

## 12) Riesgos y mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigacion |
|---|---|---|---|
| Divergencia visual entre vistas nuevas y legacy | Media | Media | Baseline obligatorio + revision UI por checklist |
| Fallas solo en produccion por diferencias de entorno | Alta | Alta | Aplicar reglas RV-01..RV-08 y pruebas en entorno cercano a prod |
| Inicializaciones DataTables inconsistentes | Media | Media | Priorizar `DataTableConfig.initTable` y retirar duplicaciones gradualmente |
| Dependencia de stack mixto (Bootstrap 4/5) | Media | Media | No mezclar componentes sin validar compatibilidad por pantalla |

---

## 13) Definition of Done (DoD)

- [x] Baseline tecnico dev/prod documentado.
- [x] Diferencias de entorno traducidas a reglas de codigo.
- [x] Estilo UI base documentado (AdminLTE, DataTables, Font Awesome, CSS, tipografia).
- [x] Checklist operativo definido para nuevas features.
- [x] Spec lista para usarse como referencia obligatoria.

---

## 14) Registro de decisiones (ADR breve)

Decision:
- Definir una spec SDD inicial unica para estandarizar stack y estilo en todo el proyecto.

Alternativas consideradas:
- A) Mantener reglas distribuidas en multiples documentos sin baseline unico.
- B) Refactor visual total inmediato antes de documentar baseline.

Motivo de eleccion:
- Reducir ambiguedad ahora, sin bloquear avance funcional ni exigir migracion masiva.

Consecuencias:
- Mayor consistencia en nuevas implementaciones.
- Base clara para futuras specs de refactor incremental.

---

## 15) Continuidad recomendada

Proximo paso natural:
- Crear SPEC-COOPERADORA-003 de normalizacion progresiva de vistas legacy (unificar DataTables y quitar inicializaciones duplicadas), sin romper flujos estables.
