<?php
/**
 * All Related Norsani Vendor Management Functions
 *
 * @package Norsani
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

class Norsani_Vendor {
	
	/**
	 * The single instance of the class.
	 *
	 * @var Norsani_Vendor
	 * @since 1.9
	 */
	protected static $_instance = null;
	
	/**
	 * Main Norsani_Vendor Instance.
	 *
	 * @since 1.9
	 * @static
	 * @return Norsani_Vendor - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Norsani_Vendor Constructor.
	 */
	public function __construct() {

		add_action('frozr_norsani_vendor_status_main_menu', array($this, 'frozr_norsani_vendor_status_menu'), 10);
		
		add_filter( 'woocommerce_new_customer_data', array($this, 'frozr_new_customer_data') );
		add_filter( 'ajax_query_attachments_args', array($this, 'frozr_media_uploader_filter') );
		do_action( 'norsani_vendor_loaded' );
	}

	/**
	 * Inject first and last name to WooCommerce for new vendor registration
	 *
	 * @param array $data
	 * @return array
	 */
	public function frozr_new_customer_data( $data ) {
		$allowed_roles = array( 'customer', 'seller' );
		$role = ( isset( $_POST['role'] ) && in_array( $_POST['role'], $allowed_roles ) ) ? $_POST['role'] : 'customer';

		$data['role'] = $role;

		if ( $role == 'seller' ) {
			$data['first_name'] = sanitize_text_field( $_POST['frozr_vendor_first_name'] );
			$data['last_name'] = sanitize_text_field( $_POST['frozr_vendor_last_name'] );
			$data['user_nicename'] = sanitize_title($_POST['frozr_vendor_shopname_name']);
		}

		return $data;
	}

	/**
	 * Get vendor info based on seller ID
	 *
	 * @param int $seller_id
	 * @return array
	 */
	public function frozr_get_store_info( $seller_id ) {
		$info = get_user_meta( $seller_id, 'frozr_profile_settings', true );
		$info = is_array( $info ) ? $info : array();

		$defaults = apply_filters('frozr_get_default_store_info',array(
			'store_name' => '',
			'socialfb' => '',
			'socialtwitter' => '',
			'socialyoutube' => '',
			'socialinsta' => '',
			'payment' => apply_filters('frozr_default_accepted_withdraw_payment',array( 'paypal' => array( 'email' ), 'bank' => array() )),
			'phone' => '',
			'shipping_fee' => '',
			'deliveryby' => 'order',
			'shipping_pro_adtl_cost' => '',
			'accpet_order_type' => frozr_default_accepted_orders_types(),
			'accpet_order_type_cl' => frozr_default_accepted_orders_types_closed(),
			'allow_ofline_orders' => 'yes',
			'show_rest_tables' => 'no',
			'banner' => 0,
			'allow_email' => 1,
			'gravatar' => 0,
			'min_order_amt' => 0
		));

		$info = wp_parse_args( $info, $defaults );

		return $info;
	}
	
	/**
	 * Get all vendors
	 *
	 * @return array
	 */
	public function frozr_get_all_sellers() {
		$args = apply_filters( 'frozr_fee_get_sellers_list_query', array(
			'role' => 'seller',
			'orderby' => 'registered',
			'order' => 'ASC',
			'meta_query' => array(
				array(
					'key' => 'frozr_enable_selling',
					'value' => 'yes',
					'compare' => '='
				)
			)));

		$user_query = new WP_User_Query( $args );
		$get_sellers = $user_query->get_results();
		$sellers = array();
		
		foreach($get_sellers as $seller) {
			$vendor_name = frozr_get_store_info($seller->ID);
			$sellers[$seller->ID] = $vendor_name['store_name'];
		}
		return $sellers;
	}
	
	/**
	 * Get all customers
	 *
	 * @return array
	 */
	public function frozr_get_all_customers() {
		$args = apply_filters( 'frozr_fee_get_customers_list_query', array(
			'role' => 'customer',
			'orderby' => 'registered',
			'order' => 'ASC',
			));

		$user_query = new WP_User_Query( $args );
		$get_customers = $user_query->get_results();
		$customers = array();
		
		foreach($get_customers as $customer) {
			$customers[$customer->ID] = $customer->display_name;
		}
		return $customers;
	}
	
	/**
	 * Get vendor page url of a vendor
	 *
	 * @param int $user_id
	 * @return string
	 */
	public function frozr_get_store_url( $user_id ) {
		$userdata = get_userdata( $user_id );

		return sprintf( '%s/%s/', home_url( '/vendor' ), $userdata->user_nicename );
	}
	
	/**
	 * Vendors cant see others media uploads.
	 *
	 * @param array $args	Default args for the media uploder.
	 * @return array
	 */
	public function frozr_media_uploader_filter( $args ) {
		/* bail out for admin and editor*/
		if ( current_user_can( 'delete_pages' ) ) {
			return $args;
		}

		if ( current_user_can( 'frozer' ) ) {
			$args['author'] = get_current_user_id();

			return $args;
		}

		return $args;
	}
	
	/**
	 * Get seller rating in a readable rating format
	 *
	 * @param int $seller_id
	 * @param bool|true $output_html Output html or a number.
	 * @return int|void
	 */
	public function frozr_get_readable_seller_rating( $seller_id, $output_html = true ) {

		$seller_ratings = get_user_meta($seller_id, 'rest_rating', true);
		if (empty($seller_ratings)) {
			if ($output_html) {
			return __('No Ratings Yet!','frozr-norsani');
			} else {
			return 0;
			}
		}
		
		$rc = array();
		$rv = array();

		foreach($seller_ratings as $n => $v) {
			$rc[] = $n;
			$rv[] = $v;
		}
		$nx = count($rc);
		$xx = (array_sum($rv) * 100) / ($nx * 5);

		if ($output_html) {
			return apply_filters('frozr_readable_seller_rating','%' . sprintf( __( '%1$s Based on', 'frozr-norsani' ) .' '. _n('%2$s Rating', '%2$s Ratings', $nx, 'frozr-norsani' ), $xx, $nx), $seller_id);
		} else {
			return $xx;
		}
	}
	
	/**
	 * Get seller rating in a readable rating format ready for a REST call
	 *
	 * @param int $seller_id
	 * @return void
	 */
	public function frozr_rest_get_readable_seller_rating( $seller_id ) {

		$seller_ratings = get_user_meta($seller_id, 'rest_rating', true);
		if (empty($seller_ratings)) {
			
			return apply_filters('frozr_no_readable_seller_rating', '<div class="woocommerce-product-rating"><a class="star-rating" href="#" title="'.__('No Rating Yet!','frozr-norsani').'"></a></div>');
		}
		
		$rc = array();
		$rv = array();
		foreach($seller_ratings as $n => $v) {
			$rc[] = $n;
			$rv[] = $v;
		}
		$nx = count($rc);
		$xx = array_sum($rv)/$nx;
		$output = '<div class="woocommerce-product-rating">';
		$output .= wc_get_rating_html( $xx, $nx );
		$output .= '</div>';
		
		return apply_filters('frozr_readable_seller_rating',$output, $seller_id);
	}
	
	/**
	 * Vendor rating form
	 *
	 * @param int $seller
	 * @param int $orderid Order id made by the current customer.
	 * @return void
	 */
	public function frozr_store_rating_form( $seller, $orderid ) {
		$store_info = frozr_get_store_info( $seller );
		
		ob_start();
		frozr_get_template('views/html-vendor-store_rating.php', array('store_info' => $store_info, 'seller' => $seller, 'orderid' => $orderid));
		echo apply_filters('frozr_store_page_rating_html',ob_get_clean(), $store_info, $seller, $orderid);
	}
	
	/**
	 * Get_vendor addresses
	 *
	 * @param int $seller_id
	 * @return string
	 */
	public function frozr_get_vendor_address($seller_id) {

		/*get vendor addresses*/
		$getallads= get_terms( 'vendor_addresses', 'fields=names&hide_empty=0' );
		$restads = wp_get_object_terms( $seller_id, 'vendor_addresses', array("fields" => "names") );
		if (is_array($restads)) {
			$restaddresses = isset($restads[0]) ? $restads[0] : '';
		} elseif ( ! empty( $getallads ) && ! is_wp_error( $getallads )) {
			$restaddresses = $restads;
		}
		return apply_filters('frozr_get_vendor_address',$restaddresses,$seller_id);
	}
	
	/**
	 * Vendor menu type options
	 *
	 * @param int $seller_id
	 * @return bool|array
	 */
	public function frozr_meal_type_options($seller_id) {
		$meal_types = get_user_meta($seller_id, '_rest_meal_types', true);
		$options = array();
		
		if (!empty($meal_types)) {
			foreach ($meal_types as $mvals){
				$options[sanitize_title(wp_unslash($mvals['title']))] = $mvals['title'];
			}
			$filterd_options = array_filter($options);
			if (!empty($filterd_options)) {
			return $options;
			} else {
			return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Vendors opening/closing time
	 *
	 * @param int $seller_id
	 * @param bool|true $lyt Result string layout. True if this is used in vendor store page.
	 * @return array
	 */
	public function frozr_vendors_open_close($seller_id, $lyt = true) {

		$runds = $rstss = false;
		$unds = ('' != get_user_meta($seller_id, '_rest_unavds', true)) ? get_user_meta($seller_id, '_rest_unavds', true) : array();
		$filterd_inds = array_filter($unds);
		if ($lyt == false) {
			$txt_open = '';
			$txt_opens_at = ' '.__("at", 'frozr-norsani').' ';
			$txt_opens_tmro = ' '.__("tomorrow", 'frozr-norsani').' ';
			$txt_close = ' '.__("another day", 'frozr-norsani').' ';
			$txt_at = ' '.__("at", 'frozr-norsani').' ';
		} else {
			$txt_open = __("Open till", 'frozr-norsani').' ';
			$txt_opens_at = __("Opens at", 'frozr-norsani').' ';
			$txt_opens_tmro = __("Opens Tomorrow", 'frozr-norsani').' ';
			$txt_close = __("Closed", 'frozr-norsani');
			$txt_at = ' '.__("at", 'frozr-norsani').' ';
		}
		
		if (frozr_manual_vendor_online()) {
		return apply_filters('frozr_vendor_open_close_timing',array($txt_close, $rstss), $seller_id, $lyt);
		}
		
		if (!empty($filterd_inds)) {
		foreach ($filterd_inds as $unava_date => $unava_value) {
			if (!empty($unava_value['start']) && !empty ($unava_value['end'])) {
			if (date('Ymd', strtotime(str_replace('/', '-', $unava_value['start']))) < date("Ymd") && date("Ymd") < date('Ymd', strtotime(str_replace('/', '-', $unava_value['end']))) || date('Ymd', strtotime(str_replace('/', '-', $unava_value['start']))) == date("Ymd")) {
				$runds = $unava_value['end'];
				continue; 
			}
			}
		}
		}
		$restime = get_user_meta( $seller_id, 'rest_open_close_time',true );

		$tm = current_time('D');
		$nw = new DateTime(date('H:i', strtotime(current_time('H:i'))));
		
		$rest_open = isset($restime[$tm]['restop']) ? $restime[$tm]['restop'] : null;
		$rest_open_2 = isset($restime[date('D', strtotime('+1 day'))]['restop']) ? $restime[date('D', strtotime('+1 day'))]['restop'] : '';
		$rest_open_3 = isset($restime[date('D', strtotime('+2 days'))]['restop']) ? $restime[date('D', strtotime('+2 days'))]['restop'] : '';
		$rest_open_4 = isset($restime[date('D', strtotime('+3 days'))]['restop']) ? $restime[date('D', strtotime('+3 days'))]['restop'] : '';
		$rest_open_5 = isset($restime[date('D', strtotime('+4 days'))]['restop']) ? $restime[date('D', strtotime('+4 days'))]['restop'] : '';
		$rest_open_6 = isset($restime[date('D', strtotime('+5 days'))]['restop']) ? $restime[date('D', strtotime('+5 days'))]['restop'] : '';
		$rest_open_7 = isset($restime[date('D', strtotime('+6 days'))]['restop']) ? $restime[date('D', strtotime('+6 days'))]['restop'] : '';
		$rest_shifts = isset($restime[$tm]['restshifts']) ? $restime[$tm]['restshifts'] : '';
		$rest_opening_one = isset($restime[$tm]['open_one']) ? new DateTime(date('H:i', strtotime($restime[$tm]['open_one']))) : null;
		$rest_closing_one = isset($restime[$tm]['close_one']) ? new DateTime(date('H:i', strtotime($restime[$tm]['close_one']))) : null;
		$rest_opening_two = isset($restime[$tm]['open_two']) ? new DateTime(date('H:i', strtotime($restime[$tm]['open_two']))) : null;
		$rest_closing_two = isset($restime[$tm]['close_two']) ? new DateTime(date('H:i', strtotime($restime[$tm]['close_two']))) : null;
		
		if ($runds) {
			$rsts = $txt_opens_at . $runds;
		} elseif ($rest_open) {
			if ($rest_opening_one <= $nw && $nw < $rest_closing_one) {
				$rsts = $txt_open . date_i18n(frozr_get_time_date_format(), strtotime($restime[$tm]['close_one']));
				$rstss = true;
			} elseif ($rest_shifts && $rest_opening_two <= $nw && $nw < $rest_closing_two) {
				$rsts = $txt_open . date_i18n(frozr_get_time_date_format(), strtotime($restime[$tm]['close_two']));
				$rstss = true;
			} elseif ($rest_opening_one > $nw) {
				$rsts = $txt_opens_at . date_i18n(frozr_get_time_date_format(), strtotime($restime[$tm]['open_one']));
			} elseif ($rest_shifts && $nw < $rest_opening_two) {
				$rsts = $txt_opens_at . date_i18n(frozr_get_time_date_format(), strtotime($restime[$tm]['open_two']));
			} else {
				if ($rest_open_2) {
					$rsts = $txt_opens_tmro . $txt_at . date(frozr_get_time_date_format(), strtotime($restime[date('D', strtotime('+1 days'))]['open_one']));
				} elseif ($rest_open_3) {
					$rsts = $txt_opens_at . date_i18n('l', strtotime('+2 days')) . $txt_at . date(frozr_get_time_date_format(), strtotime($restime[date('D', strtotime('+2 days'))]['open_one']));
				} elseif ($rest_open_4) {
					$rsts = $txt_opens_at . date_i18n('l', strtotime('+3 days')) . $txt_at . date(frozr_get_time_date_format(), strtotime($restime[date('D', strtotime('+3 days'))]['open_one']));
				} elseif ($rest_open_5) {
					$rsts = $txt_opens_at . date_i18n('l', strtotime('+4 days')) . $txt_at . date(frozr_get_time_date_format(), strtotime($restime[date('D', strtotime('+4 days'))]['open_one']));
				} elseif ($rest_open_6) {
					$rsts = $txt_opens_at . date_i18n('l', strtotime('+5 days')) . $txt_at . date(frozr_get_time_date_format(), strtotime($restime[date('D', strtotime('+5 days'))]['open_one']));
				} elseif ($rest_open_7) {
					$rsts = $txt_opens_at . date_i18n('l', strtotime('+6 days')) . $txt_at . date(frozr_get_time_date_format(), strtotime($restime[date('D', strtotime('+6 days'))]['open_one']));
				} else {
					$rsts = $txt_close;
				}
			}
		} else {
			if ($rest_open_2) {
				$rsts = $txt_opens_tmro;
			} elseif ($rest_open_3) {
				$rsts = $txt_opens_on . date_i18n('l', strtotime('+2 days')) . $txt_at . date(frozr_get_time_date_format(), strtotime($restime[date('D', strtotime('+2 days'))]['open_one']));
			} elseif ($rest_open_4) {
				$rsts = $txt_opens_on . date_i18n('l', strtotime('+3 days')) . $txt_at . date(frozr_get_time_date_format(), strtotime($restime[date('D', strtotime('+3 days'))]['open_one']));
			} elseif ($rest_open_5) {
				$rsts = $txt_opens_on . date_i18n('l', strtotime('+4 days')) . $txt_at . date(frozr_get_time_date_format(), strtotime($restime[date('D', strtotime('+4 days'))]['open_one']));
			} elseif ($rest_open_6) {
				$rsts = $txt_opens_on . date_i18n('l', strtotime('+5 days')) . $txt_at . date(frozr_get_time_date_format(), strtotime($restime[date('D', strtotime('+5 days'))]['open_one']));
			} elseif ($rest_open_7) {
				$rsts = $txt_opens_on . date_i18n('l', strtotime('+6 days')) . $txt_at . date(frozr_get_time_date_format(), strtotime($restime[date('D', strtotime('+6 days'))]['open_one']));
			} else {
				$rsts = $txt_close;
			}
		}
		
		return apply_filters('frozr_vendor_open_close_timing',array($rsts, $rstss), $seller_id, $lyt);
	}

	/**
	 * Check the vendor open/close status
	 *
	 * @param int $seller_id
	 * @param bool|true $lyt Result string layout. True to include description, and False for bool|time result.
	 * @return string|bool
	 */
	public function frozr_rest_status($seller_id, $lyt = true) {
		$option = get_option( 'frozr_gen_settings' );
		$manual_online = isset( $option['frozr_manual_online_seller']) ? $option['frozr_manual_online_seller'] : 0;
		$max_unactive_time = isset($option['frozr_auto_offline_max_time']) ? $option['frozr_auto_offline_max_time'] : 10;
		$manual_status_change_time = get_user_meta($seller_id,'frozr_vendor_manual_change_time',true) ? get_user_meta($seller_id,'frozr_vendor_manual_change_time',true) : 0;
		$manual_status = get_user_meta($seller_id,'frozr_vendor_manual_status',true) ? get_user_meta($seller_id,'frozr_vendor_manual_status',true) : array('online'=>null,'time'=>current_time('mysql'));
		$manual_duration = null !== get_user_meta($seller_id,'frozr_manual_duration',true) ? get_user_meta($seller_id,'frozr_manual_duration',true) : 0;
		$nw = new DateTime(current_time('mysql'));
		$changetime = $manual_status_change_time != 0 ? new DateTime($manual_status_change_time) : false;
		$opentime = new DateTime($manual_status['time']);
		$opendiff = $opentime->diff($nw);
		$changediff = $changetime? $changetime->diff($nw) : false;
		$mandur = $changediff ? $changediff->i : 0;
		$mandur_hour = $changediff ? $changediff->h : 0;
		$mandur_day = $changediff ? $changediff->d : 0;
		$nx = $this->frozr_vendors_open_close($seller_id, $lyt);
		
		if ($manual_online) {
			if ($manual_status['online'] && intval($max_unactive_time) == 0 && $mandur_day == 0 && $mandur_hour == 0 && $mandur < intval($manual_duration) || $manual_status['online'] && $opendiff->d == 0 && $opendiff->h == 0 && $opendiff->i < intval($max_unactive_time) && $mandur_day == 0 && $mandur_hour == 0 && $mandur < intval($manual_duration)) {
			$mleft = $manual_duration - $mandur;
			if (!$lyt) {
			return "+$mleft minutes";
			} else {
			return sprintf(_n('Open for %s minute', 'Open for %s minutes', $mleft,'frozr-norsani'),$mleft);
			}
			} else {
			$manual_status['online'] = null;
			update_user_meta($seller_id,'frozr_vendor_manual_status',$manual_status);
			if (!$lyt) {
			return false;
			} else {
			return __('Closed','frozr-norsani');
			}
			}
		} elseif ('off' == $manual_status['online']) {
			if ($changediff && $mandur_day == 0 && $mandur_hour == 0 && $mandur < intval($manual_duration)) {
			$mleft = $manual_duration - $mandur;
			if (!$lyt) {
			return "+$mleft minutes";
			} else {
			return sprintf(_n('Closed for %s minute', 'Closed for %s minutes', $mleft,'frozr-norsani'),$mleft);
			}
			} else {
			$manual_status['online'] = null;
			update_user_meta($seller_id,'frozr_vendor_manual_status',$manual_status);
			if (!$lyt) {
			return false;
			} else {
			return __('Closed','frozr-norsani');
			}
			}
		} elseif ($manual_status['online']) {
			if (intval($max_unactive_time) == 0 && $mandur_day == 0 && $mandur_hour == 0 && $mandur < intval($manual_duration) || $opendiff->d == 0 && $opendiff->h == 0 && $opendiff->i < intval($max_unactive_time) && $mandur_day == 0 && $mandur_hour == 0 && $mandur < intval($manual_duration)) {
			$mleft = $manual_duration - $mandur;
			if (!$lyt) {
			return "+$mleft minutes";
			} else {
			return sprintf(_n('Open for %s minute', 'Open for %s minutes', $mleft,'frozr-norsani'),$mleft);
			}
			} else {
			$manual_status['online'] = null;
			update_user_meta($seller_id,'frozr_vendor_manual_status',$manual_status);
			if (!$lyt) {
			return false;
			} else {
			return __('Closed','frozr-norsani');
			}
			}
		} else {
			return $nx[0];
		}
	}

	/**
	 * Check if vendor is open
	 *
	 * @param int $seller_id
	 * @return bool
	 */
	public function frozr_is_rest_open($seller_id) {
		$option = get_option( 'frozr_gen_settings' );
		$manual_online = (! empty( $option['frozr_manual_online_seller']) ) ? $option['frozr_manual_online_seller'] : 0;
		$max_unactive_time = null !== $option['frozr_auto_offline_max_time'] ? $option['frozr_auto_offline_max_time'] : 10;
		$manual_status_change_time = get_user_meta($seller_id,'frozr_vendor_manual_change_time',true) ? get_user_meta($seller_id,'frozr_vendor_manual_change_time',true) : 0;
		$manual_status = get_user_meta($seller_id,'frozr_vendor_manual_status',true) ? get_user_meta($seller_id,'frozr_vendor_manual_status',true) : array('online'=>null,'time'=>current_time('mysql'));
		$manual_duration = null !== get_user_meta($seller_id,'frozr_manual_duration',true) ? get_user_meta($seller_id,'frozr_manual_duration',true) : 0;
		$nw = new DateTime(current_time('mysql'));
		$changetime = $manual_status_change_time != 0 ? new DateTime($manual_status_change_time) : false;
		$opentime = new DateTime($manual_status['time']);
		$opendiff = $opentime->diff($nw);
		$changediff = $changetime? $changetime->diff($nw) : false;
		$mandur = $changediff ? $changediff->i : 0;
		$mandur_hour = $changediff ? $changediff->h : 0;
		$mandur_day = $changediff ? $changediff->d : 0;
		$nx = $this->frozr_vendors_open_close($seller_id);
		
		if ($manual_online) {
			if ($manual_status['online'] && intval($max_unactive_time) == 0 && $mandur_day == 0 && $mandur_hour == 0 && $mandur < intval($manual_duration) || $manual_status['online'] && $opendiff->d == 0 && $opendiff->h == 0 && $opendiff->i < intval($max_unactive_time) && $mandur_day == 0 && $mandur_hour == 0 && $mandur < intval($manual_duration)) {
			return true;
			} else {
			$manual_status['online'] = null;
			update_user_meta($seller_id,'frozr_vendor_manual_status',$manual_status);
			return false;
			}
		} elseif ('off' == $manual_status['online']) {
			if ($changediff && $mandur_day == 0 && $mandur_hour == 0 && $mandur < intval($manual_duration)) {
			return false;
			} else {
			$manual_status['online'] = null;
			update_user_meta($seller_id,'frozr_vendor_manual_status',$manual_status);
			return false;
			}
		} elseif ($manual_status['online']) {
			if (intval($max_unactive_time) == 0 && $mandur_day == 0 && $mandur_hour == 0 && $mandur < intval($manual_duration) || $opendiff->d == 0 && $opendiff->h == 0 && $opendiff->i < intval($max_unactive_time) && $mandur_day == 0 && $mandur_hour == 0 && $mandur < intval($manual_duration)) {
			return true;
			} else {
			$manual_status['online'] = null;
			update_user_meta($seller_id,'frozr_vendor_manual_status',$manual_status);
			return false;
			}
		} elseif ($nx[1]) {
			return true;
		} else {
			return false;
		}
	}
	
	/*
	 * Get vendor shop coupons
	 *
	 * @param int $seller_id
	 * @return void
	 */
	public function frozr_show_shop_coupons($seller_id) {
		$args = apply_filters('frozr_show_shop_coupons_args',array(
			'post_type' => 'shop_coupon',
			'post_status' => array('publish'),
			'posts_per_page' => 1,
			'author' => $seller_id,
			'meta_query' => array(
				array(
					'key' => 'show_cp_inshop',
					'value' => 'yes',
					'compare' => '='
				)
			)
		),$seller_id);

		$all_coupons = get_posts( $args );

		if ( !empty($all_coupons) ) {
			ob_start();
			frozr_get_template('views/html-vendor-store_coupon.php', array('all_coupons' => $all_coupons, 'seller_id' => $seller_id));
			echo apply_filters('frozr_store_page_coupon_html',ob_get_clean(), $all_coupons, $seller_id);
		}
	}
	
	/**
	 * Get the time info for the vendor for a single day.
	 *
	 * @param string $day
	 * @return array
	 */
	public function frozr_vendor_timing( $day ) {
		
		$current_user = get_current_user_id();
		$restime = get_user_meta( $current_user, 'rest_open_close_time',true );
		
		$rest_open = isset($restime[$day]['restop']) ? $restime[$day]['restop'] : '';
		$rest_shifts = isset($restime[$day]['restshifts']) ? $restime[$day]['restshifts'] : '';
		$rest_opening_one = isset($restime[$day]['open_one']) ? $restime[$day]['open_one'] : '';
		$rest_closing_one = isset($restime[$day]['close_one']) ? $restime[$day]['close_one'] : '';
		$rest_opening_two = isset($restime[$day]['open_two']) ? $restime[$day]['open_two'] : '';
		$rest_closing_two = isset($restime[$day]['close_two']) ? $restime[$day]['close_two'] : '';
		
		return apply_filters('frozr_vendor_timing',array ($rest_open, $rest_shifts, $rest_opening_one, $rest_closing_one, $rest_opening_two, $rest_closing_two), $day);

	}
	
	/**
	 * Vendor profile settings output
	 *
	 * @return void
	 */
	public function frozr_output_vendor_settings() {
		$current_user = get_current_user_id();
		$profile_info = frozr_get_store_info( $current_user );

		$banner = isset( $profile_info['banner'] ) ? absint( $profile_info['banner'] ) : 0;
		$storename = isset( $profile_info['store_name'] ) ? esc_attr( $profile_info['store_name'] ) : '';
		$gravatar = isset( $profile_info['gravatar'] ) ? absint( $profile_info['gravatar'] ) : 0;

		$fb = isset( $profile_info['socialfb']) ? esc_url( $profile_info['socialfb']) : '';
		$twitter = isset( $profile_info['socialtwitter']) ? esc_url( $profile_info['socialtwitter']) : '';
		$youtube = isset( $profile_info['socialyoutube']) ? esc_url( $profile_info['socialyoutube']) : '';
		$instagram = isset( $profile_info['socialinsta']) ? esc_url( $profile_info['socialinsta']) : '';

		$phone = isset( $profile_info['phone'] ) ? esc_attr( $profile_info['phone'] ) : '';
		$allow_email = isset( $profile_info['allow_email'] ) ? esc_attr( $profile_info['allow_email'] ) : 1;
		$allow_ofline_orders = isset( $profile_info['allow_ofline_orders'] ) ? esc_attr( $profile_info['allow_ofline_orders'] ) : 'yes';
		$show_rest_tables = isset( $profile_info['show_rest_tables'] ) ? esc_attr( $profile_info['show_rest_tables'] ) : 'no';

		/*Delivery*/
		$shipping_fee = isset( $profile_info['shipping_fee'] ) ? $profile_info['shipping_fee'] : '';
		$shipping_fee_peak = isset( $profile_info['shipping_fee_peak'] ) ? $profile_info['shipping_fee_peak'] : '';
		$deliveryby = isset( $profile_info['deliveryby'] ) ? $profile_info['deliveryby'] : 'order';
		$deliveryby_peak = isset( $profile_info['deliveryby_peak'] ) ? $profile_info['deliveryby_peak'] : 'order';
		$shipping_pro_adtl_cost = isset( $profile_info['shipping_pro_adtl_cost'] ) ? $profile_info['shipping_pro_adtl_cost'] : '';
		$shipping_pro_adtl_cost_peak = isset( $profile_info['shipping_pro_adtl_cost_peak'] ) ? $profile_info['shipping_pro_adtl_cost_peak'] : '';
		$min_order_amt = isset( $profile_info['min_order_amt'] ) ? $profile_info['min_order_amt'] : '';
		$min_order_amt_peak = isset( $profile_info['min_order_amt_peak'] ) ? $profile_info['min_order_amt_peak'] : '';

		$orders_accept = ! empty ($profile_info['accpet_order_type']) ? $profile_info['accpet_order_type'] : frozr_default_accepted_orders_types();
		$orders_accept_cl = ! empty ($profile_info['accpet_order_type_cl']) ? $profile_info['accpet_order_type_cl'] : frozr_default_accepted_orders_types_closed();
		$peak_orders = '' != get_user_meta($current_user, 'frozr_peak_number', true) ? get_user_meta($current_user, 'frozr_peak_number', true) : 0;

		$rest_food_type = '' != get_user_meta($current_user, 'frozr_food_type', true) ? get_user_meta($current_user, 'frozr_food_type', true) : array('veg','nonveg','sea-food');

		/*get all addresses*/
		$geo_loc = '' != get_user_meta($current_user, 'rest_address_geo', true) ? get_user_meta($current_user, 'rest_address_geo', true) : '';
		$getallads= get_terms( 'vendor_addresses', 'fields=names&hide_empty=0' );
		$addresses_slug = array();
		if ( ! empty( $getallads ) && ! is_wp_error( $getallads ) ){
		foreach ( $getallads as $term ) {
			$addresses_slug[] = $term;
		}
		$alladdresses = '"'.join( '"," ', $addresses_slug ).'"';
		}

		/*get vendor type*/
		$getalltyps = get_terms( 'vendorclass', 'fields=names&hide_empty=0' );
		$restypes = wp_get_object_terms( $current_user, 'vendorclass', array("fields" => "names") );
		$restype_slug = array();
		if (is_array($restypes)) {
		foreach ( $restypes as $restype ) {
			$restype_slug[] = $restype;
		}
		$grestypes = join( '- ', $restype_slug );
		} elseif ( ! empty( $getalltyps ) && ! is_wp_error( $getalltyps )) {
		$grestypes = $restypes;
		}
		/*get all types*/
		$rtys_slug = array();
		if ( ! empty( $getalltyps ) && ! is_wp_error( $getalltyps ) ){
		foreach ( $getalltyps as $term ) {
			$rtys_slug[] = $term;
		}
		$allgrestypes = '"'.join( '"," ', $rtys_slug ).'"';
		}

		$locs = '';
		$allocs = '';
		if (frozr_is_using_geolocation()) {
			$delunfilterd = array();
			$delivery_meta = get_user_meta($current_user, 'delivery_location', true);
			$delivery_locations = ! empty ($delivery_meta) ? implode(',',$delivery_meta) : '';
			$delivery_ins = __( 'Click on the map to start drawing the path for a polygon representing the area that you can provide delivery service.','frozr-norsani');
			$delivery_locs_filtered = get_user_meta($current_user, 'delivery_location_filtered', true);
			if (!empty($delivery_locs_filtered)) {
				foreach ($delivery_locs_filtered as $vals) {
					$delunfilterd[] = implode(',', $vals);
				}
			}
			} else {
			/*get user locations*/
			$getallocs = get_terms( 'location', 'fields=names&hide_empty=0' );
			$locations = wp_get_object_terms( $current_user, 'location', array("fields" => "names") );
			$locations_slug = array();
			if (is_array($locations)) {
				foreach ( $locations as $location ) {
					$locations_slug[] = $location;
				}
				$locs = join( '- ', $locations_slug );
			} elseif ( ! empty( $getallocs ) && ! is_wp_error( $getallocs )) {
				$locs = $locations;
			}
			/*get all locations*/
			$locs_slug = array();
			if ( ! empty( $getallocs ) && ! is_wp_error( $getallocs ) ){
				foreach ( $getallocs as $term ) {
				$locs_slug[] = $term;
				}
				$allocs = '"'.join( '"," ', $locs_slug ).'"';
			}
			$delivery_ins = __( 'Road/Street names. Type first two/three letters and choose from list, if the list doesn\'t appear, then complete typing and hit the comma button.', 'frozr-norsani' );
		}
		$user_notice = get_user_meta($current_user, 'frozr_vendor_store_notice', true);
		$default_types = frozr_get_default_vendors_types();
		$vendor_type = get_user_meta($current_user, 'frozr_vendor_type', true);
		$calculate_prepare_time = null != get_user_meta($current_user, 'calculate_prepare_time', true) ? get_user_meta($current_user, 'calculate_prepare_time', true) : 50;

		$meal_types = get_user_meta($current_user, '_rest_meal_types', true);
		$filterd_metyps = is_array($meal_types) ? array_filter($meal_types) : $meal_types;
		$filterd_metyps_opts = is_array($filterd_metyps[0]) ? array_filter($filterd_metyps[0]) : $filterd_metyps[0];

		$opxlar = apply_filters('frozr_store_timing_week_array',array(
			'sat' => __( 'Saturday', 'frozr-norsani' ),
			'sun' => __( 'Sunday', 'frozr-norsani' ),
			'mon' => __( 'Monday', 'frozr-norsani' ),
			'tue' => __( 'Tuesday', 'frozr-norsani' ),
			'wed' => __( 'Wednesday', 'frozr-norsani' ),
			'thu' => __( 'Thursday', 'frozr-norsani' ),
			'fri' => __( 'Friday', 'frozr-norsani' ),
			));
		$opxlarx = apply_filters('frozr_store_timing_week_args',array('Sat','Sun','Mon','Tue','Wed','Thu','Fri'));
		$opxlnum = 0;

		$unds = null != get_user_meta($current_user, '_rest_unavds', true) ? get_user_meta($current_user, '_rest_unavds', true) : false;			
		$filterd_inds = is_array($unds) ? array_filter($unds) : $unds;
		$filterd_inds_opts = isset($filterd_inds[0]) ? array_filter($filterd_inds[0]) : array();

		$withdrawl_methods = norsani()->withdraw->frozr_withdraw_get_active_methods();

		$args = apply_filters('frozr_dashboard_vendor_settings_page_form_vars',array(
			'current_user' => $current_user,
			'profile_info' => $profile_info,
			'banner' => $banner,
			'storename' => $storename,
			'gravatar' => $gravatar,
			'fb' => $fb,
			'twitter' => $twitter,
			'youtube' => $youtube,
			'instagram' => $instagram,
			'phone' => $phone,
			'allow_email' => $allow_email,
			'allow_ofline_orders' => $allow_ofline_orders,
			'show_rest_tables' => $show_rest_tables,
			'shipping_fee' => $shipping_fee,
			'shipping_fee_peak' => $shipping_fee_peak,
			'deliveryby' => $deliveryby,
			'deliveryby' => $deliveryby,
			'deliveryby_peak' => $deliveryby_peak,
			'shipping_pro_adtl_cost' => $shipping_pro_adtl_cost,
			'shipping_pro_adtl_cost_peak' => $shipping_pro_adtl_cost_peak,
			'min_order_amt' => $min_order_amt,
			'min_order_amt_peak' => $min_order_amt_peak,
			'orders_accept' => $orders_accept,
			'orders_accept_cl' => $orders_accept_cl,
			'peak_orders' => $peak_orders,
			'rest_food_type' => $rest_food_type,
			'getalltyps' => $getalltyps,
			'geo_loc' => $geo_loc,
			'grestypes' => $grestypes,
			'allgrestypes' => $allgrestypes,
			'delivery_locations' => $delivery_locations,
			'delunfilterd' => $delunfilterd,
			'locs' => $locs,
			'allocs' => $allocs,
			'delivery_ins' => $delivery_ins,
			'user_notice' => $user_notice,
			'default_types' => $default_types,
			'vendor_type' => $vendor_type,
			'calculate_prepare_time' => $calculate_prepare_time,
			'filterd_metyps' => $filterd_metyps,
			'filterd_metyps_opts' => $filterd_metyps_opts,
			'opxlar' => $opxlar,
			'opxlarx' => $opxlarx,
			'opxlnum' => $opxlnum,
			'filterd_inds' => $filterd_inds,
			'filterd_inds_opts' => $filterd_inds_opts,
			'withdrawl_methods' => $withdrawl_methods,
		));

		ob_start();
		frozr_get_template('views/html-dashboard-vendor-settings_form.php', $args);
		echo apply_filters('frozr_dashboard_vendor_settings_page_html',ob_get_clean(), $args);
	}
	
	/**
	 * Get distance divider
	 *
	 * @param bool $get_per Return the distance unit system or int distance unit system meters.
	 * @return int|string
	 */
	public function frozr_distance_divider($get_per=false) {
		$option = get_option( 'frozr_dis_settings' );
		$options = (! empty( $option['frozr_norsani_distance_unitsystem']) ) ? $option['frozr_norsani_distance_unitsystem'] : 'google.maps.UnitSystem.METRIC';
		if ($options == 'google.maps.UnitSystem.METRIC') {
			$divider = 1000;
		} else {
			$divider = 1609;
		}
		if ($get_per) {
			$divider = $options == 'google.maps.UnitSystem.METRIC' ? __('km.','frozr-norsani') : __('mi.','frozr-norsani');
		}
		return $divider;
	}
	
	/**
	 * Get delivery settings of vendor
	 *
	 * @param int $current_vendor	Vendor id.
	 * @param string $setting 		What setting to return.
	 * @param bool $no_cal 			Return a calculated result.
	 * @param string $address 		Address to calculate result on.
	 * @return string
	 */
	public function frozr_delivery_settings($current_vendor, $setting, $no_cal = false, $address = null) {
		$profile_info = frozr_get_store_info( $current_vendor );
		$divider = $this->frozr_distance_divider();
		$shipping_fee = isset( $profile_info['shipping_fee'] ) ? $profile_info['shipping_fee'] : 0;
		$shipping_fee_peak = isset( $profile_info['shipping_fee_peak'] ) ? $profile_info['shipping_fee_peak'] : 0;
		$deliveryby = isset( $profile_info['deliveryby'] ) ? $profile_info['deliveryby'] : 'order';
		$deliveryby_peak = isset( $profile_info['deliveryby_peak'] ) ? $profile_info['deliveryby_peak'] : 'order';
		$shipping_pro_adtl_cost = isset( $profile_info['shipping_pro_adtl_cost'] ) ? $profile_info['shipping_pro_adtl_cost'] : 0;
		$shipping_pro_adtl_cost_peak = isset( $profile_info['shipping_pro_adtl_cost_peak'] ) ? $profile_info['shipping_pro_adtl_cost_peak'] : 0;
		$min_order_amt = isset( $profile_info['min_order_amt'] ) ? $profile_info['min_order_amt'] : 0;
		$min_order_amt_peak = isset( $profile_info['min_order_amt_peak'] ) ? $profile_info['min_order_amt_peak'] : 0;
		$peak_orders = 0 != get_user_meta($current_vendor, 'frozr_peak_number', true) ? get_user_meta($current_vendor, 'frozr_peak_number', true) : 0;
		$current_processing_orders = frozr_count_user_object('wc-processing', 'shop_order',$current_vendor);

		if (intval($peak_orders) > 0 && intval($current_processing_orders) > intval($peak_orders)) {
			switch ($setting) {
				case 'shipping_fee':
				if ($no_cal) {
				return $shipping_fee_peak;
				} else {
					if($shipping_fee_peak != 0) {
					$distance_of_customer = frozr_get_distance($current_vendor,$address);
					$total_desc = $distance_of_customer > $divider ? $distance_of_customer/$divider : 1;
					$total_del_fee = $total_desc * $shipping_fee_peak;
					return intval($total_del_fee);
					} else {
					return $shipping_fee_peak;
					}
				}
				break;
				case 'deliveryby':
				return $deliveryby_peak;
				break;
				case 'shipping_pro_adtl_cost':
				return $shipping_pro_adtl_cost_peak;
				break;
				case 'min_order_amt':
				return $min_order_amt_peak;
				break;
			}
		} else {
			switch ($setting) {
				case 'shipping_fee':
				if ($no_cal) {
				return $shipping_fee;
				} else {
					if($shipping_fee != 0) {
					$distance_of_customer = frozr_get_distance($current_vendor,$address);
					$total_desc = $distance_of_customer > $divider ? $distance_of_customer/$divider : 1;
					$total_del_fee = $total_desc * $shipping_fee;
					return intval($total_del_fee);
					} else {
					return $shipping_fee;
					}
				}
				break;
				case 'deliveryby':
				return $deliveryby;
				break;
				case 'shipping_pro_adtl_cost':
				return $shipping_pro_adtl_cost;
				break;
				case 'min_order_amt':
				return $min_order_amt;
				break;
			}
		}
	}
	
	/**
	 * Get vendor store notice
	 *
	 * @param int $seller_id
	 * @param string $notice 		If false or empty, the system will return the notice set in the vendor settings page.
	 * @return void
	 */
	public function frozr_rest_notice_output($seller_id,$notice=false) {
		$user_notice = $notice ? $notice : get_user_meta($seller_id, 'frozr_vendor_store_notice', true);
		if (!ctype_space($user_notice) && !empty($user_notice) ) {
			ob_start();
			frozr_get_template('views/html-vendor-store_notice.php', array('seller_id' => $seller_id, 'user_notice' => $user_notice));
			echo apply_filters('frozr_vendor_store_page_notice_html',ob_get_clean(), $seller_id, $user_notice);
		}
	}
	
	/**
	 * Insert data to distance table
	 * 
	 * @param int $vendor_id
	 * @param string $originAddresses 		Address set by the customer.
	 * @param float $distance
	 * @param string $duration
	 * @return void
	 */
	public function frozr_add_distance($vendor_id,$originAddresses,$distance,$duration) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'forzr_distances';
		$destinationAddresses = '' != get_user_meta(intval($vendor_id), 'rest_address_geo', true) ? get_user_meta(intval($vendor_id), 'rest_address_geo', true) : '';
		$sanitized_originaddress = sanitize_title(wp_unslash($originAddresses));
		$sanitized_destinationaddresses = sanitize_title(wp_unslash($destinationAddresses));
		$sanitized_distance = floatval($distance);
		$sanitized_duration = esc_attr($duration);
		$check_current_value = frozr_get_distance($vendor_id,$originAddresses);
		
		if (! $check_current_value && $sanitized_distance > 0 ) {
			$wpdb->query( $wpdb->prepare(
				"
					INSERT INTO $table_name
					( originAddresses, destinationAddresses, distance, duration )
					VALUES ( %s, %s, %s, %s )
				",
				$sanitized_originaddress,
				$sanitized_destinationaddresses,
				$sanitized_distance,
				$sanitized_duration
			));
		} else {
			return false;
		}
	}
	
	/**
	 * Get data from distance table
	 *
	 * @param int $vendor_id
	 * @param string $originAddresses 		Address set by the customer.
	 * @param bool $get_duration			Get the duration too?
	 * @return string
	 */
	public function frozr_get_distance($vendor_id,$originAddresses=null,$get_duration=false) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'forzr_distances';
		$destinationAddresses = '' != get_user_meta(intval($vendor_id), 'rest_address_geo', true) ? get_user_meta(intval($vendor_id), 'rest_address_geo', true) : '';
		$sanitized_originaddress = $originAddresses ? sanitize_title(wp_unslash($originAddresses)) : sanitize_title(wp_unslash(frozr_norsani_cookies('locun')));
		$sanitized_destinationaddresses = sanitize_title(wp_unslash($destinationAddresses));
		$select = 'distance';
		if ($get_duration) {
			$select = 'duration';
		}

		$all_distances = $wpdb->get_var( $wpdb->prepare( 
			"
			SELECT $select
			FROM $table_name
			WHERE originAddresses = %s
				AND destinationAddresses = %s
			",
			$sanitized_originaddress,
			$sanitized_destinationaddresses
			
		));
		return $all_distances;
	}
	
	/**
	 * Get vendor email form
	 *
	 * @param int $seller_id
	 * @param bool $admin 			Are we showing the form to admin or not?
	 * @param bool $rest			Are we asking this form for a REST call or not?
	 * @param bool $hide_title		Get the form with it's title or not?
	 * @return void
	 */
	public function frozr_vendor_email_form($seller_id, $admin=false, $rest=false, $hide_title=false) {
		$info = frozr_get_store_info( $seller_id );
		$sellerstore = !empty($info['store_name']) ? $info['store_name'] : __('Vendor','frozr-norsani');
		
		ob_start();
		frozr_get_template('views/html-vendor-store_email_form.php', array('info' => $info, 'sellerstore' => $sellerstore, 'seller_id' => $seller_id, 'admin' => $admin, 'rest' => $rest, 'hide_title' => $hide_title));
		echo apply_filters('frozr_vendor_store_page_email_form_html',ob_get_clean(), $info, $sellerstore, $seller_id, $admin, $rest, $hide_title);
	}
	
	/**
	 * Check if product can be purchased at the current time according to item menu timing
	 *
	 * @param int $item_id
	 * @return bool
	 */
	public function frozr_check_item_timing($item_id) {

		$menu_type = ( null != (get_post_meta($item_id, 'product_meal_type', true )) ) ? get_post_meta($item_id, 'product_meal_type', true ) : array();
		$vendor = get_post_field('post_author',$item_id);
		$rest_loc_time = strtotime(current_time('H:i'));	
		$store_info = frozr_get_store_info($vendor);
		$meal_types = get_user_meta($vendor, '_rest_meal_types', true);
		$nw = new DateTime(date('H:i', $rest_loc_time));
		$gals_name = array();
		$filterd_opts = is_array($meal_types) ? array_filter($meal_types[0]) : array();
		$allow_ofline_orders = isset( $store_info['allow_ofline_orders'] ) ? esc_attr( $store_info['allow_ofline_orders'] ) : 'yes';

		if (empty($menu_type) || empty($filterd_opts)) {
		return true;
		}

		if (is_array($meal_types) && !empty($filterd_opts)) {
			foreach ($meal_types as $mvals){
			$startime = new DateTime($mvals['start']);
			$endtime = new DateTime($mvals['end']);
			if (intval(date('H:i',strtotime($mvals['start'])))== 0 && intval(date('H:i',strtotime($mvals['end']))) == 0) {
				return true;
			}
			if ($startime <= $nw && $nw < $endtime) {
				$gals_name[] = sanitize_title(wp_unslash($mvals['title']));
			}
			}
		}
		foreach($gals_name as $gal_name) {
		if (in_array($gal_name, $menu_type)) {
		return true;
		}
		}

		if ($this->frozr_vendor_manual_offline($vendor)) {
		return false;
		}
		if (frozr_is_rest_open($vendor) == false && frozr_manual_vendor_online()) {
		return false;
		}
		if (frozr_is_rest_open($vendor) == false && !isset($store_info['accpet_order_type_cl']) || frozr_is_rest_open($vendor) == false && isset($store_info['accpet_order_type_cl']['none']) || frozr_is_rest_open($vendor) == false && $allow_ofline_orders != 'yes') {
		return false;
		}

		if (frozr_is_rest_open($vendor) == false && isset($store_info['accpet_order_type_cl']) && $allow_ofline_orders == 'yes') {
		return true;
		}

		return false;
	}

	/**
	 * Check if product can be purchased at a given time in checkout page
	 *
	 * @param int $item_id
	 * @param string $time	24 hour time string i.e 16:20
	 * @return bool
	 */
	public function frozr_check_item_order_timing($item_id,$time) {
		$menu_type = ( null != (get_post_meta($item_id, 'product_meal_type', true )) ) ? get_post_meta($item_id, 'product_meal_type', true ) : array();
		$vendor = get_post_field('post_author',$item_id);
		$meal_types = get_user_meta($vendor, '_rest_meal_types', true);
		$filterd_opts = is_array($meal_types) ? array_filter($meal_types[0]) : array();
		$nw = new DateTime(date('H:i',strtotime($time)));
		$gals_name = array();

		if (empty($menu_type) || empty($filterd_opts)) {
		return true;
		}

		if (is_array($meal_types) && !empty($filterd_opts)) {
			foreach ($meal_types as $mvals){
			$startime = new DateTime($mvals['start']);
			$endtime = new DateTime($mvals['end']);
			if (intval(date('H:i',strtotime($mvals['start'])))== 0 && intval(date('H:i',strtotime($mvals['end']))) == 0) {
				return true;
			}
			$endtime->sub(new DateInterval('P0DT1H'));
			if ($startime <= $nw && $nw <= $endtime) {
				$gals_name[] = sanitize_title(wp_unslash($mvals['title']));
			}
			}
		}
		foreach($gals_name as $gal_name) {
		if (in_array($gal_name, $menu_type)) {
		return true;
		}
		}
		return false;
	}
	
	/**
	 * Get the time the product could be pickedup
	 *
	 * @param int $item_id
	 * @return bool|string
	 */
	public function frozr_get_item_timing($item_id) {
		$vendor = get_post_field('post_author',$item_id);

		if (frozr_is_rest_open($vendor)) {
		return false;
		}

		$rsts = $runds = $order_timing = false;
		$unds = ('' != get_user_meta($vendor, '_rest_unavds', true)) ? get_user_meta($vendor, '_rest_unavds', true) : array();
		$filterd_inds = array_filter($unds);
		$today = new DateTime(date(current_time('mysql')));

		if (!empty($filterd_inds)) {
		foreach ($filterd_inds as $unava_date => $unava_value) {
			if (!empty($unava_value['start']) && !empty ($unava_value['end'])) {
			$unstart_date = new DateTime(date($unava_value['start']));
			$unend_date = new DateTime(date($unava_value['end']));
			if ($unstart_date <= $today && $today < $unend_date) {
				$runds = $unava_value['end'];
				continue; 
			}
			}
		}
		}
		$restime = get_user_meta( $vendor, 'rest_open_close_time',true );

		$tm = current_time('D');

		$rest_open = isset($restime[$tm]['restop']) ? $restime[$tm]['restop'] : null;
		$rest_open_2 = isset($restime[date('D', strtotime('+1 day'))]['restop']) ? $restime[date('D', strtotime('+1 day'))]['restop'] : '';
		$rest_open_3 = isset($restime[date('D', strtotime('+2 days'))]['restop']) ? $restime[date('D', strtotime('+2 days'))]['restop'] : '';
		$rest_open_4 = isset($restime[date('D', strtotime('+3 days'))]['restop']) ? $restime[date('D', strtotime('+3 days'))]['restop'] : '';
		$rest_open_5 = isset($restime[date('D', strtotime('+4 days'))]['restop']) ? $restime[date('D', strtotime('+4 days'))]['restop'] : '';
		$rest_open_6 = isset($restime[date('D', strtotime('+5 days'))]['restop']) ? $restime[date('D', strtotime('+5 days'))]['restop'] : '';
		$rest_open_7 = isset($restime[date('D', strtotime('+6 days'))]['restop']) ? $restime[date('D', strtotime('+6 days'))]['restop'] : '';
		$txt_opens_on = __("on", 'frozr-norsani').' ';

		if ($runds) {
			$rsts = $txt_opens_on . $runds;
		} elseif ($rest_open_2) {
			$rsts = __("tomorrow", 'frozr-norsani').' ';
		} elseif ($rest_open_3) {
			$rsts = $txt_opens_on . date_i18n('l', strtotime('+2 days'));
		} elseif ($rest_open_4) {
			$rsts = $txt_opens_on . date_i18n('l', strtotime('+3 days'));
		} elseif ($rest_open_5) {
			$rsts = $txt_opens_on . date_i18n('l', strtotime('+4 days'));
		} elseif ($rest_open_6) {
			$rsts = $txt_opens_on . date_i18n('l', strtotime('+5 days'));
		} elseif ($rest_open_7) {
			$rsts = $txt_opens_on . date_i18n('l', strtotime('+6 days'));
		}

		$order_timing = $this->frozr_get_item_timings($vendor,$item_id);

		if (!empty($order_timing)) {
			$sep = ' '.__('or','frozr-norsani').' ';
			return $rsts.' - '.implode($sep,$order_timing);
		} elseif($rsts) {
			return $rsts;
		}
		return false;
	}
	
	/**
	 * Get the time the product could be pickedup
	 *
	 * @param int $author		Product author (vendor) id.
	 * @param int $product_id
	 * @return string
	 */
	public function frozr_get_item_timings($author,$product_id) {
		$meal_types = get_user_meta($author, '_rest_meal_types', true);
		$nw = new DateTime(date('H:i', strtotime(current_time('mysql'))));
		$filterd_opts = is_array($meal_types) ? array_filter($meal_types[0]) : array();
		$menu_type = ( null != (get_post_meta($product_id, 'product_meal_type', true )) ) ? get_post_meta($product_id, 'product_meal_type', true ) : array();
		$gals_name = false;
		$timing = array();
		
		if (is_array($meal_types) && !empty($filterd_opts) && !empty($menu_type)) {
			foreach ($meal_types as $mvals){
			$gals_name = sanitize_title(wp_unslash($mvals['title']));
			if (in_array($gals_name,$menu_type)) {
				$timing[] = date(frozr_get_time_date_format(),strtotime($mvals['start'])).' '.__('to','frozr-norsani').' '.date(frozr_get_time_date_format(),strtotime($mvals['end']) - (60*60));
			}
			}
		}
		return $timing;
	}
	
	/**
	 * Get the preperation time percentage to be used to calculate the total order preperation time
	 *
	 * @param int $vendor_id
	 * @return string
	 */
	public function frozr_get_vendor_prepare_percentage($vendor_id) {
		$percent = ( null != get_user_meta($vendor_id, 'calculate_prepare_time', true ) ) ? get_user_meta($vendor_id, 'calculate_prepare_time', true ) : 50;
		return floatval($percent);
	}
	
	/**
	 * Vendor status menu
	 *
	 * @return void
	 */
	public function frozr_norsani_vendor_status_menu(){
		$option = get_option( 'frozr_gen_settings' );
		$manual_online = (! empty( $option['frozr_manual_online_seller']) ) ? $option['frozr_manual_online_seller'] : 0;
		$max_unactive_time = null !== $option['frozr_auto_offline_max_time'] ? $option['frozr_auto_offline_max_time'] : 10;
		$vendor_id = get_current_user_id();
		$manual_status = get_user_meta($vendor_id,'frozr_vendor_manual_status',true) ? get_user_meta($vendor_id,'frozr_vendor_manual_status',true) : array('online'=>0,'time'=>date(current_time('mysql')));
		$notice_status = get_user_meta($vendor_id,'frozr_vendor_notice_status',true) ? ' active' : '';
		$online = __('online','frozr-norsani');
		$offline = __('offline','frozr-norsani');
		$active = false;
		if (frozr_is_rest_open($vendor_id)) {
			$cstatus = $online;
			$sstatus = $offline;
			$active = true;
		} else {
			$sstatus = $online;
			$cstatus = $offline;
		}

		$timing = $this->frozr_vendors_open_close($vendor_id,false);
		$restime = get_user_meta( $vendor_id, 'rest_open_close_time',true );
		$tm = current_time('D');
		$nw = new DateTime(date('H:i', strtotime(current_time('H:i'))));
		$nw_display = date_i18n(frozr_get_time_date_format('date_time'), strtotime(current_time('mysql')));
		$rest_open = isset($restime[$tm]['restop']) ? $restime[$tm]['restop'] : '';
		$rest_shifts = isset($restime[$tm]['restshifts']) ? $restime[$tm]['restshifts'] : null;
		$rest_opening_one = isset($restime[$tm]['open_one']) ? new DateTime(date('H:i', strtotime($restime[$tm]['open_one']))) : null;
		$rest_closing_one = isset($restime[$tm]['close_one']) ? new DateTime(date('H:i', strtotime($restime[$tm]['close_one']))) : null;
		$rest_opening_two = isset($restime[$tm]['open_two']) ? new DateTime(date('H:i', strtotime($restime[$tm]['open_two']))) : null;
		$rest_closing_two = isset($restime[$tm]['close_two']) ? new DateTime(date('H:i', strtotime($restime[$tm]['close_two']))) : null;
		$rsts = $rstst = $already_closed = false;

		if($rest_closing_one && $rest_opening_one) {
			if ($rest_shifts) {
				$message = __('Your first shift will start','frozr-norsani');
			} else {
				$message = __('Your timing for today is','frozr-norsani');
			}
			$rstst = date(frozr_get_time_date_format(), strtotime($restime[$tm]['open_one']));
			$rsts = date(frozr_get_time_date_format(), strtotime($restime[$tm]['close_one']));
		} elseif ($rest_shifts && $nw > $rest_closing_one && $nw < $rest_opening_two || $nw > $rest_opening_two && $nw < $rest_closing_two) {
			$message = __('Your second shift starts','frozr-norsani');
			$rstst = date(frozr_get_time_date_format(), strtotime($restime[$tm]['open_two']));
			$rsts = date(frozr_get_time_date_format(), strtotime($restime[$tm]['close_two']));
		} elseif ($nw > $rest_opening_two && $nw < $rest_closing_two) {
			$message = __('Your second shift timing is','frozr-norsani');
		} elseif ($nw > $rest_closing_two) {
			$already_closed = true;
			$message = sprintf(__('Your shop has closed today. You will open again %s','frozr-norsani'),$timing[0]);
		}
		
		$args = apply_filters('frozr_vendor_status_menu_args', array (
			'manual_online' => $manual_online,
			'max_unactive_time' => $max_unactive_time,
			'manual_status' => $manual_status,
			'notice_status' => $notice_status,
			'active' => $active,
			'cstatus' => $cstatus,
			'sstatus' => $sstatus,
			'timing' => $timing,
			'nw_display' => $nw_display,
			'rest_open' => $rest_open,
			'rest_shifts' => $rest_shifts,
			'rest_opening_one' => $rest_opening_one,
			'rest_closing_one' => $rest_closing_one,
			'rest_opening_two' => $rest_opening_two,
			'rest_closing_two' => $rest_closing_two,
			'rest_shifts' => $rest_shifts,
			'rstst' => $rstst,
			'rsts' => $rsts,
			'message' => $message,
			'already_closed' => $already_closed,
		));
		
		ob_start();
		frozr_get_template('views/html-dashboard-vendor-status_menu.php', $args);
		echo apply_filters('frozr_dashboard_vendor_status_menu_html',ob_get_clean(), $args);
	}
	
	/**
	 * Check if vendor is manually online
	 *
	 * @return bool
	 */
	public function frozr_manual_vendor_online() {
		$option = get_option( 'frozr_gen_settings' );
		$manual_online = (! empty( $option['frozr_manual_online_seller']) ) ? $option['frozr_manual_online_seller'] : 0;
		if ($manual_online) {
			return true;
		}
		return false;
	}
	
	/**
	 * Check if vendor is manually offline
	 *
	 * @param int $seller_id
	 * @return bool
	 */
	public function frozr_vendor_manual_offline($seller_id) {
		$manual_status = get_user_meta($seller_id,'frozr_vendor_manual_status',true) ? get_user_meta($seller_id,'frozr_vendor_manual_status',true) : array('online'=>1,'time'=>date(current_time('mysql')));
		if ('off' == $manual_status['online']) {
			return true;
		}
		return false;
	}

}