<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        app('livewire')->component('notification-bell', \App\Livewire\NotificationBell::class);

        // Implicitly grant 'super-admin' role all permissions
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        // Register toSystemFormat Carbon Macro
        \Illuminate\Support\Carbon::macro('toSystemFormat', function () {
            /** @var \Illuminate\Support\Carbon $this */
            $format = \App\Models\SystemSetting::get('date_format', 'd.m.Y H:i');
            return $this->format($format);
        });

        // Record Auth events (Login, Logout, Failed)
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            \App\Models\ActivityLog::record(
                event: 'login',
                description: 'Kullanıcı sisteme başarılı şekilde giriş yaptı.',
                user: $event->user
            );
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            if ($event->user) {
                \App\Models\ActivityLog::record(
                    event: 'logout',
                    description: 'Kullanıcı oturumunu kapattı.',
                    user: $event->user
                );
            }
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Failed::class, function ($event) {
            \App\Models\ActivityLog::record(
                event: 'failed_login',
                description: 'Başarısız giriş denemesi yapıldı: ' . ($event->credentials['email'] ?? 'bilinmiyor'),
                user: $event->user
            );
        });

        if (!app()->runningInConsole() && \Illuminate\Support\Facades\Schema::hasTable('system_settings')) {
            $appName = \App\Models\SystemSetting::get('app_name');
            if (!empty($appName)) {
                config(['app.name' => $appName]);
            }

            $tz = \App\Models\SystemSetting::get('timezone');
            if (!empty($tz)) {
                config(['app.timezone' => $tz]);
                date_default_timezone_set($tz);
            }

            $allowReg = \App\Models\SystemSetting::get('allow_registration');
            if ($allowReg !== null && !filter_var($allowReg, FILTER_VALIDATE_BOOLEAN)) {
                config(['fortify.features' => array_filter(config('fortify.features', []), fn($f) => $f !== \Laravel\Fortify\Features::registration())]);
            }
        }

        // Override available_locales from database-driven Language model
        if (!app()->runningInConsole() && \Illuminate\Support\Facades\Schema::hasTable('languages')) {
            $dbLocales = \App\Models\Language::getActiveKeyed();
            if (!empty($dbLocales)) {
                config(['app.available_locales' => $dbLocales]);
            }
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
