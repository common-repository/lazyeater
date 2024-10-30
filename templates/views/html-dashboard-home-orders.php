<?php
/**
 * Dashboard View: Home page orders overview
 *
 * @package Norsani/Dashboard/Home
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$orders_url = home_url( '/dashboard/orders/');
?>
<div class="dash_totals f-black">
	<span class="dash_totals_title"><i class="material-icons">work</i>&nbsp;<?php _e( 'Orders', 'frozr-norsani' ); frozr_inline_help_db('dash_home_orders'); ?></span>
	<table class="dash_top_selling_items">
	<thead>
		<tr class="table_collumns">
			<th data-priority="1"><?php _e( 'Status', 'frozr-norsani' ); ?></th>
			<th data-priority="2"><?php _e( 'Count', 'frozr-norsani' ); ?></th>
		</tr>
	</thead>
	<tbody>
	<tr>
		<td class="dast_dtit">
			<i class="material-icons">check_circle</i>
			<a href="<?php echo add_query_arg( array( 'order_status' => 'completed' ), $orders_url ); ?>" ><?php _e( 'Completed', 'frozr-norsani' ); ?></a>
		</td>
		<td class="dast_pcount">
			<?php echo frozr_count_user_object('wc-completed', 'shop_order'); ?>
		</td>
	</tr>
	<tr>
		<td class="dast_dtit">
			<i class="material-icons">queue</i>
			<a href="<?php echo add_query_arg( array( 'order_status' => 'pending' ), $orders_url ); ?>" ><?php _e( 'Pending Payment', 'frozr-norsani' ); ?></a>
		</td>
		<td class="dast_pcount">
			<?php echo frozr_count_user_object('wc-pending', 'shop_order'); ?>
		</td>
	</tr>
	<tr>
		<td class="dast_dtit">
			<i class="material-icons">shop_two</i>
			<a href="<?php echo add_query_arg( array( 'order_status' => 'processing' ), $orders_url ); ?>" ><?php _e( 'Processing', 'frozr-norsani' ); ?></a>
		</td>
		<td class="dast_pcount">
			<?php echo frozr_count_user_object('wc-processing', 'shop_order'); ?>
		</td>
	</tr>
	<tr>
		<td class="dast_dtit">
			<i class="material-icons">remove_shopping_cart</i>
			<a href="<?php echo add_query_arg( array( 'order_status' => 'cancelled' ), $orders_url ); ?>" ><?php _e( 'Cancelled', 'frozr-norsani' ); ?></a>
		</td>
		<td class="dast_pcount">
			<?php echo frozr_count_user_object('wc-cancelled', 'shop_order'); ?>
		</td>
	</tr>
	<tr>
		<td class="dast_dtit">
			<i class="material-icons">refresh</i>
			<a href="<?php echo add_query_arg( array( 'order_status' => 'refunded' ), $orders_url ); ?>" ><?php _e( 'Refunded', 'frozr-norsani' ); ?></a>
		</td>
		<td class="dast_pcount">
			<?php echo frozr_count_user_object('wc-refunded', 'shop_order'); ?>
		</td>
	</tr>
	<tr>
		<td class="dast_dtit">
			<i class="material-icons">query_builder</i>
			<a href="<?php echo add_query_arg( array( 'order_status' => 'on-hold' ), $orders_url ); ?>" ><?php _e( 'On-hold', 'frozr-norsani' ); ?></a>
		</td>
		<td class="dast_pcount">
			<?php echo frozr_count_user_object('wc-on-hold', 'shop_order'); ?>
		</td>
	</tr>
	</tbody>
	</table>
</div>