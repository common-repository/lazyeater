<?php
/**
 * Frozr Coupons Class
 *
 * @package Norsani/Dashboard/Coupon
 */
if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

class Norsani_Coupon {
	
	/**
	 * The single instance of the class.
	 *
	 * @var Norsani_Coupon
	 * @since 1.9
	 */
	protected static $_instance = null;
	
	/**
	 * Main Norsani_Coupon Instance.
	 *
	 * @since 1.9
	 * @static
	 * @return Norsani_Coupon - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	/**
	 * Coupons page header
	 *
	 * @return void frontend dashboard coupons page header.
	 */
	public function frozr_coupons_header_nav() {
		ob_start();
		frozr_get_template('views/html-dashboard-coupon-header.php', array());
		echo apply_filters('frozr_dashboard_coupons_page_header_html',ob_get_clean());
	}
	/**
	 * Coupons page list
	 *
	 * @return void frontend dashboard coupons page list.
	 */
	public function frozr_list_user_coupons() {
		ob_start();
		frozr_get_template('views/html-dashboard-coupon-list.php', array());
		echo apply_filters('frozr_dashboard_coupons_page_list_html',ob_get_clean());
	}
	/**
	 * Coupon edit/add form
	 *
	 * @return void frontend dashboard coupons page add/edit form.
	 */
	public function frozr_add_coupons_form() {
		ob_start();
		frozr_get_template('views/html-dashboard-coupon-form.php', array());
		echo apply_filters('frozr_dashboard_coupons_page_form_html',ob_get_clean());
	}
}