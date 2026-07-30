<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? __($title).' - '.config('app.name', 'Laravel-adminX') : config('app.name', 'Laravel-adminX') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

@php
    $themeColor = \App\Models\SystemSetting::get('theme_color', 'emerald');
    $palettes = [
        'emerald' => ['brand' => '#059669', 'hover' => '#047857', 'accent' => '#10b981', 'light' => '#d1fae5', 'dark' => '#064e3b'],
        'indigo'  => ['brand' => '#4f46e5', 'hover' => '#4338ca', 'accent' => '#6366f1', 'light' => '#e0e7ff', 'dark' => '#312e81'],
        'rose'    => ['brand' => '#e11d48', 'hover' => '#be123c', 'accent' => '#f43f5e', 'light' => '#ffe4e6', 'dark' => '#881337'],
        'amber'   => ['brand' => '#d97706', 'hover' => '#b45309', 'accent' => '#f59e0b', 'light' => '#fef3c7', 'dark' => '#78350f'],
        'violet'  => ['brand' => '#7c3aed', 'hover' => '#6d28d9', 'accent' => '#8b5cf6', 'light' => '#ede9fe', 'dark' => '#4c1d95'],
        'teal'    => ['brand' => '#0d9488', 'hover' => '#0f766e', 'accent' => '#14b8a6', 'light' => '#ccfbf1', 'dark' => '#134e4a'],
    ];

    $customColor = \App\Models\SystemSetting::get('theme_custom_color');
    if ($themeColor === 'custom' && !empty($customColor)) {
        $colors = ['brand' => $customColor, 'hover' => $customColor, 'accent' => $customColor, 'light' => '#f3f4f6', 'dark' => '#111827'];
    } else {
        $colors = $palettes[$themeColor] ?? $palettes['emerald'];
    }
@endphp
<style>
    .swal2-popup { border-radius: 12px; font-family: inherit; }
    .swal2-title { font-size: 1.125rem; font-weight: 700; padding: 0 0 0.25rem; }
    .swal2-actions { margin-top: 1rem; gap: 0.5rem; }
    .swal2-icon { border-color: var(--theme-brand); color: var(--theme-brand); }
    .swal2-icon-content { color: var(--theme-brand); }
    .swal2-warning { border-color: var(--theme-brand); }
    .swal2-styled.swal2-cancel { background-color: #f4f4f5 !important; color: #27272a !important; border: 1px solid #e4e4e7 !important; font-weight: 500; box-shadow: none !important; }
    .dark .swal2-styled.swal2-cancel { background-color: #3f3f46 !important; color: #e4e4e7 !important; border-color: #52525b !important; }
</style>
<style>
    [data-flux-modal] > dialog::backdrop { background: rgba(0,0,0,0.55) !important; }
    .dark [data-flux-modal] > dialog::backdrop { background: rgba(0,0,0,0.75) !important; }
    input[type="checkbox"] { accent-color: var(--theme-brand); width: 1.1rem; height: 1.1rem; cursor: pointer; }
</style>
<style id="system-theme-style">
    :root {
        --theme-brand: {{ $colors['brand'] }};
        --theme-brand-hover: {{ $colors['hover'] }};
        --theme-brand-accent: {{ $colors['accent'] }};
        --theme-brand-light: {{ $colors['light'] }};
        --theme-brand-dark: {{ $colors['dark'] }};
    }
</style>
