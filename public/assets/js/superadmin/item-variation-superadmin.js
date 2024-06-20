$(document).ready(function() {
    console.log('Document ready');

    $('#modal-add').on('shown.bs.modal', function () {
        console.log('Modal shown');

        // Reset the form
        $(this).find('form')[0].reset();

        // Re-enable the size and color select elements
        $('#sizeSelect').prop('disabled', true).empty().append('<option value="" disabled selected>Select Size</option>');
        $('#colorSelect').prop('disabled', true).empty().append('<option value="" disabled selected>Select Color</option>');
    });

    $('#modal-edit').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var modal = $(this);
    
        var id = button.data('id');
        var itemId = button.data('item-id');
        var itemName = button.data('item');
        var sizeId = button.data('size-id');
        var sizeText = button.data('size');
        var colorId = button.data('color-id');
        var colorText = button.data('color');
        var price = button.data('price');
        var stocked = button.data('stocked');
        var qoh = button.data('qoh');
        var statusId = button.data('status-id');
        var statusText = button.data('status');
    
        // Populate the modal fields with the extracted data
        modal.find('#itemv_id').val(id);
        modal.find('#item').empty().append('<option value="' + itemId + '">' + itemName + '</option>').val(itemId);
    
        // Populate size select field
        var sizeSelect = modal.find('select[name="size"]');
        sizeSelect.empty().append('<option value="' + sizeId + '">' + sizeText + '</option>').val(sizeId);
    
        // Populate color select field
        var colorSelect = modal.find('select[name="color"]');
        colorSelect.empty().append('<option value="' + colorId + '">' + colorText + '</option>').val(colorId);
    
        modal.find('#qoh').val(qoh);
        modal.find('#price').val(price);
    
        // Populate status select field
        var statusSelect = modal.find('select[name="status"]');
        statusSelect.empty();
    
        $.each(statuses, function(index, status) {
            var selected = status.items_id == statusId ? 'selected' : '';
            statusSelect.append('<option value="' + status.items_id + '" ' + selected + '>' + status.items_descript + '</option>');
        });
    
        // Conditionally set the price input field to readonly based on the status
        if (statusText === 'RELEASED') {
            modal.find('#price').prop('readonly', true);
        } else {
            modal.find('#price').prop('readonly', false);
        }
    });
       

    $('#modal-del').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var itemv_id = button.closest('tr').find('td:first').text();
        var modal = $(this);
        modal.find('#itemv_id').val(itemv_id);
    });

    $('#itemSelect').change(function() {
        console.log('itemSelect changed');
        var itemId = $(this).val();
        console.log('Selected item ID:', itemId);

        if (itemId) {
            $.post('../public/assets/js/superadmin/fetch_item_data.php', { item_id: itemId }, function(response) {
                console.log('Server response:', response);

                var sizeSelect = $('#sizeSelect');
                var colorSelect = $('#colorSelect');

                console.log('Size select element:', sizeSelect);
                console.log('Color select element:', colorSelect);

                // Clear previous options
                sizeSelect.empty().append('<option value="" disabled selected>Select Size</option>');
                colorSelect.empty().append('<option value="" disabled selected>Select Color</option>');

                if (response.sizes && response.colors) {
                    // Populate size options
                    $.each(response.sizes, function(index, size) {
                        sizeSelect.append('<option value="' + size.item_size_id + '">' + size.item_size_descript + '</option>');
                    });

                    // Populate color options
                    $.each(response.colors, function(index, color) {
                        colorSelect.append('<option value="' + color.item_col_id + '">' + color.item_col_descript + '</option>');
                    });

                    // Enable the size and color select elements
                    sizeSelect.prop('disabled', false);
                    colorSelect.prop('disabled', false);
                } else {
                    console.error('Invalid response structure:', response);
                }
            }).fail(function(xhr, status, error) {
                console.error('AJAX error:', status, error);
            });
        }
    });
});
