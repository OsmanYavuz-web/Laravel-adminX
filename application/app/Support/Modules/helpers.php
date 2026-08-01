<?php

use App\Support\Modules\Module;
use App\Support\Modules\ModuleManager;

if (! function_exists('modules')) {
    /**
     * Resolve the ModuleManager singleton.
     */
    function modules(): ModuleManager
    {
        return app(ModuleManager::class);
    }
}

if (! function_exists('module')) {
    /**
     * Resolve a module by its name.
     */
    function module(string $name): ?Module
    {
        return modules()->get($name);
    }
}

if (! function_exists('nav_colors')) {
    /**
     * Accent color maps for the dynamic module navigation.
     * Class strings stay literal here so Tailwind can detect them
     * (the app/Support directory is registered as a CSS source).
     *
     * @return array{soft: array<string, string>, solid: array<string, string>, title: array<string, string>}
     */
    function nav_colors(): array
    {
        return [
            'soft' => [
                'brand' => 'bg-brand/15 text-brand dark:text-brand-accent',
                'amber' => 'bg-amber-500/15 text-amber-600 dark:text-amber-400',
                'blue' => 'bg-blue-500/15 text-blue-600 dark:text-blue-400',
                'purple' => 'bg-purple-500/15 text-purple-600 dark:text-purple-400',
                'pink' => 'bg-pink-500/15 text-pink-600 dark:text-pink-400',
                'indigo' => 'bg-indigo-500/15 text-indigo-600 dark:text-indigo-400',
                'green' => 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400',
                'red' => 'bg-red-500/15 text-red-600 dark:text-red-400',
                'orange' => 'bg-orange-500/15 text-orange-600 dark:text-orange-400',
                'teal' => 'bg-teal-500/15 text-teal-600 dark:text-teal-400',
                'cyan' => 'bg-cyan-500/15 text-cyan-600 dark:text-cyan-400',
                'zinc' => 'bg-zinc-500/15 text-zinc-600 dark:text-zinc-400',
            ],
            'solid' => [
                'brand' => 'bg-brand text-white',
                'amber' => 'bg-amber-500 text-white',
                'blue' => 'bg-blue-500 text-white',
                'purple' => 'bg-purple-500 text-white',
                'pink' => 'bg-pink-500 text-white',
                'indigo' => 'bg-indigo-500 text-white',
                'green' => 'bg-emerald-500 text-white',
                'red' => 'bg-red-500 text-white',
                'orange' => 'bg-orange-500 text-white',
                'teal' => 'bg-teal-500 text-white',
                'cyan' => 'bg-cyan-500 text-white',
                'zinc' => 'bg-zinc-500 text-white',
            ],
            'title' => [
                'brand' => 'text-brand dark:text-brand-accent',
                'amber' => 'text-amber-700 dark:text-amber-400',
                'blue' => 'text-blue-700 dark:text-blue-400',
                'purple' => 'text-purple-700 dark:text-purple-400',
                'pink' => 'text-pink-700 dark:text-pink-400',
                'indigo' => 'text-indigo-700 dark:text-indigo-400',
                'green' => 'text-emerald-700 dark:text-emerald-400',
                'red' => 'text-red-700 dark:text-red-400',
                'orange' => 'text-orange-700 dark:text-orange-400',
                'teal' => 'text-teal-700 dark:text-teal-400',
                'cyan' => 'text-cyan-700 dark:text-cyan-400',
                'zinc' => 'text-zinc-700 dark:text-zinc-400',
            ],
        ];
    }
}
