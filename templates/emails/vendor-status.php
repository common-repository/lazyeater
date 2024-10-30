<?php
/**
 * Vendor status email
 *
 * This template can be overridden by copying it to yourtheme/frozr-norsani/emails/vendor-status.php.
 *
 * @package Norsani/Templates/Emails/HTML
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$user_edit_url = home_url('/dashboard/sellers/');
$user_dashboard_tour = home_url('/dashboard/home/?tour=yes');
$user_dashboard = home_url('/dashboard/home');
$user_privileges_check = null != get_user_meta( $vendor_id, 'frozr_enable_selling', true ) ? get_user_meta( $vendor_id, 'frozr_enable_selling', true ): '';

do_action( 'woocommerce_email_header', $email_heading, $email );

if ($msg_type == 'to_new_seller_auto') {
	
	echo '<p>'.__('Congratulations of been a part of our community. You can start selling by posting your products from your dashboard','frozr-norsani').': '.$user_dashboard.'</p>';

} elseif ($msg_type == 'to_new_seller') {
	
	echo '<p>'.__('Your selling privileges status will be activated shortly by one of our website editors. You will be notified on any updates.','frozr-norsani').'</p>';

} elseif ($msg_type == 'new_seller' || $msg_type == 'new_seller_auto') {
	echo '<p><strong>'. __('Vendor details:','frozr-norsani') . '</strong></p>';
	echo '<p>'.__('Email:','frozr-norsani') ." ". $vendor_email. '</p>';
	echo '<p>'.__('First Name:','frozr-norsani') ." ". $vendor_first_name. '</p>';
	echo '<p>'.__('Last Name:','frozr-norsani') ." ". $vendor_last_name. '</p>';
	echo '<p>'.__('Shop Name:','frozr-norsani') ." ". $shopname. '</p>';
	echo '<p>'.__('Shop URL:','frozr-norsani') ." ". $shopurl. '</p>';
	echo '<p>'.__('Contact Number:','frozr-norsani') ." ". $shopphone. '</p>';
	if ($msg_type == 'new_seller_auto') {
		echo '<p>'.__('Manage all vendors from your dashboard sellers page','frozr-norsani').' '. $user_edit_url. '</p>';
	} else {
		echo '<p>'.__('Activate this vendor&apos;s selling privileges from','frozr-norsani') .' '. $user_edit_url. '</p>';
	}
} elseif ($msg_type == 'privileges') {
	echo '<p>'.sprintf(__('Hello %s','frozr-norsani'), $shopname). '</p>';
	if ($user_privileges_check == 'yes') {
	echo '<p>'.__('Your selling privileges has been activated. You can start selling by posting your products from your dashboard.','frozr-norsani').': '.$user_dashboard_tour. '</p>';
	} else {
	echo '<p>'.__('Your selling privileges has been deactivated','frozr-norsani').'</p>';
	}
}

do_action( 'woocommerce_email_footer', $email );