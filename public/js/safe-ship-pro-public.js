/**
 * Public scripts for Safe Ship Pro.
 *
 * @since      1.0.0
 */

(function( $ ) {
    'use strict';

    $(document).ready(function() {
        // Update checkout when protection is toggled
        $('#safe_ship_pro_protection').on('change', function() {
            $('body').trigger('update_checkout');
        });
        
        // Protection info toggle on product page
        $('.safe-ship-pro-product-info-toggle').on('click', function(e) {
            e.preventDefault();
            $('.safe-ship-pro-product-info-content').slideToggle();
            $(this).toggleClass('active');
        });
    });

})( jQuery );