<?php

namespace App\Providers;

use App\Support\Modules\ModuleBootstrapper;
use App\Support\Modules\ModuleManager;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register the ModuleManager singleton and configuration.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(config_path('modules.php'), 'modules');

        $this->app->singleton(ModuleManager::class);

        $this->app->bind(ModuleBootstrapper::class, fn () => new ModuleBootstrapper($this->app));
    }

    /**
     * Boot all enabled modules (views, translations, migrations,
     * Livewire namespaces and routes).
     */
    public function boot(): void
    {
        $this->app->make(ModuleManager::class)->boot();
    }
}
