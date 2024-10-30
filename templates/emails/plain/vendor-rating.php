<?php
/**
 * Vendor rating request email (plain text)
 *
 * This template can be overridden by copying it to yourtheme/norsani/emails/plain/vendor-rating.php.
 *
 * @package Norsani/Templates/Emails/Plain
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '= ' . esc_html( $email_heading ) . " =\n\n";

echo sprintf(__('Hello %1$s,','frozr-norsani'), $customer_name).'\n\n';

echo sprintf(__('Your order number %1$s, dated %2$s, has been marked as completed. Please take few moments and make a review on %3$s by clicking on the following link','frozr-norsani'), $order_id, $order_date, $vendor_name).'\n\n';

echo $revlink.'\n\n';

echo esc_html( 'Thank you.', 'frozr-norsani' ).'\n\n';

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );