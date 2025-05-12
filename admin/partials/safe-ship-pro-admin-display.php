<?php
/**
 * Admin settings page template.
 *
 * @since      1.0.0
 */
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    
    <h2 class="nav-tab-wrapper">
        <a href="?page=safe-ship-pro&tab=general" class="nav-tab <?php echo ( ! isset( $_GET['tab'] ) || $_GET['tab'] == 'general' ) ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'General Settings', 'safe-ship-pro' ); ?></a>
        <a href="?page=safe-ship-pro&tab=display" class="nav-tab <?php echo ( isset( $_GET['tab'] ) && $_GET['tab'] == 'display' ) ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Display Options', 'safe-ship-pro' ); ?></a>
        <a href="?page=safe-ship-pro&tab=claims" class="nav-tab <?php echo ( isset( $_GET['tab'] ) && $_GET['tab'] == 'claims' ) ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Claims Settings', 'safe-ship-pro' ); ?></a>
    </h2>
    
    <form method="post" action="options.php">
        <?php
        $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
        
        switch ( $active_tab ) {
            case 'display':
                settings_fields( 'safe_ship_pro_display' );
                do_settings_sections( 'safe_ship_pro_display' );
                $this->display_display_settings();
                break;
            case 'claims':
                settings_fields( 'safe_ship_pro_claims' );
                do_settings_sections( 'safe_ship_pro_claims' );
                $this->display_claims_settings();
                break;
            default: // 'general'
                settings_fields( 'safe_ship_pro_general' );
                do_settings_sections( 'safe_ship_pro_general' );
                $this->display_general_settings();
                break;
        }
        
        submit_button();
        ?>
    </form>
</div>

<?php
/**
 * Display general settings.
 */
function display_general_settings() {
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
 */
function display_display_settings() {
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
 */
function display_claims_settings() {
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