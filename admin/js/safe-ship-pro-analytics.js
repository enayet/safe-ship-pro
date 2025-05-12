/**
 * Analytics admin scripts for Safe Ship Pro.
 *
 * @since      1.0.0
 */

(function( $ ) {
    'use strict';

    $(document).ready(function() {
        // Date filter form
        $('.safe-ship-pro-date-filter form').on('submit', function(e) {
            var dateFrom = $('#date_from').val();
            var dateTo = $('#date_to').val();
            
            if (dateFrom && dateTo) {
                var fromDate = new Date(dateFrom);
                var toDate = new Date(dateTo);
                
                if (fromDate > toDate) {
                    e.preventDefault();
                    alert('From date cannot be after To date');
                    return false;
                }
            }
        });
        
        // Export data functionality
        $('#export-analytics-data').on('click', function(e) {
            e.preventDefault();
            
            var dateFrom = $('#date_from').val();
            var dateTo = $('#date_to').val();
            
            // Redirect to export endpoint
            var exportUrl = $(this).attr('href') + '&date_from=' + dateFrom + '&date_to=' + dateTo;
            window.location.href = exportUrl;
        });
    });

})( jQuery );