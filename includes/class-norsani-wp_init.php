<?php
/**
 * WordPress int functions
 *
 * @package Norsani
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

class Norsani_WP_Init {
	
	/**
	 * The single instance of the class.
	 *
	 * @var Norsani_WP_Init
	 * @since 1.9
	 */
	protected static $_instance = null;
	
	/**
	 * Main Norsani_WP_Init Instance.
	 *
	 * @since 1.9
	 * @static
	 * @return Norsani_WP_Init - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Norsani_WP_Init Constructor.
	 */
	public function __construct() {
		add_action( 'init', array($this, 'frozr_create_ingredients'), 0 );
		add_action( 'init', array($this, 'frozr_create_delivery_location'), 0 );
		add_action( 'init', array($this, 'frozr_create_addresses_taxonomy'), 0 );
		add_action( 'init', array($this, 'frozr_create_rest_type'), 0 );
		add_action( 'init', array($this, 'frozr_items_offline_post_status'), 0 );
		add_action( 'init', array($this, 'frozr_norsani_withdraw'), 0 );
		add_action( 'init', array($this, 'frozr_custom_post_status'), 0 );

		do_action( 'norsani_wp_init_loaded' );
	}

	/**
	 * add the vendor ingredients taxonomy
	 */
	public function frozr_create_ingredients() {

		/* Add new taxonomy, NOT hierarchical (like tags)*/
		$labels = apply_filters( 'frozr_ingredents_taxonomy_labels', array(
			'name'                       => _x( 'Ingredients', 'frozr-norsani' ),
			'singular_name'              => _x( 'Ingredient', 'frozr-norsani' ),
			'search_items'               => __( 'Search Ingredients', 'frozr-norsani' ),
			'popular_items'              => __( 'Popular Ingredients', 'frozr-norsani' ),
			'all_items'                  => __( 'All Ingredients', 'frozr-norsani' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Ingredient', 'frozr-norsani' ),
			'update_item'                => __( 'Update Ingredient', 'frozr-norsani' ),
			'add_new_item'               => __( 'Add New Ingredient', 'frozr-norsani' ),
			'new_item_name'              => __( 'New Ingredient Name', 'frozr-norsani' ),
			'separate_items_with_commas' => __( 'Separate Ingredients with commas', 'frozr-norsani' ),
			'add_or_remove_items'        => __( 'Add or remove Ingredients', 'frozr-norsani' ),
			'choose_from_most_used'      => __( 'Choose from the most used Ingredients', 'frozr-norsani' ),
			'not_found'                  => __( 'No Ingredients found.', 'frozr-norsani' ),
			'menu_name'                  => __( 'Ingredients', 'frozr-norsani' ),
		));

		$args = apply_filters( 'frozr_ingredents_taxonomy_args', array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'ingredient' ),
		));

		register_taxonomy( 'ingredient', 'product', $args );
		register_taxonomy_for_object_type('ingredient', 'product');
	}
	
	/**
	 * Add the vendor delivery taxonomy
	 */
	public function frozr_create_delivery_location() {

		/* Add new taxonomy, NOT hierarchical (like tags)*/
		$labels = apply_filters( 'frozr_delivery_locations_taxonomy_labels', array(
			'name'                       => _x( 'Delivery Locations', 'frozr-norsani' ),
			'singular_name'              => _x( 'Delivery Location', 'frozr-norsani' ),
			'search_items'               => __( 'Search Locations', 'frozr-norsani'  ),
			'popular_items'              => __( 'Popular Locations', 'frozr-norsani'  ),
			'all_items'                  => __( 'All Locations', 'frozr-norsani'  ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Location', 'frozr-norsani'  ),
			'update_item'                => __( 'Update Location', 'frozr-norsani'  ),
			'add_new_item'               => __( 'Add New Location', 'frozr-norsani'  ),
			'new_item_name'              => __( 'New Location Name', 'frozr-norsani'  ),
			'separate_items_with_commas' => __( 'Separate Locations with commas', 'frozr-norsani'  ),
			'add_or_remove_items'        => __( 'Add or remove Locations', 'frozr-norsani'  ),
			'choose_from_most_used'      => __( 'Choose from the most used Locations', 'frozr-norsani'  ),
			'not_found'                  => __( 'No Locations found', 'frozr-norsani'  ),
			'menu_name'                  => __( 'Delivery Locations', 'frozr-norsani'  ),
		));

		$args = apply_filters( 'frozr_delivery_locations_taxonomy_args', array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_in_quick_edit'	=> false,
			'show_admin_column'     => false,
			'meta_box_cb'           => false,
			'update_count_callback' => '_update_generic_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'location' ),
		));

		register_taxonomy( 'location', 'user', $args );
		register_taxonomy_for_object_type('location', 'user');
	}

	/**
	 * add the vendor addresses taxonomy
	 */
	public function frozr_create_addresses_taxonomy() {

		/* Add new taxonomy, NOT hierarchical (like tags)*/
		$labels = apply_filters( 'frozr_vendor_addresses_taxonomy_labels', array(
			'name'                       => _x( 'Vendor Addresses', 'frozr-norsani' ),
			'singular_name'              => _x( 'Vendor Address', 'frozr-norsani' ),
			'search_items'               => __( 'Search Vendor Addresses', 'frozr-norsani'  ),
			'popular_items'              => __( 'Popular Addresses', 'frozr-norsani'  ),
			'all_items'                  => __( 'All Addresses of Vendors', 'frozr-norsani'  ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Address', 'frozr-norsani'  ),
			'update_item'                => __( 'Update Address', 'frozr-norsani'  ),
			'add_new_item'               => __( 'Add New Address', 'frozr-norsani'  ),
			'new_item_name'              => __( 'New Address', 'frozr-norsani'  ),
			'separate_items_with_commas' => __( 'Separate Addresses with commas', 'frozr-norsani'  ),
			'add_or_remove_items'        => __( 'Add or remove Addresses', 'frozr-norsani'  ),
			'choose_from_most_used'      => __( 'Choose from the most used Addresses', 'frozr-norsani'  ),
			'not_found'                  => __( 'No Addresses found', 'frozr-norsani'  ),
			'menu_name'                  => __( 'Vendor Addresses', 'frozr-norsani'  ),
		));

		$args = apply_filters( 'frozr_vendor_addresses_taxonomy_args', array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_in_quick_edit'	=> false,
			'show_admin_column'     => false,
			'meta_box_cb'           => false,
			'update_count_callback' => '_update_generic_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'vendor_addresses' ),
		));

		register_taxonomy( 'vendor_addresses', 'user', $args );
		register_taxonomy_for_object_type('vendor_addresses', 'user');
	}

	/**
	 * add the vendor type taxonomy
	 */
	public function frozr_create_rest_type() {

		/* Add new taxonomy, NOT hierarchical (like tags)*/
		$labels = apply_filters( 'frozr_vendor_types_taxonomy_labels', array(
			'name'                       => _x( 'Vendor Tags', 'frozr-norsani'  ),
			'singular_name'              => _x( 'Vendor Tag', 'frozr-norsani'  ),
			'search_items'               => __( 'Vendor Tags', 'frozr-norsani'  ),
			'popular_items'              => __( 'Popular Vendor Tags', 'frozr-norsani'  ),
			'all_items'                  => __( 'All Tags', 'frozr-norsani'  ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Tag', 'frozr-norsani'  ),
			'update_item'                => __( 'Update Tag', 'frozr-norsani'  ),
			'add_new_item'               => __( 'Add New Tag', 'frozr-norsani'  ),
			'new_item_name'              => __( 'New Tag Name', 'frozr-norsani'  ),
			'separate_items_with_commas' => __( 'Separate Tags with commas', 'frozr-norsani' ),
			'add_or_remove_items'        => __( 'Add or remove Tags', 'frozr-norsani'  ),
			'choose_from_most_used'      => __( 'Choose from the most used Tags', 'frozr-norsani'  ),
			'not_found'                  => __( 'No vendor Tag found.', 'frozr-norsani'  ),
			'menu_name'                  => __( 'Vendor Tags', 'frozr-norsani'  ),
		));

		$args = apply_filters( 'frozr_vendor_types_taxonomy_args', array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_in_quick_edit'	=> false,
			'show_admin_column'     => false,
			'meta_box_cb'           => false,
			'update_count_callback' => '_update_generic_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'vendorclass' ),
		));

		register_taxonomy( 'vendorclass', 'user', $args );
		register_taxonomy_for_object_type('vendorclass', 'user');
	}

	/**
	 * Add offline product (post) status
	 */
	public function frozr_items_offline_post_status() {
		register_post_status( 'offline', array(
			'label'                     => _x( 'Offline', 'post' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Offline (%s)', 'Offline (%s)', 'frozr-norsani' ),
		) );
	}

	/**
	 * Register withdraw requests post type
	 */
	public function frozr_norsani_withdraw() {

		$rewrite = array(
			'slug'                  => 'withdrawals',
			'with_front'            => true,
			'pages'                 => true,
			'feeds'                 => false,
		);
		$args = array(
			'label'                 => __( 'Withdraw', 'frozr-norsani' ),
			'description'           => __( 'Withdrawal Requests Post Types', 'frozr-norsani' ),
			'supports'              => array(),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => false,
			'show_in_menu'          => false,
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => 'withdraws_archives',
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'rewrite'               => $rewrite,
			'capability_type'       => 'post',
		);
		register_post_type( 'frozr_withdraw', apply_filters( 'frozr_users_withdraw_post_type',$args ));

	}
	
	/**
	 * Register withdraw "completed" status
	 */
	public function frozr_custom_post_status() {

		$args = array(
			'label'                     => _x( 'Completed', 'Status General Name', 'frozr-norsani' ),
			'label_count'               => _n_noop( 'Completed (%s)',  'Completed (%s)', 'frozr-norsani' ), 
			'public'                    => true,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => false,
			'exclude_from_search'       => true,
		);
		register_post_status( 'completed', apply_filters( 'frozr_withdraw_custom_post_status',$args ));

	}
}
return new Norsani_WP_Init();