<?php
/**
 * Plugin Name: Safe Ship Pro
 * Plugin URI: https://example.com/safe-ship-pro
 * Description: Add shipping protection options to your WooCommerce store.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: safe-ship-pro
 * Domain Path: /languages
 * WC requires at least: 3.0.0
 * WC tested up to: 8.0.0
 * 
 * Woo: 12345:342928dfsfhsf8429842374wdf4234sfd
 * WC requires at least: 3.0.0
 * WC tested up to: 8.0.0
 */
 
/**
 * WooCommerce HPOS compatibility.
 */
add_action('before_woocommerce_init', function() {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        if (method_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil', 'declare_compatibility')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        }
    }
});

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 */
define( 'SAFE_SHIP_PRO_VERSION', '1.0.0' );
define( 'SAFE_SHIP_PRO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SAFE_SHIP_PRO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SAFE_SHIP_PRO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_safe_ship_pro() {
    require_once SAFE_SHIP_PRO_PLUGIN_DIR . 'includes/class-safe-ship-pro-activator.php';
    Safe_Ship_Pro_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_safe_ship_pro() {
    require_once SAFE_SHIP_PRO_PLUGIN_DIR . 'includes/class-safe-ship-pro-deactivator.php';
    Safe_Ship_Pro_Deactivator::deactivate();
    
    // Also deactivate the license
    require_once SAFE_SHIP_PRO_PLUGIN_DIR . 'includes/class-safe-ship-pro-license.php';
    Safe_Ship_Pro_License::deactivate();    
    
}

register_activation_hook( __FILE__, 'activate_safe_ship_pro' );
register_deactivation_hook( __FILE__, 'deactivate_safe_ship_pro' );

/**
 * Check if WooCommerce is active
 */
function safe_ship_pro_check_woocommerce_active() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'safe_ship_pro_woocommerce_missing_notice' );
        return false;
    }
    return true;
}

/**
 * Admin notice for missing WooCommerce
 */
function safe_ship_pro_woocommerce_missing_notice() {
    ?>
    <div class="error">
        <p><?php esc_html_e( 'Safe Ship Pro requires WooCommerce to be installed and active.', 'safe-ship-pro' ); ?></p>
    </div>
    <?php
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require SAFE_SHIP_PRO_PLUGIN_DIR . 'includes/class-safe-ship-pro.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_safe_ship_pro() {
    // Check if WooCommerce is active
    if ( ! safe_ship_pro_check_woocommerce_active() ) {
        return;
    }

    $plugin = new Safe_Ship_Pro();
    $plugin->run();
}
add_action( 'plugins_loaded', 'run_safe_ship_pro' );