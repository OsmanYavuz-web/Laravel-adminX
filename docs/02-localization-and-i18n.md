# 02 — Localization & Internationalization (i18n) Guide

**Laravel-adminX** implements strict Laravel i18n standards for seamless multi-language support.

---

## 🌍 Core i18n Rules & Standards

### 1. English Source Keys in Code
All Blade templates and Livewire components MUST use clean **English string keys** inside the `__('...')` helper function:

```blade
<!-- Correct -->
<flux:heading>{{ __('System Settings') }}</flux:heading>
<flux:input :label="__('Email address')" :placeholder="__('email@example.com')" />

<!-- Incorrect (Do NOT use hardcoded Turkish strings inside code) -->
<flux:heading>{{ __('Sistem Ayarları') }}</flux:heading>
```

### 2. Translation Files (`lang/tr.json` & `lang/en.json`)
Translations are stored in JSON dictionaries under `lang/`:

- **`lang/tr.json`**:
```json
{
    "System Settings": "Sistem Ayarları",
    "Email address": "E-posta Adresi",
    "email@example.com": "eposta@ornek.com"
}
```

- **`lang/en.json`**:
```json
{
    "System Settings": "System Settings",
    "Email address": "Email Address",
    "email@example.com": "email@example.com"
}
```

---

## 🕒 System Timezone & Date Formatting (`toSystemFormat()`)

All timestamps across the application (activity logs, backup dates, media uploads) use the global `toSystemFormat()` Carbon macro.

### How it Works:
1. Administrators select **Default Date Format** and **System Timezone** in `/adminx/settings/system`.
2. Carbon instances format automatically:

```php
// Example usage in Blade templates
{{ \Carbon\Carbon::createFromTimestamp($media['created_at'])->toSystemFormat() }}
```

Output based on settings:
- `29.07.2026 23:07` (TR / EU)
- `2026-07-29 23:07` (ISO)
- `07/29/2026 11:07 PM` (US)

---

## 🔤 Switching Locales

Users can switch languages via top navbar pill selectors or the `/locale/{lang}` route.
Logged-in user locale preferences are saved to `users.locale`.
