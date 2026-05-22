# PLANTILLA BASE - SKILLS (IA)

## Objetivo
Definir una Skill reutilizable para ejecutar una tarea de forma consistente, con entradas claras, salida verificable y limites de uso.

---

## 1) Metadata de la Skill

```
Nombre Skill:
Version:
Owner:
Ultima actualizacion:
Estado: draft | active | deprecated
```

## 2) Problema que resuelve

```
Resumen corto:
Dolor principal que evita:
Impacto esperado (tiempo, calidad, errores):
```

## 3) Cuándo usarla

- Condicion 1:
- Condicion 2:
- Condicion 3:

## 4) Cuándo NO usarla

- Limite 1:
- Limite 2:
- Caso alternativo recomendado:

## 5) Inputs requeridos

```
Input obligatorio A:
Input obligatorio B:
Input opcional C:
Dependencias (archivos, rutas, servicios):
```

## 6) Salida esperada

```
Artefacto principal:
Formato de salida:
Criterio de listo:
```

## 7) Protocolo de ejecucion (paso a paso)

1. Validar precondiciones.
2. Recolectar contexto minimo necesario.
3. Ejecutar accion principal.
4. Validar resultado tecnico.
5. Validar que no haya regresiones.
6. Entregar salida con trazabilidad.

## 8) Guardrails tecnicos

- No romper funcionalidad estable ya validada.
- No cambiar APIs publicas sin documentar impacto.
- Mantener cambios minimos y acotados al objetivo.
- Si un riesgo no puede resolverse, elevar bloqueo explicito.

## 9) Checklist de calidad de la Skill

- [ ] Inputs completos
- [ ] Precondiciones verificadas
- [ ] Resultado funcional validado
- [ ] Regresion revisada
- [ ] Trazabilidad documentada
- [ ] Riesgos abiertos reportados

## 10) Plantilla de respuesta estandar

```
Resumen:
Cambios aplicados:
Validaciones realizadas:
Riesgos/limitaciones:
Siguientes pasos:
```

## 11) Ejemplo de instancia (rellenable)

```
Nombre Skill: RefactorSeguroModuloCobros
Problema: Ajustar logica de cobro sin romper flujo unificado.
Input obligatorio A: modulo objetivo
Input obligatorio B: criterio funcional del cambio
Salida esperada: codigo + validacion de no regresion
```

## 12) Notas para trabajo multiagente

- Agente Explorador: inventario de codigo y riesgos.
- Agente Implementador: cambio puntual segun spec.
- Agente QA: pruebas funcionales y regresion.
- Agente Reviewer: revision final de riesgo y consistencia.

Regla practica: cada agente recibe una tarea cerrada, con objetivo unico y criterio de exito medible.
