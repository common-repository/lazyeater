<?php
/**
 * Dashboard View: Items page item form variation section
 *
 * @package Norsani/Dashboard/Items
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="multi-field item_variation">
	<input value="<?php echo $variation_id; ?>" name="item_options[][id]" class="item_options item_options_id form-control" type="hidden">
	<div class="options_group_wrapper">
		<?php foreach ($item_attributes as $attribute) {
		if ( ! $attribute->get_variation() ) {
			continue;
		}
		$selected_value = isset( $attribute_values[ sanitize_title( $attribute->get_name() ) ] ) ? $attribute_values[ sanitize_title( $attribute->get_name() ) ] : '';
		?>
		<div class="form-group option_group">
			<div class="option_form">
			<?php if ( $attribute->is_taxonomy() ) : ?>
			<div class="control-label attr_name"><?php echo esc_html( wc_attribute_label( $attribute->get_name() ) ); ?></div>
			<?php else : ?>
			<div class="control-label attr_name"><?php echo wc_attribute_label( $attribute->get_name() ); ?></div>
			<?php endif; ?>		
			<input value="<?php echo wc_attribute_label( $attribute->get_name() ); ?>" data-attrname="<?php echo sanitize_title( $attribute->get_name() ); ?>" name="var_<?php echo sanitize_title( $attribute->get_name() ); ?>" class="item_options item_option_attribute form-control" type="hidden">
			</div>
			<div class="option_form">
			<label class="control-label option_label" for="item_options[][<?php echo sanitize_title( $attribute->get_name() ); ?>]"><?php _e( 'Option','frozr-norsani'); ?></label>
			<select data-attropt="<?php echo sanitize_title( $attribute->get_name() ); ?>" data-role="none" name="item_options[][<?php echo sanitize_title( $attribute->get_name() ); ?>]" class="item_options form-control">
				<option value=""><?php printf( esc_html__( 'Any %1$s&hellip;', 'frozr-norsani' ), wc_attribute_label( $attribute->get_name() ) ); ?></option>
				<?php if ( $attribute->is_taxonomy() ) : ?>
					<?php foreach ( $attribute->get_terms() as $option ) : ?>
						<option <?php selected( $selected_value, $option->slug ); ?> value="<?php echo esc_attr( $option->slug ); ?>"><?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $option->name ) ); ?></option>
					<?php endforeach; ?>
				<?php else : ?>
					<?php foreach ( $attribute->get_options() as $option ) : ?>
						<option <?php selected( $selected_value, $option ); ?> value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ); ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
			</div>
		</div>
		<?php } ?>
	</div>
	<div class="form-group">
		<label class="control-label" for="item_options[][description]"><?php _e( 'Variation Description','frozr-norsani'); frozr_inline_help_db('dash_item_vardesc'); ?></label>
		<input value="<?php echo $discription; ?>" name="item_options[][description]" class="item_options form-control" type="text" placeholder="<?php _e('Few words about this variation...','frozr-norsani'); ?>">
	</div>
	<?php
	/* Price*/
	frozr_wp_text_input( array( 'id' => 'item_options[][regular_price]', 'value' => $regular_price, 'label' => __( 'Regular Price', 'frozr-norsani' ) . ' (' . get_woocommerce_currency_symbol() . ')', 'data_type' => 'price') );

	/* Special Price*/
	frozr_wp_text_input( array( 'id' => 'item_options[][price]', 'data_type' => 'price','value' => $price, 'label' => __( 'Sale Price', 'frozr-norsani' ) . ' ('.get_woocommerce_currency_symbol().')', 'description' => '<a href="#" class="sale_schedule">' . __( 'Schedule', 'frozr-norsani' ) . '</a>' ) );
	
	echo '<div class="form-group sale_price_dates_fields frozr_hide">
			<div class="frozr_item_sale_dates">
			<div>
			<label class="control-label" for="_sale_price_dates_from">' . __( 'Sale price start date', 'frozr-norsani' ) . '</label>
			<input type="date" class="short" name="_sale_price_dates_from" id="_sale_price_dates_from" value="' . esc_attr( $_sale_price_dates_from ) . '" placeholder="' . __( 'Sale price start date', 'placeholder', 'frozr-norsani' ) . ' YYYY-MM-DD" maxlength="10" />
			</div>
			<div>
			<label class="control-label" for="_sale_price_dates_to">' . __( 'Sale price end date', 'frozr-norsani' ) . '</label>
			<input type="date" class="short" name="_sale_price_dates_to" id="_sale_price_dates_to" value="' . esc_attr( $_sale_price_dates_to ) . '" placeholder="' . __( 'Sale price end date', 'placeholder', 'frozr-norsani' ) . '  YYYY-MM-DD" maxlength="10" />
			</div>
			</div>
			<a href="#" title="'.esc_attr__( 'The sale will end at the beginning of the set date.', 'frozr-norsani' ).'" class="cancel_sale_schedule frozr_hide">'. __( 'Cancel', 'frozr-norsani' ) .'</a>
		</div>';
	?>
	<i class="remove_option_field material-icons" data-varid="<?php echo $variation_id; ?>">close</i>
</div>