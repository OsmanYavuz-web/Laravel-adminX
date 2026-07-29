# 05 — Activity & Audit Logging Guide

**Laravel-adminX** includes an automated activity & audit logging system accessible at `/adminx/settings/logs`.

---

## 🔍 Log Event Types

1. **Model Event Diffs** (via `App\Traits\LogsActivity`):
   - Automatically records `created`, `updated`, and `deleted` events on Eloquent models (User, Role, Language, etc.).
   - Stores old vs new attributes in JSON format.
2. **Auth Events**:
   - `Login`: User successfully authenticated.
   - `Logout`: User signed out.
   - `Failed`: Failed login attempt (tracks IP address & input email).
3. **System Events**:
   - Backup creation, media uploads, and settings updates.

---

## 💻 Adding Activity Logging to Eloquent Models

To add automatic activity diff logging to any new Eloquent model:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Product extends Model
{
    use LogsActivity;

    protected $fillable = ['name', 'price'];
}
```

That's it! All creation, edits, and deletions will automatically log diffs into `activity_logs`.

---

## 🧹 Purging Logs

Users with `logs.delete` permission can purge logs older than 7, 30, or 90 days directly from the Activity Logs page.
