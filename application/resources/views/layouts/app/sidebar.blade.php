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
                @php
                    $navSections = app(\App\Support\Modules\ModuleManager::class)->navigation();
                @endphp
                @foreach($navSections as $navSection)
                    @if(($navSection['type'] ?? 'link') === 'group')
                        @include('partials.module-nav.group', ['item' => $navSection])
                    @else
                        @include('partials.module-nav.link', ['item' => $navSection])
                    @endif
                @endforeach
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
