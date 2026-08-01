<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
    use LogsActivity;

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'flag',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get all active languages (cached).
     */
    public static function getActive(): array
    {
        return Cache::rememberForever('languages_active', function () {
            try {
                return static::where('is_active', true)
                    ->orderBy('sort_order')
                    ->get()
                    ->toArray();
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    /**
     * Get active language codes.
     */
    public static function getCodes(): array
    {
        return array_column(static::getActive(), 'code');
    }

    /**
     * Get the default language.
     */
    public static function getDefault(): ?array
    {
        $active = static::getActive();
        foreach ($active as $lang) {
            if ($lang['is_default']) {
                return $lang;
            }
        }

        return $active[0] ?? null;
    }

    /**
     * Get active languages as a keyed array (code => details) for compatibility
     * with the old config('app.available_locales') format.
     */
    public static function getActiveKeyed(): array
    {
        $result = [];
        foreach (static::getActive() as $lang) {
            $result[$lang['code']] = [
                'name' => $lang['name'],
                'native_name' => $lang['native_name'],
                'flag' => $lang['flag'],
                'code' => strtoupper($lang['code']),
            ];
        }

        return $result;
    }

    /**
     * Clear the language cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('languages_active');
    }

    /**
     * Boot the model — clear cache on any change.
     */
    protected static function booted(): void
    {
        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }
}
