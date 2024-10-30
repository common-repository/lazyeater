<?php
/**
 * Norsani setup
 *
 * @package Norsani
 * @since   1.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main Norsani Class.
 *
 * @class Frozr_Norsani
 */
final class Frozr_Norsani {

	/**
	 * Norsani version.
	 *
	 * @var string
	 */
	public $version = '1.9.0';

	/**
	 * The single instance of the class.
	 *
	 * @var Frozr_Norsani
	 * @since 1.9
	 */
	protected static $_instance = null;

	/**
	 * The array of theme page templates that the plugin tracks.
	 *
	 * @var array
	 */
	protected $templates = array();

	/**
	 * Vendors instance.
	 *
	 * @var Norsani_Vendor
	 */
	public $vendor = null;

	/**
	 * Orders instance.
	 *
	 * @var Norsani_Order
	 */
	public $order = null;

	/**
	 * Coupons instance.
	 *
	 * @var Norsani_Coupon
	 */
	public $coupon = null;

	/**
	 * Items instance.
	 *
	 * @var Norsani_Item
	 */
	public $item = null;
	
	/**
	 * Admin class instance.
	 *
	 * @var Norsani_Admin
	 */
	public $admin = null;
	
	/**
	 * Dashboard class instance.
	 *
	 * @var Norsani_Dashboard
	 */
	public $dashboard = null;
	
	/**
	 * Norsani fields class instance.
	 *
	 * @var Norsani_Fields
	 */
	public $fields = null;
	
	/**
	 * Norsani Sellers class instance.
	 *
	 * @var Norsani_Sellers
	 */
	public $sellers = null;
	
	/**
	 * Norsani Withdraw class instance.
	 *
	 * @var Norsani_Withdraw
	 */
	public $withdraw = null;

	/**
	 * Main Norsani Instance.
	 *
	 * Ensures only one instance of Norsani is loaded or can be loaded.
	 *
	 * @since 1.9
	 * @static
	 * @see norsani()
	 * @return Frozr_Norsani - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.9
	 */
	public function __clone() {
		norsani_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'frozr-norsani' ), '1.9' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.9
	 */
	public function __wakeup() {
		norsani_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'frozr-norsani' ), '1.9' );
	}

	/**
	 * Norsani Constructor.
	 *
	 * @since 1.9
	 */
	public function __construct() {
		$this->includes();
		$this->init_hooks();

		do_action( 'norsani_loaded' );
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.9
	 */
	public function init_hooks() {		
		add_action( 'init', array($this, 'init' ), 0 );
		
		/*filters*/
		add_filter('posts_where', array($this, 'hide_others_uploads') );
		add_filter('wp_insert_post_data', array($this, 'register_project_templates' ) );
		add_filter('template_include', array($this, 'view_project_template') );
		add_filter('theme_page_templates', array($this, 'add_new_template' ));
	}

	/**
	 * Run the Norsani Install class.
	 *
	 * @return void
	 */
	public static function activate() {

		require_once NORSANI_ABSPATH . 'installer.php';
		
		$install = new Norsani_Install();
		$install->install();
		
		if ( get_site_option( 'frozr_distance_tbl_version' ) != NORSANI_DB_VERSION ) {
			$install->frozr_distances_table_ins();
		}
		
		update_option('frozr_do_active_redirect', 1);
	}
	
	/**
	 * Run the Norsani Uninstall class.
	 *
	 * @return void
	 */
	public static function deactivate() {
		
		require_once NORSANI_ABSPATH . 'uninstall.php';
	
		$uninstall = new Frozr_Uninstall();
		$uninstall->uninstall();
	}
	
	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	public function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! defined( 'REST_REQUEST' );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {

		include_once NORSANI_ABSPATH . 'Mobile_Detect.php';
		include_once NORSANI_ABSPATH . 'norsani-options.php';
		include_once NORSANI_ABSPATH . 'includes/main-functions.php';
		include_once NORSANI_ABSPATH . 'includes/print-functions.php';
		
		/*Classes*/
		include_once NORSANI_ABSPATH . 'includes/class-norsani-wp_init.php';
		include_once NORSANI_ABSPATH . 'includes/class-norsani-scripts.php';
		include_once NORSANI_ABSPATH . 'includes/class-norsani-rewrites.php';
		include_once NORSANI_ABSPATH . 'includes/class-norsani-vendor.php';
		include_once NORSANI_ABSPATH . 'includes/class-norsani-item.php';
		include_once NORSANI_ABSPATH . 'includes/class-norsani-coupon.php';
		include_once NORSANI_ABSPATH . 'includes/class-norsani-withdraw.php';
		include_once NORSANI_ABSPATH . 'includes/class-norsani-order.php';
		include_once NORSANI_ABSPATH . 'includes/class-norsani-dashboard.php';
		include_once NORSANI_ABSPATH . 'includes/class-norsani-sellers.php';
		include_once NORSANI_ABSPATH . 'includes/class-norsani-fields.php';
		include_once NORSANI_ABSPATH . 'includes/class-norsani-emails.php';
		include_once NORSANI_ABSPATH . 'includes/class-norsani-shortcodes.php';

		if ( $this->is_request( 'ajax' ) ) {
		include_once NORSANI_ABSPATH . 'includes/class-norsani-ajax.php';
		}
		
		if ( $this->is_request( 'admin' ) ) {
			include_once NORSANI_ABSPATH . 'includes/class-norsani-admin.php';
		}
	}

	/**
	 * Init Norsani when WordPress Initialises.
	 */
	public function init() {
		/*Before init action*/
		do_action( 'before_norsani_init' );

		/*Templates*/
		$this->templates = array(
			'vendors.php' => 'Vendors list',
			'vendors-registration.php' => 'Vendor Registration Form',
			'sellers.php' => 'Admin dashboard sellers list',
		);
		
		/*Set up localisation*/
		$this->load_plugin_textdomain();

		/*Load class*/
		$this->item				= new Norsani_Item();
		$this->vendor			= new Norsani_Vendor();
		$this->order			= new Norsani_Order();
		$this->coupon			= new Norsani_Coupon();
		$this->fields			= new Norsani_Fields();
		$this->withdraw			= new Norsani_Withdraw();					

		if ( $this->is_request( 'admin' ) ) {
			$this->admin		= new Norsani_Admin();
		}

		if ( user_can( get_current_user_id(), 'frozer' ) || frozr_is_seller_enabled(get_current_user_id()) || is_super_admin() ) {
			$this->dashboard	= new Norsani_Dashboard();					
		}
		
		if ( is_super_admin() ) {
			$this->sellers		= new Norsani_Sellers();					
		}

		/*Init action*/
		do_action( 'frozr_norsani_init' );
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 */
	public function load_plugin_textdomain() {
		$locale = $this->is_request( 'admin' ) && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'frozr-norsani' );

		unload_textdomain( 'frozr-norsani' );
		load_textdomain( 'frozr-norsani', NORSANI_ABSPATH . 'languages/frozr-norsani-' . $locale . '.mo' );
		load_plugin_textdomain( 'frozr-norsani', false, plugin_basename( dirname( NORSANI_FILE ) ) . '/languages' );
	}
	
	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( NORSANI_FILE ) );
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function template_path() {
		return apply_filters( 'frozr_template_path', 'frozr-norsani/' );
	}

	/**
	 * Adds our template to the page dropdown for v4.7+
	 *
	 */
	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}
	
	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doesn't really exist.
	 * Thanks to Harri Bell-Thomas - http://hbt.io/
	 */
	public function register_project_templates( $atts ) {
		/* Create the key used for the themes cache*/
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );
		/* Retrieve the cache list. */
		/* If it doesn't exist, or it's empty prepare an array*/
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		} 
		/* New cache, therefore remove the old one*/
		wp_cache_delete( $cache_key , 'themes');
		/* Now add our template to the list of templates by merging our templates*/
		/* with the existing templates array from the cache.*/
		$templates = array_merge( $templates, $this->templates );
		/* Add the modified cache to allow WordPress to pick it up for listing*/
		/* available templates*/
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );
		return $atts;
	}
	
	/**
	 * Checks if the template is assigned to the page
	 */
	public function view_project_template( $template ) {
		
		/* Get global post*/
		global $post;
		/* Return template if post is empty*/
		if ( ! $post ) {
			return $template;
		}
		/* Return default template if we don't have a custom one defined*/
		if ( !isset( $this->templates[get_post_meta( 
			$post->ID, '_wp_page_template', true 
		)] ) ) {
			return $template;
		} 
		$file = NORSANI_TMP .get_post_meta( 
			$post->ID, '_wp_page_template', true
		);
		/* Just to be safe, we check if the file exist first*/
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}
		/* Return template*/
		return $template;
	}
	
	/**
	 * Hide other users uploads for `seller` users
	 *
	 * Hide media uploads in page "upload.php" and "media-upload.php" for
	 * sellers. They can see only their uploads.
	 *
	 * @param string $where
	 * @return string
	 */
	function hide_others_uploads( $where ) {
		global $pagenow, $wpdb;

		if ( ( $pagenow == 'upload.php' || $pagenow == 'media-upload.php') && current_user_can( 'frozer' ) ) {
			$user_id = get_current_user_id();

			$where .= " AND $wpdb->posts.post_author = $user_id";
		}

		return $where;
	}
}
register_activation_hook( NORSANI_FILE, array( 'Frozr_Norsani', 'activate' ) );
register_deactivation_hook( NORSANI_FILE, array( 'Frozr_Norsani', 'deactivate' ) );
