<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Módulos del sistema (§5 del plan). Cada uno genera permisos CRUD.
     */
    private const MODULES = [
        'users',
        'clients',
        'equipment',
        'work_orders',
        'technicians',
        'trainings',
        'maintenances',
        'reports',
    ];

    /**
     * Verbos de permiso por módulo.
     */
    private const ABILITIES = ['view', 'create', 'update', 'delete'];

    public function run(): void
    {
        // Limpia la caché de permisos de spatie antes de sembrar.
        Artisan::call('permission:cache-reset');

        $permissions = $this->syncPermissions();

        // admin: todos los permisos.
        Role::firstOrCreate(['name' => 'admin'])
            ->syncPermissions($permissions);

        // tecnico: opera equipos, órdenes, mantenimientos; consulta clientes/capacitaciones.
        Role::firstOrCreate(['name' => 'tecnico'])->syncPermissions([
            'view clients',
            'view equipment', 'update equipment',
            'view work_orders', 'update work_orders',
            'view maintenances', 'update maintenances',
            'view trainings',
        ]);

        // cliente: solo consulta de su propia información (segregación en Policies, §7).
        Role::firstOrCreate(['name' => 'cliente'])->syncPermissions([
            'view equipment',
            'view work_orders',
            'view maintenances',
            'view trainings',
            'view reports',
        ]);
    }

    /**
     * Crea/asegura todos los permisos "{ability} {module}" y devuelve sus nombres.
     *
     * @return list<string>
     */
    private function syncPermissions(): array
    {
        $names = [];

        foreach (self::MODULES as $module) {
            foreach (self::ABILITIES as $ability) {
                $name = "{$ability} {$module}";
                Permission::firstOrCreate(['name' => $name]);
                $names[] = $name;
            }
        }

        return $names;
    }
}
