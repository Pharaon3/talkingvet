<x-app-layout>
    <x-slot name="privateScripts">
        <!-- Scripts -->
    </x-slot>

    <x-slot name="header">
        @include('partials.internal-header')
    </x-slot>

    <livewire:encounters.encounters-table-component :accounts="Auth::guard('internal-auth-guard')->user()->get_account_list()" />

</x-app-layout>
