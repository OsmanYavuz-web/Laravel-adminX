<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\SystemSetting;
use Flux\Flux;

new #[Title('System Settings')] #[Layout('layouts.app')] class extends Component {
    public bool $allowRegistration = true;
    public string $defaultLocale = 'tr';
    public string $dateFormat = 'd.m.Y H:i';
    public string $timezone = 'Europe/Istanbul';
    public string $appName = 'Laravel-adminX';
    public string $themeColor = 'emerald';
    public string $themeCustomColor = '#059669';
    public int $maxUploadSize = 20;
    public string $allowedFileTypes = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('settings.view'), 403);
        $this->allowRegistration = filter_var(SystemSetting::get('allow_registration', true), FILTER_VALIDATE_BOOLEAN);
        $this->defaultLocale = SystemSetting::get('default_locale', 'tr');
        $this->dateFormat = SystemSetting::get('date_format', 'd.m.Y H:i');
        $this->timezone = SystemSetting::get('timezone', config('app.timezone', 'Europe/Istanbul'));
        $this->appName = SystemSetting::get('app_name', config('app.name', 'Laravel-adminX'));
        $this->themeColor = SystemSetting::get('theme_color', 'emerald');
        $this->themeCustomColor = SystemSetting::get('theme_custom_color', '#059669');
        $this->maxUploadSize = (int) SystemSetting::get('max_upload_size', 20);
        $this->allowedFileTypes = SystemSetting::get('allowed_file_types', 'jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,txt,xlsx,csv,zip,rar,7z,tar,gz');
    }

    public function saveSystemSettings(): void
    {
        abort_unless(auth()->user()->can('settings.update'), 403);

        $this->validate([
            'appName' => ['required', 'string', 'max:255'],
            'defaultLocale' => ['required', 'string', \Illuminate\Validation\Rule::in(array_keys(config('app.available_locales', [])))],
            'dateFormat' => ['required', 'string'],
            'timezone' => ['required', 'string'],
            'themeColor' => ['required', 'string'],
            'themeCustomColor' => ['nullable', 'string', 'max:20'],
            'allowRegistration' => ['required', 'boolean'],
            'maxUploadSize' => ['required', 'integer', 'min:1', 'max:512'],
            'allowedFileTypes' => ['required', 'string', 'regex:/^[a-z0-9,]+$/'],
        ], [
            'allowedFileTypes.regex' => __('File types must be comma-separated extensions only (e.g. jpg,png,pdf).'),
        ]);

        SystemSetting::set('allow_registration', $this->allowRegistration ? 'true' : 'false', 'general');
        SystemSetting::set('default_locale', $this->defaultLocale, 'localization');
        SystemSetting::set('date_format', $this->dateFormat, 'localization');
        SystemSetting::set('timezone', $this->timezone, 'localization');
        SystemSetting::set('app_name', $this->appName, 'general');
        SystemSetting::set('theme_color', $this->themeColor, 'theme');
        SystemSetting::set('theme_custom_color', $this->themeCustomColor, 'theme');
        SystemSetting::set('max_upload_size', (string) $this->maxUploadSize, 'media');
        SystemSetting::set('allowed_file_types', $this->allowedFileTypes, 'media');

        // Dynamically apply timezone for current session
        config(['app.timezone' => $this->timezone]);
        date_default_timezone_set($this->timezone);

        Flux::toast(variant: 'success', text: __('System settings updated successfully.'));
    }
}; ?>
<div>
    <div class="space-y-6 w-full">
        <div>
            <flux:heading size="xl" class="font-extrabold tracking-tight">{{ __('System Settings') }}</flux:heading>
            <flux:subheading>{{ __('Manage system-wide configurations, localization, and access rules.') }}</flux:subheading>
        </div>

        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-xs">
            <form wire:submit="saveSystemSettings" class="space-y-6">
                <flux:input wire:model="appName" :label="__('Application Name')" icon="building-office" required />

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:select wire:model="defaultLocale" :label="__('Default System Language')">
                        @foreach(config('app.available_locales', []) as $code => $loc)
                            <flux:select.option value="{{ $code }}">{{ $loc['flag'] }} {{ __($loc['name']) }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="dateFormat" :label="__('Default Date Format')">
                        <flux:select.option value="d.m.Y H:i">29.07.2026 23:07 (TR / EU)</flux:select.option>
                        <flux:select.option value="Y-m-d H:i">2026-07-29 23:07 (ISO)</flux:select.option>
                        <flux:select.option value="m/d/Y h:i A">07/29/2026 11:07 PM (US)</flux:select.option>
                        <flux:select.option value="d F Y H:i">29 Temmuz 2026 23:07</flux:select.option>
                    </flux:select>

                    <flux:select wire:model="timezone" :label="__('System Timezone')">
                        <flux:select.option value="Europe/Istanbul">Europe/Istanbul (GMT+3)</flux:select.option>
                        <flux:select.option value="UTC">UTC (Universal Coordinated Time)</flux:select.option>
                        <flux:select.option value="Europe/London">Europe/London (GMT+0 / BST)</flux:select.option>
                        <flux:select.option value="Europe/Berlin">Europe/Berlin (GMT+1 / CEST)</flux:select.option>
                        <flux:select.option value="America/New_York">America/New_York (EST / EDT)</flux:select.option>
                        <flux:select.option value="Asia/Dubai">Asia/Dubai (GMT+4)</flux:select.option>
                        <flux:select.option value="Asia/Tokyo">Asia/Tokyo (GMT+9)</flux:select.option>
                    </flux:select>
                </div>

                <flux:separator />

                {{-- Primary Theme Color Selector --}}
                <div class="space-y-4">
                    <div>
                        <flux:heading size="lg">{{ __('Application Theme Color') }}</flux:heading>
                        <flux:subheading class="text-xs">{{ __('Choose the primary brand theme color used across buttons, badges, and accents.') }}</flux:subheading>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                        {{-- Emerald --}}
                        <label wire:click="$set('themeColor', 'emerald')" class="p-3 rounded-2xl border flex flex-col items-center gap-2 cursor-pointer transition-all {{ $themeColor === 'emerald' ? 'border-emerald-500 bg-emerald-500/10 ring-2 ring-emerald-500/30 font-bold' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-400' }}">
                            <div class="size-6 rounded-full bg-[#059669] shadow-xs"></div>
                            <span class="text-xs text-zinc-800 dark:text-zinc-200">{{ __('Emerald') }}</span>
                        </label>

                        {{-- Indigo --}}
                        <label wire:click="$set('themeColor', 'indigo')" class="p-3 rounded-2xl border flex flex-col items-center gap-2 cursor-pointer transition-all {{ $themeColor === 'indigo' ? 'border-indigo-500 bg-indigo-500/10 ring-2 ring-indigo-500/30 font-bold' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-400' }}">
                            <div class="size-6 rounded-full bg-[#4f46e5] shadow-xs"></div>
                            <span class="text-xs text-zinc-800 dark:text-zinc-200">{{ __('Indigo') }}</span>
                        </label>

                        {{-- Rose --}}
                        <label wire:click="$set('themeColor', 'rose')" class="p-3 rounded-2xl border flex flex-col items-center gap-2 cursor-pointer transition-all {{ $themeColor === 'rose' ? 'border-rose-500 bg-rose-500/10 ring-2 ring-rose-500/30 font-bold' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-400' }}">
                            <div class="size-6 rounded-full bg-[#e11d48] shadow-xs"></div>
                            <span class="text-xs text-zinc-800 dark:text-zinc-200">{{ __('Rose') }}</span>
                        </label>

                        {{-- Amber --}}
                        <label wire:click="$set('themeColor', 'amber')" class="p-3 rounded-2xl border flex flex-col items-center gap-2 cursor-pointer transition-all {{ $themeColor === 'amber' ? 'border-amber-500 bg-amber-500/10 ring-2 ring-amber-500/30 font-bold' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-400' }}">
                            <div class="size-6 rounded-full bg-[#d97706] shadow-xs"></div>
                            <span class="text-xs text-zinc-800 dark:text-zinc-200">{{ __('Amber') }}</span>
                        </label>

                        {{-- Violet --}}
                        <label wire:click="$set('themeColor', 'violet')" class="p-3 rounded-2xl border flex flex-col items-center gap-2 cursor-pointer transition-all {{ $themeColor === 'violet' ? 'border-violet-500 bg-violet-500/10 ring-2 ring-violet-500/30 font-bold' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-400' }}">
                            <div class="size-6 rounded-full bg-[#7c3aed] shadow-xs"></div>
                            <span class="text-xs text-zinc-800 dark:text-zinc-200">{{ __('Violet') }}</span>
                        </label>

                        {{-- Teal --}}
                        <label wire:click="$set('themeColor', 'teal')" class="p-3 rounded-2xl border flex flex-col items-center gap-2 cursor-pointer transition-all {{ $themeColor === 'teal' ? 'border-teal-500 bg-teal-500/10 ring-2 ring-teal-500/30 font-bold' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-400' }}">
                            <div class="size-6 rounded-full bg-[#0d9488] shadow-xs"></div>
                            <span class="text-xs text-zinc-800 dark:text-zinc-200">{{ __('Teal') }}</span>
                        </label>
                    </div>

                    @if($themeColor === 'custom')
                        <div class="pt-2">
                            <flux:input wire:model="themeCustomColor" type="color" :label="__('Custom Hex Color')" />
                        </div>
                    @endif
                </div>

                <flux:separator />

                {{-- Media Upload Settings --}}
                <div class="space-y-4">
                    <div>
                        <flux:heading size="lg">{{ __('Media Upload Settings') }}</flux:heading>
                        <flux:subheading class="text-xs">{{ __('Configure file upload limits and allowed file types for the Media Library.') }}</flux:subheading>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input wire:model="allowedFileTypes" :label="__('Allowed File Types')" icon="document-text" description="jpg,jpeg,png,pdf,zip,..." />
                        <flux:input wire:model="maxUploadSize" type="number" min="1" max="512" :label="__('Maximum Upload Size (MB)')" description="Maximum single file upload size in megabytes" />
                    </div>
                </div>

                <flux:separator />

                <div class="space-y-3">
                    <flux:heading size="lg">{{ __('Access & Registration') }}</flux:heading>

                    <div class="flex items-center justify-between p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/40">
                        <div>
                            <div class="font-medium text-sm text-zinc-900 dark:text-white">{{ __('Public User Registration') }}</div>
                            <div class="text-xs text-zinc-500">{{ __('When disabled, new users cannot register publicly from the login page.') }}</div>
                        </div>
                        <flux:switch wire:model="allowRegistration" />
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <flux:button variant="primary" type="submit" icon="check" class="bg-brand hover:bg-brand-hover text-white cursor-pointer px-6">
                        {{ __('Save Settings') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
