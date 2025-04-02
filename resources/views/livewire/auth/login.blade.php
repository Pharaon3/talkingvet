<div class="mt-4">

    @if($step === 1)
        <div>
            <x-input-label for="username" :value="__('Username')"/>
            <x-text-input id="username" class="block mt-1 w-full" type="text" wire:model="username"
                          value="{{$username ?? old('username')}}" required autofocus autocomplete="username"/>
            <x-input-error :messages="$errors->get('username')" class="mt-2"/>
        </div>
        <div class="flex justify-end items-center">
            <x-primary-button class="mt-4" wire:click="nextStep">
                {{ __('btn-next') }}
            </x-primary-button>
        </div>

    @elseif($step === 2)
    <!-- Step 2: Password Input for Existing Users -->
        <div>
            <x-input-label for="password" :value="__('Password')"/>
            <x-text-input id="password" class="block mt-1 w-full" type="password" wire:model="password" required
                autocomplete="current-password"/>
            <x-input-error :messages="$errors->get('password')" class="mt-2"/>
        </div>
        <div class="flex justify-between items-center mt-4">
            <button type="button" wire:click="previousStep" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                {{ __('Back') }}
            </button>
            <x-primary-button wire:click="login">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

    @elseif($step === 3)
        <div>
            <x-input-label for="password" :value="__('Password')"/>
            <x-text-input id="password" class="block mt-1 w-full" type="password" wire:model="password" required/>
            <x-input-error :messages="$errors->get('password')" class="mt-2"/>
        </div>
        <div class="mt-4">
            <x-country-select-div wire:model="country"/>
        </div>
        <div class="flex justify-between items-center mt-4">
            <button type="button" wire:click="previousStep" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                {{ __('Back') }}
            </button>
            <x-primary-button class="mt-4" wire:click="login">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    @endif
        @if (session('errors'))
            <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    role="alert"
                    class="mb-4 rounded-lg bg-danger-100 px-6 py-5 text-base text-danger-700">
                <div class="flex justify-between items-center">
                    <ul>
                        @foreach (session('errors')->all() as $error)
                            <li> {{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="text-gray-500 hover:text-gray-700" wire:click="clearError">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.293 4.293a1 1 0 0 1 1.414 0L10 8.586l5.293-5.293a1 1 0 0 1 1.414 1.414L11.414 10l5.293 5.293a1 1 0 0 1-1.414 1.414L10 11.414l-5.293 5.293a1 1 0 0 1-1.414-1.414L8.586 10 3.293 4.707a1 1 0 0 1 0-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif
</div>
