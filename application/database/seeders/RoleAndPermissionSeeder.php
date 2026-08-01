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
            // ExcaCoin — Sözlükler
            'dictionaries.view'   => ['tr' => 'Sözlükleri Görüntüle', 'en' => 'View Dictionaries'],
            'dictionaries.create' => ['tr' => 'Sözlük Ekle', 'en' => 'Create Dictionary Entry'],
            'dictionaries.update' => ['tr' => 'Sözlük Düzenle', 'en' => 'Update Dictionary Entry'],
            'dictionaries.delete' => ['tr' => 'Sözlük Sil', 'en' => 'Delete Dictionary Entry'],
            // ExcaCoin — Kazı Projeleri
            'excavation_projects.view'   => ['tr' => 'Kazı Projelerini Görüntüle', 'en' => 'View Excavation Projects'],
            'excavation_projects.create' => ['tr' => 'Kazı Projesi Oluştur', 'en' => 'Create Excavation Project'],
            'excavation_projects.update' => ['tr' => 'Kazı Projesi Düzenle', 'en' => 'Update Excavation Project'],
            'excavation_projects.delete' => ['tr' => 'Kazı Projesi Sil', 'en' => 'Delete Excavation Project'],
            // ExcaCoin — Buluntular
            'finds.view'   => ['tr' => 'Buluntuları Görüntüle', 'en' => 'View Finds'],
            'finds.create' => ['tr' => 'Buluntu Ekle', 'en' => 'Create Find'],
            'finds.update' => ['tr' => 'Buluntu Düzenle', 'en' => 'Update Find'],
            'finds.delete' => ['tr' => 'Buluntu Sil', 'en' => 'Delete Find'],
            // ExcaCoin — Sikkeler
            'coins.view'   => ['tr' => 'Sikkeleri Görüntüle', 'en' => 'View Coins'],
            'coins.create' => ['tr' => 'Sikke Ekle', 'en' => 'Create Coin'],
            'coins.update' => ['tr' => 'Sikke Düzenle', 'en' => 'Update Coin'],
            'coins.delete' => ['tr' => 'Sikke Sil', 'en' => 'Delete Coin'],
            'coins.export' => ['tr' => 'Sikkeleri Dışa Aktar', 'en' => 'Export Coins'],
            'finds.export' => ['tr' => 'Buluntuları Dışa Aktar', 'en' => 'Export Finds'],
            // ExcaCoin — Hızlı Veri Girişi
            'quick_entry.access' => ['tr' => 'Hızlı Veri Girişine Eriş', 'en' => 'Access Quick Data Entry'],
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
            // ExcaCoin
            'dictionaries.view', 'dictionaries.create', 'dictionaries.update',
            'excavation_projects.view', 'excavation_projects.create', 'excavation_projects.update',
            'finds.view', 'finds.create', 'finds.update', 'finds.export',
            'coins.view', 'coins.create', 'coins.update', 'coins.export',
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
