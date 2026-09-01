# Specs de módulo

Un archivo por módulo del dominio (`clientes.md`, `equipos.md`, `ordenes.md`…). Cada uno es el **spec ejecutable** de ese módulo: campos, enums, relaciones, reglas y permisos.

## Para qué sirven (optimización de coste/tiempo)
Cuando se construye un módulo, este spec es la **entrada directa de `/nuevo-recurso`**: en vez de razonar el modelo desde cero o preguntar campo por campo, se lee un archivo corto y se genera el CRUD. Precomputo que se paga una vez y se reutiliza.

## Reglas
1. **Just-in-time**: se crea/finaliza el spec *justo antes* de construir ese módulo, no años antes. Así no acumulamos specs especulativos que envejecen.
2. **`project.md` manda**: el spec debe reconciliarse con el alcance de negocio en `project.md`. Si hay conflicto, gana `project.md`.
3. **Al terminar el módulo**, el spec se marca `implementado` y se mantiene al día con el código (misma regla que `CLAUDE.md`).
4. Copia [`_TEMPLATE.md`](./_TEMPLATE.md) para empezar uno nuevo.

## Semillas históricas
El primer commit contiene borradores de campos/enums (arquitectura antigua, dominio "clínicas") útiles como punto de partida. Recupéralos con:
```bash
git show HEAD:modules/equipment/CONTEXT.md
git show HEAD:modules/service-orders/CONTEXT.md
git show HEAD:modules/assignments/CONTEXT.md
git show HEAD:modules/clinics/CONTEXT.md
```
Úsalos solo como semilla; reconcilia siempre con `project.md` antes de fijar el spec.
