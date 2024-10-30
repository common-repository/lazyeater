<?php
/**
 * Dashboard View: Single order page customer details
 *
 * @package Norsani/Dashboard/Order
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="or-body general-details">
	<table class="order_details_table">
		<tr>
			<td><?php _e( 'Customer:', 'frozr-norsani' ); ?></td>
			<td>
			<?php
			$customer_user = $order->get_user_id();
			if ($customer_user > 0) {
				$customer_userdata = get_user_by('id', $customer_user);
				echo $customer_userdata->display_name;
			} else {
				echo __('Guest','frozr-norsani');
			}
			?>
			</td>
		</tr>
		<tr>
			<td><?php _e( 'Email:', 'frozr-norsani' ); ?></td>
			<td><?php echo esc_html( get_post_meta( $order->get_id(), '_billing_email', true ) ); ?></td>
		</tr>
		<tr>
			<td><?php _e( 'Phone:', 'frozr-norsani' ); ?></td>
			<td><?php echo esc_html( get_post_meta( $order->get_id(), '_billing_phone', true ) ); ?></td>
		</tr>
		<tr>
			<td><?php _e( 'Address:', 'frozr-norsani' ); ?></td>
			<td><?php echo $order->get_formatted_billing_address(); do_action('frozr_order_after_customer_address',$order);?></td>
		</tr>
	</table>

	<?php
	if ( get_option( 'woocommerce_enable_order_comments' ) != 'no' ) {
		$customer_note = get_post_field( 'post_excerpt', $order->get_id() );

		if ( !empty( $customer_note ) ) { ?>
			<div class="alert alert-success customer-note">
				<strong><?php _e( 'Customer Note:', 'frozr-norsani' ) ?></strong><br>
				<?php echo wp_kses_post( $customer_note ); ?>
			</div>
		<?php } ?>
	<?php } ?>
</div>