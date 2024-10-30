<?php
/**
 * Norsani Emails Controller
 *
 * This class is a hook call into WooCommerce Emails Class which handles the sending on transactional emails and email templates.
 *
 * @package Norsani/Emails
 * @version 1.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Emails class.
 */
class Norsani_Emails {

	/**
	 * The single instance of the class
	 *
	 * @var Norsani_Emails
	 */
	protected static $_instance = null;

	/**
	 * Main Norsani_Emails Instance.
	 *
	 * Ensures only one instance of Norsani_Emails is loaded or can be loaded.
	 *
	 * @since 2.1
	 * @static
	 * @return Norsani_Emails Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Norsani Emails Constructor.
	 *
	 * @since 1.9
	 */
	public function __construct() {
		add_filter('woocommerce_email_actions', array($this, 'frozr_add_norsani_email_actions'),10);
		add_filter('woocommerce_email_classes', array($this, 'frozr_add_norsani_email_classes'),10);
	}
	
	/**
	 * Hook in all transactional emails.
	 */
	public static function frozr_add_norsani_email_actions($email_actions) {
		$email_actions[] = 'frozr_send_vendor_message';
		$email_actions[] = 'frozr_send_vendor_order_refund_email';
		$email_actions[] = 'frozr_send_customer_rating_request_email';
		$email_actions[] = 'frozr_send_vendor_status_message';
		$email_actions[] = 'frozr_send_vendor_registration_message';
		$email_actions[] = 'frozr_send_admin_new_vendor_message';
		$email_actions[] = 'frozr_send_vendor_auto_active_message';
		$email_actions[] = 'frozr_send_admin_auto_new_vendor_message';
		$email_actions[] = 'frozr_withdraw_saved';
		
		return $email_actions;
	}

	/**
	 * Norsani email classes.
	 */
	public function frozr_add_norsani_email_classes($emails) {

		$emails['Norsani_Email_Vendor_Msg']				= include 'emails/class-norsani-email-vendor-message.php';
		$emails['Norsani_Email_Vendor_Order_Refund']	= include 'emails/class-norsani-email-vendor-order-refund.php';
		$emails['Norsani_Email_Vendor_Rating']			= include 'emails/class-norsani-email-vendor-rating.php';
		$emails['Norsani_Email_Vendor_Status']			= include 'emails/class-norsani-email-vendor-status.php';
		$emails['Norsani_Email_Withdraw_Status']		= include 'emails/class-norsani-email-withdraw-status.php';
		
		return $emails;
	}
}
return new Norsani_Emails();