<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="space-y-1">
                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}" wire:navigate class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors cursor-pointer {{ request()->routeIs('dashboard') ? 'bg-zinc-200/60 dark:bg-zinc-800/80 text-zinc-900 dark:text-white' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/60' }}">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand/15 text-brand dark:text-brand-accent shadow-2xs">
                            <flux:icon icon="home" class="size-4" />
                        </span>
                        <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ __('Dashboard') }}</span>
                    </div>
                </a>

                {{-- 1. AYARLAR MENÜSÜ --}}
                @canany(['settings.view', 'languages.view'])
                    <div x-data="{ open: {{ request()->routeIs('settings.system', 'settings.languages') ? 'true' : 'false' }} }" class="space-y-1 pt-2">
                        <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors cursor-pointer">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand/15 text-brand dark:text-brand-accent shadow-2xs">
                                    <flux:icon icon="cog-6-tooth" class="size-4" />
                                </span>
                                <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ __('Settings') }}</span>
                            </div>
                            <flux:icon icon="chevron-down" class="size-3.5 transition-transform duration-200 text-zinc-400" ::class="open ? 'rotate-180' : ''" />
                        </button>

                        <div x-show="open" x-collapse class="pl-4 space-y-1 border-l-2 border-brand/20 ml-6 my-1.5">
                            @can('settings.view')
                                <flux:sidebar.item icon="adjustments-horizontal" :href="route('settings.system')" :current="request()->routeIs('settings.system')" wire:navigate>
                                    {{ __('System Settings') }}
                                </flux:sidebar.item>
                            @endcan
                            @can('languages.view')
                                <flux:sidebar.item icon="language" :href="route('settings.languages')" :current="request()->routeIs('settings.languages')" wire:navigate>
                                    {{ __('Languages') }}
                                </flux:sidebar.item>
                            @endcan
                        </div>
                    </div>
                @endcanany

                {{-- 2. SİSTEM MENÜSÜ (ALTTA) --}}
                @canany(['media.view', 'users.view', 'roles.view', 'logs.view', 'backups.view'])
                    <div x-data="{ open: {{ request()->routeIs('media.*', 'users.*', 'roles.*', 'settings.logs', 'settings.backups', 'settings.system-info') ? 'true' : 'false' }} }" class="space-y-1 pt-1">
                        <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors cursor-pointer">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand/15 text-brand dark:text-brand-accent shadow-2xs">
                                    <flux:icon icon="server-stack" class="size-4" />
                                </span>
                                <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ __('System') }}</span>
                            </div>
                            <flux:icon icon="chevron-down" class="size-3.5 transition-transform duration-200 text-zinc-400" ::class="open ? 'rotate-180' : ''" />
                        </button>

                        <div x-show="open" x-collapse class="pl-4 space-y-1 border-l-2 border-brand/20 ml-6 my-1.5">
                            @can('media.view')
                                <flux:sidebar.item icon="photo" :href="route('media.index')" :current="request()->routeIs('media.index')" wire:navigate>
                                    {{ __('Media Library') }}
                                </flux:sidebar.item>
                            @endcan
                            @can('users.view')
                                <flux:sidebar.item icon="users" :href="route('users.index')" :current="request()->routeIs('users.index')" wire:navigate>
                                    {{ __('User Management') }}
                                </flux:sidebar.item>
                            @endcan
                            @can('roles.view')
                                <flux:sidebar.item icon="shield-check" :href="route('roles.index')" :current="request()->routeIs('roles.index')" wire:navigate>
                                    {{ __('Roles & Permissions') }}
                                </flux:sidebar.item>
                            @endcan
                            @can('logs.view')
                                <flux:sidebar.item icon="clipboard-document-list" :href="route('settings.logs')" :current="request()->routeIs('settings.logs')" wire:navigate>
                                    {{ __('Activity Logs') }}
                                </flux:sidebar.item>
                            @endcan
                            @can('backups.view')
                                <flux:sidebar.item icon="archive-box" :href="route('settings.backups')" :current="request()->routeIs('settings.backups')" wire:navigate>
                                    {{ __('Backups Management') }}
                                </flux:sidebar.item>
                            @endcan
                            @can('settings.view')
                                <flux:sidebar.item icon="server-stack" :href="route('settings.system-info')" :current="request()->routeIs('settings.system-info')" wire:navigate>
                                    {{ __('System Information') }}
                                </flux:sidebar.item>
                            @endcan
                        </div>
                    </div>
                @endcanany
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    @foreach(config('app.available_locales', []) as $code => $locale)
                        <flux:menu.item href="/locale/{{ $code }}">
                            {{ $locale['flag'] }} {{ __($locale['name']) }} @if(app()->getLocale() === $code) ✓ @endif
                        </flux:menu.item>
                    @endforeach

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            const isDark = document.documentElement.classList.contains('dark');
            const brand = getComputedStyle(document.documentElement).getPropertyValue('--theme-brand').trim() || '#059669';

            const swalDefaults = {
                background: isDark ? '#27272a' : '#ffffff',
                color: isDark ? '#e4e4e7' : '#18181b',
                confirmButtonColor: brand,
                cancelButtonColor: isDark ? '#3f3f46' : '#f4f4f5',
                reverseButtons: true,
            };

            const _origFire = Swal.fire.bind(Swal);
            Swal.fire = function (opts) {
                if (typeof opts === 'string') return _origFire(opts);
                return _origFire({ ...swalDefaults, ...opts });
            };

            document.addEventListener('livewire:init', () => {
                Livewire.on('swal', (data) => Swal.fire(data[0]));
                Livewire.on('swal-toast', (data) => Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true, icon: 'success', background: isDark ? '#27272a' : '#ffffff', color: isDark ? '#e4e4e7' : '#18181b', ...data[0] }));
            });
        </script>
    </body>
</html>
