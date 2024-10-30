<?php
/**
 * Norsani admin/vendor frontend dashboard class
 *
 * @package Norsani/Dashboard
 */
if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

class Norsani_Dashboard {
	
	/**
	 * The single instance of the class.
	 *
	 * @var Norsani_Dashboard
	 * @since 1.9
	 */
	protected static $_instance = null;
	
	/**
	 * Main Norsani_Dashboard Instance.
	 *
	 * @since 1.9
	 * @static
	 * @return Norsani_Dashboard - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Norsani_Dashboard Constructor.
	 */
	public function __construct() {
		add_action('frozr_norsani_admin_dashboard_menu', array($this,'frozr_norsani_dash_menu'), 10, 1);
		
		do_action( 'norsani_vendor_loaded' );
	}
	
	/**
	 * Dashboard register main nav
	 *
	 * @return Norsani frontend dashboard main menu items array.
	 */
	public function frozr_get_dashboard_nav() {
		$urls = apply_filters('frozr_dashboard_nav_menu',array(
			'dashboard' => array(
				'title' => __( 'Dashboard', 'frozr-norsani'),
				'url' => home_url( '/dashboard/home/' )
			),
			'items' => array(
				'title' => __( 'Products', 'frozr-norsani'),
				'url' => home_url( '/dashboard/items/' )
			),
			'order' => array(
				'title' => __( 'Orders', 'frozr-norsani'),
				'url' => home_url( '/dashboard/orders/' )
			),
			'coupon' => array(
				'title' => __( 'Coupons', 'frozr-norsani'),
				'url' => home_url( '/dashboard/coupons/' )
			),
			'withdraw' => array(
				'title' => __( 'Withdraw', 'frozr-norsani'),
				'url' => home_url( '/dashboard/withdraw/' )
			),
			'settings' => array(
				'title' => __( 'Settings', 'frozr-norsani'),
				'url' => home_url( '/dashboard/settings/' )
			),
			'sellers' => array(
				'title' => __( 'Sellers', 'frozr-norsani'),
				'url' => home_url( '/dashboard/sellers/' )
			),
		));
		if (is_super_admin()){
			array_splice($urls, 1, 1);
			array_splice($urls, 4, 1);
		} else {
			array_splice($urls, 6, 1);
		}
		return apply_filters( 'frozr_get_dashboard_nav', $urls );
	}
	
	/**
	 * Dashboard main menu
	 *
	 * @param bool	$show_title	Show menu title or not?.
	 * @return void				Norsani frontend dashboard main menu.
	 */
	public function frozr_norsani_dash_menu( $show_title = true ) {
		$title = (is_super_admin()) ? __('Admin','frozr-norsani') : __('Vendor','frozr-norsani');

		if (frozr_is_seller_enabled(get_current_user_id()) || is_super_admin()) {
			$urls = $this->frozr_get_dashboard_nav();
			ob_start();
			frozr_get_template('views/html-dashboard-menu.php', array('urls' => $urls, 'title' => $title, 'show_title' => $show_title));
			echo apply_filters('frozr_dashboard_menu_html',ob_get_clean(), $urls, $title, $show_title);
		}
	}
	
	/**
	 * Dashboard Total Sales
	 *
	 * @param string	$type	Results for what period. today, week, month, lastmonth, year, custom, beginning.
	 * @param string	$start	Start date for the results Y-m-d.
	 * @param string	$end	End date for the results Y-m-d.
	 * @param integer	$user	Vendor id to get results for.
	 * @return array
	 */
	public function frozr_dash_total_sales( $type = 'today', $start = '', $end = '', $user=null) {
		$website_fee = array();
		$total_coupon_usage = array();
		$total = array();
		$refunded_tax = array();	
		$taxes_total = array();
		$taxes = array();
		$refunded = array();
		$completed = array();
		$pending = array();
		$processing = array();
		$cancelled = array();
		$on_hold = array();
		
		$psts = ($type == 'beginning') ? array('wc-refunded', 'wc-completed', 'wc-pending', 'wc-processing', 'wc-cancelled', 'wc-on-hold') :  array ('wc-completed');

		$ord_args = apply_filters('frozr_dashboard_total_sales_orders_args',array(
			'posts_per_page'	=> -1,
			'post_type'			=> 'shop_order',
			'orderby'			=> 'date',
			'order'				=> 'desc',
			'post_status'		=> $psts,
		) );
		
		if (!is_super_admin()) {
			$ord_args['meta_key'] = '_frozr_vendor';
			$ord_args['meta_value'] = get_current_user_id();
		} elseif (is_super_admin() && $user) {
			$ord_args['meta_key'] = '_frozr_vendor';
			$ord_args['meta_value'] = intval($user);
		}
		
		$order_ids = get_posts($ord_args);
		
		foreach ($order_ids as $order_id) {
			$sub_orders = get_children( array( 'post_parent' => $order_id->ID, 'post_type' => 'shop_order' ) );
			if ( $sub_orders ) {
				continue;
			}
			$order = wc_get_order($order_id);
			$data = $order->get_data();
			$complete_day = strtotime($data['date_completed']);

			$website_profit = apply_filters('frozr_dashboard_website_fee',get_post_meta($order_id->ID, 'frozr_order_website_fee', true), $order);

			if ($type == 'today') {
				if ( date('ymd', $complete_day) == current_time('ymd') ) {
					$website_fee[] = $website_profit;			
					$total_coupon_usage[] = $data['discount_total'];
					if ( wc_tax_enabled() ) {
						foreach ( $order->get_tax_totals() as $code => $tax ) {
							$taxes_total[] = $tax->amount;
							$taxes[$tax->label] = $tax->formatted_amount;
						}
					}
					$total[] = apply_filters('frozr_dashboard_report_total_order', floatval(frozr_get_seller_total_order($order)), $order);
				}
			} elseif ($type == 'week') {
				if (date('ymd', $complete_day) > date('ymd', strtotime("-6 days")) ) {
					$website_fee[] = $website_profit;			
					$total_coupon_usage[] = $data['discount_total'];
					if ( wc_tax_enabled() ) {
						foreach ( $order->get_tax_totals() as $code => $tax ) {
							$taxes_total[] = $tax->amount;
							$taxes[$tax->label] = $tax->formatted_amount;
						}
					}
					$total[] = apply_filters('frozr_dashboard_report_total_order', floatval(frozr_get_seller_total_order($order)), $order);
				}
			} elseif ($type == 'month') {
				if (date('ym', $complete_day) == current_time('ym') ) {
					$website_fee[] = $website_profit;			
					$total_coupon_usage[] = $data['discount_total'];
					if ( wc_tax_enabled() ) {
						foreach ( $order->get_tax_totals() as $code => $tax ) {
							$taxes_total[] = $tax->amount;
							$taxes[$tax->label] = $tax->formatted_amount;
						}
					}
					$total[] = apply_filters('frozr_dashboard_report_total_order', floatval(frozr_get_seller_total_order($order)), $order);
				}
			} elseif ($type == 'lastmonth') {
				if (date('ymd', $complete_day) > date('ymd', strtotime("first day of last month")) && date('ymd', $complete_day) < date('ymd', strtotime("last day of last month")) ) {
					$website_fee[] = $website_profit;			
					$total_coupon_usage[] = $data['discount_total'];
					if ( wc_tax_enabled() ) {
						foreach ( $order->get_tax_totals() as $code => $tax ) {
							$taxes_total[] = $tax->amount;
							$taxes[$tax->label] = $tax->formatted_amount;
						}
					}
					$total[] = apply_filters('frozr_dashboard_report_total_order', floatval(frozr_get_seller_total_order($order)), $order);
				}
			} elseif ($type == 'year') {
				if (date('y', $complete_day) == current_time('y') ) {
					$website_fee[] = $website_profit;			
					$total_coupon_usage[] = $data['discount_total'];
					if ( wc_tax_enabled() ) {
						foreach ( $order->get_tax_totals() as $code => $tax ) {
							$taxes_total[] = $tax->amount;
							$taxes[$tax->label] = $tax->formatted_amount;
						}
					}
					$total[] = apply_filters('frozr_dashboard_report_total_order', floatval(frozr_get_seller_total_order($order)), $order);
				}
			} elseif ($type == 'custom' && $start != '' && $end != '') {
				
				if (date('ymd', $complete_day) > date('ymd', strtotime($start)) && date('ymd', $complete_day) < date('ymd', strtotime($end)) ) {
					$website_fee[] = $website_profit;			
					$total_coupon_usage[] = $data['discount_total'];
					if ( wc_tax_enabled() ) {
						foreach ( $order->get_tax_totals() as $code => $tax ) {
							$taxes_total[] = $tax->amount;
							$taxes[$tax->label] = $tax->formatted_amount;
						}
					}
					$total[] = apply_filters('frozr_dashboard_report_total_order', floatval(frozr_get_seller_total_order($order)), $order);
				}
			} elseif ($type == 'beginning') {
				$total_coupon_usage[] = $data['discount_total'];
				if ( wc_tax_enabled() ) {
					foreach ( $order->get_tax_totals() as $code => $tax ) {
						if ( ( $refunded_tax[] = $order->get_total_tax_refunded_by_rate_id( $tax->rate_id ) ) > 0 ) {
							$taxes_total[] = $tax->amount - $order->get_total_tax_refunded_by_rate_id( $tax->rate_id );
							$taxes[$tax->label] = $tax->formatted_amount. ' - ' . ($tax->amount - $order->get_total_tax_refunded_by_rate_id( $tax->rate_id )) . ' ' . __('(Refunded)','frozr-norsani');
						} else {
							$taxes_total[] = $tax->amount;
							$taxes[$tax->label] = $tax->formatted_amount;
						}
					}
				}
				$total[] = apply_filters('frozr_dashboard_report_total_order', floatval(frozr_get_seller_total_order($order)), $order);
				if ($order_id->post_status == 'wc-refunded'){
					$refunded[] = apply_filters('frozr_dashboard_report_total_order_refunded', floatval(frozr_get_seller_total_order($order)), $order);
				} elseif ($order_id->post_status == 'wc-completed'){
					$completed[] = apply_filters('frozr_dashboard_report_total_order_completed', floatval(frozr_get_seller_total_order($order)), $order);
					$website_fee[] = $website_profit;			
				} elseif ($order_id->post_status == 'wc-pending'){
					$pending[] = apply_filters('frozr_dashboard_report_total_order_pending', floatval(frozr_get_seller_total_order($order)), $order);
				} elseif ($order_id->post_status == 'wc-processing'){
					$processing[] = apply_filters('frozr_dashboard_report_total_order_processing', floatval(frozr_get_seller_total_order($order)), $order);
				} elseif ($order_id->post_status == 'wc-cancelled'){
					$cancelled[] = apply_filters('frozr_dashboard_report_total_order_cancelled', floatval(frozr_get_seller_total_order($order)), $order);
				} elseif ($order_id->post_status == 'wc-on-hold'){
					$on_hold[] = apply_filters('frozr_dashboard_report_total_order_onhold', floatval(frozr_get_seller_total_order($order)), $order);
				}
			}
		}
		$total_sales = !empty ($completed) ? $completed : $total;
		$percent = (!is_super_admin()) ? floatval(array_sum($website_fee)) : floatval(array_sum($total_sales)) - floatval(array_sum($website_fee));
		$netsales = (!is_super_admin()) ? floatval(array_sum($total_sales)) - $percent : floatval(array_sum($website_fee));

		return apply_filters ('frozr_output_dash_totals', array (
		floatval(array_sum($total)),
		count($total),
		floatval(array_sum($total_coupon_usage)),
		count($total_coupon_usage),
		floatval(array_sum($refunded)),
		count($refunded),
		floatval(array_sum($completed)),
		count($completed),
		floatval(array_sum($pending)),
		count($pending),
		floatval(array_sum($processing)),
		count($processing),
		floatval(array_sum($cancelled)),
		count($cancelled),
		floatval(array_sum($on_hold)),
		count($on_hold),
		$percent,
		$netsales,
		floatval(array_sum($taxes_total)),
		floatval(array_sum($refunded_tax)),
		$taxes,
		), $type, $start, $end, $user);
	}
	
	/**
	 * Output dashboard totals table
	 *
	 * @param string	$type	Results for what period. today, week, month, lastmonth, year, custom, beginning.
	 * @param string	$start	Start date for the results Y-m-d.
	 * @param string	$end	End date for the results Y-m-d.
	 * @param integer	$user	Vendor id to get results for.
	 * @return void
	 */
	public function frozr_dashboard_totals( $type = 'today', $start = '', $end = '', $user=null) {
		ob_start();
		frozr_get_template('views/html-dashboard-home-totals-table.php', array('type' => $type, 'start' => $start, 'end' => $end, 'user' => $user));
		echo apply_filters('frozr_dashboard_home_page_totals_table_html',ob_get_clean(), $type, $start, $end, $user);
	}
	
	/**
	 * Output dashboard totals
	 *
	 * @param string	$css_class	optional add a css class output div.
	 * @return void
	 */
	public function frozr_output_totals($css_class = '') {
		$sellers_results = null;
		
		if (is_super_admin()) {
			$args = array(
				'role'			=> 'seller',
				'orderby'		=> 'registered',
				'order'			=> 'DESC',
				'fields'		=> 'ID'
			 );
			$sellers_query = new WP_User_Query( apply_filters( 'frozr_output_totals_sellers_listing_query', $args ) );
			$sellers_results = $sellers_query->get_results();
		}
		
		ob_start();
		frozr_get_template('views/html-dashboard-home-totals.php', array('sellers_results' => $sellers_results, 'css_class' => $css_class));
		echo apply_filters('frozr_dashboard_home_page_totals_html',ob_get_clean(), $sellers_results, $css_class);
	}
	
	/**
	 * Dashboard top selling items
	 *
	 * @return void
	 */
	public function frozr_dash_top_items() {
		$meta_query = WC()->query->get_meta_query();
		$get_curnt_user = (is_super_admin()) ? '' : get_current_user_id();
			
		$args = apply_filters('frozr_dashboard_top_items_products_query', array(
			'post_type'				=> 'product',
			'post_status'			=> 'publish',
			'ignore_sticky_posts'	=> 1,
			'posts_per_page'		=> 10,
			'author'				=> $get_curnt_user,
			'meta_key'				=> 'total_sales',
			'orderby'				=> 'meta_value_num',
			'meta_query'			=> $meta_query
		));		
		$products = get_posts($args);

		ob_start();
		frozr_get_template('views/html-dashboard-home-top_items.php', array());
		echo apply_filters('frozr_dashboard_home_page_top_items_html',ob_get_clean(), $products, $get_curnt_user);
	}
	
	/**
	 * Dashboard vendor balance
	 *
	 * @return void
	 */
	public function frozr_dash_rest_balance() {
		ob_start();
		frozr_get_template('views/html-dashboard-home-vendor_balance.php', array());
		echo apply_filters('frozr_dashboard_home_page_vendor_balance_html',ob_get_clean());
	}
	
	/**
	 * Dashboard top customers
	 *
	 * @return void
	 */
	public function frozr_dash_top_customers() {
		$clients = array();
		$args = array(
			'limit'		=> -1,
			'post_type' => 'shop_order',
			'post_status' => array ('wc-completed'),
		);
		if (!is_super_admin()) {
			$args['meta_key'] = '_frozr_vendor';
			$args['meta_value'] = get_current_user_id();
		}
		$posts = get_posts( $args );
		foreach ($posts as $post) {
			$order = wc_get_order($post->ID);
			$clients[$order->get_billing_email()] += $order->get_total();
			if (count($clients) > 9) {
			break;
			}
		}

		arsort($clients);
		
		ob_start();
		frozr_get_template('views/html-dashboard-home-top_clients.php', array('clients' => $clients));
		echo apply_filters('frozr_dashboard_home_page_top_clients_html',ob_get_clean(), $clients);
	}
	
	/**
	 * Dashboard orders overview
	 *
	 * @return void
	 */
	public function frozr_dash_orders() {
		ob_start();
		frozr_get_template('views/html-dashboard-home-orders.php', array());
		echo apply_filters('frozr_dashboard_home_page_orders_html',ob_get_clean());
	}
}