<div>
    <audio id="{{ $playerId }}" controls data-able-player data-width="360" wire:ignore>
        Your browser does not support the audio element.
    </audio>

    <script>
        // Get the audio element
        var audioElement = document.getElementById('{{ $playerId }}');

        // Function to generate a cache-busting query parameter
        /*function getCacheBuster() {
            return Math.random().toString(36).substring(7);
        }
        */
        // Function to set the audio source with cache-busting
        function setAudioSource(audio_type, audio_data) {

            let audioType = "audio/wav"; // default
            switch (audio_type) {
                case "audio/ogg":
                    audioType = audio_type;
                    break;
                default:
                    /* Do Nothing - use default (wav) */
                    break;
            }

            console.log(`reloading new audio data.., audio type: ${audioType}`);

            audioSrcTag = `data:${audioType};base64,${audio_data}`;

            audioElement.src = audioSrcTag;
            audioElement.load();
        }

        // Livewire hook for reinitializing Able Player
        document.addEventListener('livewire:load', function () {
            console.log("live review system loaded");
{{--            @dd($audio)--}}
            // Set the initial audio source
            setAudioSource(`{{$audio['audioType']}}`, `{{$audio['audioData']}}`); // $audio from mount

            Livewire.on('ReviewItemChanged', audio => {
                console.log("new review loaded");
                setAudioSource(audio.audioType, audio.audioData);
            });
        });
    </script>
</div>
