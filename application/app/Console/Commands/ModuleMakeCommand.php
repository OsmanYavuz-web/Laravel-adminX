<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ModuleMakeCommand extends Command
{
    protected $signature = 'make:module
        {name : Module name, e.g. Blog or blog}
        {--force : Overwrite existing files}';

    protected $description = 'Create a new panel module skeleton';

    public function handle(Filesystem $files): int
    {
        $name = Str::studly($this->argument('name'));

        if ($name === 'System') {
            $this->error('"System" is reserved for the core panel module.');

            return self::FAILURE;
        }

        $slug = Str::snake($name, '-');
        $base = app_path('Modules/'.$name);

        if ($files->exists($base.'/module.php') && ! $this->option('force')) {
            $this->error("Module [{$name}] already exists.");

            return self::FAILURE;
        }

        $stubs = [
            'module.php' => $base.'/module.php',
            'routes/web.php' => $base.'/routes/web.php',
            'resources/views/pages/{{slug}}/index.blade.php' => $base.'/resources/views/pages/'.$slug.'/index.blade.php',
            'resources/lang/tr.json' => $base.'/resources/lang/tr.json',
            'resources/lang/en.json' => $base.'/resources/lang/en.json',
        ];

        foreach ($stubs as $stub => $target) {
            $files->ensureDirectoryExists(dirname($target));
            $files->put($target, $this->populate($files->get($this->stubPath($stub)), $name, $slug));
        }

        $this->components->info("Module [{$name}] created successfully.");
        $this->components->bulletList([
            'Manifest: '.$base.'/module.php',
            'Register menu entries, permissions and translations in the manifest.',
            'Run "php artisan modules:sync" to sync the new permissions into the database.',
        ]);

        return self::SUCCESS;
    }

    protected function stubPath(string $file): string
    {
        return __DIR__.'/stubs/module/'.$file.'.stub';
    }

    protected function populate(string $stub, string $name, string $slug): string
    {
        return str_replace(
            ['{{name}}', '{{slug}}', '{{permission}}'],
            [$name, $slug, Str::replace('-', '_', $slug)],
            $stub,
        );
    }
}
