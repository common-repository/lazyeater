<?php
/**
 * Ajax handler for Norsani
 *
 * @package Norsani
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

class Norsani_Ajax {
	
	/**
	 * The single instance of the class.
	 *
	 * @var Norsani_Ajax
	 * @since 1.9
	 */
	protected static $_instance = null;
	
	/**
	 * Main Norsani_Ajax Instance.
	 *
	 * @since 1.9
	 * @static
	 * @return Norsani_Ajax - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Norsani_Ajax Constructor.
	 */
	public function __construct() {
		$this->init();

		do_action( 'norsani_ajax_loaded' );
	}
	
    /**
     * Init ajax handlers
     *
     * @return void
     */
    public function init() {
		
		do_action('before_frozr_init_ajax');

		add_action( 'wp_ajax_frozr_ajax_add_to_cart', array( $this, 'frozr_ajax_add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_frozr_ajax_add_to_cart', array( $this, 'frozr_ajax_add_to_cart' ) );
		add_action( 'wp_ajax_frozr_seller_settings', array( $this, 'frozr_seller_settings' ) );
		add_action( 'wp_ajax_frozr_save_vendor_settings', array( $this, 'save_vendor_settings' ) );
		add_action( 'wp_ajax_frozr_grant_access_to_download', array( $this, 'grant_access_to_download' ) );
		add_action( 'wp_ajax_frozr_contact_seller', array( $this, 'contact_seller' ) );
		add_action( 'wp_ajax_nopriv_frozr_contact_seller', array($this, 'contact_seller') );
		add_action( 'wp_ajax_frozr_delete_item', array( $this, 'delete_item' ) );
		add_action( 'wp_ajax_nopriv_frozr_rating_login', array( $this, 'rating_login' ) );
		add_action( 'wp_ajax_frozr_get_totals_data', array( $this, 'get_totals_data' ) );
		add_action( 'wp_ajax_frozr_print_summary_report', array( $this, 'dash_print_summary_report' ) );
		add_action( 'wp_ajax_frozr_print_order', array( $this, 'dash_print_order' ) );
		add_action( 'wp_ajax_frozr_save_rest_rating', array( $this, 'save_rest_rating') );
		add_action( 'wp_ajax_frozr_update_product', array( $this, 'update_product' ) );
		add_action( 'wp_ajax_nopriv_frozr_adv_loc_filter', array( $this, 'frozr_adv_loc_filter' ) );
		add_action( 'wp_ajax_frozr_adv_loc_filter', array( $this, 'frozr_adv_loc_filter' ) );
		add_action( 'wp_ajax_frozr_send_rest_invitation', array( $this, 'frozr_send_rest_invitation' ) );
		add_action( 'wp_ajax_frozr_add_fee_setting_row', array( $this, 'frozr_add_fee_setting_row' ) );
		add_action( 'wp_ajax_frozr_add_product_variation', array($this, 'frozr_add_product_variation') );
		add_action( 'wp_ajax_frozr_get_updated_product', array($this, 'frozr_get_updated_product') );
		add_action( 'wp_ajax_frozr_add_rest_to_fav', array($this, 'frozr_add_rest_to_fav') );
		add_action( 'wp_ajax_frozr_add_item_to_fav', array($this, 'frozr_add_item_to_fav') );
		add_action( 'wp_ajax_frozr_set_vendor_status', array($this,'frozr_set_vendor_status') );
		add_action( 'wp_ajax_frozr_check_new_orders', array($this,'frozr_check_new_orders') );
		add_action( 'wp_ajax_frozr_change_item_status', array($this,'frozr_change_item_status') );
		add_action( 'wp_ajax_frozr_make_item_special', array($this,'frozr_make_item_special') );
		add_action( 'wp_ajax_frozr_update_orders_count', array($this,'frozr_update_orders_count') );

		add_action( 'wp_ajax_nopriv_shop_url', array($this, 'shop_url_check') );
		add_action( 'wp_ajax_frozr_user_loc_cookie', array($this, 'user_loc_cookie') );
		add_action( 'wp_ajax_nopriv_frozr_user_loc_cookie', array($this, 'user_loc_cookie') );
		
		add_action( 'wp_ajax_frozr_get_delivery_info', array($this, 'frozr_get_delivery_info') );
		add_action( 'wp_ajax_nopriv_frozr_get_delivery_info', array($this, 'frozr_get_delivery_info') );
		
		add_action( 'wp_ajax_frozr_get_sellers_locs', array($this, 'frozr_get_sellers_locs') );
		add_action( 'wp_ajax_nopriv_frozr_get_sellers_locs', array($this, 'frozr_get_sellers_locs') );

		add_action( 'wp_ajax_frozr_item_remove_from_cart', array($this, 'frozr_item_remove_from_cart') );
		add_action( 'wp_ajax_nopriv_frozr_item_remove_from_cart', array($this, 'frozr_item_remove_from_cart') );
	
		add_action( 'wp_ajax_frozr_get_resturants_data', array($this, 'frozr_get_resturants_data') );
		add_action( 'wp_ajax_nopriv_frozr_get_resturants_data', array($this, 'frozr_get_resturants_data') );
		
		add_action( 'wp_ajax_frozr_get_near_vendors_data', array($this, 'frozr_get_near_vendors_data') );
		add_action( 'wp_ajax_nopriv_frozr_get_near_vendors_data', array($this, 'frozr_get_near_vendors_data') );
	
		add_action( 'wp_ajax_frozr_add_new_distance', array($this, 'frozr_add_new_distance') );
		add_action( 'wp_ajax_nopriv_frozr_add_new_distance', array($this, 'frozr_add_new_distance') );
		
		add_action( 'wp_ajax_frozr_save_item_view_count', array($this, 'frozr_save_item_view_count') );
		add_action( 'wp_ajax_nopriv_frozr_save_item_view_count', array($this, 'frozr_save_item_view_count') );
		
		add_action( 'wp_ajax_frozr_add_new_distance_bulk', array($this, 'frozr_add_new_distance_bulk') );
		add_action( 'wp_ajax_nopriv_frozr_add_new_distance_bulk', array($this, 'frozr_add_new_distance_bulk') );
		
		add_action( 'wp_ajax_frozr_coupons_create', array($this, 'frozr_coupons_create') );
		add_action( 'wp_ajax_frozr_coupun_delete', array($this, 'frozr_coupun_delete') );

		add_action( 'wp_ajax_frozr_add_order_note',  array($this, 'frozr_add_order_note') );
		add_action( 'wp_ajax_frozr_delete_order_note', array($this, 'frozr_delete_order_note') );
		add_action( 'wp_ajax_frozr_set_order_status', array($this, 'frozr_set_order_status') );
		
		add_action( 'wp_ajax_frozr_delete_withdraw', array($this, 'frozr_delete_withdraw') );
		add_action( 'wp_ajax_frozr_cancel_payout',  array($this, 'frozr_cancel_payout') );
		add_action( 'wp_ajax_frozr_save_withdraw', array($this, 'frozr_save_withdraw') );
		add_action('wp_ajax_frozr_process_payout', array($this, 'frozr_process_payout') );
		add_action( 'wp_ajax_frozr_check_withdraws', array($this, 'frozr_check_withdraws') );

		do_action('after_frozr_init_ajax');
	}
	
	/*Update payout withdraws list*/
	function frozr_check_withdraws() {
		check_ajax_referer( 'frozr_check_payouts_sec', 'security' );
		$option = get_option( 'frozr_withdraw_settings' );
		$pay_instantly = isset($option['frozr_pay_vendors_instantly_paypal']) ? 1 : null;
		$clientid = ! empty( $option['frozr_paypal_clientid']) ? $option['frozr_paypal_clientid'] : null;
		$clientsecret = ! empty( $option['frozr_paypal_clientsecret']) ? $option['frozr_paypal_clientsecret'] : null;
		$author = (!is_super_admin()) ? get_current_user_id() : '';

		if( !isset($pay_instantly) || empty($clientid) || empty($clientsecret) ) {
			die(-1);
		}

		require_once NORSANI_PATH . '/bootstrap.php';

		$apiContext = getApiContext($clientId, $clientSecret);
		$payouts = new \PayPal\Api\Payout();
		$args = array(
			'post_type' => 'frozr_withdraw',
			'post_status' => 'pending',
			'posts_per_page' => -1,
			'author' => $author,
		);
		$withdraws = get_posts(apply_filters('frozr_withdraws_tocheck', $args));
		$trashed_payouts = array();
		$updated_payouts = array();
		foreach ($withdraws as $withdraw) {
			$payoutBatchId = !empty(get_post_meta($withdraw->ID, 'wid_tras_id',true)) ? get_post_meta($withdraw->ID, 'wid_tras_id',true) : false;
			$vendor = $withdraw->post_author;
			$vendor_profit = !empty(get_post_meta($withdraw->ID, 'wid_req_amount_sec',true)) ? get_post_meta($withdraw->ID, 'wid_req_amount_sec',true) : 0;
			if ($payoutBatchId) {
				try {
					$payoutBatch = $payouts->get($payoutBatchId, $apiContext);
					$payoutItems = $payoutBatch->getItems();
					$payoutItem = $payoutItems[0];
					$tras_status = $payoutItem->getTransactionStatus();
					update_post_meta( $withdraw->ID, 'wid_tras_sts', $tras_status);
					if ($tras_status == 'SUCCESS') {
						$wid_stat = 'completed';
						$user_current_balance = get_user_meta($vendor,"_vendor_balance", true);
						$seller_new_balance = $user_current_balance - floatval($vendor_profit);
						update_user_meta($vendor, "_vendor_balance",$seller_new_balance);
					} elseif($tras_status != 'ONHOLD' && $tras_status != 'PENDING' && $tras_status != 'UNCLAIMED') {
						$trashed_payouts[] = $withdraw->ID;
						update_post_meta( $withdraw->ID, 'wid_req_del_note', __('The payout was canceled','frozr-norsani'));
						$request_info = array(
							'ID' => $withdraw->ID,
							'post_status' => 'trash',
							'comment_status' => 'closed'
						);
						wp_update_post( $request_info );
					} else {
						$updated_payouts[$withdraw->ID] = $tras_status;
					}
				} catch (Exception $ex) {
					/*die silently*/
					die(-1);
				}
			}
		}
		$no_result = false;
		if (count($withdraws) == 1 && count($trashed_payouts) == 1) {
			$no_result = true;
			wp_send_json(array('no_result'=>$no_result));
			die();
		}
		if (is_super_admin()) {
			$post_counts = wp_count_posts( 'frozr_withdraw' );
		} else {
			$post_counts = frozr_count_posts( 'frozr_withdraw', get_current_user_id() );
		}
		$wids_count = array(
		'pending' => $post_counts->pending,
		'completed' => $post_counts->completed,
		'trashed' => $post_counts->trash,
		);

		wp_send_json(array('nav'=>$wids_count,'trashed'=>$trashed_payouts,'updated'=>$updated_payouts));

		die();
	}
	
	/*Process PayPal Payout*/
	function frozr_process_payout() {
		check_ajax_referer( 'frozr_process_inst_payment_payout', 'security' );

		if ( !is_super_admin() ) {
		die(-1);
		}

		$option = get_option( 'frozr_withdraw_settings' );
		$clientid = ! empty( $option['frozr_paypal_clientid']) ? $option['frozr_paypal_clientid'] : null;
		$clientsecret = ! empty( $option['frozr_paypal_clientsecret']) ? $option['frozr_paypal_clientsecret'] : null;
		$minimum_withdraw_balance = ! empty( $option['frozr_minimum_withdraw_balance']) ? $option['frozr_minimum_withdraw_balance'] : 50;
		$wid_id = isset($_POST['wid_id'])? intval($_POST['wid_id']): null;
		$batchid = get_post_meta($wid_id, 'wid_tras_id',true);

		if(null == $wid_id) {
		wp_send_json(array('error'=>__('Something went wrong.','frozr-norsani')));
		die(-1);
		}

		if( empty($clientid) || empty($clientsecret)) {
		wp_send_json(array('error'=>__("Please enter your 'PayPal App clientId' and 'Secret' in Norsani's admin Withdrawals Requests Settings.","frozr-norsani")));
		die(-1);
		}

		if ($batchid) {
		wp_send_json(array('error'=>__('Payment is already processing. Please refresh page to see status.','frozr-norsani')));
		die(-1);
		}

		require_once NORSANI_PATH . '/bootstrap.php';

		// Create a new instance of Payout object
		$payouts = new \PayPal\Api\Payout();
		$website_name = esc_attr(get_bloginfo('name', 'display'));
		$wid_post = get_post( $wid_id );
		$wid_author = $wid_post->post_author;
		$vendor = get_user_by( 'id', $wid_author );
		$store_settings = frozr_get_store_info( $wid_author );
		$email = isset( $store_settings['payment']['paypal']['email'] ) ? esc_attr( $store_settings['payment']['paypal']['email'] ) : $vendor->user_email;
		$currency = get_woocommerce_currency();
		$payout_title = __('Payment for order: #','frozr-norsani').$wid_id;
		$vendor_profit = !empty(get_post_meta($wid_id, 'wid_req_amount',true)) ? get_post_meta($wid_id, 'wid_req_amount',true) : 0;

		/* Check permissions again and make sure we have what we need*/
		if ($minimum_withdraw_balance > floatval(get_user_meta($wid_author,"_vendor_balance", true))) {
		wp_send_json(array('error'=>__("The available funds in the vendor's account is lower than the minimum amount to make a withdraw request.","frozr-norsani")));
		die(-1);
		}

		if ($vendor_profit == 0) {
		wp_send_json(array('error'=>__('The amount of withdraw is 0','frozr-norsani')));
		die(-1);
		}

		if ($vendor_profit > floatval(get_user_meta($wid_author,"_vendor_balance", true))) {
		wp_send_json(array('error'=>__("Insufficient funds in the vendor's account.","frozr-norsani")));
		die(-1);
		}

		$senderBatchHeader = new \PayPal\Api\PayoutSenderBatchHeader();
		$senderBatchHeader->setSenderBatchId(uniqid())->setEmailSubject($website_name.' '.__("Order Payment",'frozr-norsani'));

		$senderItem = new \PayPal\Api\PayoutItem(
			array(
				"recipient_type" => "EMAIL",
				"receiver" => $email,
				"note" => $payout_title,
				"sender_item_id" => uniqid(),
				"amount" => array(
					"value" => $vendor_profit,
					"currency" => $currency
				)
			)
		);
		$payouts->setSenderBatchHeader($senderBatchHeader)->addItem($senderItem);
		$apiContext = getApiContext($clientId, $clientSecret);
		$tras_status = 'pending';
		try {
			$output = $payouts->create(null,$apiContext);
			$tras_id = $output->getBatchHeader()->getPayoutBatchId();
			$tras_status = $output->getBatchHeader()->getBatchStatus();
			$wid_stat = 'pending';
			
			if ($tras_status == 'SUCCESS') {
				$wid_stat = 'completed';
				$user_current_balance = get_user_meta($wid_author,"_vendor_balance", true);
				$seller_new_balance = $user_current_balance - floatval($vendor_profit);
				update_user_meta($wid_author, "_vendor_balance",$seller_new_balance);
			}
			
			$request_info = array(
				'ID' => $wid_id,
				'post_status' => $wid_stat,
				'comment_status' => 'closed'
			);
			wp_update_post( $request_info );

			update_post_meta( $wid_id, 'wid_req_amount_sec', $vendor_profit);
			update_post_meta( $wid_id, 'wid_tras_id', $tras_id);

		} catch (Exception $ex) {
			$data = null;
			if ($ex instanceof \PayPal\Exception\PayPalConnectionException) {
				$data = $ex->getData();
			}
			$note = frozr_get_payout_error($data,$ex->getMessage());
			wp_send_json(array('error'=>$note));
			die(-1);
		}
		$wids_count = array();
		$no_result = false;
		if ($tras_status == 'SUCCESS') {
			$post_counts = wp_count_posts( 'frozr_withdraw' );
			$p_count = $post_counts->pending;
			if ($p_count == 0) {
				$no_result = true;
				wp_send_json(array('no_result'=>$no_result));
				
				die();
			}

			$t_count = $post_counts->trash;
			$wids_count = array(
			'pending' => $p_count,
			'completed' => $post_counts->completed,
			'trashed' => $t_count,
			);
		}
		wp_send_json(array('sts'=>$tras_status,'message'=>__('The payment is currently processing. Please refresh the page to see status.','frozr-norsani'),'nav'=>$wids_count,'no_result'=>$no_result));
		die();
	}

	/**
	 * Save withdraw request
	 *
	 */
	function frozr_save_withdraw() {
		
		ob_start();

		check_ajax_referer( 'save_fro_withdraw', 'security' );

		if (empty ($_POST['withdraw_id'])) {
			$args = array(
				'post_type' => 'frozr_withdraw',
				'post_status' => 'pending',
				'author' => get_current_user_id(),
			);
			$withdraw_query = get_posts( apply_filters( 'frozr_withdraw_listing_query', $args ) );

			$fle_option = get_option( 'frozr_withdraw_settings' );
			$minimum_withdraw_balance = (! empty( $fle_option['frozr_minimum_withdraw_balance']) ) ? $fle_option['frozr_minimum_withdraw_balance'] : 50;

			/* Check permissions again and make sure we have what we need*/
			if ( is_super_admin() || !current_user_can( 'frozer' ) && !frozr_is_seller_enabled(get_current_user_id()) || $minimum_withdraw_balance > floatval(get_user_meta(get_current_user_id(),"_vendor_balance", true)) || !empty($withdraw_query)) {
				die( -1 );
			}
			$withdraw_info = apply_filters('frozr_save_new_withdraw_data',array(
				'post_type' => 'frozr_withdraw',
				'post_status' => 'pending',
			));

			$withdraw_id = wp_insert_post( $withdraw_info );
			$withdraw_post = get_post( $withdraw_id );
			$vendor = get_user_by( 'id', $withdraw_post->post_author );
			
			if ( isset( $_POST['withdraw_amount'] ) ) {
				update_post_meta( $withdraw_post->ID, 'wid_req_amount', ( $_POST['withdraw_amount'] === '' ? '' : wc_format_decimal( $_POST['withdraw_amount'] ) ) );
			}
			if ( isset( $_POST['withdraw_method'] ) ) {
				update_post_meta( $withdraw_post->ID, 'wid_req_via', ( $_POST['withdraw_method'] === '' ? '' : wc_clean( $_POST['withdraw_method'] ) ) );
			}

		} else {
			$wid_id = intval($_POST['withdraw_id']);
			$withdraw_post = get_post( $wid_id );
			$author = $withdraw_post->post_author;
			$vendor = get_user_by( 'id', $author );
			$payoutBatchId = !empty(get_post_meta($wid_id, 'wid_tras_id',true)) ? get_post_meta($wid_id, 'wid_tras_id',true) : false;
			/* Check permissions again and make sure we have what we need*/
			if ($payoutBatchId) {
				die( -1 );
			}
			/* Check permissions again and make sure we have what we need*/
			if ( !current_user_can( 'frozer' ) && !frozr_is_seller_enabled(get_current_user_id()) || empty( $withdraw_post->ID ) || $author != get_current_user_id() && !is_super_admin() || $withdraw_post->post_status == 'completed' && !is_super_admin() || $withdraw_post->post_status == 'trash' && !is_super_admin() ) {
				die( -1 );
			}
			if (is_super_admin() && $withdraw_post->post_status == 'completed') {
				/** set images **/
				if (isset($_POST['wid_image_id']) && is_super_admin()) {
					$wid_invoice = absint( $_POST['wid_image_id'] );
				}
				if ( $wid_invoice ) {
					set_post_thumbnail( $withdraw_post->ID, $wid_invoice );
				}
			} else {
			if (isset($_POST['withdraw_status']) && is_super_admin()) {
				$wid_stat = wc_clean($_POST['withdraw_status']);
			} else {
				$wid_stat = 'pending';
			}
			$withdraw_info = apply_filters('frozr_save_withdraw_data',array(
				'ID' => $withdraw_post->ID,
				'post_status' => $wid_stat,
			));
			if ($wid_stat == 'completed') {
			
				$user_current_balance = wc_format_decimal(get_user_meta($author,"_vendor_balance", true));
				$seller_new_balance = $user_current_balance - wc_format_decimal( $_POST['withdraw_amount'] );
				update_user_meta($author, "_vendor_balance",$seller_new_balance);
			
			}
			wp_update_post( $withdraw_info );
			
			/** set images **/
			if (isset($_POST['wid_image_id']) && is_super_admin()) {
				$wid_invoice = absint( $_POST['wid_image_id'] );
			}
			if ( $wid_invoice ) {
				set_post_thumbnail( $withdraw_post->ID, $wid_invoice );
			}

			if ( isset( $_POST['withdraw_amount'] ) ) {
				update_post_meta( $withdraw_post->ID, 'wid_req_amount', ( $_POST['withdraw_amount'] === '' ? '' : wc_format_decimal( $_POST['withdraw_amount'] ) ) );
			}
			if ( isset( $_POST['withdraw_method'] ) ) {
				update_post_meta( $withdraw_post->ID, 'wid_req_via', ( $_POST['withdraw_method'] === '' ? '' : wc_clean( $_POST['withdraw_method'] ) ) );
			}
			if ( isset( $_POST['wid_reject_note']) && is_super_admin() ) {
				update_post_meta( $withdraw_post->ID, 'wid_req_del_note', ( $_POST['wid_reject_note'] === '' ? '' : wc_clean( $_POST['wid_reject_note'] ) ) );
			}
				
			}
		}
		
		do_action('frozr_withdraw_saved', $vendor, $withdraw_post, $_POST);

		die();
	}
	
	/**
	 * Cancel withdraw payout
	 *
	 */
	function frozr_cancel_payout() {
		ob_start();

		check_ajax_referer( 'cancel_fro_withdraw', 'security' );
		$wid_id = intval($_POST['payout_id']);
		$withdraw_post = get_post( $wid_id );
		$option = get_option( 'frozr_withdraw_settings' );
		$clientid = ! empty( $option['frozr_paypal_clientid']) ? $option['frozr_paypal_clientid'] : null;
		$clientsecret = ! empty( $option['frozr_paypal_clientsecret']) ? $option['frozr_paypal_clientsecret'] : null;
		$author = $withdraw_post->post_author;
		$payoutBatchId = !empty(get_post_meta($wid_id, 'wid_tras_id',true)) ? get_post_meta($wid_id, 'wid_tras_id',true) : false;
		/* Check permissions again and make sure we have what we need*/
		if (!$payoutBatchId) {
			wp_send_json(array('error'=>__('Something went wrong!','frozr-norsani')));	
			die( -1 );
		}
		if ( empty($clientid) || empty($clientsecret) || !current_user_can( 'frozer' ) && !frozr_is_seller_enabled(get_current_user_id()) || empty( $withdraw_post->ID ) || $author != get_current_user_id() && !is_super_admin() || $withdraw_post->post_status != 'pending' && !is_super_admin() || $withdraw_post->post_status == 'completed' ) {
			wp_send_json(array('error'=>__('Something went wrong!','frozr-norsani')));	
			die( -1 );
		}
		require_once NORSANI_PATH . '/bootstrap.php';
		$message = __('Payout was canceled and moved to the "canceled list".','frozr-norsani');
		$error = false;
		$apiContext = getApiContext($clientId, $clientSecret);
		$payouts = new \PayPal\Api\Payout();
		$payoutBatch = $payouts->get($payoutBatchId, $apiContext);
		$payoutItems = $payoutBatch->getItems();
		$payoutItem = $payoutItems[0];
		$payoutItemId = $payoutItem->getPayoutItemId();
		$tras_status = $payoutItem->getTransactionStatus();
		try {
		if ($tras_status != 'SUCCESS') {
			$output = \PayPal\Api\PayoutItem::cancel($payoutItemId, $apiContext);
			$tras_status = $output->getTransactionStatus();
			update_post_meta( $wid_id, 'wid_tras_sts', $tras_status);
			update_post_meta( $wid_id, 'wid_req_del_note', __('Payout was canceled by Admin','frozr-norsani'));
			$request_info = array(
				'ID' => $wid_id,
				'post_status' => 'trash',
				'comment_status' => 'closed'
			);
			wp_update_post( $request_info );
		} else {
			$message = __('Payout already completed and cannot be canceled.','frozr-norsani');
		}
		} catch (Exception $ex) {
			$data = null;
			if ($ex instanceof \PayPal\Exception\PayPalConnectionException) {
				$data = $ex->getData();
			}
			$error = frozr_get_payout_error($data,$ex->getMessage());
			if (is_super_admin()) {
			wp_send_json(array('error'=>$error));
			} else {
			wp_send_json(array('error'=>__('Something went wrong!','frozr-norsani')));	
			}
			die(-1);
		}
		
		if (is_super_admin()) {
			$post_counts = wp_count_posts( 'frozr_withdraw' );
		} else {
			$post_counts = frozr_count_posts( 'frozr_withdraw', get_current_user_id() );
		}
		
		$p_count = $post_counts->pending;
		$no_result = false;
		if ($p_count == 0) {
		$no_result = true;
		wp_send_json(array('no_result'=>$no_result));
		die();
		}
		
		$t_count = $post_counts->trash;
		$wids_count = array(
		'pending' => $p_count,
		'completed' => $post_counts->completed,
		'trashed' => $t_count,
		);

		wp_send_json(array('message'=>$message,'nav'=>$wids_count,'no_result'=>$no_result));
		die();
	}

	/**
	 * Delete withdraw request
	 */
	public function frozr_delete_withdraw() {
		ob_start();

		check_ajax_referer( 'delete_fro_withdraw', 'security' );
		$wid_id = intval($_POST['withdraw_id']);
		$withdraw_post = get_post( $wid_id );
		$payoutBatchId = !empty(get_post_meta($wid_id, 'wid_tras_id',true)) ? get_post_meta($wid_id, 'wid_tras_id',true) : false;
		$payoutStatus = !empty(get_post_meta($wid_id, 'wid_tras_sts',true)) ? get_post_meta($wid_id, 'wid_tras_sts',true) : __('pending','frozr-norsani');
		$author = $withdraw_post->post_author;
		/* Check permissions again and make sure we have what we need*/
		if ($payoutBatchId && $payoutStatus != 'returned' || $payoutBatchId && $payoutStatus != 'completed') {
			die(-1);
		}
		if ( !current_user_can( 'frozer' ) && !frozr_is_seller_enabled(get_current_user_id()) || empty( $withdraw_post->ID ) || $author != get_current_user_id() && !is_super_admin() || $withdraw_post->post_status != 'pending' && !is_super_admin() || $withdraw_post->post_status == 'completed' ) {
			die( -1 );
		}
		wp_delete_post( $withdraw_post->ID );
		
		die();
	}
	/*some ajax function to process the order actions*/
	function frozr_set_order_status() {

		check_ajax_referer( 'set_order_status', 'security' );
		
		$order_post = get_post( intval($_POST['order_id']) );
		$author = frozr_get_order_author(intval($_POST['order_id']));

		/* Check permissions again and make sure we have what we need*/
		if ( !current_user_can( 'frozer' ) && !frozr_is_seller_enabled(get_current_user_id()) || empty( $order_post->ID ) || $author != get_current_user_id() && !is_super_admin()) {
			die( -1 );
		}
		$order = wc_get_order( intval($_POST['order_id']) );
		
		if ($_POST['order_sts'] == 'refunded' && $order_post->post_status != 'wc-completed' && $order_post->post_status != 'wc-refunded') {
			$order->update_status( 'refunded', __( 'The order should be refunded. Customer, please send a message to', 'frozr-norsani' ) .' '. get_option( 'admin_email' ) .' '.__('in reference to this order ID and your payment account details and method to complete refund.','frozr-norsani') );
		} elseif ($_POST['order_sts'] == 'cancelled' && $order_post->post_status != 'wc-completed' && $order_post->post_status != 'wc-refunded' && $order_post->post_status != 'wc-cancelled') {
			$order->update_status( 'cancelled', __( 'The order has been cancelled.', 'frozr-norsani' ) );
		} elseif ($_POST['order_sts'] == 'pending' && $order_post->post_status != 'wc-completed' && $order_post->post_status != 'wc-refunded' && $order_post->post_status != 'wc-cancelled' && $order_post->post_status != 'wc-pending') {
			$order->update_status( 'pending', __( 'The order is being prepared.', 'frozr-norsani' ) );
		} elseif ($_POST['order_sts'] == 'processing' && $order_post->post_status != 'wc-completed' && $order_post->post_status != 'wc-refunded' && $order_post->post_status != 'wc-cancelled' && $order_post->post_status != 'wc-processing') {
			$order->update_status( 'processing', __( 'The order is on its way to customer.', 'frozr-norsani' ) );
		} elseif ($_POST['order_sts'] == 'completed' && $order_post->post_status != 'wc-completed' && $order_post->post_status != 'wc-refunded' && $order_post->post_status != 'wc-cancelled' ) {
			$order->update_status( 'completed', __( 'Customer has received the order.', 'frozr-norsani' ) );
		} elseif ($_POST['order_sts'] == 'failed' && $order_post->post_status != 'wc-completed' && $order_post->post_status != 'wc-refunded' ) {
			$order->update_status( 'failed', __( 'The order was failed to process.', 'frozr-norsani' ) );
		}

		die();
	}
	/**
	 * Delete order note via ajax
	 */
	function frozr_delete_order_note() {

		check_ajax_referer( 'delete-order-note', 'security' );

		if ( ! current_user_can( 'frozer' ) ) {
			die(-1);
		}

		$note_id = (int) $_POST['note_id'];

		if ( $note_id > 0 ) {
		wp_delete_comment( $note_id );
		}

		/* Quit out*/
		die();
	}
	/**
	 * Add order note via ajax
	 */
	function frozr_add_order_note() {

		check_ajax_referer( 'add-order-note', 'security' );

		$order_post = get_post( intval($_POST['post_id']) );
		$author = frozr_get_order_author($order_post->ID);

		if ( ! current_user_can( 'frozer' ) || $author != get_current_user_id() && !is_super_admin() ) {
			die(-1);
		}

		$post_id   = absint( $_POST['post_id'] );
		$note      = wp_kses_post( trim( stripslashes( $_POST['note'] ) ) );
		$note_type = $_POST['note_type'];

		$is_customer_note = $note_type == 'customer' ? 1 : 0;

		if ( $post_id > 0 ) {
			$order      = wc_get_order( $post_id );
			$comment_id = $order->add_order_note( $note, $is_customer_note, true );

			echo '<li rel="' . esc_attr( $comment_id ) . '" class="note ';
			if ( $is_customer_note ) {
				echo 'customer-note';
			}
			echo '"><div class="note_content">';
			echo wpautop( wptexturize( $note ) );
			echo '</div><p class="meta"><a href="#" class="delete_note">'.__( 'Delete note', 'frozr-norsani' ).'</a></p>';
			echo '</li>';
		}

		/* Quit out*/
		die();
	}
	/*Delete Coupon*/
	function frozr_coupun_delete() {
		
		check_ajax_referer( 'coupon_del_nonce', 'security' );

		$seller_id = get_current_user_id();
		$post_id = intval($_POST['post_id']);
		if ( ! frozr_is_author( $post_id ) && !is_super_admin() || ! frozr_is_seller_enabled($seller_id) && ! is_super_admin()) {
			wp_send_json_error(__('Something Went Wrong!','frozr-norsani'));
			die( -1 );
		}
		
		wp_delete_post( $post_id , true );

		/*send WC notices*/
		wp_send_json_success( $post_id . ' ' . __( 'Coupon has been deleted successfully!', 'frozr-norsani' ));
	}
	/*Save coupon*/
	function frozr_coupons_create() {
		ob_start();

		check_ajax_referer( 'coupon_nonce_field', 'security' );
		$posts_ids = array_map('wc_clean', $_POST['product_drop_down']);
		
		/* Check permissions and make sure we have what we need*/
		if ( !current_user_can( 'frozer' ) || !frozr_is_seller_enabled(get_current_user_id()) && !is_super_admin() || count($posts_ids) == 0 ) {
			$message = __('Something Went Wrong!','frozr-norsani');
			wp_send_json_error($message);
			die( -1 );
		}
		global $post;
		$seller_id = get_current_user_id();

		$product_ids_query = get_posts( array(
			'posts_per_page'	=> -1,
			'post_type'			=> 'product',
			'post_status'		=> array( 'publish' ),
			'author'			=> $seller_id,
			'fields'			=> 'ids',
		));

		/* Check permissions again and make sure we have what we need*/
		if ( count(array_intersect(array_map( 'intval', (array) $_POST['product_drop_down'] ), $product_ids_query)) != count(array_map( 'intval', (array) $_POST['product_drop_down'] )) && !is_super_admin() ) {
			$message = __('You are not allowed to edit/create coupons for the selected products!','frozr-norsani');
			wp_send_json_error($message);
			die( -1 );
		}

		$product_ids = implode( ',', array_filter( array_map( 'intval', (array) $_POST['product_drop_down'] ) ) );

		if ( empty( $_POST['post_id'] ) ) {

			if ( is_super_admin() ) {
				$message = __('You\'re the site admin, you cant post coupons!','frozr-norsani');
				wp_send_json_error($message);
				die( -1 );
			}

			$post = apply_filters('frozr_coupons_create_args',array(
			'post_title' => sanitize_title($_POST['title']),
			'post_content' => sanitize_text_field($_POST['description']),
			'post_status' => 'publish',
			'post_type' => 'shop_coupon',
			));

			$post_id = wp_insert_post( $post );

			$message = __('Coupon has been saved successfully!','frozr-norsani');

		} else {

			if ( ! frozr_is_author( $_POST['post_id'] ) && ! is_super_admin() ) {
				$message = __('Something Went Wrong!','frozr-norsani');
				wp_send_json_error($message);
				die( -1 );
			}
			$post = apply_filters('frozr_coupons_update_args',array(
			'ID' => intval($_POST['post_id']),
			'post_title' => sanitize_title($_POST['title']),
			'post_content' => sanitize_text_field($_POST['description']),
			'post_status' => 'publish',
			'post_type' => 'shop_coupon',
			));
			
			$post_id = wp_update_post( $post );
			$message = __('Coupon has been updated successfully!','frozr-norsani');
		}

		if ( !$post_id ) {
			$message = __('Something Went Wrong!','frozr-norsani');
			wp_send_json_error($message);
			die( -1 );
		}
		
		$customer_email = array_filter( array_map( 'trim', explode( ',', sanitize_text_field( $_POST['email_restrictions'] ) ) ) );
		$type = sanitize_text_field( $_POST['discount_type'] );
		$amount = sanitize_text_field( $_POST['amount'] );
		$usage_limit = empty( $_POST['usage_limit'] ) ? '' : absint( $_POST['usage_limit'] );
		$usage_limit_per_user   = empty( $_POST['usage_limit_per_user'] ) ? '' : absint( $_POST['usage_limit_per_user'] );
		$limit_usage_to_x_items = empty( $_POST['limit_usage_to_x_items'] ) ? '' : absint( $_POST['limit_usage_to_x_items'] );
		$expiry_date = sanitize_text_field( $_POST['expire'] );
		$individual_uses = isset( $_POST['individual_use'] ) ? 'yes' : 'no';
		$apply_before_tax = isset( $_POST['apply_before_tax'] ) ? 'yes' : 'no';
		$show_cp_inshop = isset( $_POST['show_cp_inshop'] ) ? 'yes' : 'no';
		$show_cp_inshop_txt = empty( $_POST['show_cp_inshop_txt'] ) ? '' : sanitize_text_field( $_POST['show_cp_inshop_txt'] );
		$free_shipping = isset( $_POST['enable_free_ship'] ) ? 'yes' : 'no';
		$exclude_sale_items = isset( $_POST['exclude_sale_items'] ) ? 'yes' : 'no';
		$minimum_amount = wc_format_decimal( $_POST['minium_ammount'] );
		$maximum_amount = wc_format_decimal( $_POST['maxum_ammount'] );

		update_post_meta( $post_id, 'discount_type', $type );
		update_post_meta( $post_id, 'coupon_amount', $amount );
		update_post_meta( $post_id, 'product_ids', $product_ids );
		update_post_meta( $post_id, 'individual_use', $individual_uses );
		update_post_meta( $post_id, 'usage_limit', $usage_limit );
		update_post_meta( $post_id, 'usage_limit_per_user', $usage_limit_per_user );
		update_post_meta( $post_id, 'limit_usage_to_x_items', $limit_usage_to_x_items );
		update_post_meta( $post_id, 'expiry_date', $expiry_date );
		update_post_meta( $post_id, 'apply_before_tax', $apply_before_tax );
		update_post_meta( $post_id, 'free_shipping', $free_shipping );
		update_post_meta( $post_id, 'show_cp_inshop', $show_cp_inshop );
		update_post_meta( $post_id, 'show_cp_inshop_txt', $show_cp_inshop_txt );
		update_post_meta( $post_id, 'exclude_sale_items', $exclude_sale_items );
		update_post_meta( $post_id, 'minimum_amount', $minimum_amount );
		update_post_meta( $post_id, 'maximum_amount', $maximum_amount );
		update_post_meta( $post_id, 'customer_email', $customer_email );
		
		do_action('frozr_seller_coupons_created', $post_id);
		
		/*send WC notices*/
		wp_send_json(array(
			'message'	=> $message,
			'dlink'		=> home_url( '/dashboard/coupons' ),
		));
		die();
	}
	function frozr_save_item_view_count() {
		check_ajax_referer( 'frozr_add_new_page_count', 'security' );
		$post_id = intval($_POST['itemid']);
		$current_number = (int) get_post_meta( $post_id, 'frozr_item_views_count', true );
		$new_number = $current_number + 1;
		update_post_meta($post_id, 'frozr_item_views_count',$new_number);
		die();
	}
	function frozr_get_delivery_info() {
		check_ajax_referer( 'frozr_set_user_loc', 'security' );
		$address = $_POST['address'];
		$vend_id = intval($_POST['vendor']);
		$duration = frozr_get_distance($vend_id,$address,true);
		$data = array();
		
		if ($duration) {
		$divider = norsani()->vendor->frozr_distance_divider();
		$distance_of_customer = frozr_get_distance($vend_id,$address);
		$total_del = frozr_delivery_settings($vend_id,'shipping_fee',false,$address);
		$data['total_del'] = $total_del;
		$data['divider'] = norsani()->vendor->frozr_distance_divider(true);
		$data['distance'] = $distance_of_customer > $divider ? $distance_of_customer/$divider : 1;
		$data['duration'] = $duration;
		}
		
		wp_send_json($data);
		
		die();
	}
	function frozr_add_new_distance_bulk() {
		check_ajax_referer( 'frozr_add_new_distance_nonce', 'security' );
		
		$vendors_list = $_POST['list'];
		$address = $_POST['startaddr'];
		
		foreach ($vendors_list as $key => $val) {
		norsani()->vendor->frozr_add_distance($val['vend'], $address, $val['dis'], $val['dur']);
		}

		die();
	}
	function frozr_add_new_distance() {
		check_ajax_referer( 'frozr_add_new_distance_nonce', 'security' );

		$distance = $_POST['distance'];
		$address = $_POST['startaddr'];
		$duration = $_POST['duration'];
		$vend_id = intval($_POST['tovend']);

		norsani()->vendor->frozr_add_distance($vend_id, $address, $distance, $duration);
		
		$total_del = frozr_delivery_settings($vend_id,'shipping_fee',false,$address);
		
		wp_send_json(array('total_del'=>$total_del));

		die();
	}
	function frozr_item_remove_from_cart() {
		check_ajax_referer( 'remove_cart_item', 'security' );
		$cart_item_key = sanitize_text_field( $_POST['itemkey'] );
		$cart_empty = false;
		
		if ( $cart_item = WC()->cart->get_cart_item( $cart_item_key ) ) {
			WC()->cart->remove_cart_item( $cart_item_key );
		}
		ob_start();
		woocommerce_mini_cart();
		$minicart = ob_get_clean();
		ob_start();
		wc_cart_totals_order_total_html();
		$minicarttotal = ob_get_clean();
		
		if ( WC()->cart->is_empty() ) {
		$cart_empty = true;
		}
		
		wp_send_json(array('minicart'=>$minicart,'total'=>$minicarttotal, 'cart_is_empty'=>$cart_empty));
		die();
	}
	function frozr_make_item_special() {
		check_ajax_referer( 'norsani_add_product_special', 'security' );
		$u = get_current_user_id();
		$change_item = esc_attr($_POST['stat']) == 'offline' ? 1 : 0;
		$change_status = esc_attr($_POST['stat']) == 'offline' ? 'online' : 'offline';
		$item_id = intval($_POST['item']);
		
		if ( !user_can( $u, 'frozer' ) || !frozr_is_seller_enabled($u) ||  !frozr_is_author($item_id) ) {
			wp_send_json_error(__('You\'re not authorized to do this step.','frozr-norsani'));
			die(-1);
		}
		$item_new_sts = intval($change_item);
		$item_save_time = date(current_time('mysql'));
		update_post_meta($item_id, 'frozr_special_item', $item_new_sts);
		update_post_meta($item_id, 'frozr_special_item_save_time', $item_save_time);
		update_post_meta($item_id, 'frozr_special_item_status', $change_status);
		
		if ($change_item == 1) {
			$data = __("Success: The product will show in the special products list only for today and only while your store is open.","frozr-norsani");
		} else {
			$data = __("Success: The product has been removed from today's specials products list.","frozr-norsani");
		}
		wp_send_json_success($data);
		
		die();
	}
	function frozr_change_item_status() {
		check_ajax_referer( 'norsani_change_product_status', 'security' );
		$u = get_current_user_id();
		$change_to = esc_attr($_POST['stat']);
		$item_id = intval($_POST['id']);
		
		if ( !user_can( $u, 'frozer' ) || !frozr_is_seller_enabled($u) ||  !frozr_is_author($item_id) ) {
			wp_send_json_error(__("You're not authorized to do this step.","frozr-norsani"));
			die(-1);
		}

		$item_changed = wp_update_post( array( 'ID' => $item_id, 'post_status' => $change_to ) );
		
		$post_counts = frozr_count_posts( 'product', get_current_user_id() );
		$offline_total = $post_counts->offline;
		$publish_total = $post_counts->publish;
		
		wp_send_json(array('online'=>$offline_total, 'offline'=>$publish_total));
		
		die();
	}
	function frozr_check_new_orders() {
		check_ajax_referer( 'norsani_check_new_orders', 'security' );
		$u = get_current_user_id();
		$manual_status = get_user_meta($u,'frozr_vendor_manual_status',true) ? get_user_meta($u,'frozr_vendor_manual_status',true) : array('online'=>0,'time'=>date(current_time('mysql')));
		$notice_status = get_user_meta($u,'frozr_vendor_notice_status',true) ? 1 : 0;
		$order_status = isset( $_GET['order_status'] ) ? sanitize_key( $_GET['order_status'] ) : 'all';
		$on_orders_page = boolval($_POST['on_orders']);
		$current_count = intval($_POST['ccount']);
		$orders = false;
		$nw = date(current_time('mysql'));
		
		if ( !user_can( $u, 'frozer' ) || !frozr_is_seller_enabled($u) ) {
			wp_send_json_error(__('Error while checking for new orders','frozr-norsani'));
			die(-1);
		}
		/*Get vendor orders*/
		$orders_args = array(
			'posts_per_page'=> -1,
			'offset'		=> 0,
			'post_type'		=> 'shop_order',
			'post_status'	=> array('wc-processing','wc-on-hold'),
			'fields'		=> 'ids',
		);

		if (!is_super_admin()) {
			$orders_args['meta_key'] = '_frozr_vendor';
			$orders_args['meta_value'] = get_current_user_id();
		}

		$orders_array = get_posts( $orders_args );
		$new_count = count($orders_array);
		/*varify the vendor is still online*/
		$manual_status['time'] = $nw;
		update_user_meta($u,'frozr_vendor_manual_status',$manual_status);
		
		/*Get new orders if we are on the orders page*/
		if ($on_orders_page && $new_count > $current_count) {
		$orders = true;
		}
		
		wp_send_json(array('count'=>$new_count, 'orders'=>$orders, 'notice'=>$notice_status));
		
		die();
	}
	function frozr_update_orders_count() {
		check_ajax_referer( 'update_orders_count', 'security' );
		$u = get_current_user_id();
		if ( !user_can( $u, 'frozer' ) || !frozr_is_seller_enabled($u) ) {
			wp_send_json_error(__('You do not have permissions to perform this.','frozr-norsani'));
			die(-1);
		}
		/*Get vendor orders*/
		$orders_args = array(
			'posts_per_page'=> -1,
			'offset'		=> 0,
			'post_type'		=> 'shop_order',
			'post_status'	=> array('wc-processing','wc-on-hold'),
			'fields'		=> 'ids',
		);
		
		if (!is_super_admin()) {
			$orders_args['meta_key'] = '_frozr_vendor';
			$orders_args['meta_value'] = get_current_user_id();
		}
		
		$orders_array = get_posts( $orders_args );
		$new_count = count($orders_array);
		
		wp_send_json( array('count'=>$new_count) );
		die();
	}
	function frozr_set_vendor_status() {
		check_ajax_referer( 'norsani_save_vendor_status', 'security' );
		$u = get_current_user_id();
		$status = esc_attr($_POST['stat']);
		$change = esc_attr($_POST['change']);
		$manual_duration = intval($_POST['duration']);
		$nw = current_time('mysql');
		$manual_status = get_user_meta($u,'frozr_vendor_manual_status',true) ? get_user_meta($u,'frozr_vendor_manual_status',true) : array('online'=>0,'time'=>current_time('mysql'));
		$new_count = null;
		
		if ( !user_can( $u, 'frozer' ) || !frozr_is_seller_enabled($u) ) {
			wp_send_json_error(__('You do not have permissions to perform this.','frozr-norsani'));
			die(-1);
		}
		if ($change == 'status') {
			if ($manual_duration > 0) {
			$manual_status['online'] = ($status == 'online') ? 1 : 'off';
			$manual_status['time'] = $nw;
			} else {
			$manual_status['online'] = null;
			$manual_status['time'] = $nw;
			}
			update_user_meta($u,'frozr_vendor_manual_change_time',$nw);
			update_user_meta($u,'frozr_vendor_manual_status',$manual_status);
			update_user_meta($u,'frozr_manual_duration',$manual_duration);
		} elseif($change == 'notices') {
			update_user_meta($u,'frozr_vendor_notice_status',$status);			
		}
		
		ob_start();
		do_action('frozr_norsani_vendor_status_main_menu');
		$data = ob_get_clean();
		
		if ('no' == $_POST['ordscnt']) {
		/*Get vendor orders*/
		$orders_args = array(
			'posts_per_page'=> -1,
			'offset'		=> 0,
			'post_type'		=> 'shop_order',
			'post_status'	=> array('wc-processing','wc-on-hold'),
			'fields'		=> 'ids',
		);
		
		if (!is_super_admin()) {
			$orders_args['meta_key'] = '_frozr_vendor';
			$orders_args['meta_value'] = get_current_user_id();
		}
		
		$orders_array = get_posts( $orders_args );
		$new_count = count($orders_array);
		}
		
		wp_send_json_success(array('data'=>$data,'vid'=>$u,'rtime'=>norsani()->vendor->frozr_rest_status($u),'vsts'=>frozr_is_rest_open($u),'order_count'=>$new_count));
		
		die();
	}
	function frozr_add_item_to_fav () {
		check_ajax_referer( 'add_item_to_fav', 'security' );
		
		$item_id	= $_POST['itemid'];
		$user_id	= get_current_user_id();
		
		if ($user_id > 0) {
		
		$item_favs	= '' != get_user_meta($user_id, 'frozr_item_favs', true) ? get_user_meta($user_id, 'frozr_item_favs', true) : apply_filters('frozr_default_items_favs', array());
		if (!empty($item_favs)) {
			if (in_array($item_id,$item_favs)) {
				if (($key = array_search($item_id, $item_favs)) !== false) {
					unset($item_favs[$key]);
				}
			} else {
				$item_favs[] = $item_id;
			}
		} else {
			$item_favs[] = $item_id;
		}
		
		update_user_meta( $user_id, 'frozr_item_favs', $item_favs );
		
		wp_send_json( $item_favs );
		
		die();
		
		}
		die(-1);
	}
	function frozr_add_rest_to_fav() {
		check_ajax_referer( 'add_rest_to_fav', 'security' );
		
		$item_id	= $_POST['itemid'];
		$user_id	= get_current_user_id();
		
		if ($user_id > 0) {
		
		$rest_favs	= '' !== get_user_meta($user_id, 'frozr_rests_favs', true) ? get_user_meta($user_id, 'frozr_rests_favs', true) : apply_filters('frozr_default_rests_favs', array());
		
		if (!empty($rest_favs)) {
			if (in_array($item_id,$rest_favs)) {
				if (($key = array_search($item_id, $rest_favs)) !== false) {
					unset($rest_favs[$key]);
				}
			} else {
				$rest_favs[] = $item_id;
			}
		} else {
			$rest_favs[] = $item_id;
		}

		update_user_meta( $user_id, 'frozr_rests_favs', $rest_favs );

		wp_send_json( $rest_favs );
		
		die();
		
		}
		die(-1);
	}
	/*Get near vendors*/
	function frozr_get_near_vendors_data() {
		check_ajax_referer( 'load_norsani_rests', 'security' );
		
		$user_loc = $_POST['user_loc'];
		$orderd_sellers = array();
		
		if ($user_loc) {
		/*Get Vendors*/
		$orderd_sellers = array();
		$args = apply_filters( 'frozr_rest_get_near_vendors', array(
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
		foreach($get_sellers as $seller) {
			/*get sellers by distance*/
			$orderd_sellers[] = array('vend'=>$seller->ID,'dis'=>frozr_get_distance($seller->ID,$user_loc));
		}}
		
		$data = array(
			'near_sellers' => $orderd_sellers,
		);

		wp_send_json( $data );

		die();
	}
	/*Get all vendors*/
	function frozr_get_resturants_data() {
		
		check_ajax_referer( 'load_norsani_rests', 'security' );
		
		$current_user = get_current_user_id();
		$reco_items = array();
		$reco_rests = array();
		$top_rests = array();
		$user_loc = $_POST['user_loc'];
		
		if ($current_user) {
		/*Get recommended Products*/
		$statuses = array_keys( wc_get_order_statuses() );
		$customer_orders = get_posts( apply_filters( 'frozr_rest_reco_items_args', array(
			'numberposts' => 12,
			'meta_key'    => '_customer_user',
			'meta_value'  => get_current_user_id(),
			'post_type'   => wc_get_order_types( 'view-orders' ),
			'post_status' => $statuses,
		) ) );

		if ( $customer_orders ) {
			foreach ( $customer_orders as $customer_order ) {
				$order			= wc_get_order( $customer_order );
				$line_items		= $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
				$vendor_id		= frozr_get_order_author($order->get_id());
				$reco_vendor_type = get_user_meta($vendor_id, 'frozr_vendor_type', true);
				if (isset($reco_rests[$reco_vendor_type]) && !in_array($vendor_id,$reco_rests[$reco_vendor_type]) || !isset($reco_rests[$reco_vendor_type])) {
				$reco_rests[$reco_vendor_type][] = $vendor_id;
				}
				
				foreach ( $line_items as $item_id => $item ) {
					$_productid		= $item['product_id'];
					$itemcats		= wp_get_post_terms( $_productid, 'product_cat', array("fields" => "names") );
					
					if (is_array($itemcats)) {
						foreach ( $itemcats as $itemcat ) {
							$reco_items[] = $itemcat;
						}
					} else {
						$reco_items[] = $itemcats;
					}
				}
			}
			$reco_items = array_unique($reco_items);
		}
		}

		/*Get featured items*/
		$option = get_option( 'frozr_gen_settings' );
		$options = (! empty( $option['frozr_reco_items']) ) ? $option['frozr_reco_items'] : array('0');
		$products_data = array();
		$items_args = array(
		'posts_per_page'=> -1,
		'offset'		=> 0,
		'include'		=> $options,
		'post_type'		=> 'product',
		'post_status'	=> array('publish','offline'),
		);
		
		$items_array = get_posts( $items_args );
		foreach ( $items_array as $product ) {
			$product_obj = wc_get_product($product->ID);
			$profile_info = frozr_get_store_info($product->post_author);
			/*item cats*/
			$discts = get_terms( 'product_cat', 'fields=names&hide_empty=0' );
			$itemcats = wp_get_post_terms( $product->ID, 'product_cat', array("fields" => "names") );
			$itemcats_slug = array();
			if (is_array($itemcats)) {
				foreach ( $itemcats as $itemcat ) {
					$itemcats_slug[] = $itemcat;
				}
				$item_cats = join( ' ', $itemcats_slug );
			} elseif ( ! empty( $discts ) && ! is_wp_error( $discts )) {
				$item_cats = $itemcats;
			}
			/*item ingredents*/
			$ings = get_terms( 'ingredient', 'fields=names&hide_empty=0' );
			$ingredients = wp_get_post_terms( $product->ID, 'ingredient', array("fields" => "names") );
			$ingredients_slug = array();
			if (is_array($ingredients)) {
				foreach ( $ingredients as $ingredient ) {
					$ingredients_slug[] = $ingredient;
				}
				$ingreds = join( ' ', $ingredients_slug );
			} elseif ( ! empty( $ings ) && ! is_wp_error( $ings )) {
				$ingreds = $ingredients;
			}
			/*menu type*/
			$item_menu = ( null != (get_post_meta( $product->ID, 'product_meal_type', true )) ) ? get_post_meta( $product->ID, 'product_meal_type', true ) : array();
			
			$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($product->ID), 'full');
		
			$unavailable_item = '';
			if ($product->post_status == 'offline') {
				$unavailable_item = '<span class="frozr_product_unavailable">'.__('This product is currently unavailable.','frozr-norsani').'</span>';
			}

			$products_data[] = array(
				'id' => $product->ID,
				'link' => get_permalink($product->ID),
				'author' => $profile_info['store_name'],
				'author_link' => frozr_get_store_url($product->post_author),
				'itemstatus' => $unavailable_item,
				'title' => esc_attr( $product->post_title ),
				'excerpt' => isset( $product->post_excerpt ) ? $product->post_excerpt : '',
				'cats' => $item_cats,
				'ingredients' => $ingreds,
				'item_promotions' => ( null != (get_post_meta( $product->ID, 'item_promotions', true )) ) ? get_post_meta( $product->ID, 'item_promotions', true ) : array(),
				'menu_type' => ( null != (get_post_meta( $product->ID, 'product_meal_type', true )) ) ? get_post_meta( $product->ID, 'product_meal_type', true ) : array(),
				'image' => $large_image_url[0],
				'price' => $product_obj->get_price_html(),
				'rating_html' => frozr_rest_get_item_rating($product),
			);		
		}
		/*Get Vendors*/
		$seloption = get_option( 'frozr_gen_settings' );
		$featured_sellers = array();
		$orderd_sellers = array();
		$args = apply_filters( 'frozr_rest_get_vendors', array(
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
		$sellers_data = array();
		foreach($get_sellers as $seller) {
			$vendor_type = get_user_meta($seller->ID, 'frozr_vendor_type', true);
			/*Get recommended rests*/
			if (isset($seloption['frozr_reco_sellers']) && in_array($seller->ID, $seloption['frozr_reco_sellers'])) {
				$featured_sellers[$vendor_type][] = $seller->ID;
			}
			$getalltyps= get_terms( 'vendorclass', 'fields=names&hide_empty=0' );
			$restypes = wp_get_object_terms( $seller->ID, 'vendorclass', array("fields" => "names") );
			$restype_slug = array();
			if (is_array($restypes)) {
				foreach ( $restypes as $restype ) {
					$restype_slug[] = $restype;
				}
				$grestypes = join( ' - ', $restype_slug );
			} elseif ( ! empty( $getalltyps ) && ! is_wp_error( $getalltyps )) {
				$grestypes = $restypes;
			}
			
			$profile_info = frozr_get_store_info($seller->ID);
			$sellers_data[$vendor_type][$seller->ID] = array(
				'type' => $vendor_type,
				'name' => $profile_info['store_name'],
				'logo' => isset( $profile_info['gravatar'] ) ? wp_get_attachment_url(absint( $profile_info['gravatar'] )) : '',
				'address' => get_user_meta($seller->ID, 'rest_address_geo', true) ? get_user_meta($seller->ID, 'rest_address_geo', true) : '',
				'rating' => norsani()->vendor->frozr_rest_get_readable_seller_rating( $seller->ID ),
				'vendorclass' => $grestypes ?  $grestypes : '',
				'accepted_orders' => ! empty ($profile_info['accpet_order_type']) ? $profile_info['accpet_order_type'] : frozr_default_accepted_orders_types(),
				'accepted_orders_closed' => ! empty ($profile_info['accpet_order_type_cl']) ? $profile_info['accpet_order_type_cl'] : frozr_default_accepted_orders_types_closed(),
				'timing_status' => frozr_is_rest_open($seller->ID),
				'url' => frozr_get_store_url($seller->ID),
			);
			if (norsani()->vendor->frozr_get_readable_seller_rating($seller->ID, false) != 0) {
				$top_rests[$vendor_type][$seller->ID] = norsani()->vendor->frozr_get_readable_seller_rating($seller->ID, false);
			}
			if ($user_loc) {
			/*get sellers by distance*/
			$orderd_sellers[] = array('vend'=>$seller->ID,'dis'=>frozr_get_distance($seller->ID,$user_loc));
			}
		}

		arsort($top_rests);

		$data = array(
			'near_sellers' => $orderd_sellers,
			'sellers_data' => $sellers_data,
			'reco_items' => $reco_items,
			'featured_rests' => $featured_sellers,
			'featured_items' => $products_data,
			'reco_rests' => $reco_rests,
			'top_rests' => $top_rests,
		);
		
		if ($current_user) {
			$data['fav_items'] = get_user_meta($current_user, 'frozr_item_favs', true);
			$data['fav_rests'] = get_user_meta($current_user, 'frozr_rests_favs', true);
		}

		wp_send_json( $data );

		die();
	}
	/**
     * get_updated product
     *
     */
    function frozr_get_updated_product() {

		check_ajax_referer( 'frozr_get_product', 'security' );
		
		$item_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
		$seller_id = get_current_user_id();
		
		if ( ! frozr_is_author( $item_id ) && ! is_super_admin() || ! current_user_can( 'frozer') && ! is_super_admin() || ! frozr_is_seller_enabled($seller_id) && ! is_super_admin() || $item_id == 0 ) {
			die(-1);
		}
		
		norsani()->item->frozr_get_dash_item($item_id);
		
		die();
	}
	/**
     * Add a new variation row
     *
     */
    function frozr_add_product_variation() {
		check_ajax_referer( 'frozr_add_variation', 'security' );
		?>
		<div class="multi-field item_variation">
			<div class="options_group_wrapper">
				<div class="form-group option_group">
					<div class="option_form">
					<label class="control-label option_label" for="var_<?php echo sanitize_title($_POST['varname']); ?>"><?php _e( 'Variation','frozr-norsani'); ?></label>
					<div class="attr_name"><?php echo esc_attr($_POST['varname']); ?></div>
					<input value="<?php echo esc_attr($_POST['varname']); ?>" data-attrname="<?php echo sanitize_title($_POST['varname']); ?>" name="var_<?php echo sanitize_title($_POST['varname']); ?>" class="item_options item_option_attribute form-control" type="hidden" data-enhance="false" data-ajax="false">
					</div>
					<div class="option_form">
					<label class="control-label option_label" for="item_options[][<?php echo sanitize_title($_POST['varname']); ?>]"><?php _e( 'Option','frozr-norsani'); ?></label>
					<select data-attropt="<?php echo sanitize_title($_POST['varname']); ?>" data-role="none" name="item_options[][<?php echo sanitize_title($_POST['varname']); ?>]" class="item_options form-control">
						<option selected="selected" value=""><?php echo __('Any','frozr-norsani') . ' ' . esc_attr($_POST['varname']); ?></option>
						<?php foreach (array_map('sanitize_title', $_POST['varopts']) as $option) { ?>
						<option value="<?php echo str_replace( '-', ' ', $option); ?>"><?php echo str_replace( '-', ' ', $option); ?></option>
						<?php } ?>
					</select>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label" for="item_options[][description]"><?php _e( 'Variation Description','frozr-norsani'); frozr_inline_help_db('dash_item_vardesc'); ?></label>
				<input value="" name="item_options[][description]" class="item_options form-control" type="text" placeholder="<?php _e('Few words about this variation','frozr-norsani'); ?>">
			</div>
			<?php
			/* Price*/
			frozr_wp_text_input( array( 'id' => 'item_options[][regular_price]', 'value' => '', 'label' => __( 'Regular Price', 'frozr-norsani' ) . ' (' . get_woocommerce_currency_symbol() . ')', 'data_type' => 'price') );

			/* Sale Price*/
			frozr_wp_text_input( array( 'id' => 'item_options[][price]', 'data_type' => 'price','value' => '', 'label' => __( 'Sale Price', 'frozr-norsani' ) . ' ('.get_woocommerce_currency_symbol().')', 'description' => '<a href="#" class="sale_schedule">' . __( 'Schedule', 'frozr-norsani' ) . '</a>' ) );
			
			echo '<div class="form-group sale_price_dates_fields frozr_hide">
					<div class="frozr_item_sale_dates">
					<div>
					<label class="control-label" for="_sale_price_dates_from">' . __( 'Sale price start date', 'frozr-norsani' ) . '</label>
					<input type="date" class="short" name="_sale_price_dates_from" id="_sale_price_dates_from" value="" placeholder="' . __( 'Sale price start date', 'placeholder', 'frozr-norsani' ) . ' YYYY-MM-DD" maxlength="10" />
					</div>
					<div>
					<label class="control-label" for="_sale_price_dates_to">' . __( 'Sale price end date', 'frozr-norsani' ) . '</label>
					<input type="date" class="short" name="_sale_price_dates_to" id="_sale_price_dates_to" value="" placeholder="' . __( 'Sale price end date', 'placeholder', 'frozr-norsani' ) . '  YYYY-MM-DD" maxlength="10" />
					</div>
					</div>
					<a href="#" title="'.esc_attr__( 'The sale will end at the beginning of the set date.', 'frozr-norsani' ).'" class="cancel_sale_schedule frozr_hide">'. __( 'Cancel', 'frozr-norsani' ) .'</a>
				</div>';
			?>
			<i class="remove_option_field material-icons" data-varid="<?php echo $variation_data['variation_id']; ?>">close</i>
		</div>
		<?php
        die();
    }
	/**
     * Add a new row to the admin fees/commission table
     *
     */
    function frozr_add_fee_setting_row() {

        if ( !is_super_admin() ) {
            echo $message = __( 'Something went wrong!', 'frozr-norsani' );
			die(-1);
        }
		
		frozr_get_fees_rules_body();

		die();
	}
	/**
	* Send Invitation Letter to Vendor
	*
	*/
	function frozr_send_rest_invitation() {
		
		check_ajax_referer( 'frozr_rest_invitation_nonce', 'security' );

		if ( !is_super_admin() ) {
			wp_send_json( array('message' => __("Something went wrong!",'frozr-norsani')));
			die(-1);
		}

		$msg_args = array (
			'to' => sanitize_email($_POST['rest_invit_email']),
			'subject' => sanitize_text_field($_POST['rest_invit_subject']),
			'msg' => wc_clean($_POST['rest_invit_text']),
			'type' => 'invite',
		);
		
		do_action('frozr_send_vendor_message', $msg_args);

		wp_send_json( array('message' => __("Email sent successfully!",'frozr-norsani')));		
		die();
	}

	/**
	 * Ajax Add to cart
	 *
	 */
	function frozr_ajax_add_to_cart() {
		
		$get_full_cart		= isset($_POST['getfull']) ? true : false;
		$product_id			= apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['pid'] ) );
		$product_author		= get_post_field( 'post_author', $product_id );
		$quantity			= empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( $_POST['quantity'] );
		$variation_id		= empty( $_POST['variation_id'] ) ? 0 : absint( $_POST['variation_id'] );
		$product_status		= get_post_status( $product_id );
		$adding_to_cart		= wc_get_product( $product_id );
		$missing_attributes	= array();
		$variations			= array();
		$attributes			= $adding_to_cart->get_attributes();
		$variation			= empty( $_POST['variation_id'] ) ? wc_get_product( $variation_id ) : '';
		$variation_data		= !empty($variation_id) ? wc_get_product_variation_attributes( $variation_id ) : array();
		$cart_item_data		= array();
		$cart_item_data		= (array) apply_filters( 'woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id );
		$cart_id			= WC()->cart->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );
		$cart_item_key		= WC()->cart->find_product_in_cart( $cart_id );
		$add_to_cart_handler = apply_filters( 'woocommerce_add_to_cart_handler', wc_clean($_POST['product_type']), $adding_to_cart );
		$order_type			= $_POST['order_type'];
		$userslocids		= frozr_get_delivery_sellers();
		$in_cart_quantity	= $cart_item_key ? WC()->cart->cart_contents[ $cart_item_key ]['quantity'] : 0;
		
		/*Availability check*/
		if (!frozr_is_seller_enabled($product_author)) {
			wp_send_json_error( __('Sorry you cannot order from this vendor','frozr-norsani') );
			die(-1);
		}
		
		if (!norsani()->vendor->frozr_check_item_timing($product_id)) {
			wp_send_json_error( __('Sorry you cannot order this product at this time!','frozr-norsani') );
			die(-1);
		}
		
		if (norsani()->item->frozr_max_orders_reached($product_id)) {
			wp_send_json_error( __('Sorry, we cannot receive any more orders for this product today, please come back another day.','frozr-norsani') );
			die(-1);
		}
		
		/* Security check*/
		if ( $quantity <= 0 || ! $adding_to_cart || 'trash' === $adding_to_cart->get_status() ) {
			wp_send_json_error( __('Something Went Wrong!','frozr-norsani') );
			die(-1);
		}
		if ( $in_cart_quantity > 0 && $adding_to_cart->is_sold_individually()) {
			$message = sprintf( '<a href="%s" class="button wc-forward" data-ajax="false">%s</a> %s', wc_get_cart_url(), __( 'View Cart', 'frozr-norsani' ), sprintf( __( 'You cannot add another &quot;%s&quot; to your cart.', 'frozr-norsani' ), $adding_to_cart->get_title() ) );
			wp_send_json_error( $message );
			die(-1);
		}
		/* Check product is_purchasable*/
		if ( ! $adding_to_cart->is_purchasable() ) {
			$message = __( 'Sorry, this product cannot be purchased.', 'frozr-norsani' );
			wp_send_json_error( $message );
			die(-1);
		}

		/* Stock check - only check if we're managing stock and backorders are not allowed*/
		if ( ! $adding_to_cart->is_in_stock() ) {
			$message = sprintf( __( 'You cannot add &quot;%s&quot; to the cart because the product is out of stock.', 'frozr-norsani' ), $adding_to_cart->get_title() );
			wp_send_json_error( $message );
			die(-1);
		}
		if ( ! $adding_to_cart->has_enough_stock( $quantity ) ) {
			$message = sprintf(__( 'You cannot add that amount of &quot;%1$s&quot; to the cart because there is not enough stock (%2$s remaining).', 'frozr-norsani' ), $adding_to_cart->get_title(), $adding_to_cart->get_stock_quantity() );
			wp_send_json_error( $message );
			die(-1);
		}
		if (! in_array($product_author, $userslocids) && $order_type == 'delivery') {
			$message = __('The Vendor will not deliver to your location, please choose another option.','frozr-norsani');
			wp_send_json_error( $message );
			die(-1);
		}
		/* Stock check - this time accounting for what's already in-cart*/
		if ( $managing_stock = $adding_to_cart->managing_stock() ) {
			$products_qty_in_cart = WC()->cart->get_cart_item_quantities();

			if ( $adding_to_cart->is_type( 'variation' ) && true === $managing_stock ) {
				$check_qty = isset( $products_qty_in_cart[ $variation_id ] ) ? $products_qty_in_cart[ $variation_id ] : 0;
			} else {
				$check_qty = isset( $products_qty_in_cart[ $product_id ] ) ? $products_qty_in_cart[ $product_id ] : 0;
			}

			/**
			 * Check stock based on all items in the cart.
			 */
			if ( ! $adding_to_cart->has_enough_stock( $check_qty + $quantity ) ) {
				$message = sprintf(
					'<a href="%s" class="button wc-forward" data-ajax="false">%s</a> %s',
					wc_get_cart_url(),
					__( 'View Cart', 'frozr-norsani' ),
					sprintf( __( 'You cannot add that amount to the cart &mdash; we have %s in stock and you already have %s in your cart.', 'frozr-norsani' ), $adding_to_cart->get_stock_quantity(), $check_qty )
				);
				wp_send_json_error( $message );
				die(-1);
			}
		}

		/* Start add to cart process*/
		/* Variable product handling*/
		if ( 'variable' === $add_to_cart_handler ) {
			/* Verify all attributes*/
			foreach ( $attributes as $attribute ) {
				
				if ( ! $attribute['is_variation'] ) {
					continue;
				}

				$taxonomy = 'attribute_' . sanitize_title( $attribute['name'] );

				if ( isset( $_POST[ $taxonomy ] ) ) {

					/* Get value from post data*/
					if ( $attribute['is_taxonomy'] ) {
						/* Don't use wc_clean as it destroys sanitized characters*/
						$value = sanitize_title( stripslashes( $_POST[ $taxonomy ] ) );
					} else {
						$value = wc_clean( stripslashes( $_POST[ $taxonomy ] ) );
					}

					/* Get valid value from variation*/
					$valid_value = isset( $variation_data[ $taxonomy ] ) ? $variation_data[ $taxonomy ] : '';

					/* Allow if valid*/
					if ( '' === $valid_value || $valid_value === $value ) {
						$variations[ $taxonomy ] = $value;
					} else {
						$message = sprintf( __( 'Invalid value posted for %s', 'frozr-norsani' ), wc_attribute_label( $attribute['name'] ) );
						wp_send_json_error( $message );
						die(-1);
					}

				} else {
					$missing_attributes[] = wc_attribute_label( $attribute['name'] );
				}
			}
			if ( $missing_attributes ) {
				$message = sprintf( _n( '%s is a required field', '%s are required fields', sizeof( $missing_attributes ), 'frozr-norsani' ), wc_format_list_of_items( $missing_attributes ) );
				wp_send_json_error( $message );
				die(-1);
			} elseif ( empty( $variation_id ) ) {
				$message = __( 'Please choose product options&hellip;', 'frozr-norsani' );
				wp_send_json_error( $message );
				die(-1);
			} else {
				/* Add to cart validation*/
				$passed_validation 	= apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations );

				if ( $passed_validation && $this->add_to_cart( $product_id, $quantity, $variation_id, $variations, $cart_item_data, $cart_id ) !== false && 'publish' === $product_status ) {

					do_action( 'woocommerce_ajax_added_to_cart', $product_id );

					$this->get_refreshed_fragments($product_id,$get_full_cart);
				}
			}

		} else {

			$passed_validation	= apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );

			if ($passed_validation && $this->add_to_cart($product_id, $quantity, $variation_id, $variations, $cart_item_data, $cart_id ) && 'publish' === $product_status) {

				do_action( 'woocommerce_ajax_added_to_cart', $product_id );

				$this->get_refreshed_fragments($product_id,$get_full_cart);

			} else {
				wp_send_json_error( __('Something Went Wrong!','frozr-norsani') );
				die(-1);
			}

		}
		/* If we added the product to the cart we can now optionally do a redirect.*/
		/* If has custom URL redirect there*/
		if ( $url = apply_filters( 'woocommerce_add_to_cart_redirect', $url ) ) {
			wp_safe_redirect( $url );
			exit;
		} elseif ( get_option( 'woocommerce_cart_redirect_after_add' ) === 'yes' ) {
			wp_safe_redirect( wc_get_cart_url() );
			exit;
		}
	
		die();
	}
	public function add_to_cart( $product_id = 0, $quantity = 1, $variation_id = 0, $variation = array(), $cart_item_data = array(), $cart_id = "" ) {
		/* Wrap in try catch so plugins can throw an exception to prevent adding to cart*/
		try {
			$product_id   = absint( $product_id );
			$variation_id = absint( $variation_id );

			/* Ensure we don't add a variation to the cart directly by variation ID*/
			if ( 'product_variation' === get_post_type( $product_id ) ) {
				$variation_id = $product_id;
				$product_id   = wp_get_post_parent_id( $variation_id );
			}

			/* Get the product*/
			$product_data = wc_get_product( $variation_id ? $variation_id : $product_id );

			/* Sanity check*/
			if ( $quantity <= 0 || ! $product_data || 'trash' === $product_data->get_status() ) {
				return false;
			}

			/* Find the cart item key in the existing cart*/
			$cart_item_key  = WC()->cart->find_product_in_cart( $cart_id );

			/* Force quantity to 1 if sold individually and check for existing item in cart*/
			if ( $product_data->is_sold_individually() ) {
				$quantity         = apply_filters( 'woocommerce_add_to_cart_sold_individually_quantity', 1, $quantity, $product_id, $variation_id, $cart_item_data );
				$in_cart_quantity = $cart_item_key ? WC()->cart->cart_contents[ $cart_item_key ]['quantity'] : 0;

				if ( $in_cart_quantity > 0 ) {
					/* translators: %s: product name */
					throw new Exception( sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', wc_get_cart_url(), __( 'View cart', 'frozr-norsani' ), sprintf( __( 'You cannot add another "%s" to your cart.', 'frozr-norsani' ), $product_data->get_name() ) ) );
				}
			}

			/* Check product is_purchasable*/
			if ( ! $product_data->is_purchasable() ) {
				throw new Exception( __( 'Sorry, this product cannot be purchased.', 'frozr-norsani' ) );
			}

			/* Stock check - only check if we're managing stock and backorders are not allowed*/
			if ( ! $product_data->is_in_stock() ) {
				throw new Exception( sprintf( __( 'You cannot add &quot;%s&quot; to the cart because the product is out of stock.', 'frozr-norsani' ), $product_data->get_name() ) );
			}

			if ( ! $product_data->has_enough_stock( $quantity ) ) {
				/* translators: 1: product name 2: quantity in stock */
				throw new Exception( sprintf( __( 'You cannot add that amount of &quot;%1$s&quot; to the cart because there is not enough stock (%2$s remaining).', 'frozr-norsani' ), $product_data->get_name(), wc_format_stock_quantity_for_display( $product_data->get_stock_quantity(), $product_data ) ) );
			}

			/* Stock check - this time accounting for whats already in-cart*/
			if ( $product_data->managing_stock() ) {
				$products_qty_in_cart = WC()->cart->get_cart_item_quantities();

				if ( isset( $products_qty_in_cart[ $product_data->get_stock_managed_by_id() ] ) && ! $product_data->has_enough_stock( $products_qty_in_cart[ $product_data->get_stock_managed_by_id() ] + $quantity ) ) {
					throw new Exception( sprintf(
						'<a href="%s" class="button wc-forward">%s</a> %s',
						wc_get_cart_url(),
						__( 'View Cart', 'frozr-norsani' ),
						sprintf( __( 'You cannot add that amount to the cart &mdash; we have %1$s in stock and you already have %2$s in your cart.', 'frozr-norsani' ), wc_format_stock_quantity_for_display( $product_data->get_stock_quantity(), $product_data ), wc_format_stock_quantity_for_display( $products_qty_in_cart[ $product_data->get_stock_managed_by_id() ], $product_data ) )
					) );
				}
			}

			/* If cart_item_key is set, the item is already in the cart*/
			if ( $cart_item_key ) {
				$new_quantity = $quantity + WC()->cart->cart_contents[ $cart_item_key ]['quantity'];
				WC()->cart->set_quantity( $cart_item_key, $new_quantity, false );
			} else {
				$cart_item_key = $cart_id;

				/* Add item after merging with $cart_item_data - hook to allow plugins to modify cart item*/
				WC()->cart->cart_contents[ $cart_item_key ] = apply_filters( 'woocommerce_add_cart_item', array_merge( $cart_item_data, array(
					'product_id'	=> $product_id,
					'variation_id'	=> $variation_id,
					'variation' 	=> $variation,
					'quantity' 		=> $quantity,
					'data'			=> $product_data,
				) ), $cart_item_key );
			}

			if ( did_action( 'wp' ) ) {
				if ( ! WC()->cart->is_empty() ) {
					$cart_shs = md5( json_encode( WC()->cart->get_cart_for_session() ) );
					wc_setcookie( 'woocommerce_items_in_cart', 1 );
					wc_setcookie( 'woocommerce_cart_hash', $cart_shs );
				} elseif ( isset( $_COOKIE['woocommerce_items_in_cart'] ) ) {
					wc_setcookie( 'woocommerce_items_in_cart', 0, time() - HOUR_IN_SECONDS );
					wc_setcookie( 'woocommerce_cart_hash', '', time() - HOUR_IN_SECONDS );
				}
				do_action( 'woocommerce_set_cart_cookies', true );
			}

			do_action( 'woocommerce_add_to_cart', $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data );

			return $cart_item_key;

		} catch ( Exception $e ) {
			if ( $e->getMessage() ) {
				wp_send_json_error( $e->getMessage() );
			}
			return false;
		}
	}
	/**
	 * Get a refreshed cart fragment.
	 */
	public static function get_refreshed_fragments($product_id,$get_full_cart) {
		
		$refresh_page = isset( $_COOKIE['woocommerce_items_in_cart'] ) ? false : true;
		
		ob_start();
		
		/* Get mini cart*/
		woocommerce_mini_cart();

		$mini_cart = ob_get_clean();
		
		ob_start();
		wc_cart_totals_order_total_html();
		$cart_total = ob_get_clean();

		/*get cross items*/
		$crsel = ( null != (get_post_meta( $product_id, '_crosssell_ids', true )) ) ? get_post_meta( $product_id, '_crosssell_ids', true ) : array();
		/* Fragments and mini cart are returned*/
		$data = array(
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
					'#topcart .mini_cart' => $mini_cart,
					'amount' => $cart_total,
				)
			),
			'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() ),
			'count_items' => WC()->cart->get_cart_contents_count(),
			'refresh_page' => $refresh_page,
		);
		if (!empty($crsel)) {
			$title = '<i class="material-icons">check_circle</i> '. get_the_title( $product_id ). ' ' .__('was added to cart.','frozr-norsani');
			$desc = __('We also recommend these products with','frozr-norsani').' '.get_the_title( $product_id );
			$data['cross_sells'] = frozr_get_related_items($crsel,$title,$desc);
		}
		if ($get_full_cart) {
			ob_start();
			wc_get_template( 'cart/cart.php' );
			$data['full_cart'] = ob_get_clean();
		}

		wp_send_json( $data );
	}

	/**
	 * load home search location filter lists
	 *
	 */
	function frozr_adv_loc_filter() {

		check_ajax_referer( 'frozr_adv_loc_filter_nonce', 'security' );
		$loctype = wc_clean($_POST['loctype']);
		
		if ($loctype == 'default') {

			$svals = wc_clean($_POST['svals']);
			$termsarray = array();
			
			/*get all locations*/
			$getallocs = get_terms( 'location', 'hide_empty=0' );
			/*get all addresses*/
			$getallads = array_merge($getallocs, get_terms( 'vendor_addresses', 'hide_empty=0' ));			
			
			if ($svals == 'delivery') {
				if ( ! empty( $getallocs ) && ! is_wp_error( $getallocs ) ){
				foreach ( $getallocs as $term ) {
					echo "<li class=\"ui-screen-hidden\"><a href=\"#\" data-aft=\"refresh\" data-ajax=\"false\" data-loc=\"". $term->slug ."\" data-src=\"delivery\">" . $term->name . "</a></li>";
				} }
			} elseif ($svals == 'vendors') {
				if ( ! empty( $getallads ) && ! is_wp_error( $getallads ) ){
				foreach ( $getallads as $term ) {
					if (!in_array($term->slug, $termsarray)) {
					$termsarray[] = $term->slug;
					echo "<li class=\"ui-screen-hidden\"><a href=\"#\" data-aft=\"refresh\" data-ajax=\"false\" data-loc=\"". $term->slug ."\" data-src=\"vendors\">" . $term->name . "</a></li>";
					}
				} }
			} else {
				wp_send_json_error( __('Something Went Wrong!','frozr-norsani') );
			}

		}
		die();
	}

	/**
	 * save sellers settings
	 *
	 */
	function frozr_seller_settings() {

		check_ajax_referer( 'frozr_seller_settings_nonce', 'security' );
		
		if (!is_super_admin()) {
			wp_send_json( array('message' => __("Something went wrong!",'frozr-norsani')));		
			die(-1);
		}
		$user_id = intval($_POST['seller_edit_id']);
		$user_sel = esc_attr($_POST['seller_edit_selling']);
		$seller = get_user_by('id', $user_id);
		$store_info = frozr_get_store_info($user_id);
		
		update_user_meta($user_id, 'frozr_enable_selling', $user_sel);

		$msg_args = apply_filters('frozr_vendor_sts_change_msg_args',array(
			'to' => sanitize_email($seller->user_email),
			'id' => esc_attr($user_id),
			'shopname' => $store_info['store_name'],
			'type' => 'privileges',
		));
		do_action('frozr_send_vendor_status_message', $msg_args);
		
		wp_send_json( array('sts' => $user_sel, 'message' => __("Settings Saved!",'frozr-norsani')));				
		die();
	}

	/**
	 * setting the user location cookie
	 *
	 */
	function frozr_get_sellers_locs() {
		check_ajax_referer( 'frozr_set_user_loc', 'security' );
		$all_vendors_locs = frozr_get_all_sellers_locations('filtered');
		wp_send_json($all_vendors_locs);
		die();
	}
	/**
	 * setting the user location cookie
	 *
	 */
	function user_loc_cookie() {

		check_ajax_referer( 'frozr_set_user_loc', 'security' );

		$using_geo = frozr_is_using_geolocation();
		$getallocs = !$using_geo ? get_terms( 'location', 'fields=slugs&hide_empty=0' ) : array();
		$user_location = urldecode(sanitize_title(wp_unslash($_POST['userloc'])));
		$delivery_sellers = !empty($_POST['sellers']) ? implode('-',$_POST['sellers']) : '';
		$user_location_unslashed = wp_unslash($_POST['userloc']);
		$current_location = frozr_norsani_cookies();
		$default_contry = frozr_get_default_country();
		$user_country = frozr_get_client_ip(true);
		$message = __('Location set successfully','frozr-norsani');
		$success = true;


		if (!in_array($user_location, $getallocs) && !$using_geo || $using_geo && empty($delivery_sellers)) {
			$message = apply_filters('frozr_out_of_loaction_message', __('Your location is out the range of our delivery service.','frozr-norsani'));
			$success = false;
		}

		if ($user_location == $current_location) {
			$message = apply_filters('frozr_location_not_changed_message', __( 'You did not change your location!', 'frozr-norsani' ));
			$success = false;
			wp_send_json(array('message' => $message,'success' => $success,'usr_loc' => $user_location,'usr_loc_un' => $user_location_unslashed,'all_sell_locs' => $all_vendors_locs));
				die(-1);
			}

		if ($using_geo) {
			wc_setcookie('frozr_del_sellers', $delivery_sellers, time() + (86400 * 30), false);
		} elseif(!empty($delivery_sellers)) {
			$message = apply_filters('frozr_geo_ad_message', __('You will not be able to use the delivery service due to Geolocation access denied error.','frozr-norsani'));
			$success = false;
		}
		
		wc_setcookie('frozr_user_location', $user_location, time() + (86400 * 30), false);
		wc_setcookie('frozr_user_location_unslashed', $user_location_unslashed, time() + (86400 * 30), false);
		wc_empty_cart();

		do_action('frozr_after_save_user_location', $_POST);
		
		wp_send_json( apply_filters('frozr_user_locaation_set',array(
			'message' => $message,
			'success' => $success,
			'usr_loc' => $user_location,
			'usr_loc_un' => $user_location_unslashed,
		),$_POST));
		
		die();
	}

	/**
	 * Item delete action
	 *
	 */
	function delete_item() {

		check_ajax_referer( 'frozr_delete_item_nonce', 'security' );

		$item_id = isset( $_POST['itemid'] ) ? intval( $_POST['itemid'] ) : 0;
		$seller_id = get_current_user_id();
		
		if ( ! frozr_is_author( $item_id ) && ! is_super_admin() || ! current_user_can( 'frozer') && ! is_super_admin() || ! frozr_is_seller_enabled($seller_id) && ! is_super_admin() || $item_id == 0 ) {
			die(-1);
		}
		$result = wp_delete_post( $item_id );
		 
		 if (is_wp_error($result)) {
			wp_send_json_error( $result->get_error_message() );
		 } else {
			wp_send_json_success( __('Product was deleted!','frozr-norsani') );
		 }
		 
		die();
	}

	/**
	 * Save User Settings via AJAX 
	 */
	public static function save_vendor_settings() {

		check_ajax_referer( 'frozr_settings_nonce', 'security' );
		$store_id = get_current_user_id();
		if (!frozr_manual_vendor_online()) {
		$openclosetime = array(
			'Sat' => array('restop'=> wc_clean($_POST['rest_sat_open']), 'restshifts' => wc_clean($_POST['rest_sat_shifts']), 'open_one' => wc_clean($_POST['rest_sat_opening_one']), 'close_one' => wc_clean($_POST['rest_sat_closing_one']), 'open_two' => wc_clean($_POST['rest_sat_opening_two']), 'close_two' => wc_clean($_POST['rest_sat_closing_two'])),
			'Sun' => array('restop'=> wc_clean($_POST['rest_sun_open']), 'restshifts' => wc_clean($_POST['rest_sun_shifts']), 'open_one' => wc_clean($_POST['rest_sun_opening_one']), 'close_one' => wc_clean($_POST['rest_sun_closing_one']), 'open_two' => wc_clean($_POST['rest_sun_opening_two']), 'close_two' => wc_clean($_POST['rest_sun_closing_two'])),
			'Mon' => array('restop'=> wc_clean($_POST['rest_mon_open']), 'restshifts' => wc_clean($_POST['rest_mon_shifts']), 'open_one' => wc_clean($_POST['rest_mon_opening_one']), 'close_one' => wc_clean($_POST['rest_mon_closing_one']), 'open_two' => wc_clean($_POST['rest_mon_opening_two']), 'close_two' => wc_clean($_POST['rest_mon_closing_two'])),
			'Tue' => array('restop'=> wc_clean($_POST['rest_tue_open']), 'restshifts' => wc_clean($_POST['rest_tue_shifts']), 'open_one' => wc_clean($_POST['rest_tue_opening_one']), 'close_one' => wc_clean($_POST['rest_tue_closing_one']), 'open_two' => wc_clean($_POST['rest_tue_opening_two']), 'close_two' => wc_clean($_POST['rest_tue_closing_two'])),
			'Wed' => array('restop'=> wc_clean($_POST['rest_wed_open']), 'restshifts' => wc_clean($_POST['rest_wed_shifts']), 'open_one' => wc_clean($_POST['rest_wed_opening_one']), 'close_one' => wc_clean($_POST['rest_wed_closing_one']), 'open_two' => wc_clean($_POST['rest_wed_opening_two']), 'close_two' => wc_clean($_POST['rest_wed_closing_two'])),
			'Thu' => array('restop'=> wc_clean($_POST['rest_thu_open']), 'restshifts' => wc_clean($_POST['rest_thu_shifts']), 'open_one' => wc_clean($_POST['rest_thu_opening_one']), 'close_one' => wc_clean($_POST['rest_thu_closing_one']), 'open_two' => wc_clean($_POST['rest_thu_opening_two']), 'close_two' => wc_clean($_POST['rest_thu_closing_two'])),
			'Fri' => array('restop'=> wc_clean($_POST['rest_fri_open']), 'restshifts' => wc_clean($_POST['rest_fri_shifts']), 'open_one' => wc_clean($_POST['rest_fri_opening_one']), 'close_one' => wc_clean($_POST['rest_fri_closing_one']), 'open_two' => wc_clean($_POST['rest_fri_opening_two']), 'close_two' => wc_clean($_POST['rest_fri_closing_two'])),
		);
		update_user_meta( $store_id, 'rest_open_close_time', $openclosetime );
		}
		$frozr_settings = array(
			'store_name' => sanitize_text_field($_POST['frozr_store_name']),
			'socialfb' => esc_url($_POST['socialfb']),
			'socialtwitter' => esc_url($_POST['socialtwitter']),
			'socialinsta' => esc_url($_POST['socialinsta']),
			'socialyoutube' => esc_url($_POST['socialyoutube']),
			'payment' => array(),
			'phone' => esc_attr($_POST['setting_phone']),
			'show_email' => esc_attr( $_POST['setting_show_email']),
			'allow_email' => esc_attr( $_POST['setting_allow_email']),
			'accpet_order_type' => ($_POST['accept_order_types'][0] != '') ? array_map( 'wc_clean', $_POST['accept_order_types']) : array('delivery'),
			'shipping_fee' => floatval($_POST['shipping_fee']),
			'deliveryby' => esc_attr($_POST['deliveryby']),
			'shipping_pro_adtl_cost' => floatval($_POST['shipping_pro_adtl_cost']),
			'min_order_amt' => floatval($_POST['min_order_amt']),
			'shipping_fee_peak' => floatval($_POST['shipping_fee_peak']),
			'deliveryby_peak' => esc_attr($_POST['deliveryby_peak']),
			'shipping_pro_adtl_cost_peak' => floatval($_POST['shipping_pro_adtl_cost_peak']),
			'min_order_amt_peak' => floatval($_POST['min_order_amt_peak']),
			'banner' => intval($_POST['frozr_banner']),
			'gravatar' => intval($_POST['frozr_gravatar'])
		);
		if (!frozr_manual_vendor_online()) {
			$frozr_settings['accpet_order_type_cl'] = in_array('none', $_POST['accept_order_types_cl']) ? array('none') : array_map( 'wc_clean', $_POST['accept_order_types_cl']);
			$frozr_settings['allow_ofline_orders'] = esc_attr( $_POST['setting_allow_ofline_orders']);
		}
		if ( isset( $_POST['settings']['bank'] ) ) {
			$bank = $_POST['settings']['bank'];

			$frozr_settings['payment']['bank'] = apply_filters('frozr_save_bank_payment_settings', array(
				'ac_name' => sanitize_text_field( $bank['ac_name'] ),
				'ac_number' => sanitize_text_field( $bank['ac_number'] ),
				'bank_name' => sanitize_text_field( $bank['bank_name'] ),
				'bank_addr' => sanitize_text_field( $bank['bank_addr'] ),
				'swift' => sanitize_text_field( $bank['swift'] ),
			));
		}

		if ( isset( $_POST['settings']['paypal'] ) ) {
		$frozr_settings['payment']['paypal'] = apply_filters('frozr_save_paypal_payment_settings', array(
			'email' => sanitize_email( $_POST['settings']['paypal']['email'] )
		));
		}

		if ( isset( $_POST['settings']['skrill'] ) ) {
		$frozr_settings['payment']['skrill'] = apply_filters('frozr_save_skrill_payment_settings', array(
			'email' => sanitize_email( $_POST['settings']['skrill']['email'] )
		));
		}
		
		if ($_POST['frozr_vendor_store_notice']){update_user_meta($store_id, 'frozr_vendor_store_notice', wp_kses_post($_POST['frozr_vendor_store_notice']));}
		update_user_meta( $store_id, 'frozr_profile_settings', $frozr_settings );
		update_user_meta( $store_id, '_rest_meal_types', array_map( 'wc_clean', $_POST['rest_meal_types']) );
		update_user_meta( $store_id, '_rest_unavds', array_map( 'wc_clean', $_POST['rest_unads']) );
		/*update_user_meta( $store_id, 'frozr_food_type', array_map( 'wc_clean', $_POST['rest_food_type']) );*/
		update_user_meta( $store_id, 'frozr_peak_number', intval($_POST['peak_orders']));
		update_user_meta( $store_id, 'calculate_prepare_time', intval($_POST['calculate_prepare_time']));

		/*Save vendor addresses*/
		wp_set_object_terms( $store_id, wc_clean($_POST['setting_address']), 'vendor_addresses' );
		$rest_add_geo = !empty($_POST['addressgeo']) ? wc_clean($_POST['addressgeo']) : '';
		update_user_meta( $store_id, 'rest_address_geo', $rest_add_geo);

		/*Save vendor tags*/
		$rtvals = explode('-', $_POST['frozr_rest_tags']);
		foreach($rtvals as $key => $val) {
			$rtvals[$key] = trim($val);
		}
		$rt_vals = array_diff($rtvals, array(""));
		$restype = array_map( 'strval', $rt_vals );

		wp_set_object_terms( $store_id, $restype, 'vendorclass' );

		/*Save delivery locations*/
		if (frozr_is_using_geolocation()) {
			$delunfilterd = array();
			$delivery_locs = !empty($_POST['delivery_locs']) ? explode('/',$_POST['delivery_locs']) : array();
			$delivery_locs_filtered = !empty($_POST['delivery_locs_filtered']) ? explode('/',$_POST['delivery_locs_filtered']) : array();
			update_user_meta($store_id, 'delivery_location', $delivery_locs);
			
			if (!empty($delivery_locs_filtered)) {
				foreach ($delivery_locs_filtered as $vals) {
					$delunfilterd[] = explode(',', $vals);
				}
			}
			update_user_meta($store_id, 'delivery_location_filtered', $delunfilterd);
		} else {
			$vals = explode('-', $_POST['delivery_locations']);
			foreach($vals as $key => $val) {
				$vals[$key] = trim($val);
			}
			$loc_vals = array_diff($vals, array(""));
			$locs = array_map( 'strval', $loc_vals );

			wp_set_object_terms( $store_id, $locs, 'location' );
		}
		
		do_action( 'frozr_store_profile_saved', $store_id, $frozr_settings );

		wp_send_json_success( __('Settings Saved!','frozr-norsani') );
			
		die();
	}
	
	/**
	 * update product via AJAX
	 */
	function update_product() {

		check_ajax_referer( 'update_wc_product', 'security' );

		$seller_id = get_current_user_id();
		
		if ($_POST['product_id']) {

			/* Check permissions again and make sure we have what we need*/
			if ( empty( $_POST ) || empty( $_POST['product_id'] ) ) {
				die( -1 );
			}

			/* Check permissions again and make sure we have what we need*/
			if ( !current_user_can( 'frozer' ) && !is_super_admin() || ! frozr_is_author( absint($_POST['product_id']) ) && !is_super_admin() || !frozr_is_seller_enabled($seller_id) && !is_super_admin()) {
				die( -1 );
			}

			$product_id = absint( $_POST['product_id'] );

			$message = __('Product was updated successfully!','frozr-norsani');
			
		} else {

			/* Check permissions again and make sure we have what we need*/
			if ( !current_user_can( 'frozer' ) && !is_super_admin() || !frozr_is_seller_enabled($seller_id) && !is_super_admin()) {
				die( -1 );
			}
			$product_info = apply_filters('frozr_product_update_info_args',array(
				'post_type' => 'product',
				'post_title' => wc_clean($_POST['post_title']),
				'post_content' =>  wp_kses_post($_POST['post_content']),
				'post_excerpt' => wp_kses_post($_POST['post_excerpt']),
				'post_status' => 'publish',
				'comment_status' => 'open',
			));
			$message = __('Product was created successfully!','frozr-norsani');
			
			$product_id = wp_insert_post( $product_info );
			
			/* Add default sales*/
			add_post_meta( $product_id, 'total_sales', '0', true );
		}
		
		norsani()->item->frozr_process_item_meta($product_id);

		/* Clear cache/transients*/
		wc_delete_product_transients( $product_id );

		add_meta( $product_id );

		add_post_meta( $product_id, '_edit_last', $seller_id );

		/* Now that we have an ID we can fix any attachment anchor hrefs*/
		_fix_attachment_links( $product_id );
		
		if ($_POST['newitem']) {
			norsani()->item->frozr_edit_add_item_body(0,true);
		} else {
			wp_send_json( array(
				'msg' => $message,
				'linkd' => home_url( '/dashboard/items' ),
			));
		}
		
		die();
	}

	/**
	* shop url check
	*/
	function shop_url_check() {
        
		check_ajax_referer( 'new_vendor_nonce', 'security' );

		$url_slug = esc_attr($_POST['url_slug']);

		$check = true;

		$user = get_user_by( 'slug', $url_slug );

		if ( $user != '' ) {
			$check = false;
		}

		echo $check;

		die();
	}

	/**
	* Seller vendor page email contact form handler
	*
	* Catches the form submission from vendor page
	*/
    function contact_seller() {
		
		check_ajax_referer( 'frozr_contact_seller' );

        $vendor = get_user_by( 'id', (int) $_POST['seller_id'] );
		$store_info = frozr_get_store_info( $_POST['seller_id'] );

		if ( !$vendor || $store_info['allow_email'] != 1) {
			wp_send_json( array('message' => __("Something went wrong!",'frozr-norsani')));
			die(-1);
        }
		$msg_args = array (
		'to' => sanitize_email($vendor->user_email),
		'msg' => sanitize_text_field( $_POST['message'] ),
		);
		if (is_super_admin()) {
			if (!empty ($_POST['subject'])) {
				$msg_args['subject'] = sanitize_text_field($_POST['subject']);
			}
		} else {
				$msg_args['name'] = sanitize_text_field($_POST['name']);
				$msg_args['email'] = sanitize_text_field($_POST['email']);
		}
		
		do_action('frozr_send_vendor_message', $msg_args);

		wp_send_json( array('message' => __("Email sent successfully!",'frozr-norsani')));
		die();
	}

	/*user login via ajax on rating*/
	function rating_login( $credentials = array(), $secure_cookie = '' ) {

		check_ajax_referer( 'rating_user_login', 'security' );
		
		$email = esc_attr($_POST['rat_username']);
		$password = esc_attr($_POST['rat_password']);
		if ( empty($email) || empty($password) ) {
			wp_send_json( array('message' => __("Please fill the email and password fields.",'frozr-norsani')));
			die();
		}

		/*check user*/
		$logged_user = get_user_by('email',$email);
		
		if (!$logged_user) {
			wp_send_json( array('message' => __("Incorrect email address, please try again.",'frozr-norsani')));
			die();
		}
		$logcreds = array();
		$logcreds['user_login'] = $email;
		$logcreds['user_password'] = $password;
		$logcreds['remember'] = false;
		
		$user = wp_signon( $logcreds, false );
		
		if ( is_wp_error($user) ) {
			wp_send_json( array('message' => __("Incorrect password, please try again.",'frozr-norsani')));
			die();
		}

		die();
	}

	/* Save user rating*/
	function save_rest_rating() {

		check_ajax_referer( 'add_rest_review', 'security' );
		
		$customer = absint( get_post_meta( intval($_POST['order_id']), '_customer_user', true ) );
		$vendorid = intval($_POST['seller_id']);
		
		if ( $customer != get_current_user_id() ) {
			wp_send_json( array('message' => __("Sorry, you cant rate this vendor.",'frozr-norsani')));
			die(-1);
		}
		if ( $vendorid == get_current_user_id() ) {
			wp_send_json( array('message' => __("Sorry, you cant rate your self.",'frozr-norsani')));
			die(-1);
		}
		$rest_rate_orders = array();
		$rest_array = get_user_meta( $vendorid, 'rest_rating', true ) ? get_user_meta( $vendorid, 'rest_rating', true ) : array();
		if (is_array($rest_array)) {
			foreach($rest_array as $n => $v) {
				$rest_rate_orders[] = $n;
			}
		}
		if (in_array(intval($_POST['order_id']),$rest_rate_orders)) {
			wp_send_json( array('message' => __("You have already made your rating.",'frozr-norsani')));
			die(-1);
		}
		$rest_array[intval($_POST['order_id'])] = sanitize_text_field($_POST['restrating']);
		update_user_meta( $vendorid, 'rest_rating', $rest_array );
		
		wp_send_json( array('rating'=> norsani()->vendor->frozr_get_readable_seller_rating($vendorid),'message' => __("Thank you for rating this vendor.",'frozr-norsani')));
		die();
	}

	/*print order*/
	function dash_print_order() {

		check_ajax_referer( 'frozr_dash_print', 'security' );
		
		$order_post = get_post( intval($_POST['order_id']) );
		$author = frozr_get_order_author(intval($_POST['order_id']));

		if ( ! current_user_can( 'frozer' ) || $author != get_current_user_id() && !is_super_admin() ) {
			die(-1);
		}
		wp_send_json( array(
			'url' => home_url('/dashboard/orders/') . '?print=order&order_id='. intval($_POST['order_id']),
		));
	}
	
	/*print summary report*/
	function dash_print_summary_report() {

		check_ajax_referer( 'frozr_dash_print', 'security' );

		if ( ! current_user_can( 'frozer' ) ) {
			die(-1);
		}
		if (is_super_admin()) {
			if (esc_attr($_POST['rtype']) != 'custom') {
				wp_send_json( array(
					'url' => home_url('/dashboard/home/') . '?print=summary&rtype='. wc_clean($_POST['rtype']) . '&auser='. wc_clean($_POST['auser']),
				));
			} else {
				wp_send_json( array(
					'url' => home_url('/dashboard/home/') . '?print=summary&rtype='. wc_clean($_POST['rtype']) . '&startd='. wc_clean($_POST['startd']) . '&endd='. wc_clean($_POST['endd']) . '&auser='. wc_clean($_POST['auser']),
				));
			}
		} elseif (esc_attr($_POST['rtype']) != 'custom') {
			wp_send_json( array(
				'url' => home_url('/dashboard/home/') . '?print=summary&rtype='. wc_clean($_POST['rtype']),
			));
		} else {
			wp_send_json( array(
				'url' => home_url('/dashboard/home/') . '?print=summary&rtype='. wc_clean($_POST['rtype']) . '&startd='. wc_clean($_POST['startd']) . '&endd='. wc_clean($_POST['endd']),
			));
		}
		/* Quit out*/
		die();
	}
	/*get dashboard reports data via ajax*/
	function get_totals_data() {

		check_ajax_referer( 'get_dash_totals', 'security' );

		if ( ! current_user_can( 'frozer' ) ) {
			die(-1);
		}
		if (is_super_admin()) {
			if (esc_attr($_POST['rtype']) != 'custom') {
				frozr_dashboard_totals(wc_clean($_POST['rtype']), '','',wc_clean($_POST['auser']));
			} else {
				frozr_dashboard_totals(wc_clean($_POST['rtype']), wc_clean($_POST['startd']), wc_clean($_POST['endd']), wc_clean($_POST['auser']));
			}
		} elseif (esc_attr($_POST['rtype']) != 'custom') {
			frozr_dashboard_totals(wc_clean($_POST['rtype']));
		} else {
			frozr_dashboard_totals(wc_clean($_POST['rtype']), wc_clean($_POST['startd']), wc_clean($_POST['endd']));
		}
		/* Quit out*/
		die();
	}
}
return new Norsani_Ajax();