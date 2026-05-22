# SKILL ESPECIFICA - CIERRE COBROS UNIFICADOS COOPERADORA

## 1) Metadata

Nombre Skill: CierreCobrosUnificadosCooperadora  
Version: 1.0.0  
Owner: Equipo Cooperadora  
Ultima actualizacion: 22-05-2026  
Estado: active

---

## 2) Problema que resuelve

Garantiza que el flujo de cobro sea consistente y no regresivo entre los modulos activos y los modulos en expansion, usando un protocolo de ejecucion controlado.

Dolor que evita:
- Cambios parciales que rompen un modulo estable.
- Diferencias de UX en cobros por tipo de concepto.
- Rutas funcionales con vistas faltantes en produccion.

Impacto esperado:
- Menos errores operativos de caja.
- Menor costo de soporte por inconsistencias.
- Cierre mas seguro del proyecto final.

## 3) Cuando usar esta Skill

- Cuando se agregue o ajuste un flujo de cobro.
- Cuando aparezcan nuevas rutas de cobro por programa/modulo.
- Antes de un release de estabilizacion o pre-produccion.

## 4) Cuando NO usarla

- Para cambios puramente visuales sin logica de cobro.
- Para tareas de infraestructura sin impacto funcional en ventas.
- Para experimentos exploratorios no aprobados por spec.

Alternativa en esos casos:
- Usar skill de UI o de deployment segun corresponda.

## 5) Inputs requeridos

Input obligatorio A: spec aprobada del cambio.  
Input obligatorio B: rutas, controlador y vistas afectadas.  
Input obligatorio C: criterio de no regresion validado por negocio.

Input opcional:
- evidencia de errores previos,
- checklist de QA manual,
- prioridad de release.

Dependencias del proyecto:
- routes/web.php
- app/Http/Controllers/BoxController.php
- app/Http/Controllers/PostgradoController.php
- resources/views/box/cobros/*.blade.php
- resources/views/components/payment-modals.blade.php

## 6) Salida esperada

Artefacto principal:
- cambio funcional documentado + pruebas de no regresion.

Formato de salida:
- resumen tecnico,
- archivos tocados,
- validaciones ejecutadas,
- riesgos abiertos.

Criterio de listo:
- rutas de cobro operativas,
- flujo unificado respetado,
- sin ruptura en BOX,
- evidencia de pruebas.

## 7) Protocolo de ejecucion

1. Levantar mapa de rutas de cobro activas y pendientes.
2. Identificar brechas entre modulo referencia (BOX) y modulo objetivo.
3. Implementar el minimo cambio funcional para cerrar la brecha.
4. Reusar componente de modal unificado cuando aplique.
5. Validar flujo completo: seleccion -> carrito -> pago -> confirmacion.
6. Ejecutar anti-regresion en los 5 cobros BOX.
7. Documentar resultado y riesgos.

## 8) Guardrails tecnicos obligatorios

- Regla de oro: si cambia cobro de un modulo, validar impacto en todos los cobros homologables.
- No modificar contratos de rutas sin trazabilidad.
- Mantener cambios pequenos y aislados por iteracion.
- Prohibido cerrar task con pruebas pendientes en flujos criticos.

## 9) Checklist de calidad

- [ ] Spec aprobada antes de codificar
- [ ] Rutas mapeadas y verificadas
- [ ] Vistas/controladores consistentes
- [ ] Modal de pago unificado aplicado correctamente
- [ ] Pruebas funcionales ejecutadas
- [ ] Anti-regresion BOX completada
- [ ] Riesgos remanentes informados

## 10) Plantilla de handoff multiagente

Agente Explorador
```
Objetivo: inventariar rutas, vistas y brechas reales.
Entregar: tabla de brechas + impacto + prioridad.
```

Agente Implementador
```
Objetivo: cerrar brecha especificada con cambios minimos.
Entregar: archivos modificados + decision tecnica.
```

Agente QA/Reviewer
```
Objetivo: validar criterio de aceptacion y no regresion.
Entregar: evidencia de pruebas + go/no-go.
```

## 11) Definicion operativa de exito

- Las rutas de cobro definidas en spec responden sin error.
- El operador de caja tiene el mismo esquema mental de cobro.
- No se detectan regresiones en:
  - /box/cobros/productos
  - /box/cobros/cuotas
  - /box/cobros/bonos
  - /box/cobros/odontologia
  - /box/cobros/otros

## 12) Anti-patrones que esta Skill evita

- Resolver errores de vista con hotfixes aislados sin spec.
- Crear variantes de modal de pago por modulo.
- Cerrar tareas sin verificar flujo completo de cobro.
