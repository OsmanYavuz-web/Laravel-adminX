<?php

namespace App\Providers;

use App\Livewire\NotificationBell;
use App\Models\ActivityLog;
use App\Models\Language;
use App\Models\SystemSetting;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Features;

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

        app('livewire')->component('notification-bell', NotificationBell::class);

        // Implicitly grant 'super-admin' role all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        // Register toSystemFormat Carbon Macro
        Carbon::macro('toSystemFormat', function () {
            /** @var Carbon $this */
            $format = SystemSetting::get('date_format', 'd.m.Y H:i');

            return $this->format($format);
        });

        // Record Auth events (Login, Logout, Failed)
        Event::listen(Login::class, function ($event) {
            ActivityLog::record(
                event: 'login',
                description: 'Kullanıcı sisteme başarılı şekilde giriş yaptı.',
                user: $event->user
            );
        });

        Event::listen(Logout::class, function ($event) {
            if ($event->user) {
                ActivityLog::record(
                    event: 'logout',
                    description: 'Kullanıcı oturumunu kapattı.',
                    user: $event->user
                );
            }
        });

        Event::listen(Failed::class, function ($event) {
            ActivityLog::record(
                event: 'failed_login',
                description: 'Başarısız giriş denemesi yapıldı: '.($event->credentials['email'] ?? 'bilinmiyor'),
                user: $event->user
            );
        });

        if (! app()->runningInConsole() && Schema::hasTable('system_settings')) {
            $appName = SystemSetting::get('app_name');
            if (! empty($appName)) {
                config(['app.name' => $appName]);
            }

            $tz = SystemSetting::get('timezone');
            if (! empty($tz)) {
                config(['app.timezone' => $tz]);
                date_default_timezone_set($tz);
            }

            $allowReg = SystemSetting::get('allow_registration');
            if ($allowReg !== null && ! filter_var($allowReg, FILTER_VALIDATE_BOOLEAN)) {
                config(['fortify.features' => array_filter(config('fortify.features', []), fn ($f) => $f !== Features::registration())]);
            }
        }

        // Override available_locales from database-driven Language model
        if (! app()->runningInConsole() && Schema::hasTable('languages')) {
            $dbLocales = Language::getActiveKeyed();
            if (! empty($dbLocales)) {
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
