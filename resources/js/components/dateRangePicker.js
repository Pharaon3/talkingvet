import flatpickr from "flatpickr";
import rangePlugin from "flatpickr/dist/plugins/rangePlugin";
import moment from "moment";
import confirmDatePlugin from "flatpickr/dist/plugins/confirmDate/confirmDate";

const dateRangePickerEl = document.getElementById('dateRange');
const fp = dateRangePickerEl.flatpickr({
    plugins: [new confirmDatePlugin({
        confirmIcon: ``, // your icon's html, if you wish to override
        confirmText: "Ok",
        showAlways: false,
        theme: "dark" // or "dark"
    })],
    // plugins: [new rangePlugin({ input: "#dateEnd"})],
    mode: "range",
    // minDate: new Date().fp_incr(-12 * 30),
    maxDate: moment().add(1, 'days').toISOString(),
    enableTime: true,
    // altInput: true,
    // altFormat: 'd M Y, h:i:S K (l)',
    dateFormat: "d M Y, h:i:S K (l)",
    defaultDate: [
        // moment().subtract(3, 'months').toISOString(),
        // moment().toISOString()

        moment().startOf('day').subtract(1,'week').toISOString(),
        moment().add(1, 'days').startOf('day').toISOString()
    ],
    allowInput: true, // Allows the user to enter a date directly into the input field. By default, direct entry is disabled.
    allowInvalidPreload: false, // Allows the preloading of an invalid date. When disabled, the field will be cleared if the provided date is invalid
    // dateFormat: "m-d-Y H:i:S",
    onReady: function (selectedDates, dateStr, instance) {
        window.livewire.emit('dateSelected',
            {
                start: moment(selectedDates[0]).format('MM-DD-YYYY HH:mm:ss ZZ'),
                end: moment(selectedDates[1]).format('MM-DD-YYYY HH:mm:ss ZZ')
                // end: moment(selectedDates[1]).format('MM-DD-YYYY HH:mm:ss')
            });
    },
    // onValueUpdate: function (selectedDates, dateStr, instance) {
    onClose: function (selectedDates, dateStr, instance) {
        // console.log(dateStr);
        // console.log(selectedDates);
        window.livewire.emit('dateSelected',
            {
                start: moment(selectedDates[0]).format('MM-DD-YYYY HH:mm:ss ZZ'),
                end: moment(selectedDates[1]).format('MM-DD-YYYY HH:mm:ss ZZ')
                // end: moment(selectedDates[1]).format('MM-DD-YYYY HH:mm:ss')
            });
        // document.querySelector('#updateDateRangeBtn').click();
    }
    // additional options go here
});
/*
document.addEventListener("livewire:load", function() {
    console.log("date: " + fp.selectedDates);
    window.livewire.emit('dateSelected',
        {
            start: moment(fp.selectedDates[0]).format('MM-DD-YYYY HH:mm:ss ZZ'),
            end: moment(fp.selectedDates[1]).format('MM-DD-YYYY HH:mm:ss ZZ')
            // end: moment(selectedDates[1]).format('MM-DD-YYYY HH:mm:ss')
        });
});*/
