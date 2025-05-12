<?php
/**
 * Claim status update email template.
 *
 * @since      1.0.0
 */
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
    <title><?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
</head>
<body style="background-color: #f7f7f7; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.5; margin: 0; padding: 0;">
    <div style="background-color: #f7f7f7; padding: 20px;">
        <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e5e5e5; border-radius: 3px;">
            <div style="background-color: #557da1; padding: 20px; color: #ffffff; text-align: center; border-radius: 3px 3px 0 0;">
                <h2 style="margin: 0; font-size: 24px;"><?php esc_html_e( 'Shipping Protection Claim Update', 'safe-ship-pro' ); ?></h2>
            </div>
            
            <div style="padding: 20px;">
                <p style="margin-top: 0;">
                    <?php
                    printf(
                        esc_html__( 'The status of your shipping protection claim for Order #%s has been updated.', 'safe-ship-pro' ),
                        esc_html( $order->get_order_number() )
                    );
                    ?>
                </p>
                
                <div style="background-color: #f9f9f9; padding: 15px; border: 1px solid #e5e5e5; border-radius: 3px; margin-top: 20px;">
                    <h3 style="margin-top: 0;"><?php esc_html_e( 'Claim Details', 'safe-ship-pro' ); ?></h3>
                    
                    <p><strong><?php esc_html_e( 'Claim ID:', 'safe-ship-pro' ); ?></strong> <?php echo esc_html( $claim->id ); ?></p>
                    <p><strong><?php esc_html_e( 'Order:', 'safe-ship-pro' ); ?></strong> #<?php echo esc_html( $order->get_order_number() ); ?></p>
                    <p><strong><?php esc_html_e( 'Type:', 'safe-ship-pro' ); ?></strong> <?php echo esc_html( $claim_type ); ?></p>
                    
                    <div style="margin-top: 15px; padding: 10px; background-color: #ffffff; border: 1px solid #e5e5e5; border-radius: 3px;">
                        <h4 style="margin-top: 0; margin-bottom: 5px;"><?php esc_html_e( 'Status Update:', 'safe-ship-pro' ); ?></h4>
                        <p style="margin: 0; font-weight: bold; 
                            <?php 
                            $color = '';
                            switch ( $status ) {
                                case 'pending':
                                    $color = 'color: #94660c;';
                                    break;
                                case 'processing':
                                    $color = 'color: #5b841b;';
                                    break;
                                case 'approved':
                                    $color = 'color: #2e4453;';
                                    break;
                                case 'denied':
                                    $color = 'color: #761919;';
                                    break;
                                case 'completed':
                                    $color = 'color: #5b841b;';
                                    break;
                            }
                            echo $color;
                            ?>">
                            <?php echo esc_html( $status_label ); ?>
                        </p>
                    </div>
                </div>
                
                <?php if ( $status === 'approved' || $status === 'completed' ) : ?>
                    <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;"><?php esc_html_e( 'Your Claim Has Been Approved', 'safe-ship-pro' ); ?></h3>
                    
                    <p><?php esc_html_e( 'Good news! Your shipping protection claim has been approved. Our team will process your claim according to our shipping protection policy.', 'safe-ship-pro' ); ?></p>
                    
                    <p><?php esc_html_e( 'If there are any additional steps required, we will contact you directly with further instructions.', 'safe-ship-pro' ); ?></p>
                <?php elseif ( $status === 'denied' ) : ?>
                    <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;"><?php esc_html_e( 'Claim Decision', 'safe-ship-pro' ); ?></h3>
                    
                    <p><?php esc_html_e( 'After careful review, we were unable to approve your shipping protection claim.', 'safe-ship-pro' ); ?></p>
                    
                    <p><?php esc_html_e( 'If you believe this decision was made in error or if you have additional information to support your claim, please contact our customer service team.', 'safe-ship-pro' ); ?></p>
                <?php elseif ( $status === 'processing' ) : ?>
                    <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;"><?php esc_html_e( 'Claim In Progress', 'safe-ship-pro' ); ?></h3>
                    
                    <p><?php esc_html_e( 'Your claim is now being processed by our team. We are reviewing the details and may contact you if additional information is needed.', 'safe-ship-pro' ); ?></p>
                    
                    <p><?php esc_html_e( 'You will receive another update when a decision has been made.', 'safe-ship-pro' ); ?></p>
                <?php endif; ?>
                
                <div style="margin-top: 30px; text-align: center;">
                    <a href="<?php echo esc_url( $account_url ); ?>" style="display: inline-block; padding: 10px 15px; background-color: #557da1; color: #ffffff; text-decoration: none; border-radius: 3px;"><?php esc_html_e( 'View Claim Details', 'safe-ship-pro' ); ?></a>
                </div>
                
                <p style="margin-top: 30px; font-size: 12px; color: #666; text-align: center;">
                    <?php esc_html_e( 'If you have any questions, please contact us.', 'safe-ship-pro' ); ?>
                </p>
            </div>
            
            <div style="background-color: #f7f7f7; padding: 15px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 3px 3px;">
                <p style="margin: 0;"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
            </div>
        </div>
    </div>
</body>
</html>