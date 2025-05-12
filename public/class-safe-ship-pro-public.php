<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 */
class Safe_Ship_Pro_Public {

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
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style( $this->plugin_name, SAFE_SHIP_PRO_PLUGIN_URL . 'public/css/safe-ship-pro-public.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script( $this->plugin_name, SAFE_SHIP_PRO_PLUGIN_URL . 'public/js/safe-ship-pro-public.js', array( 'jquery' ), $this->version, false );
        
        // Add claims form script only on claims page
        if ( is_account_page() && isset( WC()->query->query_vars['shipping-claims'] ) ) {
            wp_enqueue_script( $this->plugin_name . '-claims', SAFE_SHIP_PRO_PLUGIN_URL . 'public/js/safe-ship-pro-claims.js', array( 'jquery' ), $this->version, false );
            wp_localize_script( $this->plugin_name . '-claims', 'safe_ship_pro_claims', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'safe_ship_pro_submit_claim' ),
                'i18n' => array(
                    'confirm_submission' => __( 'Are you sure you want to submit this claim?', 'safe-ship-pro' ),
                    'submitting' => __( 'Submitting...', 'safe-ship-pro' ),
                    'error' => __( 'Error submitting claim.', 'safe-ship-pro' ),
                ),
            ) );
        }
    }
    
    /**
     * Add view order action for claims.
     *
     * @since    1.0.0
     * @param    array     $actions    Order actions.
     * @param    WC_Order  $order      Order object.
     * @return   array     Modified order actions.
     */
    public function add_view_order_actions( $actions, $order ) {
        // Check if order has protection - HPOS compatible
        $has_protection = $order->get_meta( '_safe_ship_pro_protection_added', true ) === 'yes';
        
        if ( $has_protection ) {
            // Check if claims are enabled
            if ( get_option( 'safe_ship_pro_claims_enabled', 'yes' ) === 'yes' ) {
                // Add action to file claim
                $actions['safe_ship_pro_file_claim'] = array(
                    'url'  => wc_get_endpoint_url( 'shipping-claims', '', wc_get_page_permalink( 'myaccount' ) ) . '?action=new&order_id=' . $order->get_id(),
                    'name' => __( 'File Shipping Claim', 'safe-ship-pro' ),
                );
            }
        }
        
        return $actions;
    }
    
    /**
     * Add protection info to order received page and emails.
     *
     * @since    1.0.0
     * @param    WC_Order  $order  Order object.
     */
    public function display_order_protection_info( $order ) {
        // Check if order has protection - HPOS compatible
        $has_protection = $order->get_meta( '_safe_ship_pro_protection_added', true ) === 'yes';
        
        if ( $has_protection ) {
            $protection_amount = $order->get_meta( '_safe_ship_pro_protection_amount', true );
            $protection_policy = get_option( 'safe_ship_pro_protection_policy', '' );
            
            echo '<div class="safe-ship-pro-order-protection-info">';
            echo '<h3>' . esc_html__( 'Shipping Protection', 'safe-ship-pro' ) . '</h3>';
            echo '<p>' . esc_html__( 'Your order includes shipping protection for', 'safe-ship-pro' ) . ' ' . wc_price( $protection_amount ) . '</p>';
            
            if ( ! empty( $protection_policy ) ) {
                echo '<p class="safe-ship-pro-policy-info">';
                echo wp_kses_post( $protection_policy );
                echo '</p>';
            }
            
            // If claims are enabled, show info about filing claims
            if ( get_option( 'safe_ship_pro_claims_enabled', 'yes' ) === 'yes' ) {
                echo '<p>' . esc_html__( 'If you encounter any issues with your shipment, you can file a claim from your account dashboard.', 'safe-ship-pro' ) . '</p>';
            }
            
            echo '</div>';
        }
    }
    
    /**
     * Add protection info to order details in My Account.
     *
     * @since    1.0.0
     * @param    array     $item_data    Order item data.
     * @param    WC_Order_Item  $item    Order item.
     * @return   array     Modified item data.
     */
    public function add_order_item_protection_info( $item_data, $item ) {
        // Get order
        $order = $item->get_order();
        if ( ! $order ) {
            return $item_data;
        }
        
        // Check if order has protection - HPOS compatible
        $has_protection = $order->get_meta( '_safe_ship_pro_protection_added', true ) === 'yes';
        
        if ( $has_protection ) {
            // Add protection info to each item
            // Note: This will add to all items, but could be filtered by product eligibility if needed
            $item_data[] = array(
                'key'   => __( 'Shipping Protection', 'safe-ship-pro' ),
                'value' => __( 'Included', 'safe-ship-pro' ),
                'display' => '',
            );
        }
        
        return $item_data;
    }
}