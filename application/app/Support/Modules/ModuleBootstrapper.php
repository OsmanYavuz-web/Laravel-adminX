<?php

namespace App\Support\Modules;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;

/**
 * Registers a single module's views, translations, migrations,
 * Livewire page namespace and routes into the running application.
 */
class ModuleBootstrapper extends ServiceProvider
{
    public function boot(Module $module): void
    {
        $this->registerViews($module);
        $this->registerLivewire($module);
        $this->registerLivewireComponents($module);
        $this->registerTranslations($module);
        $this->registerMigrations($module);
        $this->registerRoutes($module);
    }

    protected function registerViews(Module $module): void
    {
        $views = $module->path('resources/views');

        if (! is_dir($views)) {
            return;
        }

        $this->loadViewsFrom($views, $module->name());

        $this->callAfterResolving('blade.compiler', function ($blade) use ($views, $module) {
            $blade->anonymousComponentPath($views, $module->name());
        });
    }

    protected function registerLivewire(Module $module): void
    {
        $pages = $module->path('resources/views/pages');

        if (! is_dir($pages)) {
            return;
        }

        Livewire::addNamespace($module->name(), $pages, $this->classNamespace($module).'\\Livewire\\Pages');
    }

    protected function registerLivewireComponents(Module $module): void
    {
        $components = $module->path('Livewire/Components');

        if (! is_dir($components)) {
            return;
        }

        $namespace = $this->classNamespace($module).'\\Livewire\\Components';

        foreach (glob($components.'/*.php') ?: [] as $file) {
            $class = $namespace.'\\'.basename($file, '.php');

            if (is_subclass_of($class, Component::class)) {
                Livewire::component($module->name().'.components.'.Str::kebab(basename($file, '.php')), $class);
            }
        }
    }

    protected function classNamespace(Module $module): string
    {
        $relative = Str::startsWith($module->path(), app_path())
            ? Str::after($module->path(), app_path().DIRECTORY_SEPARATOR)
            : 'Modules/'.Str::studly($module->name());

        return 'App\\'.str_replace(DIRECTORY_SEPARATOR, '\\', $relative);
    }

    protected function registerTranslations(Module $module): void
    {
        $lang = $module->path('resources/lang');

        if (is_dir($lang)) {
            // PHP array files (e.g. validation messages) are namespaced.
            $this->loadTranslationsFrom($lang, $module->name());

            // JSON files are merged into the global lookup so plain __()
            // calls in module views resolve from the module's own files.
            $this->app->make('translator')->addJsonPath($lang);
        }
    }

    protected function registerMigrations(Module $module): void
    {
        $migrations = $module->path('database/migrations');

        if (is_dir($migrations)) {
            $this->loadMigrationsFrom($migrations);
        }
    }

    protected function registerRoutes(Module $module): void
    {
        $routes = $module->get('routes');

        if (! is_string($routes) || ! is_file($routes)) {
            return;
        }

        $prefix = config('modules.prefix') ?: config('fortify.prefix', 'adminx');
        $middleware = array_merge(['web'], (array) config('modules.middleware', ['web', 'auth', 'verified']));

        Route::prefix($prefix)->middleware($middleware)->group(function () use ($routes, $module) {
            require $routes;
        });

        // Route names are fluent (->name() applied after the route is added to the
        // collection), so the collection's name lookup table must be rebuilt.
        Route::getRoutes()->refreshNameLookups();
    }
}
