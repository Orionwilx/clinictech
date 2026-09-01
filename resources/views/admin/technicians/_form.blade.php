{{-- Espera: $technician (nullable) --}}
@php($editing = isset($technician) && $technician->exists)

<div class="space-y-6">
    {{-- Ficha del técnico --}}
    <div>
        <h3 class="text-sm font-semibold text-brand-900 border-b border-gray-100 pb-2 mb-4">Ficha del técnico</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="name" :value="__('Nombre completo')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                              :value="old('name', $technician->name ?? '')" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="document" :value="__('Documento')" />
                <x-text-input id="document" name="document" type="text" class="mt-1 block w-full"
                              :value="old('document', $technician->document ?? '')" required />
                <x-input-error :messages="$errors->get('document')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="phone" :value="__('Celular')" />
                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                              :value="old('phone', $technician->phone ?? '')" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="specialty" :value="__('Especialidad')" />
                <x-text-input id="specialty" name="specialty" type="text" class="mt-1 block w-full"
                              :value="old('specialty', $technician->specialty ?? '')" />
                <x-input-error :messages="$errors->get('specialty')" class="mt-2" />
            </div>
        </div>
    </div>

    {{-- Credenciales de acceso (cuenta rol tecnico) --}}
    <div>
        <h3 class="text-sm font-semibold text-brand-900 border-b border-gray-100 pb-2 mb-4">Acceso al sistema</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="email" :value="__('Correo (inicio de sesión)')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                              :value="old('email', $technician->email ?? '')" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div class="hidden sm:block"></div>
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
               @checked(old('is_active', $technician->is_active ?? true))
               class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
        <x-input-label for="is_active" :value="__('Técnico activo')" />
    </div>
</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ $editing ? __('Actualizar') : __('Crear') }}</x-primary-button>
    <a href="{{ route('admin.technicians.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>
