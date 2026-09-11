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

        $userValle = $clinicaValle->user;
        $userNorte = $hospitalNorte->user;
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
        $uci = $clinicaValle->areas()->create(['name' => 'UCI',          'description' => 'Unidad de Cuidados Intensivos']);
        $urgValle = $clinicaValle->areas()->create(['name' => 'Urgencias']);
        $urgNorte = $hospitalNorte->areas()->create(['name' => 'Urgencias']);
        $hospNorte = $hospitalNorte->areas()->create(['name' => 'Hospitalización']);
        $imgSala = $centroImagen->areas()->create(['name' => 'Imagenología', 'description' => 'Sala 2']);

        // ─── Helper: crea un equipo COMPLETO derivando la plantilla de su categoría
        //     (características, subtareas, accesorios) + fabricante/país de la marca. ─────
        $makeEquipment = function (Client $client, ?int $areaId, string $brandName, string $modelName, array $unit): Equipment {
            $model = EquipmentModel::with('brand', 'category')
                ->whereHas('brand', fn ($q) => $q->where('name', $brandName))
                ->where('name', $modelName)->firstOrFail();

            $template = $model->category?->templateData() ?? [];
            unset($template['name']); // el nombre lo define la unidad

            return Equipment::create(array_merge([
                'client_id' => $client->id,
                'area_id' => $areaId,
                'brand_id' => $model->brand_id,
                'model_id' => $model->id,
                'category_id' => $model->category_id,
                'manufacturer' => $model->brand?->manufacturer,
                'origin_country' => $model->brand?->origin_country,
                'warranty_status' => 'en_garantia',
                'acquisition_type' => 'purchase',
                'status' => 'active',
                ...$template,
            ], $unit));
        };

        // ─── Equipos (todos con ficha completa) ───────────────────────────────
        // Clínica del Valle
        $monitor = $makeEquipment($clinicaValle, $uci->id, 'Philips', 'IntelliVue MX450', [
            'name' => 'Monitor de signos vitales', 'serial_number' => 'SN-VLL-0001', 'location' => 'Sede Principal - Cali',
            'entry_date' => '2023-03-15', 'purchase_date' => '2023-03-10', 'warranty_expiry' => '2026-03-10',
            'invima_registry' => 'INVIMA-2023EBC-0012345',
        ]);
        $ventilador = $makeEquipment($clinicaValle, $uci->id, 'Dräger', 'Evita V300', [
            'name' => 'Ventilador mecánico', 'serial_number' => 'SN-VLL-0002', 'location' => 'Sede Principal - Cali',
            'entry_date' => '2022-07-05', 'purchase_date' => '2022-07-01', 'warranty_expiry' => '2025-07-01',
            'invima_registry' => 'INVIMA-2022EBC-0022110', 'status' => 'inactive',
        ]);
        $makeEquipment($clinicaValle, $urgValle->id, 'B. Braun', 'Perfusor Space', [
            'name' => 'Bomba de jeringa', 'serial_number' => 'SN-VLL-0003', 'location' => 'Sede Principal - Cali',
            'entry_date' => '2023-09-01', 'purchase_date' => '2023-08-20', 'warranty_expiry' => '2026-08-20',
            'invima_registry' => 'INVIMA-2023EBC-0033007',
        ]);
        $makeEquipment($clinicaValle, $uci->id, 'GE Healthcare', 'Giraffe OmniBed', [
            'name' => 'Incubadora neonatal', 'serial_number' => 'SN-VLL-0004', 'location' => 'Sede Principal - Cali',
            'entry_date' => '2021-02-10', 'purchase_date' => '2021-02-01', 'warranty_expiry' => '2024-02-01',
            'warranty_status' => 'sin_garantia', 'invima_registry' => 'INVIMA-2021EBC-0011884',
        ]);
        $makeEquipment($clinicaValle, $urgValle->id, 'Dräger', 'Fabius Tiro', [
            'name' => 'Máquina de anestesia', 'serial_number' => 'SN-VLL-0005', 'location' => 'Sede Principal - Cali',
            'entry_date' => '2022-11-15', 'purchase_date' => '2022-11-01', 'warranty_expiry' => '2025-11-01',
            'invima_registry' => 'INVIMA-2022EBC-0044521', 'acquisition_type' => 'comodato',
        ]);

        // Hospital del Norte
        $desfibrilador = $makeEquipment($hospitalNorte, $urgNorte->id, 'Zoll', 'R Series', [
            'name' => 'Desfibrilador', 'serial_number' => 'SN-NOR-0001', 'location' => 'Sede Norte - Barranquilla',
            'entry_date' => '2021-11-25', 'purchase_date' => '2021-11-20', 'warranty_expiry' => '2024-11-20',
            'warranty_status' => 'sin_garantia', 'invima_registry' => 'INVIMA-2021EBC-0055120',
        ]);
        $bomba = $makeEquipment($hospitalNorte, $hospNorte->id, 'B. Braun', 'Infusomat Space', [
            'name' => 'Bomba de infusión', 'serial_number' => 'SN-NOR-0002', 'location' => 'Sede Norte - Barranquilla',
            'entry_date' => '2020-05-20', 'purchase_date' => '2020-05-15', 'warranty_expiry' => '2023-05-15',
            'warranty_status' => 'sin_garantia', 'invima_registry' => 'INVIMA-2020EBC-0066033', 'status' => 'inactive',
        ]);
        $makeEquipment($hospitalNorte, $hospNorte->id, 'Fresenius', '4008S', [
            'name' => 'Máquina de diálisis', 'serial_number' => 'SN-NOR-0003', 'location' => 'Sede Norte - Barranquilla',
            'entry_date' => '2023-04-10', 'purchase_date' => '2023-04-01', 'warranty_expiry' => '2026-04-01',
            'invima_registry' => 'INVIMA-2023EBC-0077914', 'acquisition_type' => 'leasing', 'warranty_status' => 'leasing',
        ]);
        $makeEquipment($hospitalNorte, $urgNorte->id, 'Mindray', 'BeneVision N22', [
            'name' => 'Monitor multiparámetro', 'serial_number' => 'SN-NOR-0004', 'location' => 'Sede Norte - Barranquilla',
            'entry_date' => '2024-01-20', 'purchase_date' => '2024-01-10', 'warranty_expiry' => '2027-01-10',
            'invima_registry' => 'INVIMA-2024EBC-0088245',
        ]);

        // Centro de Imágenes
        $ecografo = $makeEquipment($centroImagen, $imgSala->id, 'GE Healthcare', 'Logiq E10', [
            'name' => 'Ecógrafo', 'serial_number' => 'SN-IMG-0001', 'location' => 'Sede Medellín',
            'entry_date' => '2024-01-10', 'purchase_date' => '2024-01-05', 'warranty_expiry' => '2027-01-05',
            'invima_registry' => 'INVIMA-2024EBC-0099002',
        ]);
        $makeEquipment($centroImagen, $imgSala->id, 'Siemens Healthineers', 'Somatom Go', [
            'name' => 'Tomógrafo', 'serial_number' => 'SN-IMG-0002', 'location' => 'Sede Medellín',
            'entry_date' => '2022-06-01', 'purchase_date' => '2022-05-15', 'warranty_expiry' => '2027-05-15',
            'invima_registry' => 'INVIMA-2022EBC-0100777', 'acquisition_type' => 'leasing', 'warranty_status' => 'leasing',
        ]);
        $makeEquipment($centroImagen, $imgSala->id, 'Mindray', 'Resona 7', [
            'name' => 'Ecógrafo doppler', 'serial_number' => 'SN-IMG-0003', 'location' => 'Sede Medellín',
            'entry_date' => '2023-03-01', 'purchase_date' => '2023-02-20', 'warranty_expiry' => '2026-02-20',
            'invima_registry' => 'INVIMA-2023EBC-0111360', 'status' => 'inactive',
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
            'maintenance_tasks' => ['Prueba de funcionamiento', 'Limpieza de filtros', 'Revisión de conectores'],
            'accessories_checked' => ['Cable de AC'],
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
            'maintenance_tasks' => ['Prueba de funcionamiento', 'Revisión de alarma'],
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
            'maintenance_tasks' => ['Prueba de funcionamiento', 'Revisión de conectores'],
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
            'maintenance_tasks' => ['Prueba de funcionamiento', 'Revisión de alarma', 'Revisión de conectores'],
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
            'maintenance_tasks' => ['Prueba de funcionamiento', 'Revisión de conectores'],
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
        $equipmentCount = Equipment::count();
        $this->command?->info("DemoSeeder: 3 clientes, 3 técnicos, {$equipmentCount} equipos (ficha completa) y {$count} órdenes creados.");
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
