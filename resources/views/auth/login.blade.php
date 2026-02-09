<x-guest-layout>
    <div class="w-full max-w-sm glass-card p-6">
        <h1 class="text-2xl font-bold text-center mb-1">Zaloguj się</h1>
        <p class="text-center text-sm text-slate-500 dark:text-slate-300 mb-6">KanAI — zarządzanie przepływem pracy</p>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <x-input-label for="email" :value="__('E-mail')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Hasło')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <label for="remember_me" class="inline-flex items-center text-sm">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2">Zapamiętaj mnie</span>
            </label>

            <x-primary-button class="w-full justify-center">Zaloguj</x-primary-button>

            @if (Route::has('password.request'))
                <a class="block text-center text-sm text-indigo-600 hover:text-indigo-500" href="{{ route('password.request') }}">Nie pamiętasz hasła?</a>
            @endif
        </form>
    </div>
</x-guest-layout>
