<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - {{ __('Unauthorized Access') }} | {{ config('app.name', 'Laravel-adminX') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-50 dark:bg-zinc-900 flex items-center justify-center p-6 text-zinc-800 dark:text-zinc-200">
    <div class="max-w-md w-full text-center space-y-6">
        {{-- Icon Badge --}}
        <div class="inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-red-500/10 text-red-500 dark:text-red-400 border border-red-500/20 shadow-xs">
            <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </div>

        {{-- Text Content --}}
        <div class="space-y-2">
            <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">
                403 - {{ __('Unauthorized Access') }}
            </h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">
                {{ __('You do not have permission to view this page. Please contact your system administrator.') }}
            </p>
        </div>

        {{-- Go Back Button --}}
        <div class="pt-4">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-brand hover:bg-brand-hover text-white font-semibold text-sm shadow-xs transition-colors cursor-pointer">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                <span>{{ __('Return to Dashboard') }}</span>
            </a>
        </div>
    </div>
</body>
</html>
