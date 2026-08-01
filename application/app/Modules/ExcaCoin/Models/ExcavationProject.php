<?php

namespace App\Modules\ExcaCoin\Models;

use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExcavationProject extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'site_name',
        'location',
        'country',
        'start_date',
        'end_date',
        'director',
        'description',
        'is_active',
        'created_by',
        'visible_fields',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'visible_fields' => 'array',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'excavation_project_user');
    }

    /**
     * Scope query to only include projects accessible by the specified or current user.
     */
    public function scopeAccessibleBy(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        // Super Admin ve Admin tüm projelere erişebilir
        if ($user->hasRole(['super-admin', 'admin'])) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->whereHas('users', function ($uq) use ($user) {
                $uq->where('users.id', $user->id);
            })->orWhere('created_by', $user->id);
        });
    }

    /**
     * Check if a field is visible for this project.
     * If visible_fields is empty or null, all fields are visible by default.
     */
    public function isFieldVisible(string $field): bool
    {
        if (empty($this->visible_fields)) {
            return true;
        }

        return in_array($field, $this->visible_fields, true);
    }

    /**
     * Check if at least one field from an array of fields is visible for this project.
     * If visible_fields is empty or null, all fields are visible by default.
     */
    public function hasAnyFieldVisible(array $fields): bool
    {
        if (empty($this->visible_fields)) {
            return true;
        }

        foreach ($fields as $field) {
            if ($this->isFieldVisible($field)) {
                return true;
            }
        }

        return false;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function finds(): HasMany
    {
        return $this->hasMany(Find::class);
    }

    public function coins(): HasMany
    {
        return $this->hasMany(Coin::class);
    }

    /**
     * Proje etiketini döndür (isim + alan)
     */
    public function getFullTitleAttribute(): string
    {
        return "{$this->name} — {$this->site_name}";
    }
}
