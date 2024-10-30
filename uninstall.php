<?php
/**
 * Norsani Uninstall
 *
 * @package Norsani
 */
if ( ! defined('ABSPATH') ) {
	exit();
}
class Frozr_Uninstall {


	public function uninstall() {

		/* uninstalls*/
		$this->remove_user_roles();
		
		do_action('frozr_norsani_uninstalled');
		
		flush_rewrite_rules();

	}
	/**
	* Init frozr user roles
	*
	* @global WP_Roles $wp_roles
	*/
    public function remove_user_roles() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && !isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		/*check if role exist before removing it*/
		if( get_role('seller') ){
			remove_role( 'seller' );
		}
		
		$wp_roles->remove_cap( 'shop_manager', 'frozer' );
		$wp_roles->remove_cap( 'administrator', 'frozer' );
	}
}