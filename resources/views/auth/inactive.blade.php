<x-guest-layout>
    <div class="text-center space-y-4">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-100">
            <svg class="h-7 w-7 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
            </svg>
        </div>
        <h1 class="text-lg font-semibold text-gray-900">Tu cuenta está inactiva</h1>
        <p class="text-sm text-gray-600">
            El acceso a la plataforma fue desactivado. Si crees que se trata de un error
            o necesitas reactivarla, contacta con el administrador.
        </p>
        <a href="{{ route('login') }}"
           class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
            Volver al inicio de sesión
        </a>
    </div>
</x-guest-layout>
