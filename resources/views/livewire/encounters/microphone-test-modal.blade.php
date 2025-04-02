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
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto">
        <div class="p-6">
            <h2 class="text-xl font-bold mb-4">Test Microphone</h2>
            <div class="p-6">
                <div id="mic-status">
                    <div class="mx-auto mb-6 rounded-full h-36 w-36 object-cover @if($mic_available) bg-primary-500 @else bg-gray-100 @endif  p-4 shadow-lg cursor-pointer transition transform active:scale-95" title="Mic Disabled">
                        <svg class="" fill="#ffffff" version="1.1" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 512 512" xmlns:xlink="http://www.w3.org/1999/xlink"
                             enable-background="new 0 0 512 512">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <g>
                                    <g>
                                        <path d="m439.5,236c0-11.3-9.1-20.4-20.4-20.4s-20.4,9.1-20.4,20.4c0,70-64,126.9-142.7,126.9-78.7,0-142.7-56.9-142.7-126.9 0-11.3-9.1-20.4-20.4-20.4s-20.4,9.1-20.4,20.4c0,86.2 71.5,157.4 163.1,166.7v57.5h-23.6c-11.3,0-20.4,9.1-20.4,20.4 0,11.3 9.1,20.4 20.4,20.4h88c11.3,0 20.4-9.1 20.4-20.4 0-11.3-9.1-20.4-20.4-20.4h-23.6v-57.5c91.6-9.3 163.1-80.5 163.1-166.7z"></path>
                                        <path d="m256,323.5c51,0 92.3-41.3 92.3-92.3v-127.9c0-51-41.3-92.3-92.3-92.3s-92.3,41.3-92.3,92.3v127.9c0,51 41.3,92.3 92.3,92.3zm-52.3-220.2c0-28.8 23.5-52.3 52.3-52.3s52.3,23.5 52.3,52.3v127.9c0,28.8-23.5,52.3-52.3,52.3s-52.3-23.5-52.3-52.3v-127.9z"></path>
                                    </g>
                                </g>
                            </g>
                        </svg>
                    </div>

                    <audio id="myAudio" autoplay muted></audio>

                    <div class="flex justify-center items-center">
                        @if($mic_available)
                            <button wire:click="test_mic" class="mt-3 bg-primary-500 text-white px-4 py-2 rounded"><i class="fa fa-record-vinyl"></i> Test Microphone</button>
                        @else
                            <button wire:click="request_mic" class="mt-3 bg-green-500 text-white px-4 py-2 rounded"><i class="fa fa-check-circle"></i> Enable Microphone</button>
                        @endif
                    </div>

                </div>
            </div>
            <div class="flex justify-end">
                <button type="button" wire:click="close_modal()" class="bg-gray-500 text-white px-4 py-2 rounded">
                    <i class="fa fa-remove"></i> Cancel
                </button>
            </div>
        </div>
    </div>
</div>




