document.addEventListener('DOMContentLoaded', function() {
    // Address selection toggle
    const currentAddressRadio = document.getElementById('current_address');
    const newAddressRadio = document.getElementById('new_address');
    const newAddressField = document.getElementById('new-address-field');
    
    function toggleAddressField() {
        newAddressField.style.display = newAddressRadio.checked ? 'block' : 'none';
    }
    
    currentAddressRadio.addEventListener('change', toggleAddressField);
    newAddressRadio.addEventListener('change', toggleAddressField);
    
    // Payment method toggles
    const creditCardRadio = document.getElementById('credit-card');
    const tngRadio = document.getElementById('tng');
    const bankTransferRadio = document.getElementById('bank-transfer');
    const creditCardDetails = document.getElementById('credit-card-details');
    const tngDetails = document.getElementById('tng-details');
    const bankDetails = document.getElementById('bank-details');
    
    function togglePaymentDetails() {
        // Hide all payment details first
        creditCardDetails.style.display = 'none';
        tngDetails.style.display = 'none';
        bankDetails.style.display = 'none';
        
        // Show only the selected payment method's details
        if (creditCardRadio.checked) {
            creditCardDetails.style.display = 'block';
            // Make credit card fields required
            document.getElementById('card_number').required = true;
            document.getElementById('expiry_date').required = true;
            document.getElementById('cvv').required = true;
            document.getElementById('card_name').required = true;
            // Make other payment fields not required
            document.getElementById('bank_name').required = false;
            document.getElementById('bank_number').required = false;
            document.getElementById('account_name').required = false;
        } else if (tngRadio.checked) {
            tngDetails.style.display = 'block';
            // No fields required for TNG
            document.getElementById('card_number').required = false;
            document.getElementById('expiry_date').required = false;
            document.getElementById('cvv').required = false;
            document.getElementById('card_name').required = false;
            document.getElementById('bank_name').required = false;
            document.getElementById('bank_number').required = false;
            document.getElementById('account_name').required = false;
        } else if (bankTransferRadio.checked) {
            bankDetails.style.display = 'block';
            // Make bank transfer fields required
            document.getElementById('bank_name').required = true;
            document.getElementById('bank_number').required = true;
            document.getElementById('account_name').required = true;
            // Make credit card fields not required
            document.getElementById('card_number').required = false;
            document.getElementById('expiry_date').required = false;
            document.getElementById('cvv').required = false;
            document.getElementById('card_name').required = false;
        }
    }
    
    // Add event listeners to all payment method radios
    creditCardRadio.addEventListener('change', togglePaymentDetails);
    tngRadio.addEventListener('change', togglePaymentDetails);
    bankTransferRadio.addEventListener('change', togglePaymentDetails);
    
    // Initialize both toggles
    toggleAddressField();
    togglePaymentDetails();

    // Format card number input
    const cardNumber = document.getElementById('card_number');
    if (cardNumber) {
        cardNumber.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            e.target.value = value;
        });
    }

    // Format expiry date input
    const expiryDate = document.getElementById('expiry_date');
    if (expiryDate) {
        expiryDate.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
    }
});