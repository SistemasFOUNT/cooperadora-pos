# SPEC-COOPERADORA-004
## Encapsulamiento de aislamiento en rutas genericas (estudiantes, carreras, productos)

---

## 1) Encabezado

ID Spec: SPEC-COOPERADORA-004  
Titulo: Encapsulamiento de aislamiento para grupos CRUD genericos autenticados  
Fecha: 01-06-2026  
Autor: Equipo Cooperadora (asistido por IA)  
Estado: done  
Modulo principal: TRANSVERSAL  
Modulos potencialmente impactados: Rutas web, middlewares, EstudianteController, CarreraController, ProductoController, tests Feature  
Relacion con cobro unificado: no

---

## 2) Contexto y problema

Situacion actual:
- Existen grupos CRUD en espanol bajo `auth` para estudiantes, carreras y productos.
- Estos grupos estan definidos como rutas genericas y no declaran aislamiento por modulo en el bloque de rutas.
- Los controladores asociados no muestran validaciones explicitas de rol/punto de venta.

Problema concreto:
- Riesgo de acceso funcional cruzado entre BOX, Postgrado y Odonto en rutas genericas autenticadas.
- Riesgo de regresion futura si se agregan endpoints a esos grupos sin una politica de aislamiento centralizada.

Evidencia actual:
- Rutas genericas en `estudiantes/*`, `carreras/*` y `productos/*` definidas sin middleware modular explicito.
- `EstudianteController`, `CarreraController` y `ProductoController` operan sin checks de aislamiento por rol/punto.

Por que importa para operacion diaria:
- El protocolo operativo define aislamiento estricto por punto de venta como principio no negociable.
- Una sola fuga en estos CRUD puede exponer datos o permitir operaciones fuera del dominio del usuario.

---

## 3) Alcance

### Incluye
- Definir politica de acceso explicita para rutas genericas de estudiantes/carreras/productos.
- Encapsular aislamiento en middleware o estrategia central equivalente (no checks dispersos por accion).
- Ajustar rutas genericas para aplicar la politica elegida de forma uniforme.
- Agregar pruebas Feature de acceso permitido/denegado por rol y acceso admin.

### No incluye
- Refactor funcional de formularios, validaciones de negocio o UI de esos CRUD.
- Cambios en flujos de cobro/facturacion.
- Rediseno de modelo de datos legacy.

---

## 4) Requisitos funcionales

- RF-01: usuario no admin solo accede a recursos genericos permitidos para su dominio operativo.
- RF-02: usuario no autorizado por politica recibe 403 en rutas genericas protegidas.
- RF-03: admin mantiene acceso transversal segun politica vigente.
- RF-04: operaciones CRUD validas en rutas genericas continan funcionando para usuarios autorizados.

---

## 5) Requisitos no funcionales

- RNF-01 (seguridad/roles/middleware): enforcement centralizado, consistente y auditable.
- RNF-02 (aislamiento): sin acceso cruzado no autorizado entre dominios.
- RNF-03 (cambios minimos): tocar solo rutas/middleware/tests necesarios.
- RNF-04 (mantenibilidad): evitar duplicacion de checks en cada metodo de controlador.
- RNF-05 (compatibilidad): ejecucion valida en entorno Windows y Ubuntu.

---

## 6) Reglas de negocio

- RB-01: aislamiento entre BOX/Postgrado/Odonto es obligatorio tambien en rutas genericas.
- RB-02: admin puede supervisar transversalmente.
- RB-03: no se modifica protocolo de cobro unificado.

---

## 7) Contrato tecnico

Entradas:
- Request autenticada con `role` y `punto_venta_id`.
- Rutas `estudiantes/*`, `carreras/*`, `productos/*`.

Salidas:
- HTTP 200 para roles autorizados.
- HTTP 403 para roles no autorizados.

Validaciones:
- Politica por rol definida y aplicada en un punto central.

Errores esperados:
- 403 acceso denegado por politica de aislamiento.

Manejo de errores:
- Mensaje de acceso denegado claro.
- Sin filtracion de detalles internos.

---

## 8) Criterios de aceptacion (Given/When/Then)

1. Given un usuario no admin sin permiso para `estudiantes/*`, When accede a `/estudiantes`, Then recibe 403.
2. Given un usuario no admin sin permiso para `carreras/*`, When accede a `/carreras`, Then recibe 403.
3. Given un usuario no admin sin permiso para `productos/*`, When accede a `/productos`, Then recibe 403.
4. Given un usuario autorizado por politica, When accede al CRUD correspondiente, Then recibe 200 y comportamiento esperado.
5. Given un admin autenticado, When accede a las rutas genericas protegidas, Then mantiene acceso permitido.
6. Given la suite de acceso para rutas genericas y modulares, When se ejecuta, Then queda en verde sin romper aislamiento ya endurecido en SPEC-003.

---

## 9) Skills a ejecutar

- [x] SKILL-01 Reconocimiento de modulo
- [x] SKILL-02 Validacion de aislamiento
- [x] SKILL-03 Cambios minimos y seguros
- [ ] SKILL-04 Cobro unificado (no aplica)
- [x] SKILL-05 Pruebas y no-regresion
- [x] SKILL-06 Cierre y trazabilidad

Resultado esperado por skill:
- SKILL-01: inventario completo de rutas/controladores genericos.
- SKILL-02: riesgos y mitigacion de fuga inter-modulo.
- SKILL-03: encapsulamiento de acceso con diff acotado.
- SKILL-05: pruebas de acceso en verde.
- SKILL-06: cierre con evidencia y riesgos remanentes.

---

## 10) Casos borde y anti-regresion

- Caso borde 1: usuario con rol valido pero datos incompletos en `punto_venta_id`.
- Caso borde 2: rutas API internas de busqueda asociadas a estos CRUD.
- Flujo estable que no se puede romper: accesos ya consolidados en BOX/Postgrado/Odonto (SPEC-003).
- Integraciones sensibles: middlewares `punto_venta`, `box_menu`, `postgrado_menu`, `odonto_menu`.

Anti-regresion minima:
- Re-ejecutar suite de acceso de SPEC-003.
- Ejecutar nueva suite de acceso para rutas genericas.

---

## 11) Plan de implementacion

1. Definir y documentar politica de autorizacion para rutas genericas.
2. Aplicar middleware/guard central a grupos `estudiantes`, `carreras`, `productos`.
3. Ajustar solo lo necesario en rutas/controladores para respetar politica.
4. Crear tests Feature para permitido/denegado/admin por grupo.
5. Ejecutar pruebas objetivo y anti-regresion SPEC-003.
6. Cerrar con trazabilidad y riesgos remanentes.

---

## 12) Plan de pruebas

### Feature/Integracion
- F1: denegacion de acceso a `/estudiantes` para rol no permitido.
- F2: denegacion de acceso a `/carreras` para rol no permitido.
- F3: denegacion de acceso a `/productos` para rol no permitido.
- F4: acceso permitido para rol autorizado segun politica.
- F5: acceso admin permitido.
- F6: anti-regresion de SPEC-003 en verde.

### Manuales (si aplica)
- M1: login por rol y smoke de rutas genericas.
- M2: verificacion de menus/rutas principales sin regresion visual/funcional.

### Estado de ejecucion actual
- Middleware central implementado para encapsular acceso por recurso (`estudiantes`, `carreras`, `productos`).
- Matriz validada por negocio: `productos/*` exclusivo para BOX (Odonto sin acceso a productos).
- Rutas genericas protegidas con middleware parametrico por grupo.
- Suite objetivo ejecutada en verde: 34 pruebas aprobadas, 0 fallidas (SPEC-004 + anti-regresion SPEC-003).
- Validacion incremental posterior al ajuste de negocio: `GenericCrudAccessTest` en verde (16 pruebas aprobadas, 0 fallidas).

---

## 13) Riesgos y mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigacion |
|---|---|---|---|
| Bloquear funcionalidades legitimas por politica demasiado restrictiva | Media | Alta | Definir matriz rol-ruta antes de implementar y validarla con negocio |
| Duplicar controles entre middleware y controlador | Media | Media | Centralizar en middleware y eliminar redundancias innecesarias |
| Regresion en aislamiento ya logrado | Baja | Alta | Re-ejecutar anti-regresion de SPEC-003 en el mismo ciclo |

---

## 14) Definition of Done (DoD)

- [x] Spec aprobada antes de implementar
- [x] Requisitos funcionales cumplidos
- [x] Criterios de aceptacion validados
- [x] Pruebas relevantes en verde
- [x] Anti-regresion cubierta
- [x] Sin ruptura de aislamiento BOX/Postgrado/Odonto
- [x] Sin cambios en cobro unificado
- [x] Documentacion y trazabilidad actualizadas

---

## 15) Registro de decisiones (ADR breve)

Decision:
- Encapsular aislamiento de rutas genericas con enforcement centralizado en vez de checks por metodo.

Alternativas consideradas:
- A) Mantener estado actual y confiar en `auth` + buenas practicas manuales.
- B) Agregar checks puntuales dentro de cada metodo de los tres controladores.

Motivo de eleccion:
- Menor riesgo de omisiones futuras y mejor mantenibilidad.

Consecuencias tecnicas:
- Regla de acceso mas clara y reusable.
- Menor dispersion de logica de seguridad.

Consecuencias operativas:
- Menos incidentes de acceso cruzado y menor costo de soporte.

---

## 16) Handoff operativo

Implementacion:
- Archivos previstos: `routes/web.php`, middleware de acceso generico (nuevo o reutilizado), tests Feature nuevos.
- Cambios clave: politica central de autorizacion para CRUD genericos.
- Supuestos: roles actuales del sistema se mantienen sin cambios.

QA/Review:
- Pruebas a ejecutar: suite nueva de rutas genericas + anti-regresion SPEC-003.
- Resultado: verde completo (34/34).
- Riesgo remanente: definicion de matriz rol-ruta debe validarse con criterio operativo del sistema.

Continuidad:
- Proximo paso natural: pasar esta spec a in-progress y aplicar el encapsulamiento con diff minimo.
