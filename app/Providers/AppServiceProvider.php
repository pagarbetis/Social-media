<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider; // <-- Tambahkan baris ini di atas

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Paksa semua link & form menggunakan HTTPS saat pakai Ngrok
        if (str_contains(request()->url(), 'ngrok-free.dev') || app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
