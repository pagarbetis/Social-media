<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Thread Anjay</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <main class="grid min-h-screen place-items-center bg-gray-100 px-4 py-8">
            <div class="w-full max-w-md">
                <div class="mb-6 text-center">
                    <x-application-logo />
                    <p class="mt-2 text-sm text-gray-600">Ruang untuk berbagi cerita.</p>
                </div>

                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-8">
                    {{ $slot }}
                </section>
            </div>
        </main>
    </body>
</html>
