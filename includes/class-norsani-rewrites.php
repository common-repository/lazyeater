<?php
/**
 * Norsani rewrite rules class
 *
 * @package Norsani
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

class Norsani_Rewrites {
    
	/**
	 * Query vars of the class.
	 *
	 * @var query_vars
	 */
	public $query_vars = array();
	
	/**
	 * The single instance of the class.
	 *
	 * @var Norsani_Rewrites
	 * @since 1.9
	 */
	protected static $_instance = null;
	
	/**
	 * Main Norsani_Rewrites Instance.
	 *
	 * @since 1.9
	 * @static
	 * @return Norsani_Rewrites - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Norsani_Rewrites Constructor.
	 */
	public function __construct() {
		$this->init();

		do_action( 'norsani_rewrites_loaded' );
	}

	public function init() {

		add_action( 'init', array( $this, 'register_rule' ) );

        add_filter( 'template_include', array($this, 'store_template') );
        add_filter( 'template_include', array($this, 'dashboard_template'), 11 );
        
		add_filter( 'query_vars', array($this, 'register_query_var') );
        
		add_filter( 'pre_get_posts', array( $this, 'items_listing' ) );
        add_filter( 'pre_get_posts', array( $this, 'orders_listing' ) );
        add_filter( 'pre_get_posts', array( $this, 'withdraw_listing' ) );
        add_filter( 'pre_get_posts', array( $this, 'coupons_listing' ) );
		add_action( 'pre_get_posts', array( $this, 'exclude_lazy_pages_from_search'));
	}

    /**
     * Register the rewrite rule
     *
     * @return void
     */
    function register_rule() {

		$sellers_page_id = get_option("sellers");
		$vendors_page_id = get_option("vendors");

		$permalinks = get_option( 'woocommerce_permalinks', array() );
		if( isset( $permalinks['product_base'] ) ) {
			$base = substr( $permalinks['product_base'], 1 );
		}

		add_rewrite_rule( '^vendors/([^/]+)/?$', 'index.php?page_id='.$vendors_page_id.'&vendors=$matches[1]', 'top' );
		add_rewrite_rule( '^vendors/([^/]+)/page/?([0-9]{1,})/?$', 'index.php?page_id='.$vendors_page_id.'&vendors=$matches[1]&paged=$matches[2]', 'top' );
		
		add_rewrite_rule( '^dashboard/sellers/?$', 'index.php?page_id='.$sellers_page_id.'', 'top' );
		add_rewrite_rule( '^dashboard/sellers/page/?([0-9]{1,})/?$', 'index.php?page_id='.$sellers_page_id.'&paged=$matches[1]', 'top' );
 
		add_rewrite_rule( '^vendor/([^/]+)/?$', 'index.php?vendor=$matches[1]', 'top' );
		add_rewrite_rule( '^vendor/([^/]+)/page/?([0-9]{1,})/?$', 'index.php?vendor=$matches[1]&paged=$matches[2]', 'top' );

		add_rewrite_rule( '^dashboard/([^/]+)/?$', 'index.php?dashboard=$matches[1]', 'top' );
		add_rewrite_rule( '^dashboard/([^/]+)/page/?([0-9]{1,})/?$', 'index.php?dashboard=$matches[1]&paged=$matches[2]', 'top' );
		add_rewrite_rule( '^dashboard/([^/]+)(/[0-9]+)?/edit/?$', 'index.php?product=$matches[1]&paged=$matches[2]&edit=true', 'top' );

	}
	/**
	* Register the query var
	*
	* @param array $vars
	* @return array
	*/	
    function register_query_var( $vars ) {
		$vars[] = 'vendor';
		$vars[] = 'vendors';
		$vars[] = 'dashboard';
		$vars[] = 'vendorclass';

		return $vars;
	}

	/**
	* Include vendor template
	*
	* @param type $template
	* @return string
	*/
	function store_template( $template ) {

		$store_name = get_query_var( 'vendor' );

		if ( !empty( $store_name ) ) {
			$store_user = get_user_by( 'slug', $store_name );

			/* no user found*/
			if ( !$store_user ) {
				return get_404_template();
			}

			/* check if the user is seller*/
			if ( !user_can( $store_user->ID, 'frozer' ) ) {
				return get_404_template();
			}

			return NORSANI_TMP . 'vendor/vendor.php';
		}

		return $template;
	}

	function dashboard_template( $template ) {

		$redirect = get_query_var( 'dashboard' );
		if ( $redirect == 'home') {
			return NORSANI_TMP . 'dashboard.php';
		} elseif ( $redirect == 'items' ) {
			$redirect2 = get_query_var( $redirect );
			if ($redirect2 === 'edit') {
			return NORSANI_TMP . 'item-edit.php';
			} else {
			return NORSANI_TMP . 'items.php';
			}
		} elseif ( $redirect == 'orders' ) {
			return NORSANI_TMP . 'orders.php';
		} elseif ( $redirect == 'coupons' ) {
			return NORSANI_TMP . 'coupons.php';
		} elseif ( $redirect == 'withdraw' ) {
			return NORSANI_TMP . 'withdraw.php';
		} elseif ( $redirect == 'settings' ) {
			return NORSANI_TMP . 'settings.php';
		}
		
	return $template;
	}

	function items_listing( $query ) {


		if ( !is_admin() && $query->is_main_query()) {
			$page = get_query_var( 'dashboard' );
			if ($page == 'items') {
			$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
			$post_statuses = apply_filters('frozr_items_listing_post_status',array('publish', 'draft', 'pending', 'offline'));

			$query->set( 'post_type', 'product' );
			$query->set( 'post_status', $post_statuses );
			$query->set( 'author', get_current_user_id() );
			$query->set( 'orderby', 'post_date' );
			$query->set( 'order', 'DESC' );
			$query->set( 'paged', $paged );

			if ( isset( $_GET['post_status']) && in_array( $_GET['post_status'], $post_statuses ) ) {
				$query->set( 'post_status', wc_clean($_GET['post_status']) );
			}
			}
		}
	}
	function orders_listing( $query ) {

		if ( !is_admin() && $query->is_main_query() ) {
			$page = get_query_var( 'dashboard' );
			if ($page == 'orders') {
			$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
			$order_status = isset( $_GET['order_status'] ) ? sanitize_key( $_GET['order_status'] ) : 'all';

			$query->set( 'post_type', 'shop_order' );
			$query->set( 'post_status', array('wc-'. $order_status) );
			$query->set( 'orderby', 'date' );
			
			if(!is_super_admin()) {
			$query->set( 'meta_key', '_frozr_vendor' );
			$query->set( 'meta_value', get_current_user_id() );
			}
			
			if($order_status == 'processing') {
			$query->set( 'posts_per_page', -1 );
			$query->set( 'order', 'ASC' );
			} else {
			$query->set( 'paged', $paged );
			$query->set( 'order', 'DESC' );
			}
			
			}
		}
	}
	function withdraw_listing( $query ) {

		$withdraw_status = array('completed', 'trash', 'pending');
		$status_class = isset( $_GET['withdraw_status'] ) ? sanitize_key($_GET['withdraw_status']) : 'pending';

		if ( !is_admin() && $query->is_main_query() && in_array( $status_class, $withdraw_status ) ) {
			$page = get_query_var( 'dashboard' );
			if ($page == 'withdraw') {
			$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
			$author = (!is_super_admin()) ? get_current_user_id() : '';

			$query->set( 'post_type', 'frozr_withdraw' );
			$query->set( 'post_status', $status_class );
			$query->set( 'author', $author );
			$query->set( 'orderby', 'post_date' );
			$query->set( 'order', 'DESC' );
			$query->set( 'paged', $paged );
			}

		}
	}
	function coupons_listing( $query ) {

		if ( !is_admin() && $query->is_main_query()) {
			$page = get_query_var( 'dashboard' );
			if ($page == 'coupons') {
			$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
			$author = (!is_super_admin()) ? get_current_user_id() : '';

			$query->set( 'post_type', 'shop_coupon' );
			$query->set( 'post_status', array('publish', 'pending') );
			$query->set( 'author', $author );
			$query->set( 'orderby', 'post_date' );
			$query->set( 'order', 'DESC' );
			$query->set( 'paged', $paged );
			}
		}
	}
	function exclude_lazy_pages_from_search($query) {
		$rest_page_id = get_option("vendors");
		$sellers_page_id = get_option("sellers");
		/*only run for the main query and don't run on admin pages*/
		if (!is_admin() && $query->is_main_query()) {
			/*now check to see if you are on a search results page*/
			if ($query->is_search) {
			$query->set('post__not_in', array($rest_page_id,$sellers_page_id));
			}
		}
	}
}
return new Norsani_Rewrites();