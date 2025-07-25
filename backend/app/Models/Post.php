<?php

namespace App\Models;

use App\Enums\PostStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'image_path',
        'status',
        'social_media_response',
        'scheduled_for',
        'scheduled_at',
        'sent_at',
        'failed_at',
    ];

    protected $casts = [
        'status' => PostStatusEnum::class,
        'social_media_response' => 'array',
        'scheduled_for' => 'datetime',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sendLogs(): HasMany
    {
        return $this->hasMany(SendLog::class);
    }
}
