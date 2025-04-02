import flatpickr from "flatpickr";
import moment from "moment";
import confirmDatePlugin from "flatpickr/dist/plugins/confirmDate/confirmDate";

document.addEventListener('livewire:load', function() {

    // Function to initialize Flatpickr
    function initializeFlatpickr() {
        const datePickerEl = document.getElementById('encounter_date');

        if (datePickerEl) {
            const fp = flatpickr(datePickerEl, {
                plugins: [
                    confirmDatePlugin({
                        confirmIcon: '',
                        confirmText: 'Ok',
                        showAlways: false,
                        theme: 'dark',
                    }),
                ],
                enableTime: true,
                dateFormat: 'M d, Y H:m',
                maxDate: moment().add(1, 'days').toISOString(),
                defaultDate: moment().toISOString(),
                allowInput: true,
                allowInvalidPreload: false,
                onReady: function(selectedDates, dateStr, instance) {
                    if (selectedDates[0]) {
                        window.livewire.emit('dateSelected', {
                            date: moment(selectedDates[0]).format('MM-DD-YYYY HH:mm:ss ZZ')
                        });
                    }
                },
                onChange: function(selectedDates, dateStr, instance) {
                    window.livewire.emit('dateSelected', {
                        date: moment(selectedDates[0]).format('MM-DD-YYYY HH:mm:ss ZZ')
                    });
                }
            });
        } else {
            console.log('Element with ID "encounter_date" not found in the DOM.');
        }
    }

    // Listen for the modal-loaded event
    window.addEventListener('modal-loaded', function () {
        console.log('Modal loaded event received');
        initializeFlatpickr();
    });
});
