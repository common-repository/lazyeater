<?php
/**
 * Dashboard View: Orders page list table
 *
 * @package Norsani/Dashboard/Order
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action('frozr_before_orders_table'); 

?>
<table <?php echo apply_filters('frozr_orders_table_atts', $orders_table_atts); ?> class="table_orders_list ui-responsive table-stroke dash_tables">
<thead>
	<tr class="table_collumns">
		<th data-priority="1" class="frozr_dash_order_number_header"><?php _e( 'Order', 'frozr-norsani' ); ?></th>
		<th data-priority="4" class="hide_on_mobile"><?php _e( 'Total', 'frozr-norsani' ); ?></th>
		<?php if ($order_status == 'processing') { ?>
		<th data-priority="2"><?php _e( 'Status', 'frozr-norsani' ); ?></th>
		<?php } ?>
		<th data-priority="7"><?php _e( 'Customer', 'frozr-norsani' ); ?></th>
		<th data-priority="6" class="hide_on_mobile"><?php _e( 'Date', 'frozr-norsani' ); ?></th>
		<th data-priority="3" class="dash_tables_actions hide_on_mobile"><?php _e( 'Update', 'frozr-norsani' ); ?></th>
		<?php if (is_super_admin()) { ?>
		<th data-priority="8"><?php _e( 'Vendor', 'frozr-norsani' ); ?></th>
		<?php } ?>
		<?php do_action('frozr_after_dashboard_orders_table_header', $order_status); ?>
	</tr>
</thead>
<tbody class="orders_lists" data-ods="<?php echo $order_status; ?>">
	<?php norsani()->order->frozr_orders_lists($order_status); ?>
</tbody>
</table>
<?php do_action('frozr_after_orders_table'); ?>