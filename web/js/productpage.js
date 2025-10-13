$(document).ready(function () {
    // Set default size selection
    $('.size-btn-container .size-btn[data-size="S"]').click();

    // Size button selection
    $('.size-btn-container .size-btn').click(function () {
        $('.size-btn-container .size-btn').removeClass('selected');
        $(this).addClass('selected');
        $('#selected-size').val($(this).data('size'));
    });


    $('.quantity-input .minus').click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        const quantityInput = $(this).siblings('.quantity');
        let value = parseInt(quantityInput.val()) || 1;
        if (value > 1) {
            quantityInput.val(value - 1);
        }
        quantityInput.trigger('input');
    });

    $('.quantity-input .plus').click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        const quantityInput = $(this).siblings('.quantity');
        let value = parseInt(quantityInput.val()) || 1;
        if (value < 10) {
            quantityInput.val(value + 1);
        }
        quantityInput.trigger('input');
    });

    $('.quantity-input .quantity').on('input change', function () {
        let value = parseInt($(this).val()) || 1;
        if (value < 1) value = 1;
        if (value > 10) value = 10;
        $(this).val(value);
    });

    // Validate form before submission (add quantity check)
    $('form').on('submit', function (e) {
        if ($('#selected-size').val() === '') {
            e.preventDefault();
            alert('Please select a size.');
            return;
        }
        
        const quantity = parseInt($('.quantity').val());
        if (quantity < 1 || quantity > 10) {
            e.preventDefault();
            alert('Quantity must be between 1 and 10.');
        }
    });


    // Dropdown toggle for details
    $('.dropdown-toggle').click(function () {
        $(this).closest('.dropdown').toggleClass('active');
    });

    // Validate form before submission
    $('form').on('submit', function (e) {
        if ($('#selected-size').val() === '') {
            e.preventDefault();
            alert('Please select a size.');
        }
    });

    // Open the modal when the link is clicked
    $('#size-guide-link').click(function (event) {
        event.preventDefault(); // Prevent the link from navigating
        $('#size-guide-modal').fadeIn(); // Show the modal
    });

    // Close the modal when the close button is clicked
    $('.close').click(function () {
        $('#size-guide-modal').fadeOut(); // Hide the modal
    });

    // Close the modal when clicking outside the modal content
    $(window).click(function (event) {
        if (event.target === $('#size-guide-modal')[0]) {
            $('#size-guide-modal').fadeOut(); // Hide the modal
        }
    });
});