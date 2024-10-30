<?php
/**
 * Dashboard View: Items page item edit/add form data
 *
 * @package Norsani/Dashboard/Items
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="woocommerce-product-data" class="panel-wrap product_data">
	<div class="form-group gen_item_opts">		
		<div class="product-image">
			<div class="instruction-inside<?php echo $instruction_class; ?>">
				<input type="hidden" name="feat_image_id" class="frozr-feat-image-id" value="<?php echo $feat_image_id; ?>">
				<i class="material-icons">add_a_photo</i>
				<a href="#" class="frozr-feat-image-btn btn btn-sm" title="<?php _e('Upload Cover','frozr-norsani'); ?>"><?php _e( 'Upload cover image', 'frozr-norsani' ); ?></a>
			</div>

			<div class="image-wrap<?php echo $wrap_class; ?>">
				<a class="close frozr-remove-feat-image" href="#" title="<?php _e('Remove Image','frozr-norsani'); ?>"><i class="material-icons">photo_camera</i></a>
				<?php if ( $feat_image_id ) {
				$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'full');
				echo '<div class="product-photo" style="background-image: url( '.$large_image_url[0].');"></div>';
				} else { ?>
				<div class="product-photo"></div>
				<?php } ?>
			</div>
		</div>
		<div class="product-general-details">
			<div class="form-group">
				<input class="pid" type="hidden" name="frozr_product_id" value="<?php echo $post_id; ?>">
				<label class="control-label" for="post_title"><?php _e('Product Name','frozr-norsani'); ?></label>
				<?php frozr_post_input_box( $post_id, 'post_title', array( 'placeholder' => __('Type name including brand name if available.','frozr-norsani'), 'value' => $product_title ) ); ?>
			</div>
		</div>
	</div>
	<div class="frozr_product_edit_collapsible" data-role="collapsible-set">
	<div id="general_product_data" class="panel tablist-content woocommerce_options_panel" data-role="collapsible" <?php if ($new == true) { echo 'data-collapsed="false"'; } ?>>
	<h3><?php _e( 'General fields', 'frozr-norsani' ); ?><i class="material-icons">expand_more</i></h3>
	<div class="options_group">
		<div class="form-group ppdetail">
			<label class="control-label" for="post_excerpt"><?php _e('Short Description','frozr-norsani'); frozr_inline_help_db('dash_item_det'); ?></label>
			<?php frozr_post_input_box( $post_id, 'post_excerpt', array( 'placeholder' => __('Few words about the product...','frozr-norsani'), 'value' => $product_excerpt ), 'text' ); ?>
		</div>
		<div class="form-group">
			<label class="control-label" for="post_content"><?php _e( 'Full Description','frozr-norsani'); frozr_inline_help_db('dash_item_desc'); ?></label>
			<textarea id="post_content<?php echo $post_id; ?>" name="post_content"><?php echo $product_content; ?></textarea>
		</div>
		<div class="form-group">
			<label class="control-label" for="product_cat"><?php _e( 'Category', 'frozr-norsani' ); frozr_inline_help_db('dash_item_cat'); ?></label>
			<input id="product_cat_<?php echo $post_id; ?>" name="product_cat" type="text" value="<?php echo $item_cats; ?>">
		</div>
		<?php if (!get_option('frozr_hide_menus')) { ?>
		<div class="form-group">
			<label class="control-label" for="product_cat"><?php _e( 'Menu', 'frozr-norsani' ); frozr_inline_help_db('dash_item_menu'); ?></label>
			<?php if (norsani()->vendor->frozr_meal_type_options(get_current_user_id())) { ?>
			<select name="product_meal_type[]" data-role="none" class="pmty" multiple="multiple">
				<?php foreach(norsani()->vendor->frozr_meal_type_options(get_current_user_id()) as $get_option_val => $get_option_title) { ?>
				<option value="<?php echo $get_option_val; ?>" <?php echo in_array($get_option_val, $product_meal_type) ? 'selected="selected"' : ''; ?> ><?php echo $get_option_title; ?></option>
				<?php } ?>
			</select>
			<?php } else { ?>
			<span class="no_meal_types"><?php echo sprintf( __('You must set your %1$s before you can use this option', 'frozr-norsani'), '<a href="'.home_url( '/dashboard/settings/#usr_meal_types_opts' ).'">'.__('meal types','frozr-norsani').'</a>') ?></span>
			<?php } ?>
		</div>
		<?php } ?>
		<div class="form-group">
			<label class="control-label" for="item_ingredients"><?php _e( 'Ingredients','frozr-norsani'); frozr_inline_help_db('dash_item_ing'); ?></label>
			<input id="item_ingredients_<?php echo $post_id; ?>" name="item_ingredients" type="text" value="<?php echo $ingreds; ?>">
		</div>
		<div class="form-group">
			<label class="control-label" for="item_pretime"><?php _e( 'Preparation/Handling time (in minutes)','frozr-norsani'); frozr_inline_help_db('dash_item_pretime'); ?></label>
			<input id="item_pretime<?php echo $post_id; ?>" name="item_pretime" type="number" min="0" step="1" value="<?php echo $pretime; ?>">
		</div>
		<div class="form-group">
			<label class="control-label" for="item_maxords"><?php _e( 'Maximum orders per day','frozr-norsani'); frozr_inline_help_db('dash_item_max_orders'); ?></label>
			<input id="item_maxords_<?php echo $post_id; ?>" name="item_maxords" type="number" min="0" step="1" value="<?php echo $max_orders; ?>">
		</div>
		<!--
		<div class="form-group">
			<span class="control-label"> echo __( 'Type', 'frozr-norsani' ); ?></span>
			<div class="frozr_set_food_type">
				<label> _e( 'Veg.', 'frozr-norsani' ); ?>
					<input type="radio" name="item_veg" value="veg"  checked( $vegp, 'veg' ); ?>>
				</label>
				<label> _e( 'Non-Veg.', 'frozr-norsani' ); ?>
					<input type="radio" name="item_veg" value="nonveg"  checked( $vegp, 'nonveg' ); ?>>
				</label>
			</div>
		</div>
		<div class="form-group">
			<span class="control-label"> echo __( 'Other info', 'frozr-norsani' ); ?></span>
			<div class="frozr_set_food_type">
				<label> _e( 'Product is Spicy?', 'frozr-norsani' ); ?>
					<input type="checkbox" name="item_spicy" value="yes"  checked( $spicp, 'yes' ); ?>>
				</label>						
				<label> _e( 'Show Fat Amount?', 'frozr-norsani' ); ?>
					<input type="checkbox" class="item_fat" name="item_fat" value="yes"  checked( $fatp, 'yes' ); ?>>
				</label>						
				<input type="number" class="item_fat_rate  if ($fatp != 'yes') { echo 'frozr_hide'; } ?>" name="item_fat_rate" min="0" max="100" step="0.01" value=" echo esc_attr($fatrp); ?>" placeholder=" _e('Amount of Fat in Grams.','frozr-norsani'); ?>">
			</div>
		</div>
		-->
	</div>
	<div class="options_group pricing<?php echo ( $item_has_options ) ? ' frozr_reg_price_dis': ''; ?>">
	<?php
	frozr_wp_text_input( array( 'id' => '_regular_price', 'value' => $regular_price, 'label' => __( 'Regular Price', 'frozr-norsani' ) . ' (' . get_woocommerce_currency_symbol() . ')', 'data_type' => 'price', 'custom_attributes' => $in_disabled ) );

	/* Special Price*/
	frozr_wp_text_input( array( 'id' => '_sale_price', 'value' => $sale_price, 'data_type' => 'price', 'label' => __( 'Sale Price', 'frozr-norsani' ) . ' ('.get_woocommerce_currency_symbol().')', 'description' => '<a href="#" class="sale_schedule">' . __( 'Schedule', 'frozr-norsani' ) . '</a>', 'custom_attributes' => $in_disabled ) );


	echo '<div class="form-group sale_price_dates_fields frozr_hide">
			<div class="frozr_item_sale_dates">
			<div>
			<label class="control-label" for="_sale_price_dates_from">' . __( 'Sale price start date', 'frozr-norsani' ) . '</label>
			<input type="date" class="short" name="_sale_price_dates_from" id="_sale_price_dates_from" '.$on_disabled.' value="' . esc_attr( $_sale_price_dates_from ) . '" placeholder="' . __( 'Sale price start date', 'placeholder', 'frozr-norsani' ) . ' YYYY-MM-DD" maxlength="10" />
			</div>
			<div>
			<label class="control-label" for="_sale_price_dates_to">' . __( 'Sale price end date', 'frozr-norsani' ) . '</label>
			<input type="date" class="short" name="_sale_price_dates_to" id="_sale_price_dates_to" '.$on_disabled.' value="' . esc_attr( $_sale_price_dates_to ) . '" placeholder="' . __( 'Sale price end date', 'placeholder', 'frozr-norsani' ) . '  YYYY-MM-DD" maxlength="10" />
			</div>
			</div>
			<a href="#" title="'.esc_attr__( 'The sale will end at the beginning of the set date.', 'frozr-norsani' ).'" class="cancel_sale_schedule frozr_hide">'. __( 'Cancel', 'frozr-norsani' ) .'</a>
		</div>';

	do_action( 'woocommerce_product_options_pricing' );
	?>
	</div>
	<?php do_action( 'woocommerce_product_options_general_product_data' ); ?>
	</div>
	<div id="product_variations" class="panel tablist-content woocommerce_options_panel" data-role="collapsible">
		<h3><?php _e( 'Variations', 'frozr-norsani' ); ?><i class="material-icons">expand_more</i></h3>
		
		<div class="form-group">
			<label><?php _e( 'The product has variations?', 'frozr-norsani' ); ?>
				<input type="checkbox" class="product_has_variations" name="item_has_options" value="<?php echo $item_has_options; ?>" <?php checked( $item_has_options, 'yes' ); ?>>
				<?php frozr_inline_help_db('dash_item_var'); ?>
			</label>
		</div>					
		<div class="item_variation_wrapper <?php if ($item_has_options != 'yes') { echo 'frozr_hide'; } ?>">
			<div class="options_group_wrapper">
				<div class="option_multiple">
				<?php $prod_atrs = ( $new == false && $product_obj != '' && $product_obj->is_type( 'variable' )) ? $product_obj->get_variation_attributes(): array(); if ( $new == false && $product_obj != '' && $product_obj->is_type( 'variable' ) && !empty($prod_atrs) ) { ?>
					<?php 
					
					$attributes	= $product_obj->get_attributes( 'edit' );
					
					foreach ( $attributes as $attribute ) {
					?>
					<div class="form-group option_group frozr_item_options_wrapper">
						<div class="option_form">
							<?php if ( $attribute->is_taxonomy() ) : ?>
							<strong><?php echo __( 'Variation title', 'frozr-norsani' ) . ' ' . esc_html( wc_attribute_label( $attribute->get_name() ) ); frozr_inline_help_db('dash_item_var_admin'); ?></strong>
							<input type="hidden" class="attribute_name" data-attrname="<?php echo sanitize_title( $attribute->get_name() ); ?>" name="attribute_names[<?php echo sanitize_title( $attribute->get_name() ); ?>]" value="<?php echo wc_attribute_label( $attribute->get_name() ); ?>" />
							<?php else : ?>
							<label class="control-label"><?php _e( 'variation title', 'frozr-norsani' ); frozr_inline_help_db('dash_item_var_title'); ?></label>
							<input type="text" class="attribute_name" data-attrname="<?php echo sanitize_title( $attribute->get_name() ); ?>" name="attribute_names[<?php echo sanitize_title( $attribute->get_name() ); ?>]" value="<?php echo wc_attribute_label( $attribute->get_name() ); ?>" />
							<?php endif; ?>
						</div>
						<div class="option_form">
						<label class="control-label"><?php _e( 'Variations', 'frozr-norsani' ); frozr_inline_help_db('dash_item_vars'); ?></label>
						<?php if ( $attribute->is_taxonomy() && ( $attribute_taxonomy = $attribute->get_taxonomy_object() ) ) : ?>
						<?php if ( 'select' === $attribute_taxonomy->attribute_type ) : ?>
							<div class="frozr_select_attr_vals">
							<span><?php _e('Update product after selecting to see changes on options below.','frozr-norsani'); ?></span>
							<select data-role="none" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select terms', 'frozr-norsani' ); ?>" name="attribute_values[<?php echo sanitize_title( $attribute->get_name() ); ?>][]" data-attropt="<?php echo sanitize_title( $attribute->get_name() ); ?>">
								<?php
								$args = array(
									'orderby'    => 'name',
									'hide_empty' => 0,
								);
								$all_terms = get_terms( $attribute->get_taxonomy(), apply_filters( 'woocommerce_product_attribute_terms', $args ) );
								if ( $all_terms ) {
									foreach ( $all_terms as $term ) {
										$options = $attribute->get_options();
										$options = ! empty( $options ) ? $options : array();
										echo '<option value="' . esc_attr( $term->term_id ) . '" ' . selected( in_array( $term->term_id, $options ), true, false ) . '>' . esc_attr( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) ) . '</option>';
									}
								}
								?>
							</select>
							</div>
						<?php elseif ( 'text' == $attribute_taxonomy->attribute_type ) : ?>
						<textarea name="attribute_values[<?php echo sanitize_title( $attribute->get_name() ); ?>]" data-attropt="<?php echo sanitize_title( $attribute->get_name() ); ?>" class="attrs_vals" cols="5" rows="3" placeholder="<?php printf( esc_attr__( 'Enter variations by "%1$s" separating values.', 'frozr-norsani' ), WC_DELIMITER ); ?>"><?php echo esc_textarea( wc_implode_text_attributes( wp_list_pluck( $attribute->get_terms(), 'name' ) ) ); ?></textarea>
						<?php endif; ?>
						<?php else : ?>
						<textarea name="attribute_values[<?php echo sanitize_title( $attribute->get_name() ); ?>]" data-attropt="<?php echo sanitize_title( $attribute->get_name() ); ?>" class="attrs_vals" cols="5" rows="3" placeholder="<?php printf( esc_attr__( 'Enter variations by "%1$s" separating values.', 'frozr-norsani' ), WC_DELIMITER ); ?>"><?php echo esc_textarea( wc_implode_text_attributes( $attribute->get_options() ) ); ?></textarea>
						<?php endif; ?>
						</div>
						<i class="remove_option_group material-icons">close</i>
					</div>
				<?php } } else { ?>
					<div class="form-group option_group frozr_item_options_wrapper">
						<div class="option_form">
							<label class="control-label"><?php _e( 'Variation title', 'frozr-norsani' ); frozr_inline_help_db('dash_item_var_title'); ?></label>
							<input type="text" class="attribute_name new_attr" name="attribute_names[]" value="" />
						</div>
						<div class="option_form">
							<label class="control-label"><?php _e( 'Variations', 'frozr-norsani' ); frozr_inline_help_db('dash_item_vars'); ?></label>
							<textarea class="attrs_vals new_attr" name="attribute_values[]" cols="5" rows="3" placeholder="<?php printf( esc_attr__( 'Enter variations by "%1$s" separating values.', 'frozr-norsani' ), WC_DELIMITER ); ?>"></textarea>
						</div>
						<i class="remove_option_group material-icons">close</i>
					</div>
				<?php } ?>
				</div>
				<div class="add_option_form"><?php _e('+ Add Option Group','frozr-norsani'); ?></div>
			</div>
			<div class="multi-field-wrapper">
			<div class="multi-fields"><?php $pord_attrs = ( $new == false && $product_obj != '' && $product_obj->is_type( 'variable' )) ? $product_obj->get_variation_attributes(): array(); if ( $new == false && $product_obj != '' && $product_obj->is_type( 'variable' ) && !empty($pord_attrs) ) {norsani()->item->frozr_get_product_variations($post->ID);} ?></div>
			<button type="button" class="add-field"><?php _e('Add new option','frozr-norsani'); ?></button>
			</div>
		</div>
	</div>
	<div id="product_promotions" class="panel tablist-content woocommerce_options_panel" data-role="collapsible">
	<h3><?php _e( 'Promotions', 'frozr-norsani' ); ?><i class="material-icons">expand_more</i></h3>
		<div class="multi-field-wrapper">
			<div class="multi-fields">
				<?php if (!empty($item_promotions)) { foreach ($item_promotions as $vals){ ?>
				<div class="multi-field item_promotions">
					<div class="form-group">
						<label class="control-label" for="item_promotions[][buy]"><?php _e( 'Buy','frozr-norsani'); frozr_inline_help_db('dash_item_promo'); ?></label>
						<input value="<?php echo $vals['buy']; ?>" name="item_promotions[][buy]" class="item_promotions form-control" type="number" placeholder="<?php _e('Buy','frozr-norsani'); ?>">
					</div>
					<div class="form-group">
						<label class="control-label" for="item_promotions[][get]"><?php _e( 'Get','frozr-norsani'); frozr_inline_help_db('dash_item_promo_type'); ?></label>
						<select name="item_promotions[][get]" data-role="none" class="item_promotions_get">
							<?php foreach($get_options as $get_option_val => $get_option_title) { ?>
							<option value="<?php echo $get_option_val; ?>" <?php selected($get_option_val, $vals['get']); ?> ><?php echo $get_option_title; ?></option>
							<?php } ?>
						</select>	
					</div>
					<div class="form-group discount-form-control" <?php if ($vals['get'] != "discount") { echo 'style="display:none" disabled="disabled"'; } ?>>
						<label class="control-label" for="item_promotions[][discount]"><?php _e( 'Discount %','frozr-norsani'); frozr_inline_help_db('dash_item_promo_desc'); ?></label>
						<input value="<?php echo $vals['discount']; ?>" name="item_promotions[][discount]" class="item_promotions" type="number" placeholder="<?php _e('Discount %','frozr-norsani'); ?>">
					</div>
					<div class="form-group item-form-control" <?php if ($vals['get'] != "free_item") { echo 'style="display:none" disabled="disabled"'; } ?>>
						<label class="control-label" for="item_promotions[][item]"><?php _e( 'Free Product','frozr-norsani'); frozr_inline_help_db('dash_item_promo_item'); ?></label>
						<select name="item_promotions[][item]" data-role="none" class="item_promotions">
							<?php foreach ( $linking_posts as $linking_post ) { setup_postdata( $linking_post ); ?>
							<option value="<?php echo $linking_post->ID; ?>" <?php selected($linking_post->ID, $vals['item']); ?> ><?php echo get_the_title($linking_post->ID); ?></option>
							<?php }
							wp_reset_postdata(); ?>
						</select>
					</div>
					<i class="remove-field material-icons">close</i>
				</div>
				<?php } } else { ?>
				<div class="multi-field item_promotions">
					<div class="form-group">
						<label class="control-label" for="item_promotions[][buy]"><?php _e( 'Buy','frozr-norsani'); frozr_inline_help_db('dash_item_promo'); ?></label>
						<input value="" name="item_promotions[][buy]" class="item_promotions form-control" type="number" placeholder="<?php _e('Buy','frozr-norsani'); ?>">
					</div>
					<div class="form-group">
						<label class="control-label" for="item_promotions[][get]"><?php _e( 'Get','frozr-norsani'); frozr_inline_help_db('dash_item_promo_type'); ?></label>
						<select name="item_promotions[][get]" data-role="none" class="item_promotions_get">
							<?php foreach($get_options as $get_option_val => $get_option_title) { ?>
							<option value="<?php echo $get_option_val; ?>" ><?php echo $get_option_title; ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="form-group discount-form-control">
						<label class="control-label" for="item_promotions[][discount]"><?php _e( 'Discount %','frozr-norsani'); frozr_inline_help_db('dash_item_promo_desc'); ?></label>
						<input value="" name="item_promotions[][discount]" class="item_promotions" type="number" placeholder="<?php _e('Discount %','frozr-norsani'); ?>">
					</div>
					<div style="display:none;" disabled="disabled" class="form-group item-form-control">
						<label class="control-label" for="item_promotions[][item]"><?php _e( 'Free Product','frozr-norsani'); frozr_inline_help_db('dash_item_promo_item'); ?></label>
						<select name="item_promotions[][item]" data-role="none" class="item_promotions">
							<?php foreach ( $linking_posts as $linking_post ) { setup_postdata( $linking_post ); ?>
							<option value="<?php echo $linking_post->ID; ?>" ><?php echo get_the_title($linking_post->ID); ?></option>
							<?php }
							wp_reset_postdata(); ?>
						</select>
					</div>
					<i class="remove-field material-icons">close</i>
				</div>
				<?php } ?>
			</div>
			<button type="button" class="add-field"><?php _e('Add new promotion rule','frozr-norsani'); ?></button>
		</div>
	</div>
	<div id="linked_product_data" class="panel tablist-content woocommerce_options_panel" data-role="collapsible">
		<h3><?php _e( 'Link products', 'frozr-norsani' ); ?><i class="material-icons">expand_more</i></h3>

		<div class="options_group">

			<div class="fl-form-field">
				<label class="control-label" for="upsell_ids"><?php _e( 'Up-Sells', 'frozr-norsani' ); frozr_inline_help_db('dash_item_upsell'); ?></label>
				<select name="upsell_ids[]" id="upsell_ids" multiple="multiple" data-native-menu="false" data-role="none" >
					<?php foreach ( $linking_posts as $linking_post ) { setup_postdata( $linking_post ); ?>
					<option value="<?php echo $linking_post->ID; ?>" <?php if (in_array($linking_post->ID, $upsel)) { echo 'selected'; } ?> ><?php echo get_the_title($linking_post->ID); ?></option>
					<?php }
					wp_reset_postdata(); ?>
				</select>
			</div>

			<div class="fl-form-field">
				<label class="control-label" for="crosssell_ids"><?php _e( 'Cross-Sells', 'frozr-norsani' ); frozr_inline_help_db('dash_item_crsell'); ?></label>
				<select name="crosssell_ids[]" id="crosssell_ids" multiple="multiple" data-native-menu="false" data-role="none" >
					<?php foreach ( $linking_posts as $linking_post ) { setup_postdata( $linking_post ); ?>
					<option value="<?php echo $linking_post->ID; ?>" <?php if (in_array($linking_post->ID, $crsel)) { echo 'selected'; } ?> ><?php echo get_the_title($linking_post->ID); ?></option>
					<?php }
					wp_reset_postdata(); ?>
				</select>
			</div>
		</div>
		<?php do_action( 'woocommerce_product_options_related' ); ?>
	</div>
	<div id="advanced_product_data" class="panel tablist-content woocommerce_options_panel" data-role="collapsible">
		<h3 class="form-group-label"><?php _e( 'Other options', 'frozr-norsani' ); ?><i class="material-icons">expand_more</i></h3>
		<div class="options_group hide_if_external">
			<?php /* Purchase note*/
			frozr_wp_textarea_input(  array( 'id' => '_purchase_note', 'label' => __( 'Purchase Note', 'frozr-norsani' ) ) );
			?>
			<span class="frozr_help_tip"><?php echo __( 'Enter an optional note to send the customer after purchase.', 'frozr-norsani' ); ?></span>
		</div>
		<?php do_action( 'woocommerce_product_options_advanced' ); ?>
	</div>
	<?php do_action( 'woocommerce_product_data_panels' ); ?>
	</div>
	<div class="clear"></div>
</div>
<script>
	jQuery(function () {
		jQuery('#item_ingredients_<?php echo $post_id; ?>').tagator({
			autocomplete: [<?php echo $ings; ?>]
		});
		
		jQuery('#product_cat_<?php echo $post_id; ?>').tagator({
			autocomplete: [<?php echo $product_cats; ?>]
		});
	});
</script>