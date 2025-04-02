<x-guest-layout>
    <livewire:auth.login :username="$username" :country="$country" />
    {{--<livewire:transactions-table-component :accounts="Auth::user()->getAccountsList()" />--}}
</x-guest-layout>
