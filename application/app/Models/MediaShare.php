<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MediaShare extends Model
{
    protected $fillable = [
        'file_path',
        'share_token',
        'password',
        'expires_at',
        'created_by',
        'views_count',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function views(): HasMany
    {
        return $this->hasMany(MediaShareView::class, 'media_share_id');
    }

    public function isPasswordProtected(): bool
    {
        return ! empty($this->password);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function checkPassword(string $inputPassword): bool
    {
        if (! $this->isPasswordProtected()) {
            return true;
        }

        return Hash::check($inputPassword, $this->password);
    }

    public static function createShare(string $filePath, ?string $password = null, ?int $expiresInDays = null): static
    {
        return static::create([
            'file_path' => $filePath,
            'share_token' => Str::random(32),
            'password' => $password ? Hash::make($password) : null,
            'expires_at' => $expiresInDays ? now()->addDays($expiresInDays) : null,
            'created_by' => auth()->id(),
        ]);
    }
}
