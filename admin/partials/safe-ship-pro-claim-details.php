<?php
/**
 * Claim details admin template.
 *
 * @since      1.0.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h2><?php printf(esc_html__('Claim #%s Details', 'safe-ship-pro'), esc_html($claim_id)); ?></h2>
    
    <p>
        <a href="<?php echo esc_url(admin_url('admin.php?page=safe-ship-pro-claims')); ?>" class="button">
            <?php esc_html_e('← Back to Claims List', 'safe-ship-pro'); ?>
        </a>
    </p>
    
    <div class="safe-ship-pro-claim-details">
        <div class="safe-ship-pro-claim-header">
            <div class="safe-ship-pro-claim-status">
                <h3><?php esc_html_e('Status', 'safe-ship-pro'); ?></h3>
                <?php
                $status_labels = array(
                    'pending' => __('Pending Review', 'safe-ship-pro'),
                    'processing' => __('Processing', 'safe-ship-pro'),
                    'approved' => __('Approved', 'safe-ship-pro'),
                    'denied' => __('Denied', 'safe-ship-pro'),
                    'completed' => __('Completed', 'safe-ship-pro'),
                );
                $status_label = isset($status_labels[$claim->claim_status]) ? $status_labels[$claim->claim_status] : $claim->claim_status;
                $status_class = 'status-' . sanitize_html_class($claim->claim_status);
                ?>
                <span class="claim-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span>
            </div>
            
            <div class="safe-ship-pro-claim-dates">
                <p><strong><?php esc_html_e('Created:', 'safe-ship-pro'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($claim->date_created))); ?></p>
                <p><strong><?php esc_html_e('Last Updated:', 'safe-ship-pro'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($claim->date_updated))); ?></p>
            </div>
        </div>
        
        <div class="safe-ship-pro-claim-info-grid">
            <div class="safe-ship-pro-claim-customer">
                <h3><?php esc_html_e('Customer Information', 'safe-ship-pro'); ?></h3>
                <p><strong><?php esc_html_e('Name:', 'safe-ship-pro'); ?></strong> <?php echo esc_html($customer_name); ?></p>
                <p><strong><?php esc_html_e('Email:', 'safe-ship-pro'); ?></strong> <?php echo esc_html($customer_email); ?></p>
            </div>
            
            <div class="safe-ship-pro-claim-order">
                <h3><?php esc_html_e('Order Information', 'safe-ship-pro'); ?></h3>
                <p>
                    <strong><?php esc_html_e('Order:', 'safe-ship-pro'); ?></strong> 
                    <a href="<?php echo esc_url(admin_url('post.php?post=' . $claim->order_id . '&action=edit')); ?>">
                        #<?php echo esc_html($order_number); ?>
                    </a>
                </p>
                <p><strong><?php esc_html_e('Order Total:', 'safe-ship-pro'); ?></strong> <?php echo wc_price($order_total); ?></p>
                <p><strong><?php esc_html_e('Protection Amount:', 'safe-ship-pro'); ?></strong> <?php echo wc_price($protection_amount); ?></p>
            </div>
        </div>
        
        <div class="safe-ship-pro-claim-content">
            <h3><?php esc_html_e('Claim Details', 'safe-ship-pro'); ?></h3>
            <p><strong><?php esc_html_e('Claim Type:', 'safe-ship-pro'); ?></strong> <?php echo esc_html($claim_type); ?></p>
            
            <div class="safe-ship-pro-claim-reason">
                <h4><?php esc_html_e('Reason:', 'safe-ship-pro'); ?></h4>
                <div class="safe-ship-pro-claim-reason-text">
                    <?php echo wpautop(esc_html($claim->claim_reason)); ?>
                </div>
            </div>
            
            <?php if (!empty($attachments)): ?>
            <div class="safe-ship-pro-claim-attachments">
                <h4><?php esc_html_e('Attachments:', 'safe-ship-pro'); ?></h4>
                <ul>
                    <?php foreach ($attachments as $url): ?>
                    <li>
                        <a href="<?php echo esc_url($url); ?>" target="_blank">
                            <?php echo esc_html(basename($url)); ?>
                        </a>
                        <?php 
                        $file_ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
                        if (in_array($file_ext, array('jpg', 'jpeg', 'png', 'gif'))): 
                        ?>
                        <div class="attachment-preview">
                            <img src="<?php echo esc_url($url); ?>" alt="<?php esc_attr_e('Attachment preview', 'safe-ship-pro'); ?>" style="max-width: 200px; height: auto;">
                        </div>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="safe-ship-pro-claim-management">
            <h3><?php esc_html_e('Claim Management', 'safe-ship-pro'); ?></h3>

            <form id="update-claim-form" method="post" action="">
                <?php wp_nonce_field('safe_ship_pro_update_claim', 'claim_nonce'); ?>
                <input type="hidden" name="claim_id" value="<?php echo esc_attr($claim_id); ?>">

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="status"><?php esc_html_e('Status:', 'safe-ship-pro'); ?></label></th>
                        <td>
                            <select name="status" id="status">
                                <option value="pending" <?php selected($claim->claim_status, 'pending'); ?>><?php esc_html_e('Pending Review', 'safe-ship-pro'); ?></option>
                                <option value="processing" <?php selected($claim->claim_status, 'processing'); ?>><?php esc_html_e('Processing', 'safe-ship-pro'); ?></option>
                                <option value="approved" <?php selected($claim->claim_status, 'approved'); ?>><?php esc_html_e('Approved', 'safe-ship-pro'); ?></option>
                                <option value="denied" <?php selected($claim->claim_status, 'denied'); ?>><?php esc_html_e('Denied', 'safe-ship-pro'); ?></option>
                                <option value="completed" <?php selected($claim->claim_status, 'completed'); ?>><?php esc_html_e('Completed', 'safe-ship-pro'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="admin_notes"><?php esc_html_e('Admin Notes:', 'safe-ship-pro'); ?></label></th>
                        <td>
                            <textarea name="admin_notes" id="admin_notes" rows="5" cols="50"><?php echo esc_textarea($claim->admin_notes); ?></textarea>
                            <p class="description"><?php esc_html_e('These notes are only visible to administrators.', 'safe-ship-pro'); ?></p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="update_safe_ship_claim" id="update_claim" class="button button-primary" value="<?php esc_attr_e('Update Claim', 'safe-ship-pro'); ?>">
                </p>
            </form>
        </div>
        
        
    </div>
</div>

<style>
    .safe-ship-pro-claim-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    
    .safe-ship-pro-claim-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-gap: 20px;
        margin-bottom: 20px;
    }
    
    .safe-ship-pro-claim-customer,
    .safe-ship-pro-claim-order {
        background: #f9f9f9;
        padding: 15px;
        border: 1px solid #e5e5e5;
    }
    
    .safe-ship-pro-claim-content,
    .safe-ship-pro-claim-management {
        background: #f9f9f9;
        padding: 15px;
        border: 1px solid #e5e5e5;
        margin-bottom: 20px;
    }
    
    .safe-ship-pro-claim-reason-text {
        background: #fff;
        padding: 10px;
        border: 1px solid #e5e5e5;
    }
    
    .claim-status {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 3px;
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
    
    .safe-ship-pro-claim-attachments ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    
    .safe-ship-pro-claim-attachments li {
        margin-bottom: 10px;
    }
    
    .attachment-preview {
        margin-top: 5px;
    }
</style>