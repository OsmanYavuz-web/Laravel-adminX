# ExcaCoin — Fazlı Implementasyon Planı (Livewire + Flux UI)

## Stack

| Katman | Teknoloji |
|---|---|
| Backend | **Laravel 13** (PHP 8.3+) |
| Frontend & UI | **Livewire v3** + **Flux UI v2** (Livewire Starter Kit) |
| CSS | **Tailwind CSS v4** (Flux UI gereksinimidir) |
| Rol/Yetki | **spatie/laravel-permission** + Livewire Bileşeni |
| Fotoğraf / Medya | **spatie/laravel-medialibrary** |
| Çoklu Dil | **spatie/laravel-translatable** |
| Veritabanı | **SQLite** (geliştirme) → MySQL (üretim) |
| Export | **maatwebsite/excel** |

> [!NOTE]
> **Flux UI**, Tailwind CSS v4 üzerine kurulu Livewire component kütüphanesidir. Laravel'in resmi Livewire Starter Kit'i ile birlikte gelir.
> `livewire/flux` ayrı bir Pro lisansı gerektirir. Starter Kit kurulumunda ücretsiz olarak dahil edilir.

---

## 🚀 FAZ 1: Altyapı, Paketler & Temel Panel

> [!IMPORTANT]
> **Hedef:** Laravel + Livewire Starter Kit (Flux UI dahil) kurulumu, tüm paketlerin yüklenmesi, migration'ların çalıştırılması ve `/login` üzerinden giriş yapılabilir bir Flux UI paneli elde etmek.

### Adımlar

1. **Proje Oluşturma** (Livewire Starter Kit ile — Flux UI otomatik dahil olur):
   ```bash
   laravel new application --kit=livewire --database=sqlite
   ```
   > Bu komut; Laravel 13 + Livewire v3 + Flux UI v2 + Tailwind CSS v4 + Auth sayfalarını otomatik kurar.

2. **Frontend Bağımlılıkları**:
   ```bash
   cd application
   npm install && npm run build
   ```

3. **Ek Paketlerin Kurulumu**:
   ```bash
   composer require -W spatie/laravel-permission spatie/laravel-medialibrary spatie/laravel-translatable maatwebsite/excel
   ```

4. **Vendor Asset Yayınlama**:
   ```bash
   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
   php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
   ```

5. **Migration Çalıştırma**:
   ```bash
   php artisan migrate
   ```

6. **Süper Admin Kullanıcısı Oluşturma**:
   ```bash
   php artisan tinker
   # App\Models\User::create([...])
   ```

7. **Geliştirme Sunucusu**:
   ```bash
   composer dev   # php artisan serve --host=0.0.0.0 --port=8383
   ```

8. **✅ Doğrulama:** `http://localhost:8383/login` adresinden giriş yaparak Flux UI panelini görüntüleme.

---

## 📦 FAZ 2: Veritabanı Mimarisi & Modeller

> [!IMPORTANT]
> Seed'ler (roller ve sözlükler) FAZ 3 geliştirmelerinde kullanılabilmesi için bu fazda oluşturulur.

1. `users` tablosuna `locale` sütunu eklenmesi
2. `excavation_projects` — Kazı Projeleri Migration & Model (`HasTranslations`)
3. `dictionaries` — Dönem, Metal, Birim, Bölge vb. Migration & Model (`HasTranslations`)
4. `finds` — Buluntu Bağlamları (`buluntu-bilgileri.csv` alanları) Migration & Model
5. `coins` — Sikke Tanımları (`sikke-tanimlari.csv` alanları) Migration & Model (`HasTranslations`, `InteractsWithMedia`)
6. **Model İlişkileri**: `ExcavationProject → Find → Coin → Media`
7. **Seeder**: Varsayılan roller (`super_admin`, `proje_yoneticisi`, `numismat`, `arkeolog`, `okuyucu`) + Spatie Permission tanımları

---

## 🎨 FAZ 3A: Navigasyon & Layout Yapısı

1. **Ana Layout**: Sidebar navigation, kullanıcı profil menüsü (`<flux:sidebar>`, `<flux:navbar>`)
2. **Profil & Şifre Değiştirme**: Starter Kit'in hazır sayfaları genişletilir, `locale` tercihi eklenir
3. **`SetLocale` Middleware**: Kullanıcının `locale` alanına göre her istekte dil ayarı uygulanır
4. **Auth Rotaları**: Starter Kit'ten gelen Login, Register, Password Reset sayfaları korunur

---

## 🎨 FAZ 3B: CRUD Ekranları & Flux UI Bileşenleri

1. **Sözlükler Ekranı**: `<flux:table>` + `<flux:modal>` ile Dönem, Metal, Birim vb. yönetimi
2. **Kazı Projeleri Ekranı**: Proje listesi ve form
3. **Buluntular Ekranı**: `buluntu-bilgileri.csv` alanlarıyla veri giriş formu
4. **Sikkeler Ekranı**: `sikke-tanimlari.csv` alanları + çoklu dil sekmeleri (Spatie Translatable)
5. **Ön/Arka Yüz Görsel Yükleyici**: Spatie MediaLibrary + `<flux:input type="file">` ile fotoğraf yükleme

---

## 🔐 FAZ 4: Yetkilendirme, Export & Son Doğrulama

1. **Rol & Yetki Yönetim Ekranı**: Livewire bileşeni + Spatie Permission ile kullanıcılara rol atama
2. **`@can` / `Gate` Kısıtlamaları**: Tüm CRUD ekranlarına rol bazlı erişim kontrolü
3. **Excel Export**: Sikkeler ve buluntular için `.xlsx` aktarım butonu (`maatwebsite/excel`)
4. **Seed Tamamlama**: Varsayılan sözlük verileri (Dönem: Arkaik, Klasik vb.; Metal: AE, AR, AU vb.)
5. **Son Doğrulama**: Form validation, görsel yükleme, dil geçişi ve rol kısıtlamalarının testi
