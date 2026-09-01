<?php

namespace App\Services;

use App\Models\Technician;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TechnicianService
{
    /**
     * Crea un técnico junto con su cuenta de acceso (rol tecnico).
     *
     * @param  array<string, mixed>  $data  validado por StoreTechnicianRequest
     */
    public function create(array $data): Technician
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => $data['is_active'] ?? true,
            ]);
            $user->assignRole('tecnico');

            return Technician::create([
                'name' => $data['name'],
                'document' => $data['document'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'specialty' => $data['specialty'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'user_id' => $user->id,
            ]);
        });
    }

    /**
     * Actualiza el técnico y su cuenta vinculada.
     *
     * @param  array<string, mixed>  $data  validado por UpdateTechnicianRequest
     */
    public function update(Technician $technician, array $data): void
    {
        DB::transaction(function () use ($technician, $data) {
            $technician->update([
                'name' => $data['name'],
                'document' => $data['document'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'specialty' => $data['specialty'] ?? null,
                'is_active' => $data['is_active'] ?? false,
            ]);

            if ($technician->user) {
                $technician->user->fill([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'is_active' => $data['is_active'] ?? false,
                ]);

                if (! empty($data['password'])) {
                    $technician->user->password = Hash::make($data['password']);
                }

                $technician->user->save();
            }
        });
    }

    /**
     * Baja lógica del técnico y desactivación de su cuenta.
     */
    public function delete(Technician $technician): void
    {
        DB::transaction(function () use ($technician) {
            $technician->user?->update(['is_active' => false]);
            $technician->delete();
        });
    }
}
