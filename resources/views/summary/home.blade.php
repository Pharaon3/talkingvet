<x-app-layout>
    <x-slot name="header">
        @include('partials.internal-header')
    </x-slot>
    <div class="space-y-6">

        <x-card>
            <div x-data="{ activeTab: 'summary', copyHeaders: {{ env('COPY_HEADERS', true) ? 'true' : 'false' }} }" class="p-4">
                <!-- Tabs Navigation -->
                <nav class="flex justify-between space-x-4">
                    <div class="flex space-x-4">
                        <a href="{{route('assist.home')}}"
                            class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition">
                            <i class="fa fa-arrow-left"></i> Back To Visits
                        </a>
                        <select id="selectedPrompt" class="appearance-none border border-gray-300 bg-white text-gray-700 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($prompts as $prompt_item)
                            <option value="{{$prompt_item->id}}" @if($prompt_item->id == $prompt->id) selected @endif>{{$prompt_item->name}}</option>
                            @endforeach
                        </select>
                        <button @click="reGenerate()"
                            class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition">
                            <i class="fa fa-refresh"></i> Re-Generate
                        </button>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button @click="activeTab = 'summary'"
                            :class="{ 'border-blue-500 outline outline-2 outline-blue-500 rounded-lg': activeTab === 'summary' }"
                            class="py-2 px-4 border-b-2 border-transparent hover:border-gray-300 transition">
                            Summary
                        </button>
                        <button @click="activeTab = 'sections'"
                            :class="{ 'border-blue-500 outline outline-2 outline-blue-500 rounded-lg': activeTab === 'sections' }"
                            class="py-2 px-4 border-b-2 border-transparent hover:border-gray-300 transition">
                            Sections
                        </button>
                        <button @click="activeTab = 'transcripts'"
                            :class="{ 'border-blue-500 outline outline-2 outline-blue-500 rounded-lg': activeTab === 'transcripts' }"
                            class="py-2 px-4 border-b-2 border-transparent hover:border-gray-300 transition">
                            Transcripts
                        </button>
                    </div>
                </nav>

                <!-- Tab Contents -->
                <div id="toastContainer" class="fixed inset-x-0 bottom-0 flex items-center justify-end p-4 pointer-events-none z-50"></div>

                <!-- Summary Tab Content -->
                <div x-show="activeTab === 'summary'" class="mt-4">
                    <!-- Display Encounter Name and ID -->
                    <div class="mb-4 text-gray-700 dark:text-gray-300">
                        <strong>Encounter Name:</strong> {{ $encounter->identifier }}<br>
                        <strong>Encounter ID:</strong> {{ $encounter->encounter_id }}
                    </div>

                    <div class="relative mt-1">
                        <div id="summaryText" class="block w-full border border-gray-300 rounded-md shadow-sm bg-gray-100 p-6 overflow-y-auto dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            {!! $summary_html !!}
                        </div>
                        <button @click="copyToClipboard(`{{$summary_text}}`)"
                            class="absolute bottom-2 right-2 bg-blue-300 text-white px-3 py-2 rounded-full shadow-md hover:bg-blue-600 transition duration-200 ease-in-out">
                            <i class="fa fa-copy"></i>
                        </button>
                    </div>
                    <div class="flex justify-between w-full mt-3">
                        <button @click="copyToClipboard(`{{$summary_text}}`)"
                            class="mt-2 bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition">
                            <i class="fa fa-copy"></i> Copy All to Clipboard
                        </button>
                    </div>
                </div>

                <!-- Sections Tab Content -->
                <div x-show="activeTab === 'sections'" class="mt-4">
                    <!-- Display Encounter Name and ID -->
                    <div class="mb-4 text-gray-700 dark:text-gray-300">
                        <strong>Encounter Name:</strong> {{ $encounter->identifier }}<br>
                        <strong>Encounter ID:</strong> {{ $encounter->encounter_id }}
                    </div>

                    <label class="block mt-4">
                        <input id="copy_header" type="checkbox" x-model="copyHeaders"> Include field headers in copy
                    </label>
                    @foreach($summary_sections as $summary_section)
                    <div class="mt-4">
                        <label class="block text-lg font-bold text-gray-700 dark:text-gray-100">
                            {{$summary_section['heading']}}
                        </label>
                        <div class="relative mt-1">
                            <div id="summaryText" class="block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-gray-100 dark:bg-gray-700 p-4 overflow-y-auto text-gray-900 dark:text-gray-100">
                                {!! $summary_section['content'] !!}
                            </div>
                            <button @click="copyToClipboard('{{$summary_section['content']}}')"
                                class="absolute bottom-2 right-2 bg-blue-300 dark:bg-blue-600 text-white px-3 py-2 rounded-full shadow-md hover:bg-blue-600 dark:hover:bg-blue-500 transition duration-200 ease-in-out">
                                <i class="fa fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach

                    <input id="without_header_text" type="hidden" value="@foreach($summary_sections as $summary_section) {{$summary_section['original_text']}} @endforeach">
                    <div class="flex justify-between w-full mt-3">
                        <a href="{{route('assist.home')}}"
                            class="mt-2 bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition">
                            <i class="fa fa-arrow-left"></i> Back To Visits
                        </a>
                        <button @click="copySectionsToClipboard(`{{$summary_text}}`)"
                            class="mt-2 bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition">
                            <i class="fa fa-copy"></i> Copy All to Clipboard
                        </button>
                    </div>
                </div>

                <!-- Transcripts Tab Content -->
                <div x-show="activeTab === 'transcripts'" class="mt-4">
                    <!-- Display Encounter Name and ID -->
                    <div class="mb-4 text-gray-700 dark:text-gray-300">
                        <strong>Encounter Name:</strong> {{ $encounter->identifier }}<br>
                        <strong>Encounter ID:</strong> {{ $encounter->encounter_id }}
                    </div>
                    <div class="relative mt-2">
                        <div class="flex flex-col space-y-2">
                            @foreach($transcripts as $transcript)
                            @if($transcript['position'] == 'left')
                            <div class="flex justify-start">
                                <div class="bubble bg-gray-100 p-4 rounded-md max-w-4xl w-4/5 dark:bg-gray-800 dark:text-white">
                                    {{$transcript['dialogue']}}
                                </div>
                            </div>
                            @else
                            <div class="flex justify-end">
                                <div class="bubble bg-blue-100 p-4 rounded-md max-w-4xl w-4/5 dark:bg-blue-600 dark:text-white">
                                    {{$transcript['dialogue']}}
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="flex justify-between w-full mt-3">
                        <a href="{{route('assist.home')}}"
                            class="mt-2 bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition">
                            <i class="fa fa-arrow-left"></i> Back To Visits
                        </a>
                        <button @click="copyToClipboard('transcriptsContainer')"
                            class="mt-2 bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition">
                            <i class="fa fa-copy"></i> Copy All to Clipboard
                        </button>
                    </div>
                </div>


            </div>

    </div>
    </x-card>
    </div>

    <script>
        function reGenerate() {
            const requestData = {
                encounter_id: "{{$encounter->id}}",
                prompt_id: document.getElementById("selectedPrompt").value,
                transaction: "$transcripts",
            };
            fetch('/re-generate-summary', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData)
            }).then(response => console.log("response: ", response));
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text)
                .then(() => {
                    console.log('Text copied to clipboard');
                    showToast("Copied to clipboard");
                })
                .catch((error) => {
                    console.error(error);
                });
        }

        function copySectionsToClipboard(text) {
            let checkbox = document.getElementById('copy_header');
            if (checkbox.checked) {
                copyToClipboard(text)
            } else {
                let hiddenInput = document.getElementById('without_header_text');
                let value = hiddenInput.value;
                copyToClipboard(value)
            }
        }

        function showToast(toastText, error = false) {
            // Create toast element
            var toastContainer = document.getElementById('toastContainer');
            var toast = document.createElement('div');
            toast.className = error ? 'bg-red-500' : 'bg-green-500';
            toast.className += ' text-white rounded-lg p-3 flex items-center';

            toast.innerHTML =
                error ?
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">' +
                '  <path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd" />' +
                '</svg>' :
                '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5 mr-2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>';

            toast.innerHTML += toastText;

            // Append toast to container
            toastContainer.appendChild(toast);

            // Remove toast after 3 seconds
            setTimeout(function() {
                toastContainer.removeChild(toast);
            }, 3000);
        }
    </script>
</x-app-layout>