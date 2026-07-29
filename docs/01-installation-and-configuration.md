# 01 — Installation & Configuration Guide

This guide covers setting up **Laravel-adminX** from scratch and configuring environment variables.

---

## 🛠️ Installation Steps

### 1. Environment Configuration (`.env`)
Create or edit your `.env` file with the following core settings:

```env
APP_NAME="Laravel-adminX"
ADMIN_PREFIX="adminx"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8383

APP_LOCALE=tr
APP_FALLBACK_LOCALE=en
```

### 2. Run Database Migrations & Seeders
Run the following Artisan commands to initialize tables, permissions, default languages, and the super admin account:

```bash
php artisan migrate
php artisan db:seed --class=LanguageSeeder
php artisan db:seed --class=RoleAndPermissionSeeder
php artisan storage:link
```

---

## 🔑 Default Super Admin Credentials

After running seeders, you can access the admin panel using:

- **Login URL**: `http://localhost:8383/adminx/login`
- **Email**: `admin@admin.com`
- **Password**: `password`

---

## 🔒 Customizing Admin URL Prefix (`ADMIN_PREFIX`)

You can change the admin panel URL prefix at any time without breaking routes.

In `.env`:
```env
ADMIN_PREFIX="panel" # Changes URL from /adminx/login to /panel/login
```

After updating `.env`, clear application caches:
```bash
php artisan config:clear
php artisan route:clear
```
