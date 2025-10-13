$(document).ready(function() {
    // Get photo path & display
    $('#photo_path').on('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview')
                    .attr('src', e.target.result)
                    .css('display', 'block');
            };
            reader.readAsDataURL(file);
        }
    });

    // Edit Page can see existing picture
    const existingPhoto = $('#preview').attr('src');
    if (existingPhoto && existingPhoto !== '#' && existingPhoto.trim() !== '') {
        $('#preview').css('display', 'block');
    }
});