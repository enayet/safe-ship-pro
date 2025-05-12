<?php
/**
 * Claims management admin page template.
 *
 * @since      1.0.0
 */
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Shipping Protection Claims', 'safe-ship-pro' ); ?></h1>
    
    <?php
    // Handle single claim view
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' && isset( $_GET['claim_id'] ) ) {
        $claim_id = intval( $_GET['claim_id'] );
        $this->display_claim_details( $claim_id );
        return;
    }
    
    // Handle creating new claim
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'new' && isset( $_GET['order_id'] ) ) {
        $order_id = intval( $_GET['order_id'] );
        $this->display_new_claim_form( $order_id );
        return;
    }
    ?>
    
    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get">
                <input type="hidden" name="page" value="safe-ship-pro-claims" />
                <select name="status">
                    <option value=""><?php esc_html_e( 'All Statuses', 'safe-ship-pro' ); ?></option>
                    <option value="pending" <?php selected( isset( $_GET['status'] ) && $_GET['status'] === 'pending' ); ?>><?php esc_html_e( 'Pending Review', 'safe-ship-pro' ); ?></option>
                    <option value="processing" <?php selected( isset( $_GET['status'] ) && $_GET['status'] === 'processing' ); ?>><?php esc_html_e( 'Processing', 'safe-ship-pro' ); ?></option>
                    <option value="approved" <?php selected( isset( $_GET['status'] ) && $_GET['status'] === 'approved' ); ?>><?php esc_html_e( 'Approved', 'safe-ship-pro' ); ?></option>
                    <option value="denied" <?php selected( isset( $_GET['status'] ) && $_GET['status'] === 'denied' ); ?>><?php esc_html_e( 'Denied', 'safe-ship-pro' ); ?></option>
                    <option value="completed" <?php selected( isset( $_GET['status'] ) && $_GET['status'] === 'completed' ); ?>><?php esc_html_e( 'Completed', 'safe-ship-pro' ); ?></option>
                </select>
                <input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'safe-ship-pro' ); ?>" />
            </form>
        </div>
        
        <?php
        // Pagination
        $page_links = paginate_links( array(
            'base' => add_query_arg( 'paged', '%#%' ),
            'format' => '',
            'prev_text' => __( '&laquo;', 'safe-ship-pro' ),
            'next_text' => __( '&raquo;', 'safe-ship-pro' ),
            'total' => $total_pages,
            'current' => $current_page
        ) );
        
        if ( $page_links ) {
            echo '<div class="tablenav-pages">' . $page_links . '</div>';
        }
        ?>
        <br class="clear" />
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-claim-id"><?php esc_html_e( 'Claim ID', 'safe-ship-pro' ); ?></th>
                <th scope="col" class="manage-column column-date"><?php esc_html_e( 'Date', 'safe-ship-pro' ); ?></th>
                <th scope="col" class="manage-column column-customer"><?php esc_html_e( 'Customer', 'safe-ship-pro' ); ?></th>
                <th scope="col" class="manage-column column-order"><?php esc_html_e( 'Order', 'safe-ship-pro' ); ?></th>
                <th scope="col" class="manage-column column-type"><?php esc_html_e( 'Type', 'safe-ship-pro' ); ?></th>
                <th scope="col" class="manage-column column-status"><?php esc_html_e( 'Status', 'safe-ship-pro' ); ?></th>
                <th scope="col" class="manage-column column-actions"><?php esc_html_e( 'Actions', 'safe-ship-pro' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ( ! empty( $claims ) ) {
                foreach ( $claims as $claim ) {
                    // Get user info
                    $user_info = get_userdata( $claim->user_id );
                    $customer_name = $user_info ? $user_info->display_name : __( 'Unknown', 'safe-ship-pro' );
                    
                    // Get order info
                    $order = wc_get_order( $claim->order_id );
                    $order_number = $order ? $order->get_order_number() : $claim->order_id;
                    
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
                    
                    // Row output
                    ?>
                    <tr>
                        <td class="column-claim-id"><?php echo esc_html( $claim->id ); ?></td>
                        <td class="column-date">
                            <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $claim->date_created ) ) ); ?>
                        </td>
                        <td class="column-customer"><?php echo esc_html( $customer_name ); ?></td>
                        <td class="column-order">
                            <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $claim->order_id . '&action=edit' ) ); ?>" target="_blank">
                                #<?php echo esc_html( $order_number ); ?>
                            </a>
                        </td>
                        <td class="column-type"><?php echo esc_html( $claim_type ); ?></td>
                        <td class="column-status">
                            <span class="claim-status <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span>
                        </td>
                        <td class="column-actions">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=safe-ship-pro-claims&action=edit&claim_id=' . $claim->id ) ); ?>" class="button button-small">
                                <?php esc_html_e( 'View / Edit', 'safe-ship-pro' ); ?>
                            </a>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="7"><?php esc_html_e( 'No claims found.', 'safe-ship-pro' ); ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    
    <div class="tablenav bottom">
        <?php
        if ( $page_links ) {
            echo '<div class="tablenav-pages">' . $page_links . '</div>';
        }
        ?>
    </div>
</div>

<?php
/**
 * Display claim details for editing.
 *
 * @param int $claim_id Claim ID.
 */
function display_claim_details( $claim_id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'safe_ship_pro_claims';
    
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
    $protection_amount = $order ? get_post_meta( $claim->order_id, '_safe_ship_pro_protection_amount', true ) : 0;
    
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
    ?>
    
    <div class="notice notice-info inline">
        <p><?php esc_html_e( 'Viewing claim details. Update the status and add notes as needed.', 'safe-ship-pro' ); ?></p>
    </div>
    
    <p>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=safe-ship-pro-claims' ) ); ?>" class="button">
            <?php esc_html_e( '← Back to Claims', 'safe-ship-pro' ); ?>
        </a>
    </p>
    
    <div class="safe-ship-pro-claim-details">
        <div class="safe-ship-pro-claim-header">
            <h2><?php printf( esc_html__( 'Claim #%s', 'safe-ship-pro' ), esc_html( $claim_id ) ); ?></h2>
            <div class="safe-ship-pro-claim-dates">
                <span><?php esc_html_e( 'Created:', 'safe-ship-pro' ); ?> <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $claim->date_created ) ) ); ?></span>
                <span><?php esc_html_e( 'Last Updated:', 'safe-ship-pro' ); ?> <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $claim->date_updated ) ) ); ?></span>
            </div>
        </div>
        
        <div class="safe-ship-pro-claim-info">
            <div class="safe-ship-pro-claim-customer">
                <h3><?php esc_html_e( 'Customer Information', 'safe-ship-pro' ); ?></h3>
                <p><strong><?php esc_html_e( 'Name:', 'safe-ship-pro' ); ?></strong> <?php echo esc_html( $customer_name ); ?></p>
                <p><strong><?php esc_html_e( 'Email:', 'safe-ship-pro' ); ?></strong> <?php echo esc_html( $customer_email ); ?></p>
                <p><strong><?php esc_html_e( 'User ID:', 'safe-ship-pro' ); ?></strong> <?php echo esc_html( $claim->user_id ); ?></p>
            </div>
            
            <div class="safe-ship-pro-claim-order">
                <h3><?php esc_html_e( 'Order Information', 'safe-ship-pro' ); ?></h3>
                <p>
                    <strong><?php esc_html_e( 'Order:', 'safe-ship-pro' ); ?></strong> 
                    <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $claim->order_id . '&action=edit' ) ); ?>" target="_blank">
                        #<?php echo esc_html( $order_number ); ?>
                    </a>
                </p>
                <p><strong><?php esc_html_e( 'Order Total:', 'safe-ship-pro' ); ?></strong> <?php echo wc_price( $order_total ); ?></p>
                <p><strong><?php esc_html_e( 'Protection Amount:', 'safe-ship-pro' ); ?></strong> <?php echo wc_price( $protection_amount ); ?></p>
            </div>
        </div>
        
        <div class="safe-ship-pro-claim-details-content">
            <h3><?php esc_html_e( 'Claim Details', 'safe-ship-pro' ); ?></h3>
            <p><strong><?php esc_html_e( 'Claim Type:', 'safe-ship-pro' ); ?></strong> <?php echo esc_html( $claim_type ); ?></p>
            
            <div class="safe-ship-pro-claim-reason">
                <strong><?php esc_html_e( 'Reason:', 'safe-ship-pro' ); ?></strong>
                <div class="safe-ship-pro-claim-reason-content">
                    <?php echo wp_kses_post( wpautop( $claim->claim_reason ) ); ?>
                </div>
            </div>
            
            <?php if ( ! empty( $attachments ) ) : ?>
                <div class="safe-ship-pro-claim-attachments">
                    <h3><?php esc_html_e( 'Attachments', 'safe-ship-pro' ); ?></h3>
                    <ul>
                        <?php foreach ( $attachments as $attachment_url ) : ?>
                            <li>
                                <a href="<?php echo esc_url( $attachment_url ); ?>" target="_blank">
                                    <?php echo esc_html( basename( $attachment_url ) ); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="safe-ship-pro-claim-management">
            <h3><?php esc_html_e( 'Claim Management', 'safe-ship-pro' ); ?></h3>
            
            <form id="claim-update-form">
                <input type="hidden" name="claim_id" value="<?php echo esc_attr( $claim_id ); ?>" />
                <input type="hidden" name="action" value="safe_ship_pro_update_claim" />
                <input type="hidden" name="security" value="<?php echo esc_attr( wp_create_nonce( 'safe_ship_pro_update_claim' ) ); ?>" />
                
                <div class="safe-ship-pro-claim-status">
                    <label for="claim_status"><strong><?php esc_html_e( 'Status:', 'safe-ship-pro' ); ?></strong></label>
                    <select name="status" id="claim_status">
                        <option value="pending" <?php selected( $claim->claim_status, 'pending' ); ?>><?php esc_html_e( 'Pending Review', 'safe-ship-pro' ); ?></option>
                        <option value="processing" <?php selected( $claim->claim_status, 'processing' ); ?>><?php esc_html_e( 'Processing', 'safe-ship-pro' ); ?></option>
                        <option value="approved" <?php selected( $claim->claim_status, 'approved' ); ?>><?php esc_html_e( 'Approved', 'safe-ship-pro' ); ?></option>
                        <option value="denied" <?php selected( $claim->claim_status, 'denied' ); ?>><?php esc_html_e( 'Denied', 'safe-ship-pro' ); ?></option>
                        <option value="completed" <?php selected( $claim->claim_status, 'completed' ); ?>><?php esc_html_e( 'Completed', 'safe-ship-pro' ); ?></option>
                    </select>
                </div>
                
                <div class="safe-ship-pro-claim-notes">
                    <label for="admin_notes"><strong><?php esc_html_e( 'Admin Notes:', 'safe-ship-pro' ); ?></strong></label>
                    <textarea name="admin_notes" id="admin_notes" rows="5" class="large-text"><?php echo esc_textarea( $claim->admin_notes ); ?></textarea>
                    <p class="description"><?php esc_html_e( 'Internal notes for administrators only. Not visible to customers.', 'safe-ship-pro' ); ?></p>
                </div>
                
                <div class="safe-ship-pro-claim-actions">
                    <button type="submit" class="button button-primary"><?php esc_html_e( 'Update Claim', 'safe-ship-pro' ); ?></button>
                    <span class="spinner"></span>
                    <div id="update-message"></div>
                </div>
            </form>
        </div>
    </div>
    
    <style>
        .safe-ship-pro-claim-details {
            margin-top: 20px;
        }
        .safe-ship-pro-claim-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .safe-ship-pro-claim-dates {
            text-align: right;
        }
        .safe-ship-pro-claim-dates span {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }
        .safe-ship-pro-claim-info {
            display: flex;
            margin-bottom: 20px;
        }
        .safe-ship-pro-claim-customer,
        .safe-ship-pro-claim-order {
            flex: 1;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #e5e5e5;
            margin-right: 20px;
        }
        .safe-ship-pro-claim-order {
            margin-right: 0;
        }
        .safe-ship-pro-claim-details-content,
        .safe-ship-pro-claim-management {
            background: #f9f9f9;
            border: 1px solid #e5e5e5;
            padding: 15px;
            margin-bottom: 20px;
        }
        .safe-ship-pro-claim-reason {
            margin-top: 10px;
        }
        .safe-ship-pro-claim-reason-content {
            margin-top: 5px;
            background: #fff;
            padding: 10px;
            border: 1px solid #e5e5e5;
        }
        .safe-ship-pro-claim-attachments ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .safe-ship-pro-claim-attachments li {
            padding: 5px 0;
        }
        .safe-ship-pro-claim-status {
            margin-bottom: 15px;
        }
        .safe-ship-pro-claim-notes {
            margin-bottom: 15px;
        }
        .safe-ship-pro-claim-actions {
            display: flex;
            align-items: center;
        }
        .safe-ship-pro-claim-actions .spinner {
            float: none;
            margin-left: 10px;
        }
        #update-message {
            margin-left: 10px;
        }
        .claim-status {
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
    </style>
    <?php
}