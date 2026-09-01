{{-- Espera: $client (nullable) --}}
@php($editing = isset($client) && $client->exists)
@php($usuario = $editing ? optional($client->user)->name : '')

<div class="space-y-6">
    {{-- Datos de empresa --}}
    <div>
        <h3 class="text-sm font-semibold text-brand-900 border-b border-gray-100 pb-2 mb-4">Datos de la empresa</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="name" :value="__('Nombre de la empresa')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                              :value="old('name', $client->name ?? '')" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="nit" :value="__('NIT')" />
                <x-text-input id="nit" name="nit" type="text" class="mt-1 block w-full"
                              :value="old('nit', $client->nit ?? '')" required />
                <x-input-error :messages="$errors->get('nit')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="city" :value="__('Ciudad')" />
                <x-text-input id="city" name="city" type="text" class="mt-1 block w-full"
                              :value="old('city', $client->city ?? '')" />
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="country" :value="__('País')" />
                <x-text-input id="country" name="country" type="text" class="mt-1 block w-full"
                              :value="old('country', $client->country ?? '')" />
                <x-input-error :messages="$errors->get('country')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="whatsapp" :value="__('WhatsApp')" />
                <x-text-input id="whatsapp" name="whatsapp" type="text" class="mt-1 block w-full"
                              :value="old('whatsapp', $client->whatsapp ?? '')" />
                <x-input-error :messages="$errors->get('whatsapp')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="phone" :value="__('Celular')" />
                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                              :value="old('phone', $client->phone ?? '')" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
        </div>
    </div>

    {{-- Credenciales de acceso (cuenta rol cliente) --}}
    <div>
        <h3 class="text-sm font-semibold text-brand-900 border-b border-gray-100 pb-2 mb-4">Acceso al panel (cuenta del cliente)</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="usuario" :value="__('Usuario')" />
                <x-text-input id="usuario" name="usuario" type="text" class="mt-1 block w-full"
                              :value="old('usuario', $usuario)" required />
                <x-input-error :messages="$errors->get('usuario')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="email" :value="__('Correo (inicio de sesión)')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                              :value="old('email', $client->email ?? '')" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="password" :value="$editing ? __('Contraseña (dejar en blanco para no cambiar)') : __('Contraseña')" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                              :required="! $editing" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full"
                              autocomplete="new-password" />
            </div>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input id="is_active" name="is_active" type="checkbox" value="1"
               @checked(old('is_active', $client->is_active ?? true))
               class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
        <x-input-label for="is_active" :value="__('Cliente activo')" />
    </div>
</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ $editing ? __('Actualizar') : __('Crear') }}</x-primary-button>
    <a href="{{ route('admin.clients.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>
