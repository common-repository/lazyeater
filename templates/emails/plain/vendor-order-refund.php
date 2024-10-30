<?php
/**
 * Vendor order refunded email (plain text)
 *
 * This template can be overridden by copying it to yourtheme/norsani/emails/plain/vendor-order-refund.php.
 *
 * @package Norsani/Templates/Emails/Plain
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


echo '= ' . esc_html( $email_heading ) . " =\n\n";

echo sprintf(__('Hello %1$s,','frozr-norsani'), $vendor_name).'\n\n';

echo sprintf(__('The order id %1$s, dated %2$s, status has been changed from completed to %3$s. The order total amount is %4$s.','frozr-norsani'), $order_id, $order_date, $new_sts, $order_amount).'\n';

echo sprintf(__('Your new balance is %1$s. Please contact us via %2$s if you might need any clarification.','frozr-norsani'), $current_balance, get_option( 'admin_email' )).'\n\n';

echo esc_html( 'Thank you.', 'frozr-norsani' ).'\n\n';

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );