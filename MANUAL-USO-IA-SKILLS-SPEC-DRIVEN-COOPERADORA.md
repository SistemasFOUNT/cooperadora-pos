# MANUAL DE USO

## IA + SKILLS + SPEC-DRIVEN EN COOPERADORA

Fecha: 22-05-2026  
Estado: operativo

---

## 1. Proposito de este manual

Este documento explica como trabajar el proyecto Cooperadora con:

- varios agentes de IA,
- Skills reutilizables,
- especificaciones formales (Spec-Driven Development),
- enfoque de cero regresiones en flujos estables.

Objetivo principal:

- acelerar implementaciones sin perder control de calidad,
- mantener consistencia funcional entre modulos,
- proteger lo que ya funciona.

---

## 2. Archivos clave del flujo

### Plantillas base

- PLANTILLA-SKILLS.md
- PLANTILLA-SPEC-DRIVEN.md

### Documento base del sistema

- SPEC-COOPERADORA-000-SDD-INICIAL-SISTEMA.md ← SDD baseline: stack, entornos, estándares visuales, restricciones

### Archivos aplicados al caso real

- SKILL-COOPERADORA-CIERRE-COBROS-UNIFICADOS.md
- SPEC-COOPERADORA-001-CIERRE-BRECHA-COBROS-POSTGRADO.md

### Implementacion de ejemplo creada

- resources/views/postgrado/cobros/_template-cobro.blade.php
- resources/views/postgrado/cobros/maestrias.blade.php
- resources/views/postgrado/cobros/doctorados.blade.php
- resources/views/postgrado/cobros/especialidades.blade.php
- resources/views/postgrado/cobros/diplomaturas.blade.php
- resources/views/postgrado/cobros/cursos.blade.php

---

## 3. Principios operativos obligatorios

1. No romper lo estable.
2. Cambios pequenos y trazables.
3. Una spec aprobada antes de codificar.
4. Pruebas y anti-regresion antes de cerrar.
5. Si se toca un flujo unificado, validar impacto transversal.

---

## 4. Flujo recomendado de trabajo

### Paso 1: Definir la spec

Usar PLANTILLA-SPEC-DRIVEN.md y completar:

- contexto,
- alcance,
- criterios de aceptacion,
- plan de pruebas,
- DoD.

Resultado esperado:

- spec en estado approved.

### Paso 2: Instanciar la Skill de ejecucion

Usar PLANTILLA-SKILLS.md y convertirla en una Skill operativa concreta para esa spec.

Resultado esperado:

- protocolo paso a paso,
- guardrails claros,
- checklist de salida.

### Paso 3: Ejecutar por roles de agente

Rol Explorador:

- confirma mapa real de archivos, rutas y riesgos.

Rol Implementador:

- aplica cambio minimo funcional exactamente segun spec.

Rol QA/Reviewer:

- valida criterios de aceptacion,
- valida anti-regresion,
- define go/no-go.

### Paso 4: Cerrar con evidencia

No se cierra task sin:

- pruebas,
- lista de archivos tocados,
- riesgos remanentes,
- estado final de DoD.

---

## 5. Ejemplo aplicado: cobros Postgrado

Se detecto una brecha:

- rutas de cobro existentes en Postgrado,
- vistas de cobro faltantes.

Se aplico cierre inicial con:

- plantilla compartida de cobro,
- cinco vistas por programa,
- reuso del modal de pago unificado.

Alcance de este ejemplo:

- deja el flujo UI funcional para seleccion, carrito y pago en modo demostracion,
- deja preparada la conexion a endpoint real para persistencia.

---

## 6. Como ejecutar una iteracion completa

1. Elegir una brecha concreta (una sola por iteracion).
2. Crear/actualizar spec.
3. Crear/actualizar skill operativa.
4. Implementar cambio minimo.
5. Probar modulo objetivo.
6. Probar modulo estable de referencia (anti-regresion).
7. Documentar y cerrar.

Tiempo recomendado por iteracion:

- 1 a 3 horas para cambios chicos.

---

## 7. Checklist de salida por iteracion

- [ ] Spec aprobada
- [ ] Skill definida
- [ ] Cambios implementados
- [ ] Criterios de aceptacion cumplidos
- [ ] Anti-regresion ejecutada
- [ ] Riesgos abiertos documentados
- [ ] DoD completo

---

## 8. Guia de no regresion (Cooperadora)

Flujos criticos a proteger siempre:

- /box/cobros/productos
- /box/cobros/cuotas
- /box/cobros/bonos
- /box/cobros/odontologia
- /box/cobros/otros

Regla practica:

- si un cambio toca logica de modal, descuentos, metodos de pago o comprobantes, correr anti-regresion de los cinco flujos.

---

## 9. Convenciones de documentacion

Nombre sugerido para specs:

- SPEC-COOPERADORA-XXX-NOMBRE.md

Nombre sugerido para skills:

- SKILL-COOPERADORA-NOMBRE.md

Estructura de estado:

- draft -> approved -> in-progress -> done

---

## 10. Proximo paso sugerido para este proyecto final

Estado actual completado:

1. endpoint de persistencia Postgrado implementado,
2. boton de pago conectado a backend real,
3. ticket PDF operativo desde venta persistida.

Siguiente objetivo recomendado:

1. definir si Postgrado requiere factura formal (local/fiscal) ademas de ticket,
2. agregar pruebas feature de endpoint POST y ticket,
3. ejecutar anti-regresion funcional de los cobros BOX,
4. cerrar release con evidencia QA documentada.

---

## 11. Endpoints y flujo tecnico involucrado

### 11.1 Contexto de enrutado

Las rutas de BOX y Postgrado operan en grupos protegidos por middleware de punto de venta y menu especifico del modulo.

- BOX: middleware punto_venta + box_menu.
- Postgrado: middleware punto_venta + postgrado_menu.

### 11.2 Endpoints de cobro en Postgrado (implementados en esta fase)

Estado actual:

- Existen endpoints GET para vistas por programa.
- Existe endpoint POST real para persistencia de cobros en Postgrado.
- Existe endpoint GET de ticket PDF para comprobante.
- El boton de pago de la plantilla compartida envia AJAX al endpoint POST, registra la venta y abre el ticket.

Mapa de rutas:

1. GET /postgrado/cobros/maestrias
Controlador: PostgradoController
Metodo: cobrosMaestrias
Vista retornada: postgrado.cobros.maestrias

2. GET /postgrado/cobros/doctorados
Controlador: PostgradoController
Metodo: cobrosDoctorados
Vista retornada: postgrado.cobros.doctorados

3. GET /postgrado/cobros/especialidades
Controlador: PostgradoController
Metodo: cobrosEspecialidades
Vista retornada: postgrado.cobros.especialidades

4. GET /postgrado/cobros/diplomaturas
Controlador: PostgradoController
Metodo: cobrosDiplomaturas
Vista retornada: postgrado.cobros.diplomaturas

5. GET /postgrado/cobros/cursos
Controlador: PostgradoController
Metodo: cobrosCursos
Vista retornada: postgrado.cobros.cursos

6. POST /postgrado/procesar-venta
Controlador: PostgradoController
Metodo: procesarVenta
Objetivo: valida payload de cobro, persiste venta en ventas + items_venta y retorna JSON de confirmacion.

7. GET /postgrado/ventas/{sale}/ticket
Controlador: PostgradoController
Metodo: descargarTicket
Objetivo: genera y retorna PDF de ticket desde una venta persistida de Postgrado.

Flujo de ejecucion de estos endpoints:

1. Request GET llega a routes/web.php.
2. Pasa middlewares del grupo postgrado.
3. Invoca metodo correspondiente en PostgradoController.
4. El metodo retorna la vista de cobro del programa.
5. La vista carga la plantilla compartida de cobro.

Flujo de ejecucion del endpoint POST:

1. Frontend envia carrito, metodo de pago, comprobante, totales y datos de cliente.
2. El metodo procesarVenta valida reglas de negocio (efectivo/mixto/totales).
3. Resuelve payment_method_id segun codigo de metodo.
4. Crea registro en ventas con tipo student_fee.
5. Crea items asociados en items_venta.
6. Devuelve respuesta JSON con venta_id y sale_number.
7. Incluye ticket_url para emision inmediata del comprobante.

Flujo de ejecucion del endpoint de ticket:

1. Recibe sale_id por route model binding.
2. Verifica pertenencia al punto de venta Postgrado.
3. Reconstruye carrito desde items_venta.
4. Genera PDF con servicio PDFTicket.
5. Responde PDF inline para impresion/descarga.

### 11.3 Flujo frontend actual de Postgrado (plantilla compartida)

Archivo base:

- resources/views/postgrado/cobros/_template-cobro.blade.php

Funciones y comportamiento:

1. formatearPrecio
Normaliza formato de importes para interfaz.

2. actualizarCarrito
Gestiona alta, baja y cantidades de conceptos seleccionados.

3. actualizarTotalesModal
Calcula subtotal, descuento y total final dentro del modal de pago.

4. Evento de btn-procesar-pago
Valida datos, arma payload y envia POST AJAX a /postgrado/procesar-venta.
Con respuesta exitosa, abre ticket_url en nueva pestana.

Resultado operativo actual:

- La navegacion y UX de cobro quedan funcionando.
- El registro de venta en backend ya funciona para Postgrado.
- La emision de ticket PDF desde venta persistida ya funciona.
- Resta una iteracion para facturacion completa (si se requiere factura local/fiscal formal para Postgrado).

### 11.4 Endpoints de referencia en BOX (backend completo ya operativo)

Estos endpoints se usan como referencia funcional porque ya tienen procesamiento real de cobro.

1. GET /box/cobros/cuotas/buscar
Controlador: BoxController
Metodo: buscarCuotasPorEstudiante
Objetivo: devuelve datos de cuotas en JSON para construir carrito.

2. POST /box/cobros/cuotas/registrar
Controlador: BoxController
Metodo: registrarPagoCuota
Objetivo: valida request, calcula recargos/descuentos, registra pagos y retorna resultado.

3. POST /box/procesar-venta
Controlador: BoxController
Metodo: procesarVenta
Objetivo: normaliza payload y delega al flujo unificado procesarPagoConFactura.

4. POST /box/generar-ticket
Controlador: BoxController
Metodo: generarTicketGeneral
Objetivo: alias para generarTicketPDF con compatibilidad de rutas.

5. POST /box/cobros/productos/ticket-pdf
Controlador: BoxController
Metodo: generarTicketPDF
Objetivo: genera ticket PDF a partir del carrito.

6. POST /box/facturas/procesar-pago-con-factura
Controlador: BoxController
Metodo: procesarPagoConFactura
Objetivo: persistencia de venta y soporte de facturacion en flujo unificado.

Flujo de trabajo backend de referencia (BOX):

1. Frontend envia carrito + datos de pago.
2. Endpoint valida reglas y formato de datos.
3. Se aplican calculos de subtotal, descuentos, intereses y total.
4. Se persiste venta/pago segun metodo de cobro.
5. Se retorna respuesta para ticket/factura y feedback UI.

### 11.5 Flujo objetivo para siguiente iteracion (Postgrado con facturacion completa)

Para cerrar el circuito de punta a punta en Postgrado, el flujo recomendado es:

1. Mantener ticket operativo ya implementado como fallback universal.
2. Implementar factura local/fiscal dedicada para Postgrado (si aplica al proceso administrativo).
3. Mantener descarga/impresion de comprobante al finalizar cobro.
4. Mantener anti-regresion de los 5 cobros BOX antes de cierre.

### 11.6 Trazabilidad rapida (archivo por archivo)

Rutas:

- routes/web.php

Controladores:

- app/Http/Controllers/PostgradoController.php
- app/Http/Controllers/BoxController.php

Vistas de cobro Postgrado:

- resources/views/postgrado/cobros/_template-cobro.blade.php
- resources/views/postgrado/cobros/maestrias.blade.php
- resources/views/postgrado/cobros/doctorados.blade.php
- resources/views/postgrado/cobros/especialidades.blade.php
- resources/views/postgrado/cobros/diplomaturas.blade.php
- resources/views/postgrado/cobros/cursos.blade.php

Componente compartido de pago:

- resources/views/components/payment-modals.blade.php

---

## 12. Resumen ejecutivo

Con este manual y los archivos creados ya tenes:

- un sistema operativo de trabajo con IA,
- plantillas reutilizables,
- un ejemplo real implementado,
- un metodo seguro para llegar al cierre final sin improvisar.

---

## 13. Cierre final implementado (fase actual)

En esta fase se cerraron los tres pendientes operativos detectados:

1. **Auditoria interna admin operativa**
	 - Rutas nuevas:
		 - `GET /admin/auditoria` (`admin.auditoria.index`)
		 - `GET /admin/auditoria/{id}` (`admin.auditoria.show`)
	 - Implementacion:
		 - `AdminController::auditoriaIndex()` con filtros por evento, modelo, usuario, rango de fechas y texto.
		 - `AdminController::auditoriaShow()` con detalle completo de `old_values` y `new_values`.
	 - Vistas nuevas:
		 - `resources/views/admin/auditoria/index.blade.php`
		 - `resources/views/admin/auditoria/show.blade.php`
	 - Acceso desde menu admin:
		 - nuevo item **Auditoria Interna** en `config/admin-menu.php`.

2. **Reportes de ventas con top productos para Postgrado y Odonto**
	 - `PostgradoController::reportes()` y `OdontoController::reportes()` ahora soportan:
		 - filtro por `fecha_desde` y `fecha_hasta`,
		 - totales del periodo (ventas, ingresos, ticket promedio),
		 - top productos/conceptos vendidos (cantidad y monto total) desde `items_venta`.
	 - Vistas implementadas:
		 - `resources/views/postgrado/reportes/index.blade.php`
		 - `resources/views/odonto/reportes/index.blade.php`
	 - Vistas de subreportes de Postgrado creadas para evitar brechas de navegacion:
		 - `postgrado.reportes.estudiantes`
		 - `postgrado.reportes.recaudacion`
		 - `postgrado.reportes.matriculas`
		 - `postgrado.reportes.inscripciones`
		 - `postgrado.reportes.certificados`
		 - `postgrado.reportes.pagos`
		 - `postgrado.reportes.titulos`

3. **Egresos reales en libros/ingresos-egresos de Postgrado y Odonto**
	 - Se reemplazo el `TODO` de egresos en cero por consulta real a `pagos_proveedores` (`PagoProveedor`) filtrando por punto de venta y estado `registrado`.
	 - Metodos actualizados:
		 - `PostgradoController::adminIngresosEgresos()`
		 - `PostgradoController::adminLibroCaja()`
		 - `OdontoController::adminIngresosEgresos()`
		 - `OdontoController::adminLibroCaja()`
	 - Resultado:
		 - los egresos dejan de ser valores hardcodeados en cero,
		 - el saldo del periodo pasa a calcularse con `ingresos - egresos` reales.

### Validacion tecnica ejecutada

- `php -l` exitoso en:
	- `app/Http/Controllers/AdminController.php`
	- `app/Http/Controllers/PostgradoController.php`
	- `app/Http/Controllers/OdontoController.php`
	- `routes/web.php`
- `php artisan route:list` confirma rutas nuevas de auditoria y reportes.
