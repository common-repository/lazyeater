<?php
/**
 * Dashboard - Orders
 *
 * @package Norsani/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

frozr_redirect_login();
frozr_redirect_if_not_seller();

$order_id = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;
$orders = get_post($order_id);

if ($order_id && isset($_GET['print']) && $_GET['print'] == 'order') {

	$order = wc_get_order( $order_id );

	frozr_print_order_template($order);

} elseif ( $order_id > 0 && get_current_user_id() != frozr_get_order_author($order_id) && !is_super_admin()) {
	
	wp_redirect( home_url( '/' ) );

} elseif (! isset($_GET['order_status']) && ! isset($_GET['order_id'])) {

	wp_redirect(add_query_arg( array( 'order_status' => 'processing' ), home_url( '/dashboard/orders/') ));

} else {

/*Get Header*/
get_header();

/*Dashboard Action Hook*/
do_action('norsani_dashboard_orders_page', $order_id);

/* calling footer.php*/
get_footer();

}