/**
 * Admin scripts for Safe Ship Pro.
 *
 * @since      1.0.0
 */

jQuery(document).ready(function($) {
    'use strict';

    // Toggle protection fee min/max fields based on protection type
    function toggleFeeFields() {
        var protectionType = $('select[name="safe_ship_pro_protection_type"]').val();
        
        if (protectionType === 'percentage') {
            $('.percentage-option').show();
        } else {
            $('.percentage-option').hide();
        }
    }
    
    $('select[name="safe_ship_pro_protection_type"]').on('change', function() {
        toggleFeeFields();
    });
    
    // Call on page load
    toggleFeeFields();
    
    // Handle claim type management in settings
    $('.add-claim-type').on('click', function() {
        var template = $('#claim-type-template').html();
        var typeCounter = $('.claim-type-row').length;
        var newRow = template.replace(/{key}/g, 'new_' + typeCounter);
        $('#claim-types-container').append(newRow);
    });
    
    $(document).on('click', '.remove-claim-type', function() {
        $(this).closest('.claim-type-row').remove();
    });
    
    // Meta box order protection info
    $('#safe_ship_pro_order_meta_box .safe-ship-pro-claims-list a').on('click', function(e) {
        e.preventDefault();
        window.open($(this).attr('href'), '_blank');
    });
    
    // Logo upload handler - Fixed version
    $('.safe-ship-pro-upload-logo').on('click', function(e) {
        e.preventDefault();
        
        console.log('Upload logo button clicked'); // Debug logging
        
        var button = $(this);
        var logoUrlField = $('#safe_ship_pro_logo_url');
        var logoPreview = $('#safe-ship-pro-logo-preview');
        
        // Check if wp.media is available
        if (typeof wp !== 'undefined' && wp.media) {
            // Create a media frame
            var mediaFrame = wp.media({
                title: 'Select or Upload Logo',
                button: {
                    text: 'Use this logo'
                },
                multiple: false,
                library: {
                    type: 'image' // Only allow images
                }
            });
            
            // When an image is selected, run a callback
            mediaFrame.on('select', function() {
                var attachment = mediaFrame.state().get('selection').first().toJSON();
                console.log('Selected attachment:', attachment); // Debug
                
                logoUrlField.val(attachment.url);
                
                // Update preview
                logoPreview.html('<img src="' + attachment.url + '" style="max-width: 100%;" />');
            });
            
            // Open the media library frame
            mediaFrame.open();
        } else {
            console.error('WordPress Media Uploader API is not available');
            alert('The media uploader is not available. Please check if the page has been properly initialized.');
        }
    });
});