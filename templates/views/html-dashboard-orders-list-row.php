<?php
/**
 * Dashboard View: Orders page list table single row
 *
 * @package Norsani/Dashboard/Order
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<tr class="frozr_single_order_rwo <?php echo apply_filters('frozr_single_order_rwo_css_class','_order_'.$sts, $order_id, $sts);?>">
	<td class="frozr_order_list_title">
		<?php echo '<a href="' . wp_nonce_url( add_query_arg( array( 'order_id' => $the_order->get_id() ), home_url( '/dashboard/orders/') ), 'frozr_view_order' ) . '" title="'. __('More Details','frozr-norsani') .'"><strong>' . sprintf( __( 'Order %1$s', 'frozr-norsani' ), esc_attr( $the_order->get_order_number() ) ) . '</strong></a>'; ?>
		<?php echo '<a href="#" data-ord="'.$order_id.'" class="frozr_order_quick_view">'.__('Quick View','frozr-norsani').'</a>';?>
		<?php do_action('frozr_orders_list_after_id', $the_order); ?>
		<div class="hide_on_desktop">
		<div class="frozr_dash_orders_amount">
		<?php echo $the_order->get_formatted_order_total(); ?></br>
		<?php if ( $the_order->get_payment_method_title() ) {
		echo '<span class="meta">' . __('Via','frozr-norsani') . ' ' . esc_html( $the_order->get_payment_method_title() ) . '</span>';
		} do_action('frozr_orders_list_after_order_total', $the_order); ?>
		</div>
		</div>
	</td>
	<td class="hide_on_mobile">
		<?php echo $the_order->get_formatted_order_total(); ?></br>
		<?php if ( $the_order->get_payment_method_title() ) {
		echo '<span class="meta">' . __('Via','frozr-norsani') . ' ' . esc_html( $the_order->get_payment_method_title() ) . '</span>';
		} do_action('frozr_orders_list_after_order_total', $the_order); ?>
	</td>
	<?php if ($sts == 'processing') { ?>
	<td class="frozr_ord_pre_time_wrap">
		<?php norsani()->order->frozr_get_order_pre_time($the_order); ?>
		<?php do_action('frozr_after_order_pre_time',$the_order); ?>
	</td>
	<?php } ?>
	<td>
		<?php
		$customer_tip = '';
		if ( $address = $the_order->get_formatted_billing_address() ) {
		$customer_tip .= __( 'Billing:', 'frozr-norsani' ) . ' ' . $address;
		}
		if ( $the_order->get_billing_phone() ) {
		$customer_tip .= ' '.__( 'Tel:', 'frozr-norsani' ) . ' ' . $the_order->get_billing_phone();
		}
		echo '<div class="order_poster">';
		if ( $the_order->get_user_id() ) {
		$user_info = get_userdata( $the_order->get_user_id() );
		}
		if ( ! empty( $user_info ) ) {
		$username = '<span class="frozr_dash_order_customer">';
		if ( $user_info->first_name || $user_info->last_name ) {
		$username .= esc_html( ucfirst( $user_info->first_name ) . ' ' . ucfirst( $user_info->last_name ) );
		} else { $username .= esc_html( ucfirst( $user_info->display_name ) ); }
		$username .= '</span>';
		} else { $username = '<span class="frozr_dash_order_customer">';
		if ( $the_order->get_billing_first_name() || $the_order->get_billing_last_name() ) {
		$username = trim( $the_order->get_billing_first_name() . ' ' . $the_order->get_billing_last_name() );
		} else { $username = __( 'Guest', 'frozr-norsani' ); }
		$username .= '</span>';
		}
		echo $username . '<span class="frozr_dash_order_customer_det">'.$customer_tip.'</span>'
		?>
		</div>
		<div class="frozr_order_time_mobile hide_on_desktop">
		<?php norsani()->order->frozr_order_time($order_post); ?>
		</div>
		<div class="frozr_dash_mobile_actions hide_on_desktop">
		<?php norsani()->order->frozr_order_actions($the_order,$order_id); ?>
		</div>
		<?php do_action('frozr_orders_list_after_customer', $the_order); ?>
	</td>
	<td class="frozr_ord_list_date hide_on_mobile">
		<?php norsani()->order->frozr_order_time($order_post); ?>
	</td>
	<td class="frozr_dash_mobile_actions hide_on_mobile">
		<?php norsani()->order->frozr_order_actions($the_order,$order_id); ?>
	</td>
		<?php if (is_super_admin()) { ?>
	<td>
		<span><?php echo get_the_author_meta('login', $order_vendor). ' - ' . $order_author['store_name']; ?></span>
		<?php do_action('frozr_orders_list_after_seller', $the_order); ?>
	</td>
	<?php } ?>
	<?php do_action('frozr_after_order_listing_table_body', $order_id, $sts); ?>
</tr>
<tr class="frozr_order_quickview_wrapper" data-ord="<?php echo $order_id; ?>">
	<td colspan="<?php echo $colspan; ?>">
		<ul class="frozr_ord_quick_list_header">
		<li><?php echo __('Product','frozr-norsani'); ?></li>
		<li><?php echo __('Options','frozr-norsani'); ?></li>
		<li><?php echo __('Quantity','frozr-norsani'); ?></li>
		</ul>
		<?php echo norsani()->order->frozr_order_quick_view_body($the_order); ?>
	</td>
</tr>
<?php do_action('frozr_after_order_listing_table_item', $the_order); ?>