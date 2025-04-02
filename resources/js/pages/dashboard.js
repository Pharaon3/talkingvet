import "../components/dateRangePicker"

function ready(fn) {
    if (document.readyState !== 'loading') {
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}


function accountdropdownFilter()
{
    /* Accounts Dropdown Filter */
    // Get the necessary elements
    const accountsDropdownSearchButton = document.getElementById('accountsDropdownSearchButton');
    const accountsDropdownButtonText = document.getElementById('accountsDropdownBtnText');
    const accountsDropdownSearch = document.getElementById('accountsDropdownSearch');
    const searchInput = document.getElementById('input-group-search');

    // Add event listener to the dropdown search button
    accountsDropdownSearchButton.addEventListener('click', () => {
        accountsDropdownSearch.classList.toggle('hidden');
    });

    // Add event listener to the search input
    searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.toLowerCase();
        const items = accountsDropdownSearch.querySelectorAll('li');

        items.forEach((item) => {
            const label = item.querySelector('label');
            const text = label.innerText.toLowerCase();

            if (text.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // const selectTrigger = document.querySelector('.select-trigger');
    const options = document.querySelectorAll('#accountsDropdownOptions li');

    options.forEach(function(option) {
        option.addEventListener('click', function() {
            accountsDropdownButtonText.textContent = this.textContent.trim();

            console.log("changed to " + this.textContent.trim());
            Livewire.emit('accountUpdated', this.textContent.trim());

            /*options.forEach(function(option) {
                option.classList.remove('selected');
            });
            this.classList.add('selected');*/

            // close
            if (!accountsDropdownSearch.classList.contains('hidden')) {
                accountsDropdownSearch.classList.add('hidden');
            }
        });
    });

}

ready(accountdropdownFilter);

