# 03 — Roles & Permissions (RBAC) Guide

**Laravel-adminX** uses `spatie/laravel-permission` enhanced with translatable role display names.

---

## 👥 Default Roles

1. **Super Admin**: Complete unrestricted system access.
2. **Admin**: Full administrative permissions.
3. **Manager**: Access to user list, media library, activity logs, and backups.
4. **User**: Standard user access (Media Library view).

---

## 🔒 Permission Matrix

Permissions are granular and defined in `RoleAndPermissionSeeder.php`:

| Module | Permission Name | Turkish Display Name | English Display Name |
| :--- | :--- | :--- | :--- |
| **Users** | `users.view`, `users.create`, `users.update`, `users.delete` | Kullanıcı İşlemleri | User Operations |
| **Roles** | `roles.view`, `roles.create`, `roles.update`, `roles.delete` | Rol İşlemleri | Role Operations |
| **Settings**| `settings.view`, `settings.update` | Sistem Ayarları | System Settings |
| **Media** | `media.view`, `media.create`, `media.delete` | Medya Kütüphanesi | Media Library |
| **Logs** | `logs.view`, `logs.delete` | Etkinlik Günlükleri | Activity Logs |
| **Backups** | `backups.view`, `backups.create`, `backups.delete` | Yedekleme Yönetimi | Backup Management |

---

## 💻 Using Permissions in Blade & Livewire

### Blade Directives:
```blade
@can('users.create')
    <flux:button wire:click="createUser">{{ __('New User') }}</flux:button>
@endcan
```

### Livewire Authorization Checks:
```php
public function mount(): void
{
    abort_unless(auth()->user()->can('users.view'), 403);
}
```
