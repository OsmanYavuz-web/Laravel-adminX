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
<style id="system-theme-style">
    :root {
        --theme-brand: {{ $colors['brand'] }};
        --theme-brand-hover: {{ $colors['hover'] }};
        --theme-brand-accent: {{ $colors['accent'] }};
        --theme-brand-light: {{ $colors['light'] }};
        --theme-brand-dark: {{ $colors['dark'] }};
    }
</style>
