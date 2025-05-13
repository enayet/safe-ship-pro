<?php
/**
 * The licensing functionality of the plugin.
 *
 * @since      1.0.0
 */
class Safe_Ship_Pro_License {

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
     * The store URL for EDD licensing.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $store_url    The store URL.
     */
    private $store_url = 'https://safeshippro.com/';

    /**
     * The item name for EDD licensing.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $item_name    The item name.
     */
    private $item_name = 'Safe Ship Pro';

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name    The name of the plugin.
     * @param    string    $version        The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        // Register license settings
        add_action('admin_init', array($this, 'register_license_settings'));
        
        // Schedule license check
        if (!wp_next_scheduled('safe_ship_pro_license_check')) {
            wp_schedule_event(time(), 'daily', 'safe_ship_pro_license_check');
        }
        
        // Hook into scheduled license check
        add_action('safe_ship_pro_license_check', array($this, 'check_license'));
        
        // Admin notices for license status
        add_action('admin_notices', array($this, 'license_admin_notices'));
    }

    /**
     * Register license settings.
     *
     * @since    1.0.0
     */
    public function register_license_settings() {
        register_setting('safe_ship_pro_license', 'safe_ship_pro_license_key', array($this, 'sanitize_license'));
        register_setting('safe_ship_pro_license', 'safe_ship_pro_license_status');
    }

    /**
     * Sanitize license.
     *
     * @since    1.0.0
     * @param    string    $new    The license key.
     * @return   string    Sanitized license key.
     */
    public function sanitize_license($new) {
        $old = get_option('safe_ship_pro_license_key');
        
        if ($old && $old != $new) {
            // New license has been entered, clear the status
            delete_option('safe_ship_pro_license_status');
        }
        
        return sanitize_text_field($new);
    }

    /**
     * Activate the license.
     *
     * @since    1.0.0
     */
    public function activate_license() {
        // Verify nonce
        if (!isset($_POST['safe_ship_pro_license_nonce']) || !wp_verify_nonce($_POST['safe_ship_pro_license_nonce'], 'safe_ship_pro_license_nonce')) {
            return;
        }
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Get license key
        $license = trim(get_option('safe_ship_pro_license_key'));
        
        // Data to send to the API
        $api_params = array(
            'edd_action' => 'activate_license',
            'license'    => $license,
            'item_name'  => urlencode($this->item_name),
            'url'        => home_url()
        );
        
        // Call the API
        $response = wp_remote_post($this->store_url, array(
            'timeout'   => 15,
            'sslverify' => false,
            'body'      => $api_params
        ));
        
        // Check for API error
        if (is_wp_error($response)) {
            return;
        }
        
        // Parse API response
        $license_data = json_decode(wp_remote_retrieve_body($response));
        
        // Update license status
        update_option('safe_ship_pro_license_status', $license_data->license);
        
        // Store additional license data
        update_option('safe_ship_pro_license_data', $license_data);
    }

    /**
     * Deactivate the license.
     *
     * @since    1.0.0
     */
    public function deactivate_license() {
        // Verify nonce
        if (!isset($_POST['safe_ship_pro_license_nonce']) || !wp_verify_nonce($_POST['safe_ship_pro_license_nonce'], 'safe_ship_pro_license_nonce')) {
            return;
        }
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Get license key
        $license = trim(get_option('safe_ship_pro_license_key'));
        
        // Data to send to the API
        $api_params = array(
            'edd_action' => 'deactivate_license',
            'license'    => $license,
            'item_name'  => urlencode($this->item_name),
            'url'        => home_url()
        );
        
        // Call the API
        $response = wp_remote_post($this->store_url, array(
            'timeout'   => 15,
            'sslverify' => false,
            'body'      => $api_params
        ));
        
        // Check for API error
        if (is_wp_error($response)) {
            return;
        }
        
        // Parse API response
        $license_data = json_decode(wp_remote_retrieve_body($response));
        
        // Update license status
        if ($license_data->license == 'deactivated') {
            delete_option('safe_ship_pro_license_status');
            delete_option('safe_ship_pro_license_data');
        }
    }

    /**
     * Check if license is valid.
     *
     * @since    1.0.0
     * @return   boolean    True if license is valid.
     */
    public function is_license_valid() {
        $status = get_option('safe_ship_pro_license_status');
        return ($status == 'valid');
    }

    /**
     * Get license expiration date.
     *
     * @since    1.0.0
     * @return   string    License expiration date.
     */
    public function get_expiration_date() {
        $license_data = get_option('safe_ship_pro_license_data');
        
        if (!$license_data || !isset($license_data->expires)) {
            return '';
        }
        
        if ($license_data->expires == 'lifetime') {
            return __('Lifetime', 'safe-ship-pro');
        }
        
        return date_i18n(get_option('date_format'), strtotime($license_data->expires));
    }

    /**
     * Check license status with the remote server.
     *
     * @since    1.0.0
     */
    public function check_license() {
        // Get license key
        $license = trim(get_option('safe_ship_pro_license_key'));
        
        if (empty($license)) {
            return;
        }
        
        // Data to send to the API
        $api_params = array(
            'edd_action' => 'check_license',
            'license'    => $license,
            'item_name'  => urlencode($this->item_name),
            'url'        => home_url()
        );
        
        // Call the API
        $response = wp_remote_post($this->store_url, array(
            'timeout'   => 15,
            'sslverify' => false,
            'body'      => $api_params
        ));
        
        // Check for API error
        if (is_wp_error($response)) {
            return;
        }
        
        // Parse API response
        $license_data = json_decode(wp_remote_retrieve_body($response));
        
        // Update license status and data
        update_option('safe_ship_pro_license_status', $license_data->license);
        update_option('safe_ship_pro_license_data', $license_data);
    }

    /**
     * Display admin notices for license status.
     *
     * @since    1.0.0
     */
    public function license_admin_notices() {
        // Only show notices on plugin pages
        $screen = get_current_screen();
        if (!isset($screen->id) || strpos($screen->id, 'safe-ship-pro') === false) {
            return;
        }
        
        // Check if license key is set
        $license = get_option('safe_ship_pro_license_key');
        if (empty($license)) {
            echo '<div class="notice notice-warning"><p>' . sprintf(
                __('Please <a href="%s">enter your license key</a> for Safe Ship Pro to receive updates and support.', 'safe-ship-pro'),
                admin_url('admin.php?page=safe-ship-pro-license')
            ) . '</p></div>';
            return;
        }
        
        // Check license status
        $status = get_option('safe_ship_pro_license_status');
        if ($status != 'valid') {
            echo '<div class="notice notice-error"><p>' . sprintf(
                __('Your Safe Ship Pro license is not active. Please <a href="%s">activate your license</a>.', 'safe-ship-pro'),
                admin_url('admin.php?page=safe-ship-pro-license')
            ) . '</p></div>';
        }
        
        // Check license expiration
        $license_data = get_option('safe_ship_pro_license_data');
        if ($status == 'valid' && $license_data && isset($license_data->expires) && $license_data->expires != 'lifetime') {
            $expiration = strtotime($license_data->expires);
            $now = time();
            
            // Show notice if license expires in less than 30 days
            if ($expiration < strtotime('+30 days', $now)) {
                echo '<div class="notice notice-warning"><p>' . sprintf(
                    __('Your Safe Ship Pro license will expire on %s. <a href="%s" target="_blank">Renew your license</a> to continue receiving updates and support.', 'safe-ship-pro'),
                    date_i18n(get_option('date_format'), $expiration),
                    $this->store_url . 'checkout/?edd_license_key=' . $license
                ) . '</p></div>';
            }
        }
    }

    /**
     * Display the license page.
     *
     * @since    1.0.0
     */
    public function display_license_page() {
        $license = get_option('safe_ship_pro_license_key');
        $status  = get_option('safe_ship_pro_license_status');
        
        // Process form submission
        if (isset($_POST['safe_ship_pro_license_activate'])) {
            $this->activate_license();
            // Refresh the page to get updated status
            echo '<meta http-equiv="refresh" content="0">';
        } elseif (isset($_POST['safe_ship_pro_license_deactivate'])) {
            $this->deactivate_license();
            // Refresh the page to get updated status
            echo '<meta http-equiv="refresh" content="0">';
        }
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Safe Ship Pro License', 'safe-ship-pro'); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('safe_ship_pro_license'); ?>
                
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row" valign="top">
                                <?php esc_html_e('License Key', 'safe-ship-pro'); ?>
                            </th>
                            <td>
                                <input id="safe_ship_pro_license_key" name="safe_ship_pro_license_key" type="text" class="regular-text" value="<?php echo esc_attr($license); ?>" <?php echo ($status == 'valid' ? 'readonly' : ''); ?> />
                                <p class="description"><?php esc_html_e('Enter your license key to enable updates and support.', 'safe-ship-pro'); ?></p>
                            </td>
                        </tr>
                        <?php if (!empty($license)) : ?>
                        <tr valign="top">
                            <th scope="row" valign="top">
                                <?php esc_html_e('License Status', 'safe-ship-pro'); ?>
                            </th>
                            <td>
                                <?php
                                $status_classes = array(
                                    'valid'     => 'status-valid',
                                    'invalid'   => 'status-invalid',
                                    'expired'   => 'status-expired',
                                    'disabled'  => 'status-disabled',
                                    'site_inactive' => 'status-inactive',
                                );
                                
                                $status_labels = array(
                                    'valid'     => __('Active', 'safe-ship-pro'),
                                    'invalid'   => __('Invalid', 'safe-ship-pro'),
                                    'expired'   => __('Expired', 'safe-ship-pro'),
                                    'disabled'  => __('Disabled', 'safe-ship-pro'),
                                    'site_inactive' => __('Inactive', 'safe-ship-pro'),
                                );
                                
                                $status_class = isset($status_classes[$status]) ? $status_classes[$status] : 'status-unknown';
                                $status_label = isset($status_labels[$status]) ? $status_labels[$status] : __('Unknown', 'safe-ship-pro');
                                ?>
                                <span class="license-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span>
                                
                                <?php if ($status == 'valid') : ?>
                                    <p><strong><?php esc_html_e('Expires:', 'safe-ship-pro'); ?></strong> <?php echo esc_html($this->get_expiration_date()); ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php submit_button(__('Save License', 'safe-ship-pro')); ?>
            </form>
            
            <?php if (!empty($license)) : ?>
            <form method="post">
                <?php wp_nonce_field('safe_ship_pro_license_nonce', 'safe_ship_pro_license_nonce'); ?>
                
                <div class="license-actions">
                    <?php if ($status == 'valid') : ?>
                        <input type="submit" class="button-secondary" name="safe_ship_pro_license_deactivate" value="<?php esc_attr_e('Deactivate License', 'safe-ship-pro'); ?>"/>
                    <?php else : ?>
                        <input type="submit" class="button-primary" name="safe_ship_pro_license_activate" value="<?php esc_attr_e('Activate License', 'safe-ship-pro'); ?>"/>
                    <?php endif; ?>
                </div>
            </form>
            <?php endif; ?>
        </div>
        
        <style>
            .license-status {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 3px;
                font-weight: bold;
            }
            
            .status-valid {
                background: #c6e1c6;
                color: #5b841b;
            }
            
            .status-invalid,
            .status-expired,
            .status-disabled {
                background: #eba3a3;
                color: #761919;
            }
            
            .status-inactive,
            .status-unknown {
                background: #f8dda7;
                color: #94660c;
            }
            
            .license-actions {
                margin-top: 20px;
            }
        </style>
        <?php
    }

    /**
     * Clean up when the plugin is deactivated.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('safe_ship_pro_license_check');
    }
}