<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Translatable\HasTranslations;

class Permission extends SpatiePermission
{
    use HasTranslations;

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
