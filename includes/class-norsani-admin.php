<?php
/**
 * Norsani WP Admin functions
 *
 * @package Norsani
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

/**
 * Norsani WP admin side class
 *
 * @class Norsani_Admin
 */
class Norsani_Admin {
	
	/**
	 * The single instance of the class.
	 *
	 * @var Norsani_Admin
	 * @since 1.9
	 */
	protected static $_instance = null;
	
	/**
	 * Main Norsani_Admin Instance.
	 *
	 * @since 1.9
	 * @static
	 * @return Norsani_Admin - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Norsani_Admin Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array($this,'frozr_activate_redirect'));
		add_action( 'admin_notices', array($this, 'frozr_fee_admin_error_notice'));
		add_action( 'manage_shop_order_posts_custom_column', array($this, 'frozr_shop_order_custom_columns'), 11 );
		add_action( 'admin_footer-edit.php', array($this, 'frozr_admin_shop_order_scripts') );
		add_action( 'woocommerce_order_status_changed', array($this, 'frozr_on_order_refund'), 10, 4 );
		add_action( 'restrict_manage_posts', array($this, 'frozr_admin_shop_order_toggle_sub_orders'));
		add_action( 'show_user_profile', array($this, 'frozr_add_meta_fields'), 20 );
		add_action( 'edit_user_profile', array($this, 'frozr_add_meta_fields'), 20 );
		add_action( 'personal_options_update', array($this, 'frozr_save_user_meta_fields') );
		add_action( 'edit_user_profile_update', array($this, 'frozr_save_user_meta_fields') );
		
		/*Admin Ajax Methods*/
		add_action('wp_ajax_frozr_fix_demo_pages', array($this, 'frozr_fix_demo_pages'));
		add_action('wp_ajax_frozr_save_frozrhelp_user_data', array($this, 'frozr_save_frozrhelp_user_data'));
		add_action('wp_ajax_frozr_install_demo_data', array($this, 'frozr_install_demo_data'));
		add_action('wp_ajax_frozr_activate_default_theme', array($this, 'frozr_activate_default_theme'));
		add_action('wp_ajax_frozr_install_default_theme', array($this, 'frozr_install_default_theme'));
	
		add_filter( 'manage_edit-shop_order_columns', array($this, 'frozr_admin_shop_order_edit_columns'), 11 );
		add_filter( 'post_class', array($this, 'frozr_admin_shop_order_row_classes'), 10, 2);
		add_filter( 'manage_edit-product_columns', array($this, 'frozr_admin_product_columns') );		
		
		do_action( 'norsani_admin_loaded' );
	}
	
	/**
	 * Redirect to Norsani help center after activating plugin.
	 *
	 */
	public function frozr_activate_redirect() {
		$first_installation = get_option('norsani_first_install');
		if (is_admin() && get_option('frozr_do_active_redirect', false)) {
			$themes_installed = get_option('frozr_themes_installed');
			if ($first_installation != 1 && $themes_installed == 1) {
				switch_theme('frozrdash-child');
				update_option('norsani_first_install',1);
			}
			delete_option('frozr_do_active_redirect');
			flush_rewrite_rules();
			if ($first_installation != 1) {
				wp_redirect('admin.php?page=norsani_help_center&plugin=installed');
			} else {
				wp_redirect('admin.php?page=norsani_help_center&plugin=activated');
			}
		}
	}

	/**
	 * Check if Norsani template page is installed
	 *
	 * @param string $page_template		Template page to check.
	 * @return bool
	 */
	public function frozr_check_page($page_template) {
		$page_option = explode('.',$page_template);
		$args = array(
			'posts_per_page'   => 1,
			'meta_key'         => '_wp_page_template',
			'meta_value'       => $page_template,
			'post_type'        => 'page',
			'fields'           => 'ids',
		);
		$posts_array = get_posts( $args );
		if ($posts_array && !empty($posts_array)) {
			update_option($page_option[0], $posts_array[0]);
			return true;
		} else {
			delete_option($page_option[0]);
			return false;
		}
	}

	/**
	 * Check for specific vendor type
	 *
	 * @param string $vendor_type		Vendor type to check.
	 * @return bool
	 */
	public function frozr_check_vendors($vendor_type) {
		$args = apply_filters( 'frozr_seller_list_query', array(
		'role' => 'seller',
		'meta_query' => array(
			array(
				'key' => 'frozr_enable_selling',
				'value' => 'yes',
				'compare' => '='
			),
			array(
				'key' => 'frozr_vendor_type',
				'value' => $vendor_type,
				'compare' => '='
			),
		)
		));
		$user_query = new WP_User_Query( $args );
		$count_vendors = $user_query->total_users;
		if ($count_vendors > 0) {
			return true;
		}
		return false;
	}

	/**
	 * Fee/Commission unset admin notice
	 *
	 * @return void
	 */
	public function frozr_fee_admin_error_notice() {
		$frozr_option = get_option( 'frozr_fees_settings' );
		$fees_options = (! empty( $frozr_option['frozr_lazy_fees']) ) ? $frozr_option['frozr_lazy_fees'] : false;
		$woo_file = 'woocommerce/woocommerce.php';
		$plugin_file = WP_PLUGIN_DIR .'/'. $woo_file;
		$dir = dirname( $plugin_file );

		$norsani_page = isset( $_GET['page'] ) && $_GET['page'] == 'norsani' ? true : false;
		if (!$norsani_page && isset( $_GET['page'] ) || isset($_GET['post_type'])) {
			return false;
		}

		if ( ! current_theme_supports( 'frozr-norsani' ) ) {
			$theme_exisit = wp_get_theme('frozrdash-child');
			$action_txt = $theme_exisit->exists() ? __("Activate the Norsani's default childtheme","frozr-norsani") : __("Install the Norsani's default theme","frozr-norsani");
			$action_class = $theme_exisit->exists() ? 'frozr_activate_default_theme' : 'frozr_install_default_theme';
			$class = "frozr error notice";
			$message = __('Your current theme dose not support Norsani. Please install and activate a supported theme or','frozr-norsani'). ' <a href="#" class="button button-primary '.$action_class.'" title="'.$action_txt.'">'.$action_txt.'</a>';
			echo '<div class="'.$class.'">'.$message.'</div>';
		}
		if ( isset($_GET['tab']) && $_GET['tab'] != 'fees' && !$fees_options || $norsani_page && !$fees_options) {
			$class = "frozr error notice is-dismissible";
			$message = __('You have not yet set a fee/commission on your vendors sales. Vendors will get %100 of their sales. Set your fee/commission now from the&nbsp;','frozr-norsani'). '<a href="'.admin_url( 'admin.php?page=norsani&tab=fees' ).'" title="'.__('Fees/Commission Settings','frozr-norsani').'">'.__('Fees/Commission Settings','frozr-norsani').'</a>';
			echo sprintf('<div class="%s">%s</div>',$class, $message);
		}

		if (!is_dir( $dir ) || WP_PLUGIN_DIR == $dir) {
			$class = "frozr error notice";
			$message = __('Norsani highly depends on WooCommerce plugin which was uninstalled. Norsani will stop working untill you install and activate WooCommerce.','frozr-norsani');
			echo sprintf('<div class="%s">%s</div>',$class, $message);
		} elseif (!is_plugin_active($woo_file)) {
			$class = "frozr error notice";
			$message = __('Norsani highly depends on WooCommerce plugin which was deactivated. Norsani will stop working untill you reactivate WooCommerce.','frozr-norsani');
			echo sprintf('<div class="%s">%s</div>',$class, $message);
		}
	}

	/**
	 * Check for Norsani demo pages and add missing.
	 *
	 */
	public function frozr_fix_demo_pages() {
		check_ajax_referer( 'frozrhelp_user_data_check', 'security' );
		$this->frozr_add_norsani_pages();
		die();
	}

	/**
	 * Save admin password for Norsani help center
	 *
	 */
	public function frozr_save_frozrhelp_user_data() {
		check_ajax_referer( 'frozrhelp_user_data_check', 'security' );
		$user_p = $_POST['userd'];
		$user = get_current_user_id();
		update_user_meta($user,'frozrhelp_user',$user_p);
		die();
	}

	/**
	 * Install demo data
	 *
	 */
	public function frozr_install_demo_data() {
		check_ajax_referer( 'frozr_install_demo_data_check', 'security' );

		$users_meta = array(
			'seller1' => array(
				'frozr_vendor_type' => 'chef',
				'nickname'=>'seller1','first_name' => 'Seller','last_name' => 'One',
				'rich_editing' => 'true','syntax_highlighting' => 'true','comment_shortcuts' => 'false','admin_color' => 'fresh','use_ssl' => '0','show_admin_bar_front' => 'true','wp_capabilities' => array('seller' => 1),'wp_user_level' => '0','frozr_enable_selling' => 'yes',
				'frozr_profile_settings' => array (
				'store_name' => 'Mr.Chef',
				'socialfb' => '#','socialtwitter' => '#','socialyoutube' => '#',
				'payment' => array('paypal' => array('email' => 'seller1@mahmudhamid.com')),
				'phone' => '+100000000',
				'allow_email' => 1,
				'accpet_order_type' => array('delivery','pickup','dine-in','curbside'),
				'accpet_order_type_cl' => array('pickup','dine-in','curbside'),
				'allow_ofline_orders' => 'yes',
				'shipping_fee' => 3.1,
				'deliveryby' => 'order',
				'shipping_pro_adtl_cost' => 1,
				'min_order_amt' => 40,
				'shipping_fee_peak' => 5,
				'deliveryby_peak' => 'order',
				'shipping_pro_adtl_cost_peak' => 0,
				'min_order_amt_peak' => 47,
				),
				'rest_open_close_time' => array(
				'Sat' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Sun' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Mon' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Tue' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Wed' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Thu' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Fri' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				),
				'_rest_unavds' => array(array('start' => '','end' => '',)),
				'_rest_meal_types' => array(
				array('title' => 'Breakfast','start' => '08:00','end' => '12:00',),
				array('title' => 'Lunch','start' => '12:00','end' => '17:00',),
				array('title' => 'Dinner','start' => '17:00','end' => '21:00',),
				),
				'frozr_food_type' => array('veg','nonveg','sea-food'),
				'rest_address_geo' => '39.768260701805076,-86.14604267416638',
				'terms' => array(
					'vendor_addresses' => '636 E Market St, Indianapolis, IN 46202, USA',
					'vendorclass' => array('chinese','indian','italian')
				),
				'delivery_location' => array(
				'{lat:39.69815217160338, lng:-86.24897205828427}',
				'{lat:39.71716903101407, lng:-86.05190479754208}',
				'{lat:39.90388884222825, lng:-86.08280384539364}',
				'{lat:39.90915604584373, lng:-86.2565251588702}',
				),
				'delivery_location_filtered' => array(
				array(39.69815217160338,-86.24897205828427),
				array(39.71716903101407,-86.05190479754208),
				array(39.90388884222825,-86.08280384539364),
				array(39.90915604584373,-86.2565251588702),
				),
				'frozr_peak_number' => 1,
				'frozr_vendor_store_notice' => 'Set your address near 636 E Market St, Indianapolis, IN, USA to become within the delivery zone of this vendor. i.e: East Ohio Street, Indianapolis,',
			),
			'seller2' => array(
				'frozr_vendor_type' => 'chef',
				'nickname'=>'seller2','first_name' => 'Seller','last_name' => 'Two',
				'rich_editing' => 'true','syntax_highlighting' => 'true','comment_shortcuts' => 'false','admin_color' => 'fresh','use_ssl' => '0','show_admin_bar_front' => 'true','wp_capabilities' => array('seller' => 1),'wp_user_level' => '0','frozr_enable_selling' => 'yes',
				'frozr_profile_settings' => array (
				'store_name' => 'Pasta Masta',
				'socialfb' => '#','socialtwitter' => '#','socialyoutube' => '#',
				'payment' => array('paypal' => array('email' => 'seller2@mahmudhamid.com')),
				'phone' => '+100000000',
				'allow_email' => 1,
				'accpet_order_type' => array('delivery','pickup','dine-in','curbside'),
				'accpet_order_type_cl' => array('pickup','dine-in','curbside'),
				'allow_ofline_orders' => 'yes',
				'shipping_fee' => 4.2,
				'deliveryby' => 'order',
				'shipping_pro_adtl_cost' => 1,
				'min_order_amt' => 15,
				'shipping_fee_peak' => 6,
				'deliveryby_peak' => 'order',
				'shipping_pro_adtl_cost_peak' => 0,
				'min_order_amt_peak' => 20,
				),
				'rest_open_close_time' => array(
				'Sat' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Sun' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Mon' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Tue' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Wed' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Thu' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Fri' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				),
				'_rest_unavds' => array(array('start' => '','end' => '',)),
				'_rest_meal_types' => array(
				array('title' => 'Breakfast','start' => '08:00','end' => '12:00',),
				array('title' => 'Lunch & Dinner','start' => '12:00','end' => '21:00',),
				),
				'frozr_food_type' => array('veg','nonveg','sea-food'),
				'rest_address_geo' => '40.7219357,-74.0098003',
				'terms' => array(
					'vendor_addresses' => 'Greenwich Street, New York, NY, USA',
					'vendorclass' => array('pasta')
				),
				'delivery_location' => array(
				'{lat:40.39428624258883, lng:-74.62809542595699}',
				'{lat:40.3733649441873, lng:-73.40037325798824}',
				'{lat:41.037548034572005, lng:-73.11472872673824}',
				'{lat:41.04790567183745, lng:-74.36167696892574}'
				),
				'delivery_location_filtered' => array(
				array(40.39428624258883,-74.62809542595699),
				array(40.3733649441873,-73.40037325798824),
				array(41.037548034572005,-73.11472872673824),
				array(41.04790567183745,-74.36167696892574),
				),
				'frozr_peak_number' => 1,
				'frozr_vendor_store_notice' => 'Set your address near Greenwich Street, New York, to become within the delivery zone of this vendor. i.e: Hubert Street, New York',
			),
			'seller3' => array(
				'frozr_vendor_type' => 'chef',
				'nickname'=>'seller3','first_name' => 'Seller','last_name' => 'Three',
				'rich_editing' => 'true','syntax_highlighting' => 'true','comment_shortcuts' => 'false','admin_color' => 'fresh','use_ssl' => '0','show_admin_bar_front' => 'true','wp_capabilities' => array('seller' => 1),'wp_user_level' => '0','frozr_enable_selling' => 'yes',
				'frozr_profile_settings' => array (
				'store_name' => 'Mustache',
				'socialfb' => '#','socialtwitter' => '#','socialyoutube' => '#',
				'payment' => array('paypal' => array('email' => 'seller3@mahmudhamid.com')),
				'phone' => '+100000000',
				'allow_email' => 1,
				'accpet_order_type' => array('delivery','pickup','dine-in','curbside'),
				'accpet_order_type_cl' => array('pickup','dine-in','curbside'),
				'allow_ofline_orders' => 'yes',
				'shipping_fee' => 2.2,
				'deliveryby' => 'order',
				'shipping_pro_adtl_cost' => 1,
				'min_order_amt' => 15,
				'shipping_fee_peak' => 7,
				'deliveryby_peak' => 'order',
				'shipping_pro_adtl_cost_peak' => 0,
				'min_order_amt_peak' => 20,
				),
				'rest_open_close_time' => array(
				'Sat' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Sun' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Mon' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Tue' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Wed' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Thu' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Fri' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				),
				'_rest_unavds' => array(array('start' => '','end' => '',)),
				'_rest_meal_types' => array(
				array('title' => 'Breakfast','start' => '08:00','end' => '12:00',),
				array('title' => 'Lunch & Dinner','start' => '12:00','end' => '21:00',),
				),
				'frozr_food_type' => array('veg','nonveg','sea-food'),
				'rest_address_geo' => '40.723084631479026,-74.00799463702214',
				'terms' => array(
					'vendor_addresses' => '200 Hudson St, New York, NY 10013, USA',
					'vendorclass' => array('indian','mexican')
				),
				'delivery_location' => array(
				'{lat:40.39428624258883, lng:-74.62809542595699}',
				'{lat:40.3733649441873, lng:-73.40037325798824}',
				'{lat:41.037548034572005, lng:-73.11472872673824}',
				'{lat:41.04790567183745, lng:-74.36167696892574}'
				),
				'delivery_location_filtered' => array(
				array(40.39428624258883,-74.62809542595699),
				array(40.3733649441873,-73.40037325798824),
				array(41.037548034572005,-73.11472872673824),
				array(41.04790567183745,-74.36167696892574),
				),
				'frozr_peak_number' => 1,
				'frozr_vendor_store_notice' => 'Set your address near 200 Hudson St, New York, to become within the delivery zone of this vendor. i.e: Hubert Street, New York',
			),
			'seller4' => array(
				'frozr_vendor_type' => 'restaurant',
				'nickname'=>'seller4','first_name' => 'Seller','last_name' => 'Four',
				'rich_editing' => 'true','syntax_highlighting' => 'true','comment_shortcuts' => 'false','admin_color' => 'fresh','use_ssl' => '0','show_admin_bar_front' => 'true','wp_capabilities' => array('seller' => 1),'wp_user_level' => '0','frozr_enable_selling' => 'yes',
				'frozr_profile_settings' => array (
				'store_name' => 'Salad Bar',
				'socialfb' => '#','socialtwitter' => '#','socialyoutube' => '#',
				'payment' => array('paypal' => array('email' => 'seller4@mahmudhamid.com')),
				'phone' => '+100000000',
				'allow_email' => 1,
				'accpet_order_type' => array('delivery','pickup','dine-in','curbside'),
				'accpet_order_type_cl' => array('pickup','dine-in','curbside'),
				'allow_ofline_orders' => 'yes',
				'shipping_fee' => 4.2,
				'deliveryby' => 'order',
				'shipping_pro_adtl_cost' => 0,
				'min_order_amt' => 20,
				'shipping_fee_peak' => 6,
				'deliveryby_peak' => 'order',
				'shipping_pro_adtl_cost_peak' => 0,
				'min_order_amt_peak' => 20,
				),
				'rest_open_close_time' => array(
				'Sat' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '22:00','open_two' => '','close_two' =>''),
				'Sun' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '22:00','open_two' => '','close_two' =>''),
				'Mon' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '22:00','open_two' => '','close_two' =>''),
				'Tue' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '22:00','open_two' => '','close_two' =>''),
				'Wed' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '22:00','open_two' => '','close_two' =>''),
				'Thu' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '22:00','open_two' => '','close_two' =>''),
				'Fri' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '22:00','open_two' => '','close_two' =>''),
				),
				'_rest_unavds' => array(array('start' => '','end' => '',)),
				'_rest_meal_types' => array(
				array('title' => 'Breakfast','start' => '08:00','end' => '12:00',),
				array('title' => 'Lunch','start' => '12:00','end' => '17:00',),
				array('title' => 'Dinner','start' => '17:00','end' => '22:00',),
				),
				'frozr_food_type' => array('veg','sea-food'),
				'rest_address_geo' => '42.34844649999999,-71.1557881',
				'terms' => array(
					'vendor_addresses' => 'B Washington Street, Boston, MA, USA',
					'vendorclass' => array('cafe','fastfood','seafood')
				),
				'delivery_location' => array(
				'{lat:42.30417600030217, lng:-71.24392904734674}',
				'{lat:42.305699473926374, lng:-71.06128134226861}',
				'{lat:42.41123707973555, lng:-71.0990468451983}',
				'{lat:42.40768821576199, lng:-71.25972189402643}'
				),
				'delivery_location_filtered' => array(
				array(42.30417600030217,-71.24392904734674),
				array(42.305699473926374,-71.06128134226861),
				array(42.41123707973555,-71.0990468451983),
				array(42.40768821576199,-71.25972189402643)
				),
				'frozr_peak_number' => 1,
				'frozr_vendor_store_notice' => 'Set your address near B Washington Street, Boston, Massachusetts, to become within the delivery zone of this vendor. i:e: Lake St, Boston',
			),
			'seller5' => array(
				'frozr_vendor_type' => 'restaurant',
				'nickname'=>'seller5','first_name' => 'Seller','last_name' => 'Five',
				'rich_editing' => 'true','syntax_highlighting' => 'true','comment_shortcuts' => 'false','admin_color' => 'fresh','use_ssl' => '0','show_admin_bar_front' => 'true','wp_capabilities' => array('seller' => 1),'wp_user_level' => '0','frozr_enable_selling' => 'yes',
				'frozr_profile_settings' => array (
				'store_name' => 'Pepperjack',
				'socialfb' => '#','socialtwitter' => '#','socialyoutube' => '#',
				'payment' => array('paypal' => array('email' => 'seller5@mahmudhamid.com')),
				'phone' => '+100000000',
				'allow_email' => 1,
				'accpet_order_type' => array('delivery','pickup','dine-in','curbside'),
				'accpet_order_type_cl' => array('pickup','dine-in','curbside'),
				'allow_ofline_orders' => 'yes',
				'shipping_fee' => 3.5,
				'deliveryby' => 'order',
				'shipping_pro_adtl_cost' => 0,
				'min_order_amt' => 20,
				'shipping_fee_peak' => 5,
				'deliveryby_peak' => 'order',
				'shipping_pro_adtl_cost_peak' => 0,
				'min_order_amt_peak' => 20,
				),
				'rest_open_close_time' => array(
				'Sat' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '22:00','open_two' => '','close_two' =>''),
				'Sun' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '22:00','open_two' => '','close_two' =>''),
				'Mon' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '22:00','open_two' => '','close_two' =>''),
				'Tue' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '22:00','open_two' => '','close_two' =>''),
				'Wed' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '22:00','open_two' => '','close_two' =>''),
				'Thu' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '22:00','open_two' => '','close_two' =>''),
				'Fri' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '22:00','open_two' => '','close_two' =>''),
				),
				'_rest_unavds' => array(array('start' => '','end' => '',)),
				'_rest_meal_types' => array(
				array('title' => 'Breakfast','start' => '08:00','end' => '12:00',),
				array('title' => 'Lunch','start' => '12:00','end' => '17:00',),
				array('title' => 'Dinner','start' => '17:00','end' => '22:00',),
				),
				'frozr_food_type' => array('veg','sea-food'),
				'rest_address_geo' => '42.34844649999999,-71.1557881',
				'terms' => array(
					'vendor_addresses' => 'B Washington Street, Boston, MA, USA',
					'vendorclass' => array('cafe','fastfood','seafood')
				),
				'delivery_location' => array(
				'{lat:42.30417600030217, lng:-71.24392904734674}',
				'{lat:42.305699473926374, lng:-71.06128134226861}',
				'{lat:42.41123707973555, lng:-71.0990468451983}',
				'{lat:42.40768821576199, lng:-71.25972189402643}'
				),
				'delivery_location_filtered' => array(
				array(42.30417600030217,-71.24392904734674),
				array(42.305699473926374,-71.06128134226861),
				array(42.41123707973555,-71.0990468451983),
				array(42.40768821576199,-71.25972189402643)
				),
				'frozr_peak_number' => 1,
				'frozr_vendor_store_notice' => 'Set your address near B Washington Street, Boston, Massachusetts, to become within the delivery zone of this vendor. i:e: Lake St, Boston',
			),
			'seller6' => array(
				'frozr_vendor_type' => 'restaurant',
				'nickname'=>'seller6','first_name' => 'Seller','last_name' => 'Six',
				'rich_editing' => 'true','syntax_highlighting' => 'true','comment_shortcuts' => 'false','admin_color' => 'fresh','use_ssl' => '0','show_admin_bar_front' => 'true','wp_capabilities' => array('seller' => 1),'wp_user_level' => '0','frozr_enable_selling' => 'yes',
				'frozr_profile_settings' => array (
				'store_name' => 'Luciana Pastries',
				'socialfb' => '#','socialtwitter' => '#','socialyoutube' => '#',
				'payment' => array('paypal' => array('email' => 'seller6@mahmudhamid.com')),
				'phone' => '+100000000',
				'allow_email' => 1,
				'accpet_order_type' => array('delivery','pickup','dine-in','curbside'),
				'accpet_order_type_cl' => array('pickup','dine-in','curbside'),
				'allow_ofline_orders' => 'yes',
				'shipping_fee' => 1.5,
				'deliveryby' => 'order',
				'shipping_pro_adtl_cost' => 0,
				'min_order_amt' => 20,
				'shipping_fee_peak' => 2.5,
				'deliveryby_peak' => 'order',
				'shipping_pro_adtl_cost_peak' => 0,
				'min_order_amt_peak' => 20,
				),
				'rest_open_close_time' => array(
				'Sat' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '17:00','open_two' => '','close_two' =>''),
				'Sun' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '17:00','open_two' => '','close_two' =>''),
				'Mon' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '17:00','open_two' => '','close_two' =>''),
				'Tue' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '17:00','open_two' => '','close_two' =>''),
				'Wed' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '17:00','open_two' => '','close_two' =>''),
				'Thu' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '17:00','open_two' => '','close_two' =>''),
				'Fri' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '17:00','open_two' => '','close_two' =>''),
				),
				'_rest_unavds' => array(array('start' => '','end' => '',)),
				'_rest_meal_types' => array(
				array('title' => 'Breakfast','start' => '08:00','end' => '12:00',),
				array('title' => 'Lunch','start' => '12:00','end' => '17:00',),
				),
				'frozr_food_type' => array('veg','nonveg','sea-food'),
				'rest_address_geo' => '29.7413194,-95.36917189999997',
				'terms' => array(
					'vendor_addresses' => '1626 Dennis St, Houston, TX 77004, USA',
					'vendorclass' => array('chinese','fastfood','pastries')
				),
				'delivery_location' => array(
				'{lat:29.672315761814783, lng:-95.50629501629203}',
				'{lat:29.677685074604, lng:-95.23987655926078}',
				'{lat:29.866025612305993, lng:-95.23918991375297}',
				'{lat:29.87019370055569, lng:-95.53925400066703}',
				),
				'delivery_location_filtered' => array(
				array(29.672315761814783,-95.50629501629203),
				array(29.677685074604,-95.23987655926078),
				array(29.866025612305993,-95.23918991375297),
				array(29.87019370055569,-95.53925400066703),
				),
				'frozr_peak_number' => 1,
				'frozr_vendor_store_notice' => 'Set your address near 1626 Dennis St, Houston, TX, to become within the delivery zone of this vendor. i.e: Fairview Street, Houston',
			),
			'seller7' => array(
				'frozr_vendor_type' => 'foodtruck',
				'nickname'=>'seller7','first_name' => 'Seller','last_name' => 'Seven',
				'rich_editing' => 'true','syntax_highlighting' => 'true','comment_shortcuts' => 'false','admin_color' => 'fresh','use_ssl' => '0','show_admin_bar_front' => 'true','wp_capabilities' => array('seller' => 1),'wp_user_level' => '0','frozr_enable_selling' => 'yes',
				'frozr_profile_settings' => array (
				'store_name' => 'Frok Cat',
				'socialfb' => '#','socialtwitter' => '#','socialyoutube' => '#',
				'payment' => array('paypal' => array('email' => 'seller7@mahmudhamid.com')),
				'phone' => '+100000000',
				'allow_email' => 1,
				'accpet_order_type' => array('delivery','pickup','dine-in','curbside'),
				'accpet_order_type_cl' => array('pickup','dine-in','curbside'),
				'allow_ofline_orders' => 'yes',
				'shipping_fee' => 1.5,
				'deliveryby' => 'order',
				'shipping_pro_adtl_cost' => 0,
				'min_order_amt' => 20,
				'shipping_fee_peak' => 2.5,
				'deliveryby_peak' => 'order',
				'shipping_pro_adtl_cost_peak' => 0,
				'min_order_amt_peak' => 20,
				),
				'rest_open_close_time' => array(
				'Sat' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '18:00','open_two' => '','close_two' =>''),
				'Sun' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '18:00','open_two' => '','close_two' =>''),
				'Mon' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '18:00','open_two' => '','close_two' =>''),
				'Tue' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '18:00','open_two' => '','close_two' =>''),
				'Wed' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '18:00','open_two' => '','close_two' =>''),
				'Thu' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '18:00','open_two' => '','close_two' =>''),
				'Fri' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '18:00','open_two' => '','close_two' =>''),
				),
				'_rest_unavds' => array(array('start' => '','end' => '',)),
				'_rest_meal_types' => array(
				array('title' => 'Breakfast','start' => '08:00','end' => '12:00',),
				array('title' => 'Special Orders','start' => '12:00','end' => '18:00',),
				),
				'frozr_food_type' => array('veg','nonveg','sea-food'),
				'rest_address_geo' => '37.7417823,-122.45648019999999',
				'terms' => array(
					'vendor_addresses' => 'Portola Drive, San Francisco, CA, USA',
					'vendorclass' => array('italian','barbecue')
				),
				'delivery_location' => array(
				'{lat:37.77695484249459, lng:-122.51880544738344}',
				'{lat:37.722390799669284, lng:-122.49133962707094}',
				'{lat:37.734881643090404, lng:-122.40344900207094}',
				'{lat:37.78373860142804, lng:-122.42782491759829}',
				),
				'delivery_location_filtered' => array(
				array(37.77695484249459,-122.51880544738344),
				array(37.722390799669284,-122.49133962707094),
				array(37.734881643090404,-122.40344900207094),
				array(37.78373860142804,-122.42782491759829)
				),
				'frozr_peak_number' => 1,
				'frozr_vendor_store_notice' => 'Set your address near Portola Drive, San Francisco, to become within the delivery zone of this vendor. i.e: Mission Street, San Francisco',
			),
			'seller8' => array(
				'frozr_vendor_type' => 'foodtruck',
				'nickname'=>'seller8','first_name' => 'Seller','last_name' => 'Eight',
				'rich_editing' => 'true','syntax_highlighting' => 'true','comment_shortcuts' => 'false','admin_color' => 'fresh','use_ssl' => '0','show_admin_bar_front' => 'true','wp_capabilities' => array('seller' => 1),'wp_user_level' => '0','frozr_enable_selling' => 'yes',
				'frozr_profile_settings' => array (
				'store_name' => 'Morbi',
				'socialfb' => '#','socialtwitter' => '#','socialyoutube' => '#',
				'payment' => array('paypal' => array('email' => 'seller8@mahmudhamid.com')),
				'phone' => '+100000000',
				'allow_email' => 1,
				'accpet_order_type' => array('delivery','pickup','dine-in','curbside'),
				'accpet_order_type_cl' => array('pickup','dine-in','curbside'),
				'allow_ofline_orders' => 'yes',
				'shipping_fee' => 3.3,
				'deliveryby' => 'order',
				'shipping_pro_adtl_cost' => 0,
				'min_order_amt' => 20,
				'shipping_fee_peak' => 4.5,
				'deliveryby_peak' => 'order',
				'shipping_pro_adtl_cost_peak' => 0,
				'min_order_amt_peak' => 20,
				),
				'rest_open_close_time' => array(
				'Sat' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '18:00','open_two' => '','close_two' =>''),
				'Sun' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '18:00','open_two' => '','close_two' =>''),
				'Mon' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '18:00','open_two' => '','close_two' =>''),
				'Tue' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '18:00','open_two' => '','close_two' =>''),
				'Wed' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '18:00','open_two' => '','close_two' =>''),
				'Thu' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '18:00','open_two' => '','close_two' =>''),
				'Fri' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '18:00','open_two' => '','close_two' =>''),
				),
				'_rest_unavds' => array(array('start' => '','end' => '',)),
				'_rest_meal_types' => array(
				array('title' => 'Breakfast','start' => '08:00','end' => '12:00',),
				array('title' => 'Special Orders','start' => '12:00','end' => '18:00',),
				),
				'frozr_food_type' => array('veg','nonveg','sea-food'),
				'rest_address_geo' => '37.7417823,-122.45648019999999',
				'terms' => array(
					'vendor_addresses' => 'Portola Drive, San Francisco, CA, USA',
					'vendorclass' => array('italian','barbecue')
				),
				'delivery_location' => array(
				'{lat:37.77695484249459, lng:-122.51880544738344}',
				'{lat:37.722390799669284, lng:-122.49133962707094}',
				'{lat:37.734881643090404, lng:-122.40344900207094}',
				'{lat:37.78373860142804, lng:-122.42782491759829}',
				),
				'delivery_location_filtered' => array(
				array(37.77695484249459,-122.51880544738344),
				array(37.722390799669284,-122.49133962707094),
				array(37.734881643090404,-122.40344900207094),
				array(37.78373860142804,-122.42782491759829)
				),
				'frozr_peak_number' => 1,
				'frozr_vendor_store_notice' => 'Set your address near Portola Drive, San Francisco, to become within the delivery zone of this vendor. i.e: Mission Street, San Francisco',
			),
			'seller9' => array(
				'frozr_vendor_type' => 'foodtruck',
				'nickname'=>'seller9','first_name' => 'Seller','last_name' => 'Nine',
				'rich_editing' => 'true','syntax_highlighting' => 'true','comment_shortcuts' => 'false','admin_color' => 'fresh','use_ssl' => '0','show_admin_bar_front' => 'true','wp_capabilities' => array('seller' => 1),'wp_user_level' => '0','frozr_enable_selling' => 'yes',
				'frozr_profile_settings' => array (
				'store_name' => 'Nofa cafe',
				'socialfb' => '#','socialtwitter' => '#','socialyoutube' => '#',
				'payment' => array('paypal' => array('email' => 'seller9@mahmudhamid.com')),
				'phone' => '+100000000',
				'allow_email' => 1,
				'accpet_order_type' => array('delivery','pickup','dine-in','curbside'),
				'accpet_order_type_cl' => array('pickup','dine-in','curbside'),
				'allow_ofline_orders' => 'yes',
				'shipping_fee' => 10,
				'deliveryby' => 'order',
				'shipping_pro_adtl_cost' => 0,
				'min_order_amt' => 20,
				'shipping_fee_peak' => 15,
				'deliveryby_peak' => 'order',
				'shipping_pro_adtl_cost_peak' => 0,
				'min_order_amt_peak' => 20,
				),
				'rest_open_close_time' => array(
				'Sat' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Sun' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Mon' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Tue' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Wed' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Thu' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				'Fri' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '21:00','open_two' => '','close_two' =>''),
				),
				'_rest_unavds' => array(array('start' => '','end' => '',)),
				'_rest_meal_types' => array(
				array('title' => 'Breakfast','start' => '08:00','end' => '11:00',),
				array('title' => 'Krank','start' => '10:00','end' => '15:00',),
				array('title' => 'Lunch & Dinner','start' => '14:00','end' => '21:00',),
				),
				'frozr_food_type' => array('veg','nonveg','sea-food'),
				'rest_address_geo' => '45.4825658,-122.79621329999998',
				'terms' => array(
					'vendor_addresses' => 'SW 5th St. Beaverton, OR, USA',
					'vendorclass' => array('chinese','thai')
				),
				'delivery_location' => array(
				'{lat:45.44118494180649, lng:-122.91086599720211}',
				'{lat:45.419018769897995, lng:-122.69800588978023}',
				'{lat:45.55188522617633, lng:-122.71929190052242}',
				'{lat:45.54274866618542, lng:-122.88374349964351}',
				),
				'delivery_location_filtered' => array(
				array(45.44118494180649,-122.91086599720211),
				array(45.419018769897995,-122.69800588978023),
				array(45.55188522617633,-122.71929190052242),
				array(45.54274866618542,-122.88374349964351),
				),
				'frozr_peak_number' => 1,
				'frozr_vendor_store_notice' => 'Set your address near SW 5th St. Beaverton, to become within the delivery zone of this vendor. i.e: Southwest Murray Boulevard, Beaverton',
			),
			'seller10' => array(
				'frozr_vendor_type' => 'grocery',
				'nickname'=>'seller10','first_name' => 'Seller','last_name' => 'Ten',
				'rich_editing' => 'true','syntax_highlighting' => 'true','comment_shortcuts' => 'false','admin_color' => 'fresh','use_ssl' => '0','show_admin_bar_front' => 'true','wp_capabilities' => array('seller' => 1),'wp_user_level' => '0','frozr_enable_selling' => 'yes',
				'frozr_profile_settings' => array (
				'store_name' => 'Excelente',
				'socialfb' => '#','socialtwitter' => '#','socialyoutube' => '#',
				'payment' => array('paypal' => array('email' => 'seller10@mahmudhamid.com')),
				'phone' => '+100000000',
				'allow_email' => 1,
				'accpet_order_type' => array('delivery','pickup'),
				'accpet_order_type_cl' => array(),
				'allow_ofline_orders' => 'no',
				'shipping_fee' => 1.6,
				'deliveryby' => 'order',
				'shipping_pro_adtl_cost' => 0,
				'min_order_amt' => 20,
				'shipping_fee_peak' => 3,
				'deliveryby_peak' => 'order',
				'shipping_pro_adtl_cost_peak' => 0,
				'min_order_amt_peak' => 20,
				),
				'rest_open_close_time' => array(
				'Sat' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				'Sun' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				'Mon' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				'Tue' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				'Wed' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				'Thu' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				'Fri' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				),
				'_rest_unavds' => array(array('start' => '','end' => '',)),
				'_rest_meal_types' => array(
				array('title' => 'General','start' => '08:00','end' => '23:00',),
				array('title' => 'Meat','start' => '08:00','end' => '12:00',),
				array('title' => 'Bakery','start' => '08:00','end' => '23:00',),
				),
				'frozr_food_type' => array('veg','nonveg'),
				'rest_address_geo' => '29.7824024,-95.55371980000001',
				'terms' => array(
					'vendor_addresses' => '943 Attingham Dr, Houston, TX 77024, USA',
					'vendorclass' => array('breads','canned goods','dried cereals')
				),
				'delivery_location' => array(
				'{lat:29.813205075825294, lng:-95.55210828781128}',
				'{lat:29.790952127341676, lng:-95.59159722932287}',
				'{lat:29.753792281300637, lng:-95.58370763079546}',
				'{lat:29.75304712570236, lng:-95.50088101641558}',
				'{lat:29.79802820308678, lng:-95.49250569947668}',
				),
				'delivery_location_filtered' => array(
				array(29.813205075825294,-95.55210828781128),
				array(29.790952127341676,-95.59159722932287),
				array(29.753792281300637,-95.58370763079546),
				array(29.75304712570236,-95.50088101641558),
				array(29.79802820308678,-95.49250569947668)
				),
				'frozr_peak_number' => 1,
				'frozr_vendor_store_notice' => 'Set your address near 943 Attingham Dr, Houston, TX to become within the delivery zone of this vendor. i.e: Memorial Drive, Houston',
			),
			'seller11' => array(
				'frozr_vendor_type' => 'grocery',
				'nickname'=>'seller11','first_name' => 'Seller','last_name' => 'Eleven',
				'rich_editing' => 'true','syntax_highlighting' => 'true','comment_shortcuts' => 'false','admin_color' => 'fresh','use_ssl' => '0','show_admin_bar_front' => 'true','wp_capabilities' => array('seller' => 1),'wp_user_level' => '0','frozr_enable_selling' => 'yes',
				'frozr_profile_settings' => array (
				'store_name' => 'Central Grocery',
				'socialfb' => '#','socialtwitter' => '#','socialyoutube' => '#',
				'payment' => array('paypal' => array('email' => 'seller11@mahmudhamid.com')),
				'phone' => '+100000000',
				'allow_email' => 1,
				'accpet_order_type' => array('delivery','pickup'),
				'accpet_order_type_cl' => array(),
				'allow_ofline_orders' => 'no',
				'shipping_fee' => 3.4,
				'deliveryby' => 'order',
				'shipping_pro_adtl_cost' => 0,
				'min_order_amt' => 20,
				'shipping_fee_peak' => 6,
				'deliveryby_peak' => 'order',
				'shipping_pro_adtl_cost_peak' => 0,
				'min_order_amt_peak' => 20,
				),
				'rest_open_close_time' => array(
				'Sat' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				'Sun' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				'Mon' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				'Tue' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				'Wed' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				'Thu' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				'Fri' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				),
				'_rest_unavds' => array(array('start' => '','end' => '',)),
				'_rest_meal_types' => array(
				array('title' => 'Sea Food','start' => '08:00','end' => '13:00',),
				array('title' => 'Dairy Products','start' => '08:00','end' => '11:00',),
				array('title' => 'General','start' => '08:00','end' => '23:00',),
				),
				'frozr_food_type' => array('veg','nonveg','sea-food'),
				'rest_address_geo' => '45.4825658,-122.79621329999998',
				'terms' => array(
					'vendor_addresses' => 'SW 5th St. Beaverton, OR, USA',
					'vendorclass' => array('frozen foods','nonalcoholic beverages','dairy products')
				),
				'delivery_location' => array(
				'{lat:45.44118494180649, lng:-122.91086599720211}',
				'{lat:45.419018769897995, lng:-122.69800588978023}',
				'{lat:45.55188522617633, lng:-122.71929190052242}',
				'{lat:45.53144612980211, lng:-122.86966726673336}'
				),
				'delivery_location_filtered' => array(
				array(45.44118494180649,-122.91086599720211),
				array(45.419018769897995,-122.69800588978023),
				array(45.55188522617633,-122.71929190052242),
				array(45.53144612980211,-122.86966726673336),
				),
				'frozr_peak_number' => 1,
				'frozr_vendor_store_notice' => 'Set your address near SW 5th St. Beaverton, to become within the delivery zone of this vendor. i.e: Southwest Murray Boulevard, Beaverton',
			),
			'seller12' => array(
				'frozr_vendor_type' => 'grocery',
				'nickname'=>'seller12','first_name' => 'Seller','last_name' => 'Twelve',
				'rich_editing' => 'true','syntax_highlighting' => 'true','comment_shortcuts' => 'false','admin_color' => 'fresh','use_ssl' => '0','show_admin_bar_front' => 'true','wp_capabilities' => array('seller' => 1),'wp_user_level' => '0','frozr_enable_selling' => 'yes',
				'frozr_profile_settings' => array (
				'store_name' => 'Blandit',
				'socialfb' => '#','socialtwitter' => '#','socialyoutube' => '#',
				'payment' => array('paypal' => array('email' => 'seller12@mahmudhamid.com')),
				'phone' => '+100000000',
				'allow_email' => 1,
				'accpet_order_type' => array('delivery','pickup'),
				'accpet_order_type_cl' => array(),
				'allow_ofline_orders' => 'no',
				'shipping_fee' => 2.2,
				'deliveryby' => 'order',
				'shipping_pro_adtl_cost' => 0,
				'min_order_amt' => 20,
				'shipping_fee_peak' => 4,
				'deliveryby_peak' => 'order',
				'shipping_pro_adtl_cost_peak' => 0,
				'min_order_amt_peak' => 20,
				),
				'rest_open_close_time' => array(
				'Sat' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				'Sun' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				'Mon' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				'Tue' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				'Wed' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				'Thu' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				'Fri' => array('restop' => 'yes','restshifts' =>'','open_one' => '08:00','close_one' => '23:00','open_two' => '','close_two' =>''),
				),
				'_rest_unavds' => array(array('start' => '','end' => '',)),
				'_rest_meal_types' => array(
				array('title' => 'Dairy Products','start' => '08:00','end' => '11:00',),
				array('title' => 'General','start' => '08:00','end' => '23:00',),
				),
				'frozr_food_type' => array('veg','nonveg','sea-food'),
				'rest_address_geo' => '36.720844,-119.9076096',
				'terms' => array(
					'vendor_addresses' => 'Fresno Street, Fresno, CA, USA',
					'vendorclass' => array('frozen foods','nonalcoholic beverages','dairy products')
				),
				'delivery_location' => array(
				'{lat:36.633050707807776, lng:-120.12795199617489}',
				'{lat:36.61982521873533, lng:-119.58412875398739}',
				'{lat:36.88390342640361, lng:-119.56215609773739}',
				'{lat:36.914653309590456, lng:-120.02358187898739}',
				),
				'delivery_location_filtered' => array(
				array(36.633050707807776,-120.12795199617489),
				array(36.61982521873533,-119.58412875398739),
				array(36.88390342640361,-119.56215609773739),
				array(36.914653309590456,-120.02358187898739),
				),
				'frozr_peak_number' => 1,
				'frozr_vendor_store_notice' => 'Set your address near Fresno Street, Fresno to become within the delivery zone of this vendor. i.e: West California Avenue, Fresno',
			)
		);
		$items = array( array(
			'attribute_names' => array(
			array("choose-a-drink" => "Choose a Drink", "add-cheese" => "Add Cheese"),
			array("flavor" => "Flavor"),
			array("choose-a-drink" => "Choose a Drink", "choose-a-side" => "Choose a Side", "choose-of-protein" => "Choose of Protein"),
			array("add-on" => "Add-on", "choose-of-protein" => "Choose of Protein"),
			),
			'attribute_values' => array(
			array("add-cheese" => "cheddar | swiss | provolone | pepperjack | havarti | smoked gouda | cheddar | smoke mozzarella | fresh mozzarella","choose-a-drink" => "chocolate shake | vanilla shake | tea | coffee | ice tee"),
			array("flavor" => "grape | apple | pineapple | orange | banana | coconut | green tee | hazelnut"),
			array("choose-a-drink" => "chocolate shake | vanilla shake | tea | coffee | ice tee", "choose-a-side" => "fries | side salad", "choose-of-protein" => "BBQ pork | beef | chicken"),
			array("add-on" => "Shrimp | crispy roll | fried egg", "choose-of-protein" => "BBQ Pork | Beef | chicken"),
			),
			'item_ingredients' => array(
				"fish- olive oil",
				"cheese- salad- salt-",
				"cream- eggs- milk- strawberry- sugar",
				"lamp",
				"fish- mushroom",
				"chocolate- milk- sugar",
				"turkey",
				"beans & legumes",
				"chicken",
				"pork",
				"fruit",
				"peapper- corn- carrots",
				"checken breast",
				"baked chicken",
				"chicken leg",			
			),
			'item_veg' => array("veg","nonveg"),
			'item_has_options' => 'yes',
			'item_options' => array(
				array(array("add-cheese" => "", "choose-a-drink" => "", "description" => "Mauris euismod orci in tortor mattis scelerisque. Aliquam id cursus metus. Ut nec tempor mi. In gravida at lorem quis placerat.","regular_price" => 15)),
				array(array("flavor" => "","description"=>"Praesent accumsan ut ligula vel accumsan.","regular_price" => 12),array("flavor" => "grape","description"=>"Vivamus consectetur eget dui a dignissim.","regular_price" => 10)),
				array(
				array("choose-a-drink" => "","choose-a-side" => "fries", "choose-of-protein" => "BBQ Pork", "description" => "Quisque justo libero, pretium vel aliquam sit amet, congue ac erat.","regular_price" => 20),
				array("choose-a-drink" => "","choose-a-side" => "side salad", "choose-of-protein" => "Beef", "description" => "Quisque justo libero, pretium vel aliquam sit amet, congue ac erat.","regular_price" => 30),
				array("choose-a-drink" => "","choose-a-side" => "", "choose-of-protein" => "chicken", "description" => "Quisque justo libero, pretium vel aliquam sit amet, congue ac erat.","regular_price" => 12),
				),
				array(
				array("add-on" => "Shrimp", "choose-of-protein" => "BBQ Pork", "description" => "Quisque justo libero, pretium vel aliquam sit amet, congue ac erat.","regular_price" => 10),
				array("add-on" => "crispy roll", "choose-of-protein" => "Beef", "description" => "Quisque justo libero, pretium vel aliquam sit amet, congue ac erat.","regular_price" => 26),
				array("add-on" => "fried egg", "choose-of-protein" => "chicken", "description" => "Quisque justo libero, pretium vel aliquam sit amet, congue ac erat.","regular_price" => 20),
				),
			),
			'post_title' => array("Quis placerat","Maximus amet","Ultricies est","Euismod orci","Pretium nisl","Diam interdum","Blandit risus","Ullamcorper eget","Morbi imperdiet","Malesuada erat","Velit dignissim","Nisl maximus","Magna consectetur","Urna commodo","Tristique enim","Phasellus sapien","Placerat iaculis","Curabitur turpis"),
			'post_content' => "Vel blandit diam facilisis non. Nam magna urna, tristique eu elit a, gravida elementum quam. Curabitur malesuada ante lacus, scelerisque feugiat tellus eleifend eu.",
			'post_excerpt' => "Sed ullamcorper sapien felis, quis placerat purus pretium porta. Phasellus porttitor elementum viverra.",
			'product_cat' => array("Burgers", "Side Products", "Sandwiches", "Main Course", "Appetizers", "Desserts"),
			'total_sales' => 0,
		), array(
			'attribute_names' => array(
			array("choose-size" => "size", "choose-brand" => "brand"),
			array("flavor" => "Flavor"),
			),
			'attribute_values' => array(
			array("choose-brand" => "curabitur | morbi | fusce | aliquam | vivamus | quisque | mauris | pulvinar","choose-size" => "large | medium | small"),
			array("flavor" => "classic | fruits | coconut | green tee | hazelnut"),
			),
			'item_veg' => array("veg","nonveg"),
			'item_has_options' => 'yes',
			'item_options' => array(
				array(array("choose-brand" => "", "choose-size" => "", "description" => "Mauris euismod orci in tortor mattis scelerisque. Aliquam id cursus metus. Ut nec tempor mi. In gravida at lorem quis placerat.","regular_price" => 15)),
				array(array("flavor" => "","description"=>"Praesent accumsan ut ligula vel accumsan.","regular_price" => 12),array("flavor" => "classic","description"=>"Vivamus consectetur eget dui a dignissim.","regular_price" => 10)),
			),
			'post_title' => array("Quis placerat","Maximus amet","Ultricies est","Euismod orci","Pretium nisl","Diam interdum","Blandit risus","Ullamcorper eget","Morbi imperdiet","Malesuada erat","Velit dignissim","Nisl maximus","Magna consectetur","Urna commodo","Tristique enim","Phasellus sapien","Placerat iaculis","Curabitur turpis"),
			'post_content' => "Vel blandit diam facilisis non. Nam magna urna, tristique eu elit a, gravida elementum quam. Curabitur malesuada ante lacus, scelerisque feugiat tellus eleifend eu.",
			'post_excerpt' => "Sed ullamcorper sapien felis, quis placerat purus pretium porta. Phasellus porttitor elementum viverra.",
			'product_cat' => array("Diet Foods", "Canned Goods", "Frozen Foods", "Pet Foods", "Snack Foods", "Meats & Seafoods", "Baby Foods"),
			'total_sales' => 0,
		));
		
		/*Get Norsani Settings*/
		$option = get_option( 'frozr_gen_settings' );
		$allowed_vendors = frozr_get_allowed_vendors_types();
		$featured_items = (! empty( $option['frozr_reco_items']) ) ? $option['frozr_reco_items'] : array('0');
		$featured_sellers = (! empty($option['frozr_reco_sellers']) ) ? $option['frozr_reco_sellers'] : array('0');
		$featured_seller_count = 0;
		$upsells = $crossells = array();
		$meta_counter = 0;
		$meta_key_counter = 0;
		$passwords = wp_generate_password();
		$users = array();
		$numbers = array('seller1' => 'one','seller2' => 'two','seller3' => 'three','seller4' => 'four','seller5' => 'five','seller6' => 'six','seller7' => 'seven','seller8' => 'eight','seller9' => 'nine','seller10' => 'ten','seller11' => 'eleven','seller12' => 'twelve');
		
		$vendors_to_add = array();
		if (in_array('chef',$allowed_vendors)) {
			$vendors_to_add[] = 'seller1';
			$vendors_to_add[] = 'seller2';
			$vendors_to_add[] = 'seller3';
		}
		if (in_array('restaurant',$allowed_vendors)) {
			$vendors_to_add[] = 'seller4';
			$vendors_to_add[] = 'seller5';
			$vendors_to_add[] = 'seller6';
		}
		if (in_array('foodtruck',$allowed_vendors)) {
			$vendors_to_add[] = 'seller7';
			$vendors_to_add[] = 'seller8';
			$vendors_to_add[] = 'seller9';
		}
		if (in_array('grocery',$allowed_vendors)) {
			$vendors_to_add[] = 'seller10';
			$vendors_to_add[] = 'seller11';
			$vendors_to_add[] = 'seller12';
		}
		
		foreach ($vendors_to_add as $vendo_name) {
			$users[$vendo_name] = array(
				'user_login' => $vendo_name,
				'user_pass' => $passwords,
				'user_email' => $vendo_name."@mahmudhamid.com",
				'first_name' => 'Seller',
				'last_name' => $numbers[$vendo_name],
				'user_nicename' => 'seller-'.$numbers[$vendo_name],
				'role' => 'seller',
			);
		}

		/*Add vendors and products*/
		$users_added = false;
		foreach ($users as $key => $val) {
			$get_vendor = get_user_by( 'email', $val['user_email']);
			if (!$get_vendor) {
			$items_key = ($key == 'seller10' || $key == 'seller11' || $key == 'seller12') ? 1 : 0;
			$user_id = wp_insert_user($val);
			$vendor_meta = $users_meta[$key];
			if (!is_wp_error($user_id)) {
				$users_added = true;
				/*Add vendor as featured*/
				if ($featured_seller_count < 5) {
					$featured_sellers[] = $user_id;
					$featured_seller_count++;
				}
				
				foreach ($vendor_meta as $mkey => $mval) {
					if ($mkey == 'terms') {
						foreach ($mval as $tkey => $tval) {
							wp_set_object_terms($user_id, $tval, $tkey);
						}
					} else {
						update_user_meta($user_id,$mkey,$mval);
					}
				}
				/*Reset Featured items count*/
				$feat_count = 0;
				/*Add Items*/
				for ($x = 1; $x <= 6; $x++) {
					$item_info = array(
						'post_type' => 'product',
						'post_title' => $items[$items_key]['post_title'][mt_rand(0, count($items[$items_key]['post_title']) - 1)],
						'post_author' => $user_id,
						'post_content' => $items[$items_key]['post_content'],
						'post_excerpt' => $items[$items_key]['post_excerpt'],
						'post_status' => 'publish',
						'comment_status' => 'closed',
					);			
					$item_id = wp_insert_post( $item_info );
					if (!is_wp_error($item_id)) {
					/*Add Featured Product*/
					if ($feat_count == 0) {
						$featured_items[] = $item_id;
						$feat_count++;
					}
					$upsells[] = $item_id;
					$crossells[] = $item_id;
					$select = mt_rand(0, count($items[$items_key]['attribute_names']) - 1);
					$vendor_menus = $vendor_meta['_rest_meal_types'][mt_rand(0, count($vendor_meta['_rest_meal_types']) - 1)];

					$_POST = array(
						'attribute_names' => $items[$items_key]['attribute_names'][$select],
						'attribute_values' => $items[$items_key]['attribute_values'][$select],
						'item_ingredients' => isset($items[$items_key]['item_ingredients']) ? $items[$items_key]['item_ingredients'][mt_rand(0, count($items[$items_key]['item_ingredients']) - 1)]: '',
						'item_veg' => $items[$items_key]['item_veg'][mt_rand(0, count($items[$items_key]['item_veg']) - 1)],
						'item_has_options' => 'yes',
						'item_options' => $items[$items_key]['item_options'][$select],
						'post_title' => $items[$items_key]['post_title'][mt_rand(0, count($items[$items_key]['post_title']) - 1)],
						'post_content' => $items[$items_key]['post_content'],
						'post_excerpt' => $items[$items_key]['post_excerpt'],
						'product_cat' => $items[$items_key]['product_cat'][mt_rand(0, count($items[$items_key]['product_cat']) - 1)],
						'product_meal_type' => sanitize_title(wp_unslash($vendor_menus['title'])),
						'item_promotions' => array(),
						'item_pretime' => mt_rand(5,20),
						'item_maxords' => mt_rand(15,60),
						'upsell_ids' => $upsells,
						'crosssell_ids' => $crossells,
						'total_sales' => 0,
						);

					norsani()->item->frozr_process_item_meta($item_id);
					
					/* Clear cache/transients*/
					wc_delete_product_transients( $item_id );
					add_post_meta( $item_id, '_edit_last', $user_id );
					}
				}
				$_POST = array();
			}
			}
		}
		/*Add featured vendors*/
		$option['frozr_reco_sellers'] = $featured_sellers;
		$option['frozr_reco_items'] = $featured_items;
		update_option("frozr_gen_settings", $option);
		
		/*Add pages*/
		$this->frozr_add_norsani_pages();
		
		/*Add menus*/
		$menu_name = __("Norsani's Demo Menu","frozr-norsani");
		$menu_exists = wp_get_nav_menu_object($menu_name);

		if(!$menu_exists){
		$menu_id = wp_create_nav_menu($menu_name);
		$locations = get_theme_mod('nav_menu_locations');
		
		if ($this->frozr_check_page('vendors.php')) {
		$default_types = frozr_get_default_vendors_types();
		$allowed_vendors = frozr_get_allowed_vendors_types();
		$default_icon_classes = apply_filters('frozr_default_icon_classes', array('chef'=>'nor_bc_menu','restaurant'=>'nor_br_menu','grocery'=>'nor_bg_menu','foodtruck'=>'nor_bft_menu'));
		foreach($allowed_vendors as $vendor_type) {
			$vend_type_display = isset($default_types[$vendor_type]) ? $default_types[$vendor_type] : $vendor_type;
			wp_update_nav_menu_item($menu_id, 0, array(
				'menu-item-title' => __('Browse','frozr-norsani').' '.$vend_type_display,
				'menu-item-classes' => isset($default_icon_classes[$vendor_type]) ? $default_icon_classes[$vendor_type] : 'nor_gn_menu',
				'menu-item-object' => 'page',
				'menu-item-status' => 'publish',
				'menu-item-type' => 'custom',
				'menu-item-url' => home_url("/vendors/$vendor_type"),
			));
		}
		}

		if ($this->frozr_check_page('vendors-registration.php')) {
		$vendor_reg_page_is_installed = get_option('vendors-registration');
		wp_update_nav_menu_item($menu_id, 0, array(
			'menu-item-title' => __('Vendor Registration','frozr-norsani'),
			'menu-item-object-id' => $vendor_reg_page_is_installed,
			'menu-item-classes' => 'nor_vr_menu',
			'menu-item-object' => 'page',
			'menu-item-status' => 'publish',
			'menu-item-type' => 'post_type',
		));
		}
		/*Set our demo menu as main menu*/
		$locations['primary'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
		}
		
		/*Set WooCommerce base country to US*/
		update_option( 'woocommerce_default_country', 'US' );
		update_option( 'woocommerce_specific_allowed_countries', array('US') );

		do_action('frozr_before_demo_data_complete');

		$passetted = '';
		if ($users_added) {
			$passetted = ' '.sprintf(__('Please use the following password to login to the accounts of the newly added users: %1$s','frozr-norsani'),$passwords);
		}
		wp_send_json(array('message'=>sprintf(__('Demo data was installed successfully. %1$s','frozr-norsani'),$passetted)));
		die();
	}

	/**
	 * Check and install Norsani template pages
	 *
	 */
	public function frozr_add_norsani_pages() {
		$norsani_pages = array(
			'vendors-registration' => __('Vendor Registration','frozr-norsani'),
			'vendors' => __('Vendors list','frozr-norsani'),
			'sellers' => __('Sellers list.','frozr-norsani'),
		);
		
		foreach($norsani_pages as $page_key => $page_val) {
			$page_temp = $page_key.'.php';
			if (!$this->frozr_check_page($page_temp)) {
				$args = array(
					'post_title' => $page_val,
					'post_content' => __('This page is used by Norsani. You can freely change this text but please do NOT delete it.','frozr-norsani'),
					'post_status' => "publish",
					'post_type' => 'page',
					'comment_status' => 'closed',
					'ping_status' => 'closed',
				);
				$create_page = wp_insert_post($args, $wp_error);
				if (!is_wp_error($create_page)) {
					update_post_meta( $create_page, '_wp_page_template', $page_temp);
				}
			}
		}
	}

	/**
	 * Norsani Vendor Admin Help database
	 *
	 * @param string $help_key		Help text key to return.
	 * @param bool $show_html		Return a plan string or HTML?
	 * @return string|html
	 */
	public function frozr_admin_help_db($help_key,$show_html=false){
		$google_key_help = '<a href="'.site_url() . '/wp-admin/admin.php?page=norsani_help_center&q=google_key">'.__('here','frozr-norsani').'</a>';
		$paypal_key_help = '<a href="https://developer.paypal.com/docs/integration/admin/manage-apps/">'.__('here','frozr-norsani').'</a>';
		$paypal_payouts = '<a href="https://developer.paypal.com/docs/integration/direct/payouts/integration-guide/payment_processing/">'.__('Learn how payments are processed','frozr-norsani').'</a>';
		$paypal_payouts_faq = '<a href="https://developer.paypal.com/docs/integration/direct/payouts/integration-guide/faq/">'.__('PayPal Payouts FAQ','frozr-norsani').'</a>';
		$woo_terms_help = '<a href="'.site_url() . '/wp-admin/admin.php?page=wc-settings&tab=advanced">'.__('Advance','frozr-norsani').'</a>';
		$sellers_page_link = '<a href="'.site_url() . '/dashboard/sellers/">'.__('Sellers','frozr-norsani').'</a>';
		$withdraws_page = '<a href="'.site_url() . '/dashboard/withdraw/">'.__('Withdraw','frozr-norsani').'</a>';
		
		$help_text_list = apply_filters('frozr_admin_help_db', array(
			'google_key' => sprintf(__("You must enter your Google API key to enable Norsani's user location based services such as Geolocation, Delivery orders and vendor addresses. Click %s for instruction on how to get an API key.","frozr-norsani"), $google_key_help),
			'vendor_types' => __('Enter the type of vendors you allow to register separated with commas. If you remove a vendor type, it will be removed from the vendor registration form but vendors holding that type are still going to be available until you manually delete them.','frozr-norsani'),
			'enable_auto_selling' => sprintf(__('If you selected this, vendors will be able to post their products immediately after registeration. You can manually activate/deactivate selling privileges for a vendor from the %s page.','frozr-norsani'), $sellers_page_link),
			'manual_online' => __("If this field is selected, the vendor shop's open/close timing options will not be available and vendors will have to manually change their status to 'online' to receive orders. This will insure that vendors are available to process orders once they are received. However, vendors can also change their status manually even if this option is not selected.","frozr-norsani"),
			'manual_time' => __("If the above option is selected, the system will ignore the vendor's shop timing data and make the vendor's original status to 'offline' until they manually change it to 'online'. However, vendors can manually change their original status for a specific time. In that case, enter the time the system should wait before returning the vendor status to its original status if the vendor has closed or navigated away from your website. This is helpful when you wish to make sure that the vendor is really online to receive and process orders.","frozr-norsani"),
			'featured_items' => __('Featured products will appear to users when they click the main search button.','frozr-norsani'),
			'featured_vendors' => __('Featured vendors will appear to users when they click the main search button.','frozr-norsani'),
			'auto_withdraw' => __('Select this field to automatically create withdrawal requests for vendors after their orders statuses change to completed.','frozr-norsani'),
			'client_id' => sprintf(__('If you wish to pay a withdrawal request directly from the %1$s page with one click using "PayPal" Payouts, please enter your "PayPal App ClientId". Click %2$s if you need instructions on creating a "PayPal REST App".','frozr-norsani'),$withdraws_page, $paypal_key_help),
			'secret_id' => sprintf(__('If you wish to pay a withdrawal request directly from the %1$s page with one click using "PayPal" Payouts, please enter your "PayPal App ClientSecret". Click %2$s if you need instructions on creating a "PayPal REST App".','frozr-norsani'),$withdraws_page, $paypal_key_help),
			'minimum_withdraw' => __('When vendors manually create withdrawal requests, what is the minimum amount a vendor can ask for?','frozr-norsani'),
			'withdraw_methods' => __('Select your preferred methods to pay vendors. Each selected method will add new fields to the vendors settings page, vendors will use those settings to provide you with the required information to pay them.','frozr-norsani'),
			'withdraw_status' => __('Select the default withdrawal request status when vendors submit them. The default status of an automatically created withdrawal request is "pending".','frozr-norsani'),
			'distance' => __('These settings are used to calculate the delivery cost and distance between vendors and customers.','frozr-norsani'),
			'fees' => __('These fees are only applied on vendors. This means customers will not be affected and will only pay the original total amount of the order. To add fees on customers, please use the WooCommerce Tax settings.','frozr-norsani'),
			'cod_info' => __("With 'Cash on Delivery' orders, customers will pay the order total amount including your fees to the delivery person and thus you will not receive any payment. In that case, the system by default deducts your fees from the vendor account's balance on your website.","frozr-norsani"),
			'terms' => sprintf(__('Enter terms and conditions for vendors, this will be used in the vendor registration form. To add terms for customers go to WooCommerce %s settings.','frozr-norsani'),$woo_terms_help),
			'fee_customers_effected' => __('Apply this fee on vendors when they receive orders from all, some or selected customers.','frozr-norsani'),
			'fee_sellers_effected' => __('Select vendors who will be effected by this fee.','frozr-norsani'),
			'fee_order_amount' => __('A float value which if the sub-total amount of the order exceeds, this rule will be applied.','frozr-norsani'),
			'fee_amount_effect' => __('Whether to apply this rule on the order total including delivery fee or only on the order delivery fee.','frozr-norsani'),
			'fee_rate' => __('The amount to deduct from orders. The first field is a percentage which is multiplied by the total order or delivery fee. i.e: if order total is $10 and you have entered 2.9 in first field and selected "Plus" and entered 3 in the last field, the total fee applied will be: ($10 x 2.9 / 100) + 3 = $3.29','frozr-norsani'),
			'fee_payment_method' => __('This rule will only be applied if the order was paid with the selected payment method.','frozr-norsani'),
			'fee_fee_title' => __('Enter a name for this fee. i.e: Website Fee','frozr-norsani'),
			'fee_description' => __('Let the vendor know why are you applying this fee.','frozr-norsani'),
			'accepted_orders' => __('Select the order types allowed on your website. Vendors will be restricted to select their accepted order types from your selection here.','frozr-norsani'),
			'accepted_orders_closed' => __('Vendors can also allow receiving pre-orders from their stores while closed. Select the order types that are allowed for vendors to allow while their stores are closed.','frozr-norsani'),
		));
		
		if ($show_html) {
			return $help_text_list[$help_key];
		}
		if (isset($help_text_list[$help_key])) {
			return '&nbsp;<span class="frozr_tooltip_wrapper"><span class="frozr_help_tip">'.$help_text_list[$help_key].'</span></span>&nbsp';
		}
	}

	/**
	 * Active default Norsani theme (FrozrDash)
	 *
	 */
	public function frozr_activate_default_theme() {
		check_ajax_referer( 'frozr_default_theme_activation', 'security' );
		$url = site_url() . '/wp-admin/';
		switch_theme( 'frozrdash-child' );
		flush_rewrite_rules();
		wp_send_json(array('redirect'=>$url.'admin.php?page=norsani_help_center&theme=activated'));
		die();
	}

	/**
	 * Install default Norsani theme (FrozrDash)
	 *
	 */
	public function frozr_install_default_theme() {
		check_ajax_referer( 'frozr_default_theme_installation', 'security' );
		$url = site_url() . '/wp-admin/';
		
		ob_start();
			
		if (false == $creds = request_filesystem_credentials($url, '', false, false, null)) {
			$credit_form = ob_get_clean();
			wp_send_json(array('needcredit'=>$credit_form));
			die(); // stop processing here
		}

		if (!WP_Filesystem($creds)) {
			request_filesystem_credentials($url, '', true, false, null);
			$credit_form = ob_get_clean();
			wp_send_json(array('needcredit'=>$credit_form));
			return;
		}

		global $wp_filesystem;
		$themes_path = $wp_filesystem->wp_themes_dir();
		
		$theme_exisit = wp_get_theme('frozrdash');
		$child_theme_exisit = wp_get_theme('frozrdash-child-theme');
		$child_theme_file = NORSANI_PATH . '/assets/theme/frozrdash.zip'; 
		$theme_file = NORSANI_PATH . '/assets/theme/frozrdash.zip';
		$result = $child_result = true;
		
		if (!$theme_exisit->exists()) {
		$result = unzip_file($theme_file, $themes_path); 
		}
		if (!$child_theme_exisit->exists()) {
		$child_result = unzip_file($child_theme_file, $themes_path); 
		}
		
		if($result != true || $child_result != true) {
			wp_send_json(array('unziperror'=>__('We could not complete the theme installation process due to restricted write permissions.','frozr-norsani')));
			die();
		}
		
		switch_theme('frozrdash-child');

		flush_rewrite_rules();
		wp_send_json(array('redirect'=>$url.'admin.php?page=norsani_help_center&theme=installed'));
		die();
	}

	/**
	 * Change the columns shown in WC admin.
	 * 
	 * @param array $existing_columns
	 * @return array
	 */
	public function frozr_admin_shop_order_edit_columns( $existing_columns ) {

		$existing_columns['seller']			= __( 'Vendor', 'frozr-norsani' );
		$existing_columns['suborder']		= __( 'Sub Order', 'frozr-norsani' );
		$existing_columns['order_date']		= __( 'Preparation Time', 'frozr-norsani' );
		$existing_columns['order_receive']	= __( 'Receive Date', 'frozr-norsani' );
		
		return apply_filters('frozr_admin_shop_order_edit_columns',$existing_columns);
	}

	/**
	 * Adds custom column on WC admin shop order table
	 *
	 * @global type $post
	 * @global type $woocommerce
	 * @global WC_Order $the_order
	 * @param type $col
	 */
	public function frozr_shop_order_custom_columns( $col ) {
		global $post, $woocommerce, $the_order;

		if ( empty( $the_order ) || $the_order->get_id() != $post->ID ) {
			$the_order = wc_get_order( $post->ID );
		}

		switch ($col) {
			case 'order_status':
				$get_pretim = $the_order->get_date_created()->date_i18n( 'Y-m-d H:i' );
				$today = new DateTime(date('Y-m-d H:i',strtotime(current_time('mysql'))));
				$ord_t = new DateTime(date('Y-m-d H:i',strtotime($get_pretim)));

				if ($ord_t > $today) {
				echo '<span class="order-status frozr_order_inq">'.__('In Queue','frozr-norsani').'</span>';
				}
			break;
			case 'suborder':
				$has_sub = get_post_meta( $post->ID, 'has_sub_order', true );

				if ( $has_sub ) {
					printf( '<a href="#" class="show-sub-orders" data-class="parent-%1$d" data-show="%2$s" data-hide="%3$s">%2$s</a>', $post->ID, __( 'Show Sub-Orders', 'frozr-norsani' ), __( 'Hide Sub-Orders', 'frozr-norsani' ));
				} else {
					echo 'N/A';
				}
				break;
			case 'seller':
				$has_sub = get_post_meta( $post->ID, 'has_sub_order', true );

				if ( !$has_sub ) {
					$seller = get_user_by( 'id', frozr_get_order_author($the_order->get_id()) );
					printf( '<a href="%s">%s</a>', admin_url( 'edit.php?post_type=shop_order&author=' . $seller->ID ), $seller->display_name );
				} else {
					echo __('Multiple','frozr-norsani');
				}
				break;
			case 'order_date':
				echo '<div class="frozr_pretime">';
				norsani()->order->frozr_get_order_pre_time($the_order);
				echo '</div>';
				break;
			case 'order_receive':
				norsani()->order->frozr_order_time($post);
				break;
		}
	}

	/**
	 * Adds css classes on WC admin shop order table
	 *
	 * @global WP_Post $post
	 * @param array $classes
	 * @param int $post_id
	 * @return array
	 */
	public function frozr_admin_shop_order_row_classes( $classes, $post_id ) {
		global $post;

		if ( $post->post_type == 'shop_order' && $post->post_parent != 0 ) {
			$classes[] = 'sub-order parent-' . $post->post_parent;
		}

		return $classes;
	}

	/**
	 * Show/hide sub order in WC admin css/js
	 *
	 * @return void
	 */
	public function frozr_admin_shop_order_scripts() {
		?>
		<script type="text/javascript">
		jQuery(function($) {
			$('tr.sub-order').hide();

			$('a.show-sub-orders').on('click', function(e) {
				e.preventDefault();

				var $self = $(this),
					el = $('tr.' + $self.data('class') );

				if ( el.is(':hidden') ) {
					el.show();
					$self.text( $self.data('hide') );
				} else {
					el.hide();
					$self.text( $self.data('show') );
				}
			});

			$('button.toggle-sub-orders').on('click', function(e) {
				e.preventDefault();

				$('tr.sub-order').toggle();
			});
		});
		</script>

		<style type="text/css">
			tr.sub-order {
				background: #ECFFF2 !important;
			}
		</style>
		<?php
	}

	/**
	 * Minus vendor balance if order changed from completed to any other status
	 *
	 * @param int $order_id
	 * @param string $old_status
	 * @param string $new_status
	 * @param object $order
	 */
	public function frozr_on_order_refund( $order_id, $old_status, $new_status, $order ) {
		
		$vendor_id = frozr_get_order_author($order_id);
		$seller_current_balance = floatval(get_user_meta($vendor_id,"_vendor_balance", true));
		$is_parent_order = get_post_meta( $order_id, 'has_sub_order', true );
		$order_cod_option = get_post_meta( $order_id, 'frozr_cod_option_sts', true );

		if ( $old_status == 'completed' && !$is_parent_order ) {
			$seller_profit = floatval(get_post_meta( $order_id, 'frozr_order_seller_profit', true ));		
			$website_profit = floatval(get_post_meta( $order_id, 'frozr_order_website_fee', true ));

			if ($order->get_payment_method() == 'cod' && $order_cod_option != 1) {
				$seller_new_balance = apply_filters('frozr_final_refund_value_cod', $seller_current_balance + $website_profit, $order_id, $old_status, $new_status, $order);
			} else {
				$seller_new_balance =  apply_filters('frozr_final_refund_value_no_cod', $seller_current_balance - $seller_profit, $order_id, $old_status, $new_status, $order);
			}

			update_user_meta($vendor_id, "_vendor_balance", $seller_new_balance);

			do_action('frozr_send_vendor_order_refund_email', $order, $new_status);
		}
	}

	/**
	 * Show a toggle button to toggle sub orders in WC admin
	 *
	 * @global WP_Query $wp_query
	 */
	public function frozr_admin_shop_order_toggle_sub_orders() {
		global $wp_query;

		if ( isset( $wp_query->query['post_type'] ) && $wp_query->query['post_type'] == 'shop_order' ) {
			echo '<button class="toggle-sub-orders button">' . __( 'Toggle Sub-orders', 'frozr-norsani' ) . '</button>';
		}
	}

	/**
	 * Add Norsani custom fields to WP user profile
	 *
	 * @param object $user
	 * @return void
	 */
	public function frozr_add_meta_fields( $user ) {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( !user_can( $user, 'frozer' ) ) {
			return;
		}

		$selling = get_user_meta( $user->ID, 'frozr_enable_selling', true ); ?>
		<h3><?php _e( 'Norsani Options', 'frozr-norsani' ); ?></h3>
		<table class="form-table">
			<tbody>
				<tr>
				<th><?php _e( 'Selling', 'frozr-norsani' ); ?></th>
				<td>
					<label for="frozr_enable_selling">
						<input type="hidden" name="frozr_enable_selling" value="no">
						<input name="frozr_enable_selling" type="checkbox" id="frozr_enable_selling" value="yes" <?php checked( $selling, 'yes' ); ?> />
						<?php _e( 'Enable Selling', 'frozr-norsani' ); ?>
					</label>

					<p class="description"><?php _e( 'Enable or disable the product selling capability', 'frozr-norsani' ) ?></p>
				</td>
				</tr>
			</tbody>
		</table>
	<?php
	}

	/**
	* Save Norsani custom user data on WP save user profile
	*
	* @param int $user_id
	* @return void
	*/
	public function frozr_save_user_meta_fields( $user_id ) {
		
		if ( ! is_super_admin() ) {
			return;
		}
		
		$vendor = get_user_by( 'id', $user_id );
		$selling = esc_attr( $_POST['frozr_enable_selling'] );

		if ($selling != get_user_meta($user_id, 'frozr_enable_selling', true)) {
			$msg_args = apply_filters('frozr_save_user_meta_fields_msg_args',array(
				'id' => $user_id,
				'to' => sanitize_email($vendor->user_email),
				'shopname' => frozr_get_store_url($user_id),
				'type' => 'privileges',
			));
			do_action('frozr_send_vendor_status_message', $msg_args);
		}

		update_user_meta( $user_id, 'frozr_enable_selling', $selling );
		
	}
	
	/**
	 * Adds additional columns to WC admin products table
	 *
	 * @param array $columns
	 * @return array
	 */
	public function frozr_admin_product_columns( $columns ) {
		$columns['author'] = __( 'Author', 'frozr-norsani' );

		return $columns;
	}
}