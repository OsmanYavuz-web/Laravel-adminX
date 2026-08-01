<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Support\Modules\ModuleManager;
use Illuminate\Console\Command;

class ModuleSyncCommand extends Command
{
    protected $signature = 'modules:sync';

    protected $description = 'Sync module permissions into the permissions table';

    public function handle(ModuleManager $manager): int
    {
        $before = Permission::count();

        $manager->syncPermissions();

        $created = max(0, Permission::count() - $before);

        $this->components->info(sprintf(
            'Synced %d module permission(s) (%d created, %d updated).',
            count($manager->permissions()),
            $created,
            count($manager->permissions()) - $created,
        ));

        return self::SUCCESS;
    }
}
