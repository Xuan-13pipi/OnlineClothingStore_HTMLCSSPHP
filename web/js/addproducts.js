// ============================================================================
// General Functions
// ============================================================================

$(document).ready(function() {
    // Front Image Preview
    $('#front-image').on('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#front-preview')
                    .attr('src', e.target.result)
                    .css('display', 'block');
            };
            reader.readAsDataURL(file);
        }
    });

    // Back Image Preview
    $('#back-image').on('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#back-preview')
                    .attr('src', e.target.result)
                    .css('display', 'block');
            };
            reader.readAsDataURL(file);
        }
    });
});