@props(['maxWidth' => '2xl'])

@php
    $maxWidth = [
        'sm' => 'sm:max-w-md',
        'md' => 'sm:max-w-[80%]',
        'lg' => 'sm:max-w-[80%]',
        'xl' => 'sm:max-w-[80%]',
        '2xl' => 'sm:max-w-[80%]',
    ][$maxWidth];
@endphp

<div class="fixed inset-0 flex items-center justify-center px-4 py-6 sm:px-0 z-50">
    <div class="fixed inset-0 transform transition-all">
        <div class="absolute inset-0 bg-gray-500 opacity-75 dark:bg-gray-900"></div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto">
        <div class="p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">History Summary</h2>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Read-only Notes</label>
                <div class="block mt-1 w-full border-gray-300 rounded-md shadow-sm bg-gray-100 dark:bg-gray-700 h-[530px] overflow-y-auto">
                    @foreach($history_summary['contents'] as $index => $content)
                        <div id="header{{$index}}">
                            <h5 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{$content['header']}}</h5>
                            {!! make_history_summary_html($content['content']) !!}
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Inline Select and Close Button -->
            <div class="flex justify-between items-center mt-4">
                <!-- Select Dropdown -->
                <div id="header_nav" class="w-2/3">
                    <select
                            id="options"
                            class="block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            onchange="(function(event) {
                                const selectedValue = event.target.value;
                                console.log(selectedValue);
                                if (selectedValue) {
                                    const element = document.getElementById(selectedValue);
                                    if (element) {
                                        const container = element.closest('.overflow-y-auto');
                                        if (container) {
                                            const containerRect = container.getBoundingClientRect();
                                            const elementRect = element.getBoundingClientRect();
                                            const scrollOffset = elementRect.top - containerRect.top + container.scrollTop;

                                            container.scrollTo({
                                                top: scrollOffset - 20,
                                                behavior: 'smooth'
                                            });

                                            element.classList.add('highlight');
                                            setTimeout(() => {
                                                element.classList.remove('highlight');
                                            }, 2000);
                                        }
                                    }
                                }
                            })(event)">
                        <option value="">Jump To Section</option>
                        @foreach($history_summary['headers'] as $index => $header)
                            <option value="header{{$index}}">{{$header}}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Close Button -->
                <button type="button" wire:click="close_modal()" class="ml-3 bg-gray-500 text-white px-4 py-2 rounded dark:bg-gray-600 dark:hover:bg-gray-500">
                    <i class="fa fa-remove"></i> Close
                </button>
            </div>

        </div>
    </div>
</div>
