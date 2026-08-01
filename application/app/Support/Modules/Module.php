<?php

namespace App\Support\Modules;

use Illuminate\Support\Str;

/**
 * A value object wrapping a module manifest (a "module.php" file).
 */
class Module
{
    /** @var array<string, mixed> */
    protected array $manifest;

    /**
     * @param  array<string, mixed>  $manifest
     */
    public function __construct(
        protected string $name,
        protected string $path,
        array $manifest,
    ) {
        $this->manifest = $manifest;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function path(string $sub = ''): string
    {
        return $sub === '' ? $this->path : $this->path.DIRECTORY_SEPARATOR.$sub;
    }

    /**
     * @return array<string, mixed>
     */
    public function manifest(): array
    {
        return $this->manifest;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->manifest);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->manifest[$key] ?? $default;
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->manifest['enabled'] ?? true);
    }

    public function priority(): int
    {
        return (int) ($this->manifest['priority'] ?? 500);
    }

    public function icon(): string
    {
        return (string) ($this->manifest['icon'] ?? 'folder');
    }

    public function color(): string
    {
        return (string) ($this->manifest['color'] ?? 'brand');
    }

    /**
     * Resolve a translatable value: either a plain string or an
     * array keyed by locale (['tr' => '...', 'en' => '...']).
     */
    public function trans(string $key, string $fallback = ''): string
    {
        $value = $this->manifest[$key] ?? $fallback;

        if (is_array($value)) {
            $locale = app()->getLocale();

            if (isset($value[$locale]) && $value[$locale] !== '') {
                return (string) $value[$locale];
            }

            $fallbackLocale = config('app.fallback_locale', 'en');
            if (isset($value[$fallbackLocale]) && $value[$fallbackLocale] !== '') {
                return (string) $value[$fallbackLocale];
            }

            $first = reset($value);

            return is_string($first) ? $first : $fallback;
        }

        return (string) $value;
    }

    public function title(): string
    {
        return $this->trans('title', Str::headline($this->name));
    }

    public function description(): string
    {
        return $this->trans('description', '');
    }

    /**
     * All permission definitions of this module.
     *
     * @return array<string, array<string, string>>
     */
    public function permissions(): array
    {
        return (array) ($this->manifest['permissions'] ?? []);
    }

    /**
     * Menu sections of this module, with titles resolved and module
     * metadata attached (used by the sidebar).
     *
     * @return array<int, array<string, mixed>>
     */
    public function menu(): array
    {
        $menu = (array) ($this->manifest['menu'] ?? []);
        $resolved = [];

        foreach ($menu as $item) {
            $resolved[] = $this->resolveMenuItem($item);
        }

        usort($resolved, fn ($a, $b) => (int) ($a['order'] ?? 0) <=> (int) ($b['order'] ?? 0));

        return $resolved;
    }

    /**
     * Group metadata used by the roles/permissions page.
     *
     * @return array<string, array{title: string, icon: string, color: string}>
     */
    public function permissionGroups(): array
    {
        $declared = (array) ($this->manifest['groups'] ?? []);

        $groups = [];

        foreach ($declared as $key => $meta) {
            $groups[$key] = [
                'title' => $this->resolveLabel($meta['title'] ?? null, Str::headline($key)),
                'icon' => (string) ($meta['icon'] ?? $this->icon()),
                'color' => (string) ($meta['color'] ?? $this->color()),
            ];
        }

        // Any permission prefix that is not declared explicitly falls
        // back to the module itself.
        foreach (array_keys($this->permissions()) as $permission) {
            $prefix = explode('.', $permission)[0];

            if (! isset($groups[$prefix])) {
                $groups[$prefix] = [
                    'title' => $this->title(),
                    'icon' => $this->icon(),
                    'color' => $this->color(),
                ];
            }
        }

        return $groups;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function resolveMenuItem(array $item): array
    {
        $item['module'] = $this->name;
        $item['title'] = $this->resolveLabel($item['title'] ?? null, Str::headline($item['route'] ?? $this->name));
        $item['color'] = (string) ($item['color'] ?? $this->color());
        $item['order'] = (int) ($item['order'] ?? 0);
        $item['active'] = (array) ($item['active'] ?? [$item['route'] ?? null]);

        if (isset($item['children'])) {
            $item['children'] = array_map(fn (array $child) => $this->resolveMenuItem($child), $item['children']);
        }

        return $item;
    }

    protected function resolveLabel(mixed $value, string $fallback): string
    {
        if (is_array($value)) {
            $locale = app()->getLocale();

            if (isset($value[$locale]) && $value[$locale] !== '') {
                return (string) $value[$locale];
            }

            $first = reset($value);

            return is_string($first) && $first !== '' ? $first : $fallback;
        }

        return is_string($value) && $value !== '' ? $value : $fallback;
    }
}
