<x-app-layout>
    <x-slot name="header">
        @include('partials.internal-header')
    </x-slot>

    <div class="space-y-6">
        <x-card class="dark:bg-gray-800">
            <div class="flex h-screen">
                <!-- Element List (Left Pane) -->
                <div class="w-1/4 bg-gray-300 dark:bg-gray-700 p-4 flex flex-col">
                    <!-- Add content here -->
                </div>

                <!-- Form (Right Pane) -->
                <div class="w-3/4 p-4">
                    @if (session('error'))
                        <div class="bg-red-100 dark:bg-red-800 border border-red-400 text-red-700 dark:text-red-300 px-4 py-3 rounded relative mb-3" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="bg-green-100 dark:bg-green-800 border border-green-400 text-green-700 dark:text-green-300 px-4 py-3 rounded relative mb-3" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    <form id="prompt_create_form" action="{{ route('prompts.store') }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="prompt_id">
                                    ID:
                                </label>
                                <input
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 leading-tight focus:outline-none focus:shadow-outline bg-gray-100 dark:bg-gray-600 cursor-not-allowed"
                                    id="prompt_id"
                                    type="text"
                                    name="prompt_id"
                                    disabled
                                />
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="name">
                                    Name:
                                </label>
                                <input
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 leading-tight focus:outline-none focus:shadow-outline bg-gray-100 dark:bg-gray-600"
                                    id="name"
                                    type="text"
                                    name="name"
                                    required
                                />
                            </div>
                            @error('name')
                            <span class="text-red-500 mb-1">{{ $message }}</span>
                            @enderror
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="description">
                                    Description:
                                </label>
                                <textarea
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 leading-tight focus:outline-none focus:shadow-outline bg-gray-100 dark:bg-gray-600"
                                    id="description"
                                    name="description"
                                    rows="5"
                                    required
                                ></textarea>
                            </div>
                            @error('description')
                            <span class="text-red-500 mb-1">{{ $message }}</span>
                            @enderror
                            <div>
    <div class="flex justify-between items-center mb-2">
        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold" for="prompt">
            Prompts:
        </label>
        <button
            type="button"
            class="bg-blue-500 text-white px-4 py-1 rounded hover:bg-blue-600 focus:outline-none"
            onclick="insertDefaultText()"
        >
            Insert Default Template Text
        </button>
    </div>
    <textarea
        required
        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 leading-tight focus:outline-none focus:shadow-outline bg-gray-100 dark:bg-gray-600"
        id="prompt"
        name="prompt"
        rows="10"
    ></textarea>
</div>
                            @error('prompt')
                            <span class="text-red-500 mb-1">{{ $message }}</span>
                            @enderror
                            <div>
                                <label class="inline-flex items-center">
                                    <input
                                        type="checkbox"
                                        class="form-checkbox h-5 w-5 text-blue-600"
                                        name="is_default"
                                    />
                                    <span class="ml-2 text-gray-700 dark:text-gray-300">Is Default</span>
                                </label>
                            </div>
                        </div>
                    </form>

                    <!-- Action Buttons -->
                    <div class="flex justify-end mt-4">
                        <button type="button" onclick="document.getElementById('prompt_create_form').submit()" class="mr-3 bg-green-500 text-white px-4 py-2 rounded">
                            <i class="fa fa-save"></i> Save
                        </button>
                        <button type="button" onclick="window.location.href='{{ route('prompts.home') }}'" class="bg-gray-500 text-white px-4 py-2 rounded">
                            <i class="fa fa-remove"></i> Cancel
                        </button>
                    </div>
                </div>
            </div>
        </x-card>
    </div>
    <style>
        .disabled-button {
            background-color: #d1d5db; /* Gray background */
            cursor: not-allowed;
            opacity: 0.5;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const promptField = document.getElementById('prompt');
            const nameField = document.getElementById('name');
            const descriptionField = document.getElementById('description');
            const saveButton = document.querySelector("button[onclick*='prompt_create_form']");

            function toggleSaveButton() {
                if (promptField.value.trim() === '') {
                    saveButton.disabled = true;
                    saveButton.classList.add('disabled-button');
                } else if (nameField.value.trim() === '') {
                    saveButton.disabled = true;
                    saveButton.classList.add('disabled-button');
                } else if (descriptionField.value.trim() === '') {
                    saveButton.disabled = true;
                    saveButton.classList.add('disabled-button');
                } else {
                    saveButton.disabled = false;
                    saveButton.classList.remove('disabled-button');
                }
            }

            promptField.addEventListener('input', toggleSaveButton);
            nameField.addEventListener('input', toggleSaveButton);
            descriptionField.addEventListener('input', toggleSaveButton);
            toggleSaveButton(); // Ensure proper state on page load
        });

        function insertDefaultText() {
        const promptField = document.getElementById('prompt');
        const defaultText = @json(DEFAULT_PROMPT_TEMPLATE); // Pass the constant value to JavaScript

        // Insert the default text into the textarea
        promptField.value = defaultText;

        // Trigger the input event to ensure any listeners are notified
        promptField.dispatchEvent(new Event('input'));
    }
    </script>
</x-app-layout>
