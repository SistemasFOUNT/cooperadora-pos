# PROTOCOLO SKILL-DRIVEN + SPEC-DRIVEN
## Sistema Cooperadora - BOX / Postgrado / Odonto

---

## 1. Objetivo
Convertir el protocolo operativo de Cooperadora en un marco unico de trabajo basado en:
- Skills reutilizables por tipo de tarea.
- Especificaciones (specs) con estados y criterios de cierre.
- Proteccion obligatoria de lo estable antes de agregar cambios.

Este protocolo aplica para desarrollo, mantenimiento, debugging y mejoras evolutivas.

---

## 2. Principios no negociables

### 2.1 Aislamiento por punto de venta
- BOX, Postgrado y Odonto son dominios separados.
- Ningun cambio debe mezclar datos, rutas, permisos o comportamiento entre modulos.
- Toda implementacion debe demostrar filtrado correcto por punto de venta.

### 2.2 Cobro unificado obligatorio
- Todo flujo de cobro debe respetar el estandar unificado del proyecto.
- Si cambia el cobro en un modulo, debe cambiar igual en todos los modulos equivalentes.

### 2.3 No romper lo que ya funciona
- Primero se protege el flujo estable (encapsulamiento/aislamiento).
- Luego se implementa lo nuevo.
- Toda spec debe incluir anti-regresion explicita.

### 2.4 Compatibilidad de entorno
- Desarrollo: Windows + PostgreSQL.
- Produccion: Linux Ubuntu + PostgreSQL.
- Las decisiones tecnicas deben ser compatibles en ambos entornos.

---

## 3. Mapa de skills del proyecto

## SKILL-01: Reconocimiento de modulo
### Objetivo
Levantar contexto del modulo afectado antes de tocar codigo.

### Entrada
- Modulo objetivo: BOX | POSTGRADO | ODONTO | TRANSVERSAL.
- Funcionalidad o bug.

### Verificaciones obligatorias
- Rutas del modulo.
- Controladores y servicios asociados.
- Vistas impactadas.
- Middleware activo.
- Modelos y relaciones que usan punto de venta.

### Salida esperada
- Inventario de archivos afectados.
- Dependencias directas e indirectas.
- Riesgos de aislamiento.

## SKILL-02: Validacion de aislamiento
### Objetivo
Asegurar que el cambio no cruce fronteras entre puntos de venta.

### Checklist
- Filtros por punto de venta presentes en consultas.
- Middleware correcto en rutas sensibles.
- Sin fuga de datos entre modulos.
- Sin reutilizacion incorrecta de vistas/controladores entre dominios.

### Salida esperada
- Riesgos detectados + mitigacion por riesgo.

## SKILL-03: Cambios minimos y seguros
### Objetivo
Aplicar el menor cambio posible para cumplir la spec.

### Reglas
- No mezclar refactor grande con fix funcional.
- No alterar APIs o contratos sin justificacion en spec.
- Mantener consistencia de nombres, filtros y convenciones del repo.

### Salida esperada
- Diff pequeno, focalizado y justificable.

## SKILL-04: Cobro unificado
### Objetivo
Validar que cualquier cambio de cobro respete protocolo comun.

### Verificaciones
- Modal de pago comun.
- Metodos de pago estandar.
- Tipos de comprobante estandar.
- Funciones JS obligatorias.
- Consistencia de UX entre modulos equivalentes.

### Salida esperada
- Evidencia de paridad funcional entre modulos afectados.

## SKILL-05: Pruebas y no-regresion
### Objetivo
Validar comportamiento esperado y proteger flujos criticos.

### Cobertura minima
- Feature test del flujo principal modificado.
- Caso de anti-regresion del comportamiento estable.
- Verificacion de aislamiento (caso positivo y negativo cuando aplique).

### Salida esperada
- Resultado de pruebas + riesgos remanentes.

## SKILL-06: Cierre y trazabilidad
### Objetivo
Cerrar la tarea con informacion util para continuidad.

### Incluye
- Resumen de archivos modificados.
- Criterios cumplidos/no cumplidos.
- Deuda tecnica detectada.
- Proximo paso natural.

---

## 4. Ciclo spec-driven obligatorio

## Estado de spec
- draft: problema definido, pendiente de validacion.
- approved: alcance y criterios aceptados.
- in-progress: implementacion en curso.
- blocked: impedimento tecnico/funcional.
- done: DoD completo y pruebas validas.

## Flujo de trabajo
1. Crear spec (draft) usando plantilla del proyecto.
2. Definir alcance: incluye/no incluye.
3. Enumerar requisitos funcionales, no funcionales y reglas de negocio.
4. Definir criterios Given/When/Then.
5. Ejecutar SKILL-01 y SKILL-02 antes de editar.
6. Implementar con SKILL-03 y, si aplica, SKILL-04.
7. Validar con SKILL-05.
8. Cerrar con SKILL-06 y actualizar documentacion.

---

## 5. Puertas de calidad (gates)

## Gate A - Antes de codificar
- [ ] Modulo y alcance definidos.
- [ ] Riesgos de aislamiento identificados.
- [ ] Criterios de aceptacion claros.
- [ ] Anti-regresion definida.

## Gate B - Antes de merge/entrega
- [ ] Criterios de aceptacion cumplidos.
- [ ] Pruebas relevantes en verde.
- [ ] Sin ruptura de flujos estables.
- [ ] Sin violar cobro unificado (si aplica).

## Gate C - Cierre funcional
- [ ] DoD completo.
- [ ] Riesgos remanentes documentados.
- [ ] Deuda tecnica separada de funcionalidad nueva.

---

## 6. Matriz de decision rapida

| Tipo de solicitud | Skill principal | Skill secundaria | Evidencia minima |
|---|---|---|---|
| Nuevo desarrollo en un modulo | SKILL-01 | SKILL-02, SKILL-05 | Feature test + checklist aislamiento |
| Cambio en cobros | SKILL-04 | SKILL-02, SKILL-05 | Paridad entre modulos + test |
| Bug puntual | SKILL-03 | SKILL-05 | Test de regresion del bug |
| Cambio transversal (auth/middleware/rutas) | SKILL-02 | SKILL-01, SKILL-05 | Verificacion en BOX/Postgrado/Odonto |
| Refactor tecnico | SKILL-03 | SKILL-05, SKILL-06 | Sin cambio de comportamiento documentado |

---

## 7. Prompts operativos recomendados

## Activar modo spec-driven
"Aplicar protocolo skill-driven + spec-driven de Cooperadora para [modulo/funcionalidad], iniciando por reconocimiento y validacion de aislamiento."

## Activar flujo de cobro unificado
"Aplicar SKILL-04 en [modulo], verificando paridad de cobro con los modulos equivalentes y anti-regresion."

## Activar analisis seguro antes de cambios
"Ejecutar SKILL-01 y SKILL-02 para [modulo/feature], documentar hallazgos y riesgos antes de editar codigo."

---

## 8. Definicion de terminado (DoD)
Una tarea se considera terminada solo si:
- [ ] Cumple requisitos de la spec aprobada.
- [ ] Cumple criterios de aceptacion.
- [ ] Incluye proteccion de anti-regresion.
- [ ] Mantiene aislamiento entre puntos de venta.
- [ ] Mantiene protocolo de cobro unificado (si aplica).
- [ ] Tiene trazabilidad de decisiones y riesgos.

---

## 9. Notas de implementacion para este repositorio
- Mantener coherencia con ESTANDARES-PROYECTO.md.
- Priorizar cambios pequenos y verificables.
- Si aparece deuda legacy, documentarla como deuda y no mezclarla con la entrega funcional.
- Nunca asumir que un endpoint legacy representa el contrato vigente sin validar su esquema real.

---

## 10. Versionado del protocolo
- Version: 2.0
- Fecha: 22/05/2026
- Estado: Activo
- Reemplaza: protocolo de analisis por fases manuales
