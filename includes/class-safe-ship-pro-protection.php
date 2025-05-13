<?php
/**
 * The shipping protection functionality of the plugin.
 *
 * @since      1.0.0
 */
class Safe_Ship_Pro_Protection {

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
     * Check if shipping protection is enabled.
     *
     * @since    1.0.0
     * @return   boolean    True if protection is enabled.
     */
    public function is_protection_enabled() {
        return get_option( 'safe_ship_pro_protection_enabled', 'yes' ) === 'yes';
    }
    
    /**
     * Check if a product is eligible for shipping protection.
     *
     * @since    1.0.0
     * @param    int    $product_id    The product ID to check.
     * @return   boolean    True if the product is eligible.
     */
    public function is_product_eligible( $product_id ) {
        // Check if product is specifically excluded
        $product_setting = get_post_meta( $product_id, '_safe_ship_pro_protection', true );
        if ( $product_setting === 'no' ) {
            return false;
        }
        
        // Check if product category is excluded
        $excluded_categories = get_option( 'safe_ship_pro_excluded_categories', array() );
        if ( !empty( $excluded_categories ) ) {
            $product_categories = get_the_terms( $product_id, 'product_cat' );
            if ( is_array( $product_categories ) ) {
                foreach ( $product_categories as $category ) {
                    if ( in_array( $category->term_id, $excluded_categories ) ) {
                        return false;
                    }
                }
            }
        }
        
        return true;
    }
    
    /**
     * Calculate protection fee based on settings and cart contents.
     *
     * @since    1.0.0
     * @param    float    $cart_total    The cart total.
     * @return   float    The calculated protection fee.
     */
    public function calculate_protection_fee( $cart_total ) {
        $fee_type = get_option( 'safe_ship_pro_protection_type', 'percentage' );
        $fee_amount = floatval( get_option( 'safe_ship_pro_protection_amount', 1.5 ) );
        $min_fee = floatval( get_option( 'safe_ship_pro_protection_min_fee', 0.99 ) );
        $max_fee = floatval( get_option( 'safe_ship_pro_protection_max_fee', 9.99 ) );
        
        // Calculate the fee based on type
        if ( $fee_type === 'flat' ) {
            $fee = $fee_amount;
        } else {
            // Percentage calculation
            $fee = $cart_total * ($fee_amount / 100);
            
            // Apply min/max constraints
            $fee = max( $min_fee, min( $fee, $max_fee ) );
        }
        
        return round( $fee, 2 );
    }
    
    /**
     * Display shipping protection option on checkout page.
     *
     * @since    1.0.0
     */
    public function display_protection_option() {
        if ( ! $this->is_protection_enabled() ) {
            return;
        }

        // Check if all cart items are eligible
        $all_eligible = true;
        foreach ( WC()->cart->get_cart() as $cart_item ) {
            if ( ! $this->is_product_eligible( $cart_item['product_id'] ) ) {
                $all_eligible = false;
                break;
            }
        }

        if ( ! $all_eligible ) {
            return;
        }

        $cart_total = WC()->cart->get_subtotal();
        $protection_fee = $this->calculate_protection_fee( $cart_total );
        $protection_label = get_option( 'safe_ship_pro_protection_label', __( 'Shipping Protection', 'safe-ship-pro' ) );
        $protection_description = get_option( 'safe_ship_pro_protection_description', __( 'Protect your package against loss, damage, or theft during shipping.', 'safe-ship-pro' ) );
        $protection_policy = get_option( 'safe_ship_pro_protection_policy', '' );

        // Get logo and provider info
        $logo_url = get_option( 'safe_ship_pro_protection_logo', '' );
        $provider_name = get_option( 'safe_ship_pro_provider_name', '' );
        $provider_link = get_option( 'safe_ship_pro_provider_link', '' );

        // Get default checked state (can be controlled by admin)
        $default_checked = get_option( 'safe_ship_pro_default_checked', 'no' ) === 'yes';

        // Check if user already selected protection (for when checkout refreshes)
        $checked = isset( WC()->session ) && WC()->session->get( 'safe_ship_pro_protection_added' ) ? true : $default_checked;

        ?>
        <div class="safe-ship-pro-checkout-option">
            <div class="safe-ship-pro-checkout-header">
                <?php if ( ! empty( $logo_url ) ) : ?>
                    <div class="safe-ship-pro-logo">
                        <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $protection_label ); ?>" />
                    </div>
                <?php endif; ?>

                <div class="safe-ship-pro-checkout-title">
                    <h3><?php echo esc_html( $protection_label ); ?></h3>
                    <p class="safe-ship-pro-checkout-description"><?php echo esc_html( $protection_description ); ?></p>

                    <?php if ( ! empty( $provider_name ) ) : ?>
                    <p class="safe-ship-pro-powered-by">
                        <?php esc_html_e( 'Powered by', 'safe-ship-pro' ); ?> 
                        <?php if ( ! empty( $provider_link ) ) : ?>
                            <a href="<?php echo esc_url( $provider_link ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $provider_name ); ?></a>
                        <?php else : ?>
                            <?php echo esc_html( $provider_name ); ?>
                        <?php endif; ?>
                    </p>
                    <?php endif; ?>
                </div>

                <div class="safe-ship-pro-checkout-price">
                    <span class="safe-ship-pro-fee"><?php echo wc_price( $protection_fee ); ?></span>
                    <label class="safe-ship-pro-toggle">
                        <input type="checkbox" name="safe_ship_pro_protection" id="safe_ship_pro_protection" <?php checked( $checked, true ); ?> />
                        <span class="safe-ship-pro-toggle-slider"></span>
                    </label>
                </div>
            </div>

            <?php if ( ! empty( $protection_policy ) ) : ?>
            <div class="safe-ship-pro-policy-info">
                <small><?php echo wp_kses_post( $protection_policy ); ?></small>
            </div>
            <?php endif; ?>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Update checkout on protection toggle
                $('#safe_ship_pro_protection').on('change', function() {
                    $('body').trigger('update_checkout');
                });
            });
        </script>
        <?php
    }
    
    /**
     * Add protection fee to cart if option is selected.
     *
     * @since    1.0.0
     * @param    WC_Cart    $cart    The WooCommerce cart object.
     */
    public function add_protection_fee( $cart ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }
        
        if ( ! $this->is_protection_enabled() ) {
            return;
        }
        
        // Check if protection is selected
        $protection_selected = false;
        
        // Get from POST (when checkout is updating)
        if ( isset( $_POST['post_data'] ) ) {
            parse_str( $_POST['post_data'], $post_data );
            $protection_selected = isset( $post_data['safe_ship_pro_protection'] );
        } 
        // Get from checkbox directly (page load)
        elseif ( isset( $_POST['safe_ship_pro_protection'] ) ) {
            $protection_selected = true;
        } 
        // Get from session (for persistence)
        elseif ( isset( WC()->session ) && WC()->session->get( 'safe_ship_pro_protection_added' ) ) {
            $protection_selected = true;
        }
        
        // Store in session
        if ( isset( WC()->session ) ) {
            WC()->session->set( 'safe_ship_pro_protection_added', $protection_selected );
        }
        
        // If selected, add the fee
        if ( $protection_selected ) {
            $cart_total = $cart->get_subtotal();
            $protection_fee = $this->calculate_protection_fee( $cart_total );
            $protection_label = get_option( 'safe_ship_pro_protection_label', __( 'Shipping Protection', 'safe-ship-pro' ) );
            
            $cart->add_fee( $protection_label, $protection_fee, true, 'standard' );
        }
    }
    
    /**
     * Save protection option with order.
     *
     * @since    1.0.0
     * @param    int    $order_id    The Order ID.
     */
    public function save_protection_option( $order_id ) {
        if ( isset( $_POST['safe_ship_pro_protection'] ) || 
            ( isset( WC()->session ) && WC()->session->get( 'safe_ship_pro_protection_added' ) ) ) {
            
            // Get order
            $order = wc_get_order( $order_id );
            if ( ! $order ) {
                return;
            }
            
            // Calculate the protection fee
            $cart_total = WC()->cart->get_subtotal();
            $protection_fee = $this->calculate_protection_fee( $cart_total );
            
            // Save as order meta (HPOS compatible)
            $order->update_meta_data( '_safe_ship_pro_protection_added', 'yes' );
            $order->update_meta_data( '_safe_ship_pro_protection_amount', $protection_fee );
            $order->save();
            
            // Clear session
            if ( isset( WC()->session ) ) {
                WC()->session->set( 'safe_ship_pro_protection_added', null );
            }
        }
    }
    
    /**
     * Display shipping protection information on product page.
     *
     * @since    1.0.0
     */
    public function display_product_protection_info() {
        global $product;
        
        if ( ! $this->is_protection_enabled() || ! $this->is_product_eligible( $product->get_id() ) ) {
            return;
        }
        
        $protection_info = get_option( 'safe_ship_pro_product_page_info', '' );
        if ( ! empty( $protection_info ) ) {
            echo '<div class="safe-ship-pro-product-info">';
            echo wp_kses_post( wpautop( $protection_info ) );
            echo '</div>';
        }
    }
    
    /**
     * Display protection info on cart items.
     *
     * @since    1.0.0
     * @param    array    $item_data    Cart item data.
     * @param    array    $cart_item    Cart item.
     * @return   array    Modified item data.
     */
    public function display_cart_item_protection_data( $item_data, $cart_item ) {
        if ( ! $this->is_protection_enabled() || ! $this->is_product_eligible( $cart_item['product_id'] ) ) {
            return $item_data;
        }
        
        if ( isset( WC()->session ) && WC()->session->get( 'safe_ship_pro_protection_added' ) ) {
            $item_data[] = array(
                'key'   => __( 'Eligible for Protection', 'safe-ship-pro' ),
                'value' => __( 'Yes', 'safe-ship-pro' ),
                'display' => '',
            );
        }
        
        return $item_data;
    }
    
    /**
     * Add protection badge to product in shop/category pages.
     *
     * @since    1.0.0
     */
    public function add_protection_badge() {
        global $product;
        
        if ( ! $this->is_protection_enabled() || ! $this->is_product_eligible( $product->get_id() ) ) {
            return;
        }
        
        $show_badge = get_option( 'safe_ship_pro_show_badge', 'no' ) === 'yes';
        if ( ! $show_badge ) {
            return;
        }
        
        $badge_text = get_option( 'safe_ship_pro_badge_text', __( 'Protection Eligible', 'safe-ship-pro' ) );
        
        echo '<span class="safe-ship-pro-badge">' . esc_html( $badge_text ) . '</span>';
    }
    
    /**
     * Filter products by protection eligibility in admin.
     *
     * @since    1.0.0
     * @param    array    $filters    Shop order filters.
     * @return   array    Modified filters.
     */
    public function add_protection_eligible_filter( $filters ) {
        $filters['safe_ship_pro_eligible'] = __( 'Protection Eligible', 'safe-ship-pro' );
        $filters['safe_ship_pro_not_eligible'] = __( 'Not Protection Eligible', 'safe-ship-pro' );
        
        return $filters;
    }
    
    /**
     * Filter products by protection eligibility.
     *
     * @since    1.0.0
     * @param    WP_Query    $query    The product query.
     */
    public function filter_products_by_protection( $query ) {
        global $pagenow, $typenow;
        
        if ( $pagenow === 'edit.php' && $typenow === 'product' ) {
            if ( isset( $_GET['shop_order_status'] ) ) {
                $status = sanitize_text_field( $_GET['shop_order_status'] );
                
                if ( $status === 'safe_ship_pro_eligible' || $status === 'safe_ship_pro_not_eligible' ) {
                    $meta_query = array(
                        array(
                            'key'     => '_safe_ship_pro_protection',
                            'value'   => $status === 'safe_ship_pro_eligible' ? 'yes' : 'no',
                            'compare' => '=',
                        ),
                    );
                    
                    $query->set( 'meta_query', $meta_query );
                }
            }
        }
    }
    
    /**
     * Get total protection revenue in date range.
     *
     * @since    1.0.0
     * @param    string    $start_date    Start date in Y-m-d format.
     * @param    string    $end_date      End date in Y-m-d format.
     * @return   float     Total protection revenue.
     */
    public function get_protection_revenue( $start_date = '', $end_date = '' ) {
        // Use WC_Order_Query for HPOS compatibility
        $args = array(
            'status' => array( 'completed', 'processing' ),
            'meta_query' => array(
                array(
                    'key' => '_safe_ship_pro_protection_added',
                    'value' => 'yes',
                    'compare' => '='
                )
            ),
            'limit' => -1
        );
        
        // Add date query if dates are provided
        if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
            $args['date_created'] = $start_date . '...' . $end_date;
        }
        
        $orders = wc_get_orders( $args );
        
        $total_revenue = 0;
        foreach ( $orders as $order ) {
            $protection_amount = $order->get_meta( '_safe_ship_pro_protection_amount', true );
            if ( $protection_amount ) {
                $total_revenue += floatval( $protection_amount );
            }
        }
        
        return $total_revenue;
    }
    
    /**
     * Get protection option rate (percentage of orders with protection).
     *
     * @since    1.0.0
     * @param    string    $start_date    Start date in Y-m-d format.
     * @param    string    $end_date      End date in Y-m-d format.
     * @return   float     Protection option rate.
     */
    public function get_protection_rate( $start_date = '', $end_date = '' ) {
        // Query args for all orders
        $all_orders_args = array(
            'status' => array( 'completed', 'processing', 'on-hold' ),
            'limit' => -1
        );
        
        // Query args for protected orders
        $protected_orders_args = array(
            'status' => array( 'completed', 'processing', 'on-hold' ),
            'meta_query' => array(
                array(
                    'key' => '_safe_ship_pro_protection_added',
                    'value' => 'yes',
                    'compare' => '='
                )
            ),
            'limit' => -1
        );
        
        // Add date query if dates are provided
        if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
            $all_orders_args['date_created'] = $start_date . '...' . $end_date;
            $protected_orders_args['date_created'] = $start_date . '...' . $end_date;
        }
        
        // Get orders
        $all_orders = wc_get_orders( $all_orders_args );
        $protected_orders = wc_get_orders( $protected_orders_args );
        
        $total_orders = count( $all_orders );
        
        if ( $total_orders <= 0 ) {
            return 0;
        }
        
        $protected_orders_count = count( $protected_orders );
        
        // Calculate rate
        return ( $protected_orders_count / $total_orders ) * 100;
    }
}