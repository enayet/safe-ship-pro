<?php
/**
 * The claims management functionality of the plugin.
 *
 * @since      1.0.0
 */
class Safe_Ship_Pro_Claims {

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
     * Add the claims endpoint to My Account.
     *
     * @since    1.0.0
     */
    public function add_claims_rewrite_endpoint() {
        add_rewrite_endpoint( 'shipping-claims', EP_ROOT | EP_PAGES );
    }
    
    /**
     * Add shipping claims to My Account menu.
     *
     * @since    1.0.0
     * @param    array    $items    Menu items.
     * @return   array    Modified menu items.
     */
    public function add_claims_endpoint( $items ) {
        // Insert before the logout menu item
        $logout_position = array_search( 'customer-logout', array_keys( $items ) );
        
        if ($logout_position !== false) {
            $new_items = array_slice( $items, 0, $logout_position );
            $new_items['shipping-claims'] = __( 'Shipping Claims', 'safe-ship-pro' );
            $new_items = array_merge( $new_items, array_slice( $items, $logout_position ) );
            
            return $new_items;
        }
        
        // Fallback if logout item not found
        $items['shipping-claims'] = __( 'Shipping Claims', 'safe-ship-pro' );
        return $items;
    }
    
    /**
     * Display the claims page in My Account.
     *
     * @since    1.0.0
     */
    public function display_claims_page() {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            echo '<p>' . __('You must be logged in to view claims.', 'safe-ship-pro') . '</p>';
            return;
        }
        
        // Get claims for this user
        global $wpdb;
        $table_name = $wpdb->prefix . 'safe_ship_pro_claims';
        
        // Check if the table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if (!$table_exists) {
            // Table doesn't exist, let's create it
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
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            
            // After creating the table, let claims be an empty array
            $claims = array();
        } else {
            // Table exists, get claims
            $claims = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE user_id = %d ORDER BY date_created DESC",
                    $user_id
                )
            );
        }
        
        // Get protected orders for this user
        $args = array(
            'customer_id' => $user_id,
            'limit' => -1,
        );
        
        $all_orders = wc_get_orders($args);
        $protected_orders = array();
        
        foreach ($all_orders as $order) {
            $has_protection = $order->get_meta('_safe_ship_pro_protection_added', true) === 'yes';
            if ($has_protection) {
                $protected_orders[] = $order;
            }
        }
        
        // Debug info (remove in production)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            echo '<!-- Debug: User ID: ' . $user_id . ' -->';
            echo '<!-- Debug: Claims Count: ' . count($claims) . ' -->';
            echo '<!-- Debug: Protected Orders Count: ' . count($protected_orders) . ' -->';
        }
        
        // Display template
        require_once SAFE_SHIP_PRO_PLUGIN_DIR . 'public/partials/safe-ship-pro-claims-display.php';
    }
    
    /**
     * AJAX handler for submitting claims.
     *
     * @since    1.0.0
     */
    public function ajax_submit_claim() {
        
    // Debug - write to error log to see if this function is called
    error_log('Safe Ship Pro: ajax_submit_claim function called');
    
    // Check nonce
    check_ajax_referer('safe_ship_pro_submit_claim', 'security');
    
    // More debug - check if we passed nonce verification
    error_log('Safe Ship Pro: nonce verified successfully');
        // Check nonce
        check_ajax_referer( 'safe_ship_pro_submit_claim', 'security' );
        
        // Check user login
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in to submit a claim.', 'safe-ship-pro' ) ) );
            return;
        }
        
        // Get form data
        $order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
        $claim_type = isset( $_POST['claim_type'] ) ? sanitize_text_field( $_POST['claim_type'] ) : '';
        $claim_reason = isset( $_POST['claim_reason'] ) ? sanitize_textarea_field( $_POST['claim_reason'] ) : '';
        
        // Validate data
        if ( empty( $order_id ) || empty( $claim_type ) || empty( $claim_reason ) ) {
            wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', 'safe-ship-pro' ) ) );
            return;
        }
        
        // Verify order belongs to user and has protection - HPOS compatible
        $order = wc_get_order( $order_id );
        if ( ! $order || $order->get_customer_id() != $user_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid order selection.', 'safe-ship-pro' ) ) );
            return;
        }
        
        // Check if protection was added - HPOS compatible
        $has_protection = $order->get_meta( '_safe_ship_pro_protection_added', true ) === 'yes';
        if ( ! $has_protection ) {
            wp_send_json_error( array( 'message' => __( 'This order does not have shipping protection.', 'safe-ship-pro' ) ) );
            return;
        }
        
        $attachments = array();
        
        // Handle file attachments
        if ( ! empty( $_FILES['claim_files'] ) ) {
            if ( ! function_exists('wp_handle_upload') ) {
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
            }
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            
            // Create upload directory if it doesn't exist
            $upload_dir = wp_upload_dir();
            $claims_dir = $upload_dir['basedir'] . '/safe-ship-pro-claims';
            if ( ! file_exists( $claims_dir ) ) {
                wp_mkdir_p( $claims_dir );
            }
            
            // Create index.php file to prevent directory listing
            if ( ! file_exists( $claims_dir . '/index.php' ) ) {
                $f = fopen( $claims_dir . '/index.php', 'w' );
                fwrite( $f, '<?php // Silence is golden' );
                fclose( $f );
            }
            
            // Process multiple files
            $files = $_FILES['claim_files'];
            $file_count = count( $files['name'] );
            
            for ( $i = 0; $i < $file_count; $i++ ) {
                if ( $files['error'][$i] == 0 ) {
                    $file = array(
                        'name'     => sanitize_file_name( $files['name'][$i] ),
                        'type'     => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error'    => $files['error'][$i],
                        'size'     => $files['size'][$i]
                    );
                    
                    $upload_overrides = array( 'test_form' => false );
                    $movefile = wp_handle_upload( $file, $upload_overrides );
                    
                    if ( $movefile && ! isset( $movefile['error'] ) ) {
                        $attachments[] = $movefile['url'];
                    }
                }
            }
        }
        
        // Insert claim into database
        global $wpdb;
        $table_name = $wpdb->prefix . 'safe_ship_pro_claims';
        
        $data = array(
            'date_created' => current_time( 'mysql' ),
            'date_updated' => current_time( 'mysql' ),
            'user_id' => $user_id,
            'order_id' => $order_id,
            'claim_type' => $claim_type,
            'claim_reason' => $claim_reason,
            'claim_status' => 'pending',
            'attachments' => ! empty( $attachments ) ? serialize( $attachments ) : '',
        );
        
        $format = array( 
            '%s', // date_created
            '%s', // date_updated 
            '%d', // user_id
            '%d', // order_id
            '%s', // claim_type
            '%s', // claim_reason
            '%s', // claim_status
            '%s', // attachments
        );
        
        // Insert the data and check for errors
        $result = $wpdb->insert( $table_name, $data, $format );
        
        if ( $result === false ) {
            // Log the error for debugging
            error_log('Safe Ship Pro: Database insertion error: ' . $wpdb->last_error);
            wp_send_json_error( array( 'message' => __( 'Error saving claim. Please try again.', 'safe-ship-pro' ) . ' (' . $wpdb->last_error . ')' ) );
            return;
        }
        
        // Get the inserted ID
        $claim_id = $wpdb->insert_id;
        
        if (!$claim_id) {
            wp_send_json_error( array( 'message' => __( 'Error retrieving claim ID after insertion.', 'safe-ship-pro' ) ) );
            return;
        }
        
        // Trigger notification
        do_action( 'safe_ship_pro_claim_submitted', $claim_id, $order_id );
        
        // Return success
        wp_send_json_success( array( 
            'message' => __( 'Your claim has been submitted successfully.', 'safe-ship-pro' ),
            'claim_id' => $claim_id
        ) );
    }

    
    /**
     * Display claims management admin page.
     *
     * @since    1.0.0
     */
    public function display_claims_admin_page() {
        // Get claims
        global $wpdb;
        $table_name = $wpdb->prefix . 'safe_ship_pro_claims';
        
        // Check if the table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if (!$table_exists) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Claims table does not exist. Please deactivate and reactivate the plugin.', 'safe-ship-pro') . '</p></div>';
            return;
        }
        
        // Handle filters
        $where = '';
        $status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
        if ( ! empty( $status_filter ) ) {
            $where = $wpdb->prepare( "WHERE claim_status = %s", $status_filter );
        }
        
        // Get claims with pagination
        $claims_per_page = 20;
        $current_page = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
        $offset = ( $current_page - 1 ) * $claims_per_page;
        
        $claims = $wpdb->get_results(
            "SELECT * FROM $table_name $where ORDER BY date_created DESC LIMIT $offset, $claims_per_page"
        );
        
        $total_claims = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name $where" );
        $total_pages = ceil( $total_claims / $claims_per_page );
        
        // Display template
        require_once SAFE_SHIP_PRO_PLUGIN_DIR . 'admin/partials/safe-ship-pro-claims-display.php';
    }
    
    /**
     * AJAX handler for updating claims.
     *
     * @since    1.0.0
     */
    public function ajax_update_claim() {
        check_ajax_referer( 'safe_ship_pro_update_claim', 'security' );
        
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to manage claims.', 'safe-ship-pro' ) ) );
            return;
        }
        
        $claim_id = isset( $_POST['claim_id'] ) ? intval( $_POST['claim_id'] ) : 0;
        $status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';
        $admin_notes = isset( $_POST['admin_notes'] ) ? sanitize_textarea_field( $_POST['admin_notes'] ) : '';
        
        if ( empty( $claim_id ) || empty( $status ) ) {
            wp_send_json_error( array( 'message' => __( 'Missing required fields.', 'safe-ship-pro' ) ) );
            return;
        }
        
        // Update claim
        global $wpdb;
        $table_name = $wpdb->prefix . 'safe_ship_pro_claims';
        
        $result = $wpdb->update(
            $table_name,
            array(
                'claim_status' => $status,
                'admin_notes' => $admin_notes,
                'date_updated' => current_time( 'mysql' ),
            ),
            array( 'id' => $claim_id ),
            array( '%s', '%s', '%s' ),
            array( '%d' )
        );
        
        if ( false === $result && $wpdb->last_error ) {
            wp_send_json_error( array( 'message' => __( 'Error updating claim: ', 'safe-ship-pro' ) . $wpdb->last_error ) );
            return;
        }
        
        // Get claim and order data for notification
        $claim = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $claim_id ) );
        
        if ( ! $claim ) {
            wp_send_json_error( array( 'message' => __( 'Claim not found after update.', 'safe-ship-pro' ) ) );
            return;
        }
        
        // Trigger notification for status change
        do_action( 'safe_ship_pro_claim_updated', $claim_id, $claim->order_id, $status );
        
        wp_send_json_success( array( 'message' => __( 'Claim updated successfully.', 'safe-ship-pro' ) ) );
    }
    
    /**
     * Display claim details for editing in admin.
     *
     * @since    1.0.0
     * @param    int    $claim_id    Claim ID.
     */
    public function display_claim_details( $claim_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'safe_ship_pro_claims';
        
        // Process form submission first
        if (isset($_POST['update_safe_ship_claim']) && isset($_POST['claim_nonce'])) {
            if (wp_verify_nonce($_POST['claim_nonce'], 'safe_ship_pro_update_claim')) {
                $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
                $admin_notes = isset($_POST['admin_notes']) ? sanitize_textarea_field($_POST['admin_notes']) : '';

                if (!empty($status)) {
                    $result = $wpdb->update(
                        $table_name,
                        array(
                            'claim_status' => $status,
                            'admin_notes' => $admin_notes,
                            'date_updated' => current_time('mysql'),
                        ),
                        array('id' => $claim_id),
                        array('%s', '%s', '%s'),
                        array('%d')
                    );

                    if ($result !== false) {
                        // Get the claim to trigger notification
                        $claim = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $claim_id));
                        if ($claim) {
                            // Trigger notification for status change
                            do_action('safe_ship_pro_claim_updated', $claim_id, $claim->order_id, $status);

                            echo '<div class="notice notice-success"><p>' . esc_html__('Claim updated successfully.', 'safe-ship-pro') . '</p></div>';
                        }
                    } else {
                        echo '<div class="notice notice-error"><p>' . esc_html__('Error updating claim.', 'safe-ship-pro') . '</p></div>';
                    }
                }
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html__('Security check failed.', 'safe-ship-pro') . '</p></div>';
            }
        }        
        

        $claim = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $claim_id ) );
        
        if ( ! $claim ) {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Claim not found.', 'safe-ship-pro' ) . '</p></div>';
            return;
        }
        
        // Get user info
        $user_info = get_userdata( $claim->user_id );
        $customer_name = $user_info ? $user_info->display_name : __( 'Unknown', 'safe-ship-pro' );
        $customer_email = $user_info ? $user_info->user_email : '';
        
        // Get order info
        $order = wc_get_order( $claim->order_id );
        $order_number = $order ? $order->get_order_number() : $claim->order_id;
        $order_total = $order ? $order->get_total() : 0;
        $protection_amount = $order ? $order->get_meta( '_safe_ship_pro_protection_amount', true ) : 0;
        
        // Claim type
        $claim_types = array(
            'damaged' => __( 'Damaged Package', 'safe-ship-pro' ),
            'lost' => __( 'Lost Package', 'safe-ship-pro' ),
            'stolen' => __( 'Stolen Package', 'safe-ship-pro' ),
            'delayed' => __( 'Delayed Delivery', 'safe-ship-pro' ),
            'other' => __( 'Other Issue', 'safe-ship-pro' ),
        );
        $claim_type = isset( $claim_types[$claim->claim_type] ) ? $claim_types[$claim->claim_type] : $claim->claim_type;
        
        // Get attachments
        $attachments = ! empty( $claim->attachments ) ? maybe_unserialize( $claim->attachments ) : array();
        
        // Include the admin template
        include SAFE_SHIP_PRO_PLUGIN_DIR . 'admin/partials/safe-ship-pro-claim-details.php';
    }
    
    /**
     * Display new claim form in admin.
     *
     * @since    1.0.0
     * @param    int    $order_id    Order ID.
     */
    public function display_new_claim_form( $order_id ) {
        $order = wc_get_order( $order_id );
        
        if ( ! $order ) {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Order not found.', 'safe-ship-pro' ) . '</p></div>';
            return;
        }
        
        $has_protection = $order->get_meta( '_safe_ship_pro_protection_added', true ) === 'yes';
        
        if ( ! $has_protection ) {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'This order does not have shipping protection.', 'safe-ship-pro' ) . '</p></div>';
            return;
        }
        
        // Get claim types
        $claim_types = get_option( 'safe_ship_pro_claims_types', array(
            'damaged' => __( 'Damaged Package', 'safe-ship-pro' ),
            'lost' => __( 'Lost Package', 'safe-ship-pro' ),
            'stolen' => __( 'Stolen Package', 'safe-ship-pro' ),
            'delayed' => __( 'Delayed Delivery', 'safe-ship-pro' ),
            'other' => __( 'Other Issue', 'safe-ship-pro' ),
        ) );
        
        // Include the admin template
        include SAFE_SHIP_PRO_PLUGIN_DIR . 'admin/partials/safe-ship-pro-new-claim.php';
    }
}