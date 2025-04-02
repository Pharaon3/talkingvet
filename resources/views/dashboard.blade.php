<x-app-layout>
    <x-slot name="privateScripts">
        <!-- Scripts -->
        @vite(['resources/js/pages/dashboard.js'])
        @vite(['resources/scss/libs-override/flatpicker-confirm-date.css'])

    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
            {{ __('dashboard.header') }}
        </h2>
    </x-slot>


    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg dark:border-gray-700">

        <div class="p-6 text-gray-900 dark:text-white ">
            <x-global-flash-error />
            {{ __("Welcome ") . session('username') }}
        </div>
    </div>
    <livewire:transactions-table-component :accounts="Auth::user()->getAccountsList()" />

    {{--<div class="p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        Test Preview
        <livewire:transactions-table />
    </div>--}}

    {{--<div class="p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400" id="TransactionsTbl">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="p-4">
                        <div class="flex items-center">
                            <input id="checkbox-all-search" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <label for="checkbox-all-search" class="sr-only">checkbox</label>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3">
                        #
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Date/Time
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Account
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Word Count
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Audio Length
                    </th>
                    <th scope="col" class="px-6 py-3">
                        External ID
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody>

                @for($i = 0; $i < 10; $i++)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="w-4 p-4">
                            <div class="flex items-center">
                                <input id="checkbox-table-search-1" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="checkbox-table-search-1" class="sr-only">checkbox</label>
                            </div>
                        </td>
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                            1
                        </th>
                        <td class="px-6 py-4">
                            {{ fake()->date('m-d-Y') }}
                        </td>
                        <td class="px-6 py-4">
                            {{ fake()->userName }}
                        </td>
                        <td class="px-6 py-4">
                            {{ fake()->numberBetween(10,3000)  }}
                        </td>
                        <td class="px-6 py-4">
                            {{ fake()->time()  }}
                        </td>
                        <td class="px-6 py-4">
                            {{ fake()->uuid()  }}
                        </td>
                        <td class="px-6 py-4">
                            N/A
                        </td>
                    </tr>
                @endfor

                </tbody>
            </table>
            <nav class="flex items-center justify-between p-4" aria-label="Table navigation">
                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">Showing <span class="font-semibold text-gray-900 dark:text-white">1-10</span> of <span class="font-semibold text-gray-900 dark:text-white">1000</span></span>
                <ul class="inline-flex items-center -space-x-px">
                    <li>
                        <a href="#" class="block px-3 py-2 ml-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                            <span class="sr-only">Previous</span>
                            <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">1</a>
                    </li>
                    <li>
                        <a href="#" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">2</a>
                    </li>
                    <li>
                        <a href="#" aria-current="page" class="z-10 px-3 py-2 leading-tight text-blue-600 border border-blue-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white">3</a>
                    </li>
                    <li>
                        <a href="#" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">...</a>
                    </li>
                    <li>
                        <a href="#" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">100</a>
                    </li>
                    <li>
                        <a href="#" class="block px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                            <span class="sr-only">Next</span>
                            <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>



        --}}{{--<div class="flex flex-col">
            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 sm:px-6 lg:px-8">
                    <div class="overflow-hidden">
                        <table class="min-w-full text-left text-sm font-light">
                            <thead class="border-b font-medium dark:border-neutral-500">
                            <tr>
                                <th scope="col" class="px-6 py-4">#</th>
                                <th scope="col" class="px-6 py-4">Date/Time</th>
                                <th scope="col" class="px-6 py-4">Account</th>
                                <th scope="col" class="px-6 py-4">Word Count</th>
                                <th scope="col" class="px-6 py-4">Audio Length</th>
                                <th scope="col" class="px-6 py-4">External ID</th>
                                <th scope="col" class="px-6 py-4">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600">
                                <td class="whitespace-nowrap px-6 py-4 font-medium">1</td>
                                <td class="whitespace-nowrap px-6 py-4">Mark</td>
                                <td class="whitespace-nowrap px-6 py-4">Otto</td>
                                <td class="whitespace-nowrap px-6 py-4">@mdo</td>
                            </tr>
                            <tr
                                class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600">
                                <td class="whitespace-nowrap px-6 py-4 font-medium">2</td>
                                <td class="whitespace-nowrap px-6 py-4">Jacob</td>
                                <td class="whitespace-nowrap px-6 py-4">Thornton</td>
                                <td class="whitespace-nowrap px-6 py-4">@fat</td>
                            </tr>
                            <tr
                                class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600">
                                <td class="whitespace-nowrap px-6 py-4 font-medium">3</td>
                                <td class="whitespace-nowrap px-6 py-4">Larry</td>
                                <td class="whitespace-nowrap px-6 py-4">Wild</td>
                                <td class="whitespace-nowrap px-6 py-4">@twitter</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>--}}{{--
    </div>--}}

</x-app-layout>
