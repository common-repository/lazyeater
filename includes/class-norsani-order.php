<?php
/**
 * All Related Norsani Order Management Functions
 *
 * @package Norsani
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

class Norsani_Order {
	
	/**
	 * The single instance of the class.
	 *
	 * @var Norsani_Order
	 * @since 1.9
	 */
	protected static $_instance = null;
	
	/**
	 * Main Norsani_Order Instance.
	 *
	 * @since 1.9
	 * @static
	 * @return Norsani_Order - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Norsani_Order Constructor.
	 */
	public function __construct() {

		add_action( 'wp_trash_post', array($this, 'frozr_admin_on_trash_order') );
		add_action( 'delete_post', array($this, 'frozr_admin_on_delete_order') );
		add_action( 'woocommerce_checkout_process', array($this, 'frozr_if_order_is_valid'), 10 );
		add_action( 'wp_untrash_post', array($this, 'frozr_admin_on_untrash_order') );
		add_action( 'woocommerce_checkout_order_processed', array($this, 'frozr_create_sub_order'), 10, 3 );
		add_action( 'woocommerce_order_status_changed', array($this, 'frozr_on_order_status_change'), 10, 4 );
		add_action ('woocommerce_before_checkout_billing_form', array($this, 'frozr_checkout_billing_form_title'),10,1);
		
		add_filter( 'woocommerce_email_recipient_new_order', array($this, 'frozr_new_order_vendor_email'), 10, 2 );
		add_filter('woocommerce_billing_fields', array($this, 'frozr_modified_billing_fields'),10,2);
		add_filter('woocommerce_order_item_name', array($this, 'frozr_add_store_name_to_email_item'),10,3);
		add_filter('woocommerce_checkout_fields',array($this, 'frozr_modified_checkout_fields'),10,1);
		add_filter('woocommerce_my_account_get_addresses', array($this, 'frozr_change_billing_title'),10,1);
		add_filter('woocommerce_attribute_label', array($this, 'frozr_output_custom_attributes'),10,3);
		
		do_action( 'norsani_vendor_loaded' );
	}

	/**
	 * Output Norsani custom order attributes
	 *
	 * @return void
	 */
	public function frozr_output_custom_attributes($label, $name, $product) {
		
		switch ($label) {
			case 'order-type':
			return __('Order Type', 'frozr-norsani');
			break;
			case 'special-comments':
			return __('Comments', 'frozr-norsani');
			break;
			case 'people-in':
			return __('People In', 'frozr-norsani');
			break;
			case 'car-info':
			return __('Car Info', 'frozr-norsani');
			break;
			case 'promotions':
			return __('Promotions', 'frozr-norsani');
			break;
		}
		return $label;
	}
	
	/**
	 * Orders page nav
	 *
	 * @return void
	 */
	public function frozr_order_listing_status_filter() {
		$orders_url = home_url( '/dashboard/orders/');
		$status_class = isset( $_GET['order_status'] ) ? $_GET['order_status'] : 'processing';
		
		ob_start();
		frozr_get_template('views/html-dashboard-orders-nav.php', array('orders_url' => $orders_url, 'status_class' => $status_class));
		echo apply_filters('frozr_dashboard_orders_page_nav_html',ob_get_clean(), $orders_url, $status_class);
	}
	
	/**
	 * Orders list table
	 *
	 * @return void
	 */
	public function frozr_orders_table () {
		global $post, $wp_query;
		$order_status = isset( $_GET['order_status'] ) ? sanitize_key( $_GET['order_status'] ) : 'all';

		$orders_table_atts = 'data-role="table" id="orders-table" data-mode="reflow"';

		ob_start();
		
		frozr_get_template('views/html-dashboard-orders-list.php', array('post' => $post, 'wp_query' => $wp_query, 'orders_table_atts' => $orders_table_atts, 'order_status' => $order_status));

		if ( $wp_query->max_num_pages > 1 ) {
			frozr_lazy_nav_below(true);
		}
		
		echo apply_filters('frozr_dashboard_orders_page_list_html',ob_get_clean(), $post, $wp_query, $orders_table_atts, $order_status);
		
		wp_reset_query();
	}
	
	/**
	 * Orders list rows
	 *
	 * @return void
	 */
	public function frozr_orders_lists($order_status) {
		global $post;
		
		if ( have_posts() ) {
			while (have_posts()) { the_post();
			$sub_orders = get_children( array( 'post_parent' => $post->ID, 'post_type' => 'shop_order' ) );
			if ( !$sub_orders ) {
				$this->frozr_order_listing_body($post->ID, $order_status);
			}
			}
		} else {
			/*colspan count for no orders found*/
			if(frozr_mobile()) {
				$colspan = $order_status == 'processing' ? 3 : 2;
			} else {
				$colspan = $order_status == 'processing' ? 6 : 5;
			}
			if (is_super_admin()) {
				$colspan = $colspan + 1;
			}
			
			ob_start();
			frozr_get_template('views/html-dashboard-orders-list_no_results.php', array('colspan' => $colspan));
			echo apply_filters('frozr_dashboard_orders_page_list_no_results_html',ob_get_clean(), $colspan);
		}
	}

	/**
	 * Get orders total for sellers
	 *
	 * @param object $order
	 * @param bool $inc_fee		Include delivery fee or not?
	 * @param bool $inc_sub		Inlcude the order sub total or not?
	 * @return string
	 */
	public function frozr_get_seller_total_order( $order, $inc_fee = true, $inc_sub = true) {
		$order_sub_total = 0;
		$delivery_fee_total = 0;
		$order_final_sub = 0;

		if ( $order->get_fees() && $inc_fee == true) {
			$fees = $order->get_fees();
			foreach ( $fees as $id => $fee ) {
				if ($fee['name'] == "Total Delivery") {
				$delivery_fee_total = $fee['line_total'];
				}
			}
		}
		if ($inc_sub) { $order_sub_total = $order->get_subtotal();
		$order_final_sub = apply_filters('frozr_order_final_sub_total', $order_sub_total, $order);
		}

		return apply_filters('frozr_get_seller_total_order', $order_final_sub + $delivery_fee_total);
	}

	/**
	 * Calculate the net amount a seller gets after an order
	 *
	 * @param object $order
	 * @param float $order_del		Calculate according to a giving delivery fee.
	 * @return array
	 */
	public function frozr_calculate_order_fees($order, $order_del = '') {
		$order_total = frozr_get_seller_total_order($order, true, true);
		$order_post = get_post( $order->get_id() );
		$order_seller = frozr_get_order_author($order->get_id());
		$order_customer = $order->get_user_id();
		$frozr_option = get_option( 'frozr_fees_settings' );
		$fees_options = (! empty( $frozr_option['frozr_lazy_fees']) ) ? $frozr_option['frozr_lazy_fees'] : '';

		/* Applicable fees*/
		$applicable_fees_data = array();
		$applicable_fees = array();

		if ($fees_options) {
		/* Get the order sub total*/
		$order_sub_total = frozr_get_seller_total_order($order, false, true);

		/* Get the order delivery fee*/
		$order_delivery = !empty($order_del) ? $order_del : frozr_get_seller_total_order($order, true, false);

		/* Total fees*/
		$total_fee = array();

		/* Total amount effected*/
		$amount_effected = array();

		/* Default result for testing*/
		$matching_rule = false;
				
		/* Start testing for matching fees rules*/
		foreach ($fees_options as $fees_rules) {
			if ($fees_rules['customers_effected'] == 'all' && $fees_rules['sellers_effected'] == 'all' ) {
				$matching_rule = true;
			} elseif ($fees_rules['customers_effected'] == 'all' && $fees_rules['sellers_effected'] == 'all_but') {
				if (!in_array($order_seller, $fees_rules['sellers'])) {				
					$matching_rule = true;
				}
			} elseif ($fees_rules['customers_effected'] == 'all' && $fees_rules['sellers_effected'] == 'specific') {
				if (in_array($order_seller, $fees_rules['sellers'])) {				
					$matching_rule = true;
				}
			} elseif ($fees_rules['customers_effected'] == 'all_but' && $fees_rules['sellers_effected'] == 'all') {
				if (!in_array($order_customer, $fees_rules['customers'])) {				
					$matching_rule = true;
				}
			} elseif ($fees_rules['customers_effected'] == 'all_but' && $fees_rules['sellers_effected'] == 'all_but') {
				if (!in_array($order_customer, $fees_rules['customers']) && !in_array($order_seller, $fees_rules['sellers'])) {				
					$matching_rule = true;
				}
			} elseif ($fees_rules['customers_effected'] == 'all_but' && $fees_rules['sellers_effected'] == 'specific') {
				if (!in_array($order_customer, $fees_rules['customers']) && in_array($order_seller, $fees_rules['sellers'])) {				
					$matching_rule = true;
				}
			} elseif ($fees_rules['customers_effected'] == 'specific' && $fees_rules['sellers_effected'] == 'all') {
				if (in_array($order_customer, $fees_rules['customers'])) {				
					$matching_rule = true;
				}
			} elseif ($fees_rules['customers_effected'] == 'specific' && $fees_rules['sellers_effected'] == 'all_but') {
				if (in_array($order_customer, $fees_rules['customers']) && !in_array($order_seller, $fees_rules['sellers'])) {				
					$matching_rule = true;
				}
			} elseif ($fees_rules['customers_effected'] == 'specific' && $fees_rules['sellers_effected'] == 'specific') {
				if (in_array($order_customer, $fees_rules['customers']) && in_array($order_seller, $fees_rules['sellers'])) {				
					$matching_rule = true;
				}
			}
			if ($fees_rules['payment_method'] != 'any' && $fees_rules['payment_method'] != $order->get_payment_method()) {
					$matching_rule = false;
			}
			if ($fees_rules['order_amount'] > $order_sub_total) {
					$matching_rule = false;
			}

			if ($matching_rule) {
				$applicable_fees_data[$fees_rules['fee_title']] = array('rate' => $fees_rules['rate'],'description' => $fees_rules['description'], 'effected_amount' => $fees_rules['amount_effect']);
				$applicable_fees[] = array($fees_rules['rate'], $fees_rules['amount_effect']);
			}
			
			$matching_rule = false;
		}
		}

		if (!empty ($applicable_fees)) {
				
			foreach ($applicable_fees as $fee) {
				$rate_one = $fee[0]['rate_one'];
				$rate_two = $fee[0]['rate_two'];
				$action = $fee[0]['action'];
				
				$order_sub_net = ($order_sub_total * $rate_one)/100;
				$delivery_net = ($order_delivery * $rate_one)/100;
						
				if ($fee[1] == 'order_total') {
					if ($order_sub_net > -1) {
						if (!empty($rate_two) && $rate_two > 0) {
						if($action == 'minus') {
						$order_sub_net = $order_sub_net - $rate_two;
						} elseif($action == 'multiply' && $delivery_net > 0) {
						$order_sub_net = $order_sub_net * $rate_two;
						} elseif ($action == 'plus') {
						$order_sub_net = $order_sub_net + $rate_two;
						}
						}
						if ($order_sub_net > -1) {
						$order_sub_total = $order_sub_total - $order_sub_net;
						}
					}
				} elseif ($fee[1] == 'delivery') {

					if ($delivery_net > -1) {
						if (!empty($rate_two) && $rate_two > 0) {
						if($action == 'minus') {
						$delivery_net = $delivery_net - $rate_two;
						} elseif($action == 'multiply' && $delivery_net > 0) {
						$delivery_net = $delivery_net * $rate_two;
						} elseif ($action == 'plus') {
						$delivery_net = $delivery_net + $rate_two;
						}
						}
						if ($delivery_net > -1) {
						$order_delivery = $order_delivery - $delivery_net;
						}
					}
					
				} else {
					$total_applied_percent = $order_sub_net + $delivery_net;
					if ($total_applied_percent > -1) {
					if (!empty($rate_two) && $rate_two > 0) {
					if($action == 'minus') {
					$total_applied_fee = $total_applied_percent - $rate_two;
					} elseif($action == 'multiply' && $total_applied_percent > 0) {
					$total_applied_fee = $total_applied_percent * $rate_two;
					} elseif ($action == 'plus') {
					$total_applied_fee = $total_applied_percent + $rate_two;
					}
					}
					
					if ($total_applied_fee > -1) {
					$order_sub_total = $order_sub_total + $order_delivery - $total_applied_fee;
					$order_delivery = 0;
					}
					
					}
				}
			}
			$order_net = $order_delivery + $order_sub_total;
		} else {
			$order_net = $order_total;
		}

		return apply_filters('frozr_order_seller_net_profit',array('total_profit' => $order_net, 'fee_details' => $applicable_fees_data), $order );
	}

	/**
	 * Orders list single row
	 *
	 * @param int $order_id
	 * @param string $sts		Order status.
	 * @return void
	 */
	public function frozr_order_listing_body($order_id, $sts) {
		global $post;

		$order_post = get_post( $order_id );
		$the_order = wc_get_order( $order_id );
		$order_vendor = frozr_get_order_author($order_id);
		$order_author = frozr_get_store_info($order_vendor);

		/*colspan count for quick view*/
		if(frozr_mobile()) {
			$colspan = $sts == 'processing' ? 3 : 2;
		} else {
			$colspan = $sts == 'processing' ? 6 : 5;
		}
		if (is_super_admin()) {
			$colspan = $colspan + 1;
		}

		ob_start();
		frozr_get_template('views/html-dashboard-orders-list-row.php', array('post' => $post, 'order_id' => $order_id, 'colspan' => $colspan, 'sts' => $sts, 'order_author'=> $order_author, 'order_vendor' => $order_vendor, 'the_order' => $the_order, 'order_post' => $order_post));
		echo apply_filters('frozr_dashboard_page_orders_list_row_html',ob_get_clean(), $post, $order_id, $colspan, $sts, $order_author, $order_vendor, $the_order, $order_post);
	}

	/**
	 * Orders list single row actions
	 *
	 * @param object $the_order
	 * @param int $order_id
	 * @return void
	 */
	public function frozr_order_actions($the_order,$order_id) {
		ob_start();
		frozr_get_template('views/html-dashboard-orders-list-row_actions.php', array('the_order' => $the_order, 'order_id' => $order_id));
		echo apply_filters('frozr_dashboard_page_orders_list_row_html',ob_get_clean(), $the_order, $order_id);
	}
	
	/**
	 * Get the preparation time selected by customer for a single order
	 *
	 * @param object $the_order
	 * @return void
	 */
	public function frozr_get_order_pre_time($the_order) {
		$order_timestamp = $the_order->get_date_created() ? $the_order->get_date_created()->getTimestamp() : '';

		if ( ! $order_timestamp ) {
			echo '&ndash;';
			return;
		}
		
		$get_pretim = $the_order->get_date_created()->date_i18n( 'Y-m-d H:i' );
		$today = new DateTime(date('Y-m-d H:i',strtotime(current_time('mysql'))));
		$ord_t = new DateTime(date('Y-m-d H:i',strtotime($get_pretim)));
		$ord_time = $ord_t->diff($today);
		$sep = '<span class="frozr_ord_pros">'.__('Processing','frozr-norsani').'</span>';

		if ($ord_t > $today) {
			$days = $ord_time->d > 0 ? $ord_time->d .' '._n( 'day', 'days', $ord_time->d, 'frozr-norsani' ).' ': '';
			$hour = $ord_time->h > 0 ? $ord_time->h .' '._n( 'hour', 'hours', $ord_time->h, 'frozr-norsani' ).' ': '';
			$min = $ord_time->i > 0 ? $ord_time->i.' '._n('min', 'mins',$ord_time->i,'frozr-norsani'): '';
			$show_date = '<span class="frozr_ord_pros frozr_queue">'.__('In queue','frozr-norsani').'</span>'.esc_html(__('After','frozr-norsani').' '.$days.$hour.$min);
		} elseif ( $order_timestamp > strtotime( '-1 day', current_time( 'timestamp', true ) ) && $order_timestamp <= current_time( 'timestamp', true ) ) {
			$show_date = sprintf(
				/* translators: %s: human-readable time difference */
				_x( '%1$s %2$s ago', '%2$s = human-readable time difference', 'frozr-norsani' ),
				$sep,
				human_time_diff( $the_order->get_date_created()->getTimestamp(), current_time( 'timestamp', true ) )
			);
		} else {
			$show_date = $sep.$the_order->get_date_created()->date_i18n( 'M j, Y' );
		}
		printf(
			'<time datetime="%1$s" title="%2$s">%3$s</time>',
			esc_attr( $the_order->get_date_created()->date( 'c' ) ),
			esc_html( $the_order->get_date_created()->date_i18n( frozr_get_time_date_format('date_time') ) ),
			$show_date
		);
		
		do_action('frozr_after_order_pre_time_function', $the_order);
	}

	/**
	 * Get the order receive time
	 *
	 * @param object $post
	 * @return void
	 */
	public function frozr_order_time($post) {
		$t_time		= get_post_meta($post->ID,'_order_pretime',true) ? get_post_meta($post->ID,'_order_pretime',true) : get_the_time( 'Y-m-d h:i a', $post );
		$gmt_date	= get_gmt_from_date($t_time);
		$gmt_time	= strtotime( $gmt_date . ' UTC' );
		$time_diff	= current_time( 'timestamp', 1 ) - $gmt_time;

		if ( $time_diff > 0 && $time_diff < 24 * 60 * 60 ) {
			$show_date = sprintf(
				/* translators: %s: human-readable time difference */
				_x( '%s ago', '%s = human-readable time difference', 'frozr-norsani' ),
				human_time_diff( $gmt_time, current_time( 'timestamp', true ) )
			);
		} else {
			$show_date = date_i18n('M j, Y', strtotime($t_time));
		}
		printf(
			'<time datetime="%1$s" title="%2$s">%3$s</time>',
			esc_attr( $t_time ),
			esc_html( date_i18n(frozr_get_time_date_format('date_time'), strtotime($t_time))),
			esc_html( $show_date )
		);
	}
	
	/**
	 * Orders quick view body
	 *
	 * @param object $order
	 * @param bool $rest	Is the order comming from a REST call or not?
	 * @return void
	 */
	public function frozr_order_quick_view_body($order,$rest=false) {
		global $wpdb;

		/* Get line items*/
		$line_items			= $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
		$line_items_fee		= $order->get_items( 'fee' );
		$show_tax_columns = $legacy_order = $check_item	= $tax_data = $tax_classes = $order_taxes = '';
		$classes_options	= array();

		if ( wc_tax_enabled() ) {
			$order_taxes		= $order->get_taxes();
			$tax_classes		= WC_Tax::get_tax_classes();
			$classes_options['']	= __( 'Standard', 'frozr-norsani' );

			if ( ! empty( $tax_classes ) ) {
				foreach ( $tax_classes as $class ) {
					$classes_options[ sanitize_title( $class ) ] = $class;
				}
			}

			/* Older orders won't have line taxes so we need to handle them differently :(*/
			$tax_data = '';
			if ( $line_items ) {
				$check_item	= current( $line_items );
				$tax_data	= maybe_unserialize( isset( $check_item['line_tax_data'] ) ? $check_item['line_tax_data'] : '' );
			} elseif ( $line_items_fee ) {
				$check_item	= current( $line_items_fee );
				$tax_data	= maybe_unserialize( isset( $check_item['line_tax_data'] ) ? $check_item['line_tax_data'] : '' );
			}

			$legacy_order		= ! empty( $order_taxes ) && empty( $tax_data ) && ! is_array( $tax_data );
			$show_tax_columns	= ! $legacy_order || sizeof( $order_taxes ) === 1;
		}
		ob_start();
		foreach ( $line_items as $item_id => $item ) {
			$_product	= $order->get_product_from_item( $item );
			
			wc_get_template( 'html-order-item-quick.php', array( 'rest' => $rest, 'item_id' => $item_id, 'item' => $item, 'show_tax_columns' => $show_tax_columns, 'legacy_order' => $legacy_order, 'check_item' => $check_item, 'tax_data' => $tax_data, 'classes_options' => $classes_options, 'tax_classes' => $tax_classes, 'order_taxes' => $order_taxes, 'line_items_fee' => $line_items_fee, 'order' => $order, '_product' => $_product ), '', NORSANI_PATH . '/templates/orders/');
			do_action( 'woocommerce_order_item_' . $item['type'] . '_html', $item_id, $item, $order );
		}
		do_action( 'frozr_order_quick_view_after_line_items', $order->get_id() );

		return ob_get_clean();
	}

	/**
	 * Single Order items table
	 *
	 * @param object $order
	 * @param bool $rest	Is the order comming from a REST call or not?
	 * @return void
	 */
	public function frozr_order_items_table($order, $rest=false) {
		global $wpdb;

		/* Get line items*/
		$line_items			= $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
		$line_items_fee		= $order->get_items( 'fee' );
		$classes_options	= array();
		$show_tax_columns = $legacy_order = $check_item	= $tax_data = $tax_classes = $order_taxes = '';

		if ( wc_tax_enabled() ) {
			$order_taxes			= $order->get_taxes();
			$tax_classes			= WC_Tax::get_tax_classes();
			$classes_options		= array();
			$classes_options['']	= __( 'Standard', 'frozr-norsani' );

			if ( ! empty( $tax_classes ) ) {
				foreach ( $tax_classes as $class ) {
					$classes_options[ sanitize_title( $class ) ] = $class;
				}
			}

			/* Older orders won't have line taxes so we need to handle them differently :(*/
			$tax_data = '';
			if ( $line_items ) {
				$check_item	= current( $line_items );
				$tax_data	= maybe_unserialize( isset( $check_item['line_tax_data'] ) ? $check_item['line_tax_data'] : '' );
			} elseif ( $line_items_fee ) {
				$check_item	= current( $line_items_fee );
				$tax_data	= maybe_unserialize( isset( $check_item['line_tax_data'] ) ? $check_item['line_tax_data'] : '' );
			}

			$legacy_order		= ! empty( $order_taxes ) && empty( $tax_data ) && ! is_array( $tax_data );
			$show_tax_columns	= ! $legacy_order || sizeof( $order_taxes ) === 1;
		}
		
		$args = array(
			'legacy_order' => $legacy_order,
			'check_item' => $check_item,
			'tax_data' => $tax_data,
			'classes_options' => $classes_options,
			'tax_classes' => $tax_classes,
			'order_taxes' => $order_taxes,
			'show_tax_columns' => $show_tax_columns,
			'line_items_fee' => $line_items_fee,
			'line_items' => $line_items,
			'wpdb' => $wpdb,
			'order' => $order,
			'rest' => $rest,
		);
		
		ob_start();
		frozr_get_template('views/html-dashboard-orders-single-items.php', $args);
		echo apply_filters('frozr_dashboard_page_order_single_items_html',ob_get_clean(), $args);
	}

	/**
	 * Get single order general details
	 *
	 * @param object $order
	 * @return void
	 */
	public function frozr_order_general_details($order) {
		$order_cod_option = get_post_meta( $order->get_id(), 'frozr_cod_option_sts', true );
		$cod_notice = ($order->get_payment_method() == 'cod' && $order_cod_option != 1) ? __('Note: The website fees of this order will be deducted from the current vendor balance. If the balance is insufficient, it will go in minus.','frozr-norsani') : '';
		$calculate_profits = $this->frozr_calculate_order_fees($order);
		$seller_profit = floatval($calculate_profits['total_profit']);
		$website_profit = floatval(frozr_get_seller_total_order($order, true, true)) - $seller_profit;
		$currency = get_woocommerce_currency_symbol();

		$args = array(
			'order' => $order,
			'order_cod_option' => $order_cod_option,
			'cod_notice' => $cod_notice,
			'calculate_profits' => $calculate_profits,
			'seller_profit' => $seller_profit,
			'website_profit' => $website_profit,
			'currency' => $currency,
		);
		
		ob_start();
		frozr_get_template('views/html-dashboard-orders-single-general_details.php', $args);
		echo apply_filters('frozr_dashboard_page_order_single_general_details_html',ob_get_clean(), $args);
		
	}

	/**
	 * Order customer details
	 *
	 * @param object $order
	 * @return void
	 */
	public function frozr_order_customer_details($order) {

		ob_start();
		frozr_get_template('views/html-dashboard-orders-single-customer_details.php', array('order' => $order));
		echo apply_filters('frozr_dashboard_page_order_single_customer_details_html',ob_get_clean(), $order);
	}

	/**
	 * Update the child order status when a parent order status is changed
	 *
	 * @param int $order_id
	 * @param string $old_status
	 * @param string $new_status
	 * @param object $order
	 * @return void
	 */
	public function frozr_on_order_status_change( $order_id, $old_status, $new_status, $order ) {

		$order_post = get_post( $order_id );
		$order_seller = frozr_get_order_author($order_id);;
		$parent_order_id = $order_post->post_parent;
		$is_parent_order = get_post_meta( $order_id, 'has_sub_order', true );
		$option = get_option( 'frozr_fees_settings' );
		$cod_option = isset($option['frozr_lazy_fees_cod']) ? 1 : 0;

		/* if any child orders found, change the orders as well*/
		$sub_orders = get_children( array( 'post_parent' => $order_id, 'post_type' => 'shop_order' ) );
		if ( $sub_orders && $is_parent_order ) {
			foreach ($sub_orders as $order_posts) {
				$p_order = wc_get_order($order_posts->ID);
				$p_order->update_status( $new_status );
			}
		}

		/* get all the child orders and monitor the status*/
		$psub_orders = get_children( array( 'post_parent' => $parent_order_id, 'post_type' => 'shop_order' ) );

		if ( $psub_orders && $is_parent_order ) {
		
			/* return if any child order is not completed*/
			$all_complete = true;
		
			foreach ($psub_orders as $sub) {
				$pp_order = wc_get_order($sub->ID);
				if ( $pp_order->get_status() != 'completed' ) {
					$all_complete = false;
				}
			}
			/* seems like all the child orders are completed*/
			/* mark the parent order as complete*/
			if ( $all_complete ) {
				$parent_order = wc_get_order( $parent_order_id );
				$parent_order->update_status( 'completed', __( 'The parent order was marked as "completed" because all child orders ware completed.', 'frozr-norsani' ) );
			}
		}

		/*Add refund date*/
		if ( $new_status == 'refunded' ) {
			update_post_meta($order_id, '_refunded_date', current_time('ymd'));
		}

		/*Add user balance*/
		if ( $new_status == 'completed' && !$is_parent_order) {

			$seller_profit = $this->frozr_calculate_order_fees($order);

			$seller_profit_val = apply_filters('frozr_total_user_profit_from_order', floatval($seller_profit['total_profit']), $order);
			$website_profit = apply_filters('frozr_total_website_profit_from_order', floatval(frozr_get_seller_total_order($order, true, true)) - $seller_profit_val, $order, $seller_profit_val);
			$user_current_balance = floatval(get_user_meta($order_seller,"_vendor_balance", true));
			
			if ($cod_option != 1 && $order->get_payment_method() == 'cod') {
				$seller_new_balance = apply_filters('frozr_final_value_cod', $user_current_balance - $website_profit, $order_id, $old_status, $new_status, $order);
			} else {
				$seller_new_balance = apply_filters('frozr_final_value_no_cod', $seller_profit_val + $user_current_balance, $order_id, $old_status, $new_status, $order);
			}
			update_user_meta($order_seller, "_vendor_balance",$seller_new_balance);
			update_post_meta($order_id, 'frozr_cod_option_sts', $cod_option);
			update_post_meta($order_id, 'frozr_fees_on_order', apply_filters('frozr_order_fees', $seller_profit['fee_details'], $order_id, $old_status, $new_status, $order));
			update_post_meta($order_id, 'frozr_order_seller_profit', apply_filters('frozr_seller_profit', $seller_profit_val, $order_id, $old_status, $new_status, $order));
			update_post_meta($order_id, 'frozr_order_website_fee', apply_filters('frozr_website_fee', $website_profit, $order_id, $old_status, $new_status, $order));

			do_action('frozr_after_profits_distributed',$seller_profit,$order,$order_seller);

			do_action('frozr_send_customer_rating_request_email',$order);
		}
	}

	/**
	 * Delete sub orders when parent order is trashed
	 *
	 * @param int $post_id
	 * @return void
	 */
	public function frozr_admin_on_trash_order( $post_id ) {
		$post = get_post( $post_id );

		if ( $post->post_type == 'shop_order' && $post->post_parent == 0 ) {
			$sub_orders = get_children( array( 'post_parent' => $post_id, 'post_type' => 'shop_order' ) );

			if ( $sub_orders ) {
				foreach ($sub_orders as $order_post) {
					wp_trash_post( $order_post->ID );
				}
			}
		}
	}

	/**
	 * Untrash sub orders when parent orders are untrashed
	 *
	 * @param int $post_id
	 * @return void
	 */
	public function frozr_admin_on_untrash_order( $post_id ) {
		$post = get_post( $post_id );

		if ( $post->post_type == 'shop_order' && $post->post_parent == 0 ) {
			$sub_orders = get_children( array( 'post_parent' => $post_id, 'post_type' => 'shop_order' ) );

			if ( $sub_orders ) {
				foreach ($sub_orders as $order_post) {
					wp_untrash_post( $order_post->ID );
				}
			}
		}
	}

	/**
	 * Delete sub orders and from frozr sync table when a order is deleted
	 *
	 * @param int $post_id
	 * @return void
	 */
	public function frozr_admin_on_delete_order( $post_id ) {
		$post = get_post( $post_id );

		if ( $post->post_type == 'shop_order' ) {
			$sub_orders = get_children( array( 'post_parent' => $post_id, 'post_type' => 'shop_order' ) );

			if ( $sub_orders ) {
				foreach ($sub_orders as $order_post) {
					wp_delete_post( $order_post->ID );
				}
			}
		}
	}

	/**
	 * Get the product subtotal amount
	 *
	 * @param object $product
	 * @param int $quantity
	 * @return string
	 */
	public function frozr_get_product_subtotal( $product, $quantity ) {
		$price = $product->get_price();

		if ( $product->is_taxable() ) {

			if ( 'excl' === WC()->cart->tax_display_cart ) {

				$row_price        = wc_get_price_excluding_tax( $product, array( 'qty' => $quantity ) );
				$product_subtotal = $row_price;
			} else {
				$row_price        = wc_get_price_including_tax( $product, array( 'qty' => $quantity ) );
				$product_subtotal = $row_price;
			}
		} else {
			$row_price        = $price * $quantity;
			$product_subtotal = $row_price;
		}

		return apply_filters( 'frozr_cart_product_subtotal', $product_subtotal, $product, $quantity );
	}

	/**
	 * Check if the order is valid or not according to Norsani conditions
	 *
	 * @return void|exception
	 */
	public function frozr_if_order_is_valid() {
		$sellers = array();
		
		$checkout = WC()->checkout();
		
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$seller_id = get_post_field( 'post_author', $cart_item['product_id'] );
			$sellers[$seller_id][$cart_item_key] = $cart_item;
		}

		foreach ($sellers as $sellerid => $seller_products ) {
			$total_items = array();
			$has_delivery = false;
			$profile_info = frozr_get_store_info( $sellerid );
			$min_order = $profile_info['min_order_amt'];
			$store_name = $profile_info['store_name'];
			$order_time = $checkout->get_value( 'frozr_ord_pre_time_'.$sellerid ) ? $checkout->get_value( 'frozr_ord_pre_time_'.$sellerid ) : null;
			$order_date = $checkout->get_value( 'frozr_ord_pre_date_'.$sellerid ) ? $checkout->get_value( 'frozr_ord_pre_date_'.$sellerid ) : null;
			$order_date_changed = $checkout->get_value( 'frozr_order_time_changed_'.$sellerid ) ? $checkout->get_value( 'frozr_order_time_changed_'.$sellerid ) : null;
			$total_pre = array();
			
			$ord_t = new DateTime(date('Y-m-d H:i',strtotime($order_time.' '.$order_date)));
			$today = new DateTime(date('Y-m-d H:i',strtotime(current_time('mysql'))));
			$rest_sts = norsani()->vendor->frozr_rest_status($sellerid,false);
			$allow_ofline_orders = isset( $profile_info['allow_ofline_orders'] ) ? esc_attr( $profile_info['allow_ofline_orders'] ) : 'yes';
			$offline_orders = isset($profile_info['accpet_order_type_cl']) ? $profile_info['accpet_order_type_cl'] : false;
			$time_left_to_close =  $rest_sts? new DateTime(date('H:i', strtotime($rest_sts))) : array();
			$diffrence = $time_left_to_close ? $time_left_to_close->diff($today) : false;
			$time_deff = $ord_t->diff($today);
			
			/*Vendor menu options*/
			$meal_types = get_user_meta($sellerid, '_rest_meal_types', true);
			$filterd_opts = is_array($meal_types) ? array_filter($meal_types[0]) : array();

			if (!frozr_is_seller_enabled($sellerid)) {
				throw new Exception( sprintf(__('Sorry you cannot order from %s','frozr-norsani'),$store_name) );
			}
			
			foreach ($seller_products as $cart_item_key => $cart_item) {
				$product_id	= apply_filters( 'frozr_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
				$_product	= apply_filters( 'frozr_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$currency	= get_woocommerce_currency_symbol();
				if ($cart_item['order_l_type'] == 'delivery') {
				$has_delivery = true;
				$total_items[] = apply_filters( 'frozr_chm_item_subtotal', floatval($this->frozr_get_product_subtotal( $_product, $cart_item['quantity'] )), $cart_item, $cart_item_key );
				}

				$check_item_timing = norsani()->vendor->frozr_check_item_order_timing($cart_item['product_id'],$order_time);
				$timing = norsani()->vendor->frozr_get_item_timings($sellerid,$cart_item['product_id']);
				$pretime = norsani()->item->frozr_get_product_preparation_time($product_id,false);

				if ($pretime > 0) {
					$total_pre[] = $pretime;
				}

				if (!$rest_sts && $allow_ofline_orders != 'yes' || !$rest_sts && $allow_ofline_orders == 'yes' && !in_array($cart_item['order_l_type'], $offline_orders)) {
					throw new Exception( sprintf( __( 'Sorry, You cannot make a %1$s order for %2$s at this time. Please update your %3$s.', 'frozr-norsani' ), $cart_item['order_l_type'], $_product->get_name(), '<a href="'.wc_get_cart_url().'">'.__('Cart','frozr-norsani').'</a>') );
				}
				
				if (!$check_item_timing && $diffrence && $diffrence->h > 0 || !$check_item_timing && $time_deff->h > 12 && $diffrence || !$check_item_timing && !$diffrence) {
					throw new Exception( sprintf( __( 'Sorry, The preparation timing set for %1$s&apos;s products is inappropriate. Please choose a time between %2$s', 'frozr-norsani' ), $store_name, implode(' '.__('or','frozr-norsani').' ',$timing)) );
				}

				if (norsani()->item->frozr_max_orders_reached($cart_item['product_id'])) {
					throw new Exception( sprintf( __( 'Sorry, we cannot receive any more orders for %1$s today. Please update your %2$s.', 'frozr-norsani' ), $_product->get_title(), '<a href="'.wc_get_cart_url().'">'.__('Cart','frozr-norsani').'</a>' ) );
				}
				
				do_action('frozr_after_order_pretime_products_check',$cart_item,$sellerid);
			}
			if ($min_order > 0 && array_sum($total_items) < $min_order && $has_delivery) {
				throw new Exception( sprintf( __( 'Sorry, the minimum order amount for delivery service from %1$s is %2$s', 'frozr-norsani' ), '<a href="'. frozr_get_store_url($seller_id) .'" rel="external" title="'.$store_name.'">'.$store_name.'</a>', wc_price($min_order) ) );
			}
			
			$restime = get_user_meta( $sellerid, 'rest_open_close_time',true );
			$order_day = date('D', strtotime($order_date));
			$unds = ('' != get_user_meta($sellerid, '_rest_unavds', true)) ? get_user_meta($sellerid, '_rest_unavds', true) : array();
			$filterd_inds = array_filter($unds);
			$ava_date = true;
			$vendor_total_ord_pre_time = norsani()->order->frozr_cal_total_pretime($total_pre,$sellerid);
			$vendor_total_ord_pre_time_diff = $diffrence->i - norsani()->order->frozr_cal_total_pretime($total_pre,$sellerid);

			if (!empty($filterd_inds)) {
			foreach ($filterd_inds as $unava_date => $unava_value) {
				if (!empty($unava_value['start']) && !empty ($unava_value['end'])) {
				$unstart_date = new DateTime(date($unava_value['start']));
				$unend_date = new DateTime(date($unava_value['end']));
				if ($unstart_date <= $today && $today < $unend_date) {
					$ava_date = false;
					break;
				}
				}
			}
			}
			
			if (empty($filterd_opts)) {
				$ord_time = new DateTime(date('H:i',strtotime($order_time)));
				$rest_opening_one = isset($restime[$order_day]['open_one']) ? new DateTime(date('H:i', strtotime($restime[$order_day]['open_one']))) : false;
				$rest_closing_one = isset($restime[$order_day]['close_one']) ? new DateTime(date('H:i', strtotime($restime[$order_day]['close_one']))) : false;
				$rest_shifts = isset($restime[$order_day]['restshifts']) ? $restime[$order_day]['restshifts'] : false;
				$rest_opening_two = isset($restime[$order_day]['open_two']) ? new DateTime(date('H:i', strtotime($restime[$order_day]['open_two']))) : false;
				$rest_closing_two = isset($restime[$order_day]['close_two']) ? new DateTime(date('H:i', strtotime($restime[$order_day]['close_two']))) : false;
				$order_closed = true;
				$two_shifts = false;
				
				if ($rest_opening_one && $rest_closing_one) {
					if ($ord_time >= $rest_opening_one && $ord_time <= $rest_closing_one) {
						$order_closed = false;
					}
				}
				if ($rest_shifts && $rest_opening_two && $rest_closing_two) {
					$two_shifts = true;
					if ($ord_time >= $rest_opening_two && $ord_time <= $rest_closing_two) {
						$order_closed = false;
					}
				}
				$error_msg = $two_shifts ? sprintf( __( 'Sorry, %1$s will be closed at the selected time. %1$s&apos;s timing at %2$s is %3$s to %4$s & %5$s to %6$s', 'frozr-norsani' ), $store_name, date_i18n('l',strtotime($order_date)), $rest_opening_one->format(frozr_get_time_date_format()), $rest_closing_one->format(frozr_get_time_date_format()), $rest_opening_two->format(frozr_get_time_date_format()), $rest_closing_two->format(frozr_get_time_date_format())) : sprintf( __( 'Sorry, %1$s will be closed at the selected time. %1$s&apos;s timing at %2$s is %3$s to %4$s', 'frozr-norsani' ), $store_name, date_i18n('l',strtotime($order_date)), $rest_opening_one->format(frozr_get_time_date_format()), $rest_closing_one->format(frozr_get_time_date_format()));
				if ($order_closed) {
				throw new Exception($error_msg);		
				}
			}
			$skip_last_hour_check = $skip_prepare_timing_left_check = false;
			if ($today > $ord_t && $order_date_changed || $today > $ord_t && $time_deff->h > 0 || $today > $ord_t && $time_deff->d > 0 || !isset($restime[$order_day]['restop']) || !$ava_date) {
				throw new Exception( sprintf( __( 'Sorry, The preparation time set for %s&apos;s products is inappropriate.', 'frozr-norsani' ), $store_name ));
			}
			if ($today < $ord_t && $time_deff->i > 5 && $time_deff->d == 0 && $diffrence && $diffrence->h == 0 && $diffrence->d == 0 || $today < $ord_t && $time_deff->h > 0 && $time_deff->d == 0 && $diffrence && $diffrence->h == 0 && $diffrence->d == 0) {
				if (!apply_filters('frozr_skip_last_hour_order_fall', $skip_last_hour_check, $sellerid)) {
				throw new Exception( sprintf( __( 'Sorry, %s is closing in less than an hour, Please choose another date if you wish to make pre-orders.', 'frozr-norsani' ), $store_name ));
				}
			}
			if ($diffrence && $diffrence->h == 0 && $vendor_total_ord_pre_time && $vendor_total_ord_pre_time_diff < 5 )  {
				if (!apply_filters('frozr_skip_prepare_time_left_order_fall', $skip_prepare_timing_left_check, $sellerid)) {
				throw new Exception( sprintf( __( 'Sorry, no time left to prepare %1$s&apos;s products. %1$s is closing in less than an hour. Please update your %2$s or choose another date for your order.', 'frozr-norsani' ), $store_name,'<a href="'.wc_get_cart_url().'">'.__('Cart','frozr-norsani').'</a>') );
				}
			}
		}
	}

	/**
	 * Create sub-orders from main order for each vendor
	 *
	 * @param int $order_id
	 * @param array $posted_data
	 * @param object $parent_order
	 * @return void
	 */
	public function frozr_create_sub_order( $order_id, $posted_data, $parent_order ) {
		
		$checkout = WC()->checkout();
		
		/*Save user geo location if available*/
		$user_loc = frozr_norsani_cookies('locun');
		$geo_raw = frozr_norsani_cookies('geo');
		if ($geo_raw) {
			update_post_meta($order_id,'_user_geo_location',$geo_raw);
		}
		if ($user_loc) {
			update_post_meta($order_id,'_user_del_location',$user_loc);
		}
		
		$order_items = WC()->cart->get_cart();
		
		$sellers = array();

		foreach ($order_items as $cart_item_key => $cart_item) {
			$seller_id = get_post_field( 'post_author', $cart_item['product_id'] );
			$sellers[$seller_id][$cart_item_key] = $cart_item;
		}

		/* Return if we have only ONE seller*/
		if ( count( $sellers ) == 1 ) {
			
			$this->frozr_save_order_pre_time($checkout,$seller_id,$order_id);
			
			update_post_meta( $order_id, '_frozr_vendor', $seller_id );

		} else {
			/* flag it as it has a suborder*/
			update_post_meta( $order_id, 'has_sub_order', 'is_parent' );

			/* seems like we've got multiple sellers*/
			foreach ($sellers as $sellerid => $seller_products ) {
				$this->frozr_create_seller_order( $parent_order, $sellerid, $seller_products, $posted_data);
			}
		}
	}

	/**
	 * Cretae the single sub order
	 *
	 * @param int $parent_order
	 * @param int $seller_id
	 * @param array $seller_products
	 * @param array $posted_data
	 * @return void
	 */
	public function frozr_create_seller_order( $parent_order, $seller_id, $seller_products, $posted_data) {

		WC()->cart->cart_contents = $seller_products;

		/* Update cart totals now we have customer address.*/
		WC()->cart->calculate_totals();

		$checkout = WC()->checkout();
		$posted_data['parent_id'] = $parent_order->get_id();

		$orderid = $checkout->create_order( $posted_data );
		
		$this->frozr_save_order_pre_time($checkout,$seller_id,$orderid);
		
		update_post_meta( $orderid, '_frozr_vendor', $seller_id );
	}

	/**
	 * Cretae the single sub order
	 *
	 * @param object $checkout		WooCommerce checkout object
	 * @param int $seller_id
	 * @param int $orderid
	 * @return void
	 */
	public function frozr_save_order_pre_time($checkout,$seller_id,$orderid) {
		$order_time = $checkout->get_value('frozr_ord_pre_time_'.$seller_id);
		$order_dat = $checkout->get_value('frozr_ord_pre_date_'.$seller_id);
		$order_dat_changed = $checkout->get_value('frozr_order_time_changed_'.$seller_id);
		$order_preduration = $checkout->get_value('frozr_ord_pre_duration_'.$seller_id);
		$ord_t = new DateTime(date('Y-m-d H:i',strtotime($order_time.' '.$order_dat)));
		$today = new DateTime(date('Y-m-d H:i',strtotime(current_time('mysql'))));
		$ord_time = $today->diff($ord_t);
		$time = date('Y-m-d H:i',strtotime($order_time.' '.$order_dat));
		
		if ($today > $ord_t && !$order_dat_changed) {
			$time = date('Y-m-d H:i',strtotime(current_time('mysql')));
		}
		
		/*Save preparation time*/
		update_post_meta($orderid,'_order_pretime',date('Y-m-d H:i',strtotime(current_time('mysql'))));
		
		wp_update_post( array( 'ID' => $orderid, 'post_date' => $time, 'post_date_gmt' => get_gmt_from_date($time) ) );

		/*Save preparation duration*/
		update_post_meta($orderid,'_order_preduration',floatval($order_preduration));
		
		do_action('frozr_order_pre_time_saved',$checkout,$seller_id,$orderid);
	}


	/**
	 * Send message to vendor on new order
	 *
	 * @param string $recipient		Email of vendor
	 * @param object $order
	 * @return string
	 */
	public function frozr_new_order_vendor_email( $recipient, $order ) {
		if (null == $order) {
			return false;
		}
		$seller_id = frozr_get_order_author($order->get_id());
		
		if ( $seller_id ) {
			$seller = get_user_by( 'id', $seller_id );
		
			$recipient .= ','. $seller->user_email;
		}
		return $recipient;
	}

	/**
	 * Get order vendor
	 *
	 * @param int $orderid
	 * @return int
	 */
	public function frozr_get_order_author($orderid) {
		$post_object = get_post($orderid);
		if (get_post_meta( $orderid, '_frozr_vendor', true )) {
			$author_id = get_post_meta( $orderid, '_frozr_vendor', true );
		} else {
			$author_id = $post_object->post_author;
		}
		return $author_id;
	}

	/**
	 * Get order total preparation time
	 *
	 * @param array $total_pre	Array with preparation time of each order individual item.
	 * @param int $sellerid
	 * @return string
	 */
	public function frozr_cal_total_pretime($total_pre,$sellerid) {
		arsort($total_pre);
		$total_time = 0;
		$count = 1;
		$get_pretime_percent = norsani()->vendor->frozr_get_vendor_prepare_percentage($sellerid);
		if (!empty($total_pre) && $get_pretime_percent) {
		foreach ($total_pre as $totaltime) {
			$total_time += isset($total_pre[$count]) ? $total_pre[$count]*($get_pretime_percent/100) : 0;
			$count++;
		}
		}
		$total_prep = isset($total_pre[0]) ? $total_pre[0] + round($total_time) : 0;
		return $total_prep;
	}
	
	/**
	 * Add the vendor store name to items in order emails
	 *
	 * @return void
	 */
	public function frozr_add_store_name_to_email_item($item_name, $item, $bool) {
		$product		= $item->get_product();
		$auth			= get_post_field('post_author', $product->get_id());
		$seller_info	= frozr_get_store_info($auth);
		
		$item_name = '<strong>'.$seller_info['store_name'].':</strong>&nbsp;'.$item->get_name();
		return $item_name;
	}
	
	/**
	 * Modify the WooCommerce default checkout billing form
	 *
	 * @param array $address_fields
	 * @param string $country
	 */
	public function frozr_modified_billing_fields($address_fields, $country) {

		$has_delivery = 0;
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			if ($cart_item['order_l_type'] == 'delivery') {
				$has_delivery = 1;
				break;
			}
		}

		unset( $address_fields[ 'billing_company' ] );
		unset( $address_fields[ 'billing_state' ] );
		unset( $address_fields[ 'state' ] );
		unset( $address_fields[ 'billing_country' ] );
		unset( $address_fields[ 'billing_city' ] );
		unset( $address_fields[ 'billing_postcode' ] );
		if ($has_delivery == 0) {
			unset( $address_fields[ 'billing_address_1' ] );
			unset( $address_fields[ 'billing_address_2' ] );
		}

		return $address_fields;
	}
	
	/**
	 * Modify the WooCommerce default checkout form
	 *
	 * @param array $address_fields
	 */
	public function frozr_modified_checkout_fields($address_fields) {
		$has_delivery = 0;
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			if ($cart_item['order_l_type'] == 'delivery') {
				$has_delivery = 1;
				break;
			}
		}
		unset($address_fields['billing']['billing_state']);
		unset($address_fields['billing']['billing_postcode']);

		if ($has_delivery == 0) {
			unset( $address_fields['order']['order_comments'] );
		}
		return $address_fields;
	}
	
	/**
	 * Modify the WooCommerce default checkout form title
	 *
	 * @param object $checkout
	 * @return void
	 */
	public function frozr_checkout_billing_form_title($checkout) {
		$has_delivery = 0;
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			if ($cart_item['order_l_type'] == 'delivery') {
				$has_delivery = 1;
				break;
			}
		}
		if ($has_delivery == 0) {
			$output = __('Your Details','frozr-norsani');
		} else {
			$output = __('Your Address','frozr-norsani');	
		}

		echo '<h3>'. $output .'</h3>';
	}

	/**
	 * Modify the WooCommerce default checkout billing form title
	 *
	 * @param array $address_fields
	 */
	public function frozr_change_billing_title($address_fields) {
		$address_fields['billing'] = __( 'Customer address', 'frozr-norsani' );
		
		return $address_fields;
	}
}