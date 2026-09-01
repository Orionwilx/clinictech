<aside
    class="fixed inset-y-0 left-0 z-50 flex flex-col bg-brand-900 text-brand-50 transition-all duration-200 ease-in-out lg:translate-x-0"
    :class="[
        collapsed ? 'w-20' : 'w-64',
        mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]">

    {{-- Logo / marca --}}
    <div class="flex items-center gap-3 h-16 px-4 border-b border-white/10 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden">
            <x-application-logo class="w-9 h-9 shrink-0 text-white" />
            <span x-show="!collapsed" x-cloak class="text-lg font-semibold whitespace-nowrap">{{ config('app.name', 'ClinicTech') }}</span>
        </a>
    </div>

    {{-- Navegación --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" label="Dashboard">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75 12 3l9 6.75V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.75Z"/>
                </svg>
            </x-slot:icon>
        </x-sidebar-link>

        @can('view users')
            <x-sidebar-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" label="Usuarios">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.5a3 3 0 0 0-6 0M18 21a3 3 0 0 0-3-3M6 21a3 3 0 0 1 3-3m3-3a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/>
                    </svg>
                </x-slot:icon>
            </x-sidebar-link>
        @endcan

        @can('view clients')
            <x-sidebar-link :href="route('admin.clients.index')" :active="request()->routeIs('admin.clients.*')" label="Clientes">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V5a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v16M15 21V9h3a1 1 0 0 1 1 1v11M8 8h1m-1 4h1m3-4h1m-1 4h1"/>
                    </svg>
                </x-slot:icon>
            </x-sidebar-link>
        @endcan

        @can('view equipment')
            <x-sidebar-link :href="route('admin.equipment.index')" :active="request()->routeIs('admin.equipment.*')" label="Equipos">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 7v10a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7M4 7l2-3h12l2 3M9 12h6"/>
                    </svg>
                </x-slot:icon>
            </x-sidebar-link>
        @endcan

        @can('view technicians')
            <x-sidebar-link :href="route('admin.technicians.index')" :active="request()->routeIs('admin.technicians.*')" label="Técnicos">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM5 20a7 7 0 0 1 14 0M17 8l1.5-1.5M18.5 3.5 20 2"/>
                    </svg>
                </x-slot:icon>
            </x-sidebar-link>
        @endcan
    </nav>

    {{-- Colapsar (solo desktop) --}}
    <div class="border-t border-white/10 p-3 shrink-0 hidden lg:block">
        <button type="button" @click="toggle()"
                class="w-full flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-brand-100 hover:bg-white/10 hover:text-white transition-colors">
            <span class="shrink-0 w-6 h-6 flex items-center justify-center">
                <svg class="w-5 h-5 transition-transform duration-200" :class="collapsed && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </span>
            <span x-show="!collapsed" x-cloak class="truncate">Colapsar</span>
        </button>
    </div>
</aside>
