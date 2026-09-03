# Módulo: Reportes

- **Ref. plan**: §7 de `project.md`
- **Estado**: ✅ implementado
- **Depende de**: Clientes, Equipos, Órdenes de trabajo, Técnicos

## Modelo
Sin modelo propio — capa de consulta sobre tablas existentes (`work_orders`, `equipment`, `technicians`, `clients`).

## Reportes disponibles

| Ruta | Descripción | Exporta PDF |
|------|-------------|-------------|
| `admin/reports` | Índice con KPIs globales y tarjetas de acceso | — |
| `admin/reports/work-orders` | Historial completo de OT | Sí |
| `admin/reports/maintenance` | OT de tipo preventivo/correctivo | Sí |
| `admin/reports/technicians` | Carga de trabajo por técnico | — |
| `admin/reports/equipment` | Equipos ordenados por nº de intervenciones | — |

## Filtros por reporte

### Órdenes de trabajo
- Cliente · Técnico · Tipo (todos los tipos) · Estado · Fecha desde · Fecha hasta

### Mantenimientos
- Cliente · Técnico · Subtipo (preventivo / correctivo) · Estado · Fecha desde · Fecha hasta

### Por técnico
- Estado del técnico (activo / inactivo)
- Métricas calculadas: total OT, activas, completadas, % completado

### Por equipo
- Cliente · Estado del equipo
- Métrica calculada: total de intervenciones (OT asociadas)

## Permisos (spatie)
- Módulo `reports` → genera `view/create/update/delete reports`.
- Solo `view reports` es relevante; los demás existen por convención del seeder.
- Quién accede: `admin` (completo). `tecnico`/`cliente`: pendiente de definir en Fase 8.

## Notas de UI
- Vistas en `resources/views/admin/reports/`.
- PDF generado con `barryvdh/laravel-dompdf`, orientación landscape A4.
- Botón "Exportar PDF" pasa los mismos query-string del filtro activo al endpoint `/pdf`.
- Reporte de técnicos incluye barra de progreso visual (% completado) con Alpine/CSS puro.
