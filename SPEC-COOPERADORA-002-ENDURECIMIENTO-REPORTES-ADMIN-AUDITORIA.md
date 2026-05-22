# SPEC-COOPERADORA-002
## Endurecimiento de reportes admin por punto de venta + anti-regresion de auditoria

---

## 1) Encabezado

ID Spec: SPEC-COOPERADORA-002  
Titulo: Endurecimiento de reportes admin por punto de venta y anti-regresion de auditoria  
Fecha: 22-05-2026  
Autor: Equipo Cooperadora (asistido por IA)  
Estado: done  
Modulo principal: TRANSVERSAL (Admin + Postgrado + Odonto)  
Modulos potencialmente impactados: Admin, Postgrado, Odonto  
Relacion con cobro unificado: no

---

## 2) Contexto y problema

Situacion actual:
- Se incorporaron pruebas Feature para cobertura de auditoria admin y reportes por punto de venta.
- En endpoints de reportes de Postgrado/Odonto se detectaron consultas legacy con tablas/joins no consistentes con el esquema vigente (ej.: sale_product, students).

Problema concreto:
- Los tests iniciales de reportes fallaban por errores de base de datos (500) no relacionados al objetivo de aislamiento por punto de venta.
- Una asercion textual de auditoria era fragil contra HTML completo.

Evidencia actual:
- Falla previa por tabla inexistente en reportes legacy.
- Ajuste de asercion en auditoria para evitar falso negativo.
- Resultado final validado: tests objetivo en verde.

Por que importa para operacion diaria:
- Garantiza trazabilidad y control en admin sin degradar estabilidad.
- Permite validar aislamiento por punto de venta con endpoints estables.

---

## 3) Alcance

### Incluye
- Endurecer pruebas Feature de auditoria admin.
- Validar libro caja admin para Postgrado y Odonto por periodo y punto de venta.
- Asegurar datos de prueba compatibles con integridad referencial real.

### No incluye
- Refactor completo de endpoints legacy `postgrado.reportes` y `odonto.reportes`.
- Cambios funcionales en UI de reportes legacy.

---

## 4) Requisitos funcionales

- RF-01: Auditoria admin debe filtrar y mostrar resultados consistentes sin aserciones fragiles.
- RF-02: Libro caja admin de Postgrado debe reflejar ingresos del punto de venta correcto en el periodo filtrado.
- RF-03: Libro caja admin de Odonto debe reflejar ingresos del punto de venta correcto en el periodo filtrado.
- RF-04: Las pruebas deben proteger aislamiento: ventas de otro punto de venta no deben contaminar resultados.

---

## 5) Requisitos no funcionales

- RNF-01 (seguridad/roles/middleware): acceso con usuario admin autenticado.
- RNF-02 (aislamiento entre puntos de venta): verificacion explicita en pruebas.
- RNF-03 (mantenibilidad): pruebas enfocadas en endpoints estables y representativos.
- RNF-04 (observabilidad/logs): fallas con mensajes accionables.
- RNF-05 (compatibilidad Windows/Linux): ejecucion via PHPUnit/Laravel test runner en entorno actual.

---

## 6) Reglas de negocio

- RB-01: Aislamiento operativo por punto de venta es obligatorio.
- RB-02: Ningun nuevo control debe romper funcionalidades estables previas.
- RB-03: La auditoria administrativa debe mantener trazabilidad confiable.

---

## 7) Contrato tecnico

Entradas:
- Rutas admin de auditoria y libro caja por punto de venta.
- Datos de prueba de ventas por periodo.

Salidas:
- Responses 200 en endpoints objetivo.
- Datos de vista con totales correctos por punto de venta.

Validaciones:
- Conteo y totales en `movimientos_caja.resumen_periodo`.
- Filtro efectivo de auditoria con paginacion esperada.

Errores esperados:
- FKs no satisfechas en fixtures.
- Consultas legacy con tablas inexistentes (fuera de alcance de esta spec).

Manejo de errores:
- Ajustar fixtures al esquema real (incluyendo sucursales cuando aplique).
- Redirigir cobertura a endpoints estables cuando el objetivo es aislamiento/reporting base.

---

## 8) Criterios de aceptacion (Given/When/Then)

1. Given un admin autenticado y registros de auditoria con eventos mixtos, When filtra por evento y termino de busqueda, Then se obtiene respuesta OK y el set filtrado esperado.
2. Given ventas en Postgrado y Odonto dentro del mismo periodo, When se consulta libro caja admin de Postgrado, Then solo contabiliza ingresos del punto de venta Postgrado.
3. Given ventas en Postgrado y Odonto dentro del mismo periodo, When se consulta libro caja admin de Odonto, Then solo contabiliza ingresos del punto de venta Odonto.
4. Given la suite de pruebas de esta spec, When se ejecuta, Then todas las pruebas quedan en verde.

---

## 9) Skills a ejecutar

- [x] SKILL-01 Reconocimiento de modulo
- [x] SKILL-02 Validacion de aislamiento
- [x] SKILL-03 Cambios minimos y seguros
- [ ] SKILL-04 Cobro unificado (no aplica)
- [x] SKILL-05 Pruebas y no-regresion
- [x] SKILL-06 Cierre y trazabilidad

Resultado por skill:
- SKILL-01: rutas/controladores/tests mapeados.
- SKILL-02: aislamiento validado en reportes admin por PV.
- SKILL-03: ajustes puntuales en tests y fixtures.
- SKILL-05: corrida de pruebas objetivo con resultado exitoso.
- SKILL-06: spec cerrada con evidencia y deuda separada.

---

## 10) Casos borde y anti-regresion

Casos borde:
- Filtro de auditoria con texto coincidente en UI (evitar aserciones textuales fragiles).
- Integridad referencial de ventas en PostgreSQL con FK activa.

Flujo estable que no se puede romper:
- Libro caja admin por punto de venta (Postgrado/Odonto).

Integraciones sensibles:
- Endpoints legacy de reportes con consultas no alineadas al esquema actual (deuda tecnica documentada).

---

## 11) Plan de implementacion

1. Relevar fallas y stack trace de tests objetivo.
2. Corregir aserciones fragiles en auditoria.
3. Ajustar fixtures para respetar FKs reales.
4. Reorientar cobertura de reportes hacia endpoints admin estables.
5. Reejecutar suite objetivo hasta verde.

---

## 12) Plan de pruebas

### Feature/Integracion
- F1: `AuditoriaFeatureTest` (filtro + detalle).
- F2: `PuntosVentaReportesFeatureTest` sobre `admin.libro-caja.postgrado`.
- F3: `PuntosVentaReportesFeatureTest` sobre `admin.libro-caja.odonto`.

### Resultado observado
- 4 pruebas pasadas, 0 fallidas (corrida objetivo).

---

## 13) Riesgos y mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigacion |
|---|---|---|---|
| Reintroducir aserciones fragiles en vistas HTML | Media | Media | Priorizar assertViewHas y validaciones estructurales |
| Fixtures incompletos por integridad referencial | Media | Alta | Construir fixtures alineados al esquema real (FKs) |
| Confundir deuda legacy con fallo de implementacion actual | Alta | Media | Separar alcance funcional de deuda tecnica en spec |

---

## 14) Definition of Done (DoD)

- [x] Spec aplicada al alcance definido.
- [x] Criterios de aceptacion cumplidos.
- [x] Pruebas objetivo en verde.
- [x] Aislamiento por punto de venta validado.
- [x] Riesgos remanentes documentados.
- [x] Deuda legacy separada del cierre funcional.

---

## 15) Registro de decisiones (ADR breve)

Decision:
- Validar reporte por punto de venta a traves de endpoints admin estables en lugar de forzar endpoints legacy rotos.

Alternativas consideradas:
- A) Forzar cobertura sobre endpoints legacy y corregir controladores en esta misma iteracion.
- B) Posponer pruebas de reportes hasta refactor de endpoints legacy.

Motivo de eleccion:
- Mantener foco en objetivo de aislamiento y no-regresion, con entrega verificable y riesgo acotado.

Consecuencias:
- Cobertura real sobre flujos estables hoy productivos.
- Queda deuda tecnica explicitada para posterior spec de refactor legacy.

---

## 16) Handoff operativo

Implementacion:
- Archivos tocados: `tests/Feature/Admin/AuditoriaFeatureTest.php`, `tests/Feature/Reportes/PuntosVentaReportesFeatureTest.php`.
- Cambios clave: aserciones robustas, fixtures alineados a FKs, enfoque en endpoints admin libro caja.

QA/Review:
- Pruebas ejecutadas: suite objetivo de ambos archivos.
- Resultado: verde completo.
- Riesgo remanente: endpoints `postgrado.reportes` y `odonto.reportes` con deuda legacy fuera de alcance.

Continuidad:
- Proximo paso natural: spec dedicada para refactor de queries legacy en controladores de reportes.
