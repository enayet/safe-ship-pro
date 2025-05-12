/**
 * Claims management admin scripts for Safe Ship Pro.
 *
 * @since      1.0.0
 */

(function( $ ) {
    'use strict';

    $(document).ready(function() {
        // Claims update form
        $('#claim-update-form').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var message = $('#update-message');
            var spinner = form.find('.spinner');
            var statusSelect = form.find('#claim_status');
            var originalStatus = statusSelect.val();
            
            // Confirm status change if needed
            if (originalStatus !== statusSelect.val()) {
                if (!confirm(safe_ship_pro_claims.i18n.confirm_status_change)) {
                    return false;
                }
            }
            
            // Clear previous messages
            message.html('').removeClass('notice-error notice-success');
            
            // Show spinner
            spinner.css('visibility', 'visible');
            
            // Disable submit button
            form.find('button[type="submit"]').prop('disabled', true);
            
            // Submit via AJAX
            $.ajax({
                url: safe_ship_pro_claims.ajax_url,
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        message.addClass('notice notice-success inline').html('<p>' + response.data.message + '</p>');
                        
                        // Update status display
                        var newStatus = statusSelect.val();
                        var statusLabel = statusSelect.find('option:selected').text();
                        
                        // Update status class
                        $('.safe-ship-pro-claim-header .claim-status')
                            .removeClass('status-pending status-processing status-approved status-denied status-completed')
                            .addClass('status-' + newStatus)
                            .text(statusLabel);
                    } else {
                        message.addClass('notice notice-error inline').html('<p>' + response.data.message + '</p>');
                    }
                },
                error: function() {
                    message.addClass('notice notice-error inline').html('<p>' + safe_ship_pro_claims.i18n.error + '</p>');
                },
                complete: function() {
                    spinner.css('visibility', 'hidden');
                    form.find('button[type="submit"]').prop('disabled', false);
                }
            });
        });
        
        // Claims filter form
        $('#claims-filter-form select').on('change', function() {
            $(this).closest('form').submit();
        });
    });

})( jQuery );