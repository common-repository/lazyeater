<?php
/**
 * Shows an order item
 *
 * @var object $item The item being displayed
 * @var int $item_id The id of the item being displayed
 *
 * @package Norsani/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; /* Exit if accessed directly*/
}
?>
<div class="order_item frozr_ord_quick <?php echo apply_filters( 'woocommerce_admin_html_order_item_class', ( ! empty( $class ) ? $class : '' ), $item ); ?>" data-order_item_id="<?php echo $item_id; ?>">
<div class="order_thumb">
<?php
echo ( $_product && $_product->get_sku() ) ? esc_html( $_product->get_sku() ) . ' &ndash; ' : '';
echo esc_html( $item['name'] );
if (!$rest) {
echo '&nbsp;|&nbsp;<strong>' . __( 'Product ID:', 'frozr-norsani' ) . absint( $item['product_id'] ) . '</strong>';
}
	if ( ! empty( $item['variation_id'] ) && 'product_variation' === get_post_type( $item['variation_id'] ) ) {
		echo '&nbsp;|&nbsp;<strong>' . __( 'Variation ID:', 'frozr-norsani' ) . absint( $item['variation_id'] ) . '</strong> ';
	} elseif ( ! empty( $item['variation_id'] ) ) {
		echo '&nbsp;|&nbsp;<strong>' . __( 'Variation ID:', 'frozr-norsani' ) . absint( $item['variation_id'] ) . ' (' . __( 'No longer exists', 'frozr-norsani' ) . ')</strong> ';
	}

	if ( $_product && $_product->get_sku() ) {
		echo '&nbsp;|&nbsp;<strong>' . __( 'Product SKU:', 'frozr-norsani' ) . esc_html( $_product->get_sku() ) .'</strong> ';
	}

do_action( 'woocommerce_before_order_itemmeta', $item_id, $item, $_product ) ?>
</div>
<div class="order_meta_view">
<?php
if ( $metadata = $item->get_formatted_meta_data() ) {
foreach ( $metadata as $meta_key => $meta_val ) {
	echo '<ul><li>' . $meta_val->display_key . ':</li><li>' . $meta_val->display_value . '</li></ul>';
}
}
?>
</div>
<div class="order_quantity">
<?php
echo ( isset( $item['qty'] ) ) ? esc_html( $item['qty'] ) : '';
if ( $refunded_qty = $order->get_qty_refunded_for_item( $item_id ) ) {
	echo '<small class="refunded">-' . $refunded_qty . '</small>';
}
?>
</div>
</div>