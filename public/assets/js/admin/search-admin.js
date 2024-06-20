document.getElementById('search-input').addEventListener('keyup', function() {
    let filter = this.value.toUpperCase();
    let tableRows = document.querySelectorAll('.table tbody tr'); // Select all table rows in the tbody
    let found = false; // Flag to track if any matching rows are found

    tableRows.forEach(function(row) {
        let cells = row.getElementsByTagName('td');
        let rowContainsFilterText = false;

        for (let j = 0; j < cells.length; j++) {
            let cellText = cells[j].textContent || cells[j].innerText;
            if (cellText.toUpperCase().indexOf(filter) > -1) {
                rowContainsFilterText = true;
                found = true; // Set found to true if any match is found
                break;
            }
        }

        row.style.display = rowContainsFilterText ? "" : "none";
    });

    // Show or hide the message row based on whether any matches were found
    let noMatchRow = document.getElementById('no-match-row');
    if (!found) {
        noMatchRow.style.display = "";
    } else {
        noMatchRow.style.display = "none";
    }
});
