<?php
/**
 * Admin claim notification email template.
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
                <h2 style="margin: 0; font-size: 24px;"><?php esc_html_e( 'New Shipping Protection Claim', 'safe-ship-pro' ); ?></h2>
            </div>
            
            <div style="padding: 20px;">
                <p style="margin-top: 0;"><?php esc_html_e( 'A new shipping protection claim has been submitted.', 'safe-ship-pro' ); ?></p>
                
                <div style="background-color: #f9f9f9; padding: 15px; border: 1px solid #e5e5e5; border-radius: 3px; margin-top: 20px;">
                    <h3 style="margin-top: 0;"><?php esc_html_e( 'Claim Details', 'safe-ship-pro' ); ?></h3>
                    
                    <p><strong><?php esc_html_e( 'Claim ID:', 'safe-ship-pro' ); ?></strong> <?php echo esc_html( $claim->id ); ?></p>
                    <p><strong><?php esc_html_e( 'Date:', 'safe-ship-pro' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $claim->date_created ) ) ); ?></p>
                    <p><strong><?php esc_html_e( 'Customer:', 'safe-ship-pro' ); ?></strong> <?php echo esc_html( $customer ); ?> (<?php echo esc_html( $user_email ); ?>)</p>
                    <p><strong><?php esc_html_e( 'Order:', 'safe-ship-pro' ); ?></strong> #<?php echo esc_html( $order->get_order_number() ); ?></p>
                    <p><strong><?php esc_html_e( 'Type:', 'safe-ship-pro' ); ?></strong> <?php echo esc_html( $claim_type ); ?></p>
                    
                    <p><strong><?php esc_html_e( 'Reason:', 'safe-ship-pro' ); ?></strong></p>
                    <div style="background-color: #ffffff; padding: 10px; border: 1px solid #e5e5e5; border-radius: 3px;">
                        <?php echo wp_kses_post( wpautop( $claim->claim_reason ) ); ?>
                    </div>
                    
                    <?php 
                    $attachments = ! empty( $claim->attachments ) ? maybe_unserialize( $claim->attachments ) : array();
                    if ( ! empty( $attachments ) ) : 
                    ?>
                    <p><strong><?php esc_html_e( 'Attachments:', 'safe-ship-pro' ); ?></strong></p>
                    <ul>
                        <?php foreach ( $attachments as $attachment_url ) : ?>
                            <li><a href="<?php echo esc_url( $attachment_url ); ?>"><?php echo esc_html( basename( $attachment_url ) ); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                
                <div style="margin-top: 30px; text-align: center;">
                    <a href="<?php echo esc_url( $admin_url ); ?>" style="display: inline-block; padding: 10px 15px; background-color: #557da1; color: #ffffff; text-decoration: none; border-radius: 3px;"><?php esc_html_e( 'Review This Claim', 'safe-ship-pro' ); ?></a>
                </div>
            </div>
            
            <div style="background-color: #f7f7f7; padding: 15px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 3px 3px;">
                <p style="margin: 0;"><?php echo esc_html( get_bloginfo( 'name' ) ); ?> - <?php esc_html_e( 'Shipping Protection Claims', 'safe-ship-pro' ); ?></p>
            </div>
        </div>
    </div>
</body>
</html>