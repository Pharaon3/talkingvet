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

    <div class="bg-white dark:bg-gray-800 dark:text-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto">
        <div class="p-6">
            <h2 class="text-xl font-bold mb-4">Record Visit</h2>
            <!-- Form content -->
            <form wire:submit.prevent="record_encounter">
                <!-- Practitioner Dropdown -->
                <div class="mb-4">
                    <label for="practitioner" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Practitioner</label>
                    <select id="practitioner" wire:model="practitioner"
                        class="block mt-1 w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm">
                        <option value="">-- Select Practitioner --</option> <!-- Default placeholder option -->
                        @foreach($practitioners as $practitioner_user)
                             <option value="{{$practitioner_user->id}}">{{$practitioner_user->firstname . ' ' . $practitioner_user->lastname}}</option>
                         @endforeach
                    </select>
                </div>

                <!-- Identifier Text Field -->
                <div class="mb-4">
                    <label for="identifier" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Patient</label>
                    <input type="text" id="identifier" disabled
                        class="block mt-1 w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm"
                        value="{{$identifier}}">
                </div>

                <!-- Notes Text Area -->
                <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <textarea id="notes" rows="4" disabled
                        class="block mt-1 w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm">{{ $notes }}</textarea>
                </div>

                <!-- Patient Consent Checkbox -->
                <div class="mb-4">
                    <label for="consent" class="inline-flex items-center">
                        <input type="checkbox" id="consent" wire:model="consent" class="form-checkbox dark:bg-gray-700 dark:border-gray-600">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Patient Consent Received</span>
                    </label>
                </div>

                <!-- Round Image at Bottom -->
                <div>
                    <div id="recordButton"
                        class="mx-auto mb-6 rounded-full h-36 w-36 object-cover bg-red-500 p-4 shadow-lg cursor-pointer transition transform hover:bg-red-600 active:bg-red-700 active:scale-95 flex items-center justify-center relative overflow-hidden"
                        @if (!$practitioner || $upload_audio || !$consent) style="pointer-events: none; opacity: 0.5;" @endif>
                        <!-- Mic Icon -->
                        <svg id="micIcon" class="w-20 h-20 transition-transform" fill="#ffffff" version="1.1"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <g id="SVGRepo_iconCarrier">
                                <g>
                                    <path d="m439.5,236c0-11.3-9.1-20.4-20.4-20.4s-20.4,9.1-20.4,20.4c0,70-64,126.9-142.7,126.9-78.7,0-142.7-56.9-142.7-126.9 0-11.3-9.1-20.4-20.4-20.4s-20.4,9.1-20.4,20.4c0,86.2 71.5,157.4 163.1,166.7v57.5h-23.6c-11.3,0-20.4,9.1-20.4,20.4 0,11.3 9.1,20.4 20.4,20.4h88c11.3,0 20.4-9.1 20.4-20.4 0-11.3-9.1-20.4-20.4-20.4h-23.6v-57.5c91.6-9.3 163.1-80.5 163.1-166.7z"></path>
                                    <path d="m256,323.5c51,0 92.3-41.3 92.3-92.3v-127.9c0-51-41.3-92.3-92.3-92.3s-92.3,41.3-92.3,92.3v127.9c0,51 41.3 92.3 92.3 92.3zm-52.3-220.2c0-28.8 23.5-52.3 52.3-52.3s52.3,23.5 52.3 52.3v127.9c0,28.8-23.5 52.3-52.3-52.3v-127.9z"></path>
                                </g>
                            </g>
                        </svg>

                        <!-- Red Stop Icon -->
                        <div id="stopIcon" class="hidden bg-white w-20 h-20"></div>

                        <!-- Blue Background + White Sine Wave -->
                        <div id="sineWave" class="absolute inset-0 hidden bg-blue-500 flex items-center justify-center">
                            <svg class="w-full h-full opacity-70" viewBox="0 0 100 100" preserveAspectRatio="none">
                                <path id="sinePath" d="" stroke="#ffffff" stroke-width="2" fill="none"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="mb-4 flex flex-col items-start space-y-4">
    <!-- Buttons Section -->
    <div class="flex items-center justify-between w-full">
        <!-- Upload Audio Button -->
        <div>
            <button type="button" class="bg-blue-500 text-white px-4 py-2 rounded min-h-[42px] w-40 transition disabled:opacity-50 disabled:cursor-not-allowed" onclick="document.getElementById('upload_audio').click()" @if (!$practitioner) disabled @endif>
                <i class="fa fa-file-audio"></i> Upload Audio
            </button>
        </div>

        <!-- Save & Close and Cancel Buttons -->
        <div class="flex items-center space-x-2">
            <button type="button" wire:click="save_and_close()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded min-h-[42px] w-40 transition">
                <i class="fa fa-save"></i> Save & Close
            </button>
            <button type="button" wire:click="close_modal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded min-h-[42px] w-40 transition">
                <i class="fa fa-remove"></i> Cancel
            </button>
        </div>
    </div>

    <!-- Display Uploading Indicator or Filename -->
    <div class="w-full mt-2 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-4 py-2 rounded">
    <div wire:loading wire:target="upload_audio" class="flex items-center justify-center">
            <span class="text-blue-500 font-bold">Uploading...</span>
        </div>
      <!-- Display Filename -->
      @if (!$errors->has('upload_audio') && $upload_audio)
        @if (is_array($upload_audio))
            @foreach ($upload_audio as $index => $file)
            <div class="flex items-center justify-between">
                <span class="truncate w-full overflow-hidden text-ellipsis">{{ is_object($file) ? $file->getClientOriginalName() : $file }}</span>
                <button type="button" wire:click="removeFile({{ $index }})" class="ml-2 text-red-500 hover:text-red-700">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            @endforeach
        @else
            <div class="flex items-center justify-between">
                <span class="truncate w-full overflow-hidden text-ellipsis">{{ is_object($upload_audio) ? $upload_audio->getClientOriginalName() : $upload_audio }}</span>
                <button type="button" wire:click="removeFile(0)" class="ml-2 text-red-500 hover:text-red-700">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        @endif
    @else
    <div class="text-red-500 font-bold">
        {{ $errors->first('upload_audio') }}
    </div>
    @endif
    </div>
    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

</div>

<!-- Hidden File Input -->
<input type="file" id="upload_audio" wire:model="upload_audio" class="hidden" accept=".m4a">
            </form>
        </div>
    </div>
</div>

<script>
    let selectedMicrophone = localStorage.getItem("selectedMicrophone") || "Default Microphone";

    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("active-microphone").innerText = selectedMicrophone;
        getMicrophones();
    });

    document.getElementById('upload_audio').addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file && file.size > 20 * 1024 * 1024) { // 20MB in bytes
            alert('File size must be less than 20MB');
            event.target.value = ''; // Clear the file input
        }
    });

    async function getMicrophones() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const audioDevices = devices.filter(device => device.kind === "audioinput");
            const select = document.getElementById("microphone-select");
            select.innerHTML = "";
            audioDevices.forEach(device => {
                let option = document.createElement("option");
                option.value = device.deviceId;
                option.textContent = device.label || `Microphone ${select.length + 1}`;
                select.appendChild(option);
            });
        } catch (error) {
            console.error("Error getting microphones:", error);
        }
    }

    function openMicrophonePopup() {
        document.getElementById("microphone-popup").classList.remove("hidden");
        getMicrophones();
    }

    function setMicrophone() {
        const selected = document.getElementById("microphone-select").selectedOptions[0];
        selectedMicrophone = selected.textContent;
        localStorage.setItem("selectedMicrophone", selectedMicrophone);
        document.getElementById("active-microphone").innerText = selectedMicrophone;
        Livewire.emit('updateMicrophone', selectedMicrophone);
    }

let mediaRecorder;
let audioChunks = [];
let isRecording = false;

const recordButton = document.getElementById('recordButton');
const micIcon = document.getElementById('micIcon');
const stopIcon = document.getElementById('stopIcon');
const sineWave = document.getElementById('sineWave');
const sinePath = document.getElementById('sinePath');

let audioContext, analyser, dataArray;

async function setupAudioAnalysis(stream) {
    audioContext = new (window.AudioContext || window.webkitAudioContext)();
    analyser = audioContext.createAnalyser();
    const source = audioContext.createMediaStreamSource(stream);
    source.connect(analyser);
    analyser.fftSize = 256;
    dataArray = new Uint8Array(analyser.frequencyBinCount);
}

function animateSineWave() {
    analyser.getByteFrequencyData(dataArray);

    // Calculate average volume to control amplitude
    const averageVolume = dataArray.reduce((sum, value) => sum + value, 0) / dataArray.length;
    const amplitude = Math.min(averageVolume / 3, 30);

    let path = '';
    const frequency = 0.2;
    for (let x = 0; x <= 100; x++) {
        const y = 50 + amplitude * Math.sin((x + performance.now() * frequency) * 0.1);
        path += `${x === 0 ? 'M' : 'L'} ${x} ${y} `;
    }
    sinePath.setAttribute('d', path);

    if (isRecording) requestAnimationFrame(animateSineWave);
}

recordButton.addEventListener('click', async () => {
    if (!isRecording) {
        // Start Recording
        isRecording = true;
        micIcon.classList.add('hidden');
        sineWave.classList.remove('hidden');

        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        setupAudioAnalysis(stream);
        animateSineWave();

        mediaRecorder = new MediaRecorder(stream);
        mediaRecorder.ondataavailable = (event) => audioChunks.push(event.data);

        mediaRecorder.onstop = async () => {
            const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
            audioChunks = [];

            // Convert blob to base64 and send to Livewire
            const reader = new FileReader();
            reader.readAsDataURL(audioBlob);
            reader.onloadend = () => {
                const base64Audio = reader.result.split(',')[1];
                @this.call('uploadAudio', base64Audio);
            };

            // Reset UI
            micIcon.classList.remove('hidden');
            sineWave.classList.add('hidden');
            stopIcon.classList.add('hidden');
        };

        mediaRecorder.start();
    } else {
        // Stop Recording
        isRecording = false;
        mediaRecorder.stop();
        audioContext.close();
    }
});

// Show stop icon on hover when recording
recordButton.addEventListener('mouseenter', () => {
    if (isRecording) {
        stopIcon.classList.remove('hidden');
        sineWave.classList.add('hidden');
    }
});

recordButton.addEventListener('mouseleave', () => {
    if (isRecording) {
        stopIcon.classList.add('hidden');
        sineWave.classList.remove('hidden');
    }
});
</script>