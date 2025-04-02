<div class="max-w-lg mx-auto bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-200 mb-4">Add User</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 dark:bg-green-800 border-l-4 border-green-500 text-green-700 dark:text-green-200 p-3 mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 dark:bg-red-800 border-l-4 border-red-500 text-red-700 dark:text-red-200 p-3 mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
            <input type="text" wire:model="username" required class="w-full p-2 border rounded-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name</label>
                <input type="text" wire:model="firstName" required class="w-full p-2 border rounded-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name</label>
                <input type="text" wire:model="lastName" required class="w-full p-2 border rounded-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
            <select wire:model="role" required class="w-full p-2 border rounded-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                <option value="">Select Role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}">{{ $role }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Organization</label>
            <select wire:model="organization" required class="w-full p-2 border rounded-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                <option value="">Select Organization</option>
                @foreach ($organizations as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center space-x-2">
            <input type="checkbox" wire:model="enabled" checked class="rounded">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Enabled</label>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Language</label>
            <select wire:model="defaultLanguage" disabled class="w-full p-2 border rounded-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                <option value="en-ca">en-ca</option>
            </select>
        </div>

        <div class="flex items-center space-x-2">
            <input type="checkbox" wire:model="syncNeeded" checked class="rounded">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sync Needed</label>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sync Key</label>
            <div class="flex space-x-2">
                <input type="text" wire:model="syncKey" readonly class="w-full p-2 border rounded-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                <button type="button" wire:click="generateSyncKey" class="px-4 py-2 bg-blue-500 text-white rounded-md">Generate</button>
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
            <input type="password" wire:model="password" required class="w-full p-2 border rounded-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
            <input type="password" wire:model="confirmPassword" required class="w-full p-2 border rounded-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
        </div>

        <div class="flex justify-end space-x-2 mt-4">
            <button type="button" wire:click="setSaveAction('close')" class="px-4 py-2 bg-green-500 text-white rounded-md">
                Save & Close
            </button>
            <button type="button" wire:click="setSaveAction('new')" class="px-4 py-2 bg-blue-500 text-white rounded-md">
                Save & Add New
            </button>
            <button type="button" wire:click="resetForm" class="px-4 py-2 bg-gray-500 text-white rounded-md">
                Cancel
            </button>
        </div>

    </form>
</div>
