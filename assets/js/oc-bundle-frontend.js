jQuery(document).ready(function($) {
    // Function to update the total price based on selected options
    function updateTotalPrice() {
        let totalPrice = 0;
        $('.bundle-product').each(function() {
            const quantity = $(this).find('.bundle-quantity').val();
            const price = $(this).find('.bundle-price').data('price');
            totalPrice += quantity * price;
        });
        $('.bundle-total-price').text(totalPrice.toFixed(2));
    }

    // Attach event listeners to quantity inputs
    $(document).on('change', '.bundle-quantity', updateTotalPrice);

    // Initial total price calculation
    updateTotalPrice();
});