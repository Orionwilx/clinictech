<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Solicitar mantenimiento"
            :breadcrumbs="[['label' => 'Panel', 'href' => route('client.dashboard')], ['label' => 'Órdenes', 'href' => route('client.work_orders.index')], ['label' => 'Nueva solicitud']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500 mb-6">
                    Completa el formulario para enviar una solicitud al administrador. Una vez revisada, te notificaremos la respuesta.
                </p>

                <form method="POST" action="{{ route('client.work_orders.store') }}">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <x-input-label for="title" value="Asunto de la solicitud" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                                          :value="old('title')" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="type" value="Tipo de mantenimiento" />
                            <select id="type" name="type" required
                                    class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                                @foreach (\App\Models\WorkOrder::TYPES as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="equipment_id" value="Equipo afectado (opcional)" />
                            <select id="equipment_id" name="equipment_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                                <option value="">— Sin equipo específico —</option>
                                @foreach ($equipment as $id => $name)
                                    <option value="{{ $id }}" @selected(old('equipment_id') == $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('equipment_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="description" value="Descripción del problema o necesidad" />
                            <textarea id="description" name="description" rows="4" required
                                      class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4 mt-6">
                        <x-primary-button>Enviar solicitud</x-primary-button>
                        <a href="{{ route('client.work_orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
