<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Equipment;
use App\Services\ClientService;
use App\Services\TechnicianService;
use App\Services\WorkOrderService;
use Illuminate\Database\Seeder;

/**
 * Datos de demostración para visualizar los módulos en desarrollo.
 * Idempotente: no hace nada si ya existen clientes.
 *
 * Ejecutar: php artisan db:seed --class=DemoSeeder
 * (o incluido en migrate:fresh --seed vía DatabaseSeeder).
 */
class DemoSeeder extends Seeder
{
    public function run(
        ClientService $clients,
        TechnicianService $technicians,
        WorkOrderService $workOrders
    ): void {
        if (Client::exists()) {
            $this->command?->warn('DemoSeeder omitido: ya existen clientes.');

            return;
        }

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

        $tecnicos = [
            $technicians->create([
                'name' => 'Carlos Ramírez',
                'document' => '1094567123',
                'email' => 'carlos.ramirez@ingsoln.com',
                'password' => 'password',
                'phone' => '3101234567',
                'specialty' => 'Electromedicina',
            ]),
            $technicians->create([
                'name' => 'Laura Gómez',
                'document' => '1098765432',
                'email' => 'laura.gomez@ingsoln.com',
                'password' => 'password',
                'phone' => '3117654321',
                'specialty' => 'Imagenología',
            ]),
            $technicians->create([
                'name' => 'Andrés Torres',
                'document' => '1076543210',
                'email' => 'andres.torres@ingsoln.com',
                'password' => 'password',
                'phone' => '3129998877',
                'specialty' => 'Refrigeración',
            ]),
        ];

        // Equipos por cliente.
        $monitor = Equipment::create([
            'client_id' => $clinicaValle->id,
            'name' => 'Monitor de signos vitales',
            'type' => 'Monitor', 'brand' => 'Philips', 'model' => 'MX450',
            'serial_number' => 'SN-VLL-0001', 'location' => 'UCI',
            'purchase_date' => '2023-03-10', 'warranty_expiry' => '2026-03-10',
            'status' => 'active',
        ]);
        $ventilador = Equipment::create([
            'client_id' => $clinicaValle->id,
            'name' => 'Ventilador mecánico',
            'type' => 'Ventilador', 'brand' => 'Dräger', 'model' => 'Evita V300',
            'serial_number' => 'SN-VLL-0002', 'location' => 'UCI',
            'purchase_date' => '2022-07-01', 'warranty_expiry' => '2025-07-01',
            'status' => 'maintenance',
        ]);
        $desfibrilador = Equipment::create([
            'client_id' => $hospitalNorte->id,
            'name' => 'Desfibrilador', 'type' => 'Desfibrilador',
            'brand' => 'Zoll', 'model' => 'R Series',
            'serial_number' => 'SN-NOR-0001', 'location' => 'Urgencias',
            'purchase_date' => '2021-11-20', 'warranty_expiry' => '2024-11-20',
            'status' => 'active',
        ]);
        $bomba = Equipment::create([
            'client_id' => $hospitalNorte->id,
            'name' => 'Bomba de infusión', 'type' => 'Bomba de infusión',
            'brand' => 'B. Braun', 'model' => 'Infusomat',
            'serial_number' => 'SN-NOR-0002', 'location' => 'Hospitalización',
            'purchase_date' => '2020-05-15', 'warranty_expiry' => '2023-05-15',
            'status' => 'inactive',
        ]);
        $ecografo = Equipment::create([
            'client_id' => $centroImagen->id,
            'name' => 'Ecógrafo', 'type' => 'Imagenología',
            'brand' => 'GE', 'model' => 'Logiq E10',
            'serial_number' => 'SN-IMG-0001', 'location' => 'Sala 2',
            'purchase_date' => '2024-01-05', 'warranty_expiry' => '2027-01-05',
            'status' => 'active',
        ]);

        // Órdenes de trabajo cubriendo estados/tipos/prioridades.
        $workOrders->create([
            'client_id' => $clinicaValle->id, 'equipment_id' => $monitor->id,
            'technician_id' => null,
            'title' => 'Monitor no enciende', 'description' => 'El equipo no responde al encendido.',
            'type' => 'corrective', 'priority' => 'high', 'status' => 'open',
        ]);
        $workOrders->create([
            'client_id' => $clinicaValle->id, 'equipment_id' => $ventilador->id,
            'technician_id' => $tecnicos[0]->id,
            'title' => 'Mantenimiento preventivo ventilador',
            'description' => 'Rutina trimestral programada.',
            'type' => 'preventive', 'priority' => 'medium', 'status' => 'assigned',
            'scheduled_at' => now()->addDays(3),
        ]);
        $workOrders->create([
            'client_id' => $hospitalNorte->id, 'equipment_id' => $desfibrilador->id,
            'technician_id' => $tecnicos[0]->id,
            'title' => 'Calibración de desfibrilador',
            'description' => 'Verificar entrega de energía.',
            'diagnosis' => 'Descalibración leve en niveles altos.',
            'type' => 'corrective', 'priority' => 'high', 'status' => 'in_progress',
        ]);
        $workOrders->create([
            'client_id' => $hospitalNorte->id, 'equipment_id' => $bomba->id,
            'technician_id' => $tecnicos[2]->id,
            'title' => 'Revisión bomba de infusión',
            'description' => 'Reporta oclusiones intermitentes.',
            'diagnosis' => 'Sensor de presión defectuoso.',
            'work_performed' => 'Reemplazo del sensor y prueba funcional.',
            'type' => 'corrective', 'priority' => 'medium', 'status' => 'completed',
        ]);
        $workOrders->create([
            'client_id' => $centroImagen->id, 'equipment_id' => $ecografo->id,
            'technician_id' => $tecnicos[1]->id,
            'title' => 'Instalación de software ecógrafo',
            'description' => 'Actualización de firmware v3.2.',
            'diagnosis' => 'Firmware desactualizado.',
            'work_performed' => 'Actualización aplicada y verificada.',
            'type' => 'preventive', 'priority' => 'low', 'status' => 'closed',
        ]);
        $workOrders->create([
            'client_id' => $centroImagen->id, 'equipment_id' => null,
            'technician_id' => null,
            'title' => 'Cotización de repuesto (cancelada)',
            'description' => 'Solicitud anulada por el cliente.',
            'type' => 'corrective', 'priority' => 'low', 'status' => 'cancelled',
        ]);

        // Una OT eliminada (soft delete) para ver el listado de admin.
        $eliminada = $workOrders->create([
            'client_id' => $clinicaValle->id, 'equipment_id' => null,
            'technician_id' => null,
            'title' => 'OT duplicada', 'type' => 'corrective',
            'priority' => 'low', 'status' => 'open',
        ]);
        $eliminada->delete();

        $this->command?->info('DemoSeeder: 3 clientes, 3 técnicos, 5 equipos y 7 órdenes creados.');
    }
}
