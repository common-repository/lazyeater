<?php
/**
 * Withdrawal status email
 *
 * This template can be overridden by copying it to yourtheme/frozr-norsani/emails/withdraw-status.php.
 *
 * @package Norsani/Templates/Emails/HTML
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php printf( esc_html__( 'Hi %1$s,', 'frozr-norsani' ), esc_html( $vendor_name ) ); ?></p>

<?php if($withdraw_status == 'completed') { ?>
<p><?php printf( __( 'Your withdrawal request #%1$s, with total amount of %2$s, via %3$s, has been approved. We will transfer this amount to your preferred destination shortly.', 'frozr-norsani' ), esc_html( $withdraw_id ), '<strong>' . esc_html( $amount ) . '</strong>', esc_html($via) ); ?></p><?php // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>
<?php } elseif($withdraw_status == 'trash') { ?>
<p><?php printf( __( 'Your withdrawal request #%1$s, with total amount of %2$s, via %3$s, has been rejected with the following reason: %4$s.', 'frozr-norsani' ), esc_html( $withdraw_id ), '<strong>' . esc_html( $amount ) . '</strong>', esc_html($via), esc_html($note)); ?></p><?php // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>
<?php } else { ?>
<p><?php printf( __( 'We received your withdrawal request #%1$s, with total amount of %2$s, via %3$s. We will review your request as soon as possible and get back to you.', 'frozr-norsani' ), esc_html( $withdraw_id ), '<strong>' . esc_html( $amount ) . '</strong>', esc_html($via) ); ?></p><?php // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>
<?php } ?>

<p><?php esc_html_e( 'Thank you.', 'frozr-norsani' ); ?></p>

<?php
do_action( 'woocommerce_email_footer', $email );