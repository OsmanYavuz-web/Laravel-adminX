<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Get a setting value by key with cache support.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = Cache::rememberForever('system_settings_cache', function () {
            try {
                return static::all()->pluck('value', 'key')->toArray();
            } catch (\Throwable $e) {
                return [];
            }
        });

        if (array_key_exists($key, $settings)) {
            $val = $settings[$key];
            // Decode JSON if applicable
            $json = json_decode($val, true);
            return (json_last_error() === JSON_ERROR_NONE && !is_numeric($val)) ? $json : $val;
        }

        return $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        $formattedValue = is_array($value) || is_object($value) ? json_encode($value) : (string) $value;

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $formattedValue, 'group' => $group]
        );

        Cache::forget('system_settings_cache');
    }
}
