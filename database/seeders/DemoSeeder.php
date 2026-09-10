<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Equipment;
use App\Models\EquipmentModel;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\WorkOrderNotification;
use App\Services\ClientService;
use App\Services\TechnicianService;
use Illuminate\Database\Seeder;

/**
 * Datos de demostración que cubren TODOS los estados del flujo colaborativo.
 *
 * Flujos representados:
 *   A) Admin crea OT directamente → open → assigned → in_progress → pending_review → closed → enviado al cliente
 *   B) Cliente solicita → draft → admin aprueba → técnico diligencia → pending_review → admin aprueba → enviado
 *   C) Cliente solicita → draft → admin rechaza
 *   D) Admin aprueba trabajo del técnico pero aún no envía al cliente (closed sin visible_to_client)
 *   E) Admin rechaza trabajo del técnico → devuelto al técnico con motivo
 */
class DemoSeeder extends Seeder
{
    public function run(ClientService $clients, TechnicianService $technicians): void
    {
        if (Client::exists()) {
            $this->command?->warn('DemoSeeder omitido: ya existen clientes.');
            return;
        }

        $admin = User::where('email', 'admin@ingsoln.com')->firstOrFail();

        // ─── Clientes ────────────────────────────────────────────────────────
        $clinicaValle = $clients->create([
            'name' => 'Clínica del Valle S.A.',
            'nit' => '900123456-1',
            'usuario' => 'Clínica del Valle',
            'email' => 'contacto@clinicadelvalle.com',
            'password' => 'password',
            'city' => 'Cali',
            'country' => 'Colombia',
            'whatsapp' => '3001112233',
            'phone' => '6024441122',
        ]);

        $hospitalNorte = $clients->create([
            'name' => 'Hospital del Norte E.S.E.',
            'nit' => '800987654-2',
            'usuario' => 'Hospital del Norte',
            'email' => 'sistemas@hospitaldelnorte.gov.co',
            'password' => 'password',
            'city' => 'Barranquilla',
            'country' => 'Colombia',
            'whatsapp' => '3015556677',
            'phone' => '6053338899',
        ]);

        $centroImagen = $clients->create([
            'name' => 'Centro de Imágenes Diagnósticas Ltda.',
            'nit' => '901222333-4',
            'usuario' => 'Centro de Imágenes',
            'email' => 'admin@imagenesdiagnosticas.com',
            'password' => 'password',
            'city' => 'Medellín',
            'country' => 'Colombia',
            'whatsapp' => '3024445566',
            'phone' => '6042227788',
        ]);

        $userValle  = $clinicaValle->user;
        $userNorte  = $hospitalNorte->user;
        $userImagen = $centroImagen->user;

        // ─── Técnicos ─────────────────────────────────────────────────────────
        $carlos = $technicians->create([
            'name' => 'Carlos Ramírez',
            'document' => '1094567123',
            'email' => 'carlos.ramirez@ingsoln.com',
            'password' => 'password',
            'phone' => '3101234567',
            'specialty' => 'Electromedicina',
        ]);

        $laura = $technicians->create([
            'name' => 'Laura Gómez',
            'document' => '1098765432',
            'email' => 'laura.gomez@ingsoln.com',
            'password' => 'password',
            'phone' => '3117654321',
            'specialty' => 'Imagenología',
        ]);

        $andres = $technicians->create([
            'name' => 'Andrés Torres',
            'document' => '1076543210',
            'email' => 'andres.torres@ingsoln.com',
            'password' => 'password',
            'phone' => '3129998877',
            'specialty' => 'Refrigeración',
        ]);

        // ─── Áreas ────────────────────────────────────────────────────────────
        $uci         = $clinicaValle->areas()->create(['name' => 'UCI',          'description' => 'Unidad de Cuidados Intensivos']);
        $urgValle    = $clinicaValle->areas()->create(['name' => 'Urgencias']);
        $urgNorte    = $hospitalNorte->areas()->create(['name' => 'Urgencias']);
        $hospNorte   = $hospitalNorte->areas()->create(['name' => 'Hospitalización']);
        $imgSala     = $centroImagen->areas()->create(['name' => 'Imagenología', 'description' => 'Sala 2']);

        // ─── Helper catálogo ──────────────────────────────────────────────────
        $catalog = function (string $brandName, string $modelName): array {
            $model = EquipmentModel::whereHas('brand', fn ($q) => $q->where('name', $brandName))
                ->where('name', $modelName)->first();
            return ['brand_id' => optional($model)->brand_id, 'model_id' => optional($model)->id];
        };

        // ─── Equipos ──────────────────────────────────────────────────────────
        $monitor = Equipment::create([
            'client_id' => $clinicaValle->id, 'area_id' => $uci->id,
            'name' => 'Monitor de signos vitales', 'type' => 'Monitor',
            ...$catalog('Philips', 'IntelliVue MX450'),
            'serial_number' => 'SN-VLL-0001', 'location' => 'Sede Principal - Cali',
            'entry_date' => '2023-03-15', 'purchase_date' => '2023-03-10',
            'warranty_expiry' => '2026-03-10', 'warranty_status' => 'en_garantia',
            'risk_class' => 'IIB', 'invima_registry' => 'INVIMA-2023EBC-0012345',
            'manufacturer' => 'Philips Medical', 'origin_country' => 'Países Bajos',
            'maintenance_frequency' => 'quarterly', 'acquisition_type' => 'purchase',
            'voltage' => '110-240V', 'power' => '150W',
            'specialties' => ['prevention', 'treatment'],
            'maintenance_tasks' => ['functional_test', 'alarm_check', 'connectors_check', 'boards_cleaning'],
            'accessories' => ['ac_cable', 'spo2_sensor', 'nibp_hose', 'cuff', 'battery'],
            'status' => 'active',
        ]);

        $ventilador = Equipment::create([
            'client_id' => $clinicaValle->id, 'area_id' => $uci->id,
            'name' => 'Ventilador mecánico', 'type' => 'Ventilador',
            ...$catalog('Dräger', 'Evita V300'),
            'serial_number' => 'SN-VLL-0002', 'location' => 'Sede Principal - Cali',
            'purchase_date' => '2022-07-01', 'warranty_expiry' => '2025-07-01',
            'maintenance_frequency' => 'quarterly',
            'maintenance_tasks' => ['functional_test', 'filters_cleaning', 'connections_check'],
            'status' => 'maintenance',
        ]);

        $desfibrilador = Equipment::create([
            'client_id' => $hospitalNorte->id, 'area_id' => $urgNorte->id,
            'name' => 'Desfibrilador', 'type' => 'Desfibrilador',
            ...$catalog('Zoll', 'R Series'),
            'serial_number' => 'SN-NOR-0001', 'location' => 'Sede Norte - Barranquilla',
            'purchase_date' => '2021-11-20', 'warranty_expiry' => '2024-11-20',
            'maintenance_frequency' => 'biannual',
            'status' => 'active',
        ]);

        $bomba = Equipment::create([
            'client_id' => $hospitalNorte->id, 'area_id' => $hospNorte->id,
            'name' => 'Bomba de infusión', 'type' => 'Bomba de infusión',
            ...$catalog('B. Braun', 'Infusomat Space'),
            'serial_number' => 'SN-NOR-0002', 'location' => 'Sede Norte - Barranquilla',
            'purchase_date' => '2020-05-15', 'warranty_expiry' => '2023-05-15',
            'status' => 'inactive',
        ]);

        $ecografo = Equipment::create([
            'client_id' => $centroImagen->id, 'area_id' => $imgSala->id,
            'name' => 'Ecógrafo', 'type' => 'Imagenología',
            ...$catalog('GE Healthcare', 'Logiq E10'),
            'serial_number' => 'SN-IMG-0001', 'location' => 'Sede Medellín',
            'purchase_date' => '2024-01-05', 'warranty_expiry' => '2027-01-05',
            'maintenance_frequency' => 'annual',
            'status' => 'active',
        ]);

        // ─── FLUJO A: Admin crea OT — estados progresivos ─────────────────────
        // A1) open — Admin creó, sin técnico aún
        $ot01 = WorkOrder::create([
            'code' => 'OT-000001',
            'client_id' => $clinicaValle->id, 'equipment_id' => $monitor->id,
            'title' => 'Monitor no enciende',
            'description' => 'El equipo no responde al encendido desde la madrugada.',
            'type' => 'corrective', 'priority' => 'high', 'status' => 'open',
        ]);

        // A2) assigned — Admin asignó técnico, pendiente que inicie
        $ot02 = WorkOrder::create([
            'code' => 'OT-000002',
            'client_id' => $clinicaValle->id, 'equipment_id' => $ventilador->id,
            'technician_id' => $carlos->id,
            'title' => 'Mantenimiento preventivo ventilador',
            'description' => 'Rutina trimestral programada según periodicidad del equipo.',
            'type' => 'preventive', 'priority' => 'medium', 'status' => 'assigned',
            'scheduled_at' => now()->addDays(3),
        ]);
        // Notificar a Carlos que fue asignado
        $carlos->user->notify(new WorkOrderNotification(
            $ot02,
            "Se te asignó la orden {$ot02->code} — {$ot02->title}.",
            route('technician.work_orders.show', $ot02),
        ));

        // A3) in_progress — Carlos está trabajando
        $ot03 = WorkOrder::create([
            'code' => 'OT-000003',
            'client_id' => $clinicaValle->id, 'equipment_id' => $monitor->id,
            'technician_id' => $carlos->id,
            'title' => 'Alarma de saturación sin sonido',
            'description' => 'Alarma de SpO2 no suena aunque el valor baje del umbral.',
            'type' => 'corrective', 'priority' => 'high', 'status' => 'in_progress',
            'started_at' => now()->subHours(2),
        ]);

        // A4) pending_review — Carlos envió formulario, admin debe revisar
        $ot04 = WorkOrder::create([
            'code' => 'OT-000004',
            'client_id' => $clinicaValle->id, 'equipment_id' => $ventilador->id,
            'technician_id' => $carlos->id,
            'title' => 'Calibración de sensores de flujo',
            'description' => 'Verificar lecturas de flujo inspiratorio y espiratorio.',
            'type' => 'preventive', 'priority' => 'medium', 'status' => 'pending_review',
            'started_at' => now()->subDays(2),
            'completed_at' => now()->subHours(1),
            'diagnosis' => 'Sensor de flujo espiratorio con drift del 8%. Dentro del rango aceptable.',
            'work_performed' => 'Recalibración de sensores, limpieza de filtros internos y prueba funcional completa.',
            'maintenance_tasks' => ['functional_test', 'filters_cleaning', 'connections_check'],
            'accessories_checked' => ['ac_cable'],
            'additional_observations' => 'Equipo opera correctamente. Próximo mantenimiento en 3 meses.',
        ]);
        // Notificar al admin que hay trabajo pendiente de revisión
        $admin->notify(new WorkOrderNotification(
            $ot04,
            "La orden {$ot04->code} está lista para revisión.",
            route('admin.work_orders.show', $ot04),
        ));

        // A5) closed — Admin aprobó el trabajo pero AÚN NO envió al cliente
        $ot05 = WorkOrder::create([
            'code' => 'OT-000005',
            'client_id' => $clinicaValle->id, 'equipment_id' => $monitor->id,
            'technician_id' => $carlos->id,
            'title' => 'Revisión general post-instalación',
            'description' => 'Inspección completa tras instalación en nueva área.',
            'type' => 'preventive', 'priority' => 'low', 'status' => 'closed',
            'visible_to_client' => false,
            'started_at' => now()->subDays(5),
            'completed_at' => now()->subDays(2),
            'closed_at' => now()->subDay(),
            'diagnosis' => 'Equipo en perfectas condiciones.',
            'work_performed' => 'Inspección visual, prueba funcional y verificación de alarmas.',
            'maintenance_tasks' => ['functional_test', 'alarm_check'],
        ]);

        // A6) closed + visible_to_client — Flujo admin completo, cliente puede verlo
        $ot06 = WorkOrder::create([
            'code' => 'OT-000006',
            'client_id' => $clinicaValle->id, 'equipment_id' => $monitor->id,
            'technician_id' => $carlos->id,
            'title' => 'Cambio de batería interna',
            'description' => 'Batería de respaldo con menos del 20% de capacidad.',
            'type' => 'corrective', 'priority' => 'medium', 'status' => 'closed',
            'visible_to_client' => true,
            'started_at' => now()->subDays(10),
            'completed_at' => now()->subDays(8),
            'closed_at' => now()->subDays(7),
            'diagnosis' => 'Batería al 18% de capacidad. Reemplazo necesario.',
            'work_performed' => 'Reemplazo de batería interna, prueba de autonomía (4h sin falla).',
            'maintenance_tasks' => ['functional_test', 'connectors_check'],
            'additional_observations' => 'Se instaló batería de marca original. Garantía 1 año.',
        ]);
        $userValle->notify(new WorkOrderNotification(
            $ot06,
            "La orden de trabajo {$ot06->code} ya está disponible en tu panel.",
            route('client.work_orders.show', $ot06),
        ));

        // ─── FLUJO B: Cliente solicita → flujo completo hasta envío ───────────
        // B1) draft — Solicitud del cliente pendiente de aprobación
        $ot07 = WorkOrder::create([
            'code' => 'OT-000007',
            'client_id' => $hospitalNorte->id, 'equipment_id' => $desfibrilador->id,
            'title' => 'Desfibrilador no carga a 360J',
            'description' => 'El equipo carga hasta 200J solamente. Se revisó el manual y debe llegar a 360J.',
            'type' => 'corrective', 'priority' => 'medium', 'status' => 'draft',
            'requested_by_client' => true,
        ]);
        $admin->notify(new WorkOrderNotification(
            $ot07,
            "Nueva solicitud de mantenimiento: {$ot07->code} — {$ot07->title}",
            route('admin.work_orders.show', $ot07),
        ));

        // B2) cancelled — Admin rechazó la solicitud
        WorkOrder::create([
            'code' => 'OT-000008',
            'client_id' => $hospitalNorte->id, 'equipment_id' => $bomba->id,
            'title' => 'Bomba hace ruido al infundir',
            'description' => 'Sonido metálico durante la infusión, iniciado hace dos días.',
            'type' => 'corrective', 'priority' => 'medium', 'status' => 'cancelled',
            'requested_by_client' => true,
            'rejection_reason' => 'El equipo está dado de baja. No se realizarán intervenciones. Contactar para cotizar reemplazo.',
        ]);

        // B3) in_progress — Admin aprobó solicitud, asignó técnico, Laura trabajando
        $ot09 = WorkOrder::create([
            'code' => 'OT-000009',
            'client_id' => $hospitalNorte->id, 'equipment_id' => $desfibrilador->id,
            'technician_id' => $laura->id,
            'title' => 'Falla en pantalla del desfibrilador',
            'description' => 'Pantalla parpadea al iniciar el equipo. A veces queda en negro.',
            'type' => 'corrective', 'priority' => 'high', 'status' => 'in_progress',
            'requested_by_client' => true,
            'started_at' => now()->subHours(3),
        ]);
        $laura->user->notify(new WorkOrderNotification(
            $ot09,
            "Se te asignó la orden {$ot09->code} — {$ot09->title}.",
            route('technician.work_orders.show', $ot09),
        ));

        // B4) pending_review — Laura envió, admin debe revisar
        $ot10 = WorkOrder::create([
            'code' => 'OT-000010',
            'client_id' => $hospitalNorte->id, 'equipment_id' => $desfibrilador->id,
            'technician_id' => $laura->id,
            'title' => 'Mantenimiento preventivo semestral desfibrilador',
            'description' => 'Rutina semestral solicitada por el cliente.',
            'type' => 'preventive', 'priority' => 'medium', 'status' => 'pending_review',
            'requested_by_client' => true,
            'started_at' => now()->subDays(1),
            'completed_at' => now()->subHours(4),
            'diagnosis' => 'Electrodos y batería en buen estado. Energía de descarga nominal.',
            'work_performed' => 'Prueba de descarga a 200J y 360J. Limpieza general. Verificación de alarmas.',
            'maintenance_tasks' => ['functional_test', 'alarm_check', 'connectors_check'],
            'additional_observations' => 'Batería al 85%. Próximo reemplazo estimado en 18 meses.',
        ]);
        $admin->notify(new WorkOrderNotification(
            $ot10,
            "La orden {$ot10->code} está lista para revisión.",
            route('admin.work_orders.show', $ot10),
        ));

        // B5) closed + visible_to_client — Flujo completo cliente→técnico→admin→cliente
        $ot11 = WorkOrder::create([
            'code' => 'OT-000011',
            'client_id' => $hospitalNorte->id, 'equipment_id' => $bomba->id,
            'technician_id' => $laura->id,
            'title' => 'Sensor de presión defectuoso — bomba de infusión',
            'description' => 'Reporta oclusiones falsas. Equipo detiene infusión sin causa real.',
            'type' => 'corrective', 'priority' => 'high', 'status' => 'closed',
            'requested_by_client' => true,
            'visible_to_client' => true,
            'started_at' => now()->subDays(15),
            'completed_at' => now()->subDays(13),
            'closed_at' => now()->subDays(12),
            'diagnosis' => 'Sensor de presión con falla intermitente. Umbral de detección desviado +40mmHg.',
            'work_performed' => 'Reemplazo del sensor de presión por parte original. Calibración y prueba funcional con solución salina.',
            'maintenance_tasks' => ['functional_test', 'connectors_check'],
            'additional_observations' => 'Equipo entregado en perfectas condiciones. Se recomienda revisión en 6 meses.',
        ]);
        $userNorte->notify(new WorkOrderNotification(
            $ot11,
            "La orden de trabajo {$ot11->code} ya está disponible en tu panel.",
            route('client.work_orders.show', $ot11),
        ));

        // ─── FLUJO E: Admin rechaza trabajo del técnico → devuelto con motivo ──
        $ot12 = WorkOrder::create([
            'code' => 'OT-000012',
            'client_id' => $centroImagen->id, 'equipment_id' => $ecografo->id,
            'technician_id' => $andres->id,
            'title' => 'Ruido en imagen ecográfica',
            'description' => 'La imagen presenta artefactos en el cuadrante inferior derecho.',
            'type' => 'corrective', 'priority' => 'high', 'status' => 'in_progress',
            'started_at' => now()->subDays(1),
            'rejection_reason' => 'El informe está incompleto: falta el diagnóstico claro de la causa del artefacto y no se documentaron las pruebas realizadas. Por favor completar y reenviar.',
            'diagnosis' => 'Artefacto detectado.',
            'work_performed' => 'Se revisó el transductor.',
        ]);
        $andres->user->notify(new WorkOrderNotification(
            $ot12,
            "Tu trabajo en la orden {$ot12->code} fue devuelto para corrección. Motivo: {$ot12->rejection_reason}",
            route('technician.work_orders.show', $ot12),
        ));

        $count = WorkOrder::count();
        $this->command?->info("DemoSeeder: 3 clientes, 3 técnicos, 5 equipos y {$count} órdenes creados.");
        $this->command?->info('');
        $this->command?->info('── Credenciales de acceso ──────────────────────────');
        $this->command?->info('  Admin    → admin@ingsoln.com          / password');
        $this->command?->info('  Cliente  → contacto@clinicadelvalle.com / password  (OT-000001 a OT-000006)');
        $this->command?->info('  Cliente  → sistemas@hospitaldelnorte.gov.co / password (OT-000007 a OT-000011)');
        $this->command?->info('  Cliente  → admin@imagenesdiagnosticas.com / password (OT-000012)');
        $this->command?->info('  Técnico  → carlos.ramirez@ingsoln.com  / password');
        $this->command?->info('  Técnico  → laura.gomez@ingsoln.com     / password');
        $this->command?->info('  Técnico  → andres.torres@ingsoln.com   / password');
        $this->command?->info('');
        $this->command?->info('── Estados representados ───────────────────────────');
        $this->command?->info('  OT-000001  open            (admin creó, sin técnico)');
        $this->command?->info('  OT-000002  assigned        (técnico asignado, no ha iniciado)');
        $this->command?->info('  OT-000003  in_progress     (técnico trabajando)');
        $this->command?->info('  OT-000004  pending_review  (técnico envió, admin debe revisar)  ← NOTIF admin');
        $this->command?->info('  OT-000005  closed          (aprobado, aún NO enviado al cliente)');
        $this->command?->info('  OT-000006  closed + visible_to_client  (enviado al cliente)     ← NOTIF cliente Valle');
        $this->command?->info('  OT-000007  draft  req_cliente  (solicitud pendiente admin)      ← NOTIF admin');
        $this->command?->info('  OT-000008  cancelled req_cliente  (solicitud rechazada)');
        $this->command?->info('  OT-000009  in_progress req_cliente  (admin aprobó, técnico trabaja)');
        $this->command?->info('  OT-000010  pending_review req_cliente  (técnico envió)          ← NOTIF admin');
        $this->command?->info('  OT-000011  closed + visible_to_client req_cliente  (flujo completo)  ← NOTIF cliente Norte');
        $this->command?->info('  OT-000012  in_progress + rejection_reason  (trabajo devuelto al técnico)  ← NOTIF Andrés');
    }
}
