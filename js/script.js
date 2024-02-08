jQuery(document).ready(function($) {
    function updateAddonPrices() {
        var addon1Price = parseFloat($('input[name="addon[]"][value="addon1"]').data('price'));
        var addon2Price = parseFloat($('input[name="addon[]"][value="addon2"]').data('price'));
        var addon3Price = parseFloat($('input[name="addon[]"][value="addon3"]').data('price'));

        $('input[name="addon[]"][value="addon1"]').data('price', addon1Price);
        $('input[name="addon[]"][value="addon2"]').data('price', addon2Price);
        $('input[name="addon[]"][value="addon3"]').data('price', addon3Price);
    }
    updateAddonPrices();

    $(document).on('click', '.submit', function() {
        setTimeout(function() {
            updateAddonPrices();
        }, 500); 
    });

    // Update total price when addon is selected
    $('input[name="addon[]"]').change(function() {
        var totalPrice = parseFloat($('.woocommerce-Price-amount').data('default-price'));

        // Iterate over checked addons and update the total price
        $('input[name="addon[]"]:checked').each(function() {
            var addonPrice = parseFloat($(this).data('price'));
            if (!isNaN(addonPrice)) {
                totalPrice += addonPrice; 
            }
        });

        // Update the total price on the page
        $('.woocommerce-Price-amount').text('$' + totalPrice.toFixed(2));
        console.log("Total Price: " + totalPrice);
    });

    // Initialize default price
    var defaultPrice = parseFloat($('.woocommerce-Price-amount').text().replace(/[^\d.]/g, ''));
    $('.woocommerce-Price-amount').data('default-price', defaultPrice);
});
