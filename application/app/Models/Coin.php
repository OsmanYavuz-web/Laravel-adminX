<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use App\Traits\LogsActivity;

class Coin extends Model implements HasMedia
{
    use InteractsWithMedia, LogsActivity;

    protected $fillable = [
        'find_id',
        'excavation_project_id',
        // Sözlük FK'lar
        'period_id',
        'authority_id',
        'ruler_id',
        'region_id',
        'mint_id',
        'metal_id',
        'denomination_id',
        // Tarih
        'date_range',
        // Fiziksel
        'diameter',
        'weight',
        'axis',
        'is_cut',
        'is_pierced',
        // Ön yüz
        'obverse_description',
        'obverse_legend',
        'obverse_legend_expanded',
        // Arka yüz
        'reverse_description',
        'reverse_legend',
        'reverse_legend_expanded',
        // Ekstra
        'mint_mark',
        'magistrate',
        'control_mark',
        'monogram',
        'countermark',
        'is_overstrike',
        'reference',
        'note',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'diameter'     => 'decimal:2',
            'weight'       => 'decimal:3',
            'axis'         => 'integer',
            'is_cut'       => 'boolean',
            'is_pierced'   => 'boolean',
            'is_overstrike' => 'boolean',
        ];
    }

    /**
     * Medya koleksiyonlarını tanımla
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('obverse')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('reverse')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

        $this->addMediaCollection('document')
            ->acceptsMimeTypes(['application/pdf', 'image/svg+xml', 'image/png', 'image/jpeg']);
    }

    /**
     * Medya dönüşümleri
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->performOnCollections('obverse', 'reverse', 'gallery');

        $this->addMediaConversion('preview')
            ->width(600)
            ->performOnCollections('obverse', 'reverse', 'gallery');
    }

    // --- İlişkiler ---

    public function find(): BelongsTo
    {
        return $this->belongsTo(Find::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ExcavationProject::class, 'excavation_project_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Sözlük ilişkileri
    public function period(): BelongsTo
    {
        return $this->belongsTo(Dictionary::class, 'period_id');
    }

    public function authority(): BelongsTo
    {
        return $this->belongsTo(Dictionary::class, 'authority_id');
    }

    public function ruler(): BelongsTo
    {
        return $this->belongsTo(Dictionary::class, 'ruler_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Dictionary::class, 'region_id');
    }

    public function mint(): BelongsTo
    {
        return $this->belongsTo(Dictionary::class, 'mint_id');
    }

    public function metal(): BelongsTo
    {
        return $this->belongsTo(Dictionary::class, 'metal_id');
    }

    public function denomination(): BelongsTo
    {
        return $this->belongsTo(Dictionary::class, 'denomination_id');
    }
}
