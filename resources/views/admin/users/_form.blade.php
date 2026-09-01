{{-- Espera: $user (nullable), $roles (Collection), $currentRole (nullable) --}}
@php($editing = isset($user) && $user->exists)

<div class="space-y-4">
    <div>
        <x-input-label for="name" :value="__('Nombre')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $user->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                      :value="old('email', $user->email ?? '')" required />
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

    <div>
        <x-input-label for="role" :value="__('Rol')" />
        <select id="role" name="role" required
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">— Selecciona —</option>
            @foreach ($roles as $role)
                <option value="{{ $role }}" @selected(old('role', $currentRole ?? '') === $role)>{{ ucfirst($role) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('role')" class="mt-2" />
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input id="is_active" name="is_active" type="checkbox" value="1"
               @checked(old('is_active', $user->is_active ?? true))
               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
        <x-input-label for="is_active" :value="__('Usuario activo')" />
    </div>
</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ $editing ? __('Actualizar') : __('Crear') }}</x-primary-button>
    <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>
