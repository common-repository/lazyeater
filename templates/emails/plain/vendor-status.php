<?php
/**
 * Vendor status email (plain text)
 *
 * This template can be overridden by copying it to yourtheme/norsani/emails/plain/vendor-status.php.
 *
 * @package Norsani/Templates/Emails/Plain
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$user_edit_url = home_url('/dashboard/sellers/');
$user_dashboard_tour = home_url('/dashboard/home/?tour=yes');
$user_dashboard = home_url('/dashboard/home');
$user_privileges_check = null != get_user_meta( $vendor_id, 'frozr_enable_selling', true ) ? get_user_meta( $vendor_id, 'frozr_enable_selling', true ): '';

echo '= ' . esc_html( $email_heading ) . " =\n\n";

if ($msg_type == 'to_new_seller_auto') {
	
	echo __('Congratulations of been a part of our community. You can start selling by posting your products from your dashboard','frozr-norsani').': '.$user_dashboard;

} elseif ($msg_type == 'to_new_seller') {
	
	echo __('Your selling privileges status will be activated shortly by one of our website editors. You will be notified on any updates.','frozr-norsani');

} elseif ($msg_type == 'new_seller' || $msg_type == 'new_seller_auto') {
	echo __('Vendor details:','frozr-norsani') . "\n\n";
	echo __('Email:','frozr-norsani') ." ". $vendor_email. "\n";
	echo __('First Name:','frozr-norsani') ." ". $vendor_first_name. "\n";
	echo __('Last Name:','frozr-norsani') ." ". $vendor_last_name. "\n";
	echo __('Shop Name:','frozr-norsani') ." ". $shopname. "\n";
	echo __('Shop URL:','frozr-norsani') ." ". $shopurl. "\n";
	echo __('Contact Number:','frozr-norsani') ." ". $shopphone. "\n\n";
	if ($msg_type == 'new_seller_auto') {
		echo __("Manage all vendors from your dashboard sellers page","frozr-norsani") ." ". $user_edit_url. "\n";
	} else {
		echo __("Activate this vendor&apos;s selling privileges from","frozr-norsani") ." ". $user_edit_url. "\n";
	}
} elseif ($msg_type == 'privileges') {
	if ($user_privileges_check == 'yes') {
	echo __('Your selling privileges has been activated. You can start selling by posting your products from your dashboard.','frozr-norsani').': '.$user_dashboard_tour;
	} else {
	echo __('Your selling privileges has been deactivated','frozr-norsani');
	}
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );