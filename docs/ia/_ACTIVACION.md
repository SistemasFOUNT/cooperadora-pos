# GUIA DE ACTIVACION - PROTOCOLO SKILL-DRIVEN + SPEC-DRIVEN
## Sistema Cooperadora

---

## 1. Comando corto recomendado

```text
Aplicar protocolo skill-driven + spec-driven de Cooperadora para [modulo/feature], iniciando por SKILL-01 y SKILL-02 antes de editar codigo.
```

---

## 2. Comando completo recomendado

```text
PROTOCOLO COOPERADORA OBLIGATORIO:
1) Leer docs/ia/PROTOCOLO_ANALISIS_SISTEMA_COOPERADORA.md
2) Crear o actualizar spec en docs/ia/templates/PLANTILLA-SPEC-DRIVEN.md (estado draft/approved)
3) Ejecutar SKILL-01 Reconocimiento
4) Ejecutar SKILL-02 Aislamiento
5) Implementar con SKILL-03 (y SKILL-04 si hay cobros)
6) Validar con SKILL-05 (tests + anti-regresion)
7) Cerrar con SKILL-06 y riesgos remanentes
```

---

## 3. Activadores por tipo de trabajo

## Nuevo desarrollo
```text
Aplicar protocolo Cooperadora para nueva funcionalidad en [BOX/POSTGRADO/ODONTO], con spec aprobada y validacion de aislamiento obligatoria.
```

## Bugfix
```text
Aplicar protocolo Cooperadora para bug en [modulo], con cambio minimo, test de regresion y cierre con trazabilidad.
```

## Cobros
```text
Aplicar SKILL-04 de cobro unificado en [modulo], validando paridad con los modulos equivalentes y no-regresion.
```

## Cambio transversal
```text
Cambio transversal en [auth/middleware/rutas/modelos]: ejecutar SKILL-01 y SKILL-02 sobre BOX/Postgrado/Odonto antes de implementar.
```

---

## 4. Checklist rapido de activacion

- [ ] Protocolo leido
- [ ] Spec creada/actualizada
- [ ] Modulo principal definido
- [ ] Riesgo de aislamiento evaluado
- [ ] Riesgo de cobro unificado evaluado
- [ ] Criterios de aceptacion definidos

---

## 5. Criterio de seguridad minima

No avanzar a implementacion si falta alguno de estos puntos:
- Aislamiento no validado.
- Criterios de aceptacion ambiguos.
- Anti-regresion no definida.

---

## 6. Archivos oficiales de referencia

- docs/ia/PROTOCOLO_ANALISIS_SISTEMA_COOPERADORA.md
- docs/ia/PLAYBOOK-OPERATIVO-MCP-COOPERADORA.md
- docs/ia/templates/PLANTILLA-SPEC-DRIVEN.md
- docs/ia/templates/PLANTILLA-SKILLS.md
- docs/ia/specs/ ← todas las specs del proyecto
- docs/ia/skills/ ← todas las skills del proyecto
- ESTANDARES-PROYECTO.md
