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
	
echo '<p>'.sprintf(__('Hello %1$s,','frozr-norsani'), $customer_name).'</p>';

echo '<p>'.sprintf(__('Your order number %1$s, dated %2$s, has been marked as completed. Please take few moments and make a rating on %3$s by clicking on the following link','frozr-norsani'), $order_id, $order_date, $vendor_name).'</p>';

echo '<p>'.$revlink.'</p>';

echo '<p>'.esc_html( 'Thank you.', 'frozr-norsani' ).'</p>';

do_action( 'woocommerce_email_footer', $email );