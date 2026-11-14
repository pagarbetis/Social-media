<?php

namespace App\Models;

use App\Events\NotificationCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected static function booted(): void
    {
        static::created(function (Notification $notification) {
            try {
                event(new NotificationCreated($notification));
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }

    protected $fillable = ['user_id', 'sender_id', 'post_id', 'message', 'is_read'];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
