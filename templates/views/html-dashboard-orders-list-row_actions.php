<?php
/**
 * Dashboard View: Orders page list table single row actions
 *
 * @package Norsani/Dashboard/Order
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="frozr_ord_act_icon"><i class="material-icons">update</i></div>
<div class="order_satus_change frozr_hide">
	<?php if ($the_order->get_status() == 'on-hold' || $the_order->get_status() == 'pending'|| $the_order->get_status() == 'failed') { ?>
	<a class="order_status_butn processing" data-status="processing" data-orderid="<?php echo $order_id; ?>" href="#" title="<?php _e( 'Processing', 'frozr-norsani' ); ?>"><i class="fa-motorcycle">&nbsp;</i><?php _e( 'Processing', 'frozr-norsani' ); ?></a>
	<?php } if ($the_order->get_status() == 'on-hold' || $the_order->get_status() == 'pending' || $the_order->get_status() == 'processing' || $the_order->get_status() == 'failed') { ?>
	<a class="order_status_butn complete" data-status="completed" data-orderid="<?php echo $order_id; ?>" href="#" title="<?php _e( 'Complete', 'frozr-norsani' ); ?>"><i class="fa-check">&nbsp;</i><?php _e( 'Complete', 'frozr-norsani' ); ?></a>
	<a class="order_status_butn cancelled" data-status="cancelled" data-orderid="<?php echo $order_id; ?>" href="#" title="<?php _e( 'Cancelled', 'frozr-norsani' ); ?>"><i class="fa-times-circle">&nbsp;</i><?php _e( 'Cancelled', 'frozr-norsani' ); ?></a>
	<?php } if ($the_order->get_status() == 'on-hold' || $the_order->get_status() == 'pending' || $the_order->get_status() == 'processing' || $the_order->get_status() == 'cancelled' || $the_order->get_status() == 'failed') { ?>
	<a class="order_status_butn refunded" data-status="refunded" data-orderid="<?php echo $order_id; ?>" href="#" title="<?php _e( 'Refunded', 'frozr-norsani' ); ?>"><i class="fa-mail-reply-all">&nbsp;</i><?php _e( 'Refunded', 'frozr-norsani' ); ?></a>
	<?php } if ($the_order->get_status() == 'on-hold' || $the_order->get_status() == 'processing' || $the_order->get_status() == 'failed') { ?>
	<a class="order_status_butn pending" data-status="pending" data-orderid="<?php echo $order_id; ?>" href="#" title="<?php _e( 'Pending', 'frozr-norsani' ); ?>"><i class="fa-cutlery">&nbsp;</i><?php _e( 'Pending', 'frozr-norsani' ); ?></a>
	<?php } if ($the_order->get_status() != 'completed' && $the_order->get_status() != 'refunded') { ?>
	<a class="order_status_butn failed" data-status="failed" data-orderid="<?php echo $order_id; ?>" href="#" title="<?php _e( 'Failed', 'frozr-norsani' ); ?>"><i class="fa-minus-circle">&nbsp;</i><?php _e( 'Failed', 'frozr-norsani' ); ?></a>
	<?php } ?>
	<a class="order_print_butn" data-orderid="<?php echo $order_id; ?>" href="#" title="<?php _e( 'Print', 'frozr-norsani' ); ?>"><i class="fa-print">&nbsp;</i><?php _e( 'Print', 'frozr-norsani' ); ?></a>
	<?php do_action('frozr_orders_list_after_action', $the_order); ?>
</div>