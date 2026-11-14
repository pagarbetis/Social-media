<?php

namespace App\Models;

use App\Events\NotificationCreated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use Prunable;

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

    /**
     * Notifikasi yang dihapus otomatis oleh `model:prune`:
     * semua yang umurnya lebih dari 24 jam.
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDay());
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