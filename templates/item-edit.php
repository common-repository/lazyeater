<?php
/**
 * Products - Edit
 *
 * @package Norsani/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

frozr_redirect_login();
frozr_redirect_if_not_seller();
frozr_redirect_if_admin();

$post_id = apply_filters('frozr_edit_product_id',isset( $_GET['product_id'] ) ? intval( $_GET['product_id'] ) : 0, $_GET['product_id']);
$current_post = get_post($post_id);
$new_post = $post_id > 0 ? false : true;

if ($post_id > 0 && get_current_user_id() != $current_post->post_author) {
	
	wp_redirect( home_url( '/' ) );

} else {

do_action('frozr_before_dash_home_header');

/*Get Header*/
get_header();

do_action('frozr_after_dash_home_header');

/*Dashboard item edit Action Hook*/
do_action('norsani_dashboard_item_edit_page', $post_id, $new_post);

/* calling footer.php*/
get_footer();

}