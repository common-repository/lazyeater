<?php
/*
 * Plugin Name: Norsani, The Multi-Vendor online Food Ordering System.
 * Plugin URI: http://norsani.mahmudhamid.com/
 * Description: Multi-vendor online food ordering system.
 * Version: 1.10
 * Author: Mahmud Hamid
 * Author URI: https://mahmudhamid.com
 * Text Domain: frozr-norsani
 * Domain Path: /languages/
 * Copyright: © 2009-2018 Mahmud Hamid.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 *
 * @package Norsani
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define NORSANI_FILE.
if ( ! defined( 'NORSANI_FILE' ) ) {
	define( 'NORSANI_FILE', __FILE__ );
}
if ( ! defined( 'NORSANI_ABSPATH' ) ) {
	define( 'NORSANI_ABSPATH', dirname( NORSANI_FILE ) . '/' );
}
if ( ! defined( 'NORSANI_VERSION' ) ) {
	define( 'NORSANI_VERSION', '1.10' );
}
if ( ! defined( 'FROZRDASH_VERSION' ) ) {
	define( 'FROZRDASH_VERSION', '1.13' );
}
if ( ! defined( 'NORSANI_PATH' ) ) {
	define( 'NORSANI_PATH', plugin_dir_path( NORSANI_FILE ) );
}
if ( ! defined( 'NORSANI_INC' ) ) {
	define( 'NORSANI_INC',  NORSANI_PATH .  'includes/' );
}
if ( ! defined( 'NORSANI_TMP' ) ) {
	define( 'NORSANI_TMP',  NORSANI_PATH .  'templates/' );
}
if ( ! defined( 'FROZR_REST_NAMESPACE' ) ) {
	define( 'FROZR_REST_NAMESPACE',  'norsani/v1' );
}
if ( ! defined( 'NORSANI_TEMPLATE_DEBUG_MODE' ) ) {
	define( 'NORSANI_TEMPLATE_DEBUG_MODE', false );
}
if ( ! defined( 'NORSANI_DB_VERSION' ) ) {
	define( 'NORSANI_DB_VERSION', '1.3' );
}

// Include the main Norsani class.
if ( ! class_exists( 'Frozr_Norsani' ) ) {
	include_once dirname( NORSANI_FILE ) . '/includes/class-norsani.php';
}

/**
 * Main instance of Norsani.
 *
 * Returns the main instance of Norsani to prevent the need to use globals.
 *
 * @since  1.9
 * @return Norsani
 */
function norsani() {
	return Frozr_Norsani::instance();
}

add_action('woocommerce_loaded','norsani');