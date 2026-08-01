<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <div class="flex items-center gap-1">
                    @auth
                        @livewire('notification-bell')
                    @endauth
                    <flux:sidebar.collapse class="lg:hidden" />
                </div>
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

                {{-- 1. EXCACOIN MENÜ ELEMANLARI --}}
                @canany(['quick_entry.access', 'excavation_projects.view', 'finds.view', 'coins.view', 'dictionaries.view'])
                    @can('quick_entry.access')
                        <a href="{{ route('quick-entry.index') }}" wire:navigate class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors cursor-pointer {{ request()->routeIs('quick-entry.*') ? 'bg-amber-500/20 text-amber-900 dark:text-amber-200 font-bold' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/60' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500 text-white shadow-xs">
                                    <flux:icon icon="bolt" class="size-4" />
                                </span>
                                <span class="font-semibold text-amber-700 dark:text-amber-400">{{ __('Hızlı Veri Girişi') }}</span>
                            </div>
                        </a>
                    @endcan
                    @can('excavation_projects.view')
                            <a href="{{ route('excavation-projects.index') }}" wire:navigate class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors cursor-pointer {{ request()->routeIs('excavation-projects.*') ? 'bg-zinc-200/60 dark:bg-zinc-800/80 text-zinc-900 dark:text-white' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/60' }}">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/15 text-amber-600 dark:text-amber-400 shadow-2xs">
                                        <flux:icon icon="map-pin" class="size-4" />
                                    </span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ __('Kazı Projeleri') }}</span>
                                </div>
                            </a>
                        @endcan
                        @can('finds.view')
                            <a href="{{ route('all-finds.index') }}" wire:navigate class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors cursor-pointer {{ request()->routeIs('all-finds.*', 'finds.*') ? 'bg-zinc-200/60 dark:bg-zinc-800/80 text-zinc-900 dark:text-white' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/60' }}">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/15 text-blue-600 dark:text-blue-400 shadow-2xs">
                                        <flux:icon icon="archive-box" class="size-4" />
                                    </span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ __('Tüm Buluntular') }}</span>
                                </div>
                            </a>
                        @endcan
                        @can('coins.view')
                            <a href="{{ route('all-coins.index') }}" wire:navigate class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors cursor-pointer {{ request()->routeIs('all-coins.*', 'coins.*') ? 'bg-zinc-200/60 dark:bg-zinc-800/80 text-zinc-900 dark:text-white' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/60' }}">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-600/15 text-amber-700 dark:text-amber-300 shadow-2xs">
                                        <flux:icon icon="circle-stack" class="size-4" />
                                    </span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ __('Tüm Sikkeler') }}</span>
                                </div>
                            </a>
                        @endcan
                        @can('dictionaries.view')
                            <a href="{{ route('dictionaries.index') }}" wire:navigate class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors cursor-pointer {{ request()->routeIs('dictionaries.*') ? 'bg-zinc-200/60 dark:bg-zinc-800/80 text-zinc-900 dark:text-white' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/60' }}">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-500/15 text-purple-600 dark:text-purple-400 shadow-2xs">
                                        <flux:icon icon="book-open" class="size-4" />
                                    </span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ __('Nümismatik Sözlükler') }}</span>
                                </div>
                            </a>
                        @endcan
                @endcanany

                {{-- 2. MEDYA KÜTÜPHANESİ --}}
                @can('media.view')
                    <a href="{{ route('media.index') }}" wire:navigate class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors cursor-pointer {{ request()->routeIs('media.*') ? 'bg-zinc-200/60 dark:bg-zinc-800/80 text-zinc-900 dark:text-white' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/60' }}">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-pink-500/15 text-pink-600 dark:text-pink-400 shadow-2xs">
                                <flux:icon icon="photo" class="size-4" />
                            </span>
                            <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ __('Medya Kütüphanesi') }}</span>
                        </div>
                    </a>
                @endcan

                {{-- 3. KULLANICI YÖNETİMİ MENÜSÜ --}}
                @canany(['users.view', 'roles.view'])
                    <div x-data="{ open: {{ request()->routeIs('users.*', 'roles.*') ? 'true' : 'false' }} }" class="space-y-1 pt-1">
                        <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors cursor-pointer">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 shadow-2xs">
                                    <flux:icon icon="users" class="size-4" />
                                </span>
                                <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ __('Kullanıcı Yönetimi') }}</span>
                            </div>
                            <flux:icon icon="chevron-down" class="size-3.5 transition-transform duration-200 text-zinc-400" ::class="open ? 'rotate-180' : ''" />
                        </button>

                        <div x-show="open" x-collapse class="pl-4 space-y-1 border-l-2 border-brand/20 ml-6 my-1.5">
                            @can('users.view')
                                <flux:sidebar.item icon="user-group" :href="route('users.index')" :current="request()->routeIs('users.index')" wire:navigate>
                                    {{ __('Kullanıcılar') }}
                                </flux:sidebar.item>
                            @endcan
                            @can('roles.view')
                                <flux:sidebar.item icon="shield-check" :href="route('roles.index')" :current="request()->routeIs('roles.index')" wire:navigate>
                                    {{ __('Roller & Yetkiler') }}
                                </flux:sidebar.item>
                            @endcan
                        </div>
                    </div>
                @endcanany

                {{-- 4. AYARLAR MENÜSÜ --}}
                @canany(['settings.view', 'languages.view'])
                    <div x-data="{ open: {{ request()->routeIs('settings.system', 'settings.languages') ? 'true' : 'false' }} }" class="space-y-1 pt-1">
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

                {{-- 5. SİSTEM MENÜSÜ --}}
                @canany(['logs.view', 'backups.view', 'settings.view'])
                    <div x-data="{ open: {{ request()->routeIs('settings.logs', 'settings.backups', 'settings.system-info') ? 'true' : 'false' }} }" class="space-y-1 pt-1">
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

            @auth
                @livewire('notification-bell')
            @endauth

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

        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
        <style>
            .ts-wrapper { width: 100%; }
            .ts-control {
                border-radius: 0.5rem !important;
                padding: 0.5rem 0.75rem !important;
                background-color: var(--tw-bg-opacity, #ffffff) !important;
                border-color: #e4e4e7 !important;
                min-height: 2.5rem !important;
            }
            .dark .ts-control {
                background-color: rgba(255, 255, 255, 0.1) !important;
                border-color: rgba(255, 255, 255, 0.1) !important;
                color: #e4e4e7 !important;
            }
            .dark .ts-control input { color: #e4e4e7 !important; }
            .dark .ts-dropdown {
                background-color: #27272a !important;
                border-color: #3f3f46 !important;
                color: #e4e4e7 !important;
            }
            .dark .ts-dropdown .option {
                color: #e4e4e7 !important;
            }
            .dark .ts-dropdown .active {
                background-color: #3f3f46 !important;
                color: #ffffff !important;
            }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.directive('searchable', (el, {}, { cleanup }) => {
                    if (typeof TomSelect === 'undefined') return;
                    const ts = new TomSelect(el, {
                        create: false,
                        maxOptions: 300,
                        placeholder: el.getAttribute('placeholder') || 'Arayın...',
                        plugins: ['dropdown_input'],
                        onChange(val) {
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                            el.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });
                    cleanup(() => {
                        try { ts.destroy(); } catch(e){}
                    });
                });
            });

            (function () {
                const isDark = document.documentElement.classList.contains('dark');
                const brand = getComputedStyle(document.documentElement).getPropertyValue('--theme-brand').trim() || '#059669';

                const swalDefaults = {
                    background: isDark ? '#27272a' : '#ffffff',
                    color: isDark ? '#e4e4e7' : '#18181b',
                    confirmButtonColor: brand,
                    cancelButtonColor: isDark ? '#3f3f46' : '#f4f4f5',
                    reverseButtons: true,
                };

                if (!window._swalPatched) {
                    const _origFire = Swal.fire.bind(Swal);
                    Swal.fire = function (opts) {
                        if (typeof opts === 'string') return _origFire(opts);
                        return _origFire({ ...swalDefaults, ...opts });
                    };
                    window._swalPatched = true;
                }

                if (!window._swalListenersAdded) {
                    document.addEventListener('livewire:init', () => {
                        Livewire.on('swal', (data) => Swal.fire(data[0]));
                        Livewire.on('swal-toast', (data) => Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3500, timerProgressBar: true, icon: 'success', background: document.documentElement.classList.contains('dark') ? '#27272a' : '#ffffff', color: document.documentElement.classList.contains('dark') ? '#e4e4e7' : '#18181b', ...data[0] }));
                        Livewire.on('toast', (data) => {
                            const payload = (Array.isArray(data) ? data[0] : data) || {};
                            const title = payload.message || payload.title || 'İşlem Başarılı';
                            const icon = payload.type || 'success';
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3500,
                                timerProgressBar: true,
                                icon: icon,
                                title: title,
                                background: document.documentElement.classList.contains('dark') ? '#27272a' : '#ffffff',
                                color: document.documentElement.classList.contains('dark') ? '#e4e4e7' : '#18181b',
                            });
                        });

                        // Custom SweetAlert2 handler for Livewire 3 wire:confirm
                        Livewire.directive('confirm', ({ el, directive, component, cleanup }) => {
                            let content = directive.expression;

                            let onClick = (e) => {
                                e.stopImmediatePropagation();
                                e.preventDefault();

                                const isDark = document.documentElement.classList.contains('dark');
                                const brand = getComputedStyle(document.documentElement).getPropertyValue('--theme-brand').trim() || '#059669';

                                Swal.fire({
                                    title: 'Emin misiniz?',
                                    text: content,
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Evet, Devam Et',
                                    cancelButtonText: 'Vazgeç',
                                    confirmButtonColor: brand,
                                    cancelButtonColor: isDark ? '#3f3f46' : '#e4e4e7',
                                    background: isDark ? '#27272a' : '#ffffff',
                                    color: isDark ? '#e4e4e7' : '#18181b',
                                    reverseButtons: true,
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        el.removeEventListener('click', onClick, true);
                                        el.click();
                                        el.addEventListener('click', onClick, true);
                                    }
                                });
                            };

                            el.addEventListener('click', onClick, true);

                            cleanup(() => {
                                el.removeEventListener('click', onClick, true);
                            });
                        });
                    });
                    window._swalListenersAdded = true;
                }
            })();
        </script>
    </body>
</html>
