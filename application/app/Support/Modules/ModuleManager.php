<?php

namespace App\Support\Modules;

use App\Models\Permission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Spatie\Permission\PermissionRegistrar;

/**
 * Discovers and manages all modules living in app/Modules.
 *
 * Used by the sidebar (navigation), the roles page (permission groups),
 * the permission sync command and the service provider (boot).
 */
class ModuleManager
{
    /** @var array<string, Module> */
    protected array $modules = [];

    public function __construct()
    {
        $this->discover();
    }

    protected function discover(): void
    {
        $path = config('modules.path', app_path('Modules'));

        $directories = File::directories($path);

        foreach ($directories as $directory) {
            $manifestPath = $directory.DIRECTORY_SEPARATOR.'module.php';

            if (! is_file($manifestPath)) {
                continue;
            }

            $manifest = require $manifestPath;

            if (! is_array($manifest) || empty($manifest['name'])) {
                continue;
            }

            $this->modules[$manifest['name']] = new Module(
                $manifest['name'],
                $directory,
                $manifest,
            );
        }

        uasort($this->modules, fn (Module $a, Module $b) => $a->priority() <=> $b->priority());
    }

    /**
     * All discovered modules, sorted by priority.
     *
     * @return Collection<int, Module>
     */
    public function all(): Collection
    {
        return collect(array_values($this->modules));
    }

    /**
     * Enabled modules, sorted by priority.
     *
     * @return Collection<int, Module>
     */
    public function enabled(): Collection
    {
        return $this->all()->filter(fn (Module $module) => $module->isEnabled())->values();
    }

    public function get(string $name): ?Module
    {
        return $this->modules[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->modules[$name]);
    }

    /**
     * Flat list of sidebar sections contributed by all enabled modules.
     * Each item is tagged with its owning module and already resolved
     * (title, color, active patterns).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function navigation(): Collection
    {
        $sections = collect();

        foreach ($this->enabled() as $module) {
            foreach ($module->menu() as $item) {
                $sections->push($item);
            }
        }

        return $sections
            ->sortBy(fn (array $item) => (int) ($item['order'] ?? 0))
            ->values();
    }

    /**
     * All permission definitions of every enabled module.
     *
     * @return array<string, array<string, string>>
     */
    public function permissions(): array
    {
        $permissions = [];

        foreach ($this->enabled() as $module) {
            $permissions = array_merge($permissions, $module->permissions());
        }

        return $permissions;
    }

    /**
     * Metadata used to group permissions on the roles page:
     * prefix => ['title', 'icon', 'color'].
     *
     * @return array<string, array{title: string, icon: string, color: string}>
     */
    public function permissionGroups(): array
    {
        $groups = [];

        foreach ($this->enabled() as $module) {
            foreach ($module->permissionGroups() as $prefix => $meta) {
                $groups[$prefix] ??= $meta;
            }
        }

        return $groups;
    }

    /**
     * Upsert permission rows (with translatable display names) for all
     * enabled modules. Never deletes stale permissions.
     */
    public function syncPermissions(): void
    {
        foreach ($this->permissions() as $name => $translations) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            $permission->setTranslations('display_name', $translations);
            $permission->save();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Boot every enabled module: view/lang/migration loading, Livewire
     * page namespace and route registration.
     */
    public function boot(): void
    {
        foreach ($this->enabled() as $module) {
            app(ModuleBootstrapper::class)->boot($module);
        }
    }
}
