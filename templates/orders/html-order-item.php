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
<tr class="order_item <?php echo apply_filters( 'woocommerce_admin_html_order_item_class', ( ! empty( $class ) ? $class : '' ), $item ); ?>" data-order_item_id="<?php echo $item_id; ?>">
<td class="order_name">
<?php if (!isset( $_GET['print'] )) { ?>
<div class="order_thumb hide_on_mobile">
<?php if ( $_product ) : ?>
<?php if (!$rest) {echo apply_filters( 'woocommerce_admin_order_item_thumbnail', $_product->get_image( 'shop_thumbnail', array( 'title' => '' ) ), $item_id, $item ); } ?>
<?php else : ?>
	<?php echo wc_placeholder_img( 'shop_thumbnail' ); ?>
<?php endif; ?>
</div>
<?php } ?>
<div class="frozr_item_details_txt">
<?php echo ( $_product && $_product->get_sku() ) ? esc_html( $_product->get_sku() ) . ' &ndash; ' : '';
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
?>
</div>
<?php do_action( 'woocommerce_before_order_itemmeta', $item_id, $item, $_product ) ?>
<div class="order_meta_view">
<?php
if ( $metadata = $item->get_formatted_meta_data() ) {
echo '<table cellspacing="0" class="display_meta">';
foreach ( $metadata as $meta_key => $meta_val ) {
	echo '<tr><th>' . $meta_val->display_key . ':</th><td>' . $meta_val->display_value . '</td></tr>';
}
echo '</table>';
}
?>
</div>
<?php do_action( 'woocommerce_after_order_itemmeta', $item_id, $item, $_product ) ?>
</td>
<?php do_action( 'woocommerce_admin_order_item_values', $_product, $item, absint( $item_id ) ); ?>
<td class="item_cost hide_on_mobile" width="1%">
<div class="order_view">
<?php
if ( isset( $item['line_total'] ) ) {
if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] != $item['line_total'] ) {
	echo '<del>' . wc_price( $order->get_item_subtotal( $item, false, true ), array( 'currency' => $order->get_currency () ) ) . '</del> ';
}
echo wc_price( $order->get_item_total( $item, false, true ), array( 'currency' => $order->get_currency () ) );
}
?>
</div>
</td>
<td class="order_quantity hide_on_mobile" width="1%">
<div class="order_view">
<?php
echo ( isset( $item['qty'] ) ) ? esc_html( $item['qty'] ) : '';
if ( $refunded_qty = $order->get_qty_refunded_for_item( $item_id ) ) {
	echo '<small class="refunded">-' . $refunded_qty . '</small>';
}
?>
</div>
</td>
<td class="line_cost" width="1%">
<div class="order_view">
<?php
if ( isset( $item['line_total'] ) ) {
	if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] != $item['line_total'] ) {
		echo '<del>' . wc_price( $item['line_subtotal'], array( 'currency' => $order->get_currency () ) ) . '</del> ';
	}
	echo wc_price( $item['line_total'], array( 'currency' => $order->get_currency () ) );
}

if ( $refunded = $order->get_total_refunded_for_item( $item_id ) ) {
	echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency () ) ) . '</small>';
}
?>
<div class="hide_on_desktop">
<?php
$qnt = ( isset( $item['qty'] ) ) ? esc_html( $item['qty'] ) : '';
$price = wc_price( $order->get_item_subtotal( $item, false, true ), array( 'currency' => $order->get_currency () ) );
$total_price = wc_price( $order->get_item_total( $item, false, true ), array( 'currency' => $order->get_currency () ) );
if ( isset( $item['line_total'] ) ) {
if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] != $item['line_total'] ) {
	echo '<span class="frozr_dash_single_order_price">('; echo ($qnt != '') ? $qnt .'X'.$price :'1X'.$price; echo ')</span> ';
}
echo '<span class="frozr_dash_single_order_price">('; echo ($qnt != '') ? $qnt .' X '.$total_price:'1 X '. $total_price; echo ')</span> ';
}
if ( $refunded_qty = $order->get_qty_refunded_for_item( $item_id ) ) {
	echo '<small class="refunded">-' . $refunded_qty . '</small>';
} ?>
</div>
</div>
</td>
<?php
if ( empty( $legacy_order ) && wc_tax_enabled() ) :
$line_tax_data = isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : '';
$tax_data      = maybe_unserialize( $line_tax_data );

foreach ( $order_taxes as $tax_item ) :
$tax_item_id       = $tax_item['rate_id'];
$tax_item_total    = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
$tax_item_subtotal = isset( $tax_data['subtotal'][ $tax_item_id ] ) ? $tax_data['subtotal'][ $tax_item_id ] : '';
?>
<td class="line_tax" width="1%">
<div class="order_view">
<?php
if ( '' != $tax_item_total ) {
	if ( isset( $tax_item_subtotal ) && $tax_item_subtotal != $tax_item_total ) {
		echo '<del>' . wc_price( wc_round_tax_total( $tax_item_subtotal ), array( 'currency' => $order->get_currency () ) ) . '</del> ';
	}

	echo wc_price( wc_round_tax_total( $tax_item_total ), array( 'currency' => $order->get_currency () ) );
} else {
	echo '&ndash;';
}

if ( $refunded = $order->get_tax_refunded_for_item( $item_id, $tax_item_id ) ) {
	echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency () ) ) . '</small>';
}
?>
</div>
</td>
<?php
endforeach;
endif;
?>
</tr>