<?php

namespace App\Console\Commands;

use App\Support\Modules\ModuleManager;
use Illuminate\Console\Command;

class ModuleListCommand extends Command
{
    protected $signature = 'modules:list';

    protected $description = 'List all discovered panel modules';

    public function handle(ModuleManager $manager): int
    {
        $rows = $manager->all()->map(fn ($module) => [
            'name' => $module->name(),
            'title' => $module->title(),
            'enabled' => $module->isEnabled() ? '<info>yes</info>' : '<fg=red>no</fg=red>',
            'priority' => $module->priority(),
            'permissions' => count($module->permissions()),
        ])->all();

        $this->table(['Name', 'Title', 'Enabled', 'Priority', 'Permissions'], $rows);

        return self::SUCCESS;
    }
}
