<div class="relative space-y-6">

    <x-card>
        <div class="flex flex-row mb-4">
            <!-- Existing search section with basis-2/4 -->
            <div class="flex basis-1/4 items-center">
                <div class="flex items-center w-full">
                    <div class="relative w-full">
                        <!-- Search icon -->
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor"
                                 viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                      d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                      clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <!-- Search input -->
                        <input
                                type="search"
                                wire:model.debounce.300ms="search_term"
                                wire:keydown.escape="reset_search"
                                wire:keydown.tab="reset_search"
                                wire:keydown.enter="refresh"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="Search..."
                        >
                    </div>
                </div>
            </div>

            <!-- New dropdown section -->
            <div class="flex basis-1/4 items-center ml-4">
                <select
                        wire:model="search_status"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="">All</option> <!-- this will be treated as null -->
                    <option value="0">Open</option>
                    <option value="1">In Progress</option>
                    <option value="2">Ready for Review</option>
                    <option value="3">Closed</option>
                </select>
            </div>


            <div class="flex basis-2/4 justify-end items-center">
                <x-button wire:click="show_modal({{ $MODAL_NEW_ENCOUNTER }})">
                    <svg fill="currentColor" version="1.1" xmlns="http://www.w3.org/2000/svg"
                         xmlns:xlink="http://www.w3.org/1999/xlink" width="16px" height="16px"
                         viewBox="0 0 45.402 45.402" xml:space="preserve"><g id="SVGRepo_bgCarrier"
                                                                             stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <g>
                                <path d="M41.267,18.557H26.832V4.134C26.832,1.851,24.99,0,22.707,0c-2.283,0-4.124,1.851-4.124,4.135v14.432H4.141 c-2.283,0-4.139,1.851-4.138,4.135c-0.001,1.141,0.46,2.187,1.207,2.934c0.748,0.749,1.78,1.222,2.92,1.222h14.453V41.27 c0,1.142,0.453,2.176,1.201,2.922c0.748,0.748,1.777,1.211,2.919,1.211c2.282,0,4.129-1.851,4.129-4.133V26.857h14.435 c2.283,0,4.134-1.867,4.133-4.15C45.399,20.425,43.548,18.557,41.267,18.557z"></path>
                            </g>
                        </g></svg>
                    New Visit
                </x-button>
            </div>
        </div>

        @if (session()->has('encounters-table'))

            <div class="italic text-gray-500 text-end">

                {{ session('encounters-table') }}

            </div>

        @endif

        <div class="relative overflow-x-auto shadow-lg sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400" id="TransactionsTbl">
                <thead class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="p-4">
                        <div class="flex items-center">
                            <input id="checkbox-all-search" type="checkbox"
                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <label for="checkbox-all-search" class="sr-only">checkbox</label>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3">
                        No
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Patient
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Notes
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Visit ID
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Date Created
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Last Updated
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Number of <br/> Recordings
                    </th>
                    </th>
                    <th scope="col" class="px-3 py-3">
                        History Data
                    </th>
                    <th scope="col" class="px-3 py-3">
                        Summary
                    </th>
                </tr>
                </thead>
                <tbody>

                @for($i = 0; $i < count($encounters); $i++)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                        id="row{{$i}}"
                        wire:click=""
                        x-data
                        x-on:dblclick.prevent="$wire.call('handle_tr_db_click', {{$encounters[$i]['id']}})">
                        <td class="w-4 p-4">
                            <div class="flex items-center">
                                <input id="checkbox-table-search-{{$i}}" type="checkbox"
                                       value="{{ $encounters[$i]['id'] }}"
                                       wire:model="selected_encounters"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded
                    focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800
                    dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="checkbox-table-search-{{$i}}" class="sr-only">checkbox</label>
                            </div>
                        </td>
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                            {{$i + 1}}
                        </th>
                        <td class="px-6 py-4">
                            {{ $encounters[$i]['identifier'] }}
                        </td>
                        <td class="px-6 py-4 relative">
                            <div x-data="{ tooltip: false, timeout: null }"
                                x-init="tooltip = false"
                                class="p-4"
                                @mouseenter="timeout = setTimeout(() => tooltip = true, 1500)"
                                @mouseleave="clearTimeout(timeout); tooltip = false">
                                {{ Str::words($encounters[$i]['notes'], 3, '...') }}
                                <div x-show="tooltip"
                                    x-cloak
                                    class="absolute bottom-1/2 left-1/2 transform -translate-x-1/2 mt-2 px-4 py-2 bg-gray-800 text-white text-sm rounded-lg w-[512px] z-50 text-center">
                                    {{ $encounters[$i]['notes'] }}
                                    <div style="width: 0; height: 0; border-left: 10px solid transparent; border-right: 10px solid transparent; border-top: 10px solid #1f2937; position: absolute; margin: auto; bottom: -5px; left: 50%;"
                                        class="z-20"></div>
                                </div>
                            </div>

                        </td>


                        <td class="px-6 py-4">
                            {{ $encounters[$i]['encounter_id'] }}
                        </td>
                        <td class="px-6 py-4">
                            {!! \Carbon\Carbon::parse($encounters[$i]['created_at'])->format('F d, Y h:i A') !!}

                        </td>
                        <td class="px-6 py-4">
                            {!! \Carbon\Carbon::parse($encounters[$i]['updated_at'])->format('F d, Y h:i A') !!}
                        </td>
                        <td class="px-6 py-4">
                            {{ internal_api_get_encounter_status_string($encounters[$i]['status']) }}
                        </td>
                        <td class="px-6 py-4">
                            {{ internal_api_get_recording_count($encounters[$i]['id']) }}
                        </td>
                        <td class="px-6 py-4">
                        <button
                            class="w-36px cursor-pointer flex justify-center {{ $encounters[$i]['history_summary'] ? 'bg-blue-500 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed' }} text-white font-bold p-2 rounded flex gap-1 items-center focus:outline-none focus:shadow-outline"
                            wire:click="handle_history_summary_click({{ $MODAL_HISTORY_SUMMARY }}, {{$encounters[$i]['id']}})"
                            {{ $encounters[$i]['history_summary'] ? '' : 'disabled' }}>
                            <i class="fas fa-eye"></i>
                        </button>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{route('summary.view', $encounters[$i]['id'])}}"
                               class="w-36px flex justify-center bg-blue-500 hover:bg-blue-700 text-white font-bold p-2 rounded flex gap-1 items-center focus:outline-none focus:shadow-outline">
                                <i class="fas fa-note-sticky"></i>
                            </a>
                        </td>
                    </tr>
                @endfor
                @if(count($encounters) == 0)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                        id="row1"
                        wire:click="handle_tr_single_click()"
                        x-data
                        x-on:dblclick.prevent="$wire.call('handle_tr_db_click', {{$i}})">
                        <td class="px-6 py-4 relative text-center" colspan="100%">
                            There isn't any available visits.
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>

            <div class="flex justify-between items-center mt-5 px-4">
                <div class="font-bold">
                    Double Click to Open Visit
                </div>
                <div class="flex items-center gap-3">
                    <label for="set_status_select" class="whitespace-nowrap">Set selected status to: </label>
                    <select id="set_status_select"
                            wire:model="selected_status"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option value="0">Open</option>
                        <option value="1">In Progress</option>
                        <option value="2">Ready for Review</option>
                        <option value="3">Closed</option>
                    </select>
                    <x-button wire:click="update_encounters_status()">
                        Apply
                    </x-button>
                </div>
            </div>
            <nav class="flex items-center justify-end p-4" aria-label="Table navigation">
                <div class="flex items-center justify-between mt-4 gap-3">
                    <button
                            wire:click="go_to_previous_page"
                            class="px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-700 w-[108px] @if($current_page === 1) cursor-not-allowed @endif"
                            @if($current_page === 1) disabled @endif>
                        <i class="fa fa-angle-left me-2"></i> Previous
                    </button>

                    <div class="text-sm font-medium text-gray-700">
                        Page {{ $current_page }} of {{ ceil($total_records / $per_page) }}
                    </div>

                    <button
                            wire:click="go_to_next_page"
                            class="px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-700 w-[108px] @if($current_page === ceil($total_records / $per_page)) cursor-not-allowed @endif"
                            @if($current_page === ceil($total_records / $per_page)) disabled @endif>
                        Next <i class="fa fa-angle-right ms-2"></i>
                    </button>
                </div>
            </nav>
        </div>
    </x-card>

    <div wire:loading.class="loading" class="spinner"></div>
    @if($current_modal_status == $MODAL_NEW_ENCOUNTER)
        <livewire:encounters.add-new-encounter-modal/>
    @elseif($current_modal_status == $MODAL_RECORD_ENCOUNTER)
        <livewire:encounters.record-encounter-modal
                :record_param="$record_param"
        />
    @elseif($current_modal_status == $MODAL_HISTORY_SUMMARY)
        <livewire:encounters.history-summary-modal
                :history_summary="$history_summary ?? ''"/>
    @elseif($current_modal_status == $MODAL_MICROPHONE_TEST)
        <livewire:encounters.microphone-test-modal :mic_available="$mic_available"/>
    @endif
    {{$current_modal_status == $MODAL_MICROPHONE_TEST}}

    <div class="relative group">
        @if($mic_available)
            <button
                    wire:click="show_modal({{$MODAL_MICROPHONE_TEST}})"
                    class="bg-primary-500 hover:bg-primary-600 fixed bottom-6 right-6 text-white shadow-lg rounded-full w-16 h-16 flex items-center justify-center  transition-colors duration-200"
            >
                <i class="fa fa-microphone-slash text-2xl"></i>
            </button>

            <div class="fixed bottom-20 right-6 mt-3 z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <div class="relative rounded-md shadow-lg">
                    <div class="bg-white text-gray-900 p-4 rounded-t-md">
                        <div class="font-semibold text-lg">Microphone Enabled</div>
                        <div class="text-sm">If you want to test it, please click below button.</div>
                    </div>
                    <div class="absolute bottom-0 right-1.5 transform -translate-x-1/2 -translate-y-full">
                        <div class="w-0 h-0 border-l-4 border-r-4 border-b-8 border-b-white border-solid border-l-transparent border-r-transparent"></div>
                    </div>
                </div>
            </div>
        @else
            <button
                    wire:click="show_modal({{$MODAL_MICROPHONE_TEST}})"
                    class="bg-danger-500 hover:bg-danger-600 fixed bottom-6 right-6 text-white shadow-lg rounded-full w-16 h-16 flex items-center justify-center  transition-colors duration-200"
            >
                <i class="fa fa-microphone-slash text-2xl"></i>
            </button>

            <div class="fixed bottom-20 right-6 mt-3 z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <div class="relative rounded-md shadow-lg">
                    <div class="bg-white text-gray-900 p-4 rounded-t-md">
                        <div class="font-semibold text-lg">Microphone Disabled</div>
                        <div class="text-sm">Please click this button and follow the guide to resolve the issue.</div>
                    </div>
                    <div class="absolute bottom-0 right-1.5 transform -translate-x-1/2 -translate-y-full">
                        <div class="w-0 h-0 border-l-4 border-r-4 border-b-8 border-b-white border-solid border-l-transparent border-r-transparent"></div>
                    </div>
                </div>
            </div>
        @endif

    </div>
    @vite(['resources/js/encounters/mic-test.js'])
</div>
