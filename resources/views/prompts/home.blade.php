<x-app-layout>
    <x-slot name="header">
        @include('partials.internal-header')
    </x-slot>

    <div class="space-y-6 dark:bg-gray-900 dark:text-gray-100">
        <x-card>
            <div class="flex h-screen">
                <!-- Element List (Left Pane) -->
                <div class="w-1/4 bg-gray-300 dark:bg-gray-800 p-4 flex flex-col">
                    <h2 class="text-lg font-semibold mb-4">User Summary Types</h2>
                        @foreach ($prompts as $prompt)
                            @if (!$prompt['system_default']) 
                            <li
                            style="list-style: none;"         
                            class="p-2 rounded cursor-pointer {{ $selected_prompt_id == $prompt['id'] ? 'bg-blue-100 dark:bg-blue-900' : 'hover:bg-gray-300 dark:hover:bg-gray-700' }}"
                                    onclick="window.location.href='{{ route('prompts.view', $prompt['id']) }}'"
                            >
                                <a href="{{ route('prompts.view', $prompt['id']) }}" class="">
                                    {{ $prompt['name'] }}
                                </a>
                            </li>
                            @endif
                        @endforeach
                    </ul>
                    <button class="mr-3 bg-primary-500 text-white px-4 py-2 rounded dark:bg-primary-700"
                            onclick="window.location.href='{{ route('prompts.create') }}'">
                        <i class="fa fa-plus"></i> Add New
                    </button>
                </div>

                <!-- Form (Right Pane) -->
                <div class="w-3/4 p-4 dark:bg-gray-800">
                    @if (session('error'))
                        <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded relative mb-3" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded relative mb-3" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    <form id="prompt_update_form" action="{{ route('prompts.update') }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="prompt_id">
                                    ID:
                                </label>
                                <input
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 leading-tight focus:outline-none focus:shadow-outline bg-gray-100 dark:bg-gray-700 cursor-not-allowed"
                                        id="prompt_id"
                                        name="prompt_id"
                                        type="text"
                                        value="{{$selected_prompt ? $selected_prompt['id'] : ''}}"
                                        readonly
                                />
                            </div>
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="name">
                                    Name:
                                </label>
                                <input
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 leading-tight focus:outline-none focus:shadow-outline bg-white dark:bg-gray-700"
                                        id="name"
                                        name="name"
                                        type="text"
                                        value="{{$selected_prompt ? $selected_prompt['name'] : ''}}"
                                />
                            </div>
                            @error('name')
                            <span class="text-red-500 dark:text-red-300 mb-1">{{ $message }}</span>
                            @enderror
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="description">
                                    Description:
                                </label>
                                <textarea
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 leading-tight focus:outline-none focus:shadow-outline bg-white dark:bg-gray-700"
                                        id="description"
                                        name="description"
                                        rows="5"
                                >{{$selected_prompt ? $selected_prompt['description'] : ''}}</textarea>
                            </div>
                            @error('description')
                            <span class="text-red-500 dark:text-red-300 mb-1">{{ $message }}</span>
                            @enderror
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="prompts">
                                    Prompts:
                                </label>
                                <textarea
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 leading-tight focus:outline-none focus:shadow-outline bg-white dark:bg-gray-700"
                                        id="prompt"
                                        name="prompt"
                                        rows="10"
                                >{{$selected_prompt ? $selected_prompt['prompt'] : ''}}</textarea>
                            </div>
                            @error('prompt')
                            <span class="text-red-500 dark:text-red-300 mb-1">{{ $message }}</span>
                            @enderror
                            <div>
                                <label class="inline-flex items-center">
                                    <input
                                            type="checkbox"
                                            id="is_default_checkbox"
                                            class="form-checkbox h-5 w-5 text-blue-600 dark:text-blue-300"
                                            @if($selected_prompt && $selected_prompt->is_default) checked @endif
                                    />
                                    <input type="hidden" name="is_default" id="is_default_value" value="{{ $selected_prompt && $selected_prompt->is_default ? '1' : '0' }}">
                                    <span class="ml-2 text-gray-700 dark:text-gray-300">Is Default</span>
                                </label>
                            </div>
                        </div>
                    </form>

                    <!-- Action Buttons -->
                    <div class="flex justify-end mt-4">
                        <button type="button" onclick="document.getElementById('prompt_update_form').submit()"
                                class="mr-3 bg-green-500 dark:bg-green-700 text-white px-4 py-2 rounded">
                            <i class="fa fa-save"></i> Save
                        </button>
                        <button type="button" onclick="window.location.href='{{ route('assist.home') }}'"
                                class="bg-gray-500 dark:bg-gray-600 text-white px-4 py-2 rounded">
                            <i class="fa fa-remove"></i> Cancel
                        </button>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            function deletePrompt(promptId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/prompts/delete/${promptId}`,
                            type: 'DELETE',
                            data: {
                                "_token": "{{ csrf_token() }}"
                            },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire(
                                        'Deleted!',
                                        response.message,
                                        'success'
                                    ).then(() => {
                                        window.location.href = "{{ route('prompts.home') }}";
                                    });
                                }
                            },
                            error: function () {
                                Swal.fire(
                                    'Error!',
                                    'Failed to delete prompt',
                                    'error'
                                );
                            }
                        });
                    }
                });
            }
        </script>

        <script>
            document.getElementById('is_default_checkbox').addEventListener('change', function() {
                document.getElementById('is_default_value').value = this.checked ? '1' : '0';
            });
        </script>
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
                const saveButton = document.querySelector("button[onclick*='prompt_update_form']");

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
        </script>
    @endpush
</x-app-layout>