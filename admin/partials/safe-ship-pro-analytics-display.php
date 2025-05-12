<?php
/**
 * Analytics admin page template.
 *
 * @since      1.0.0
 */
?>

<div class="wrap safe-ship-pro-analytics">
    <h1><?php esc_html_e( 'Safe Ship Pro Analytics', 'safe-ship-pro' ); ?></h1>
    
    <div class="safe-ship-pro-date-filter">
        <form method="get">
            <input type="hidden" name="page" value="safe-ship-pro-analytics" />
            
            <label for="date_from"><?php esc_html_e( 'From:', 'safe-ship-pro' ); ?></label>
            <input type="date" id="date_from" name="date_from" value="<?php echo esc_attr( $date_from ); ?>" />
            
            <label for="date_to"><?php esc_html_e( 'To:', 'safe-ship-pro' ); ?></label>
            <input type="date" id="date_to" name="date_to" value="<?php echo esc_attr( $date_to ); ?>" />
            
            <input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'safe-ship-pro' ); ?>" />
        </form>
    </div>
    
    <div class="safe-ship-pro-overview">
        <div class="safe-ship-pro-stats-card">
            <h3><?php esc_html_e( 'Protection Overview', 'safe-ship-pro' ); ?></h3>
            <div class="safe-ship-pro-stat-grid">
                <div class="safe-ship-pro-stat">
                    <span class="safe-ship-pro-stat-value"><?php echo esc_html( $protection_stats['protected_orders'] ); ?></span>
                    <span class="safe-ship-pro-stat-label"><?php esc_html_e( 'Protected Orders', 'safe-ship-pro' ); ?></span>
                </div>
                <div class="safe-ship-pro-stat">
                    <span class="safe-ship-pro-stat-value"><?php echo esc_html( $protection_stats['protection_rate'] ); ?>%</span>
                    <span class="safe-ship-pro-stat-label"><?php esc_html_e( 'Protection Rate', 'safe-ship-pro' ); ?></span>
                </div>
                <div class="safe-ship-pro-stat">
                    <span class="safe-ship-pro-stat-value"><?php echo wc_price( $protection_stats['total_protection_amount'] ); ?></span>
                    <span class="safe-ship-pro-stat-label"><?php esc_html_e( 'Total Protection Fees', 'safe-ship-pro' ); ?></span>
                </div>
                <div class="safe-ship-pro-stat">
                    <span class="safe-ship-pro-stat-value"><?php echo wc_price( $protection_stats['average_protection_amount'] ); ?></span>
                    <span class="safe-ship-pro-stat-label"><?php esc_html_e( 'Avg. Protection Fee', 'safe-ship-pro' ); ?></span>
                </div>
            </div>
        </div>
        
        <div class="safe-ship-pro-stats-card">
            <h3><?php esc_html_e( 'Claims Overview', 'safe-ship-pro' ); ?></h3>
            <div class="safe-ship-pro-stat-grid">
                <div class="safe-ship-pro-stat">
                    <span class="safe-ship-pro-stat-value"><?php echo esc_html( $claims_stats['total_claims'] ); ?></span>
                    <span class="safe-ship-pro-stat-label"><?php esc_html_e( 'Total Claims', 'safe-ship-pro' ); ?></span>
                </div>
                <div class="safe-ship-pro-stat">
                    <span class="safe-ship-pro-stat-value"><?php echo esc_html( $claims_stats['approval_rate'] ); ?>%</span>
                    <span class="safe-ship-pro-stat-label"><?php esc_html_e( 'Approval Rate', 'safe-ship-pro' ); ?></span>
                </div>
                <div class="safe-ship-pro-stat">
                    <span class="safe-ship-pro-stat-value"><?php echo esc_html( $claims_stats['status_data']['approved'] + $claims_stats['status_data']['completed'] ); ?></span>
                    <span class="safe-ship-pro-stat-label"><?php esc_html_e( 'Approved Claims', 'safe-ship-pro' ); ?></span>
                </div>
                <div class="safe-ship-pro-stat">
                    <span class="safe-ship-pro-stat-value"><?php echo esc_html( $claims_stats['status_data']['denied'] ); ?></span>
                    <span class="safe-ship-pro-stat-label"><?php esc_html_e( 'Denied Claims', 'safe-ship-pro' ); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="safe-ship-pro-charts">
        <div class="safe-ship-pro-chart-card">
            <h3><?php esc_html_e( 'Daily Protection Data', 'safe-ship-pro' ); ?></h3>
            <div class="safe-ship-pro-chart-container">
                <canvas id="daily-protection-chart"></canvas>
            </div>
        </div>
        
        <div class="safe-ship-pro-chart-card">
            <h3><?php esc_html_e( 'Claims by Type', 'safe-ship-pro' ); ?></h3>
            <div class="safe-ship-pro-chart-container">
                <canvas id="claims-type-chart"></canvas>
            </div>
        </div>
        
        <div class="safe-ship-pro-chart-card">
            <h3><?php esc_html_e( 'Claims by Status', 'safe-ship-pro' ); ?></h3>
            <div class="safe-ship-pro-chart-container">
                <canvas id="claims-status-chart"></canvas>
            </div>
        </div>
        
        <div class="safe-ship-pro-chart-card">
            <h3><?php esc_html_e( 'Daily Claims Filed', 'safe-ship-pro' ); ?></h3>
            <div class="safe-ship-pro-chart-container">
                <canvas id="daily-claims-chart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="safe-ship-pro-data-table">
        <h3><?php esc_html_e( 'Daily Data', 'safe-ship-pro' ); ?></h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Date', 'safe-ship-pro' ); ?></th>
                    <th><?php esc_html_e( 'Protected Orders', 'safe-ship-pro' ); ?></th>
                    <th><?php esc_html_e( 'Protection Amount', 'safe-ship-pro' ); ?></th>
                    <th><?php esc_html_e( 'Claims Filed', 'safe-ship-pro' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $daily_data as $day ) : ?>
                <tr>
                    <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $day['date'] ) ) ); ?></td>
                    <td><?php echo esc_html( $day['protection_orders'] ); ?></td>
                    <td><?php echo wc_price( $day['protection_amount'] ); ?></td>
                    <td><?php echo esc_html( $day['claims'] ); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .safe-ship-pro-date-filter {
        margin: 20px 0;
        padding: 15px;
        background: #f9f9f9;
        border: 1px solid #e5e5e5;
    }
    
    .safe-ship-pro-date-filter label {
        margin-right: 5px;
    }
    
    .safe-ship-pro-date-filter input[type="date"] {
        margin-right: 15px;
    }
    
    .safe-ship-pro-overview {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    
    .safe-ship-pro-stats-card {
        width: 48%;
        padding: 15px;
        background: #f9f9f9;
        border: 1px solid #e5e5e5;
    }
    
    .safe-ship-pro-stat-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        grid-gap: 15px;
        margin-top: 15px;
    }
    
    .safe-ship-pro-stat {
        background: #fff;
        padding: 15px;
        border: 1px solid #e5e5e5;
        text-align: center;
    }
    
    .safe-ship-pro-stat-value {
        display: block;
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .safe-ship-pro-stat-label {
        color: #666;
    }
    
    .safe-ship-pro-charts {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        grid-gap: 20px;
        margin-bottom: 20px;
    }
    
    .safe-ship-pro-chart-card {
        padding: 15px;
        background: #f9f9f9;
        border: 1px solid #e5e5e5;
    }
    
    .safe-ship-pro-chart-container {
        background: #fff;
        padding: 15px;
        border: 1px solid #e5e5e5;
        height: 300px;
    }
    
    .safe-ship-pro-data-table {
        margin-top: 30px;
    }
</style>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Chart colors
        const colors = {
            primary: '#2271b1',
            secondary: '#72aee6',
            tertiary: '#135e96',
            quaternary: '#8c8f94',
            success: '#00a32a',
            warning: '#dba617',
            danger: '#d63638',
            light: '#f0f0f1',
            dark: '#1d2327'
        };
        
        // Daily protection chart
        const dailyProtectionCtx = document.getElementById('daily-protection-chart').getContext('2d');
        new Chart(dailyProtectionCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode( array_column( $daily_data, 'formatted_date' ) ); ?>,
                datasets: [
                    {
                        label: '<?php esc_html_e( 'Protected Orders', 'safe-ship-pro' ); ?>',
                        data: <?php echo json_encode( array_column( $daily_data, 'protection_orders' ) ); ?>,
                        backgroundColor: colors.secondary,
                        borderColor: colors.primary,
                        borderWidth: 2,
                        tension: 0.1,
                        yAxisID: 'y1'
                    },
                    {
                        label: '<?php esc_html_e( 'Protection Amount', 'safe-ship-pro' ); ?>',
                        data: <?php echo json_encode( array_column( $daily_data, 'protection_amount' ) ); ?>,
                        backgroundColor: colors.tertiary,
                        borderColor: colors.dark,
                        borderWidth: 2,
                        tension: 0.1,
                        yAxisID: 'y2'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: '<?php esc_html_e( 'Orders', 'safe-ship-pro' ); ?>'
                        }
                    },
                    y2: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: '<?php esc_html_e( 'Amount', 'safe-ship-pro' ); ?>'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
        
        // Claims by type chart
        const claimsTypeCtx = document.getElementById('claims-type-chart').getContext('2d');
        new Chart(claimsTypeCtx, {
            type: 'pie',
            data: {
                labels: [
                    '<?php esc_html_e( 'Damaged', 'safe-ship-pro' ); ?>',
                    '<?php esc_html_e( 'Lost', 'safe-ship-pro' ); ?>',
                    '<?php esc_html_e( 'Stolen', 'safe-ship-pro' ); ?>',
                    '<?php esc_html_e( 'Delayed', 'safe-ship-pro' ); ?>',
                    '<?php esc_html_e( 'Other', 'safe-ship-pro' ); ?>'
                ],
                datasets: [
                    {
                        data: [
                            <?php echo $claims_stats['type_data']['damaged']; ?>,
                            <?php echo $claims_stats['type_data']['lost']; ?>,
                            <?php echo $claims_stats['type_data']['stolen']; ?>,
                            <?php echo $claims_stats['type_data']['delayed']; ?>,
                            <?php echo $claims_stats['type_data']['other']; ?>
                        ],
                        backgroundColor: [
                            colors.primary,
                            colors.secondary,
                            colors.tertiary,
                            colors.success,
                            colors.quaternary
                        ]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        
        // Claims by status chart
        const claimsStatusCtx = document.getElementById('claims-status-chart').getContext('2d');
        new Chart(claimsStatusCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    '<?php esc_html_e( 'Pending', 'safe-ship-pro' ); ?>',
                    '<?php esc_html_e( 'Processing', 'safe-ship-pro' ); ?>',
                    '<?php esc_html_e( 'Approved', 'safe-ship-pro' ); ?>',
                    '<?php esc_html_e( 'Denied', 'safe-ship-pro' ); ?>',
                    '<?php esc_html_e( 'Completed', 'safe-ship-pro' ); ?>'
                ],
                datasets: [
                    {
                        data: [
                            <?php echo $claims_stats['status_data']['pending']; ?>,
                            <?php echo $claims_stats['status_data']['processing']; ?>,
                            <?php echo $claims_stats['status_data']['approved']; ?>,
                            <?php echo $claims_stats['status_data']['denied']; ?>,
                            <?php echo $claims_stats['status_data']['completed']; ?>
                        ],
                        backgroundColor: [
                            colors.warning,
                            colors.secondary,
                            colors.success,
                            colors.danger,
                            colors.primary
                        ]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        
        // Daily claims chart
        const dailyClaimsCtx = document.getElementById('daily-claims-chart').getContext('2d');
        new Chart(dailyClaimsCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode( array_column( $daily_data, 'formatted_date' ) ); ?>,
                datasets: [
                    {
                        label: '<?php esc_html_e( 'Claims Filed', 'safe-ship-pro' ); ?>',
                        data: <?php echo json_encode( array_column( $daily_data, 'claims' ) ); ?>,
                        backgroundColor: colors.tertiary,
                        borderColor: colors.primary,
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: '<?php esc_html_e( 'Claims', 'safe-ship-pro' ); ?>'
                        }
                    }
                }
            }
        });
    });
</script>