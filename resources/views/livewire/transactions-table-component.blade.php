<div class="space-y-6">

    <x-card>
        <div class="flex flex-row mb-3">

            <div class="basis-1/4 inline-flex align-content-center items-center" wire:ignore>
                <label class="whitespace-nowrap mr-2" for="date-range">Account: </label>
                {{--                          <label for="countries" class="inline-flex mb-2 text-sm font-medium text-gray-900 dark:text-white">Account</label>--}}
{{--                <select id="account"--}}
{{--                        wire:model="opts.account" wire:change="refresh"--}}
{{--                        class="bg-gray-50 border border-gray-300 text-gray-900  text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">--}}
{{--                    @if(empty($accounts))--}}
{{--                        <option value="0" selected>{{ Auth::user()->username }}</option>--}}
{{--                    @else--}}
{{--                        @foreach($accounts as $key => $acc)--}}
{{--                            <option value="{{$acc}}">{{ $acc }}</option>--}}
{{--                        @endforeach--}}
{{--                    @endif--}}
{{--                </select>--}}

                <!-- accounts dropdown search button -->
                <button id="accountsDropdownSearchButton" data-dropdown-toggle="accountsDropdownSearch"
                        data-dropdown-placement="bottom"
                        wire:ignore
                        class="
                        bg-gray-50 border
                        border-gray-300 text-gray-900
                         text-sm rounded-lg focus:ring-blue-500
                         focus:border-blue-500
                         p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500
                        dark:hover:bg-gray-600
                        w-full
                        font-medium text-center
                         inline-flex items-center"
                        type="button">

                    <span id="accountsDropdownBtnText" class="flex-auto text-left">All</span>
                    <svg class="w-4 h-4 ml-2" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown menu -->
                <div id="accountsDropdownSearch" class="z-10 hidden bg-white rounded-lg shadow w-60 dark:bg-gray-700" wire:ignore>
                    <div class="p-3">
                        <label for="input-group-search" class="sr-only">Search</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path></svg>
                            </div>
                            <input type="text" id="input-group-search"
                                   class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                   placeholder="Search account">
                        </div>
                    </div>
                    <ul id="accountsDropdownOptions" class="h-48 px-3 pb-3 overflow-y-auto text-sm text-gray-700 dark:text-gray-200" aria-labelledby="accountsDropdownSearchButton" wire:ignore>


                        @if(empty($accounts))
{{--                            <option value="0" selected>{{ Auth::user()->username }}</option>--}}

                            <li>
                                <div class="flex items-center pl-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
{{--                                    <input id="checkbox-item-1" type="checkbox" value="" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">--}}
                                    <label for="checkbox-item-1" class="w-full py-2 ml-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300">{{ Auth::user()->username }}</label>
                                </div>
                            </li>

                        @else
                            @foreach($accounts as $key => $acc)
                                <li>
                                    <div class="flex items-center pl-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
{{--                                        <input id="checkbox-item-{{$key}}" type="radio" value="" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">--}}
                                        <label for="checkbox-item-{{$key}}" class="w-full py-2 ml-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300">{{ $acc }}</label>
                                    </div>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                    {{--<a href="#" class="flex items-center p-3 text-sm font-medium text-red-600 border-t border-gray-200 rounded-b-lg bg-gray-50 dark:border-gray-600 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-red-500 hover:underline">
                        <svg class="w-5 h-5 mr-1" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M11 6a3 3 0 11-6 0 3 3 0 016 0zM14 17a6 6 0 00-12 0h12zM13 8a1 1 0 100 2h4a1 1 0 100-2h-4z"></path></svg>
                        Delete user
                    </a>--}}
                </div>


            </div>

            <div class="basis-1/4 inline-flex align-content-center items-center">
                <label class="whitespace-nowrap ml-2 mr-2" for="date-range">Type: </label>
                {{--                          <label for="countries" class="inline-flex mb-2 text-sm font-medium text-gray-900 dark:text-white">Account</label>--}}
                <select id="itemType" wire:model.lazy="opts.itemType" wire:change="refresh" title="Job type"
                        class="bg-gray-50 border border-gray-300 text-gray-900 dark:hover:bg-gray-600 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="">Any</option>
                    <option value="{{\App\Models\Nvoq\TransactionItemType::DICTATION}}">Dictation</option>
                    <option value="{{\App\Models\Nvoq\TransactionItemType::SHORTCUT}}">Shortcut</option>
                    <option value="{{\App\Models\Nvoq\TransactionItemType::POPUP_SHORTCUT}}">Popup Shortcut</option>
                </select>
            </div>

            <div class="flex basis-2/4 justify-end">
                <div class="flex items-center">
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor"
                                 viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                      d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                      clip-rule="evenodd"></path>
                            </svg>
                        </div>
                       {{-- <input type="text" id="simple-search" wire:model.defer="opts.searchText"
                               wire:keydown.enter="refresh"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                               placeholder="Search (External ID)" required>--}}

                        <div class="flex items-center">
                            <label for="job-search" class="sr-only">Search</label>
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path></svg>
                                </div>
                                <input type="text"
                                       wire:model.defer="opts.searchText"
                                       wire:keydown.enter="refresh"
                                       id="job-search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                       placeholder="Search (External ID)"
                                       required>
                                <button type="button"
                                        wire:click="clearSearch"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 {{ empty($opts['searchText']) ? "hidden" : "" }}">
                                    <svg aria-hidden="true"
                                         class="w-4 h-4 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
                                         fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                              d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z"
                                              clip-rule="evenodd"/>
                                    </svg>

{{--

                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                         class="w-6 h-6">

                                    </svg>
--}}


                                </button>
                            </div>
                            <button type="button" wire:click="refresh"
                                    class="p-2.5 ml-2 text-sm font-medium text-white bg-blue-700 rounded-lg border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <span class="sr-only">Search</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="flex flex-row">

            <div class="basis-3/5">
                <div class="flex items-center">
                    <label class="whitespace-nowrap mr-2" for="date-range">Date Range:</label>
                    <input type="text" id="dateRange" name="date-range"
                           wire:ignore
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                           placeholder="Date Range">
                    {{--<input type="text" id="dateStart" name="date-start"
                           wire:ignore
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                           placeholder="Start Date">
                    <span class="mx-4 text-gray-500">to</span>
                    <input type="text" id="dateEnd" name="date-end"
                           wire:ignore
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                           placeholder="End Date">--}}
                </div>
            </div>

            <div class="flex basis-2/5 justify-end">
                <x-button wire:click="refresh()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                         class="w-6 h-6 inline">
                        <path fill-rule="evenodd"
                              d="M4.755 10.059a7.5 7.5 0 0112.548-3.364l1.903 1.903h-3.183a.75.75 0 100 1.5h4.992a.75.75 0 00.75-.75V4.356a.75.75 0 00-1.5 0v3.18l-1.9-1.9A9 9 0 003.306 9.67a.75.75 0 101.45.388zm15.408 3.352a.75.75 0 00-.919.53 7.5 7.5 0 01-12.548 3.364l-1.902-1.903h3.183a.75.75 0 000-1.5H2.984a.75.75 0 00-.75.75v4.992a.75.75 0 001.5 0v-3.18l1.9 1.9a9 9 0 0015.059-4.035.75.75 0 00-.53-.918z"
                              clip-rule="evenodd"/>
                    </svg>
                    Reload
                </x-button>
            </div>
        </div>
    </x-card>

    @if(env('APP_DEBUG') == true)
        <x-card>
            <div class="flex flex-row">
                <div class="basis-full flex items-start">

                    <b class="mr-1">DEBUG ONLY (TZ:auto - nvoq format):</b><i class="text-gray-500">{{$opts["startDate"] }} <span class="mx-4">to</span> {{ $opts["endDate"] }}</i>

                </div>
            </div>
        </x-card>
    @endif

    <x-card>
        {{--            <x-global-status-or-error />--}}
        {{--        @dd($errors->all(), $errors->trxTableBag)--}}
        @if ($errors->any())
            <div
                wire:ignore
                class="mb-4 rounded-lg bg-danger-100 px-6 py-5 text-base text-danger-700"
                role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {{--
                @foreach($errors->trxTableBag->all() as $err)
                                        @dd($err)
                    <div
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 6000)"
                        class="mb-4 rounded-lg bg-danger-100 px-6 py-5 text-base text-danger-700"
                        role="alert"
                    >
                        {{ $err }}
                    </div>
                @endforeach--}}

        @if (session()->has('trx-table'))

            <div class="italic text-gray-500 text-end">

                {{ session('trx-table') }}

            </div>

        @endif

        <div x-data="{ sortColumn: '', sortDirection: 'asc' }">
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400" id="TransactionsTbl">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        {{--                <th scope="col" class="p-4">
                                            <div class="flex items-center">
                                                <input id="checkbox-all-search" type="checkbox"
                                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="checkbox-all-search" class="sr-only">checkbox</label>
                                            </div>
                                        </th>--}}

                        @foreach($columns as $col)
                            @if($col->hidden) @continue @endif

                            <th scope="col" class="px-6 py-3"
                            >
                                @unless($col->sortable)
                                    <span
                                        class="flex items-center space-x-1 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider group focus:outline-none dark:text-gray-400">
                                {{ $col->getTitle() }}
                            </span>
                                @else

                                    <button
                                        wire:click="sortBy('{{ $col->getColumnSelectName() }}')"
                                        class="flex items-center space-x-1 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider group focus:outline-none dark:text-gray-400"
                                        {{--{{
                                            $attributes->merge($customSortButtonAttributes)
                                                ->class(['flex items-center space-x-1 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider group focus:outline-none dark:text-gray-400' => $customSortButtonAttributes['default'] ?? true])
                                                ->except(['default', 'wire:key'])
                                        }}--}}
                                    >
                                        <span>{{ $col->getTitle() }}</span>

                                        <span class="relative flex items-center">
                                    @unless($sortColumn === $col->getColumnSelectName())
                                                <svg
                                                    class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                    xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M5 15l7-7 7 7"></path>
                                        </svg>
                                            @else
                                                @if ($sortDirection === 'asc')
                                                    <svg class="w-3 h-3 group-hover:opacity-0" fill="none"
                                                         stroke="currentColor"
                                                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M5 15l7-7 7 7"></path>
                                            </svg>

                                                    <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 absolute"
                                                         fill="none"
                                                         stroke="currentColor" viewBox="0 0 24 24"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                                @elseif ($sortDirection === 'desc')
                                                    <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 absolute"
                                                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>

                                                    <svg class="w-3 h-3 group-hover:opacity-0" fill="none"
                                                         stroke="currentColor"
                                                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                                @endif
                                            @endunless
                                </span>
                                    </button>

                                @endunless
                            </th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="p-4 w-full hidden" colspan="{{ count($columns) }}" wire:loading.class.remove="hidden">
                            <div class="flex flex-col items-center">
                                <svg aria-hidden="true" class="w-12 h-12 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <!-- SVG path goes here -->
                                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                                </svg>
                            </div>
                        </td>
                    </tr>

                    @if(empty($data))
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                            wire:loading.class.delay="opacity-50 dark:bg-blue-900 dark:opacity-60"
                        >
                            <td class="p-4" colspan="{{ count($columns) }}">No results found</td>
                        </tr>
                    @else
                        @foreach($data as $transaction)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                                wire:loading.class.delay="opacity-50 dark:bg-blue-900 dark:opacity-60"
{{--                                x-data="{ trx: @js($transaction) }"--}}
{{--                                x-on:dblClick="alert('{{$transaction['reviewId']}}')"--}}
{{--                                x-on:dblClick="@php($wire.review($transaction['reviewId']))"--}}
{{--                                @dblClick="$wire.review({{$transaction['reviewId']}})"--}}
                                x-on:dblClick="$wire.review('{{$transaction['id']}}')"
                            >
                                <td class="w-4 p-4 whitespace-nowrap"
                                >
                                    <x-badge-by-item-type :item-type="$transaction['itemType']" />
                                    {{ $transaction['reviewId'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $transaction['submitTime'] }}</td>
                                <td class="px-6 py-4">{{ $transaction['realUserName'] }}</td>
                                <td class="px-6 py-4">{{ $transaction['wordCount'] }}</td>
                                <td class="px-6 py-4"> {{--Audio Length Col --}}
                                    @if(strtolower($transaction['itemType']) == \App\Models\Nvoq\TransactionItemType::DICTATION) {{$transaction['audioLength']}}s
                                    @else
                                        <x-gray-badge>{{$transaction['itemType']}}</x-gray-badge>
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $transaction['externalId'] }}</td>
                                <!-- <td class="px-6 py-4"><x-audio-quality-viewer>{{$transaction['audioQuality']}}</x-audio-quality-viewer></td> -->
                                <td></td>
                            </tr>
                        @endforeach
                    @endunless
                    </tbody>
                </table>
            </div>
        </div>
    </x-card>


</div>
