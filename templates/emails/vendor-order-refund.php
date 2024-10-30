<?php
/**
 * Vendor rating request email
 *
 * This template can be overridden by copying it to yourtheme/frozr-norsani/emails/vendor-rating.php.
 *
 * @package Norsani/Templates/Emails/HTML
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email );
	
echo '<p>'.sprintf(__('Hello %1$s,','frozr-norsani'), $vendor_name).'</p>';

echo '<p>'.sprintf(__('The order id %1$s, dated %2$s, status has been changed from completed to %3$s. The order total amount is %4$s.','frozr-norsani'), $order_id, $order_date, $new_sts, $order_amount).'</p>';
echo '<p>'.sprintf(__('Your new balance is %1$s. Please contact us via %2$s if you might need any clarification.','frozr-norsani'), $current_balance, get_option( 'admin_email' )).'</p>';

echo '<p>'.esc_html( 'Thank you.', 'frozr-norsani' ).'</p>';

do_action( 'woocommerce_email_footer', $email );