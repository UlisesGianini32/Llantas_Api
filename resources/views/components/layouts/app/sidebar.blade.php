<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">

    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse">
            <x-app-logo />
        </a>

        {{-- ======================
            DASHBOARD
        ====================== --}}
        <flux:navlist variant="outline">
            <flux:navlist.group heading="Platform" class="grid">
                <flux:navlist.item
                    icon="home"
                    :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')"
                    wire:navigate>
                    Dashboard
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>

        {{-- ======================
            INVENTARIO
        ====================== --}}
        <flux:navlist variant="outline">
            <flux:navlist.group heading="Inventario" class="grid">

                {{-- LLANTAS --}}
                <flux:navlist.item
                    :href="route('llantas.index')"
                    :current="request()->routeIs('llantas.*')"
                    wire:navigate>
                    Llantas
                </flux:navlist.item>

                {{-- PRODUCTOS COMPUESTOS --}}
                <flux:navlist.item
                    :href="route('productos.index')"
                    :current="request()->routeIs('productos.*')">
                    Productos compuestos
                </flux:navlist.item>

                {{-- IMPORTAR EXCEL (NUEVO) --}}
                <flux:navlist.item
                    :href="route('excel.vista')"
                    :current="request()->routeIs('excel.*')">
                    Importar Excel
                </flux:navlist.item>

            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

        {{-- ======================
            LINKS
        ====================== --}}
        <flux:navlist variant="outline">
            <flux:navlist.item
                icon="folder-git-2"
                href="https://github.com/laravel/livewire-starter-kit"
                target="_blank">
                Repository
            </flux:navlist.item>

            <flux:navlist.item
                icon="book-open-text"
                href="https://laravel.com/docs/starter-kits#livewire"
                target="_blank">
                Documentation
            </flux:navlist.item>
        </flux:navlist>

        {{-- ======================
            USER MENU (DESKTOP)
        ====================== --}}
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile
                :name="auth()->user()->name"
                :initials="auth()->user()->initials()"
                icon:trailing="chevrons-up-down"
            />

            <flux:menu class="w-[220px]">

                <div class="p-2 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-neutral-200 dark:bg-neutral-700">
                            {{ auth()->user()->initials() }}
                        </span>
                        <div>
                            <div class="font-semibold">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-gray-400">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                </div>

                <flux:menu.separator />

                <flux:menu.item :href="route('profile.edit')" icon="cog">
                    Settings
                </flux:menu.item>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle">
                        Log Out
                    </flux:menu.item>
                </form>

            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    {{-- CONTENIDO --}}
    {{ $slot }}

    @fluxScripts
</body>
</html>
