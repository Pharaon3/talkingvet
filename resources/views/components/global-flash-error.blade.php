@if (session('error'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition
        x-init="setTimeout(() => show = false, 3000)"
        role="alert"
        class="mb-4 rounded-lg bg-danger-100 px-6 py-5 text-base text-danger-700">
        <div class="flex justify-between items-center">
            <ul>
                @foreach (session('error') as $src => $error)
                    <li><b>{{$src}}</b> {{ $error }}</li>
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
