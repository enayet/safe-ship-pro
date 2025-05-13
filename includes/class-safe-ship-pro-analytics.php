<?php
/**
 * The analytics functionality of the plugin.
 *
 * @since      1.0.0
 */
class Safe_Ship_Pro_Analytics {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }
    
    /**
     * Add analytics admin menu.
     *
     * @since    1.0.0
     */
    public function add_analytics_menu() {
        
    }
    
    /**
     * Display analytics admin page.
     *
     * @since    1.0.0
     */
    public function display_analytics_page() {
        // Date filters
        $date_from = isset( $_GET['date_from'] ) ? sanitize_text_field( $_GET['date_from'] ) : date( 'Y-m-d', strtotime( '-30 days' ) );
        $date_to = isset( $_GET['date_to'] ) ? sanitize_text_field( $_GET['date_to'] ) : date( 'Y-m-d' );
        
        // Get analytics data
        $protection_stats = $this->get_protection_stats( $date_from, $date_to );
        $claims_stats = $this->get_claims_stats( $date_from, $date_to );
        $daily_data = $this->get_daily_data( $date_from, $date_to );
        
        // Display template
        require_once SAFE_SHIP_PRO_PLUGIN_DIR . 'admin/partials/safe-ship-pro-analytics-display.php';
    }
    
    /**
     * Get protection statistics for the given date range.
     *
     * @since    1.0.0
     * @param    string    $date_from    Start date in Y-m-d format.
     * @param    string    $date_to      End date in Y-m-d format.
     * @return   array     Protection statistics.
     */
    public function get_protection_stats( $date_from, $date_to ) {
        // Use WC_Order_Query for HPOS compatibility
        $date_range = $date_from . '...' . $date_to;
        
        // Query all orders in the date range
        $all_orders_args = array(
            'status' => array( 'completed', 'processing', 'on-hold' ),
            'date_created' => $date_range,
            'limit' => -1,
            'return' => 'ids'
        );
        
        $all_order_ids = wc_get_orders( $all_orders_args );
        $total_orders = count( $all_order_ids );
        
        // Query protected orders in the date range
        $protected_orders_args = array(
            'date_created' => $date_range,
            'meta_query' => array(
                array(
                    'key' => '_safe_ship_pro_protection_added',
                    'value' => 'yes',
                    'compare' => '='
                )
            ),
            'limit' => -1
        );
        
        $protected_orders = wc_get_orders( $protected_orders_args );
        $protected_orders_count = count( $protected_orders );
        
        // Calculate total and average protection amount
        $total_protection_amount = 0;
        
        foreach ( $protected_orders as $order ) {
            $protection_amount = $order->get_meta( '_safe_ship_pro_protection_amount', true );
            if ( $protection_amount ) {
                $total_protection_amount += floatval( $protection_amount );
            }
        }
        
        // Calculate protection rate
        $protection_rate = $total_orders > 0 ? ( $protected_orders_count / $total_orders ) * 100 : 0;
        
        return array(
            'total_orders' => $total_orders,
            'protected_orders' => $protected_orders_count,
            'protection_rate' => round( $protection_rate, 2 ),
            'total_protection_amount' => $total_protection_amount,
            'average_protection_amount' => $protected_orders_count > 0 ? round( $total_protection_amount / $protected_orders_count, 2 ) : 0,
        );
    }
    
    /**
     * Get claims statistics for the given date range.
     *
     * @since    1.0.0
     * @param    string    $date_from    Start date in Y-m-d format.
     * @param    string    $date_to      End date in Y-m-d format.
     * @return   array     Claims statistics.
     */
    public function get_claims_stats( $date_from, $date_to ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'safe_ship_pro_claims';
        
        // Adjust date range for query
        $date_from = date( 'Y-m-d 00:00:00', strtotime( $date_from ) );
        $date_to = date( 'Y-m-d 23:59:59', strtotime( $date_to ) );
        
        // Get total claims
        $total_claims_query = $wpdb->prepare(
            "SELECT COUNT(*) as count
            FROM $table_name
            WHERE date_created BETWEEN %s AND %s",
            $date_from,
            $date_to
        );
        $total_claims = $wpdb->get_var( $total_claims_query );
        
        // Get claims by status
        $status_query = $wpdb->prepare(
            "SELECT claim_status, COUNT(*) as count
            FROM $table_name
            WHERE date_created BETWEEN %s AND %s
            GROUP BY claim_status",
            $date_from,
            $date_to
        );
        $claims_by_status = $wpdb->get_results( $status_query );
        
        // Get claims by type
        $type_query = $wpdb->prepare(
            "SELECT claim_type, COUNT(*) as count
            FROM $table_name
            WHERE date_created BETWEEN %s AND %s
            GROUP BY claim_type",
            $date_from,
            $date_to
        );
        $claims_by_type = $wpdb->get_results( $type_query );
        
        // Format status data
        $status_data = array(
            'pending' => 0,
            'processing' => 0,
            'approved' => 0,
            'denied' => 0,
            'completed' => 0,
        );
        
        foreach ( $claims_by_status as $status ) {
            $status_data[$status->claim_status] = (int) $status->count;
        }
        
        // Format type data
        $type_data = array(
            'damaged' => 0,
            'lost' => 0,
            'stolen' => 0,
            'delayed' => 0,
            'other' => 0,
        );
        
        foreach ( $claims_by_type as $type ) {
            $type_data[$type->claim_type] = (int) $type->count;
        }
        
        // Calculate approval rate
        $approved_count = $status_data['approved'] + $status_data['completed'];
        $processed_count = $approved_count + $status_data['denied'];
        $approval_rate = $processed_count > 0 ? ( $approved_count / $processed_count ) * 100 : 0;
        
        return array(
            'total_claims' => $total_claims,
            'status_data' => $status_data,
            'type_data' => $type_data,
            'approval_rate' => round( $approval_rate, 2 ),
        );
    }
    
    /**
     * Get daily data for charts.
     *
     * @since    1.0.0
     * @param    string    $date_from    Start date in Y-m-d format.
     * @param    string    $date_to      End date in Y-m-d format.
     * @return   array     Daily data for charts.
     */
    public function get_daily_data( $date_from, $date_to ) {
        global $wpdb;
        $claims_table = $wpdb->prefix . 'safe_ship_pro_claims';
        
        // Create date range array
        $start = new DateTime( $date_from );
        $end = new DateTime( $date_to );
        $interval = new DateInterval( 'P1D' );
        $date_range = new DatePeriod( $start, $interval, $end->modify( '+1 day' ) );
        
        $daily_data = array();
        
        foreach ( $date_range as $date ) {
            $date_str = $date->format( 'Y-m-d' );
            $day_start = $date_str . ' 00:00:00';
            $day_end = $date_str . ' 23:59:59';
            
            // Get protection data for the day using WC_Order_Query for HPOS compatibility
            $day_date_range = $date_str . '...' . $date_str;
            
            $protected_orders_args = array(
                'date_created' => $day_date_range,
                'meta_query' => array(
                    array(
                        'key' => '_safe_ship_pro_protection_added',
                        'value' => 'yes',
                        'compare' => '='
                    )
                ),
                'limit' => -1
            );
            
            $protected_orders = wc_get_orders( $protected_orders_args );
            $protection_orders_count = count( $protected_orders );
            $protection_amount = 0;
            
            foreach ( $protected_orders as $order ) {
                $amount = $order->get_meta( '_safe_ship_pro_protection_amount', true );
                if ( $amount ) {
                    $protection_amount += floatval( $amount );
                }
            }
            
            // Get claims data for the day
            $claims_query = $wpdb->prepare(
                "SELECT COUNT(*) as claim_count
                FROM $claims_table
                WHERE date_created BETWEEN %s AND %s",
                $day_start,
                $day_end
            );
            $claims_count = $wpdb->get_var( $claims_query );
            
            $daily_data[] = array(
                'date' => $date_str,
                'formatted_date' => $date->format( 'M j' ),
                'protection_orders' => $protection_orders_count,
                'protection_amount' => round( $protection_amount, 2 ),
                'claims' => (int) $claims_count,
            );
        }
        
        return $daily_data;
    }
    
    /**
     * Get analytics summary for dashboard widget.
     *
     * @since    1.0.0
     * @return   array    Summary statistics.
     */
    public function get_analytics_summary() {
        // Default to last 30 days
        $date_from = date( 'Y-m-d', strtotime( '-30 days' ) );
        $date_to = date( 'Y-m-d' );
        
        // Get data
        $protection_stats = $this->get_protection_stats( $date_from, $date_to );
        $claims_stats = $this->get_claims_stats( $date_from, $date_to );
        
        return array(
            'protection_stats' => $protection_stats,
            'claims_stats' => $claims_stats,
            'date_range' => array(
                'from' => $date_from,
                'to' => $date_to,
            ),
        );
    }
}