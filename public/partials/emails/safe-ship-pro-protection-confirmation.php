<?php
/**
 * Protection confirmation email template.
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
                <h2 style="margin: 0; font-size: 24px;"><?php esc_html_e( 'Shipping Protection Confirmation', 'safe-ship-pro' ); ?></h2>
            </div>
            
            <div style="padding: 20px;">
                <p style="margin-top: 0;"><?php esc_html_e( 'Thank you for adding shipping protection to your order!', 'safe-ship-pro' ); ?></p>
                
                <p><?php printf( esc_html__( 'Your order (#%s) now includes shipping protection for %s.', 'safe-ship-pro' ), $order->get_order_number(), wc_price( $protection_amount ) ); ?></p>
                
                <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;"><?php esc_html_e( 'What This Means', 'safe-ship-pro' ); ?></h3>
                
                <p><?php esc_html_e( 'Your order is now protected against loss, damage, or theft during shipping. If you encounter any issues with your delivery, you can file a claim through your account.', 'safe-ship-pro' ); ?></p>
                
                <?php if ( ! empty( $protection_policy ) ) : ?>
                <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;"><?php esc_html_e( 'Protection Policy', 'safe-ship-pro' ); ?></h3>
                
                <div style="background-color: #f9f9f9; padding: 15px; border: 1px solid #e5e5e5; border-radius: 3px;">
                    <?php echo wp_kses_post( wpautop( $protection_policy ) ); ?>
                </div>
                <?php endif; ?>
                
                <div style="margin-top: 30px; text-align: center;">
                    <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" style="display: inline-block; padding: 10px 15px; background-color: #557da1; color: #ffffff; text-decoration: none; border-radius: 3px;"><?php esc_html_e( 'View Your Order', 'safe-ship-pro' ); ?></a>
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