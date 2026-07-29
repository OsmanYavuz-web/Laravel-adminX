<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Core system permissions
        $permissions = [
            // User Management
            'users.view'     => ['tr' => 'Kullanıcıları Görüntüle', 'en' => 'View Users'],
            'users.create'   => ['tr' => 'Kullanıcı Oluştur', 'en' => 'Create User'],
            'users.update'   => ['tr' => 'Kullanıcı Düzenle', 'en' => 'Update User'],
            'users.delete'   => ['tr' => 'Kullanıcı Sil', 'en' => 'Delete User'],
            // Role Management
            'roles.view'     => ['tr' => 'Rolleri Görüntüle', 'en' => 'View Roles'],
            'roles.create'   => ['tr' => 'Rol Oluştur', 'en' => 'Create Role'],
            'roles.update'   => ['tr' => 'Rol Düzenle', 'en' => 'Update Role'],
            'roles.delete'   => ['tr' => 'Rol Sil', 'en' => 'Delete Role'],
            // Settings Management
            'settings.view'   => ['tr' => 'Sistem Ayarlarını Görüntüle', 'en' => 'View System Settings'],
            'settings.update' => ['tr' => 'Sistem Ayarlarını Düzenle', 'en' => 'Update System Settings'],
            // Languages
            'languages.view'   => ['tr' => 'Dilleri Görüntüle', 'en' => 'View Languages'],
            'languages.create' => ['tr' => 'Dil Oluştur', 'en' => 'Create Language'],
            'languages.update' => ['tr' => 'Dil Düzenle', 'en' => 'Update Language'],
            'languages.delete' => ['tr' => 'Dil Sil', 'en' => 'Delete Language'],
            // Activity Logs
            'logs.view'     => ['tr' => 'Etkinlik Günlüğünü Görüntüle', 'en' => 'View Activity Logs'],
            'logs.delete'   => ['tr' => 'Eski Logları Temizle', 'en' => 'Purge Activity Logs'],
            // Backups Management
            'backups.view'   => ['tr' => 'Yedeklemeleri Görüntüle', 'en' => 'View Backups'],
            'backups.create' => ['tr' => 'Yedek Oluştur', 'en' => 'Create Backup'],
            'backups.delete' => ['tr' => 'Yedek Sil', 'en' => 'Delete Backup'],
            // Media Library
            'media.view'     => ['tr' => 'Medya Kütüphanesini Görüntüle', 'en' => 'View Media Library'],
            'media.create'   => ['tr' => 'Medya Yükle', 'en' => 'Upload Media'],
            'media.delete'   => ['tr' => 'Medya Sil', 'en' => 'Delete Media'],
        ];

        foreach ($permissions as $name => $displayName) {
            $perm = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            $perm->setTranslations('display_name', $displayName);
            $perm->save();
        }

        // Standard Universal System Roles
        $rolesData = [
            'super-admin' => ['tr' => 'Süper Yönetici', 'en' => 'Super Admin'],
            'admin'       => ['tr' => 'Yönetici', 'en' => 'Admin'],
            'manager'     => ['tr' => 'Yönetici Yardımcısı', 'en' => 'Manager'],
            'user'        => ['tr' => 'Kullanıcı', 'en' => 'User'],
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
            'users.view', 'media.view', 'media.create', 'logs.view', 'backups.view',
        ]);

        $userRole = Role::findByName('user', 'web');
        $userRole->givePermissionTo([
            'media.view',
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
