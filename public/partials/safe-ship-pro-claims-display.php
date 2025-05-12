<?php
/**
 * Claims page template for My Account.
 *
 * @since      1.0.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<?php

// Process form submission
if (isset($_POST['submit_safe_ship_claim']) && isset($_POST['safe_ship_pro_nonce'])) {
    // Verify nonce
    if (!wp_verify_nonce($_POST['safe_ship_pro_nonce'], 'safe_ship_pro_submit_claim')) {
        wc_add_notice(__('Security check failed. Please try again.', 'safe-ship-pro'), 'error');
    } else {
        $user_id = get_current_user_id();
        if (!$user_id) {
            wc_add_notice(__('You must be logged in to submit a claim.', 'safe-ship-pro'), 'error');
        } else {
            // Get form data
            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
            $claim_type = isset($_POST['claim_type']) ? sanitize_text_field($_POST['claim_type']) : '';
            $claim_reason = isset($_POST['claim_reason']) ? sanitize_textarea_field($_POST['claim_reason']) : '';
            
            // Validate data
            if (empty($order_id) || empty($claim_type) || empty($claim_reason)) {
                wc_add_notice(__('Please fill in all required fields.', 'safe-ship-pro'), 'error');
            } else {
                // Verify order belongs to user and has protection
                $order = wc_get_order($order_id);
                if (!$order || $order->get_customer_id() != $user_id) {
                    wc_add_notice(__('Invalid order selection.', 'safe-ship-pro'), 'error');
                } else {
                    // Check if protection was added
                    $has_protection = $order->get_meta('_safe_ship_pro_protection_added', true) === 'yes';
                    if (!$has_protection) {
                        wc_add_notice(__('This order does not have shipping protection.', 'safe-ship-pro'), 'error');
                    } else {
                        $attachments = array();
                        
                        // Handle file attachments
                        if (!empty($_FILES['claim_files']['name'][0])) {
                            if (!function_exists('wp_handle_upload')) {
                                require_once(ABSPATH . 'wp-admin/includes/file.php');
                            }
                            require_once(ABSPATH . 'wp-admin/includes/image.php');
                            
                            // Create upload directory if it doesn't exist
                            $upload_dir = wp_upload_dir();
                            $claims_dir = $upload_dir['basedir'] . '/safe-ship-pro-claims';
                            if (!file_exists($claims_dir)) {
                                wp_mkdir_p($claims_dir);
                            }
                            
                            // Create index.php file to prevent directory listing
                            if (!file_exists($claims_dir . '/index.php')) {
                                $f = fopen($claims_dir . '/index.php', 'w');
                                fwrite($f, '<?php // Silence is golden');
                                fclose($f);
                            }
                            
                            // Process multiple files
                            $files = $_FILES['claim_files'];
                            $file_count = count($files['name']);
                            
                            for ($i = 0; $i < $file_count; $i++) {
                                if ($files['error'][$i] == 0) {
                                    $file = array(
                                        'name'     => sanitize_file_name($files['name'][$i]),
                                        'type'     => $files['type'][$i],
                                        'tmp_name' => $files['tmp_name'][$i],
                                        'error'    => $files['error'][$i],
                                        'size'     => $files['size'][$i]
                                    );
                                    
                                    $upload_overrides = array('test_form' => false);
                                    $movefile = wp_handle_upload($file, $upload_overrides);
                                    
                                    if ($movefile && !isset($movefile['error'])) {
                                        $attachments[] = $movefile['url'];
                                    }
                                }
                            }
                        }
                        
                        // Insert claim into database
                        global $wpdb;
                        $table_name = $wpdb->prefix . 'safe_ship_pro_claims';
                        
                        $data = array(
                            'date_created' => current_time('mysql'),
                            'date_updated' => current_time('mysql'),
                            'user_id' => $user_id,
                            'order_id' => $order_id,
                            'claim_type' => $claim_type,
                            'claim_reason' => $claim_reason,
                            'claim_status' => 'pending',
                            'attachments' => !empty($attachments) ? serialize($attachments) : '',
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
                        
                        // Insert the data
                        $result = $wpdb->insert($table_name, $data, $format);
                        
                        if ($result === false) {
                            wc_add_notice(__('Error saving claim. Please try again.', 'safe-ship-pro') . ' (' . $wpdb->last_error . ')', 'error');
                        } else {
                            // Get the inserted ID
                            $claim_id = $wpdb->insert_id;
                            
                            // Trigger notification
                            do_action('safe_ship_pro_claim_submitted', $claim_id, $order_id);
                            
                            wc_add_notice(__('Your claim has been submitted successfully.', 'safe-ship-pro'), 'success');
                            
                            // Redirect to claims list
                            wp_safe_redirect(wc_get_endpoint_url('shipping-claims'));
                            exit;
                        }
                    }
                }
            }
        }
    }
}
?>




<div class="safe-ship-pro-claims-page">
    <h2><?php esc_html_e( 'Shipping Protection Claims', 'safe-ship-pro' ); ?></h2>
    
    <?php
    // Handle new claim form
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'new' && isset( $_GET['order_id'] ) ) {
        $order_id = intval( $_GET['order_id'] );
        $order = wc_get_order( $order_id );
        
        // Verify order belongs to current user and has protection
        if ( $order && $order->get_customer_id() === get_current_user_id() ) {
            // Check if order has protection in HPOS compatible way
            $has_protection = $order->get_meta( '_safe_ship_pro_protection_added', true ) === 'yes';
            
            if ( $has_protection ) {
                // Display new claim form
                ?>
                <div class="safe-ship-pro-new-claim">
                    <p><?php esc_html_e( 'Please provide details about your shipping issue to file a claim.', 'safe-ship-pro' ); ?></p>
                    
                    <form method="post" class="safe-ship-pro-claim-form" enctype="multipart/form-data">
                        <div class="safe-ship-pro-form-row">
                            <label for="order_id"><?php esc_html_e( 'Order', 'safe-ship-pro' ); ?></label>
                            <div class="safe-ship-pro-form-field">
                                <strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong>
                                <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>" />
                            </div>
                        </div>

                        <div class="safe-ship-pro-form-row">
                            <label for="claim_type"><?php esc_html_e( 'Issue Type', 'safe-ship-pro' ); ?>*</label>
                            <div class="safe-ship-pro-form-field">
                                <select name="claim_type" id="claim_type" required>
                                    <option value=""><?php esc_html_e( '-- Select Issue Type --', 'safe-ship-pro' ); ?></option>
                                    <?php
                                    $claim_types = get_option( 'safe_ship_pro_claims_types', array(
                                        'damaged' => __( 'Damaged Package', 'safe-ship-pro' ),
                                        'lost' => __( 'Lost Package', 'safe-ship-pro' ),
                                        'stolen' => __( 'Stolen Package', 'safe-ship-pro' ),
                                        'delayed' => __( 'Delayed Delivery', 'safe-ship-pro' ),
                                        'other' => __( 'Other Issue', 'safe-ship-pro' ),
                                    ) );

                                    foreach ( $claim_types as $type_key => $type_label ) {
                                        echo '<option value="' . esc_attr( $type_key ) . '">' . esc_html( $type_label ) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="safe-ship-pro-form-row">
                            <label for="claim_reason"><?php esc_html_e( 'Reason', 'safe-ship-pro' ); ?>*</label>
                            <div class="safe-ship-pro-form-field">
                                <textarea name="claim_reason" id="claim_reason" rows="5" required></textarea>
                                <p class="description"><?php esc_html_e( 'Please provide details about the issue and why you are filing a claim.', 'safe-ship-pro' ); ?></p>
                            </div>
                        </div>

                        <div class="safe-ship-pro-form-row">
                            <label for="claim_files"><?php esc_html_e( 'Supporting Files', 'safe-ship-pro' ); ?></label>
                            <div class="safe-ship-pro-form-field">
                                <input type="file" name="claim_files[]" id="claim_files" multiple />
                                <p class="description"><?php esc_html_e( 'Optional. Upload photos or documents to support your claim. Accepted formats: JPG, PNG, PDF (Max 5MB each).', 'safe-ship-pro' ); ?></p>
                            </div>
                        </div>

                        <div class="safe-ship-pro-form-row">
                            <div class="safe-ship-pro-form-submit">
                                <!-- Change this part - add name to the submit button and use WordPress nonce -->
                                <?php wp_nonce_field('safe_ship_pro_submit_claim', 'safe_ship_pro_nonce'); ?>
                                <button type="submit" name="submit_safe_ship_claim" class="button"><?php esc_html_e( 'Submit Claim', 'safe-ship-pro' ); ?></button>
                                <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'shipping-claims' ) ); ?>" class="button cancel"><?php esc_html_e( 'Cancel', 'safe-ship-pro' ); ?></a>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Debug Info (remove in production) -->
                    <?php if (defined('WP_DEBUG') && WP_DEBUG): ?>
                    <div class="debug-info" style="margin-top:20px; padding:10px; background:#f9f9f9; border:1px solid #eee;">
                        <h4>Debug Information:</h4>
                        <p>Form action: <?php echo admin_url('admin-ajax.php'); ?></p>
                        <p>User ID: <?php echo get_current_user_id(); ?></p>
                        <p>Order ID: <?php echo $order_id; ?></p>
                        <p>Has Protection: <?php echo $has_protection ? 'Yes' : 'No'; ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php
                return;
            } else {
                // Order doesn't have protection
                echo '<div class="woocommerce-message woocommerce-error">';
                esc_html_e( 'This order does not have shipping protection.', 'safe-ship-pro' );
                echo '</div>';
            }
        } else {
            // Invalid order
            echo '<div class="woocommerce-message woocommerce-error">';
            esc_html_e( 'Invalid order selected or order does not belong to your account.', 'safe-ship-pro' );
            echo '</div>';
        }
    }
    
    // List existing claims
    if ( ! empty( $claims ) ) {
        ?>
        <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive safe-ship-pro-claims-table">
            <thead>
                <tr>
                    <th class="claim-id"><?php esc_html_e( 'Claim ID', 'safe-ship-pro' ); ?></th>
                    <th class="claim-date"><?php esc_html_e( 'Date', 'safe-ship-pro' ); ?></th>
                    <th class="claim-order"><?php esc_html_e( 'Order', 'safe-ship-pro' ); ?></th>
                    <th class="claim-type"><?php esc_html_e( 'Type', 'safe-ship-pro' ); ?></th>
                    <th class="claim-status"><?php esc_html_e( 'Status', 'safe-ship-pro' ); ?></th>
                    <th class="claim-actions"><?php esc_html_e( 'Actions', 'safe-ship-pro' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ( $claims as $claim ) {
                    // Get order info
                    $order = wc_get_order( $claim->order_id );
                    $order_number = $order ? $order->get_order_number() : $claim->order_id;
                    $order_url = $order ? $order->get_view_order_url() : '';
                    
                    // Claim type
                    $claim_types = array(
                        'damaged' => __( 'Damaged Package', 'safe-ship-pro' ),
                        'lost' => __( 'Lost Package', 'safe-ship-pro' ),
                        'stolen' => __( 'Stolen Package', 'safe-ship-pro' ),
                        'delayed' => __( 'Delayed Delivery', 'safe-ship-pro' ),
                        'other' => __( 'Other Issue', 'safe-ship-pro' ),
                    );
                    $claim_type = isset( $claim_types[$claim->claim_type] ) ? $claim_types[$claim->claim_type] : $claim->claim_type;
                    
                    // Status
                    $status_labels = array(
                        'pending' => __( 'Pending Review', 'safe-ship-pro' ),
                        'processing' => __( 'Processing', 'safe-ship-pro' ),
                        'approved' => __( 'Approved', 'safe-ship-pro' ),
                        'denied' => __( 'Denied', 'safe-ship-pro' ),
                        'completed' => __( 'Completed', 'safe-ship-pro' ),
                    );
                    $status_label = isset( $status_labels[$claim->claim_status] ) ? $status_labels[$claim->claim_status] : $claim->claim_status;
                    $status_class = 'status-' . sanitize_html_class( $claim->claim_status );
                    ?>
                    <tr>
                        <td class="claim-id" data-title="<?php esc_attr_e( 'Claim ID', 'safe-ship-pro' ); ?>">
                            <?php echo esc_html( $claim->id ); ?>
                        </td>
                        <td class="claim-date" data-title="<?php esc_attr_e( 'Date', 'safe-ship-pro' ); ?>">
                            <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $claim->date_created ) ) ); ?>
                        </td>
                        <td class="claim-order" data-title="<?php esc_attr_e( 'Order', 'safe-ship-pro' ); ?>">
                            <a href="<?php echo esc_url( $order_url ); ?>">#<?php echo esc_html( $order_number ); ?></a>
                        </td>
                        <td class="claim-type" data-title="<?php esc_attr_e( 'Type', 'safe-ship-pro' ); ?>">
                            <?php echo esc_html( $claim_type ); ?>
                        </td>
                        <td class="claim-status" data-title="<?php esc_attr_e( 'Status', 'safe-ship-pro' ); ?>">
                            <span class="claim-status-badge <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span>
                        </td>
                        <td class="claim-actions" data-title="<?php esc_attr_e( 'Actions', 'safe-ship-pro' ); ?>">
                            <a href="#" class="button view-claim" data-claim-id="<?php echo esc_attr( $claim->id ); ?>">
                                <?php esc_html_e( 'View Details', 'safe-ship-pro' ); ?>
                            </a>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        
        <!-- Claim Details Modal -->
        <div id="safe-ship-pro-claim-modal" class="safe-ship-pro-modal">
            <div class="safe-ship-pro-modal-content">
                <span class="safe-ship-pro-modal-close">&times;</span>
                <div id="safe-ship-pro-claim-details-content"></div>
            </div>
        </div>
        <?php
    } else {
        // No claims yet
        ?>
        <div class="woocommerce-message">
            <?php esc_html_e( 'You have not filed any shipping protection claims yet.', 'safe-ship-pro' ); ?>
        </div>
        <?php
    }
    
    // Show button to select order for new claim
    if ( ! empty( $protected_orders ) ) {
        ?>
        <div class="safe-ship-pro-new-claim-section">
            <h3><?php esc_html_e( 'File a New Claim', 'safe-ship-pro' ); ?></h3>
            <p><?php esc_html_e( 'Select an order to file a new shipping protection claim:', 'safe-ship-pro' ); ?></p>
            
            <form method="get" class="safe-ship-pro-select-order-form">
                <input type="hidden" name="ep_page" value="shipping-claims" />
                <input type="hidden" name="action" value="new" />
                <select name="order_id" required>
                    <option value=""><?php esc_html_e( 'Select an order...', 'safe-ship-pro' ); ?></option>
                    <?php
                    foreach ( $protected_orders as $order ) {
                        // Check if order already has a claim
                        $has_claim = false;
                        foreach ( $claims as $claim ) {
                            if ( $claim->order_id == $order->get_id() ) {
                                $has_claim = true;
                                break;
                            }
                        }
                        
                        echo '<option value="' . esc_attr( $order->get_id() ) . '"' . ( $has_claim ? ' disabled' : '' ) . '>';
                        echo '#' . esc_html( $order->get_order_number() ) . ' - ' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $order->get_date_created() ) ) );
                        
                        if ( $has_claim ) {
                            echo ' (' . esc_html__( 'Claim already filed', 'safe-ship-pro' ) . ')';
                        }
                        
                        echo '</option>';
                    }
                    ?>
                </select>
                <button type="submit" class="button"><?php esc_html_e( 'Continue', 'safe-ship-pro' ); ?></button>
            </form>
        </div>
        <?php
    } else {
        // No protected orders found
        ?>
        <div class="safe-ship-pro-new-claim-section">
            <h3><?php esc_html_e( 'File a New Claim', 'safe-ship-pro' ); ?></h3>
            <p><?php esc_html_e( 'You do not have any orders with shipping protection. Shipping protection must be added during checkout to be eligible for claims.', 'safe-ship-pro' ); ?></p>
        </div>
        <?php
    }
    ?>
</div>

<style>
    .safe-ship-pro-claims-table {
        margin-bottom: 30px;
    }
    
    .claim-status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .status-pending {
        background: #f8dda7;
        color: #94660c;
    }
    
    .status-processing {
        background: #c6e1c6;
        color: #5b841b;
    }
    
    .status-approved {
        background: #c8d7e1;
        color: #2e4453;
    }
    
    .status-denied {
        background: #eba3a3;
        color: #761919;
    }
    
    .status-completed {
        background: #c6e1c6;
        color: #5b841b;
    }
    
    .safe-ship-pro-new-claim-section {
        margin-top: 30px;
        padding: 20px;
        background: #f8f8f8;
        border: 1px solid #e5e5e5;
    }
    
    .safe-ship-pro-select-order-form {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .safe-ship-pro-select-order-form select {
        flex: 1;
    }
    
    .safe-ship-pro-claim-form .safe-ship-pro-form-row {
        margin-bottom: 20px;
    }
    
    .safe-ship-pro-claim-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .safe-ship-pro-claim-form .description {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
    
    .safe-ship-pro-form-submit {
        display: flex;
        align-items: center;
    }
    
    .safe-ship-pro-form-submit .button.cancel {
        margin-left: 10px;
    }
    
    .safe-ship-pro-form-submit .spinner {
        background: url(../images/spinner.gif) no-repeat;
        background-size: 20px 20px;
        display: inline-block;
        visibility: hidden;
        vertical-align: middle;
        opacity: 0.7;
        width: 20px;
        height: 20px;
        margin: 0 10px;
    }
    
    .safe-ship-pro-form-submit .spinner.active {
        visibility: visible;
    }
    
    #claim-submission-result {
        margin-top: 15px;
    }
    
    /* Modal styles */
    .safe-ship-pro-modal {
        display: none;
        position: fixed;
        z-index: 999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
    }
    
    .safe-ship-pro-modal-content {
        background-color: #fefefe;
        margin: 10% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 600px;
        position: relative;
        border-radius: 3px;
    }
    
    .safe-ship-pro-modal-close {
        color: #aaa;
        position: absolute;
        top: 10px;
        right: 20px;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .safe-ship-pro-modal-close:hover,
    .safe-ship-pro-modal-close:focus {
        color: black;
        text-decoration: none;
    }
    
    .safe-ship-pro-claim-detail-row {
        margin-bottom: 15px;
    }
    
    .safe-ship-pro-claim-detail-row h4 {
        margin: 0 0 5px 0;
    }
    
    /* Responsive adjustments */
    @media only screen and (max-width: 768px) {
        .safe-ship-pro-select-order-form {
            flex-direction: column;
            align-items: stretch;
            gap: 15px;
        }
        
        .safe-ship-pro-form-submit {
            flex-direction: column;
            align-items: stretch;
        }
        
        .safe-ship-pro-form-submit .button {
            width: 100%;
        }
        
        .safe-ship-pro-form-submit .button.cancel {
            margin-left: 0;
            margin-top: 10px;
        }
        
        .safe-ship-pro-form-submit .spinner {
            margin: 10px auto;
        }
        
        /* Make table responsive on mobile */
        .safe-ship-pro-claims-table th.claim-id,
        .safe-ship-pro-claims-table th.claim-date {
            display: none;
        }
        
        .safe-ship-pro-claims-table td.claim-id,
        .safe-ship-pro-claims-table td.claim-date {
            display: none;
        }
    }
</style>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Claim submission

        
        // View claim details modal
        $('.view-claim').on('click', function(e) {
            e.preventDefault();
            
            var claimId = $(this).data('claim-id');
            var modal = $('#safe-ship-pro-claim-modal');
            var contentContainer = $('#safe-ship-pro-claim-details-content');
            
            // Find claim details in the page
            var row = $(this).closest('tr');
            var claimDate = row.find('.claim-date').text().trim();
            var claimOrder = row.find('.claim-order').html();
            var claimType = row.find('.claim-type').text().trim();
            var claimStatus = row.find('.claim-status').html();
            
            // Generate content
            var content = '<h3>' + 'Claim Details #' + claimId + '</h3>';
            
            content += '<div class="safe-ship-pro-claim-detail-row">';
            content += '<h4>Date:</h4>';
            content += '<p>' + claimDate + '</p>';
            content += '</div>';
            
            content += '<div class="safe-ship-pro-claim-detail-row">';
            content += '<h4>Order:</h4>';
            content += '<p>' + claimOrder + '</p>';
            content += '</div>';
            
            content += '<div class="safe-ship-pro-claim-detail-row">';
            content += '<h4>Type:</h4>';
            content += '<p>' + claimType + '</p>';
            content += '</div>';
            
            content += '<div class="safe-ship-pro-claim-detail-row">';
            content += '<h4>Status:</h4>';
            content += '<p>' + claimStatus + '</p>';
            content += '</div>';
            
            content += '<div class="safe-ship-pro-claim-detail-row">';
            content += '<p>If you have any questions about your claim, please contact us.</p>';
            content += '</div>';
            
            // Set content and show modal
            contentContainer.html(content);
            modal.css('display', 'block');
        });
        
        // Close modal
        $('.safe-ship-pro-modal-close').on('click', function() {
            $('#safe-ship-pro-claim-modal').css('display', 'none');
        });
        
        // Close modal when clicking outside
        $(window).on('click', function(e) {
            if ($(e.target).is('.safe-ship-pro-modal')) {
                $('.safe-ship-pro-modal').css('display', 'none');
            }
        });
    });
</script>