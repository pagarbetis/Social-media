<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold tracking-tight text-gray-900">Masuk ke akunmu</h1>
        <p class="mt-1 text-sm text-gray-600">Lanjutkan obrolan di linimasa.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block min-h-11 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1 block min-h-11 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-gray-800 shadow-sm focus:ring-gray-700" name="remember">
                <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
            </label>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-4">
            <div class="flex flex-col gap-3 text-center sm:flex-row sm:items-center sm:justify-between sm:text-left">
                @if (Route::has('register'))
                    <a class="rounded text-sm text-gray-700 underline underline-offset-4 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-800 focus:ring-offset-2" href="{{ route('register') }}">
                        {{ __('Belum punya akun?') }}
                    </a>
                @endif
                @if (Route::has('password.request'))
                    <a class="rounded text-sm text-gray-600 underline underline-offset-4 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-800 focus:ring-offset-2" href="{{ route('password.request') }}">
                        Lupa password?
                    </a>
                @endif
            </div>

            <x-primary-button class="min-h-11 w-full justify-center rounded-md bg-gray-800 text-sm normal-case tracking-normal hover:bg-gray-700 focus:bg-gray-700">
                Masuk
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>