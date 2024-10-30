<?php
/**
 * Items View: Items add to cart template
 *
 * @package Norsani/Items
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="fle_addtocart" data-item="<?php echo $post->ID; ?>">
	<?php do_action('frozr_before_product_addtocart', $post);

	if ( $product->is_purchasable() ) {
		
		if ($product->get_type() == 'simple') {
		
		do_action( 'woocommerce_before_add_to_cart_form' ); ?>

		<form class="cart ajax_lazy_submit" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $post->ID ); ?>">
			<?php do_action('frozr_simple_add_to_cart_form', $product, $_POST); ?>
			<?php do_action('woocommerce_before_add_to_cart_button', $product, $_POST); ?>
			<?php norsani()->item->frozr_add_to_cart_btn($product, $_POST); ?>
			<?php do_action('woocommerce_after_add_to_cart_button', $product, $_POST); ?>
		</form>

		<?php do_action( 'woocommerce_after_add_to_cart_form' );
		
		} elseif ($product->get_type() == 'variable') {
		
		/* Get Available variations?*/
		$get_variations = sizeof( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );

		$available_variations = $get_variations ? $product->get_available_variations() : false;
		$attributes           = $product->get_variation_attributes();
		$selected_attributes  = $product->get_default_attributes();
		$attribute_keys = array_keys( $attributes );

		do_action( 'woocommerce_before_add_to_cart_form', $product, $_POST ); ?>

		<form class="variations_form cart ajax_lazy_submit" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $post->ID ); ?>" data-product_variations="<?php echo htmlspecialchars( json_encode( $available_variations ) ) ?>">
			<?php do_action( 'woocommerce_before_variations_form', $product, $_POST ); ?>

			<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
				<p class="stock out-of-stock"><?php _e( 'This product is currently unavailable.', 'frozr-norsani' ); ?></p>
			<?php else : ?>
				<div class="variations" cellspacing="0">
					<?php foreach ( $attributes as $attribute_name => $options ) : ?>
					<label class="var_label" for="<?php echo sanitize_title( $attribute_name ); ?>"><?php echo wc_attribute_label( $attribute_name ); ?></label>
					<div class="var_value">
					<?php
					$selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );
					wc_dropdown_variation_attribute_options( array( 'options' => $options, 'attribute' => $attribute_name, 'product' => $product, 'selected' => $selected ) );
					echo end( $attribute_keys ) === $attribute_name ? apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'frozr-norsani' ) . '</a>' ) : '';
					?>
					</div>
					<?php endforeach;?>
				</div>

				<?php do_action('woocommerce_before_add_to_cart_button', $product, $_POST); ?>
				<?php norsani()->item->frozr_add_to_cart_btn($product, $_POST); ?>
				<?php do_action('woocommerce_after_add_to_cart_button', $product, $_POST); ?>
			<?php endif; ?>

			<?php do_action('woocommerce_after_variations_form'); ?>
		</form>
		<script>( function( $ ) {$('.variations_form').wc_variation_form();})( jQuery );</script>
		<?php do_action('woocommerce_after_add_to_cart_form');
		}
	} else {
		echo __('This product is currently unavailable.','frozr-norsani');
	}

	do_action('frozr_after_product_addtocart', $post); ?>
	<div class="frozr_item_location_wrapper frozr_hide">
	<?php frozr_user_location_form(); ?>
	</div>
</div>