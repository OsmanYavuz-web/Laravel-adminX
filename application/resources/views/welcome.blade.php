<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel-adminX') }} - {{ __('Dashboard') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100 flex flex-col justify-between antialiased">
        <!-- Navigation Bar -->
        <header class="w-full max-w-7xl mx-auto px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 border border-brand/20 text-brand dark:text-brand-accent shadow-xs">
                    <x-app-logo-icon class="size-6" />
                </span>
                <span class="font-bold text-lg tracking-tight text-zinc-900 dark:text-white">
                    {{ config('app.name', 'Laravel-adminX') }}
                </span>
            </div>

            <div class="flex items-center gap-4">
                <!-- Language Switcher -->
                <div class="flex items-center gap-1 text-xs font-semibold bg-zinc-200/60 dark:bg-zinc-900/80 p-1 rounded-lg border border-zinc-300/50 dark:border-zinc-800">
                    @foreach(config('app.available_locales', []) as $code => $locale)
                        <a href="/locale/{{ $code }}" class="px-2.5 py-1 rounded-md transition-all {{ app()->getLocale() === $code ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-xs' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white' }}">
                            {{ $locale['flag'] }} {{ $locale['code'] }}
                        </a>
                    @endforeach
                </div>

                @if (Route::has('login'))
                    <nav class="flex items-center gap-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm font-medium rounded-lg bg-brand hover:bg-brand-hover text-white transition-colors shadow-xs">
                                {{ __('Dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium rounded-lg text-zinc-700 dark:text-zinc-200 hover:bg-zinc-200/60 dark:hover:bg-zinc-800 transition-colors">
                                {{ __('Log in') }}
                            </a>

                            @if (filter_var(\App\Models\SystemSetting::get('allow_registration', true), FILTER_VALIDATE_BOOLEAN) && Route::has('register'))
                                <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-medium rounded-lg bg-brand hover:bg-brand-hover text-white transition-colors shadow-xs">
                                    {{ __('Register') }}
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </div>
        </header>

        <!-- Main Hero Content -->
        <main class="flex-1 flex items-center justify-center p-6">
            <div class="max-w-3xl w-full text-center space-y-6 my-auto">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand/10 border border-brand/20 text-brand dark:text-brand-accent text-xs font-semibold">
                    <x-app-logo-icon class="size-4" />
                    <span>{{ __('Modern Admin Panel') }}</span>
                </div>

                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-zinc-900 dark:text-white leading-tight">
                    {{ config('app.name', 'Laravel-adminX') }}
                </h1>

                <p class="text-base md:text-lg text-zinc-600 dark:text-zinc-400 max-w-xl mx-auto leading-relaxed">
                    {{ __('A powerful, modern, and extensible base admin boilerplate built with Laravel, Livewire 3, Alpine.js, and Tailwind CSS.') }}
                </p>

                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-6 py-3 text-sm font-semibold rounded-xl bg-brand hover:bg-brand-hover text-white transition-all shadow-md flex items-center justify-center gap-2">
                            <span>{{ __('Go to Control Panel') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="w-full sm:w-auto px-6 py-3 text-sm font-semibold rounded-xl bg-brand hover:bg-brand-hover text-white transition-all shadow-md flex items-center justify-center gap-2">
                            <span>{{ __('Log in') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        @if (filter_var(\App\Models\SystemSetting::get('allow_registration', true), FILTER_VALIDATE_BOOLEAN) && Route::has('register'))
                            <a href="{{ route('register') }}" class="w-full sm:w-auto px-6 py-3 text-sm font-semibold rounded-xl bg-zinc-200 dark:bg-zinc-800 hover:bg-zinc-300 dark:hover:bg-zinc-700 text-zinc-900 dark:text-white transition-all">
                                {{ __('Register') }}
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full max-w-7xl mx-auto px-6 py-6 text-center text-xs text-zinc-500 dark:text-zinc-500">
            &copy; {{ date('Y') }} {{ config('app.name', 'Laravel-adminX') }}. {{ __('All rights reserved.') }}
        </footer>
    </body>
</html>
