<?php
/**
 * Withdrawal status email (plain text)
 *
 * This template can be overridden by copying it to yourtheme/norsani/emails/plain/withdraw-status.php.
 *
 * @package Norsani/Templates/Emails/Plain
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '= ' . esc_html( $email_heading ) . " =\n\n";

echo sprintf( esc_html__( 'Hi %1$s,', 'frozr-norsani' ), esc_html( $vendor_name ) ) . "\n\n";

if($withdraw_status == 'completed') {

	printf( __( 'Your withdrawal request #%1$s, with total amount of %2$s, via %3$s, has been approved. We will transfer this amount to your preferred destination shortly.', 'frozr-norsani' ), esc_html( $withdraw_id ), esc_html( $amount ), esc_html($via) );

} elseif($withdraw_status == 'trash') {

	printf( __( 'Your withdrawal request #%1$s, with total amount of %2$s, via %3$s, has been rejected with the following reason: %4$s.', 'frozr-norsani' ), esc_html( $withdraw_id ), esc_html( $amount ), esc_html($via), esc_html($note));

} else {

	printf( __( 'We received your withdrawal request #%1$s, with total amount of %2$s, via %3$s. We will review your request as soon as possible and get back to you.', 'frozr-norsani' ), esc_html( $withdraw_id ), esc_html( $amount ), esc_html($via) );

}

echo esc_html__( 'Thank you.', 'frozr-norsani' ) . "\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );