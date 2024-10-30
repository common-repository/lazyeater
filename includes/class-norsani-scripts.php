<?php
/**
 * Handle Norsani scripts
 *
 * @package Norsani/Classes
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * General scripts class.
 */
class Norsani_Scripts {

	/**
	 * Contains an array of script handles registered by Norsani.
	 *
	 * @var array
	 */
	public static $scripts = array();

	/**
	 * Contains an array of script handles registered by Norsani.
	 *
	 * @var array
	 */
	public static $styles = array();

	/**
	 * Contains an array of script handles localized by Norsani.
	 *
	 * @var array
	 */
	public static $wp_localize_scripts = array();

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'load_admin_scripts' ) );
		add_action( 'wp_print_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
		add_action( 'wp_print_footer_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
	}

	/**
	 * Return asset URL.
	 *
	 * @param string $path Assets path.
	 * @return string
	 */
	public static function get_asset_url( $path ) {
		return apply_filters( 'norsani_get_asset_url', plugins_url( 'assets/'.$path, NORSANI_FILE ), $path );
	}

	/**
	 * Register a script for use.
	 *
	 * @uses   wp_register_script()
	 * @param  string   $handle    Name of the script. Should be unique.
	 * @param  string   $path      Full URL of the script, or path of the script relative to the WordPress root directory.
	 * @param  string[] $deps      An array of registered script handles this script depends on.
	 * @param  string   $version   String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param  boolean  $in_footer Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
	 */
	public static function register_script( $handle, $path, $deps = array( 'jquery' ), $version = NORSANI_VERSION, $in_footer = true ) {
		self::$scripts[] = $handle;
		wp_register_script( $handle, $path, $deps, $version, $in_footer );
	}

	/**
	 * Register and enqueue a script for use.
	 *
	 * @uses   wp_enqueue_script()
	 * @param  string   $handle    Name of the script. Should be unique.
	 * @param  string   $path      Full URL of the script, or path of the script relative to the WordPress root directory.
	 * @param  string[] $deps      An array of registered script handles this script depends on.
	 * @param  string   $version   String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param  boolean  $in_footer Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
	 */
	public static function enqueue_script( $handle, $path = '', $deps = array( 'jquery' ), $version = NORSANI_VERSION, $in_footer = true ) {
		if ( ! in_array( $handle, self::$scripts, true ) && $path ) {
			self::register_script( $handle, $path, $deps, $version, $in_footer );
		}
		wp_enqueue_script( $handle );
	}

	/**
	 * Register a style for use.
	 *
	 * @uses   wp_register_style()
	 * @param  string   $handle  Name of the stylesheet. Should be unique.
	 * @param  string   $path    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
	 * @param  string[] $deps    An array of registered stylesheet handles this stylesheet depends on.
	 * @param  string   $version String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param  string   $media   The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
	 * @param  boolean  $has_rtl If has RTL version to load too.
	 */
	public static function register_style( $handle, $path, $deps = array(), $version = NORSANI_VERSION, $media = 'all', $has_rtl = false ) {
		self::$styles[] = $handle;
		wp_register_style( $handle, $path, $deps, $version, $media );

		if ( $has_rtl ) {
			wp_style_add_data( $handle, 'rtl', 'replace' );
		}
	}

	/**
	 * Register and enqueue a styles for use.
	 *
	 * @uses   wp_enqueue_style()
	 * @param  string   $handle  Name of the stylesheet. Should be unique.
	 * @param  string   $path    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
	 * @param  string[] $deps    An array of registered stylesheet handles this stylesheet depends on.
	 * @param  string   $version String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param  string   $media   The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
	 * @param  boolean  $has_rtl If has RTL version to load too.
	 */
	public static function enqueue_style( $handle, $path = '', $deps = array(), $version = NORSANI_VERSION, $media = 'all', $has_rtl = false ) {
		if ( ! in_array( $handle, self::$styles, true ) && $path ) {
			self::register_style( $handle, $path, $deps, $version, $media, $has_rtl );
		}
		wp_enqueue_style( $handle );
	}

	/**
	 * Register Norsani frontend scripts.
	 */
	public static function register_scripts() {
		$register_scripts = apply_filters('frozr_norsani_register_scripts_array',array(
			'norsani-scripts'	=> array(
				'src'     => self::get_asset_url( 'js/script.js' ),
				'deps'    => array( 'jquery' ),
				'version' => NORSANI_VERSION,
			),
			'norsani-add-to-cart-variation'	=> array(
				'src'     => self::get_asset_url( 'js/add-to-cart-variation.js' ),
				'deps'    => array( 'jquery' ),
				'version' => NORSANI_VERSION,
			),
			'serializejson'	=> array(
				'src'     => self::get_asset_url( 'js/jquery.serializejson.min.js' ),
				'deps'    => array( 'jquery' ),
				'version' => '2.9.0',
			),
			'tagator'	=> array(
				'src'     => self::get_asset_url( 'js/tagator.js' ),
				'deps'    => array( 'jquery' ),
				'version' => '1.2',
			),
			'norsani-dashboard-script'	=> array(
				'src'     => self::get_asset_url( 'js/dashboard-scripts.js' ),
				'deps'    => array( 'jquery' ),
				'version' => NORSANI_VERSION,
			),
		));
		
		foreach ( $register_scripts as $name => $props ) {
			self::register_script( $name, $props['src'], $props['deps'], $props['version'] );
		}
	}

	/**
	 * Register Norsani admin scripts.
	 */
	public static function register_admin_scripts() {
		$register_scripts = apply_filters('frozr_norsani_register_admin_scripts_array',array(
			'norsani-admin-script'	=> array(
				'src'     => self::get_asset_url( 'js/admin.js' ),
				'deps'    => array( 'jquery' ),
				'version' => NORSANI_VERSION,
			),
			'norsani-general-admin-script'	=> array(
				'src'     => self::get_asset_url( 'js/general_frozr_admin.js' ),
				'deps'    => array( 'jquery' ),
				'version' => NORSANI_VERSION,
			),
			'serializejson'	=> array(
				'src'     => self::get_asset_url( 'js/jquery.serializejson.min.js' ),
				'deps'    => array( 'jquery' ),
				'version' => '2.9.0',
			),
			'tagator'	=> array(
				'src'     => self::get_asset_url( 'js/tagator.js' ),
				'deps'    => array( 'jquery' ),
				'version' => '1.2',
			),
		));
		
		foreach ( $register_scripts as $name => $props ) {
			self::register_script( $name, $props['src'], $props['deps'], $props['version'] );
		}
	}

	/**
	 * Register Norsani frontend styles.
	 */
	public static function register_styles() {
		$register_styles = apply_filters('frozr_norsani_register_styles_array',array(
			'norsani-tagator'	=> array(
				'src'     => self::get_asset_url( 'css/tagator.css' ),
				'deps'    => array(),
				'version' => NORSANI_VERSION,
				'media'   => 'all',
				'has_rtl' => false,
			),
		));
		foreach ( $register_styles as $name => $props ) {
			self::register_style( $name, $props['src'], $props['deps'], $props['version'], 'all', $props['has_rtl'] );
		}
	}
	
	/**
	 * Register Norsani admin styles.
	 */
	public static function register_admin_styles() {
		$register_styles = apply_filters('frozr_norsani_register_admin_styles_array',array(
			'norsani-admin-styles'	=> array(
				'src'     => self::get_asset_url( 'css/admin.css' ),
				'deps'    => array(),
				'version' => WC_VERSION,
				'media'   => 'all',
				'has_rtl' => false,
			),
			'norsani-tagator'	=> array(
				'src'     => self::get_asset_url( 'css/tagator.css' ),
				'deps'    => array(),
				'version' => NORSANI_VERSION,
				'media'   => 'all',
				'has_rtl' => false,
			),
		));
		foreach ( $register_styles as $name => $props ) {
			self::register_style( $name, $props['src'], $props['deps'], $props['version'], 'all', $props['has_rtl'] );
		}
	}

	/**
	 * Register/queue frontend scripts.
	 */
	public static function load_scripts() {

		$option = get_option( 'frozr_gen_settings' );
		$geo_key = (! empty( $option['frozr_lazy_google_key']) ) ? $option['frozr_lazy_google_key'] : '';
		$default_contry = frozr_get_default_country();
		$default_language = get_option('WPLANG') ? get_option('WPLANG') : 'en';

		if ( ! did_action( 'before_norsani_init' ) ) {
			return;
		}

		self::register_scripts();
		self::register_styles();
		
		/*Global Scripts*/
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		self::enqueue_script( 'serializejson' );
		self::enqueue_script( 'tagator' );
		self::enqueue_script( 'norsani-scripts' );
		self::enqueue_script( 'norsani-add-to-cart-variation' );
		
		/*Vendor Scripts*/
		if ( user_can( get_current_user_id(), 'frozer' ) || frozr_is_seller_enabled(get_current_user_id()) || is_super_admin() ) {
			wp_enqueue_media();
			wp_enqueue_script( 'upload' );
			wp_enqueue_script( 'post' );
			self::enqueue_script( 'norsani-dashboard-script' );
			do_action('loaded_dashboard_scripts');
		}
		
		self::enqueue_style( 'norsani-tagator' );

		/*Google APIs*/
		if ($geo_key) {
			wp_enqueue_script( 'google-maps-places', "https://maps.googleapis.com/maps/api/js?key=$geo_key&libraries=places,geometry&language=$default_language&region=$default_contry", false, null, true );
		}
		do_action('norsani_after_general_scripts_load');
	}

	/**
	 * Register/queue admin scripts.
	 */
	public static function load_admin_scripts() {

		if ( ! did_action( 'before_norsani_init' ) ) {
			return;
		}

		self::register_admin_scripts();
		self::register_admin_styles();
		
		/*Global Scripts*/
		self::enqueue_script( 'serializejson' );
		self::enqueue_script( 'tagator' );
		self::enqueue_script( 'norsani-admin-script' );
		self::enqueue_script( 'norsani-general-admin-script' );
	
		// CSS Styles.
		self::enqueue_style( 'norsani-admin-styles' );
		self::enqueue_style( 'norsani-tagator' );
		
		do_action('norsani_after_admin_scripts_load');
	}

	/**
	 * Localize a Norsani script once.
	 *
	 * @param string $handle Script handle the data will be attached to.
	 */
	public static function localize_script( $handle ) {
		if ( ! in_array( $handle, self::$wp_localize_scripts, true ) && wp_script_is( $handle ) ) {
			$data = self::get_script_data( $handle );

			if ( ! $data ) {
				return;
			}
			$name = str_replace( '-', '_', $handle ) . '_params';
			
			if ($name == 'norsani-general-admin-script' && !is_super_admin() || $name == 'norsani-admin-script' && !is_super_admin() ) {
				return;
			}
			
			if ($name == 'norsani-dashboard-script' && !user_can( get_current_user_id(), 'frozer' ) && !frozr_is_seller_enabled(get_current_user_id()) && !is_super_admin()) {
				return;
			}
			
			self::$wp_localize_scripts[] = $handle;
			wp_localize_script( $handle, $name, apply_filters( $name, $data ) );
		}
	}

	/**
	 * Return data for script handles.
	 *
	 * @param  string $handle Script handle the data will be attached to.
	 * @return array|bool
	 */
	public static function get_script_data( $handle ) {
		
		$default_contry = frozr_get_default_country();

		switch ( $handle ) {
			case 'norsani-admin-script':
				$params = array(
					'ajax_url'									=> admin_url( 'admin-ajax.php' ),
				);
				break;
			case 'norsani-general-admin-script':
				$params = array(
					'ajax_url'									=> admin_url( 'admin-ajax.php' ),
					'frozr_default_theme_activation_nonce'		=> wp_create_nonce( 'frozr_default_theme_activation' ),
					'frozr_install_demo_data_nonce'				=> wp_create_nonce( 'frozr_install_demo_data_check' ),
					'frozr_default_theme_installation_nonce'	=> wp_create_nonce( 'frozr_default_theme_installation' ),
					'frozrhelp_user_data_nonce'					=> wp_create_nonce( 'frozrhelp_user_data_check' ),
					'frozrhelp_gen_error'						=> esc_js( __('Something is not right :(','frozr-norsani') ),
					'frozrhelp_no_messages'						=> esc_js( __('No Messages','frozr-norsani') ),
					'frozrhelp_duplicated_question'				=> esc_js( __('it looks as though you&#8217;ve already sent that question, please suggest another question.','frozr-norsani') ),
					'installing_demo_data'						=> esc_js(__('Installing demo data. This may take several minutes...','frozr-norsani')),
					'help_route'								=> !empty($_SERVER['HTTPS']) ? 'https://mahmudhamid.com/help/wp-json/frozrhelp/v1/' : 'http://help.mahmudhamid.com/wp-json/frozrhelp/v1/',
				);
				break;
			case 'norsani-scripts':
				$params = array(
					'ajax_url'								=> admin_url( 'admin-ajax.php' ),
					'frozr_contact_seller'					=> wp_create_nonce( 'frozr_contact_seller' ),
					'add_rest_review'						=> wp_create_nonce( 'add_rest_review' ),
					'rating_user_login'						=> wp_create_nonce( 'rating_user_login' ),
					'frozr_set_user_loc_nonce'				=> wp_create_nonce( 'frozr_set_user_loc' ),
					'geo_default_country'					=> $default_contry,
					'frozr_sellers_locs'					=> frozr_get_all_sellers_locations('filtered'),
					);
				break;
			case 'norsani-dashboard-script':
				$params = array(
					'ajax_url'								=> admin_url( 'admin-ajax.php' ),
					'update_wc_product_nonce'				=> wp_create_nonce( 'update_wc_product' ),
					'vendor_settings_nonce'					=> wp_create_nonce( 'frozr_settings_nonce' ),
					'frozr_save_withdraw'					=> wp_create_nonce( 'save_fro_withdraw' ),
					'delete_fro_withdraw'					=> wp_create_nonce( 'delete_fro_withdraw' ),
					'cancel_fro_withdraw'					=> wp_create_nonce( 'cancel_fro_withdraw' ),
					'set_order_status'						=> wp_create_nonce( 'set_order_status' ),
					'add_order_note'						=> wp_create_nonce( 'add-order-note' ),
					'delete_order_note_nonce'				=> wp_create_nonce( 'delete-order-note' ),
					'get_total_dash_rep'					=> wp_create_nonce( 'get_dash_totals' ),
					'frozr_delete_item_nonce'				=> wp_create_nonce( 'frozr_delete_item_nonce' ),
					'delete_item'							=> esc_js( __( 'Are you sure you want to delete this product permanently?', 'frozr-norsani' ) ),
					'withdraw_delete'						=> esc_js( __('Are you sure you want to delete this withdrawal request?','frozr-norsani')),
					'withdraw_cancel'						=> esc_js( __('Are you sure you want to cancel this Payout?','frozr-norsani')),
					'withdraw_pay'							=> esc_js( __('Are you sure you want to pay this withdrawal request?','frozr-norsani')),
					'frozr_seller_settings_nonce'			=> wp_create_nonce( 'frozr_seller_settings_nonce' ),
					'coupon_nonce_field'					=> wp_create_nonce( 'coupon_nonce_field' ),
					'coupon_del_nonce'						=> wp_create_nonce( 'coupon_del_nonce' ),
					'coupon_delete'							=> esc_js( __('Are you sure you want to delete this coupon?','frozr-norsani')),
					'frozr_rest_invitation_nonce'			=> wp_create_nonce( 'frozr_rest_invitation_nonce' ),
					'frozr_dash_print'						=> wp_create_nonce( 'frozr_dash_print' ),
					'frozr_add_variation_nonce'				=> wp_create_nonce( 'frozr_add_variation' ),
					'forzr_save_withdraw'					=> esc_js( __( 'Are you sure?', 'frozr-norsani' )),
					'woo_sep'								=> WC_DELIMITER,
					'any_attr'								=> esc_js( __( 'Any', 'frozr-norsani' )),
					'item_form_not_changed'					=> esc_js( __( 'You did not do any changes to this form', 'frozr-norsani' )),
					'item_category_not_set'					=> esc_js( __( 'Product Category field is not set!', 'frozr-norsani' )),
					'frozr_process_inst_payment_payout'		=> wp_create_nonce( 'frozr_process_inst_payment_payout' ),
					);
				break;
			default:
				$params = false;
		}

		return apply_filters( 'norsani_get_script_data', $params, $handle );
	}

	/**
	 * Localize scripts only when enqueued.
	 */
	public static function localize_printed_scripts() {
		foreach ( self::$scripts as $handle ) {
			self::localize_script( $handle );
		}
	}
}
$Norsani_scripts = new Norsani_Scripts();
return $Norsani_scripts->init();