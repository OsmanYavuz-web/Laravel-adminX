# Laravel-adminX — Modern Base Admin Boilerplate Documentation

Welcome to the **Laravel-adminX** documentation! This package provides a modular, production-ready, highly extensible base administration panel built on top of Laravel, Livewire 3, Alpine.js, Tailwind CSS, Flux UI, and Spatie Permission.

---

## 📚 Table of Contents

1. [Installation & Configuration](01-installation-and-configuration.md)
2. [Localization & i18n Guide](02-localization-and-i18n.md)
3. [Roles & Permissions (RBAC)](03-roles-and-permissions.md)
4. [Media Library & Secure Sharing](04-media-library-and-sharing.md)
5. [Activity & Audit Logging](05-activity-logs.md)
6. [Backup Management System](06-backup-management.md)
7. [Theme System & Color Customization](07-theme-and-styling.md)

---

## ⚡ Technology Stack

- **Framework**: Laravel 13
- **Frontend Logic**: Livewire 3 & Alpine.js
- **Styling**: Tailwind CSS v4 & Flux UI Components
- **Role-Based Access Control**: Spatie Laravel Permission (with Translatable Display Names)
- **Database Support**: SQLite / MySQL / PostgreSQL / MariaDB

---

## 🚀 Quick Architecture Overview

```
application/
├── app/
│   ├── Models/            # User, Role, Permission, MediaShare, MediaShareView, ActivityLog, SystemSetting, Language
│   ├── Services/          # BackupService
│   └── Traits/            # LogsActivity
├── config/
│   └── fortify.php        # Configured with ADMIN_PREFIX env
├── docs/                  # Documentation Markdown Files
├── lang/                  # i18n JSON files (tr.json, en.json)
├── resources/
│   ├── css/app.css        # Dynamic Theme Brand Color Bindings
│   └── views/
│       ├── layouts/       # app, auth, share layouts
│       └── pages/         # users, roles, media, settings (system, languages, logs, backups)
└── routes/
    ├── web.php            # Admin Routes grouped by $adminPrefix
    └── settings.php       # Profile, Security, & Appearance Routes
```
