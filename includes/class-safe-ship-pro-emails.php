<?php
/**
 * The email notifications functionality of the plugin.
 *
 * @since      1.0.0
 */
class Safe_Ship_Pro_Emails {

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
     * Send protection confirmation email to customer.
     *
     * @since    1.0.0
     * @param    int       $order_id        The order ID.
     * @param    array     $posted_data     Posted data from checkout.
     * @param    WC_Order  $order           The order object.
     */
    public function send_protection_confirmation( $order_id, $posted_data, $order ) {
        // Check if notifications are enabled
        if ( get_option( 'safe_ship_pro_claims_email_notifications', 'yes' ) !== 'yes' ) {
            return;
        }
        
        // Check if order has protection - HPOS compatible
        $has_protection = $order->get_meta( '_safe_ship_pro_protection_added', true ) === 'yes';
        
        if ( ! $has_protection ) {
            return;
        }
        
        $to = $order->get_billing_email();
        $subject = apply_filters( 'safe_ship_pro_protection_email_subject', 
            sprintf( __( 'Shipping Protection Confirmation for Order #%s', 'safe-ship-pro' ), $order->get_order_number() )
        );
        
        $protection_amount = $order->get_meta( '_safe_ship_pro_protection_amount', true );
        $protection_policy = get_option( 'safe_ship_pro_protection_policy', '' );
        
        ob_start();
        include SAFE_SHIP_PRO_PLUGIN_DIR . 'public/partials/emails/safe-ship-pro-protection-confirmation.php';
        $message = ob_get_clean();
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail( $to, $subject, $message, $headers );
    }
    
    /**
     * Send notifications when a claim is submitted.
     *
     * @since    1.0.0
     * @param    int    $claim_id    The claim ID.
     * @param    int    $order_id    The order ID.
     */
    public function send_claim_notifications( $claim_id, $order_id ) {
        // Check if notifications are enabled
        if ( get_option( 'safe_ship_pro_claims_email_notifications', 'yes' ) !== 'yes' ) {
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'safe_ship_pro_claims';
        $claim = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $claim_id ) );
        
        if ( ! $claim ) {
            return;
        }
        
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }
        
        // Send notification to admin
        $this->send_admin_claim_notification( $claim, $order );
        
        // Send confirmation to customer
        $this->send_customer_claim_confirmation( $claim, $order );
    }
    
    /**
     * Send claim notification to admin.
     *
     * @since    1.0.0
     * @param    object    $claim    The claim object.
     * @param    WC_Order  $order    The order object.
     */
    private function send_admin_claim_notification( $claim, $order ) {
        $admin_email = get_option( 'admin_email' );
        $to = apply_filters( 'safe_ship_pro_admin_email', $admin_email );
        
        $subject = apply_filters( 'safe_ship_pro_admin_claim_email_subject', 
            sprintf( __( 'New Shipping Protection Claim for Order #%s', 'safe-ship-pro' ), $order->get_order_number() )
        );
        
        $customer = $order->get_formatted_billing_full_name();
        $user_info = get_userdata( $claim->user_id );
        $user_email = $user_info ? $user_info->user_email : '-';
        
        $claim_types = array(
            'damaged' => __( 'Damaged Package', 'safe-ship-pro' ),
            'lost' => __( 'Lost Package', 'safe-ship-pro' ),
            'stolen' => __( 'Stolen Package', 'safe-ship-pro' ),
            'delayed' => __( 'Delayed Delivery', 'safe-ship-pro' ),
            'other' => __( 'Other Issue', 'safe-ship-pro' ),
        );
        
        $claim_type = isset( $claim_types[$claim->claim_type] ) ? $claim_types[$claim->claim_type] : $claim->claim_type;
        $admin_url = admin_url( 'admin.php?page=safe-ship-pro-claims&action=edit&claim_id=' . $claim->id );
        
        ob_start();
        include SAFE_SHIP_PRO_PLUGIN_DIR . 'public/partials/emails/safe-ship-pro-admin-claim-notification.php';
        $message = ob_get_clean();
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail( $to, $subject, $message, $headers );
    }
    
    /**
     * Send claim confirmation to customer.
     *
     * @since    1.0.0
     * @param    object    $claim    The claim object.
     * @param    WC_Order  $order    The order object.
     */
    private function send_customer_claim_confirmation( $claim, $order ) {
        $to = $order->get_billing_email();
        
        $subject = apply_filters( 'safe_ship_pro_customer_claim_email_subject', 
            sprintf( __( 'Your Shipping Protection Claim for Order #%s', 'safe-ship-pro' ), $order->get_order_number() )
        );
        
        $customer = $order->get_formatted_billing_full_name();
        
        $claim_types = array(
            'damaged' => __( 'Damaged Package', 'safe-ship-pro' ),
            'lost' => __( 'Lost Package', 'safe-ship-pro' ),
            'stolen' => __( 'Stolen Package', 'safe-ship-pro' ),
            'delayed' => __( 'Delayed Delivery', 'safe-ship-pro' ),
            'other' => __( 'Other Issue', 'safe-ship-pro' ),
        );
        
        $claim_type = isset( $claim_types[$claim->claim_type] ) ? $claim_types[$claim->claim_type] : $claim->claim_type;
        $account_url = wc_get_endpoint_url( 'shipping-claims', '', wc_get_page_permalink( 'myaccount' ) );
        
        ob_start();
        include SAFE_SHIP_PRO_PLUGIN_DIR . 'public/partials/emails/safe-ship-pro-customer-claim-confirmation.php';
        $message = ob_get_clean();
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail( $to, $subject, $message, $headers );
    }
    
    /**
     * Send notification when claim status is updated.
     *
     * @since    1.0.0
     * @param    int       $claim_id    The claim ID.
     * @param    int       $order_id    The order ID.
     * @param    string    $status      The new status.
     */
    public function send_claim_status_notification( $claim_id, $order_id, $status ) {
        // Check if notifications are enabled
        if ( get_option( 'safe_ship_pro_claims_email_notifications', 'yes' ) !== 'yes' ) {
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'safe_ship_pro_claims';
        $claim = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $claim_id ) );
        
        if ( ! $claim ) {
            return;
        }
        
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }
        
        $to = $order->get_billing_email();
        
        $subject = apply_filters( 'safe_ship_pro_claim_status_email_subject', 
            sprintf( __( 'Update on Your Shipping Protection Claim for Order #%s', 'safe-ship-pro' ), $order->get_order_number() )
        );
        
        $customer = $order->get_formatted_billing_full_name();
        
        $claim_types = array(
            'damaged' => __( 'Damaged Package', 'safe-ship-pro' ),
            'lost' => __( 'Lost Package', 'safe-ship-pro' ),
            'stolen' => __( 'Stolen Package', 'safe-ship-pro' ),
            'delayed' => __( 'Delayed Delivery', 'safe-ship-pro' ),
            'other' => __( 'Other Issue', 'safe-ship-pro' ),
        );
        
        $status_labels = array(
            'pending' => __( 'Pending Review', 'safe-ship-pro' ),
            'processing' => __( 'Processing', 'safe-ship-pro' ),
            'approved' => __( 'Approved', 'safe-ship-pro' ),
            'denied' => __( 'Denied', 'safe-ship-pro' ),
            'completed' => __( 'Completed', 'safe-ship-pro' ),
        );
        
        $claim_type = isset( $claim_types[$claim->claim_type] ) ? $claim_types[$claim->claim_type] : $claim->claim_type;
        $status_label = isset( $status_labels[$status] ) ? $status_labels[$status] : $status;
        $account_url = wc_get_endpoint_url( 'shipping-claims', '', wc_get_page_permalink( 'myaccount' ) );
        
        ob_start();
        include SAFE_SHIP_PRO_PLUGIN_DIR . 'public/partials/emails/safe-ship-pro-claim-status-update.php';
        $message = ob_get_clean();
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail( $to, $subject, $message, $headers );
    }
}