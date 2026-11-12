<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold tracking-tight text-gray-900">Buat akun baru</h1>
        <p class="mt-1 text-sm text-gray-600">Mulai berbagi cerita bersama komunitas.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Nama')" />
            <x-text-input id="name" class="mt-1 block min-h-11 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block min-h-11 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1 block min-h-11 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Ulangi password')" />
            <x-text-input id="password_confirmation" class="mt-1 block min-h-11 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6 flex flex-col gap-4">
            <x-primary-button class="min-h-11 w-full justify-center rounded-md bg-gray-800 text-sm normal-case tracking-normal hover:bg-gray-700 focus:bg-gray-700">
                Buat akun
            </x-primary-button>

            <a class="rounded text-center text-sm text-gray-700 underline underline-offset-4 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-800 focus:ring-offset-2" href="{{ route('login') }}">
                Sudah punya akun? Masuk
            </a>
        </div>
    </form>
</x-guest-layout>
