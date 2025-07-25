<?php

namespace App\Models;

use App\Enums\SendLogStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SendLog extends Model
{
    protected $table = 'send_logs';

    protected $fillable = [
        'post_id',
        'user_id',
        'status',
        'response',
        'sent_at',
    ];

    protected $casts = [
        'status' => SendLogStatusEnum::class,
        'response' => 'array',
        'sent_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
