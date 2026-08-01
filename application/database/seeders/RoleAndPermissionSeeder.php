<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Modules\ModuleManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permission definitions now live inside the module manifests
        // (app/Modules/*/module.php). This syncs them into the database
        // together with their translatable display names.
        app(ModuleManager::class)->syncPermissions();

        // Standard Universal System Roles
        $rolesData = [
            'super-admin' => ['tr' => 'Süper Yönetici', 'en' => 'Super Admin'],
            'admin' => ['tr' => 'Yönetici', 'en' => 'Admin'],
            'manager' => ['tr' => 'Yönetici Yardımcısı', 'en' => 'Manager'],
            'user' => ['tr' => 'Kullanıcı', 'en' => 'User'],
        ];

        foreach ($rolesData as $name => $displayName) {
            $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            $role->setTranslations('display_name', $displayName);
            $role->save();
        }

        // Assign permissions to roles
        $superAdmin = Role::findByName('super-admin', 'web');
        $superAdmin->givePermissionTo(Permission::all());

        $adminRole = Role::findByName('admin', 'web');
        $adminRole->givePermissionTo(Permission::all());

        $managerRole = Role::findByName('manager', 'web');
        $managerRole->givePermissionTo([
            'users.view',
            'media.view',
            'media.create',
            'logs.view',
            'backups.view',
            // ExcaCoin
            'dictionaries.view',
            'dictionaries.create',
            'dictionaries.update',
            'excavation_projects.view',
            'excavation_projects.create',
            'excavation_projects.update',
            'finds.view',
            'finds.create',
            'finds.update',
            'finds.export',
            'coins.view',
            'coins.create',
            'coins.update',
            'coins.export',
        ]);

        $userRole = Role::findByName('user', 'web');
        $userRole->givePermissionTo([
            'media.view',
            // ExcaCoin — yalnızca görüntüleme
            'dictionaries.view',
            'excavation_projects.view',
            'finds.view',
            'coins.view',
        ]);

        // Assign Super Admin role to main admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'locale' => 'tr',
            ]
        );
        $adminUser->syncRoles([$superAdmin]);
    }
}
