document.addEventListener('livewire:load', function () {
    console.log('livewire loaded');

    // Check if Alpine is initialized (optional, but good practice)
    if (typeof Alpine !== 'undefined') {
        console.log('Alpine is initialized');
    } else {
        console.warn('Alpine is NOT initialized');
    }

    checkMicAvailable().then(isMicAvailable => {
        console.log(`Mic available ${isMicAvailable}`);
        if (isMicAvailable) {
            let data = {
                type: 'microphone_enabled',
                status: true
            };
            Livewire.emit('microphone_test', data);
        } else {
            let data = {
                type: 'microphone_disabled',
                status: false
            };
            Livewire.emit('microphone_test', data);
        }
    });

    window.addEventListener('enable-mic-requested', async function () {
        console.log('enable mic requested');
        navigator.mediaDevices.getUserMedia({ audio: true })
            .then(function (stream) {
                console.log('Microphone access granted!');
                const audio = document.getElementById('myAudio');
                audio.srcObject = stream;
            })
            .catch(function (err) {
                console.error('Error accessing microphone:', err);
                alert('Unable to access microphone. Please check your browser settings.');
            });
    });

    window.addEventListener('test-mic-requested', function () {
        navigator.mediaDevices.getUserMedia({ audio: true, video: false })
            .then(function (stream) {
                // Microphone access granted!
                console.log('Microphone access granted!');
                testMicrophone(stream); // Call the function to test the microphone
            })
            .catch(function (err) {
                // Microphone access denied or error occurred
                console.error('Error accessing microphone:', err);
                alert('Unable to access microphone. Please check your browser settings.');
            });
    });

    async function checkMicAvailable() {
        try {
            await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
            return true
        } catch (error) {
            console.error(error);
            return false;
        }
    }

    function testMicrophone(stream) {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const analyser = audioContext.createAnalyser();
        const microphone = audioContext.createMediaStreamSource(stream);
        microphone.connect(analyser);
        analyser.fftSize = 2048;
        const bufferLength = analyser.frequencyBinCount;
        const dataArray = new Float32Array(bufferLength);

        function checkLevel() {
            analyser.getFloatFrequencyData(dataArray);
            let sum = 0;
            for (let i = 0; i < bufferLength; i++) {
                sum += dataArray[i];
            }
            const average = sum / bufferLength;

            // Adjust the threshold based on your environment
            const threshold = -50; // Example threshold value

            if (average > threshold) {
                console.log('Microphone is working - Sound detected');

            } else {
                console.log('Microphone is not detecting sound');
            }
        }

        // Check the level periodically (e.g., every 100ms)
        setInterval(checkLevel, 100);
    }

});
