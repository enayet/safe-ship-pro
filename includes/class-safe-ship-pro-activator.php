<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 */
class Safe_Ship_Pro_Activator {

    /**
     * Set up the plugin upon activation.
     *
     * Create necessary database tables and set default options.
     *
     * @since    1.0.0
     */
    public static function activate() {
        global $wpdb;
        
        // Create the claims table
        $table_name = $wpdb->prefix . 'safe_ship_pro_claims';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            date_created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            date_updated datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            user_id bigint(20) NOT NULL,
            order_id bigint(20) NOT NULL,
            claim_type varchar(50) NOT NULL,
            claim_reason text NOT NULL,
            claim_status varchar(20) NOT NULL DEFAULT 'pending',
            admin_notes text,
            attachments text,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        
        // Add default settings
        $default_settings = array(
            'safe_ship_pro_protection_enabled' => 'yes',
            'safe_ship_pro_protection_type' => 'percentage',
            'safe_ship_pro_protection_amount' => '1.5',
            'safe_ship_pro_protection_min_fee' => '0.99',
            'safe_ship_pro_protection_max_fee' => '9.99',
            'safe_ship_pro_protection_label' => __( 'Shipping Protection', 'safe-ship-pro' ),
            'safe_ship_pro_protection_description' => __( 'Protect your package against loss, damage, or theft during shipping.', 'safe-ship-pro' ),
            'safe_ship_pro_protection_policy' => __( 'Our shipping protection covers loss, damage, or theft during transit. Claims must be filed within 14 days of the expected delivery date.', 'safe-ship-pro' ),
            'safe_ship_pro_claims_enabled' => 'yes',
            'safe_ship_pro_claims_email_notifications' => 'yes',
        );
        
        foreach ( $default_settings as $key => $value ) {
            if ( ! get_option( $key ) ) {
                update_option( $key, $value );
            }
        }
        
        // Register and flush endpoints
        if (function_exists('WC')) {
            add_rewrite_endpoint('shipping-claims', EP_ROOT | EP_PAGES);
            
            // This is crucial - we need to flush rewrite rules
            flush_rewrite_rules();
            
            // Ensure the endpoint is registered in the database for WooCommerce
            $wc_endpoints = get_option('woocommerce_endpoints', array());
            if (!isset($wc_endpoints['shipping-claims'])) {
                $wc_endpoints['shipping-claims'] = 'shipping-claims';
                update_option('woocommerce_endpoints', $wc_endpoints);
            }
        }
    }
}