<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Editar usuario" :breadcrumbs="[['label' => 'Usuarios', 'href' => route('admin.users.index')], ['label' => 'Editar']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('admin.users._form', ['user' => $user, 'roles' => $roles, 'currentRole' => $currentRole])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
