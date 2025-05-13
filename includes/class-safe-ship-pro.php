<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 */
class Safe_Ship_Pro {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Safe_Ship_Pro_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->version = SAFE_SHIP_PRO_VERSION;
        $this->plugin_name = 'safe-ship-pro';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_license_hooks(); // Added license hooks
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Safe_Ship_Pro_Loader. Orchestrates the hooks of the plugin.
     * - Safe_Ship_Pro_i18n. Defines internationalization functionality.
     * - Safe_Ship_Pro_Admin. Defines all hooks for the admin area.
     * - Safe_Ship_Pro_Public. Defines all hooks for the public side of the site.
     * - Safe_Ship_Pro_Protection. Defines shipping protection functionality.
     * - Safe_Ship_Pro_Claims. Defines claims functionality.
     * - Safe_Ship_Pro_Emails. Defines email functionality.
     * - Safe_Ship_Pro_Analytics. Defines analytics functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once SAFE_SHIP_PRO_PLUGIN_DIR . 'includes/class-safe-ship-pro-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once SAFE_SHIP_PRO_PLUGIN_DIR . 'includes/class-safe-ship-pro-i18n.php';

        /**
         * Core functionality classes
         */
        require_once SAFE_SHIP_PRO_PLUGIN_DIR . 'includes/class-safe-ship-pro-protection.php';
        require_once SAFE_SHIP_PRO_PLUGIN_DIR . 'includes/class-safe-ship-pro-claims.php';
        require_once SAFE_SHIP_PRO_PLUGIN_DIR . 'includes/class-safe-ship-pro-emails.php';
        require_once SAFE_SHIP_PRO_PLUGIN_DIR . 'includes/class-safe-ship-pro-analytics.php';
        require_once SAFE_SHIP_PRO_PLUGIN_DIR . 'includes/class-safe-ship-pro-license.php'; // Added license class

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once SAFE_SHIP_PRO_PLUGIN_DIR . 'admin/class-safe-ship-pro-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once SAFE_SHIP_PRO_PLUGIN_DIR . 'public/class-safe-ship-pro-public.php';

        $this->loader = new Safe_Ship_Pro_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Safe_Ship_Pro_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new Safe_Ship_Pro_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new Safe_Ship_Pro_Admin( $this->get_plugin_name(), $this->get_version() );
        $protection = new Safe_Ship_Pro_Protection( $this->get_plugin_name(), $this->get_version() );
        $claims = new Safe_Ship_Pro_Claims( $this->get_plugin_name(), $this->get_version() );
        $analytics = new Safe_Ship_Pro_Analytics( $this->get_plugin_name(), $this->get_version() );
        
        // Admin menu and settings
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
        
        // Claims management
        //$this->loader->add_action( 'admin_menu', $claims, 'add_claims_menu' );
        $this->loader->add_action( 'wp_ajax_safe_ship_pro_update_claim', $claims, 'ajax_update_claim' );
        
        // Analytics
        $this->loader->add_action( 'admin_menu', $analytics, 'add_analytics_menu' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new Safe_Ship_Pro_Public( $this->get_plugin_name(), $this->get_version() );
        $protection = new Safe_Ship_Pro_Protection( $this->get_plugin_name(), $this->get_version() );
        $claims = new Safe_Ship_Pro_Claims( $this->get_plugin_name(), $this->get_version() );
        $emails = new Safe_Ship_Pro_Emails( $this->get_plugin_name(), $this->get_version() );
        
        // Enqueue scripts and styles
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        
        // Protection checkout options
        $this->loader->add_action( 'woocommerce_review_order_before_payment', $protection, 'display_protection_option' );
        $this->loader->add_action( 'woocommerce_checkout_update_order_meta', $protection, 'save_protection_option' );
        $this->loader->add_action( 'woocommerce_cart_calculate_fees', $protection, 'add_protection_fee' );
        
        // Protection information display
        $this->loader->add_action( 'woocommerce_before_add_to_cart_button', $protection, 'display_product_protection_info' );
        $this->loader->add_filter( 'woocommerce_get_item_data', $protection, 'display_cart_item_protection_data', 10, 2 );
        
        // Claims system
        $this->loader->add_action( 'init', $claims, 'add_claims_rewrite_endpoint' );
        $this->loader->add_filter( 'woocommerce_account_menu_items', $claims, 'add_claims_endpoint' );
        $this->loader->add_action( 'woocommerce_account_shipping-claims_endpoint', $claims, 'display_claims_page' );
        $this->loader->add_action( 'wp_ajax_safe_ship_pro_submit_claim', $claims, 'ajax_submit_claim' );
        
        // Add order actions
        $this->loader->add_filter( 'woocommerce_my_account_my_orders_actions', $plugin_public, 'add_view_order_actions', 10, 2 );
        
        // Display protection info on order pages
        $this->loader->add_action( 'woocommerce_order_details_after_order_table', $plugin_public, 'display_order_protection_info' );
        $this->loader->add_filter( 'woocommerce_order_item_meta_end', $plugin_public, 'add_order_item_protection_info', 10, 2 );
        
        // Email notifications
        $this->loader->add_action( 'woocommerce_checkout_order_processed', $emails, 'send_protection_confirmation', 10, 3 );
        $this->loader->add_action( 'safe_ship_pro_claim_submitted', $emails, 'send_claim_notifications', 10, 2 );
        $this->loader->add_action( 'safe_ship_pro_claim_updated', $emails, 'send_claim_status_notification', 10, 3 );
    }
    
    
    
    /**
     * Register all of the hooks related to licensing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_license_hooks() {
        $license = new Safe_Ship_Pro_License($this->get_plugin_name(), $this->get_version());
        
        // Add license checks
        $this->loader->add_action('admin_init', $license, 'register_license_settings');
        $this->loader->add_action('admin_notices', $license, 'license_admin_notices');
        $this->loader->add_action('safe_ship_pro_license_check', $license, 'check_license');
        
        // Add plugin update functionality
        $this->loader->add_action('admin_init', $this, 'register_plugin_updater', 0);
    }   
    
    
    /**
     * Register the plugin updater.
     *
     * @since    1.0.0
     */
    public function register_plugin_updater() {
        // Use EDD SL Plugin Updater class
        if (!class_exists('EDD_SL_Plugin_Updater')) {
            require_once SAFE_SHIP_PRO_PLUGIN_DIR . 'includes/class-edd-sl-plugin-updater.php';
        }
        
        // Retrieve license key
        $license_key = trim(get_option('safe_ship_pro_license_key'));
        
        // Setup the updater
        $edd_updater = new EDD_SL_Plugin_Updater(
            'https://safeshippro.com/',
            SAFE_SHIP_PRO_PLUGIN_DIR . 'safe-ship-pro.php',
            array(
                'version'   => SAFE_SHIP_PRO_VERSION,
                'license'   => $license_key,
                'item_name' => 'Safe Ship Pro',
                'author'    => 'SafeShipPro', // Replace with your name
                'url'       => home_url(),
                'beta'      => false
            )
        );
    }    
    
    

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Safe_Ship_Pro_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}