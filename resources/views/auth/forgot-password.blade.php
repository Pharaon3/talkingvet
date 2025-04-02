<x-guest-layout>
    <div class="flex justify-end">
        <x-dark-mode-switch/>
    </div>

    <div class="mb-4 text-sm text-gray-600 dark:text-gray-300">
        {{ __('Forgot your password? No problem. Just let us know your username and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Username Address -->
        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required autofocus />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <!-- Country/Server -->
        <x-country-select-div :country="$country??'usa'" />

        <div class="flex items-center justify-end mt-4">

            @if (Route::has('login'))
                <a class="underline mr-auto text-sm text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login').( isset($country)?'/'.$country:'' ) }}">
                    {{ __('Login') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
