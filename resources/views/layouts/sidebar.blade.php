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

        @if(auth()->user()->hasRole('cliente'))
            {{-- Menú Panel Cliente --}}
            <x-sidebar-link :href="route('client.dashboard')" :active="request()->routeIs('client.dashboard')" label="Panel">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75 12 3l9 6.75V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.75Z"/>
                    </svg>
                </x-slot:icon>
            </x-sidebar-link>
            <x-sidebar-link :href="route('client.equipment.index')" :active="request()->routeIs('client.equipment.*')" label="Mis equipos">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 7v10a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7M4 7l2-3h12l2 3M9 12h6"/>
                    </svg>
                </x-slot:icon>
            </x-sidebar-link>
            <x-sidebar-link :href="route('client.work_orders.index')" :active="request()->routeIs('client.work_orders.*')" label="Mis OT">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4"/>
                    </svg>
                </x-slot:icon>
            </x-sidebar-link>
            <x-sidebar-link :href="route('client.technicians.index')" :active="request()->routeIs('client.technicians.*')" label="Mis técnicos">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM5 20a7 7 0 0 1 14 0M17 8l1.5-1.5M18.5 3.5 20 2"/>
                    </svg>
                </x-slot:icon>
            </x-sidebar-link>
        @elseif(auth()->user()->hasRole('tecnico'))
            {{-- Menú Panel Técnico --}}
            <x-sidebar-link :href="route('technician.dashboard')" :active="request()->routeIs('technician.dashboard')" label="Panel">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75 12 3l9 6.75V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.75Z"/>
                    </svg>
                </x-slot:icon>
            </x-sidebar-link>
            <x-sidebar-link :href="route('technician.work_orders.index')" :active="request()->routeIs('technician.work_orders.*')" label="Mis órdenes">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4"/>
                    </svg>
                </x-slot:icon>
            </x-sidebar-link>
        @else
        {{-- Menú Admin --}}
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

        @can('view brands')
            <x-sidebar-link :href="route('admin.brands.index')" :active="request()->routeIs('admin.brands.*')" label="Marcas">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75H6A2.25 2.25 0 0 0 3.75 6v1.5M16.5 3.75H18A2.25 2.25 0 0 1 20.25 6v1.5M7.5 20.25H6A2.25 2.25 0 0 1 3.75 18v-1.5M16.5 20.25H18A2.25 2.25 0 0 0 20.25 18v-1.5M9 12h6"/>
                    </svg>
                </x-slot:icon>
            </x-sidebar-link>
        @endcan

        @can('view equipment_models')
            <x-sidebar-link :href="route('admin.equipment_models.index')" :active="request()->routeIs('admin.equipment_models.*')" label="Modelos">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>
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

        @can('view work_orders')
            <x-sidebar-link :href="route('admin.work_orders.index')" :active="request()->routeIs('admin.work_orders.*')" label="Órdenes de trabajo">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4"/>
                    </svg>
                </x-slot:icon>
            </x-sidebar-link>
        @endcan

        @can('view reports')
            <x-sidebar-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.index') || request()->routeIs('admin.reports.export')" label="Reportes">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                </x-slot:icon>
            </x-sidebar-link>
            <x-sidebar-link :href="route('admin.reports.indicators')" :active="request()->routeIs('admin.reports.indicators')" label="Indicadores">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                    </svg>
                </x-slot:icon>
            </x-sidebar-link>
        @endcan
        @endif

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
