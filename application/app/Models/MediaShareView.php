<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaShareView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'media_share_id',
        'user_id',
        'ip_address',
        'user_agent',
        'action',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function share(): BelongsTo
    {
        return $this->belongsTo(MediaShare::class, 'media_share_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function recordView(MediaShare $share, string $action = 'viewed'): static
    {
        $request = request();

        return static::create([
            'media_share_id' => $share->id,
            'user_id' => auth()->id(),
            'ip_address' => $request?->ip(),
            'user_agent' => substr((string) $request?->userAgent(), 0, 500),
            'action' => $action,
            'created_at' => now(),
        ]);
    }
}
