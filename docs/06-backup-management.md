# 06 — Backup Management System

Access the Backup Manager at `/adminx/settings/backups`.

---

## 💾 Backup Scopes

The `App\Services\BackupService` supports 3 backup modes:

1. **Database Only**: Dumps database tables and creates a zip file inside `storage/app/backups/`.
2. **Files Only**: Archives uploaded public media files (`storage/app/public/`).
3. **Full System Backup**: Combines database dump and media storage into a single zip archive.

---

## ⚡ Programmatic Backup Generation

You can generate backups programmatically in code or cron jobs:

```php
use App\Services\BackupService;

// Create database-only backup
$backupFile = BackupService::createBackup('database');

// Create full backup
$fullBackup = BackupService::createBackup('full');
```
