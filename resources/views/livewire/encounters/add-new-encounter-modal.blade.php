@props(['maxWidth' => '2xl'])

@php
    $maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
    ][$maxWidth];
@endphp

<div class="fixed inset-0 flex items-center justify-center px-4 py-6 sm:px-0 z-50">
    <div class="fixed inset-0 transform transition-all">
        <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"></div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto">
        <div class="p-6 dark:text-gray-100">
            <h2 class="text-xl font-bold mb-4">Add New Visit</h2>
            <form wire:submit.prevent="">
                <div class="mb-4">
                    <label for="identifier" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Patient</label>
                    <input type="text" id="identifier" wire:model="identifier" required
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                @error('identifier')
                <span class="text-red-500 mb-1">{{ $message }}</span>
                @enderror

                <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <textarea id="notes" required wire:model="notes" rows="7"
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                </div>
                @error('notes')
                <span class="text-red-500 mb-1">{{ $message }}</span>
                @enderror

                <div class="mb-4">
                    <label for="prompts" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Summary Type</label>
                    <select id="prompts" wire:model="prompt"
                            class="block mt-1 w-full border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 shadow-sm">
                        <option value="">Select Summary Type</option>
                        @foreach($prompts as $prompt)
                            <option value="{{$prompt->id}}">{{$prompt->name}}</option>
                        @endforeach
                    </select>
                </div>
                @error('prompts')
                <span class="text-red-500 mb-1">{{ $message }}</span>
                @enderror

                <div class="mb-4">
                    <label for="encounter_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Visit Date</label>
                    <input type="text" id="encounter_date" wire:model="encounter_date" readonly
                           class="mt-1 block w-full flatpickr-input rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                @error('encounter_date')
                <span class="text-red-500 mb-1">{{ $message }}</span>
                @enderror

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Attach History Data</label>
                    <input type="file" id="pdf_files" wire:model="pdf_files" multiple class="hidden" accept=".pdf">
                    <div class="mt-2">
                        <button type="button" class="bg-blue-500 text-white px-4 py-2 rounded" onclick="document.getElementById('pdf_files').click()">
                            <i class="fa fa-file-pdf"></i> Click To Attach History Data
                        </button>
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">
                            @if(count($pdf_files) == 0)
                                No pdf files attached.
                            @else
                                There are {{ count($pdf_files) }} attached pdf files.
                            @endif
                        </span>
                    </div>
                    @error('pdf_files')
                    <span class="text-red-500 mb-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="button" wire:click="save_and_record()" class="mr-3 bg-green-500 text-white px-4 py-2 rounded">
                        <i class="fa fa-folder-open"></i> Save & Open
                    </button>
                    <button type="button" wire:click="save_and_clear()" class="mr-3 bg-blue-500 text-white px-4 py-2 rounded">
                        <i class="fa fa-save"></i> Save & Add New
                    </button>
                    <button type="button" wire:click="close_modal()" class="bg-gray-500 text-white px-4 py-2 rounded">
                        <i class="fa fa-remove"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
