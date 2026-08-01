<?php

/*
|--------------------------------------------------------------------------
| ExcaCoin Module Manifest
|--------------------------------------------------------------------------
|
| Numismatic coin and archaeological find management: quick data entry,
| dictionaries, excavation projects, finds and coins.
|
*/

return [
    'name' => 'exca-coin',

    'title' => [
        'tr' => 'ExcaCoin',
        'en' => 'ExcaCoin',
    ],

    'description' => [
        'tr' => 'Nümismatik sikke ve arkeolojik buluntu yönetimi.',
        'en' => 'Numismatic coin and archaeological find management.',
    ],

    'enabled' => true,

    'priority' => 10,

    'icon' => 'circle-stack',

    'color' => 'amber',

    'permissions' => [
        'quick_entry.access' => ['tr' => 'Hızlı Veri Girişine Eriş', 'en' => 'Access Quick Data Entry'],
        'dictionaries.view' => ['tr' => 'Sözlükleri Görüntüle', 'en' => 'View Dictionaries'],
        'dictionaries.create' => ['tr' => 'Sözlük Ekle', 'en' => 'Create Dictionary Entry'],
        'dictionaries.update' => ['tr' => 'Sözlük Düzenle', 'en' => 'Update Dictionary Entry'],
        'dictionaries.delete' => ['tr' => 'Sözlük Sil', 'en' => 'Delete Dictionary Entry'],
        'excavation_projects.view' => ['tr' => 'Kazı Projelerini Görüntüle', 'en' => 'View Excavation Projects'],
        'excavation_projects.create' => ['tr' => 'Kazı Projesi Oluştur', 'en' => 'Create Excavation Project'],
        'excavation_projects.update' => ['tr' => 'Kazı Projesi Düzenle', 'en' => 'Update Excavation Project'],
        'excavation_projects.delete' => ['tr' => 'Kazı Projesi Sil', 'en' => 'Delete Excavation Project'],
        'finds.view' => ['tr' => 'Buluntuları Görüntüle', 'en' => 'View Finds'],
        'finds.create' => ['tr' => 'Buluntu Ekle', 'en' => 'Create Find'],
        'finds.update' => ['tr' => 'Buluntu Düzenle', 'en' => 'Update Find'],
        'finds.delete' => ['tr' => 'Buluntu Sil', 'en' => 'Delete Find'],
        'finds.export' => ['tr' => 'Buluntuları Dışa Aktar', 'en' => 'Export Finds'],
        'coins.view' => ['tr' => 'Sikkeleri Görüntüle', 'en' => 'View Coins'],
        'coins.create' => ['tr' => 'Sikke Ekle', 'en' => 'Create Coin'],
        'coins.update' => ['tr' => 'Sikke Düzenle', 'en' => 'Update Coin'],
        'coins.delete' => ['tr' => 'Sikke Sil', 'en' => 'Delete Coin'],
        'coins.export' => ['tr' => 'Sikkeleri Dışa Aktar', 'en' => 'Export Coins'],
    ],

    'groups' => [
        'quick_entry' => [
            'title' => ['tr' => 'Hızlı Veri Girişi', 'en' => 'Quick Data Entry'],
            'icon' => 'bolt',
            'color' => 'amber',
        ],
        'dictionaries' => [
            'title' => ['tr' => 'Nümismatik Sözlükler', 'en' => 'Numismatic Dictionaries'],
            'icon' => 'book-open',
            'color' => 'purple',
        ],
        'excavation_projects' => [
            'title' => ['tr' => 'Kazı Projeleri', 'en' => 'Excavation Projects'],
            'icon' => 'map-pin',
            'color' => 'amber',
        ],
        'finds' => [
            'title' => ['tr' => 'Buluntular', 'en' => 'Finds'],
            'icon' => 'archive-box',
            'color' => 'blue',
        ],
        'coins' => [
            'title' => ['tr' => 'Sikkeler', 'en' => 'Coins'],
            'icon' => 'circle-stack',
            'color' => 'amber',
        ],
    ],

    'menu' => [
        [
            'type' => 'link',
            'title' => ['tr' => 'Hızlı Veri Girişi', 'en' => 'Quick Data Entry'],
            'icon' => 'bolt',
            'color' => 'amber',
            'solid' => true,
            'route' => 'quick-entry.index',
            'permission' => 'quick_entry.access',
            'active' => ['quick-entry.*'],
            'order' => 20,
        ],
        [
            'type' => 'link',
            'title' => ['tr' => 'Kazı Projeleri', 'en' => 'Excavation Projects'],
            'icon' => 'map-pin',
            'color' => 'amber',
            'route' => 'excavation-projects.index',
            'permission' => 'excavation_projects.view',
            'active' => ['excavation-projects.*'],
            'order' => 30,
        ],
        [
            'type' => 'link',
            'title' => ['tr' => 'Tüm Buluntular', 'en' => 'All Finds'],
            'icon' => 'archive-box',
            'color' => 'blue',
            'route' => 'all-finds.index',
            'permission' => 'finds.view',
            'active' => ['all-finds.*', 'finds.*'],
            'order' => 40,
        ],
        [
            'type' => 'link',
            'title' => ['tr' => 'Tüm Sikkeler', 'en' => 'All Coins'],
            'icon' => 'circle-stack',
            'color' => 'amber',
            'route' => 'all-coins.index',
            'permission' => 'coins.view',
            'active' => ['all-coins.*', 'coins.*'],
            'order' => 50,
        ],
        [
            'type' => 'link',
            'title' => ['tr' => 'Nümismatik Sözlükler', 'en' => 'Numismatic Dictionaries'],
            'icon' => 'book-open',
            'color' => 'purple',
            'route' => 'dictionaries.index',
            'permission' => 'dictionaries.view',
            'active' => ['dictionaries.*'],
            'order' => 60,
        ],
    ],

    'routes' => __DIR__.'/routes/web.php',
];
