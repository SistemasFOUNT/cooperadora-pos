# PLAYBOOK OPERATIVO MCP - COOPERADORA

Fecha: 22-05-2026  
Estado: operativo

---

## 1. Proposito

Estandarizar el uso de MCP (Model Context Protocol) en el trabajo diario para:

- reducir retrabajo,
- mejorar decisiones con contexto real,
- prevenir regresiones por contexto incompleto,
- mantener el flujo skill-driven + spec-driven.

---

## 2. Principios no negociables

1. MCP complementa el protocolo actual; no lo reemplaza.
2. Ningun cambio se implementa sin spec aprobada.
3. Si falla MCP, se aplica fallback manual sin frenar el trabajo.
4. Si se toca un flujo unificado (cobros), la validacion es transversal.
5. Ninguna automatizacion destructiva sin confirmacion humana.

---

## 3. Herramientas MCP iniciales permitidas

Uso recomendado para la etapa actual:

1. Git/PR: estado, diffs, ramas, trazabilidad.
2. Base de datos (solo lectura): tablas, vistas, esquemas, validaciones de estructura.
3. Issues/tickets: contexto funcional, criterio de negocio, alcance.

No incorporar nuevas herramientas MCP sin validacion quincenal de valor.

---

## 4. Flujo diario estandar (por tarea)

### Paso 1. Abrir contexto

- Identificar modulo objetivo y riesgo transversal.
- Leer spec activa (o crearla si no existe).

### Paso 2. Validar contexto con MCP

- Confirmar archivos y rutas impactadas.
- Confirmar dependencias cruzadas (BOX, Postgrado, Odonto si aplica).
- Confirmar estado actual en git (cambios en curso, ramas, PR relacionados).

### Paso 3. Aprobar alcance minimo

- Definir cambio minimo funcional.
- Registrar explicitamente lo que queda fuera de alcance.

### Paso 4. Implementar

- Ejecutar cambio minimo.
- Evitar refactors no solicitados.

### Paso 5. Validar cierre

- Cumplimiento de criterios de aceptacion.
- Pruebas del modulo objetivo.
- Anti-regresion del modulo estable equivalente.
- Riesgos remanentes documentados.

---

## 5. Checklist MCP rapido (obligatorio antes de codificar)

- [ ] Spec en estado approved.
- [ ] Archivos y rutas impactadas confirmadas.
- [ ] Riesgo transversal evaluado.
- [ ] Dependencias externas verificadas.
- [ ] Plan de pruebas definido.

---

## 6. Reglas por tipo de tarea

## A. Bugfix local

Usar MCP para:

- confirmar punto exacto de fallo,
- validar que no hay impacto transversal oculto,
- revisar cambios recientes relacionados.

Salida minima:

- fix puntual,
- test de regresion asociado,
- nota de causa raiz.

## B. Cambio transversal

Usar MCP para:

- mapear todos los modulos afectados,
- identificar middleware/rutas/modelos compartidos,
- verificar impacto en cobros unificados.

Salida minima:

- matriz de impacto,
- validacion cruzada por modulo,
- evidencia de no-regresion.

## C. Reportes y auditoria

Usar MCP para:

- confirmar rutas activas y endpoints reales,
- validar estructura de datos requerida,
- comprobar permisos/roles involucrados.

Salida minima:

- reporte funcional,
- filtros validados,
- trazabilidad de consulta.

---

## 7. Go / No-Go operativo

Go si:

1. Hay spec aprobada.
2. El alcance es minimo y claro.
3. Existe plan de prueba y anti-regresion.

No-Go si:

1. Falta spec o esta ambigua.
2. No se pudo validar contexto minimo.
3. Hay riesgo transversal sin analisis.

---

## 8. Fallback cuando MCP no esta disponible

1. Continuar con protocolo manual actual.
2. Registrar que validaciones MCP no pudieron correrse.
3. Aumentar rigor de lectura directa de codigo y pruebas.
4. Cerrar con advertencia de riesgo remanente.

---

## 9. Metricas de adopcion (seguimiento quincenal)

1. Tiempo promedio de analisis tecnico por tarea.
2. Reaperturas por contexto incompleto.
3. Regresiones detectadas post-cierre.
4. Porcentaje de tareas con checklist MCP completo.

Objetivo inicial:

- reducir 20% el tiempo de analisis,
- reducir 30% reaperturas por contexto,
- mantener cero regresiones en flujos criticos de cobro.

---

## 10. Plantilla corta para iniciar cada tarea

Usar este bloque al arrancar:

1. Modulo objetivo:
2. Spec activa:
3. Riesgo transversal:
4. Validacion MCP ejecutada:
5. Cambio minimo definido:
6. Pruebas planificadas:
7. Criterio de cierre:

---

## 11. Relacion con documentos oficiales

- docs/ia/_ACTIVACION.md
- docs/ia/PROTOCOLO_ANALISIS_SISTEMA_COOPERADORA.md
- docs/ia/MANUAL-USO-IA-SKILLS-SPEC-DRIVEN-COOPERADORA.md
- docs/ia/templates/PLANTILLA-SKILLS.md
- docs/ia/templates/PLANTILLA-SPEC-DRIVEN.md
- docs/ia/specs/
- docs/ia/skills/
