<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use App\Traits\LogsActivity;

class Find extends Model implements HasMedia
{
    use InteractsWithMedia, LogsActivity;

    protected $fillable = [
        'excavation_project_id',
        'find_date',
        'inventory_number',
        'excavation_area',
        'excavation_season',
        'sector',
        'area',
        'trench',
        'square',
        'sub_square',
        'locus',
        'context',
        'stratigraphic_unit',
        'unit',
        'layer',
        'level',
        'phase',
        'feature',
        'grave_number',
        'structure',
        'room',
        'architectural_feature',
        'find_spot',
        'elevation',
        'coordinate_x',
        'coordinate_y',
        'coordinate_z',
        'find_number',
        'bag_number',
        'find_group',
        'find_note',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'find_date'   => 'date',
            'elevation'   => 'decimal:2',
            'coordinate_x' => 'decimal:4',
            'coordinate_y' => 'decimal:4',
            'coordinate_z' => 'decimal:4',
        ];
    }

    /**
     * Medya koleksiyonlarını tanımla
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

        $this->addMediaCollection('document')
            ->acceptsMimeTypes(['application/pdf', 'image/svg+xml', 'image/png', 'image/jpeg']);
    }

    /**
     * Medya dönüşümleri (thumbnail'lar)
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->performOnCollections('cover', 'gallery');

        $this->addMediaConversion('preview')
            ->width(600)
            ->performOnCollections('cover', 'gallery');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ExcavationProject::class, 'excavation_project_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function coins(): HasMany
    {
        return $this->hasMany(Coin::class);
    }
}
