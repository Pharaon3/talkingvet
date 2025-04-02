@php use App\Models\Nvoq\TransactionStatus; @endphp
<div class="space-y-6">
    {{--    @dd($trx)--}}
    {{--    @dd(session()->all())--}}

    @vite(['resources/js/pages/review.js'])

    {{--  Loading overlay  --}}

    <div id="loadingOverlay" class="fixed inset-0 items-center justify-center z-[10000] bg-black bg-opacity-50 hidden" wire:loading.flex>
        <div class="flex flex-col items-center">
            <svg aria-hidden="true" class="w-24 h-24 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- SVG path goes here -->
                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
            </svg>
            <span class="mt-4 text-white dark:text-gray-600">{{__("lbl-loading")}}</span>
        </div>
    </div>

    <div id="toastContainer" class="fixed inset-x-0 bottom-0 flex items-center justify-end p-4 pointer-events-none z-50"></div>

    @if($data["modalOpen"])
        <div class="fixed inset-0 flex items-center justify-center shadow-gray-500 z-[9999]">
            <div class="fixed inset-0 bg-black opacity-70 dark:bg-gray-900"></div>
            <div class="relative bg-white dark:bg-gray-800 w-[400px] h-[300px] rounded-lg shadow-lg">
                <div class="p-4 h-full flex flex-col">
                    <h2 class="text-2xl font-bold mb-4 text-white dark:text-white">{{ $data["modalData"]["title"] }}</h2>
                    <p class="text-black dark:text-white flex-auto">{{ $data["modalData"]["content"] }}</p>
                    <div class="mt-4 text-right">
                        <button wire:click="modalConfirm" class="bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white rounded-md px-4 py-2">
                            {{$modalAction == \App\Models\Enums\ModalActions::CORRECT_OR_REJECT ? 'Yes Reject' : 'Ok'}}
                        </button>
{{--                        @if($modalAction != \App\Models\Enums\ModalActions::CORRECTION_SUCCESS)--}}
                            <button wire:click="modalCancel" class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-400 dark:hover:bg-gray-500 text-gray-700 rounded-md px-4 py-2">
                                {{$modalAction == \App\Models\Enums\ModalActions::CORRECT_OR_REJECT ? 'No correct anyway' : 'Cancel'}}
                            </button>
{{--                        @endif--}}
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="fixed inset-0 flex items-center justify-center shadow-gray-500 z-[9998] hidden" wire:ignore id="vocabModalContainer">
        <div class="fixed inset-0 bg-black opacity-70 dark:bg-gray-900"></div>
        <div class="relative bg-white dark:bg-gray-800 w-[400px] h-[300px] rounded-lg shadow-lg">
            <div class="p-4 h-full flex flex-col">
                <h2 class="text-2xl font-bold mb-4 text-white dark:text-white" id="vocabModalTitle">Add Vocabulary</h2>

                {{--                <p class="text-black dark:text-white flex-auto">content</p>--}}

                <div id="vocabModalContent">
                    <label for="vocab-word" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Word
                    </label>
                    {{--                <div class="relative mb-6">--}}
                    <div class="flex mb-6">
                        {{--                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">--}}
                        {{--                    </div>--}}

                        <span
                            class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-r-0 border-gray-300 rounded-l-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                         class="w-5 h-5">
                            <path fill-rule="evenodd"
                                  d="M4.848 2.771A49.144 49.144 0 0112 2.25c2.43 0 4.817.178 7.152.52 1.978.292 3.348 2.024 3.348 3.97v6.02c0 1.946-1.37 3.678-3.348 3.97a48.901 48.901 0 01-3.476.383.39.39 0 00-.297.17l-2.755 4.133a.75.75 0 01-1.248 0l-2.755-4.133a.39.39 0 00-.297-.17 48.9 48.9 0 01-3.476-.384c-1.978-.29-3.348-2.024-3.348-3.97V6.741c0-1.946 1.37-3.68 3.348-3.97zM6.75 8.25a.75.75 0 01.75-.75h9a.75.75 0 010 1.5h-9a.75.75 0 01-.75-.75zm.75 2.25a.75.75 0 000 1.5H12a.75.75 0 000-1.5H7.5z"
                                  clip-rule="evenodd"/>
                        </svg>
                  </span>

                        <input type="text" id="vocab-word"
                               class="rounded-none rounded-r-lg bg-gray-50 border text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm border-gray-300 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                               placeholder="word">
                    </div>

                    <label for="vocab-sounds-like" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Sounds Like
                    </label>
                    <div class="flex">
                      <span
                          class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-r-0 border-gray-300 rounded-l-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                          <path d="M8.25 4.5a3.75 3.75 0 117.5 0v8.25a3.75 3.75 0 11-7.5 0V4.5z"/>
                          <path
                              d="M6 10.5a.75.75 0 01.75.75v1.5a5.25 5.25 0 1010.5 0v-1.5a.75.75 0 011.5 0v1.5a6.751 6.751 0 01-6 6.709v2.291h3a.75.75 0 010 1.5h-7.5a.75.75 0 010-1.5h3v-2.291a6.751 6.751 0 01-6-6.709v-1.5A.75.75 0 016 10.5z"/>
                        </svg>
                      </span>
                        <input type="text" id="vocab-sounds-like"
                               class="rounded-none rounded-r-lg bg-gray-50 border text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm border-gray-300 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                               placeholder="sounds like">
                    </div>


                </div>

                <div class="mt-4 text-right">
                    <button id="vocabModalConfirm"
                            class="bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white rounded-md px-4 py-2">
                        Add
                    </button>
                    <button id="vocabModalCancel"
                            class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-400 dark:hover:bg-gray-500 text-gray-700 rounded-md px-4 py-2">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{--  Info Card  --}}
    <x-card class="space-y-5">
        <div class="flex flex-row">
            <!-- column 1 -->
            <div class="basis-1/3">
                <p class="text-lg text-gray-900 dark:text-white text-right mr-3 font-bold">{{__("lbl-id")}}:</p>
            </div>
            <!-- column 2 -->
            <div class="basis-1/3">
                <p>
                    <x-badge-by-item-type :item-type="$trx['itemType']"/>{{$trx['reviewId']}}
                </p>
            </div>
            <!-- column 3 -->
            <div class="basis-1/3">
                <p class="text-lg text-gray-900 dark:text-white text-right mr-3 font-bold">{{__("lbl-external-id")}}:</p>
            </div>
            <!-- column 4 -->
            <div class="basis-1/3">
                <p>
                    <x-text-or-na>{{$trx['externalId']}}</x-text-or-na>
                </p>
            </div>
            <!-- column 5 -->
            <div class="basis-1/3">

            </div>
            <!-- column 6 -->
            <div class="basis-1/3">

            </div>
        </div>

        <div class="flex flex-row">
            <!-- column 1 -->
            <div class="basis-1/3">
                <p class="text-base text-gray-900 dark:text-white text-right mr-3 font-bold">{{__("lbl-account")}}:</p>
                <p class="text-base text-gray-900 dark:text-white text-right mr-3 font-bold">{{__("lbl-date-dictated")}}:</p>
                <p class="text-base text-gray-900 dark:text-white text-right mr-3 font-bold">{{__("lbl-word-count")}}:</p>
            </div>
            <!-- column 2 -->
            <div class="basis-1/3">
                <p>{{$trx['realUserName']}}</p>
                <p>{{$trx['submitTime']}}</p>
                <p><x-text-or-na>{{$trx['wordCount']}}</x-text-or-na></p>
            </div>
            <!-- column 3 -->
            <div class="basis-1/3">
                <p class="text-base text-gray-900 dark:text-white text-right mr-3 font-bold">{{__("lbl-audio-length")}}:</p>
                <p class="text-base text-gray-900 dark:text-white text-right mr-3 font-bold">{{__("lbl-audio-level")}}:</p>
            </div>
            <!-- column 4 -->
            <div class="basis-1/3">
                <p>{{$trx['audioLength']}}s</p>
                <p><x-audio-quality-viewer>{{$trx['audioQuality']}}</x-audio-quality-viewer></p>
            </div>
            <!-- column 5 -->
            <div class="basis-1/3">
                @if($trx["status"] == \App\Models\Enums\CorrectionTypes::CORRECTED->value)
                    <p class="text-base text-gray-900 dark:text-white text-right mr-3 font-bold">{{__("lbl-status")}}:</p>
                    <p class="text-base text-gray-900 dark:text-white text-right mr-3 font-bold">{{__("lbl-accuracy")}}:</p>
                    <p class="text-base text-gray-900 dark:text-white text-right mr-3 font-bold">{{__("lbl-reviewed-by")}}:</p>
                @endif
            </div>
            <!-- column 6 -->
            <div class="basis-1/3">
                @if($trx["status"] == \App\Models\Enums\CorrectionTypes::CORRECTED->value)
                    <p>{{$trx["status"]}}</p>
                    <p>{{$data["accuracy"]}} %</p>
                    <p>{{$trx["reviewedBy"]}}</p>
                @endif
            </div>
        </div>
    </x-card>

    {{--  Server Text Card  --}}
    <x-card>
        {{--  Server Text Tabs Buttons  --}}
        <div class="mb-4 border-b border-gray-200 dark:border-gray-700" wire:ignore wire:key="tab-panel-1-headers">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="textContentTabs"
                data-tabs-toggle="#textContent" role="tablist">
                <li class="mr-2" role="presentation">
                <button
                        class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300"
                        id="substituted-text-tab" data-tabs-target="#substitutedText" type="button" role="tab"
                        aria-controls="substitutedText" aria-selected="true">{{  __("tab-substituted-text") }}
                    </button>
                </li>
                <li role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg" id="original-text-tab"
                            data-tabs-target="#originalText" type="button" role="tab" aria-controls="originalText"
                            aria-selected="false">{{  __("tab-original-text") }}
                    </button>
                </li>
            </ul>
        </div>

        {{--  Server Text Tabs Content  --}}
        <div id="textContent" wire:key="tab-panel-1-data">
            <div class="p-2 rounded-lg bg-white dark:bg-gray-800" id="originalText" role="tabpanel"
                 aria-labelledby="original-text-tab">
                <div class="w-full mb-4 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                    <div class="flex items-center justify-between px-3 py-2 border-b dark:border-gray-600">
                        <div class="flex flex-wrap items-center divide-gray-200 sm:divide-x dark:divide-gray-600">
                            <div class="flex items-center space-x-1 sm:pr-4">
                                <span>Original Text</span>
                            </div>
                            {{--<div class="flex flex-wrap items-center space-x-1 sm:pl-4">
                                <button type="button" class="p-2 text-gray-500 rounded cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600">
                                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
                                    <span class="sr-only">Add list</span>
                                </button>
                            </div>--}}
                        </div>
                        <button type="button" data-tooltip-target="tooltip-copy-to-clipboard" class="p-2 text-gray-500 rounded cursor-pointer sm:ml-auto hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600"
                        id="copyOrgBtn"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                            </svg>

                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" />
                            </svg>
                            <span class="sr-only">Copy to clipboard</span>
                        </button>
                        <div id="tooltip-copy-to-clipboard" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                            Copy to clipboard
                            <div class="tooltip-arrow" data-popper-arrow></div>
                        </div>
                    </div>
                    <div class="px-4 py-2 bg-white rounded-b-lg dark:bg-gray-800">
                        <label for="editor" class="sr-only">Publish post</label>
{{--                        <textarea id="editor" rows="8" class="block w-full px-0 text-sm text-gray-800 bg-white border-0 dark:bg-gray-800 focus:ring-0 dark:text-white dark:placeholder-gray-400" placeholder="Write an article..." required></textarea>--}}

                        <textarea id="originalTextArea" readonly rows="4"
                                  class="block p-2.5 w-full text-sm text-gray-900 bg-white rounded-lg
{{--                                   border border-gray-300 dark:border-gray-600 dark:focus:ring-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 focus:border-blue-500 --}}
                                    focus:ring-0 border-0
                                   dark:bg-gray-800 dark:placeholder-gray-400 dark:text-white"
                                  placeholder="Original Text" wire:model.lazy="data.originalText"
                        >{{$data['originalText']}}</textarea>

                    </div>
                </div>
            </div>


            <div class="hidden p-2 rounded-lg bg-white dark:bg-gray-800" id="substitutedText" role="tabpanel"
                 aria-labelledby="substituted-text-tab">

                <div class="w-full mb-4 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                    <div class="flex items-center justify-between px-3 py-2 border-b dark:border-gray-600">
                        <div class="flex flex-wrap items-center divide-gray-200 sm:divide-x dark:divide-gray-600">
                            <div class="flex items-center space-x-1 sm:pr-4">
                                <span>Substituted Text</span>
                            </div>
                            {{--<div class="flex flex-wrap items-center space-x-1 sm:pl-4">
                                <button type="button" class="p-2 text-gray-500 rounded cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600">
                                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
                                    <span class="sr-only">Add list</span>
                                </button>
                            </div>--}}
                        </div>
                        <button type="button" data-tooltip-target="tooltip-copy-to-clipboard" class="p-2 text-gray-500 rounded cursor-pointer sm:ml-auto hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600"
                                id="copySubBtn"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                            </svg>

                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" />
                            </svg>
                            <span class="sr-only">Copy to clipboard</span>
                        </button>
                        <div id="tooltip-copy-to-clipboard" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                            Copy to clipboard
                            <div class="tooltip-arrow" data-popper-arrow></div>
                        </div>
                    </div>
                    <div class="px-4 py-2 bg-white rounded-b-lg dark:bg-gray-800">
                        <label for="editor" class="sr-only">Publish post</label>
                        {{--                        <textarea id="editor" rows="8" class="block w-full px-0 text-sm text-gray-800 bg-white border-0 dark:bg-gray-800 focus:ring-0 dark:text-white dark:placeholder-gray-400" placeholder="Write an article..." required></textarea>--}}

                        <textarea id="substitutedTextArea" readonly rows="4"
                                  class="block p-2.5 w-full text-sm text-gray-900 bg-white rounded-lg
{{--                                   border border-gray-300 dark:border-gray-600 dark:focus:ring-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 focus:border-blue-500 --}}
                                    focus:ring-0 border-0
                                   dark:bg-gray-800 dark:placeholder-gray-400 dark:text-white"
                                  placeholder="Substituted Text"  wire:model.lazy="data.substitutedText"
                        >{{$data['substitutedText']}}</textarea>

                    </div>
                </div>
            </div>
        </div>
    </x-card>

    {{--  Reviewed Text Card  --}}
    <x-card>
        @if(Auth::user()->isAdmin())

            {{--  Reviewed Text Tabs Buttons  --}}
            <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="reviewContentTabs"
                    data-tabs-toggle="#reviewedContent" role="tablist" wire:key="tab-panel-2-headers">
                    <li class="mr-2" role="presentation">
                        <button class="inline-block p-4 border-b-2 rounded-t-lg" id="corrected-text-tab"
                                data-tabs-target="#correctedText" type="button" role="tab" aria-controls="correctedText"
                                aria-selected="true">{{__("tab-corrected-text")}}
                        </button>
                    </li>

                    <li role="presentation">
                        <button
                            class="inline-block
                            {{$trx['status'] == \App\Models\Enums\CorrectionTypes::CORRECTED->value ? "" : "hidden"}}
                             p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300"
                            id="original-text-diff-tab" data-tabs-target="#originalTextDiff" type="button" role="tab"
                            aria-controls="originalTextDiff" aria-selected="false">{{__("tab-original-text-diff")}}
                        </button>
                    </li>
                    <li role="presentation">
                        <button
                            class="inline-block
                             {{$trx['status'] == \App\Models\Enums\CorrectionTypes::CORRECTED->value ? "" : "hidden"}}
                             p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300"
                            id="substituted-text-diff-tab" data-tabs-target="#substitutedTextDiff" type="button" role="tab"
                            aria-controls="substitutedTextDiff" aria-selected="false">{{__("tab-substituted-text-diff")}}
                        </button>
                    </li>
                </ul>
            </div>
            {{--  Reviewed Text Tabs Content  --}}
            <div id="reviewedContent" wire:key="tab-panel-2-data">
                <div class="p-2 rounded-lg bg-white dark:bg-gray-800" id="correctedText" role="tabpanel"
                     aria-labelledby="corrected-text-tab">

                    <div class="w-full mb-4 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                        <div class="flex items-center justify-between px-3 py-2 border-b dark:border-gray-600">
                            <div class="flex flex-wrap items-center divide-gray-200 sm:divide-x dark:divide-gray-600">
                                <div class="flex items-center space-x-1 sm:pr-4">
                                    <span>Corrected Text</span>
                                </div>
                                {{--<div class="flex flex-wrap items-center space-x-1 sm:pl-4">
                                    <button type="button" class="p-2 text-gray-500 rounded cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600">
                                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
                                        <span class="sr-only">Add list</span>
                                    </button>
                                </div>--}}
                            </div>
                            <button type="button" data-tooltip-target="tooltip-copy-to-clipboard" class="p-2 text-gray-500 rounded cursor-pointer sm:ml-auto hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600"
                                    id="copyCorrected"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                                </svg>

                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" />
                                </svg>
                                <span class="sr-only">Copy to clipboard</span>
                            </button>
                            <div id="tooltip-copy-to-clipboard" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                Copy to clipboard
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        </div>
                        <div class="px-4 py-2 bg-white rounded-b-lg dark:bg-gray-800">
                            <label for="editor" class="sr-only">Publish post</label>
                            {{--                        <textarea id="editor" rows="8" class="block w-full px-0 text-sm text-gray-800 bg-white border-0 dark:bg-gray-800 focus:ring-0 dark:text-white dark:placeholder-gray-400" placeholder="Write an article..." required></textarea>--}}


                            <textarea id="correctedTextArea" rows="4" {{$data['editing'] ? '' : 'disabled'}}
                            class="block p-2.5 w-full text-sm text-gray-900 bg-white rounded-lg
{{--                                   border border-gray-300 dark:border-gray-600 dark:focus:ring-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 focus:border-blue-500 --}}
                                    focus:ring-0 border-0
                                   dark:bg-gray-800 dark:placeholder-gray-400 dark:text-white"
                                      placeholder="{{__("textarea-hint-click-copy-text-for-correction-button-to-start")}}"
                                      wire:model.lazy="data.correctedText"
                            ></textarea>

                        </div>
                    </div>

                </div>
{{--                @if($trx['status'] == \App\Models\Enums\CorrectionTypes::CORRECTED->value)--}}
                <div class="hidden p-2 rounded-lg bg-white dark:bg-gray-800" id="originalTextDiff" role="tabpanel"
                     aria-labelledby="original-text-diff-tab"
                     wire:model.lazy="data.orgDiff"
                >
                    <input type="text" hidden="hidden" wire:model.lazy="data.orgDiff"/>
                    {!! $data['orgDiff'] !!}
                </div>
                <div class="hidden p-2 rounded-lg bg-white dark:bg-gray-800" id="substitutedTextDiff" role="tabpanel"
                     aria-labelledby="substituted-text-diff-tab"
                >
                    <input type="text" hidden="hidden" wire:model.lazy="data.subDiff"/>
                    {!! $data['subDiff'] !!}
                </div>
{{--                @endif--}}
            </div>

        @endif

        {{--  Audio Player / Admin Buttons (Copy, Add to vocab, download audio/text)  --}}
        <div class="flex flex-row mb-3">
            <div class="basis-1/4" id="AudioPlayerContainer">
{{--                @dd($trx['audio'])--}}
                <livewire:able-player :audio="$trx['audio']"/>
            </div>
            @if(Auth::user()->isAdmin())
                <div class="basis-1/2 text-center">
                    <button type="button" wire:click="copyForCorrection()"
                            class="
                                inline-flex items-center align-items-center px-4 py-2 text-sm font-medium focus:z-10 focus:ring-2 focus:ring-gray-300 dark:focus:ring-gray-700 dark:focus:text-white
                                rounded-lg
                                text-white   bg-gray-700         border-gray-600         hover:text-white     hover:bg-gray-600
                                dark:text-white dark:bg-gray-700    dark:border-gray-600    dark:hover:text-white   dark:hover:bg-gray-600">
                        <svg aria-hidden="true" class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"
                             xmlns="http://www.w3.org/2000/svg">
                            <path clip-rule="evenodd"
                                  d="M18 5.25a2.25 2.25 0 00-2.012-2.238A2.25 2.25 0 0013.75 1h-1.5a2.25 2.25 0 00-2.238 2.012c-.875.092-1.6.686-1.884 1.488H11A2.5 2.5 0 0113.5 7v7h2.25A2.25 2.25 0 0018 11.75v-6.5zM12.25 2.5a.75.75 0 00-.75.75v.25h3v-.25a.75.75 0 00-.75-.75h-1.5z"
                                  fill-rule="evenodd"></path>
                            <path clip-rule="evenodd"
                                  d="M3 6a1 1 0 00-1 1v10a1 1 0 001 1h8a1 1 0 001-1V7a1 1 0 00-1-1H3zm6.874 4.166a.75.75 0 10-1.248-.832l-2.493 3.739-.853-.853a.75.75 0 00-1.06 1.06l1.5 1.5a.75.75 0 001.154-.114l3-4.5z"
                                  fill-rule="evenodd"></path>
                        </svg>
                        {{ $trx['status'] == \App\Models\Enums\CorrectionTypes::CORRECTED->value ? __("btn-re-correct-text") : __("btn-copy-text-for-correction")}}
                    </button>

                    <button type="button"
                            id="addToVocabBtn"
                            class="inline-flex
                            {{  Auth::user()->isAdmin() && $data['editing'] ? '' : 'hidden' }}
                            items-center focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-2 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800"
                    >
                        <svg aria-hidden="true" fill="currentColor" class="w-4 h-4 mr-2" viewBox="0 0 20 20"
                             xmlns="http://www.w3.org/2000/svg">
                            <path clip-rule="evenodd"
                                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2.5a.75.75 0 000 1.5h2.5v2.5a.75.75 0 001.5 0v-2.5h2.5a.75.75 0 000-1.5h-2.5v-2.5z"
                                  fill-rule="evenodd"></path>
                        </svg>
                        {{__("btn-add-to-vocabulary")}}
                    </button>
                </div>
                <div class="basis-1/4 text-end">
                    <button type="button" wire:click="downloadAudio()"
                            class="inline-flex items-center
                        bg-blue-700 hover:bg-blue-800 focus:ring-2 focus:ring-gray-300 rounded-l-lg font-medium text-white text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-gray-700
                        ">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                             class="w-4 h-4 mr-2">
                            <path
                                d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75z"/>
                            <path
                                d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z"/>
                        </svg>
                        {{__("btn-audio")}}
                    </button>

                    <button type="button" wire:click="downloadText()"
                            class="inline-flex items-center focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-2 focus:ring-green-300 font-medium rounded-r-lg text-sm px-4 py-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                             class="w-4 h-4 mr-2">
                            <path
                                d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75z"/>
                            <path
                                d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z"/>
                        </svg>
                        {{__("btn-text")}}
                    </button>
                </div>
            @endif
        </div>
    </x-card>

    {{--  Controls --}}
    <x-card>
        {{-- Working way to grab errors on the go --}}
        <x-global-flash-error />

        <x-global-flash-success />

            <div class="flex flex-row">
                <div class="basis-full flex">
                    <div class="inline-flex rounded-md shadow-sm items-start" role="group">
                        <button type="button"
                                {{ $data['currentIndex'] == 0 ? 'disabled':'' }}
                                class="inline-flex align-items-center
                            disabled:opacity-50
                             text-white bg-blue-700 hover:bg-blue-800 focus:ring-2 focus:ring-gray-300 rounded-l-md font-medium text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-gray-700"
                                wire:click="prev"
                        >
                            {{--<svg aria-hidden="true" fill="currentColor" class="w-4 h-4 mr-2 block m-auto"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path clip-rule="evenodd"
                                      d="M11.47 7.72a.75.75 0 011.06 0l7.5 7.5a.75.75 0 11-1.06 1.06L12 9.31l-6.97 6.97a.75.75 0 01-1.06-1.06l7.5-7.5z"
                                      fill-rule="evenodd"></path>
                            </svg>--}}
                            <svg aria-hidden="true" class="w-5 h-5 mr-2 -ml-1" fill="currentColor" viewBox="0 0 20 20"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                      d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                                      clip-rule="evenodd"></path>
                            </svg>

                            {{__("btn-prev")}}
                        </button>

                        <button type="button"
                                {{ $data['currentIndex'] == (sizeof($data['transactions'])-1) ? 'disabled':'' }}
                                class="inline-flex align-items-center
                            disabled:opacity-50
                             text-white bg-blue-700 hover:bg-blue-800 focus:ring-2 focus:ring-blue-300 rounded-r-md font-medium text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                                wire:click="next"
                        >
                            {{__("btn-next")}}
                            {{--<svg aria-hidden="true" fill="currentColor" class="w-4 h-4 mr-2 block m-auto ml-2"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path clip-rule="evenodd"
                                      d="M12.53 16.28a.75.75 0 01-1.06 0l-7.5-7.5a.75.75 0 011.06-1.06L12 14.69l6.97-6.97a.75.75 0 111.06 1.06l-7.5 7.5z"
                                      fill-rule="evenodd"></path>
                            </svg>--}}
                            <svg aria-hidden="true" class="w-5 h-5 ml-2 -mr-1" fill="currentColor" viewBox="0 0 20 20"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                      d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                      clip-rule="evenodd"></path>
                            </svg>

                        </button>

                        <span class="justify-center inline-flex items-center ml-2 self-center">
                            {{ ($data['currentIndex']+1) . "/" . sizeof($data['transactions']) }}
                        </span>
                    </div>

                    <div class="inline-flex flex-auto rounded-md shadow-sm justify-end justify-items-end ml-4" role="group">

                        @if(Auth::user()->isAdmin())

                            @if($data['editing'])
                                <button type="button"
                                        wire:click="submitCorrection"
                                        class="inline-flex align-items-center px-4 py-2 focus:outline-none rounded-l-md text-white bg-indigo-600 hover:bg-indigo-500 focus:ring-2 focus:ring-indigo-300 font-medium text-sm dark:focus:ring-indigo-900">

                                    <svg aria-hidden="true" fill="currentColor" class="w-4 h-4 block m-auto mr-2"
                                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path clip-rule="evenodd"
                                              d="M9 1.5H5.625c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0016.5 9h-1.875a1.875 1.875 0 01-1.875-1.875V5.25A3.75 3.75 0 009 1.5zm6.61 10.936a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 14.47a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z"
                                              fill-rule="evenodd"></path>
                                        <path
                                            d="M12.971 1.816A5.23 5.23 0 0114.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 013.434 1.279 9.768 9.768 0 00-6.963-6.963z"></path>
                                    </svg>
                                    {{__("btn-save")}}
                                </button>

                                <button type="button" wire:click="clearAndReset"
                                        class="inline-flex align-items-center text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-2 focus:ring-gray-200 font-medium text-sm px-4 py-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">
                                    <svg aria-hidden="true" fill="currentColor" class="w-4 h-4 block m-auto mr-2"
                                         viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path clip-rule="evenodd"
                                              d="M5.625 1.5H9a3.75 3.75 0 013.75 3.75v1.875c0 1.036.84 1.875 1.875 1.875H16.5a3.75 3.75 0 013.75 3.75v7.875c0 1.035-.84 1.875-1.875 1.875H5.625a1.875 1.875 0 01-1.875-1.875V3.375c0-1.036.84-1.875 1.875-1.875zM9.75 14.25a.75.75 0 000 1.5H15a.75.75 0 000-1.5H9.75z"
                                              fill-rule="evenodd"></path>
                                        <path
                                            d="M14.25 5.25a5.23 5.23 0 00-1.279-3.434 9.768 9.768 0 016.963 6.963A5.23 5.23 0 0016.5 7.5h-1.875a.375.375 0 01-.375-.375V5.25z"></path>
                                    </svg>
                                    {{__("btn-clear")}}
                                </button>
                                <button type="button" wire:click="reject"
                                        class="inline-flex align-items-center px-4 py-2 focus:outline-none text-white bg-yellow-400 hover:bg-yellow-500 focus:ring-2 focus:ring-yellow-300 font-medium text-sm dark:focus:ring-yellow-900">
                                    <svg aria-hidden="true" fill="currentColor" class="w-4 h-4 block m-auto mr-2"
                                         viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M15.73 5.25h1.035A7.465 7.465 0 0118 9.375a7.465 7.465 0 01-1.235 4.125h-.148c-.806 0-1.534.446-2.031 1.08a9.04 9.04 0 01-2.861 2.4c-.723.384-1.35.956-1.653 1.715a4.498 4.498 0 00-.322 1.672V21a.75.75 0 01-.75.75 2.25 2.25 0 01-2.25-2.25c0-1.152.26-2.243.723-3.218C7.74 15.724 7.366 15 6.748 15H3.622c-1.026 0-1.945-.694-2.054-1.715A12.134 12.134 0 011.5 12c0-2.848.992-5.464 2.649-7.521.388-.482.987-.729 1.605-.729H9.77a4.5 4.5 0 011.423.23l3.114 1.04a4.5 4.5 0 001.423.23zM21.669 13.773c.536-1.362.831-2.845.831-4.398 0-1.22-.182-2.398-.52-3.507-.26-.85-1.084-1.368-1.973-1.368H19.1c-.445 0-.72.498-.523.898.591 1.2.924 2.55.924 3.977a8.959 8.959 0 01-1.302 4.666c-.245.403.028.959.5.959h1.053c.832 0 1.612-.453 1.918-1.227z"></path>
                                    </svg>
                                    {{__("btn-poor-quality")}}
                                </button>
                            @endif
                            @endif

                        <button type="button" wire:click="close()"
                                class="
                                inline-flex align-items-center px-4 py-2 text-sm font-medium focus:z-10 focus:ring-2 focus:ring-gray-300 dark:focus:ring-gray-700 dark:focus:text-white
                                {{Auth::user()->isAdmin() && $data['editing'] ? "rounded-r-md" : "rounded-md"}}
                                border
                                text-white   bg-gray-700         border-gray-600         hover:text-white     hover:bg-gray-600
                                dark:text-white dark:bg-gray-700    dark:border-gray-600    dark:hover:text-white   dark:hover:bg-gray-600">
                            <svg aria-hidden="true" fill="none" stroke="currentColor" class="w-4 h-4 block m-auto mr-2"
                                 stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                      stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            {{__("btn-close")}}
                        </button>
                    </div>
                </div>
            </div>
        </x-card>


</div>
