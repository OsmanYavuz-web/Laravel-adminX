<?php

/*
|--------------------------------------------------------------------------
| System Module Manifest
|--------------------------------------------------------------------------
|
| Core panel module: dashboard, user management, roles, settings,
| media library and system pages. It cannot be disabled.
|
*/

return [
    'name' => 'system',

    'title' => [
        'tr' => 'Sistem',
        'en' => 'System',
    ],

    'description' => [
        'tr' => 'Panelin çekirdek yönetim sayfaları.',
        'en' => 'Core panel management pages.',
    ],

    'enabled' => true,

    'priority' => 1,

    'icon' => 'cog-6-tooth',

    'color' => 'brand',

    'permissions' => [
        // User Management
        'users.view' => ['tr' => 'Kullanıcıları Görüntüle', 'en' => 'View Users'],
        'users.create' => ['tr' => 'Kullanıcı Oluştur', 'en' => 'Create User'],
        'users.update' => ['tr' => 'Kullanıcı Düzenle', 'en' => 'Update User'],
        'users.delete' => ['tr' => 'Kullanıcı Sil', 'en' => 'Delete User'],
        // Role Management
        'roles.view' => ['tr' => 'Rolleri Görüntüle', 'en' => 'View Roles'],
        'roles.create' => ['tr' => 'Rol Oluştur', 'en' => 'Create Role'],
        'roles.update' => ['tr' => 'Rol Düzenle', 'en' => 'Update Role'],
        'roles.delete' => ['tr' => 'Rol Sil', 'en' => 'Delete Role'],
        // Settings Management
        'settings.view' => ['tr' => 'Sistem Ayarlarını Görüntüle', 'en' => 'View System Settings'],
        'settings.update' => ['tr' => 'Sistem Ayarlarını Düzenle', 'en' => 'Update System Settings'],
        // Languages
        'languages.view' => ['tr' => 'Dilleri Görüntüle', 'en' => 'View Languages'],
        'languages.create' => ['tr' => 'Dil Oluştur', 'en' => 'Create Language'],
        'languages.update' => ['tr' => 'Dil Düzenle', 'en' => 'Update Language'],
        'languages.delete' => ['tr' => 'Dil Sil', 'en' => 'Delete Language'],
        // Activity Logs
        'logs.view' => ['tr' => 'Etkinlik Günlüğünü Görüntüle', 'en' => 'View Activity Logs'],
        'logs.delete' => ['tr' => 'Eski Logları Temizle', 'en' => 'Purge Activity Logs'],
        // Backups Management
        'backups.view' => ['tr' => 'Yedeklemeleri Görüntüle', 'en' => 'View Backups'],
        'backups.create' => ['tr' => 'Yedek Oluştur', 'en' => 'Create Backup'],
        'backups.delete' => ['tr' => 'Yedek Sil', 'en' => 'Delete Backup'],
        // Media Library
        'media.view' => ['tr' => 'Medya Kütüphanesini Görüntüle', 'en' => 'View Media Library'],
        'media.create' => ['tr' => 'Medya Yükle', 'en' => 'Upload Media'],
        'media.delete' => ['tr' => 'Medya Sil', 'en' => 'Delete Media'],
    ],

    'groups' => [
        'users' => ['title' => ['tr' => 'Kullanıcı Yönetimi', 'en' => 'User Management'], 'icon' => 'users', 'color' => 'indigo'],
        'roles' => ['title' => ['tr' => 'Roller & Yetkiler', 'en' => 'Roles & Permissions'], 'icon' => 'shield-check', 'color' => 'indigo'],
        'settings' => ['title' => ['tr' => 'Sistem Ayarları', 'en' => 'System Settings'], 'icon' => 'adjustments-horizontal', 'color' => 'brand'],
        'languages' => ['title' => ['tr' => 'Diller', 'en' => 'Languages'], 'icon' => 'language', 'color' => 'brand'],
        'logs' => ['title' => ['tr' => 'Etkinlik Günlükleri', 'en' => 'Activity Logs'], 'icon' => 'clipboard-document-list', 'color' => 'brand'],
        'backups' => ['title' => ['tr' => 'Yedek Yönetimi', 'en' => 'Backups Management'], 'icon' => 'archive-box', 'color' => 'brand'],
        'media' => ['title' => ['tr' => 'Medya Kütüphanesi', 'en' => 'Media Library'], 'icon' => 'photo', 'color' => 'pink'],
    ],

    'menu' => [
        [
            'type' => 'link',
            'title' => ['tr' => 'Genel Bakış', 'en' => 'Dashboard'],
            'icon' => 'home',
            'color' => 'brand',
            'route' => 'dashboard',
            'active' => ['dashboard'],
            'order' => 10,
        ],
        [
            'type' => 'link',
            'title' => ['tr' => 'Medya Kütüphanesi', 'en' => 'Media Library'],
            'icon' => 'photo',
            'color' => 'pink',
            'route' => 'media.index',
            'permission' => 'media.view',
            'active' => ['media.index'],
            'order' => 70,
        ],
        [
            'type' => 'group',
            'title' => ['tr' => 'Kullanıcı Yönetimi', 'en' => 'User Management'],
            'icon' => 'users',
            'color' => 'indigo',
            'permission' => ['users.view', 'roles.view'],
            'order' => 80,
            'children' => [
                [
                    'title' => ['tr' => 'Kullanıcılar', 'en' => 'Users'],
                    'icon' => 'user-group',
                    'route' => 'users.index',
                    'permission' => 'users.view',
                    'active' => ['users.index'],
                ],
                [
                    'title' => ['tr' => 'Roller & Yetkiler', 'en' => 'Roles & Permissions'],
                    'icon' => 'shield-check',
                    'route' => 'roles.index',
                    'permission' => 'roles.view',
                    'active' => ['roles.index'],
                ],
            ],
        ],
        [
            'type' => 'group',
            'title' => ['tr' => 'Ayarlar', 'en' => 'Settings'],
            'icon' => 'cog-6-tooth',
            'color' => 'brand',
            'permission' => ['settings.view', 'languages.view'],
            'order' => 90,
            'children' => [
                [
                    'title' => ['tr' => 'Sistem Ayarları', 'en' => 'System Settings'],
                    'icon' => 'adjustments-horizontal',
                    'route' => 'settings.system',
                    'permission' => 'settings.view',
                    'active' => ['settings.system'],
                ],
                [
                    'title' => ['tr' => 'Diller', 'en' => 'Languages'],
                    'icon' => 'language',
                    'route' => 'settings.languages',
                    'permission' => 'languages.view',
                    'active' => ['settings.languages'],
                ],
            ],
        ],
        [
            'type' => 'group',
            'title' => ['tr' => 'Sistem', 'en' => 'System'],
            'icon' => 'server-stack',
            'color' => 'brand',
            'permission' => ['logs.view', 'backups.view', 'settings.view'],
            'order' => 100,
            'children' => [
                [
                    'title' => ['tr' => 'Etkinlik Günlükleri', 'en' => 'Activity Logs'],
                    'icon' => 'clipboard-document-list',
                    'route' => 'settings.logs',
                    'permission' => 'logs.view',
                    'active' => ['settings.logs'],
                ],
                [
                    'title' => ['tr' => 'Yedek Yönetimi', 'en' => 'Backups Management'],
                    'icon' => 'archive-box',
                    'route' => 'settings.backups',
                    'permission' => 'backups.view',
                    'active' => ['settings.backups'],
                ],
                [
                    'title' => ['tr' => 'Sistem Bilgisi', 'en' => 'System Information'],
                    'icon' => 'server-stack',
                    'route' => 'settings.system-info',
                    'permission' => 'settings.view',
                    'active' => ['settings.system-info'],
                ],
            ],
        ],
    ],

    'routes' => __DIR__.'/routes/web.php',
];
