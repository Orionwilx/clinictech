<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ClinicTech') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div
            x-data="{
                collapsed: JSON.parse(localStorage.getItem('sb_collapsed') || 'false'),
                mobileOpen: false,
                toggle() { this.collapsed = !this.collapsed; localStorage.setItem('sb_collapsed', this.collapsed); }
            }"
            class="min-h-screen bg-gray-100">

            {{-- Overlay (móvil) --}}
            <div x-show="mobileOpen" x-cloak @click="mobileOpen = false"
                 x-transition.opacity
                 class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"></div>

            @include('layouts.sidebar')

            {{-- Contenido, desplazado por el ancho del sidebar en desktop --}}
            <div class="transition-all duration-200 ease-in-out" :class="collapsed ? 'lg:pl-20' : 'lg:pl-64'">

                {{-- Topbar --}}
                <header class="sticky top-0 z-30 bg-white border-b border-gray-200">
                    <div class="flex items-center gap-4 min-h-[4rem] py-3 px-4 sm:px-6 lg:px-8">
                        {{-- Toggle móvil --}}
                        <button type="button" @click="mobileOpen = true"
                                class="lg:hidden p-2 -ml-2 rounded-md text-gray-500 hover:bg-gray-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        {{-- Título de página (slot header) --}}
                        <div class="flex-1 min-w-0">
                            @isset($header)
                                {{ $header }}
                            @endisset
                        </div>

                        {{-- Campana de notificaciones --}}
                        @auth
                        @php($unread = auth()->user()->unreadNotifications)
                        <div x-data="{ open: false, count: {{ $unread->count() }} }" class="relative">
                            <button @click="open = !open; if(open && count > 0) { fetch('{{ route('notifications.read-all') }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}}); count = 0; }"
                                    class="relative p-2 rounded-md text-gray-500 hover:bg-gray-100 focus:outline-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                                </svg>
                                <span x-show="count > 0" x-text="count"
                                      class="absolute -top-0.5 -right-0.5 min-w-[1.1rem] h-[1.1rem] rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center px-0.5"></span>
                            </button>

                            <div x-show="open" x-cloak @click.outside="open = false"
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50 overflow-hidden">
                                <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-600 uppercase">Notificaciones</span>
                                </div>
                                <ul class="max-h-72 overflow-y-auto divide-y divide-gray-100">
                                    @forelse ($unread->take(10) as $notification)
                                        <li>
                                            <a href="{{ $notification->data['url'] ?? '#' }}"
                                               class="block px-4 py-3 hover:bg-gray-50 text-sm text-gray-700">
                                                {{ $notification->data['message'] ?? '' }}
                                                <span class="block text-xs text-gray-400 mt-0.5">{{ $notification->created_at->diffForHumans() }}</span>
                                            </a>
                                        </li>
                                    @empty
                                        <li class="px-4 py-6 text-center text-sm text-gray-400">Sin notificaciones nuevas</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                        @endauth

                        {{-- Menú de usuario --}}
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-100 focus:outline-none transition">
                                    <span class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-semibold">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                    <span class="hidden sm:block">{{ Auth::user()->name }}</span>
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">{{ __('Perfil') }}</x-dropdown-link>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                        {{ __('Cerrar sesión') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                {{-- Contenido de página --}}
                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
