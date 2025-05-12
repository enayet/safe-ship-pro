/**
 * Claims public scripts for Safe Ship Pro.
 *
 * @since      1.0.0
 */

(function( $ ) {
    'use strict';

    $(document).ready(function() {
        // Claim submission form
        $('#safe-ship-pro-claim-form').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var result = $('#claim-submission-result');
            var spinner = form.find('.spinner');
            var formData = new FormData(this);
            
            // Validate form
            if (!form[0].checkValidity()) {
                form[0].reportValidity();
                return false;
            }
            
            // Confirm submission
            if (!confirm(safe_ship_pro_claims.i18n.confirm_submission)) {
                return false;
            }
            
            // Clear previous messages
            result.html('').removeClass('woocommerce-error woocommerce-message');
            
            // Show spinner
            spinner.addClass('active');
            
            // Disable submit button
            form.find('button[type="submit"]').prop('disabled', true);
            
            // Submit via AJAX
            $.ajax({
                url: safe_ship_pro_claims.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        result.addClass('woocommerce-message').html(response.data.message);
                        
                        // Redirect to claims list after delay
                        setTimeout(function() {
                            window.location.href = wc_add_to_cart_params.wc_ajax_url.split('?')[0] + 'shipping-claims';
                        }, 2000);
                    } else {
                        result.addClass('woocommerce-error').html(response.data.message);
                        form.find('button[type="submit"]').prop('disabled', false);
                    }
                },
                error: function() {
                    result.addClass('woocommerce-error').html(safe_ship_pro_claims.i18n.error);
                    form.find('button[type="submit"]').prop('disabled', false);
                },
                complete: function() {
                    spinner.removeClass('active');
                }
            });
        });
        
        // View claim details modal
        $('.view-claim').on('click', function(e) {
            e.preventDefault();
            
            var claimId = $(this).data('claim-id');
            var modal = $('#safe-ship-pro-claim-modal');
            var contentContainer = $('#safe-ship-pro-claim-details-content');
            
            // Find claim details in the page
            var row = $(this).closest('tr');
            var claimDate = row.find('.claim-date').text().trim();
            var claimOrder = row.find('.claim-order').html();
            var claimType = row.find('.claim-type').text().trim();
            var claimStatus = row.find('.claim-status').html();
            
            // Generate content
            var content = '<h3>' + 'Claim Details #' + claimId + '</h3>';
            
            content += '<div class="safe-ship-pro-claim-detail-row">';
            content += '<h4>Date:</h4>';
            content += '<p>' + claimDate + '</p>';
            content += '</div>';
            
            content += '<div class="safe-ship-pro-claim-detail-row">';
            content += '<h4>Order:</h4>';
            content += '<p>' + claimOrder + '</p>';
            content += '</div>';
            
            content += '<div class="safe-ship-pro-claim-detail-row">';
            content += '<h4>Type:</h4>';
            content += '<p>' + claimType + '</p>';
            content += '</div>';
            
            content += '<div class="safe-ship-pro-claim-detail-row">';
            content += '<h4>Status:</h4>';
            content += '<p>' + claimStatus + '</p>';
            content += '</div>';
            
            content += '<div class="safe-ship-pro-claim-detail-row">';
            content += '<p>If you have any questions about your claim, please contact us.</p>';
            content += '</div>';
            
            // Set content and show modal
            contentContainer.html(content);
            modal.css('display', 'block');
        });
        
        // Close modal
        $('.safe-ship-pro-modal-close').on('click', function() {
            $('#safe-ship-pro-claim-modal').css('display', 'none');
        });
        
        // Close modal when clicking outside
        $(window).on('click', function(e) {
            if ($(e.target).is('.safe-ship-pro-modal')) {
                $('.safe-ship-pro-modal').css('display', 'none');
            }
        });
    });

})( jQuery );