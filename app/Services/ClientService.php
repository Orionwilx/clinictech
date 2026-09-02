<?php

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClientService
{
    /**
     * Crea una empresa cliente junto con su cuenta de acceso (rol cliente).
     *
     * @param  array<string, mixed>  $data  validado por StoreClientRequest
     */
    public function create(array $data): Client
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['usuario'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => $data['is_active'] ?? true,
            ]);
            $user->assignRole('cliente');

            return Client::create([
                'name' => $data['name'],
                'logo_path' => $data['logo_path'] ?? null,
                'nit' => $data['nit'],
                'email' => $data['email'],
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? null,
                'whatsapp' => $data['whatsapp'] ?? null,
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'user_id' => $user->id,
            ]);
        });
    }

    /**
     * Actualiza la empresa y su cuenta vinculada.
     *
     * @param  array<string, mixed>  $data  validado por UpdateClientRequest
     */
    public function update(Client $client, array $data): void
    {
        DB::transaction(function () use ($client, $data) {
            $client->update(array_merge([
                'name' => $data['name'],
                'nit' => $data['nit'],
                'email' => $data['email'],
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? null,
                'whatsapp' => $data['whatsapp'] ?? null,
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? false,
            ], array_key_exists('logo_path', $data) ? ['logo_path' => $data['logo_path']] : []));

            if ($client->user) {
                $client->user->fill([
                    'name' => $data['usuario'],
                    'email' => $data['email'],
                    'is_active' => $data['is_active'] ?? false,
                ]);

                if (! empty($data['password'])) {
                    $client->user->password = Hash::make($data['password']);
                }

                $client->user->save();
            }
        });
    }

    /**
     * Baja lógica de la empresa y desactivación de su cuenta.
     */
    public function delete(Client $client): void
    {
        DB::transaction(function () use ($client) {
            $client->user?->update(['is_active' => false]);
            $client->delete();
        });
    }
}
