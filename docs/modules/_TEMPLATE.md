# Módulo: {Nombre} ({Recurso} en singular PascalCase)

- **Ref. plan**: §{n} de `project.md`
- **Estado**: borrador | en progreso | ✅ implementado
- **Depende de**: {otros módulos, ej. Clientes}

## Modelo
`{Recurso}` → tabla `{plural_snake}`.

## Campos
| Campo | Tipo | Reglas | Notas |
|-------|------|--------|-------|
| name | string | required, max:255 | |
| ... | | | |

## Enums
- `status`: `valor_a`, `valor_b`, ... (default: `valor_a`)

## Relaciones
- `belongsTo({Padre})` — ...
- `hasMany({Hijo})` — ...

## Reglas de negocio
- ...

## Permisos (spatie)
- Módulo en `RolePermissionSeeder`: `{plural_snake}` → genera `view/create/update/delete {plural_snake}`.
- Quién accede: admin (todo), tecnico (...), cliente (...).

## Notas de UI
- Vistas en `resources/views/admin/{plural_snake}/` siguiendo `DESIGN.md`.
- Campos especiales del formulario: ...

## Semilla (git, si aplica)
`git show HEAD:modules/{...}/CONTEXT.md`
