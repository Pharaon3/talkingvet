<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
            {{ __('review.title') }}
        </h2>
    </x-slot>

    <x-slot name="privateScripts">
        <!-- Scripts -->

        <!-- Able Player  -->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.5/dist/js.cookie.min.js"></script>

        <link rel="stylesheet" href="{{url("lib/ableplayer-4.5/build/ableplayer.min.css")}}"/>
{{--        <script src="/lib/ableplayer-4.5/build/ableplayer.js"></script>--}}
        <script src="{{url("lib/ableplayer-4.5/build/ableplayer.min.js")}}"></script>

        <style>
            {!! \Jfcherng\Diff\DiffHelper::getStyleSheet() !!}
        </style>


    </x-slot>
{{--    @dd(session()->all());--}}

    @if ($errors->any())
        <div
            wire:ignore
            class="mb-4 rounded-lg bg-danger-100 px-6 py-5 text-base text-danger-700"
            role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Working way to grab errors on the go --}}
    @if (session('error'))
        <div
            wire:ignore
            class="mb-4 rounded-lg bg-danger-100 px-6 py-5 text-base text-danger-700"
            role="alert">
            <ul>
                @foreach (session('error') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <livewire:review-component :transactions="session('review.transactions')" :index="session('review.index')" />

</x-app-layout>
