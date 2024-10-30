<?php
/**
 * Shortcodes for Norsani
 *
 * @package Norsani
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

class Norsani_Shortcodes {
	
	/**
	 * The single instance of the class.
	 *
	 * @var Norsani_Shortcodes
	 * @since 1.9
	 */
	protected static $_instance = null;
	
	/**
	 * Main Norsani_Shortcodes Instance.
	 *
	 * @since 1.9
	 * @static
	 * @return Norsani_Shortcodes - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Norsani_Shortcodes Constructor.
	 */
	public function __construct() {
		add_shortcode( 'norsani_recommended_products', array($this,'frozr_reco_items') );

		do_action( 'norsani_shortcodes_loaded' );
	}
	
	/**
	 * Add recommended products
	 */
	 public function frozr_reco_items() {
		return '<dic class="frozr_norsani_reco_home"></div>';
	}
}
return new Norsani_Shortcodes();