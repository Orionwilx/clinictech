<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; background: #fff; }

    .header { background-color: #0D9488; color: #fff; padding: 14px 20px; }
    .header table { width: 100%; border-collapse: collapse; }
    .header td { vertical-align: middle; }
    .header .logo { width: 70px; }
    .header .logo img { max-height: 50px; max-width: 70px; }
    .header .client-name { font-size: 13px; font-weight: bold; padding-left: 10px; }
    .header .client-sub { font-size: 9px; opacity: 0.85; padding-left: 10px; }
    .header .ot-title { text-align: right; font-size: 16px; font-weight: bold; letter-spacing: 1px; }
    .header .ot-code { text-align: right; font-size: 11px; opacity: 0.85; }

    .body { padding: 16px 20px; }

    .section { margin-bottom: 12px; }
    .section-title {
        background-color: #f0fdfa;
        border-left: 3px solid #0D9488;
        padding: 4px 8px;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        color: #0F766E;
        margin-bottom: 6px;
        letter-spacing: 0.5px;
    }

    .info-table { width: 100%; border-collapse: collapse; }
    .info-table td { padding: 3px 6px; vertical-align: top; }
    .info-table .label { color: #6b7280; width: 35%; font-size: 9px; }
    .info-table .value { color: #111827; font-size: 10px; }

    .two-col table { width: 100%; border-collapse: collapse; }
    .two-col td { width: 50%; vertical-align: top; padding-right: 12px; }
    .two-col td:last-child { padding-right: 0; }

    .checklist { margin-top: 2px; }
    .checklist-item { display: inline-block; width: 48%; padding: 2px 0; font-size: 9.5px; color: #1f2937; }
    .check-icon { color: #0D9488; font-weight: bold; margin-right: 4px; }

    .text-block {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 8px;
        font-size: 10px;
        color: #1f2937;
        min-height: 36px;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    .text-block.empty { color: #9ca3af; font-style: italic; }

    .signatures { margin-top: 30px; }
    .sig-table { width: 100%; border-collapse: collapse; }
    .sig-cell { width: 50%; padding: 0 20px; text-align: center; }
    .sig-line { border-top: 1px solid #374151; margin-top: 40px; padding-top: 4px; }
    .sig-label { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
    .sig-name { font-size: 10px; font-weight: bold; color: #111827; margin-top: 2px; }

    .footer { margin-top: 16px; padding-top: 8px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 8px; color: #9ca3af; }

    .badge {
        display: inline-block;
        padding: 1px 6px;
        border-radius: 10px;
        font-size: 9px;
        font-weight: bold;
    }
    .badge-preventive { background: #dcfce7; color: #166534; }
    .badge-corrective { background: #fee2e2; color: #991b1b; }
    .badge-open { background: #f3f4f6; color: #374151; }
    .badge-in_progress { background: #fef3c7; color: #92400e; }
    .badge-completed { background: #d1fae5; color: #065f46; }
    .badge-closed { background: #ccfbf1; color: #134e4a; }
</style>
</head>
<body>

{{-- HEADER --}}
<div class="header">
    <table>
        <tr>
            <td class="logo">
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo">
                @endif
            </td>
            <td>
                <div class="client-name">{{ $workOrder->client->name }}</div>
                @if ($workOrder->client->nit)
                    <div class="client-sub">NIT: {{ $workOrder->client->nit }}</div>
                @endif
                @if ($workOrder->client->city)
                    <div class="client-sub">{{ $workOrder->client->city }}{{ $workOrder->client->country ? ', '.$workOrder->client->country : '' }}</div>
                @endif
            </td>
            <td style="text-align:right; white-space:nowrap;">
                <div class="ot-title">ORDEN DE TRABAJO</div>
                <div class="ot-code">{{ $workOrder->code }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="body">

    {{-- DATOS GENERALES --}}
    <div class="section">
        <div class="section-title">Datos de la orden</div>
        <div class="two-col">
            <table>
                <tr>
                    <td>
                        <table class="info-table">
                            <tr>
                                <td class="label">Tipo</td>
                                <td class="value">{{ $workOrder->typeLabel() }}</td>
                            </tr>
                            <tr>
                                <td class="label">Estado</td>
                                <td class="value">{{ $workOrder->statusLabel() }}</td>
                            </tr>
                            <tr>
                                <td class="label">Prioridad</td>
                                <td class="value">{{ $workOrder->priorityLabel() }}</td>
                            </tr>
                            <tr>
                                <td class="label">Fecha programada</td>
                                <td class="value">{{ optional($workOrder->scheduled_at)->format('d/m/Y H:i') ?: '—' }}</td>
                            </tr>
                            @if ($workOrder->completed_at)
                            <tr>
                                <td class="label">Fecha ejecución</td>
                                <td class="value">{{ $workOrder->completed_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endif
                        </table>
                    </td>
                    <td>
                        <table class="info-table">
                            <tr>
                                <td class="label">Técnico responsable</td>
                                <td class="value">{{ optional($workOrder->technician)->name ?: '—' }}</td>
                            </tr>
                            @if ($workOrder->technician?->specialty)
                            <tr>
                                <td class="label">Especialidad</td>
                                <td class="value">{{ $workOrder->technician->specialty }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="label">Asunto</td>
                                <td class="value">{{ $workOrder->title }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- DATOS DEL EQUIPO --}}
    @if ($workOrder->equipment)
    <div class="section">
        <div class="section-title">Equipo intervenido</div>
        <div class="two-col">
            <table>
                <tr>
                    <td>
                        <table class="info-table">
                            <tr>
                                <td class="label">Equipo</td>
                                <td class="value">{{ $workOrder->equipment->name }}</td>
                            </tr>
                            <tr>
                                <td class="label">Marca</td>
                                <td class="value">{{ optional($workOrder->equipment->brand)->name ?: '—' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Modelo</td>
                                <td class="value">{{ optional($workOrder->equipment->model)->name ?: '—' }}</td>
                            </tr>
                            <tr>
                                <td class="label">N.º serie</td>
                                <td class="value">{{ $workOrder->equipment->serial_number ?: '—' }}</td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table class="info-table">
                            <tr>
                                <td class="label">Área</td>
                                <td class="value">{{ optional($workOrder->equipment->area)->name ?: '—' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Ubicación / Sede</td>
                                <td class="value">{{ $workOrder->equipment->location ?: '—' }}</td>
                            </tr>
                            @if ($workOrder->equipment->risk_class)
                            <tr>
                                <td class="label">Clasificación INVIMA</td>
                                <td class="value">{{ $workOrder->equipment->riskClassLabel() }}</td>
                            </tr>
                            @endif
                            @if ($workOrder->equipment->invima_registry)
                            <tr>
                                <td class="label">Registro INVIMA</td>
                                <td class="value">{{ $workOrder->equipment->invima_registry }}</td>
                            </tr>
                            @endif
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    {{-- DESCRIPCIÓN --}}
    @if ($workOrder->description)
    <div class="section">
        <div class="section-title">Descripción / Motivo</div>
        <div class="text-block">{{ $workOrder->description }}</div>
    </div>
    @endif

    {{-- CHECKLIST DE MANTENIMIENTO --}}
    @if ($workOrder->maintenance_tasks || $workOrder->accessories_checked)
    <div class="section">
        <div class="section-title">Checklist de mantenimiento</div>
        <div class="two-col">
            <table>
                <tr>
                    <td>
                        <div style="font-size:9px; font-weight:bold; color:#374151; margin-bottom:4px;">Subtareas ejecutadas</div>
                        @if ($workOrder->maintenance_tasks)
                            <div class="checklist">
                                @foreach ($workOrder->maintenance_tasks as $key)
                                    <div class="checklist-item">
                                        <span class="check-icon">&#10003;</span>{{ \App\Models\Equipment::MAINTENANCE_TASKS[$key] ?? $key }}
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span style="color:#9ca3af; font-size:9px;">Ninguna registrada</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-size:9px; font-weight:bold; color:#374151; margin-bottom:4px;">Accesorios revisados</div>
                        @if ($workOrder->accessories_checked)
                            <div class="checklist">
                                @foreach ($workOrder->accessories_checked as $key)
                                    <div class="checklist-item">
                                        <span class="check-icon">&#10003;</span>{{ \App\Models\Equipment::ACCESSORIES[$key] ?? $key }}
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span style="color:#9ca3af; font-size:9px;">Ninguno registrado</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    {{-- DIAGNÓSTICO --}}
    <div class="section">
        <div class="section-title">Diagnóstico</div>
        @if ($workOrder->diagnosis)
            <div class="text-block">{{ $workOrder->diagnosis }}</div>
        @else
            <div class="text-block empty">Sin diagnóstico registrado</div>
        @endif
    </div>

    {{-- ACTIVIDADES REALIZADAS --}}
    <div class="section">
        <div class="section-title">Actividades realizadas</div>
        @if ($workOrder->work_performed)
            <div class="text-block">{{ $workOrder->work_performed }}</div>
        @else
            <div class="text-block empty">Sin actividades registradas</div>
        @endif
    </div>

    {{-- OBSERVACIONES ADICIONALES --}}
    @if ($workOrder->additional_observations)
    <div class="section">
        <div class="section-title">Observaciones adicionales</div>
        <div class="text-block">{{ $workOrder->additional_observations }}</div>
    </div>
    @endif

    {{-- FIRMAS --}}
    <div class="signatures">
        <table class="sig-table">
            <tr>
                <td class="sig-cell">
                    <div class="sig-line">
                        <div class="sig-name">{{ optional($workOrder->technician)->name ?: '____________________' }}</div>
                        <div class="sig-label">Técnico responsable</div>
                        @if ($workOrder->technician?->document)
                            <div style="font-size:8.5px; color:#6b7280;">Doc: {{ $workOrder->technician->document }}</div>
                        @endif
                    </div>
                </td>
                <td class="sig-cell">
                    <div class="sig-line">
                        <div class="sig-name">____________________</div>
                        <div class="sig-label">Ingeniero / Administrador</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- PIE DE PÁGINA --}}
    <div class="footer">
        Documento generado el {{ now()->format('d/m/Y H:i') }} &mdash; {{ config('app.name') }}
    </div>

</div>
</body>
</html>
