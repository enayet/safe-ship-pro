<?php
/**
 * Customer claim confirmation email template.
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
                <h2 style="margin: 0; font-size: 24px;"><?php esc_html_e( 'Shipping Protection Claim Received', 'safe-ship-pro' ); ?></h2>
            </div>
            
            <div style="padding: 20px;">
                <p style="margin-top: 0;"><?php esc_html_e( 'Thank you for submitting your shipping protection claim.', 'safe-ship-pro' ); ?></p>
                
                <p><?php esc_html_e( 'We have received your claim and will review it shortly. You will be notified of any updates regarding your claim.', 'safe-ship-pro' ); ?></p>
                
                <div style="background-color: #f9f9f9; padding: 15px; border: 1px solid #e5e5e5; border-radius: 3px; margin-top: 20px;">
                    <h3 style="margin-top: 0;"><?php esc_html_e( 'Claim Details', 'safe-ship-pro' ); ?></h3>
                    
                    <p><strong><?php esc_html_e( 'Claim ID:', 'safe-ship-pro' ); ?></strong> <?php echo esc_html( $claim->id ); ?></p>
                    <p><strong><?php esc_html_e( 'Date:', 'safe-ship-pro' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $claim->date_created ) ) ); ?></p>
                    <p><strong><?php esc_html_e( 'Order:', 'safe-ship-pro' ); ?></strong> #<?php echo esc_html( $order->get_order_number() ); ?></p>
                    <p><strong><?php esc_html_e( 'Type:', 'safe-ship-pro' ); ?></strong> <?php echo esc_html( $claim_type ); ?></p>
                    <p><strong><?php esc_html_e( 'Status:', 'safe-ship-pro' ); ?></strong> <?php esc_html_e( 'Pending Review', 'safe-ship-pro' ); ?></p>
                </div>
                
                <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;"><?php esc_html_e( 'What Happens Next?', 'safe-ship-pro' ); ?></h3>
                
                <p><?php esc_html_e( 'Our team will review your claim and may contact you if additional information is needed. You will receive email notifications when there are updates to your claim status.', 'safe-ship-pro' ); ?></p>
                
                <p><?php esc_html_e( 'You can also check the status of your claim at any time by visiting your account dashboard.', 'safe-ship-pro' ); ?></p>
                
                <div style="margin-top: 30px; text-align: center;">
                    <a href="<?php echo esc_url( $account_url ); ?>" style="display: inline-block; padding: 10px 15px; background-color: #557da1; color: #ffffff; text-decoration: none; border-radius: 3px;"><?php esc_html_e( 'View Your Claims', 'safe-ship-pro' ); ?></a>
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