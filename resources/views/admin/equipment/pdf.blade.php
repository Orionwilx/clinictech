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
    .header .doc-title { text-align: right; font-size: 15px; font-weight: bold; letter-spacing: 1px; }
    .header .doc-sub { text-align: right; font-size: 10px; opacity: 0.85; }

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

    .checklist-item { display: inline-block; width: 48%; padding: 2px 0; font-size: 9.5px; color: #1f2937; }
    .check-icon { color: #0D9488; font-weight: bold; margin-right: 4px; }

    .ot-table { width: 100%; border-collapse: collapse; font-size: 9px; }
    .ot-table th { background: #f0fdfa; color: #0F766E; text-transform: uppercase; font-size: 8px; text-align: left; padding: 4px 6px; border-bottom: 1px solid #99f6e4; }
    .ot-table td { padding: 4px 6px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }

    .footer { margin-top: 16px; padding-top: 8px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 8px; color: #9ca3af; }
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
                <div class="client-name">{{ optional($equipment->client)->name }}</div>
                @if (optional($equipment->client)->nit)
                    <div class="client-sub">NIT: {{ $equipment->client->nit }}</div>
                @endif
            </td>
            <td style="text-align:right; white-space:nowrap;">
                <div class="doc-title">HOJA DE VIDA DE EQUIPO</div>
                <div class="doc-sub">{{ $equipment->name }} · Serie {{ $equipment->serial_number }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="body">

    {{-- IDENTIFICACIÓN --}}
    <div class="section">
        <div class="section-title">Identificación del equipo</div>
        <div class="two-col">
            <table>
                <tr>
                    <td>
                        <table class="info-table">
                            <tr><td class="label">Equipo</td><td class="value">{{ $equipment->name }}</td></tr>
                            <tr><td class="label">Categoría</td><td class="value">{{ optional($equipment->category)->name ?: '—' }}</td></tr>
                            <tr><td class="label">Marca</td><td class="value">{{ optional($equipment->brand)->name ?: '—' }}</td></tr>
                            <tr><td class="label">Modelo</td><td class="value">{{ optional($equipment->model)->name ?: '—' }}</td></tr>
                            <tr><td class="label">Número de serie</td><td class="value">{{ $equipment->serial_number }}</td></tr>
                            <tr><td class="label">Estado</td><td class="value">{{ $equipment->statusLabel() }}</td></tr>
                            <tr><td class="label">Clasificación por riesgo</td><td class="value">{{ $equipment->riskClassLabel() ?: '—' }}</td></tr>
                            <tr><td class="label">Registro INVIMA</td><td class="value">{{ $equipment->invima_registry ?: '—' }}</td></tr>
                        </table>
                    </td>
                    <td>
                        <table class="info-table">
                            <tr><td class="label">Fabricante</td><td class="value">{{ $equipment->manufacturer ?: '—' }}</td></tr>
                            <tr><td class="label">País de origen</td><td class="value">{{ $equipment->origin_country ?: '—' }}</td></tr>
                            <tr><td class="label">Especialidades</td><td class="value">{{ $equipment->specialties ? implode(', ', $equipment->specialties) : '—' }}</td></tr>
                            <tr><td class="label">Área</td><td class="value">{{ optional($equipment->area)->name ?: '—' }}</td></tr>
                            <tr><td class="label">Ubicación / sede</td><td class="value">{{ $equipment->location ?: '—' }}</td></tr>
                            <tr><td class="label">Fecha de ingreso</td><td class="value">{{ optional($equipment->entry_date)->format('d/m/Y') ?: '—' }}</td></tr>
                            <tr><td class="label">Fecha de compra</td><td class="value">{{ optional($equipment->purchase_date)->format('d/m/Y') ?: '—' }}</td></tr>
                            <tr><td class="label">Tipo de adquisición</td><td class="value">{{ $equipment->acquisitionTypeLabel() ?: '—' }}</td></tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- GARANTÍA --}}
    <div class="section">
        <div class="section-title">Garantía</div>
        <table class="info-table">
            <tr>
                <td class="label">Estado de garantía</td><td class="value">{{ $equipment->warrantyStatusLabel() ?: '—' }}</td>
                <td class="label">Vencimiento</td><td class="value">{{ optional($equipment->warranty_expiry)->format('d/m/Y') ?: '—' }}</td>
            </tr>
        </table>
    </div>

    {{-- CARACTERÍSTICAS TÉCNICAS --}}
    @php($techFields = array_filter([
        'Voltaje' => $equipment->voltage,
        'Amperaje' => $equipment->amperage,
        'Corriente' => $equipment->current,
        'Potencia' => $equipment->power,
        'Temperatura' => $equipment->temperature,
        'Presión' => $equipment->pressure,
        'Peso' => $equipment->weight,
        'Velocidad' => $equipment->speed,
        'Tecnología predominante' => $equipment->predominant_technology,
    ]))
    @if ($techFields)
        <div class="section">
            <div class="section-title">Características técnicas</div>
            <table class="info-table">
                @foreach (array_chunk($techFields, 2, true) as $pair)
                    <tr>
                        @foreach ($pair as $label => $value)
                            <td class="label">{{ $label }}</td><td class="value">{{ $value }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    {{-- PLANTILLA DE MANTENIMIENTO Y ACCESORIOS --}}
    @if ($equipment->maintenance_tasks || $equipment->accessories || $equipment->components)
        <div class="section">
            <div class="section-title">Subtareas de mantenimiento y accesorios</div>
            <div class="two-col">
                <table>
                    <tr>
                        <td>
                            @forelse ((array) $equipment->maintenance_tasks as $task)
                                <div class="checklist-item"><span class="check-icon">&#10003;</span>{{ $task }}</div>
                            @empty
                                <span style="color:#9ca3af;">Sin subtareas definidas</span>
                            @endforelse
                        </td>
                        <td>
                            @forelse ((array) $equipment->accessories as $accessory)
                                <div class="checklist-item"><span class="check-icon">&#10003;</span>{{ $accessory }}</div>
                            @empty
                                <span style="color:#9ca3af;">Sin accesorios definidos</span>
                            @endforelse
                        </td>
                    </tr>
                </table>
            </div>
            @if ($equipment->components)
                <p style="margin-top:6px; font-size:9.5px;"><strong>Componentes (detalle):</strong> {{ $equipment->components }}</p>
            @endif
        </div>
    @endif

    @if ($equipment->notes)
        <div class="section">
            <div class="section-title">Notas / observaciones</div>
            <p style="font-size:9.5px; white-space:pre-wrap;">{{ $equipment->notes }}</p>
        </div>
    @endif

    {{-- HISTORIAL DE ÓRDENES DE TRABAJO --}}
    <div class="section">
        <div class="section-title">Historial de órdenes de trabajo ({{ $equipment->workOrders->count() }})</div>
        @if ($equipment->workOrders->isNotEmpty())
            <table class="ot-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Asunto</th>
                        <th>Técnico</th>
                        <th>Diagnóstico / trabajo realizado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($equipment->workOrders as $order)
                        <tr>
                            <td>{{ $order->code }}</td>
                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                            <td>{{ $order->typeLabel() }}</td>
                            <td>{{ $order->statusLabel() }}</td>
                            <td>{{ $order->title }}</td>
                            <td>{{ optional($order->technician)->name ?: '—' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit(trim(($order->diagnosis ? $order->diagnosis.' · ' : '').($order->work_performed ?? '')), 120) ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color:#9ca3af; font-size:9.5px;">Este equipo no tiene órdenes de trabajo registradas.</p>
        @endif
    </div>

    <div class="footer">
        Documento generado el {{ now()->format('d/m/Y') }} &mdash; {{ config('app.name') }}
    </div>
</div>
</body>
</html>
