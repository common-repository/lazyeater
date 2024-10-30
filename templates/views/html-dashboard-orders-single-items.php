<?php
/**
 * Dashboard View: Single order page items list
 *
 * @package Norsani/Dashboard/Order
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<table cellpadding="0" cellspacing="0" class="woocommerce_order_items striped">
	<thead>
	<tr>
		<th class="order_item"><?php _e( 'Product', 'frozr-norsani' ); ?></th>
		<?php do_action( 'woocommerce_admin_order_item_headers', $order ); ?>
		<th class="item_cost hide_on_mobile"><?php _e( 'Cost', 'frozr-norsani' ); ?></th>
		<th class="order_quantity hide_on_mobile"><?php _e( 'Qty', 'frozr-norsani' ); ?></th>
		<th class="line_cost"><?php _e( 'Total', 'frozr-norsani' ); ?></th>
		<?php if ( empty( $legacy_order ) && ! empty( $order_taxes ) ) :
		foreach ( $order_taxes as $tax_id => $tax_item ) :
			$tax_class      = wc_get_tax_class_by_tax_id( $tax_item['rate_id'] );
			$tax_class_name = isset( $classes_options[ $tax_class ] ) ? $classes_options[ $tax_class ] : __( 'Tax', 'frozr-norsani' );
			$column_label   = ! empty( $tax_item['label'] ) ? $tax_item['label'] : __( 'Tax', 'frozr-norsani' );
			?>
			<th class="line_tax tips" data-tip="<?php
			echo esc_attr( $tax_item['name'] . ' (' . $tax_class_name . ')' );
			?>">
			<?php echo esc_attr( $column_label ); ?>
			</th>
			<?php
		endforeach;
		endif; ?>
	</tr>
	</thead>
	<tbody id="order_line_items">
		<?php foreach ( $line_items as $item_id => $item ) {
			$_product  = $order->get_product_from_item( $item );
			
			wc_get_template( 'html-order-item.php', array( 'rest' => $rest, 'item_id' => $item_id, 'item' => $item, 'show_tax_columns' => $show_tax_columns, 'legacy_order' => $legacy_order, 'check_item' => $check_item, 'tax_data' => $tax_data, 'classes_options' => $classes_options, 'tax_classes' => $tax_classes, 'order_taxes' => $order_taxes, 'line_items_fee' => $line_items_fee, 'order' => $order, '_product' => $_product ), '', NORSANI_PATH . '/templates/orders/');
			do_action( 'woocommerce_order_item_' . $item['type'] . '_html', $item_id, $item, $order );
		}
		do_action( 'woocommerce_admin_order_items_after_line_items', $order->get_id() ); ?>
	</tbody>
	<tbody id="order_fee_line_items">
		<?php
		foreach ( $line_items_fee as $item_id => $item ) {
			wc_get_template( 'html-order-fee.php', array( 'item_id' => $item_id, 'item' => $item, 'show_tax_columns' => $show_tax_columns, 'legacy_order' => $legacy_order, 'check_item' => $check_item, 'tax_data' => $tax_data, 'classes_options' => $classes_options, 'tax_classes' => $tax_classes, 'order_taxes' => $order_taxes, 'line_items_fee' => $line_items_fee, 'order' => $order, '_product' => $_product ), '', NORSANI_PATH . '/templates/orders/');
		}
		do_action( 'woocommerce_admin_order_items_after_fees', $order->get_id() );
		?>
	</tbody>
	<tbody id="order_refunds">
		<?php
		if ( $refunds = $order->get_refunds() ) {
			foreach ( $refunds as $refund ) {
			wc_get_template( 'html-order-refund.php', array( 'refund' => $refund, 'show_tax_columns' => $show_tax_columns, 'legacy_order' => $legacy_order, 'check_item' => $check_item, 'tax_data' => $tax_data, 'classes_options' => $classes_options, 'tax_classes' => $tax_classes, 'order_taxes' => $order_taxes, 'line_items_fee' => $line_items_fee, 'order' => $order, '_product' => $_product, 'item_meta' => $item_meta ), '', NORSANI_PATH . '/templates/orders/');
			}
			do_action( 'woocommerce_admin_order_items_after_refunds', $order->get_id() );
		}
		?>
	</tbody>
</table>
<?php
$coupons = $order->get_items( array( 'coupon' ) );
if ( $coupons ) { ?>
	<div class="wc-used-coupons">
		<ul class="wc_coupon_list"><?php
		echo '<li><strong>' . __( 'Coupon(s) Used', 'frozr-norsani' ) . '</strong></li>';
		foreach ( $coupons as $item_id => $item ) {
		$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish' LIMIT 1;", $item['name'] ) );

		$link = $post_id ? add_query_arg( array( 'post' => $post_id, 'action' => 'edit' ), admin_url( 'post.php' ) ) : add_query_arg( array( 's' => $item['name'], 'post_status' => 'all', 'post_type' => 'shop_coupon' ), admin_url( 'edit.php' ) );

		echo '<li class="order_code"><a href="' . esc_url( $link ) . '" class="order_tips" data-tip="' . esc_attr( wc_price( $item['discount_amount'], array( 'currency' => $order->get_currency () ) ) ) . '"><span>' . esc_html( $item['name'] ). '</span></a></li>';
		}
		?></ul>
	</div>
<?php } ?>
<table class="wc-order-totals">
	<tr>
		<td class="order_label"><?php echo __( 'Discount', 'frozr-norsani' ).':'; if (!isset( $_GET['print'] )) { frozr_inline_help_db('order_discount'); } ?></td>
		<td class="order_total">
		<?php echo wc_price( $order->get_total_discount(), array( 'currency' => $order->get_currency () ) ); ?>
		</td>
		<td width="1%"></td>
	</tr>
	<?php do_action( 'woocommerce_admin_order_totals_after_discount', $order->get_id() ); ?>
	<?php if ( wc_tax_enabled() ) : ?>
		<?php foreach ( $order->get_tax_totals() as $code => $tax ) : ?>
			<tr>
				<td class="order_label"><?php echo $tax->label; ?>:</td>
				<td class="order_total"><?php
				if ( ( $refunded = $order->get_total_tax_refunded_by_rate_id( $tax->rate_id ) ) > 0 ) {
					echo '<del>' . strip_tags( $tax->formatted_amount ) . '</del> <ins>' . wc_price( $tax->amount - $refunded, array( 'currency' => $order->get_currency () ) ) . '</ins>';
				} else {
					echo $tax->formatted_amount;
				}
				?></td>
				<td width="1%"></td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php do_action( 'woocommerce_admin_order_totals_after_tax', $order->get_id() ); ?>
	<tr>
		<td class="order_label"><?php _e( 'Order Total', 'frozr-norsani' ); ?>:</td>
		<td class="order_total">
		<div class="order_view"><?php echo $order->get_formatted_order_total(); ?></div>
		</td>
		<td width="1%"></td>
	</tr>
	<?php do_action( 'woocommerce_admin_order_totals_after_total', $order->get_id() ); ?>
	<tr>
		<td class="order_label refunded-total"><?php _e( 'Refunded', 'frozr-norsani' ); ?>:</td>
		<td class="order_total refunded-total">-<?php echo wc_price( $order->get_total_refunded(), array( 'currency' => $order->get_currency () ) ); ?></td>
		<td width="1%"></td>
	</tr>
	<?php do_action( 'woocommerce_admin_order_totals_after_refunded', $order->get_id() ); ?>
</table>