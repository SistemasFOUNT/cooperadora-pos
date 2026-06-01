# SPEC-COOPERADORA-003
## Endurecimiento de aislamiento por modulo (acceso y anti-regresion)

---

## 1) Encabezado

ID Spec: SPEC-COOPERADORA-003  
Titulo: Endurecimiento de aislamiento por modulo en rutas y middleware  
Fecha: 01-06-2026  
Autor: Equipo Cooperadora (asistido por IA)  
Estado: done  
Modulo principal: TRANSVERSAL  
Modulos potencialmente impactados: BOX, Postgrado, Odonto, Middleware, Tests Feature  
Relacion con cobro unificado: no

---

## 2) Contexto y problema

Situacion actual:
- El aislamiento por punto de venta existe, pero esta aplicado de forma asimetrica entre modulos.
- BOX tiene bloqueo explicito por rol en middleware de menu.
- Postgrado/Odonto dependen de controles parciales en controlador o middleware no bloqueante.

Problema concreto:
- El middleware `punto_venta` solo fuerza restriccion de modulo cuando recibe parametro explicito, y hoy las rutas de modulo se usan sin ese parametro.
- Esto deja riesgo de acceso cruzado si una accion no valida nuevamente el punto de venta.

Evidencia actual:
- `PuntoVentaMiddleware` verifica `$puntoVentaRequerido` solo si se provee.
- `BoxMenuMiddleware` aborta 403 por rol; `PostgradoMenuMiddleware` y `OdontoMenuMiddleware` no hacen un bloqueo equivalente.
- Existen tests de acceso para BOX, pero no equivalentes para Postgrado/Odonto.

Por que importa para operacion diaria:
- La regla no negociable del proyecto exige aislamiento estricto entre BOX, Postgrado y Odonto.
- Un acceso cruzado compromete seguridad operativa y consistencia de caja por punto de venta.

---

## 3) Alcance

### Incluye
- Endurecer enforcement de acceso por modulo para rutas `postgrado/*` y `odonto/*` con criterio equivalente a BOX.
- Estandarizar validacion de rol/modulo en middleware de menu o middleware dedicado.
- Agregar tests Feature de acceso para Postgrado y Odonto (caso permitido y denegado).
- Mantener sin cambios funcionales los flujos de cobro y reportes existentes.

### No incluye
- Refactor masivo de controladores legacy no vinculados a control de acceso.
- Cambios visuales de menu o rediseno de navegacion.
- Cambios de logica contable o de facturacion.

---

## 4) Requisitos funcionales

- RF-01: Un usuario no admin de Postgrado no puede acceder a rutas de Odonto ni BOX.
- RF-02: Un usuario no admin de Odonto no puede acceder a rutas de Postgrado ni BOX.
- RF-03: Un usuario no admin de BOX no puede acceder a Postgrado ni Odonto.
- RF-04: Un usuario del modulo correcto mantiene acceso normal a su dashboard y rutas propias.
- RF-05: Usuario admin mantiene acceso transversal segun politica vigente.

---

## 5) Requisitos no funcionales

- RNF-01 (seguridad/roles/middleware): autorizacion centralizada y consistente por modulo.
- RNF-02 (aislamiento): sin fuga de datos ni acceso funcional cruzado entre puntos de venta.
- RNF-03 (cambios minimos): diff pequeno, focalizado en middleware/rutas/tests.
- RNF-04 (observabilidad): logs sin datos sensibles y sin ruido innecesario en middleware de menu.
- RNF-05 (compatibilidad Windows/Linux): pruebas ejecutables en ambos entornos Laravel.

---

## 6) Reglas de negocio

- RB-01: BOX, Postgrado y Odonto son dominios separados y no intercambiables para usuarios no admin.
- RB-02: Ningun cambio de seguridad debe romper flujo estable del modulo autorizado.
- RB-03: La autorizacion debe ser uniforme por politica, no por validaciones dispersas por accion.
- RB-04: Esta spec no modifica el protocolo de cobro unificado.

---

## 7) Contrato tecnico

Entradas:
- Request autenticada con `user.role` y `user.punto_venta_id`.
- Rutas prefijadas de modulo (`/box/*`, `/postgrado/*`, `/odonto/*`).

Salidas:
- HTTP 403 para accesos cruzados no autorizados.
- HTTP 200 para accesos permitidos.

Validaciones:
- Correspondencia rol/modulo (usuario_box, usuario_postgrado, usuario_odonto).
- Excepcion admin.

Errores esperados:
- 403 por modulo no permitido.

Manejo de errores:
- Mensajes de autorizacion claros y consistentes.
- Sin leakage de detalles internos.

---

## 8) Criterios de aceptacion (Given/When/Then)

1. Given un usuario `usuario_postgrado`, When accede a `/odonto/dashboard`, Then recibe 403.
2. Given un usuario `usuario_odonto`, When accede a `/postgrado/dashboard`, Then recibe 403.
3. Given un usuario `usuario_postgrado`, When accede a `/postgrado/dashboard`, Then recibe 200.
4. Given un usuario `usuario_odonto`, When accede a `/odonto/dashboard`, Then recibe 200.
5. Given un usuario admin autenticado, When accede a dashboards de los tres modulos, Then el acceso se mantiene permitido segun rutas existentes.
6. Given la suite de acceso por modulos, When se ejecuta, Then queda en verde sin romper pruebas existentes de BOX.

---

## 9) Skills a ejecutar

- [x] SKILL-01 Reconocimiento de modulo
- [x] SKILL-02 Validacion de aislamiento
- [x] SKILL-03 Cambios minimos y seguros
- [ ] SKILL-04 Cobro unificado (no aplica)
- [x] SKILL-05 Pruebas y no-regresion
- [x] SKILL-06 Cierre y trazabilidad

Resultado esperado por skill:
- SKILL-01: inventario de rutas/middleware/controladores y zonas de riesgo.
- SKILL-02: riesgos priorizados y estrategia de mitigacion.
- SKILL-03: hardening de middleware con minimo impacto.
- SKILL-05: tests de acceso por modulo completos (positivo/negativo).
- SKILL-06: cierre con evidencia de pruebas y riesgos remanentes.

---

## 10) Casos borde y anti-regresion

- Caso borde 1: usuario con rol valido pero `punto_venta_id` inconsistente en datos seed.
- Caso borde 2: rutas alias/admin que invocan controladores de otro modulo con permisos admin.
- Flujo estable que no se puede romper: acceso de usuario correcto a su dashboard y operaciones propias.
- Integraciones sensibles: middlewares de menu (`box_menu`, `postgrado_menu`, `odonto_menu`) y `punto_venta`.

Anti-regresion minima:
- Mantener `tests/Feature/Box/BoxAccessTest.php` en verde.
- Agregar pruebas equivalentes para Postgrado y Odonto.
- Verificar que no cambia comportamiento de rutas admin.

---

## 11) Plan de implementacion

1. Aplicar guard uniforme en middleware para Postgrado/Odonto (equivalente al criterio de BOX).
2. Eliminar/ajustar logs de debug en middleware productivo no necesarios.
3. Crear tests Feature de acceso para Postgrado y Odonto (allow/deny).
4. Ejecutar pruebas objetivo + anti-regresion de BOX.
5. Documentar resultado y riesgos remanentes.

---

## 12) Plan de pruebas

### Unitarias
- U1: no aplica (cambio orientado a middleware/routing + feature).

### Feature/Integracion
- F1: usuario_postgrado denegado en dashboard odonto.
- F2: usuario_odonto denegado en dashboard postgrado.
- F3: usuario_postgrado permitido en dashboard postgrado.
- F4: usuario_odonto permitido en dashboard odonto.
- F5: anti-regresion BOX: tests existentes de acceso siguen en verde.

### Manuales (si aplica)
- M1: login con usuario de cada modulo y validacion de acceso cruzado.
- M2: login admin y comprobacion de acceso a supervision por modulo.

### Estado de ejecucion actual
- Implementacion aplicada en middlewares de Postgrado/Odonto.
- Tests Feature creados para Postgrado/Odonto.
- Ajuste de aislamiento en tests de acceso usando transacciones para evitar inestabilidad de `migrate:fresh` en entorno PostgreSQL actual.
- Corrida objetivo ejecutada en verde: 18 pruebas aprobadas, 0 fallidas (BOX + Postgrado + Odonto access tests).

---

## 13) Riesgos y mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigacion |
|---|---|---|---|
| Bloquear de mas rutas legitimas | Media | Alta | Limitar cambio a prefijos de modulo y cubrir con tests de acceso permitido |
| Romper comportamiento de menu en Postgrado/Odonto | Media | Media | Mantener carga de menu y sumar solo guard de autorizacion |
| Regresion en BOX | Baja | Alta | Ejecutar anti-regresion existente de BOX antes de cerrar |

---

## 14) Definition of Done (DoD)

- [x] Spec aprobada antes de implementar
- [x] Requisitos funcionales implementados (pendiente validacion automatizada)
- [x] Criterios de aceptacion validados
- [x] Pruebas relevantes en verde
- [x] Anti-regresion cubierta
- [x] Sin ruptura de aislamiento BOX/Postgrado/Odonto
- [x] Sin cambios en cobro unificado
- [x] Documentacion y trazabilidad actualizadas

---

## 15) Registro de decisiones (ADR breve)

Decision:
- Endurecer autorizacion de modulo en middleware con estrategia equivalente entre BOX, Postgrado y Odonto.

Alternativas consideradas:
- A) Mantener validaciones solo en controladores por accion.
- B) Reestructurar todas las rutas con middleware parametrico nuevo en esta misma iteracion.

Motivo de eleccion:
- Aislamiento consistente con menor riesgo y menor diff.

Consecuencias tecnicas:
- Seguridad mas uniforme y mantenible.
- Menor dependencia de chequeos ad-hoc en cada accion.

Consecuencias operativas:
- Menor probabilidad de acceso cruzado por error humano o cambio futuro.

---

## 16) Handoff operativo

Implementacion:
- Archivos previstos: middlewares de Postgrado/Odonto y nuevos tests Feature de acceso.
- Cambios clave: guard de autorizacion + pruebas de no-regresion.
- Supuestos: roles `usuario_box`, `usuario_postgrado`, `usuario_odonto` vigentes.

QA/Review:
- Pruebas a ejecutar: tests de acceso modulo + BoxAccessTest.
- Resultado actual: suite objetivo en verde (18/18).
- Riesgo remanente: rutas genericas autenticadas fuera de prefijos modulares (para spec separada).

Continuidad:
- Proximo paso natural: pasar spec a in-progress e implementar hardening + tests en el mismo ciclo.
- Deuda tecnica separada: aislamiento de grupos CRUD genericos (`/estudiantes`, `/carreras`, `/productos`).
