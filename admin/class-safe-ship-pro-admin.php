<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 */
class Safe_Ship_Pro_Admin {

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
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        $this->analytics = new Safe_Ship_Pro_Analytics( $this->plugin_name, $this->version );
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style( $this->plugin_name, SAFE_SHIP_PRO_PLUGIN_URL . 'admin/css/safe-ship-pro-admin.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script( $this->plugin_name, SAFE_SHIP_PRO_PLUGIN_URL . 'admin/js/safe-ship-pro-admin.js', array( 'jquery' ), $this->version, false );
        
        // Add claims management script only on claims page
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'safe-ship-pro-claims' ) {
            wp_enqueue_script( $this->plugin_name . '-claims', SAFE_SHIP_PRO_PLUGIN_URL . 'admin/js/safe-ship-pro-claims.js', array( 'jquery' ), $this->version, false );
            wp_localize_script( $this->plugin_name . '-claims', 'safe_ship_pro_claims', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'safe_ship_pro_update_claim' ),
                'i18n' => array(
                    'confirm_status_change' => __( 'Are you sure you want to change the status?', 'safe-ship-pro' ),
                    'saving' => __( 'Saving...', 'safe-ship-pro' ),
                    'error' => __( 'Error updating claim.', 'safe-ship-pro' ),
                ),
            ) );
        }
        
        // Add analytics scripts only on analytics page
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'safe-ship-pro' ) {
            wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js', array(), '3.7.1', true );
            wp_enqueue_script( $this->plugin_name . '-analytics', SAFE_SHIP_PRO_PLUGIN_URL . 'admin/js/safe-ship-pro-analytics.js', array( 'jquery', 'chart-js' ), $this->version, false );
        }
        
        
        // Add this inside the enqueue_scripts method
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'safe-ship-pro-settings' ) {
            wp_enqueue_media();
        }        
        
    }

    /**
     * Add admin menu.
     *
     * @since    1.0.0
     */

    public function add_admin_menu() {
        add_menu_page(
            __( 'Safe Ship Pro', 'safe-ship-pro' ),
            __( 'Safe Ship Pro', 'safe-ship-pro' ),
            'manage_woocommerce',
            'safe-ship-pro',
            array( $this->analytics, 'display_analytics_page' ),
            'dashicons-dashboard',
            56
        );

        add_submenu_page(
            'safe-ship-pro',
            __( 'Dashboard', 'safe-ship-pro' ),
            __( 'Dashboard', 'safe-ship-pro' ),
            'manage_woocommerce',
            'safe-ship-pro',
            array( $this->analytics, 'display_analytics_page' )
        );

        add_submenu_page(
            'safe-ship-pro',
            __( 'Orders', 'safe-ship-pro' ),
            __( 'Orders', 'safe-ship-pro' ),
            'manage_woocommerce',
            'safe-ship-pro-orders',
            array( $this, 'display_orders_page' )
        );

        // Claims menu moved under Orders instead of being directly under Safe Ship Pro
        add_submenu_page(
            'safe-ship-pro',
            __( 'Claims', 'safe-ship-pro' ), // Title changed from "Shipping Claims" to "Claims"
            __( 'Claims', 'safe-ship-pro' ), // Menu label changed from "Shipping Claims" to "Claims"
            'manage_woocommerce',
            'safe-ship-pro-claims',
            array( $this, 'display_claims_admin_page' )
        );

        add_submenu_page(
            'safe-ship-pro',
            __( 'Settings', 'safe-ship-pro' ),
            __( 'Settings', 'safe-ship-pro' ),
            'manage_woocommerce',
            'safe-ship-pro-settings',
            array( $this, 'display_settings_page' )
        );

        add_submenu_page(
            'safe-ship-pro',
            __( 'License', 'safe-ship-pro' ),
            __( 'License', 'safe-ship-pro' ),
            'manage_woocommerce',
            'safe-ship-pro-license',
            array( $this, 'display_license_page' )
        );

        // Add product settings to WooCommerce product data tabs
        add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_product_data_tab' ) );
        add_action( 'woocommerce_product_data_panels', array( $this, 'add_product_data_fields' ) );
        add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_data_fields' ) );

        // Add meta box to order page
        add_action( 'add_meta_boxes', array( $this, 'add_order_meta_box' ) );

        // Add dashboard widget
        add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
    }

    /**
     * Display the settings page.
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        require_once SAFE_SHIP_PRO_PLUGIN_DIR . 'admin/partials/safe-ship-pro-admin-display.php';
    }

    /**
     * Register settings.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // General settings
        register_setting( 'safe_ship_pro_general', 'safe_ship_pro_protection_enabled' );
        register_setting( 'safe_ship_pro_general', 'safe_ship_pro_protection_type' );
        register_setting( 'safe_ship_pro_general', 'safe_ship_pro_protection_amount' );
        register_setting( 'safe_ship_pro_general', 'safe_ship_pro_protection_min_fee' );
        register_setting( 'safe_ship_pro_general', 'safe_ship_pro_protection_max_fee' );
        register_setting( 'safe_ship_pro_general', 'safe_ship_pro_protection_label' );
        register_setting( 'safe_ship_pro_general', 'safe_ship_pro_protection_description' );
        register_setting( 'safe_ship_pro_general', 'safe_ship_pro_default_checked' );
        
        // Display settings
        register_setting( 'safe_ship_pro_display', 'safe_ship_pro_protection_policy' );
        register_setting( 'safe_ship_pro_display', 'safe_ship_pro_product_page_info' );
        register_setting( 'safe_ship_pro_display', 'safe_ship_pro_excluded_categories' );
        
        // Claims settings
        register_setting( 'safe_ship_pro_claims', 'safe_ship_pro_claims_enabled' );
        register_setting( 'safe_ship_pro_claims', 'safe_ship_pro_claims_email_notifications' );
        register_setting( 'safe_ship_pro_claims', 'safe_ship_pro_claims_auto_approve' );
        register_setting( 'safe_ship_pro_claims', 'safe_ship_pro_claims_types' );
        
        
        register_setting( 'safe_ship_pro_general', 'safe_ship_pro_protection_logo' );
        register_setting( 'safe_ship_pro_general', 'safe_ship_pro_provider_name' );
        register_setting( 'safe_ship_pro_general', 'safe_ship_pro_provider_link' );        
    }

    /**
     * Add Shipping Protection tab to WooCommerce product data metabox.
     *
     * @since    1.0.0
     * @param    array    $tabs    Product data tabs.
     * @return   array    Modified product data tabs.
     */
    public function add_product_data_tab( $tabs ) {
        $tabs['shipping_protection'] = array(
            'label'    => __( 'Shipping Protection', 'safe-ship-pro' ),
            'target'   => 'shipping_protection_product_data',
            'class'    => array(),
            'priority' => 100,
        );
        return $tabs;
    }

    /**
     * Add product data fields to the Shipping Protection tab.
     *
     * @since    1.0.0
     */
    public function add_product_data_fields() {
        global $post;
        
        echo '<div id="shipping_protection_product_data" class="panel woocommerce_options_panel">';
        
        woocommerce_wp_select( array(
            'id'            => '_safe_ship_pro_protection',
            'label'         => __( 'Shipping Protection', 'safe-ship-pro' ),
            'description'   => __( 'Enable or disable shipping protection for this product.', 'safe-ship-pro' ),
            'desc_tip'      => true,
            'options'       => array(
                ''    => __( 'Global Setting (Default)', 'safe-ship-pro' ),
                'yes' => __( 'Enable Protection', 'safe-ship-pro' ),
                'no'  => __( 'Disable Protection', 'safe-ship-pro' ),
            ),
        ) );
        
        do_action( 'safe_ship_pro_product_data_fields' );
        
        echo '</div>';
    }

    /**
     * Save product data fields.
     *
     * @since    1.0.0
     * @param    int    $post_id    Product ID.
     */
    public function save_product_data_fields( $post_id ) {
        $protection = isset( $_POST['_safe_ship_pro_protection'] ) ? sanitize_text_field( $_POST['_safe_ship_pro_protection'] ) : '';
        update_post_meta( $post_id, '_safe_ship_pro_protection', $protection );
    }

    /**
     * Add order protection information to the order details page.
     *
     * @since    1.0.0
     * @param    WC_Order    $order    Order object.
     */
    public function display_order_protection_info( $order ) {
        $order_id = $order->get_id();
        // HPOS compatible way to check for protection
        $has_protection = $order->get_meta( '_safe_ship_pro_protection_added', true ) === 'yes';
        
        if ( $has_protection ) {
            $protection_amount = $order->get_meta( '_safe_ship_pro_protection_amount', true );
            
            echo '<div class="order_data_column">';
            echo '<h3>' . esc_html__( 'Shipping Protection', 'safe-ship-pro' ) . '</h3>';
            echo '<p>' . esc_html__( 'Status:', 'safe-ship-pro' ) . ' <mark class="yes">' . esc_html__( 'Protected', 'safe-ship-pro' ) . '</mark></p>';
            echo '<p>' . esc_html__( 'Amount:', 'safe-ship-pro' ) . ' ' . wc_price( $protection_amount ) . '</p>';
            
            // Get claims for this order
            global $wpdb;
            $table_name = $wpdb->prefix . 'safe_ship_pro_claims';
            $claims = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM $table_name WHERE order_id = %d",
                $order_id
            ) );
            
            if ( $claims ) {
                echo '<p>' . esc_html__( 'Claims:', 'safe-ship-pro' ) . '</p>';
                echo '<ul>';
                foreach ( $claims as $claim ) {
                    $status_labels = array(
                        'pending' => __( 'Pending Review', 'safe-ship-pro' ),
                        'processing' => __( 'Processing', 'safe-ship-pro' ),
                        'approved' => __( 'Approved', 'safe-ship-pro' ),
                        'denied' => __( 'Denied', 'safe-ship-pro' ),
                        'completed' => __( 'Completed', 'safe-ship-pro' ),
                    );
                    
                    $status_label = isset( $status_labels[$claim->claim_status] ) ? $status_labels[$claim->claim_status] : $claim->claim_status;
                    
                    echo '<li>';
                    echo '<a href="' . admin_url( 'admin.php?page=safe-ship-pro-claims&action=edit&claim_id=' . $claim->id ) . '">';
                    echo esc_html__( 'Claim #', 'safe-ship-pro' ) . esc_html( $claim->id ) . ' - ' . esc_html( $status_label );
                    echo '</a>';
                    echo '</li>';
                }
                echo '</ul>';
            } else {
                echo '<p>' . esc_html__( 'No claims filed.', 'safe-ship-pro' ) . '</p>';
            }
            
            echo '</div>';
        }
    }

    /**
     * Add meta box to order page.
     *
     * @since    1.0.0
     */
    public function add_order_meta_box() {
        add_meta_box(
            'safe_ship_pro_order_meta_box',
            __( 'Shipping Protection', 'safe-ship-pro' ),
            array( $this, 'render_order_meta_box' ),
            'shop_order',
            'side',
            'high'
        );
    }

    /**
     * Render the order meta box.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    Post object.
     */
    public function render_order_meta_box( $post ) {
        $order = wc_get_order( $post->ID );
        
        if ( ! $order ) {
            return;
        }
        
        // HPOS compatible way to check for protection
        $has_protection = $order->get_meta( '_safe_ship_pro_protection_added', true ) === 'yes';
        
        if ( $has_protection ) {
            $protection_amount = $order->get_meta( '_safe_ship_pro_protection_amount', true );
            
            echo '<p><strong>' . esc_html__( 'Status:', 'safe-ship-pro' ) . '</strong> <mark class="yes">' . esc_html__( 'Protected', 'safe-ship-pro' ) . '</mark></p>';
            echo '<p><strong>' . esc_html__( 'Amount:', 'safe-ship-pro' ) . '</strong> ' . wc_price( $protection_amount ) . '</p>';
            
            // Get claims for this order
            global $wpdb;
            $table_name = $wpdb->prefix . 'safe_ship_pro_claims';
            $claims = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM $table_name WHERE order_id = %d",
                $order->get_id()
            ) );
            
            if ( $claims ) {
                echo '<p><strong>' . esc_html__( 'Claims:', 'safe-ship-pro' ) . '</strong></p>';
                echo '<ul class="safe-ship-pro-claims-list">';
                foreach ( $claims as $claim ) {
                    $status_labels = array(
                        'pending' => __( 'Pending Review', 'safe-ship-pro' ),
                        'processing' => __( 'Processing', 'safe-ship-pro' ),
                        'approved' => __( 'Approved', 'safe-ship-pro' ),
                        'denied' => __( 'Denied', 'safe-ship-pro' ),
                        'completed' => __( 'Completed', 'safe-ship-pro' ),
                    );
                    
                    $status_label = isset( $status_labels[$claim->claim_status] ) ? $status_labels[$claim->claim_status] : $claim->claim_status;
                    
                    echo '<li>';
                    echo '<a href="' . admin_url( 'admin.php?page=safe-ship-pro-claims&action=edit&claim_id=' . $claim->id ) . '">';
                    echo esc_html__( 'Claim #', 'safe-ship-pro' ) . esc_html( $claim->id ) . ' - ' . esc_html( $status_label );
                    echo '</a>';
                    echo '</li>';
                }
                echo '</ul>';
            } else {
                echo '<p>' . esc_html__( 'No claims filed.', 'safe-ship-pro' ) . '</p>';
            }
            
            echo '<p><a href="' . admin_url( 'admin.php?page=safe-ship-pro-claims&action=new&order_id=' . $order->get_id() ) . '" class="button">' . esc_html__( 'Create New Claim', 'safe-ship-pro' ) . '</a></p>';
        } else {
            echo '<p>' . esc_html__( 'No shipping protection purchased for this order.', 'safe-ship-pro' ) . '</p>';
        }
    }
    
    /**
     * Add dashboard widget for analytics summary.
     *
     * @since    1.0.0
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'safe_ship_pro_dashboard_widget',
            __( 'Safe Ship Pro - Protection Overview', 'safe-ship-pro' ),
            array( $this, 'display_dashboard_widget' )
        );
    }
    
    /**
     * Display dashboard widget content.
     *
     * @since    1.0.0
     */
    public function display_dashboard_widget() {
        $analytics = new Safe_Ship_Pro_Analytics( $this->plugin_name, $this->version );
        $summary = $analytics->get_analytics_summary();
        
        require_once SAFE_SHIP_PRO_PLUGIN_DIR . 'admin/partials/safe-ship-pro-dashboard-widget.php';
    }

    /**
     * Display general settings.
     *
     * @since    1.0.0
     */
    public function display_general_settings() {
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Enable Shipping Protection', 'safe-ship-pro' ); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="safe_ship_pro_protection_enabled" value="yes" <?php checked( get_option( 'safe_ship_pro_protection_enabled', 'yes' ), 'yes' ); ?> />
                        <?php esc_html_e( 'Enable shipping protection option during checkout', 'safe-ship-pro' ); ?>
                    </label>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Protection Fee Type', 'safe-ship-pro' ); ?></th>
                <td>
                    <select name="safe_ship_pro_protection_type">
                        <option value="percentage" <?php selected( get_option( 'safe_ship_pro_protection_type', 'percentage' ), 'percentage' ); ?>><?php esc_html_e( 'Percentage of Cart Value', 'safe-ship-pro' ); ?></option>
                        <option value="flat" <?php selected( get_option( 'safe_ship_pro_protection_type', 'percentage' ), 'flat' ); ?>><?php esc_html_e( 'Flat Fee', 'safe-ship-pro' ); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Protection Fee Amount', 'safe-ship-pro' ); ?></th>
                <td>
                    <input type="number" name="safe_ship_pro_protection_amount" value="<?php echo esc_attr( get_option( 'safe_ship_pro_protection_amount', '1.5' ) ); ?>" step="0.01" min="0" />
                    <p class="description">
                        <?php esc_html_e( 'For percentage, enter the percentage value (e.g., 1.5 for 1.5%). For flat fee, enter the amount.', 'safe-ship-pro' ); ?>
                    </p>
                </td>
            </tr>
            
            <tr valign="top" class="percentage-option">
                <th scope="row"><?php esc_html_e( 'Minimum Fee', 'safe-ship-pro' ); ?></th>
                <td>
                    <input type="number" name="safe_ship_pro_protection_min_fee" value="<?php echo esc_attr( get_option( 'safe_ship_pro_protection_min_fee', '0.99' ) ); ?>" step="0.01" min="0" />
                    <p class="description">
                        <?php esc_html_e( 'Minimum protection fee amount (for percentage-based calculation only).', 'safe-ship-pro' ); ?>
                    </p>
                </td>
            </tr>
            
            <tr valign="top" class="percentage-option">
                <th scope="row"><?php esc_html_e( 'Maximum Fee', 'safe-ship-pro' ); ?></th>
                <td>
                    <input type="number" name="safe_ship_pro_protection_max_fee" value="<?php echo esc_attr( get_option( 'safe_ship_pro_protection_max_fee', '9.99' ) ); ?>" step="0.01" min="0" />
                    <p class="description">
                        <?php esc_html_e( 'Maximum protection fee amount (for percentage-based calculation only).', 'safe-ship-pro' ); ?>
                    </p>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Protection Label', 'safe-ship-pro' ); ?></th>
                <td>
                    <input type="text" name="safe_ship_pro_protection_label" value="<?php echo esc_attr( get_option( 'safe_ship_pro_protection_label', __( 'Shipping Protection', 'safe-ship-pro' ) ) ); ?>" class="regular-text" />
                    <p class="description">
                        <?php esc_html_e( 'The label displayed for the shipping protection option.', 'safe-ship-pro' ); ?>
                    </p>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Protection Description', 'safe-ship-pro' ); ?></th>
                <td>
                    <textarea name="safe_ship_pro_protection_description" rows="3" class="large-text"><?php echo esc_textarea( get_option( 'safe_ship_pro_protection_description', __( 'Protect your package against loss, damage, or theft during shipping.', 'safe-ship-pro' ) ) ); ?></textarea>
                    <p class="description">
                        <?php esc_html_e( 'Short description displayed next to the protection option.', 'safe-ship-pro' ); ?>
                    </p>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Default Checked', 'safe-ship-pro' ); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="safe_ship_pro_default_checked" value="yes" <?php checked( get_option( 'safe_ship_pro_default_checked', 'no' ), 'yes' ); ?> />
                        <?php esc_html_e( 'Check the shipping protection option by default', 'safe-ship-pro' ); ?>
                    </label>
                </td>
            </tr>
            
            
            
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Protection Logo', 'safe-ship-pro' ); ?></th>
                <td>
                    <input type="text" name="safe_ship_pro_protection_logo" id="safe_ship_pro_logo_url" value="<?php echo esc_attr( get_option( 'safe_ship_pro_protection_logo', '' ) ); ?>" class="regular-text" />
                    <button type="button" class="button safe-ship-pro-upload-logo"><?php esc_html_e( 'Upload Logo', 'safe-ship-pro' ); ?></button>
                    <p class="description">
                        <?php esc_html_e( 'Upload or enter the URL of the logo to display with the protection option.', 'safe-ship-pro' ); ?>
                    </p>
                    <div id="safe-ship-pro-logo-preview" style="margin-top: 10px; max-width: 100px;">
                        <?php 
                        $logo_url = get_option( 'safe_ship_pro_protection_logo', '' );
                        if ( !empty($logo_url) ) {
                            echo '<img src="' . esc_url($logo_url) . '" style="max-width: 100%;" />';
                        }
                        ?>
                    </div>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Provider Name', 'safe-ship-pro' ); ?></th>
                <td>
                    <input type="text" name="safe_ship_pro_provider_name" value="<?php echo esc_attr( get_option( 'safe_ship_pro_provider_name', '' ) ); ?>" class="regular-text" />
                    <p class="description">
                        <?php esc_html_e( 'Optional. The name of the protection provider (e.g., "Navidium").', 'safe-ship-pro' ); ?>
                    </p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Provider Link', 'safe-ship-pro' ); ?></th>
                <td>
                    <input type="url" name="safe_ship_pro_provider_link" value="<?php echo esc_attr( get_option( 'safe_ship_pro_provider_link', '' ) ); ?>" class="regular-text" />
                    <p class="description">
                        <?php esc_html_e( 'Optional. Link to the protection provider\'s website.', 'safe-ship-pro' ); ?>
                    </p>
                </td>
            </tr>            
            
            
        </table>
        
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Toggle min/max fee fields based on protection type
                function toggleFeeFields() {
                    var protectionType = $('select[name="safe_ship_pro_protection_type"]').val();
                    
                    if (protectionType === 'percentage') {
                        $('.percentage-option').show();
                    } else {
                        $('.percentage-option').hide();
                    }
                }
                
                toggleFeeFields();
                
                $('select[name="safe_ship_pro_protection_type"]').on('change', function() {
                    toggleFeeFields();
                });
            });
        </script>
        <?php
    }
    
    /**
     * Display display settings.
     *
     * @since    1.0.0
     */
    public function display_display_settings() {
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Protection Policy', 'safe-ship-pro' ); ?></th>
                <td>
                    <textarea name="safe_ship_pro_protection_policy" rows="5" class="large-text"><?php echo esc_textarea( get_option( 'safe_ship_pro_protection_policy', __( 'Our shipping protection covers loss, damage, or theft during transit. Claims must be filed within 14 days of the expected delivery date.', 'safe-ship-pro' ) ) ); ?></textarea>
                    <p class="description">
                        <?php esc_html_e( 'Policy details displayed on checkout and order confirmation.', 'safe-ship-pro' ); ?>
                    </p>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Product Page Information', 'safe-ship-pro' ); ?></th>
                <td>
                    <textarea name="safe_ship_pro_product_page_info" rows="5" class="large-text"><?php echo esc_textarea( get_option( 'safe_ship_pro_product_page_info', '' ) ); ?></textarea>
                    <p class="description">
                        <?php esc_html_e( 'Optional information about shipping protection to display on the product page. Leave empty to disable.', 'safe-ship-pro' ); ?>
                    </p>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Excluded Product Categories', 'safe-ship-pro' ); ?></th>
                <td>
                    <?php

                    $excluded_categories = get_option( 'safe_ship_pro_excluded_categories', array() );
                    // Make sure it's an array
                    if (!is_array($excluded_categories)) {
                        $excluded_categories = array();
                    }        
        
        
                    $product_categories = get_terms( array(
                        'taxonomy' => 'product_cat',
                        'hide_empty' => false,
                    ) );
                    
                    if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
                        echo '<select name="safe_ship_pro_excluded_categories[]" multiple="multiple" class="regular-text" style="height: 150px;">';
                        
                        foreach ( $product_categories as $category ) {
                            echo '<option value="' . esc_attr( $category->term_id ) . '" ' . selected( in_array( $category->term_id, $excluded_categories ), true, false ) . '>' . esc_html( $category->name ) . '</option>';
                        }
                        
                        echo '</select>';
                        echo '<p class="description">' . esc_html__( 'Select categories that should not be eligible for shipping protection.', 'safe-ship-pro' ) . '</p>';
                    } else {
                        echo '<p>' . esc_html__( 'No product categories found.', 'safe-ship-pro' ) . '</p>';
                    }
                    ?>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Display claims settings.
     *
     * @since    1.0.0
     */
    public function display_claims_settings() {
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Enable Claims System', 'safe-ship-pro' ); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="safe_ship_pro_claims_enabled" value="yes" <?php checked( get_option( 'safe_ship_pro_claims_enabled', 'yes' ), 'yes' ); ?> />
                        <?php esc_html_e( 'Allow customers to file shipping protection claims', 'safe-ship-pro' ); ?>
                    </label>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Email Notifications', 'safe-ship-pro' ); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="safe_ship_pro_claims_email_notifications" value="yes" <?php checked( get_option( 'safe_ship_pro_claims_email_notifications', 'yes' ), 'yes' ); ?> />
                        <?php esc_html_e( 'Send email notifications for protection purchases and claim updates', 'safe-ship-pro' ); ?>
                    </label>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Auto-Approve Claims', 'safe-ship-pro' ); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="safe_ship_pro_claims_auto_approve" value="yes" <?php checked( get_option( 'safe_ship_pro_claims_auto_approve', 'no' ), 'yes' ); ?> />
                        <?php esc_html_e( 'Automatically approve claims under a certain value', 'safe-ship-pro' ); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e( 'Note: This is a premium feature. Please upgrade to use auto-approval.', 'safe-ship-pro' ); ?>
                    </p>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Claim Types', 'safe-ship-pro' ); ?></th>
                <td>
                    <?php
                    $default_claim_types = array(
                        'damaged' => __( 'Damaged Package', 'safe-ship-pro' ),
                        'lost' => __( 'Lost Package', 'safe-ship-pro' ),
                        'stolen' => __( 'Stolen Package', 'safe-ship-pro' ),
                        'delayed' => __( 'Delayed Delivery', 'safe-ship-pro' ),
                        'other' => __( 'Other Issue', 'safe-ship-pro' ),
                    );
                    
                    $saved_claim_types = get_option( 'safe_ship_pro_claims_types', $default_claim_types );
                    
                    if ( is_array( $saved_claim_types ) ) {
                        echo '<div id="claim-types-container">';
                        
                        foreach ( $saved_claim_types as $type_key => $type_label ) {
                            echo '<div class="claim-type-row">';
                            echo '<input type="text" name="safe_ship_pro_claims_types[' . esc_attr( $type_key ) . ']" value="' . esc_attr( $type_label ) . '" class="regular-text" />';
                            echo ' <button type="button" class="button remove-claim-type">' . esc_html__( 'Remove', 'safe-ship-pro' ) . '</button>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                        
                        echo '<div class="claim-type-controls">';
                        echo '<button type="button" class="button add-claim-type">' . esc_html__( 'Add Claim Type', 'safe-ship-pro' ) . '</button>';
                        echo '</div>';
                        
                        echo '<script type="text/html" id="claim-type-template">';
                        echo '<div class="claim-type-row">';
                        echo '<input type="text" name="safe_ship_pro_claims_types[{key}]" value="" class="regular-text" />';
                        echo ' <button type="button" class="button remove-claim-type">' . esc_html__( 'Remove', 'safe-ship-pro' ) . '</button>';
                        echo '</div>';
                        echo '</script>';
                        
                        echo '<script type="text/javascript">';
                        echo 'jQuery(document).ready(function($) {';
                        echo '    var template = $("#claim-type-template").html();';
                        echo '    var typeCounter = ' . count( $saved_claim_types ) . ';';
                        echo '    ';
                        echo '    $(".add-claim-type").on("click", function() {';
                        echo '        var newRow = template.replace(/{key}/g, "new_" + typeCounter);';
                        echo '        $("#claim-types-container").append(newRow);';
                        echo '        typeCounter++;';
                        echo '    });';
                        echo '    ';
                        echo '    $(document).on("click", ".remove-claim-type", function() {';
                        echo '        $(this).closest(".claim-type-row").remove();';
                        echo '    });';
                        echo '});';
                        echo '</script>';
                    }
                    ?>
                    <p class="description">
                        <?php esc_html_e( 'Define claim types that customers can select when filing a claim.', 'safe-ship-pro' ); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }

    public function display_orders_page() {
        include_once SAFE_SHIP_PRO_PLUGIN_DIR . 'admin/partials/safe-ship-pro-orders-display.php';
    }

    public function display_license_page() {
        echo '<div class="wrap"><h1>License (Coming Soon)</h1></div>';
    }

}