<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

use App\Traits\LogsActivity;

class Dictionary extends Model
{
    use HasTranslations, LogsActivity;

    /**
     * Çevirilebilir alanlar
     */
    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'type',
        'code',
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Dictionary tipleri
     */
    const TYPES = [
        'period'       => ['tr' => 'Dönem',      'en' => 'Period'],
        'authority'    => ['tr' => 'Otorite',     'en' => 'Authority'],
        'ruler'        => ['tr' => 'Hükümdar',    'en' => 'Ruler'],
        'region'       => ['tr' => 'Bölge',       'en' => 'Region'],
        'mint'         => ['tr' => 'Darphane',    'en' => 'Mint'],
        'metal'        => ['tr' => 'Metal',       'en' => 'Metal'],
        'denomination' => ['tr' => 'Birim',       'en' => 'Denomination'],
    ];

    /**
     * Scope for filtering by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * Sikke ilişkileri — dönem olarak kullanılanlar
     */
    public function coinsAsPeriod(): HasMany
    {
        return $this->hasMany(Coin::class, 'period_id');
    }

    public function coinsAsMetal(): HasMany
    {
        return $this->hasMany(Coin::class, 'metal_id');
    }

    public function coinsAsMint(): HasMany
    {
        return $this->hasMany(Coin::class, 'mint_id');
    }
}
