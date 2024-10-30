<?php
/**
 * Vendor message email (plain text)
 *
 * This template can be overridden by copying it to yourtheme/norsani/emails/plain/vendor-message.php.
 *
 * @package Norsani/Templates/Emails/Plain
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '= ' . esc_html( $email_heading ) . " =\n\n";

if (isset($sender_name)) {
echo __('Sender Name','frozr-norsani').': '. $sender_name.'\n';
}
if (isset($sender_email)) {
echo __('Sender Email','frozr-norsani').': '. $sender_email.'\n\n';
}

echo $msg . "\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );