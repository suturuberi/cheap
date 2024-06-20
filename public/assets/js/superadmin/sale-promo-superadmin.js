$(document).ready(function() {
    $('#salei_item_name').change(function() {
        var itemv_id = $(this).val();
        console.log('Item Selected: ' + itemv_id); 

        if (itemv_id) {
            $.post('../public/assets/js/superadmin/get_item_price.php', { itemv_id: itemv_id }, function(response) {
                var priceField = $('#salei_og_price');
                console.log('Server response:', response); 

                if (response.price && response.price.itemv_price) {
                    priceField.val(response.price.itemv_price); 
                } else {
                    console.error('Invalid response structure:', response); 
                }
            }).fail(function(xhr, status, error) {
                console.error('AJAX error:', status, error);
            });
        }
    });
});