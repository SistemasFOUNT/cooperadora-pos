# PLANTILLA SPEC-DRIVEN - COOPERADORA

## 1) Encabezado

```text
ID Spec:
Titulo:
Fecha:
Autor:
Estado: draft | approved | in-progress | blocked | done
Modulo principal: BOX | POSTGRADO | ODONTO | TRANSVERSAL
Modulos potencialmente impactados:
Relacion con cobro unificado: si | no
```

---

## 2) Contexto y problema

```text
Situacion actual:
Problema concreto:
Evidencia actual (error, flujo roto o necesidad):
Por que importa para operacion diaria:
```

---

## 3) Alcance

### Incluye
- Item 1
- Item 2

### No incluye
- Item A
- Item B

---

## 4) Requisitos funcionales

- RF-01:
- RF-02:
- RF-03:

---

## 5) Requisitos no funcionales

- RNF-01 (seguridad/roles/middleware):
- RNF-02 (aislamiento entre puntos de venta):
- RNF-03 (rendimiento):
- RNF-04 (observabilidad/logs):
- RNF-05 (compatibilidad Windows/Linux):

---

## 6) Reglas de negocio

- RB-01:
- RB-02:
- RB-03:
- RB-04 (si aplica cobros): mantener protocolo unificado en todos los modulos equivalentes.

---

## 7) Contrato tecnico

```text
Entradas:
Salidas:
Validaciones:
Errores esperados:
Manejo de errores:
```

---

## 8) Criterios de aceptacion (Given/When/Then)

1. Given ... When ... Then ...
2. Given ... When ... Then ...
3. Given ... When ... Then ...

---

## 9) Skills a ejecutar

- [ ] SKILL-01 Reconocimiento de modulo
- [ ] SKILL-02 Validacion de aislamiento
- [ ] SKILL-03 Cambios minimos y seguros
- [ ] SKILL-04 Cobro unificado (si aplica)
- [ ] SKILL-05 Pruebas y no-regresion
- [ ] SKILL-06 Cierre y trazabilidad

### Resultado esperado por skill
```text
SKILL-01:
SKILL-02:
SKILL-03:
SKILL-04:
SKILL-05:
SKILL-06:
```

---

## 10) Casos borde y anti-regresion

- Caso borde 1:
- Caso borde 2:
- Flujo estable que no se puede romper:
- Integraciones sensibles:

---

## 11) Plan de implementacion

1. Ejecutar reconocimiento y riesgos (SKILL-01 y SKILL-02).
2. Aplicar cambio minimo funcional (SKILL-03).
3. Asegurar paridad de cobro (SKILL-04) si corresponde.
4. Ejecutar pruebas objetivo y anti-regresion (SKILL-05).
5. Cerrar con evidencia y riesgos remanentes (SKILL-06).

---

## 12) Plan de pruebas

### Unitarias
- Test U1:
- Test U2:

### Feature/Integracion
- Test F1:
- Test F2:
- Test F3 (aislamiento):

### Manuales (si aplica)
- Escenario M1:
- Escenario M2:

---

## 13) Riesgos y mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigacion |
|---|---|---|---|
| Riesgo 1 | Media | Alta | Accion 1 |
| Riesgo 2 | Baja | Alta | Accion 2 |

---

## 14) Definition of Done (DoD)

- [ ] Spec aprobada antes de implementar
- [ ] Requisitos funcionales cumplidos
- [ ] Criterios de aceptacion validados
- [ ] Pruebas relevantes en verde
- [ ] Anti-regresion cubierta
- [ ] Sin ruptura de aislamiento BOX/Postgrado/Odonto
- [ ] Sin ruptura de cobro unificado (si aplica)
- [ ] Documentacion y trazabilidad actualizadas

---

## 15) Registro de decisiones (ADR breve)

```text
Decision:
Alternativas consideradas:
Motivo de eleccion:
Consecuencias tecnicas:
Consecuencias operativas:
```

---

## 16) Handoff operativo

### Implementacion
```text
Archivos tocados:
Cambios clave:
Supuestos:
```

### QA/Review
```text
Pruebas ejecutadas:
Resultado:
Riesgo remanente:
```

### Continuidad
```text
Proximo paso natural:
Deuda tecnica separada:
```
