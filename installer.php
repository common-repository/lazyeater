<?php
/**
 * Frozr installer class
 *
 * @package Norsani
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

class Norsani_Install {

	public function install() {

		/*check woocommerce*/
		self::check_woocommerce();
		
		/*check default theme*/
		self::check_theme();
		
		/*check envato plugin*/
		self::check_envato();
		
		/*install distance table*/
		self::frozr_distances_table_ins();

		/*Norsani installing*/
		self::user_roles();
		self::frozr_create_pages();
		
		/*modify WooCommerce settings*/
		self::woocommerce_settings();
		
		do_action('frozr_norsani_installed');
	}
	public function frozr_distances_table_ins() {

		/* installs*/
        $this->frozr_distances_table();
		
		do_action('frozr_norsani_distance_table_installed');
	}

	private function woocommerce_settings() {
		$selling_contry = get_option('woocommerce_default_country');
		
		update_option( 'woocommerce_enable_myaccount_registration', 'yes' );
		update_option( 'woocommerce_ship_to_countries', 'disabled' );
		update_option( 'woocommerce_ship_to_destination', 'billing_only' );
		update_option( 'woocommerce_allowed_countries', 'specific' );
		update_option( 'woocommerce_specific_allowed_countries', array($selling_contry) );
		
		$cart_page = get_page_by_title('Cart');
		$checkout_page = get_page_by_title('Checkout');
		if ($cart_page) {
			update_option('woocommerce_cart_page_id',$cart_page->ID);
		}
		if ($checkout_page) {
			update_option('woocommerce_checkout_page_id',$checkout_page->ID);
		}
	}

	/**
	* Init frozr user roles
	*
	* @global WP_Roles $wp_roles
	*/
	private function user_roles() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && !isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		add_role( 'seller', __( 'Seller', 'frozr-norsani' ), apply_filters('frozr_add_seller_role', array(
			'read' => true,
			'publish_posts' => false,
			'edit_posts' => false,
			'delete_published_posts' => false,
			'edit_published_posts' => false,
			'delete_posts' => false,
			'manage_categories' => false,
			'moderate_comments' => false,
			'upload_files' => true,
			'frozer' => true,
		)));

		$wp_roles->add_cap( 'shop_manager', 'frozer' );
		$wp_roles->add_cap( 'administrator', 'frozer' );
    }
	/**
	 * Create places distance table
	 * This is a Pro Feature
	 */
	private function frozr_distances_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'forzr_distances';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			originAddresses LONGTEXT NOT NULL,
			destinationAddresses LONGTEXT NOT NULL,
			distance LONGTEXT NOT NULL,
			duration LONGTEXT NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		add_option( 'frozr_distance_tbl_version', NORSANI_DB_VERSION );
	}
	private function check_theme() {
		$url = site_url() . '/wp-admin/';
		
		ob_start();
			
		if (false == $creds = request_filesystem_credentials($url, '', false, false, null)) {
			$credit_form = ob_get_clean();
			return;
		}

		if (!WP_Filesystem($creds)) {
			return;
		}

		global $wp_filesystem;
		$themes_path = $wp_filesystem->wp_themes_dir();
		
		$theme_exisit = wp_get_theme('frozrdash');
		$child_theme_exisit = wp_get_theme('frozrdash-child');
		$child_theme_file = NORSANI_PATH . '/assets/theme/frozrdash-child.zip'; 
		$theme_file = NORSANI_PATH . '/assets/theme/frozrdash.zip';
		$parent_theme = $child_theme = true;
		
		if (!$theme_exisit->exists()) {
		$parent_theme = unzip_file($theme_file, $themes_path); 
		} else {
			$get_parent_theme_version = $theme_exisit->get( 'Version' );
			if ($get_parent_theme_version != FROZRDASH_VERSION) {
				if (delete_theme('frozrdash')) {
				$parent_theme = unzip_file($theme_file, $themes_path);	
				}
			}
		}
		if (!$child_theme_exisit->exists()) {
		$child_theme = unzip_file($child_theme_file, $themes_path); 
		}
		
		if ($parent_theme && $child_theme) {
			update_option('frozr_themes_installed',1);
		}
		return;
	}
	private function check_woocommerce() {
		$url = site_url() . '/wp-admin/';
		$woo_file = 'woocommerce/woocommerce.php';
		$plugin_file = WP_PLUGIN_DIR .'/'. $woo_file;
		$dir = dirname( $plugin_file );

		if (is_plugin_active($woo_file)) {
			return;
		}

		if (is_dir( $dir ) && WP_PLUGIN_DIR !== $dir) {
			$active_woo_plugin = activate_plugin($woo_file);
			if (is_wp_error($active_woo_plugin)) {
				deactivate_plugins(NORSANI_FILE);
				$error = new WP_Error( 'Frozr_woo_fail', __( "Norsani highly depends on WooCommrce plugin which could not be automatically activated. Please activate WooCommerce manually and try again.", 'frozr-norsani' ) );
				wp_die($error,__('Norsani Install Error','frozr-norsani'),array( 'back_link' => true ));
			}
			return;
		}
		
		ob_start();
		
		if (false === ($creds = request_filesystem_credentials($url, '', false, false, null) ) || ! WP_Filesystem($creds) ) {
			$credit_form = ob_get_clean();
			deactivate_plugins(NORSANI_FILE);
			$error = new WP_Error( 'Frozr_woo_fail', __( "Norsani highly depends on the 'WooCommrce' plugin which we couldn't automatically install and activate. Please install and activate 'WooCommerce' manually and try again.", 'frozr-norsani' ) );
			wp_die($error,__('Norsani Install Error','frozr-norsani'),array( 'back_link' => true ));
		}
		
		wp_clean_plugins_cache();
		global $wp_filesystem;
		$plugins_path = $wp_filesystem->wp_plugins_dir();
		$themes_path = $wp_filesystem->wp_themes_dir();
		
		$plugin_file = NORSANI_PATH . '/assets/plugins/woocommerce.zip'; 
		$result = unzip_file($plugin_file, $plugins_path); 

		if($result != true){
			deactivate_plugins(NORSANI_FILE);
			$error = new WP_Error( 'Frozr_woo_fail', __( "Norsani highly depends on WooCommrce plugin which could not be automatically installed and activated. Please install and actiavte WooCommerce manually and try again.", 'frozr-norsani' ) );
			wp_die($error,__('Norsani Install Error','frozr-norsani'),array( 'back_link' => true ));
		}
		
		$active_woo_plugin = activate_plugin($woo_file);
		if (is_wp_error($active_woo_plugin)) {
			deactivate_plugins(NORSANI_FILE);
			$error = new WP_Error( 'Frozr_woo_fail', __( "Norsani highly depends on WooCommrce plugin which could not be automatically activated. Please activate WooCommerce manually and try again.", 'frozr-norsani' ) );
			wp_die($error,__('Norsani Install Error','frozr-norsani'),array( 'back_link' => true ));
		}
		return;
	}
	private function check_envato() {
		$url = site_url() . '/wp-admin/';
		$plugin_file = 'envato-market/envato-market.php';
		$plugin_file = WP_PLUGIN_DIR .'/'. $plugin_file;
		$dir = dirname( $plugin_file );

		if (is_plugin_active($plugin_file)) {
			return;
		}
		
		if (is_dir( $dir ) && WP_PLUGIN_DIR !== $dir) {
			$active_plugin = activate_plugin($plugin_file);
			return;
		}
		
		ob_start();
		
		if (false === ($creds = request_filesystem_credentials($url, '', false, false, null) ) || ! WP_Filesystem($creds) ) {
			$credit_form = ob_get_clean();
		}
		
		wp_clean_plugins_cache();
		global $wp_filesystem;
		$plugins_path = $wp_filesystem->wp_plugins_dir();
		$themes_path = $wp_filesystem->wp_themes_dir();
		
		$asset_file = NORSANI_PATH . '/assets/plugins/envato-market.zip'; 
		$result = unzip_file($asset_file, $plugins_path); 

		if($result != true){
			return;
		}
		
		$active_plugin = activate_plugin($plugin_file);

		return;
	}
	/**
	* create default pages
	*
	*/
	private function frozr_create_pages(){
		global $wp_error;

		$vendors_registration = get_option("vendors-registration");
		$vendors_is_installed = get_option("vendors");
		$sellers_is_installed = get_option("sellers");

		if (!$vendors_is_installed) {
			$vendors = array(
				'post_title' => "vendors list",
				'post_content' => __('This page is used by Norsani. You can freely change this text but please do NOT delete the page.','frozr-norsani'),
				'post_status' => "publish",
				'post_type' => 'page',
				'comment_status' => 'closed',
				'ping_status' => 'closed',
			);
			$create_rest_page = wp_insert_post($vendors, $wp_error);
			if (!is_wp_error($create_rest_page)) {
				update_post_meta( $create_rest_page, '_wp_page_template', 'vendors.php' );
				update_option("vendors", $create_rest_page);
			}
		}
		
		if (!$sellers_is_installed) {
			$sellers = array(
				'post_title' => "sellers",
				'post_content' => __('This page is used by Norsani. You can freely change this text but please do NOT delete the page.','frozr-norsani'),
				'post_status' => "publish",
				'post_type' => 'page',
				'comment_status' => 'closed',
				'ping_status' => 'closed',
			);
			$create_sellers_page = wp_insert_post($sellers, $wp_error);
			
			if (!is_wp_error($create_sellers_page)) {
				update_post_meta( $create_sellers_page, '_wp_page_template', 'sellers.php' );
				update_option("sellers", $create_sellers_page);
			}
		}
		
		if (!$vendors_registration) {
			$vendor_reg = array(
				'post_title' => "Vendor Registration Form",
				'post_content' => __('This page is used by Norsani. You can freely change this text but please do NOT delete the page.','frozr-norsani'),
				'post_status' => "publish",
				'post_type' => 'page',
				'comment_status' => 'closed',
				'ping_status' => 'closed',
			);
			$create_vendor_reg_page = wp_insert_post($vendor_reg, $wp_error);
			
			if (!is_wp_error($create_vendor_reg_page)) {
				update_post_meta( $create_vendor_reg_page, '_wp_page_template', 'vendors-registration.php' );
				update_option("vendors-registration", $create_vendor_reg_page);
			}
		}
	
		/*Create woocommerce pages*/
		WC_Install::create_pages();
	}
}