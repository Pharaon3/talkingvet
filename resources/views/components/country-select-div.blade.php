<div class="mt-4">
    <x-input-label for="country" :value="__('Country')" />

    <!-- Livewire Select Dropdown -->
    <select name="country" wire:model="country" class="block bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
        <option value="usa" default>USA</option>
        <option value="canada">Canada</option>
        <option value="test">Test</option>
    </select>

    <x-input-error :messages="$errors->get('country')" class="mt-2" />
</div>
