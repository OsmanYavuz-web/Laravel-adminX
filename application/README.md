# Laravel-adminX — Modül Geliştirme Rehberi

Bu proje, panel modüllerini `app/Modules/` altında **bildirimsel manifest** (Approach A) yaklaşımıyla yönetir.
Her modül kendi manifesti, rotaları, modelleri, migration'ları, view'ları ve çevirileriyle tek bir dizinde yaşar.

Mevcut modüller:

| Modül | Slug | İçerik |
| --- | --- | --- |
| `System` | `system` | Dashboard, kullanıcılar, roller, ayarlar (çekirdek panel) |
| `ExcaCoin` | `exca-coin` | Hızlı veri girişi, nümismatik sözlükler, kazı projeleri, buluntular, sikkeler |

---

## Mimarı

- **`config/modules.php`** — modül kök dizini, admin prefix ve middleware grubu.
- **`app/Support/Modules/ModuleManager.php`** — modülleri dizin tarayarak keşfeder; sidebar navigasyonunu, rol/izin gruplarını, izin senkronizasyonunu üretir.
- **`app/Support/Modules/Module.php`** — tek bir manifesti saran değer nesnesi (`title()`, `permissions()`, `menu()`, `permissionGroups()`...).
- **`app/Support/Modules/ModuleBootstrapper.php`** — bir modülün view/Livewire/lang/migration namespace'lerini ve rotalarını uygulamaya kaydeder.
- **`app/Support/Modules/helpers.php`** — `modules()`, `module()`, `nav_colors()` global yardımcıları.
- **`app/Providers/ModuleServiceProvider.php`** — keşfedilen tüm modülleri boot eder.
- **Komutlar** — `make:module`, `modules:list`, `modules:sync`.

### Keşif kuralları

- `app/Modules/` altındaki her alt dizin, içinde `module.php` manifesti varsa modüldür.
- Manifestteki `name` zorunludur; dizin adıyla birebir aynı olmalıdır (örn. `ExcaCoin/` ↔ `exca-coin`).
- Modüller `priority` alanına göre sıralanır (düşük önce).
- `enabled => false` modülü tamamen gizler (rota, menü, izinler).

### Yardımcı fonksiyonlar

```php
modules()                  // ModuleManager singleton
modules()->all()           // tüm modüller (Module koleksiyonu)
modules()->enabled()       // aktif modüller
modules()->navigation()    // sidebar bölümleri (link + group)
modules()->permissions()   // tüm izin tanımları
modules()->permissionGroups() // rol sayfası grupları
modules()->syncPermissions()  // izinleri DB'ye yazar
module('exca-coin')        // tek modül çeker (null dönebilir)
nav_colors()               // sidebar ikon renkleri (soft/solid/title)
```

---

## Yeni Modül Oluşturma

```bash
php artisan make:module Blog
```

Bu komut `app/Modules/Blog/` altında şu iskeleti üretir:

```
app/Modules/Blog/
├── module.php                      ← manifest (menü, izinler, çeviriler)
├── routes/web.php                  ← admin prefix'inde yüklenen rotalar
├── resources/
│   ├── views/pages/blog/index.blade.php   ← örnek sayfa view'ı
│   └── lang/tr.json + en.json ← modül çevirileri (JSON)
└── database/migrations/            ← (elle oluşturulur)
```

Oluşturduktan sonra:

```bash
php artisan modules:sync   # manifestteki izinleri DB'ye yazar
php artisan modules:list   # keşfi doğrular
```

`System` adı ayrılmıştır ve kullanılamaz.

---

## Manifest Şeması (`module.php`)

```php
<?php

return [
    'name' => 'blog',   // zorunlu, dizin adıyla aynı (kebab-case)

    'title' => [                // string veya locale dizisi
        'tr' => 'Blog',
        'en' => 'Blog',
    ],

    'description' => [
        'tr' => 'Blog modülü.',
        'en' => 'The Blog module.',
    ],

    'enabled' => true,          // false → modül tamamen gizlenir

    'priority' => 500,          // roller sayfası sıralaması (System: 1)

    'icon' => 'folder',         // Flux ikon adı
    'color' => 'brand',         // brand|amber|blue|purple|pink|indigo|green|red|orange|teal|cyan|zinc

    'permissions' => [
        'blog.view' => ['tr' => 'Blog Görüntüle', 'en' => 'View Blog'],
        'blog.create' => ['tr' => 'Blog Oluştur', 'en' => 'Create Blog'],
        // prefix.action kalıbı zorunludur; roller sayfası prefix'e göre gruplar
    ],

    'groups' => [   // isteğe bağlı: rol sayfası grup görünümünü ezmek için
        'blog' => [
            'title' => ['tr' => 'Blog', 'en' => 'Blog'],
            'icon' => 'folder',
            'color' => 'brand',
        ],
    ],

    'menu' => [
        // ... link ve group öğeleri (aşağıya bakınız)
    ],

    'routes' => __DIR__.'/routes/web.php',
];
```

### Menu öğeleri

**`link`** — tek sayfa bağlantısı:

```php
[
    'type' => 'link',
    'title' => ['tr' => 'Tüm Yazılar', 'en' => 'All Posts'],
    'icon' => 'folder',
    'color' => 'brand',
    'solid' => false,            // isteğe bağlı, dolu renkli ikon
    'route' => 'blog.index',     // zorunlu — rotanın adı
    'permission' => 'blog.view', // string veya dizi (canAny)
    'active' => ['blog.*'],      // aktif durum için rota kalıpları
    'order' => 20,               // sidebar'daki global sıra
]
```

**`group`** — açılır alt menü:

```php
[
    'type' => 'group',
    'title' => ['tr' => 'Yazılar', 'en' => 'Posts'],
    'icon' => 'folder',
    'color' => 'brand',
    'permission' => ['blog.view', 'blog.create'], // canAny; boşsa herkese görünür
    'order' => 30,
    'children' => [
        ['title' => ['tr' => 'Tüm Yazılar', 'en' => 'All Posts'], 'route' => 'blog.index', 'icon' => 'folder'],
        ['title' => ['tr' => 'Kategoriler', 'en' => 'Categories'], 'route' => 'blog.categories.index'],
    ],
]
```

Grup yalnızca yetkili en az bir çocuk öğesi varsa görünür.

---

## Rotalar ve Livewire Sayfaları

Modülün `routes/web.php` dosyası `{prefix}/` altında `web + auth + verified` middleware grubuyla yüklenir.
`Route::livewire()` kullanırken component adı **modül namespace'i** ile yazılır:

```php
use Illuminate\Support\Facades\Route;

Route::livewire('posts', 'blog::posts')->name('blog.index');
Route::livewire('posts/create', 'blog::posts.create')->name('blog.create');
```

`blog::posts` ismi şu sınıfa çözülür:

```
App\Modules\Blog\Livewire\Pages\Posts\Index
```

Çözüm kuralları (`ModuleBootstrapper::registerLivewire`):

- `blog::posts` → `App\Modules\Blog\Livewire\Pages\Posts\Index.php` (Index sınıfı)
- `blog::posts.create` → `App\Modules\Blog\Livewire\Pages\Posts\Create.php`
- Yani `Livewire/Pages/` altındaki her alt dizin bir sayfa, `Index.php` dizinin kendisi olarak kullanılır.

Sayfa sınıfları view'larını modül namespace'iyle render eder:

```php
public function render()
{
    return view('blog::pages.posts.index')
        ->layout('layouts.app', ['title' => __('Yazılar')]);
}
```

### SFC (tek dosyalı) sayfalar

`resources/views/pages/` altına doğrudan `.blade.php` SFC dosyası koyabilirsiniz; aynı namespace altından erişilir:
`resources/views/pages/blog/index.blade.php` → `blog::blog.index` (veya `blog::blog`).

---

## Yeniden Kullanılabilir Livewire Component'leri

`Livewire/Components/` altındaki sınıflar otomatik kaydedilir (`ModuleBootstrapper::registerLivewireComponents`):

- Dosya: `app/Modules/Blog/Livewire/Components/PostPicker.php`
- Component adı: `blog.components.post-picker`
- Kullanım: `<livewire:blog.components.post-picker />`
- View: `resources/views/livewire/components/post-picker.blade.php` → `view('blog::livewire.components.post-picker')`

---

## View'lar

- `resources/views/` → view namespace `blog::` olarak kaydedilir (`loadViewsFrom`).
- `render()` içinde `view('blog::pages.xxx')`, `@include('blog::pages.xxx._form')` şeklinde kullanılır.
- Layout (`layouts.app`) globaldir, modüle taşınmaz.

## Çeviriler

- `resources/lang/{locale}.json` → Bootstrapper `Translator::addJsonPath()` ile modül JSON'larını global aramaya katar; modül view'ları düz `__('metin')` kullanmaya devam eder ve anahtarlar modülün kendi dosyasından çözülür.
- PHP array dosyaları (`lang/{tr,en}/messages.php` gibi) varsa namespaced erişilir: `blog::messages.anahtar`.
- JSON anahtarları modül dışında benzersiz olmalıdır (çakışan anahtarda modül JSON'u global'e üstün gelir).
- Modülden taşınan çeviriler global `lang/*.json` dosyalarından temizlenmiştir; modüle ait anahtarların tek kaynağı modüldür.
- Manifest içi çeviriler (`title`, `description`, izin adları) doğrudan manifestte locale dizisi olarak tutulur; `Module::trans()` ve `resolveLabel()` ile çözülür. Burada da düz string yerine `['tr' => ..., 'en' => ...]` kullanın — düz string her dilde aynı görüntülenir.

## Migration'lar

- `database/migrations/` otomatik yüklenir (`loadMigrationsFrom`).
- Migration'lar buraya taşınırken dosya adları ve sıraları korunmalıdır (birbirine FK bağımlılıkları var).

## Modeller ve Policy'ler

- Modeller: `app/Modules/Blog/Models/` → namespace `App\Modules\Blog\Models`
- Policy'ler: `app/Modules/Blog/Policies/` → namespace `App\Modules\Blog\Policies`
- Laravel policy otomatik keşfi, model namespace'inde `Models` → `Policies` dönüşümünü yapar; ekstra kayıt gerekmez.
- **Dikkat:** `User` gibi global modelleri import etmeden kullanmayın — modül namespace'i artık `App\Models` olmadığı için `User::class` çözümlenemez. Her zaman `use App\Models\User;` yazın.

---

## Komutlar

```bash
php artisan make:module Blog         # yeni modül iskeleti
php artisan modules:list             # keşfedilen modüller + izin sayıları
php artisan modules:sync             # manifest izinlerini DB'ye upsert eder
```

---

## Testler

Modül sistemi `tests/Feature/Modules/ModuleSystemTest.php` ile test edilir (keşif, menü, izin grupları, senkronizasyon, `web` middleware'i, `make:module`).

```bash
php artisan test --filter=ModuleSystemTest
```

Önemli püf noktaları:

- `$this->artisan(...)->assertSuccessful()` komutu **çalıştırmaz**; `PendingCommand::run()` kullanın.
- Modül rotaları service provider'dan kayıt edildiği için `web` grubunu (session, CSRF) manuel almazlar; `config/modules.php` içindeki `middleware` grubu bunu garanti eder.
- Route cache kullanıyorsanız (`route:cache`), modül rotaları değişince cache'i yenileyin.
