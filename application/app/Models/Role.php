<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Translatable\HasTranslations;

class Role extends SpatieRole
{
    use HasTranslations, LogsActivity;

    /** @var array<string> */
    public array $translatable = ['display_name'];

    /**
     * Get the localized display name, falling back to the raw name.
     */
    public function getLocalizedNameAttribute(): string
    {
        return $this->display_name ?: $this->name;
    }
}
