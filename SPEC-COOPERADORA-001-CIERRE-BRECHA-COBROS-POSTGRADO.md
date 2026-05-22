# SPEC-COOPERADORA-001
## Cierre de brecha de cobros Postgrado con no regresion en BOX

---

## 1) Encabezado

ID Spec: SPEC-COOPERADORA-001  
Titulo: Cierre de brecha de cobros Postgrado con protocolo unificado  
Fecha: 22-05-2026  
Autor: Equipo Cooperadora (asistido por IA)  
Estado: draft  
Modulos impactados: Postgrado, BOX (validacion anti-regresion)

## 2) Contexto y problema

Situacion actual verificada:
- Existen rutas de cobro en Postgrado:
  - `postgrado.cobros.maestrias`
  - `postgrado.cobros.doctorados`
  - `postgrado.cobros.especialidades`
  - `postgrado.cobros.diplomaturas`
  - `postgrado.cobros.cursos`
- El controlador `PostgradoController` retorna vistas `postgrado.cobros.*`.
- En el arbol actual no se encontraron archivos `resources/views/postgrado/cobros/*.blade.php`.

Problema concreto:
- Riesgo de error por vistas faltantes al acceder a rutas de cobro de Postgrado.
- Riesgo de divergencia de flujo respecto al protocolo unificado ya estabilizado en BOX.

Por que importa:
- Impacta continuidad operativa del punto de venta.
- Afecta objetivo de cierre final sin regresiones.
- Contradice la regla de homogeneidad operativa de cobros.

## 3) Alcance

### Incluye
- Crear vistas de cobro base para Postgrado en `resources/views/postgrado/cobros/`.
- Reusar estructura de cobro unificado (carrito + modal de pago + validaciones base).
- Confirmar consistencia visual y funcional con protocolo vigente.
- Ejecutar anti-regresion funcional en los 5 cobros BOX.

### No incluye
- Rediseño completo de UI de Postgrado.
- Refactor profundo de controladores fuera de cobros.
- Cambios contables de asiento automatico.

## 4) Requisitos funcionales

- RF-01: Cada ruta de `postgrado.cobros.*` debe renderizar sin error 500.
- RF-02: Cada vista de cobro Postgrado debe incluir modal de pago unificado.
- RF-03: Debe existir flujo minimo operable: seleccionar item, agregar, totalizar, proceder pago.
- RF-04: Los metodos de pago y tipos de comprobante deben mantener el contrato estandar.
- RF-05: Los cobros BOX deben mantener comportamiento actual sin regresion.

## 5) Requisitos no funcionales

- RNF-01 (confiabilidad): cero errores de vista faltante en rutas objetivo.
- RNF-02 (mantenibilidad): reutilizar componentes comunes en lugar de duplicar logica.
- RNF-03 (trazabilidad): registrar archivos modificados y evidencia de validacion.
- RNF-04 (compatibilidad): mantener funcionamiento en entorno Windows dev y Ubuntu prod.

## 6) Reglas de negocio

- RB-01: El proceso de cobro debe ser exacto y consistente entre modulos homologables.
- RB-02: Ninguna nueva funcionalidad puede romper una funcionalidad estable previa.
- RB-03: Cualquier ajuste en flujo de pago debe considerarse transversal para cobros equivalentes.

## 7) Contrato tecnico

Entradas:
- rutas activas de cobro,
- controlador y vistas implicadas,
- componente de modal de pago existente.

Salidas:
- vistas postgrado.cobros creadas y accesibles,
- validacion funcional documentada,
- reporte de no regresion BOX.

Validaciones:
- resolucion de vistas,
- render de modal,
- interaccion basica del carrito,
- envios de formulario o endpoint asociado.

Errores esperados y manejo:
- vista inexistente: crear archivo faltante segun patron.
- datos faltantes: fallback controlado y mensaje de error amigable.

## 8) Criterios de aceptacion (Given/When/Then)

1. Given usuario autenticado en Postgrado, When navega a `/postgrado/cobros/maestrias`, Then la vista renderiza correctamente sin excepcion.
2. Given una vista de cobro Postgrado, When abre el proceso de pago, Then se presenta el modal unificado con metodos y comprobantes estandar.
3. Given flujo de cobro BOX existente, When se repite smoke test en 5 pantallas de cobro, Then no hay cambios de comportamiento no esperados.

## 9) Casos borde y anti-regresion

Casos borde:
- Ruta de cobro con dataset vacio (sin items configurados).
- Usuario sin permisos del punto de venta intentando acceso.

Anti-regresion critica:
- `box.cobros.productos`
- `box.cobros.cuotas`
- `box.cobros.bonos`
- `box.cobros.odontologia`
- `box.cobros.otros`

Integraciones sensibles:
- Facturacion desde flujo de cobro.
- Generacion de ticket y post de venta.

## 10) Plan de implementacion por fases

### Fase A - Exploracion y mapa de brechas
1. Verificar rutas de Postgrado y vistas faltantes.
2. Confirmar componentes de cobro reutilizables desde BOX.
3. Definir minimo set de datos para render estable.

### Fase B - Implementacion minima funcional
1. Crear vistas:
   - `resources/views/postgrado/cobros/maestrias.blade.php`
   - `resources/views/postgrado/cobros/doctorados.blade.php`
   - `resources/views/postgrado/cobros/especialidades.blade.php`
   - `resources/views/postgrado/cobros/diplomaturas.blade.php`
   - `resources/views/postgrado/cobros/cursos.blade.php`
2. Incluir componente de modal de pago unificado donde corresponda.
3. Ajustar JS minimo de carrito/totales si el contexto lo requiere.

### Fase C - QA y anti-regresion
1. Probar render y navegacion de las 5 rutas de cobro Postgrado.
2. Ejecutar smoke test de cobros BOX.
3. Registrar evidencia y riesgos remanentes.

## 11) Plan de pruebas

### Unitarias/Feature sugeridas
- Test F1: cada ruta `postgrado.cobros.*` responde 200 para usuario autorizado.
- Test F2: acceso no autorizado devuelve 403 o redireccion valida.

### Manuales
- M1: flujo completo de agregar item y abrir modal en cada cobro Postgrado.
- M2: validar metodos de pago y comprobantes disponibles.
- M3: repetir flujo base en 5 cobros BOX para verificar no regresion.

## 12) Riesgos y mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigacion |
|---|---|---|---|
| Vistas nuevas no alineadas con flujo unificado | Media | Alta | Reusar componentes y checklist de protocolo |
| Regresion en BOX por cambios compartidos | Media | Alta | Smoke test obligatorio en 5 cobros BOX |
| Datos reales insuficientes para pruebas | Media | Media | Dataset de prueba controlado + validacion manual |

## 13) Definition of Done

- [ ] 5 rutas de cobro Postgrado renderizan sin errores
- [ ] Modal de pago unificado presente y operativo
- [ ] Pruebas de flujo basico completas
- [ ] Anti-regresion BOX validada
- [ ] Cambios y evidencia documentados

## 14) ADR breve

Decision:
- Implementar cierre incremental por Postgrado como siguiente paso de cierre final.

Alternativas consideradas:
- A) Postergar Postgrado y liberar solo BOX.
- B) Implementar todo Postgrado + Odonto en una unica iteracion grande.

Razon de eleccion:
- Menor riesgo operativo y mayor control al avanzar por brechas concretas.

Consecuencias:
- Cierre ordenado y con evidencia de estabilidad por fases.

## 15) Handoff multiagente sugerido

Agente 1 - Exploracion
```
Objetivo: confirmar mapa exacto de rutas, vistas faltantes y dependencias.
Salida: informe de brechas + prioridad de implementacion.
```

Agente 2 - Implementacion
```
Objetivo: crear vistas de cobro Postgrado minimas y coherentes con protocolo.
Salida: commit funcional acotado + notas tecnicas.
```

Agente 3 - QA/Review
```
Objetivo: validar criterios de aceptacion y no regresion BOX.
Salida: evidencia de pruebas + recomendacion go/no-go.
```

## 16) Checklist de arranque inmediato

1. Aprobar esta spec (estado approved).
2. Ejecutar Fase A y validar alcance final.
3. Implementar Fase B en una rama dedicada.
4. Ejecutar Fase C con evidencia.
5. Cerrar con DoD completo.
