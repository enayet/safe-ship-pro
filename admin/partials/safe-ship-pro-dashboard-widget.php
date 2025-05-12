<?php
/**
 * Dashboard widget template.
 *
 * @since      1.0.0
 */
?>

<div class="safe-ship-pro-dashboard-widget">
    <div class="safe-ship-pro-dashboard-stats">
        <div class="safe-ship-pro-dashboard-stat">
            <span class="safe-ship-pro-dashboard-value"><?php echo esc_html( $summary['protection_stats']['protected_orders'] ); ?></span>
            <span class="safe-ship-pro-dashboard-label"><?php esc_html_e( 'Protected Orders', 'safe-ship-pro' ); ?></span>
        </div>
        
        <div class="safe-ship-pro-dashboard-stat">
            <span class="safe-ship-pro-dashboard-value"><?php echo wc_price( $summary['protection_stats']['total_protection_amount'] ); ?></span>
            <span class="safe-ship-pro-dashboard-label"><?php esc_html_e( 'Protection Fees', 'safe-ship-pro' ); ?></span>
        </div>
        
        <div class="safe-ship-pro-dashboard-stat">
            <span class="safe-ship-pro-dashboard-value"><?php echo esc_html( $summary['claims_stats']['total_claims'] ); ?></span>
            <span class="safe-ship-pro-dashboard-label"><?php esc_html_e( 'Total Claims', 'safe-ship-pro' ); ?></span>
        </div>
        
        <div class="safe-ship-pro-dashboard-stat">
            <span class="safe-ship-pro-dashboard-value"><?php echo esc_html( $summary['claims_stats']['status_data']['pending'] ); ?></span>
            <span class="safe-ship-pro-dashboard-label"><?php esc_html_e( 'Pending Claims', 'safe-ship-pro' ); ?></span>
        </div>
    </div>
    
    <div class="safe-ship-pro-dashboard-footer">
        <p class="safe-ship-pro-date-range">
            <?php 
            printf(
                esc_html__( 'Data from %1$s to %2$s', 'safe-ship-pro' ),
                date_i18n( get_option( 'date_format' ), strtotime( $summary['date_range']['from'] ) ),
                date_i18n( get_option( 'date_format' ), strtotime( $summary['date_range']['to'] ) )
            ); 
            ?>
        </p>
        
        <p class="safe-ship-pro-dashboard-actions">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=safe-ship-pro-analytics' ) ); ?>" class="button button-small">
                <?php esc_html_e( 'View Full Analytics', 'safe-ship-pro' ); ?>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=safe-ship-pro-claims' ) ); ?>" class="button button-small">
                <?php esc_html_e( 'Manage Claims', 'safe-ship-pro' ); ?>
            </a>
        </p>
    </div>
</div>

<style>
    .safe-ship-pro-dashboard-widget {
        margin: -12px;
    }
    
    .safe-ship-pro-dashboard-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        grid-gap: 10px;
        margin-bottom: 15px;
    }
    
    .safe-ship-pro-dashboard-stat {
        background: #f9f9f9;
        padding: 10px;
        text-align: center;
        border: 1px solid #e5e5e5;
    }
    
    .safe-ship-pro-dashboard-value {
        display: block;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .safe-ship-pro-dashboard-label {
        color: #666;
        font-size: 12px;
    }
    
    .safe-ship-pro-dashboard-footer {
        border-top: 1px solid #eee;
        padding-top: 10px;
        font-size: 12px;
        color: #666;
    }
    
    .safe-ship-pro-dashboard-actions {
        margin-top: 10px;
        text-align: right;
    }
    
    .safe-ship-pro-dashboard-actions .button {
        margin-left: 5px;
    }
</style>