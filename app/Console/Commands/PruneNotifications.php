<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class PruneNotifications extends Command
{
    protected $signature = 'notifications:prune';

    protected $description = 'Hapus notifikasi yang umurnya lebih dari 24 jam';

    public function handle(): int
    {
        $deleted = Notification::where('created_at', '<', now()->subDay())->delete();

        $this->info("{$deleted} notifikasi dihapus.");

        return self::SUCCESS;
    }
}