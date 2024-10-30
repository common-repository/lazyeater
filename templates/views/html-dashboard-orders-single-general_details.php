<?php
/**
 * Dashboard View: Single order page general details
 *
 * @package Norsani/Dashboard/Order
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$get_post = get_post($order->get_id());
?>
<div class="or-body general-details">
	<table class="order_details_table">
		<tr>
			<td><?php _e( 'Order Status:', 'frozr-norsani' ); ?></td>
			<td class="status-label"><?php echo $order->get_status(); ?></td>
		</tr>
		<tr>
			<td><?php _e( 'Payment Method:', 'frozr-norsani' ); ?></td>
			<td><?php echo esc_html( $order->get_payment_method_title() ) . ' <span class="frozr_cod_notice">'. $cod_notice . '</span>'; ?></td>
		</tr>
		<tr>
			<td><?php _e( 'Created Date:', 'frozr-norsani' ); ?></td>
			<td><?php echo date_i18n(frozr_get_time_date_format('date_time'),strtotime($get_post->post_date)); ?></td>
		</tr>
		<tr>
			<td><?php _e( 'Website Fee:', 'frozr-norsani' ); ?></td>
			<td><?php echo $currency.$website_profit; ?></td>
		</tr>
		<tr>
			<td><?php _e( "Vendor's Net:", 'frozr-norsani' ); ?></td>
			<td><?php echo $currency.$seller_profit; ?></td>
		</tr>
	</table>
</div>