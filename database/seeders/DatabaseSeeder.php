<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@ingsoln.com',
        ])->assignRole('admin');

        $this->call(EquipmentCatalogSeeder::class);
        $this->call(DemoSeeder::class);
    }
}
