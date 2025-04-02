$(document).ready(function (){

    const vocabModal = document.getElementById('vocabModalContainer');
    const correctedTextArea = document.getElementById('correctedTextArea');
    const vocabWordInput = document.getElementById('vocab-word');
    const vocabSoundsLikeInput = document.getElementById('vocab-sounds-like');

    $("#copyOrgBtn").on('click', function () {
        copyToClipboard($("#originalTextArea").text());
    });

    $("#copySubBtn").on('click', function () {
        copyToClipboard($("#substitutedTextArea").text());
    });

    $("#copyCorrected").on('click', function () {
        copyToClipboard(document.querySelector("#correctedTextArea").value);
    });


    function copyToClipboard(text) {
        navigator.clipboard.writeText(text)
            .then(() => {
                console.log('Text copied to clipboard');
                showToast("Copied to clipboard");
            })
            .catch((error) => {
                console.error('Error copying text to clipboard');
            });
    }

    /* Vocab */
    Livewire.on('addToVocabCallback', result => {
        console.log("result:");
        console.log(result);
        showToast(result.msg, result.error);
    });

    $("#addToVocabBtn").on('click', function(){
        // copy selection to modal window
        // Assuming you have a textarea with id "myTextarea"
        let selectedText = '';

        if (correctedTextArea.selectionStart !== undefined) {
            // For modern browsers that support the selectionStart and selectionEnd properties
            selectedText = correctedTextArea.value.substring(correctedTextArea.selectionStart, correctedTextArea.selectionEnd);
        } else if (document.selection && document.selection.createRange) {
            // For older versions of Internet Explorer
            correctedTextArea.focus();
            let range = document.selection.createRange();
            selectedText = range.text;
        }

        vocabWordInput.value = selectedText;
        // console.log('Selected text:', selectedText);
        // show modal
        if (vocabModal.classList.contains('hidden')) {
            vocabModal.classList.remove('hidden');
        }
    });

    $("#vocabModalConfirm").on('click', function(){
        // call controller
        Livewire.emit('addToVocab', vocabWordInput.value, vocabSoundsLikeInput.value);

        // close dialog
        if (!vocabModal.classList.contains('hidden')) {
            vocabModal.classList.add('hidden');
        }
    });

    $("#vocabModalCancel").on('click', function(){
        if (!vocabModal.classList.contains('hidden')) {
            vocabModal.classList.add('hidden');
        }
    });

    /* Helper Functions */

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
                '</svg>'
                :
            '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5 mr-2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>';

        toast.innerHTML += toastText;

        // Append toast to container
        toastContainer.appendChild(toast);

        // Remove toast after 3 seconds
        setTimeout(function() {
            toastContainer.removeChild(toast);
        }, 3000);
    }

});
