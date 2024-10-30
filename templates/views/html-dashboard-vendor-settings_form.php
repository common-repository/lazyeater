<?php
/**
 * Dashboard View: Vendor dashboard settings page form
 *
 * @package Norsani/Dashboard/Vendor
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<form method="post" id="settings-form"  action="" class="user-settings-form">
<div class="user-setting-header">
<div class="frozr-banner">
	<div class="image-wrap<?php echo $banner ? '' : ' frozr_hide'; ?>">
		<?php $banner_url = $banner ? wp_get_attachment_url( $banner ) : ''; ?>
		<input type="hidden" name="frozr_banner" class="frozr-banner-field" value="<?php echo $banner; ?>" >
		<div class="frozr-banner-img" style="background-image: url(<?php echo esc_url( $banner_url ); ?>);"></div>
		<a class="close frozr-remove-banner-image"><?php _e('Change','frozr-norsani'); ?></a>
	</div>

	<div class="button-area<?php echo $banner ? ' frozr_hide' : ''; ?>">
		<i class="material-icons">add_a_photo</i>
		<a href="#" class="frozr-banner-drag btn btn-info" data-uploader_title="<?php echo __('Select your shop\'s banner','frozr-norsani') ?>" data-uploader_button_text="<?php echo __('Select Image','frozr-norsani'); ?>"><?php _e( 'Upload banner', 'frozr-norsani' ); ?></a>
		<p class="help-block"><?php _e( 'Upload a banner for your shop. Banner size is (1125x190) pixel.', 'frozr-norsani' ); ?></p>
	</div>
</div>
<div class="pro_img">
	<div class="frozr-gravatar">
		<div class="gravatar-wrap<?php echo $gravatar ? '' : ' frozr_hide'; ?>">
			<?php $gravatar_url = $gravatar ? wp_get_attachment_url( $gravatar ) : ''; ?>
			<input type="hidden" class="frozr-gravatar-field" value="<?php echo $gravatar; ?>" name="frozr_gravatar">
			<div class="frozr-gravatar-img" style="background-image: url(<?php echo esc_url( $gravatar_url ); ?>);"></div>
			<a class="close frozr-remove-gravatar-image"><?php _e('Change','frozr-norsani'); ?></a>
		</div>
		<div class="gravatar-button-area<?php echo $gravatar ? ' frozr_hide' : ''; ?>">
			<i class="material-icons">add_a_photo</i>
			<a href="#" class="frozr-gravatar-drag btn btn-info"><?php _e( 'Upload Logo', 'frozr-norsani' ); ?></a>
		</div>
	</div>
	<a class="settings-store-name" href="<?php echo frozr_get_store_url( $current_user ); ?>"><?php echo $storename; ?></a>
</div>
</div>
<div data-role="tabs"class="frozr_dash_seller_settings_wrapper">
	<ul data-role="listview" data-inset="true" class="tablist-left">
		<?php do_action('frozr_before_rest_set_tabs'); ?>
		<li><a href="#usr_gen_opts" class="ui-icon-gear active" data-ajax="false"><?php _e('General Settings','frozr-norsani'); ?></a></li>
		<?php if (!get_option('frozr_hide_menus')) { ?>
		<li><a href="#usr_meal_types_opts" class="ui-icon-cutlery" data-ajax="false"><?php _e( 'Menus', 'frozr-norsani' ); ?></a></li>
		<?php } ?>
		<li><a href="#usr_orders_opts" class="ui-icon-gear" data-ajax="false"><?php _e( 'Orders Settings', 'frozr-norsani' ); ?></a></li>
		<li><a href="#usr_delivery_opts" class="ui-icon-gear frozr_vendor_delivery_opts" data-ajax="false"><?php _e( 'Delivery Settings', 'frozr-norsani' ); ?></a></li>
		<li><a href="#usr_notices_opts" class="ui-icon-comment" data-ajax="false"><?php _e( 'Shop Notice', 'frozr-norsani' ); ?></a></li>
		<li><a href="#usr_social_profile" class="ui-icon-user" data-ajax="false"><?php _e( 'Social Settings', 'frozr-norsani' ); ?></a></li>
		<?php if (!frozr_manual_vendor_online()) {?>
		<li><a href="#usr_opcl_opts" class="ui-icon-clock" data-ajax="false"><?php _e( 'Open/Close Timings', 'frozr-norsani' ); ?></a></li>
		<?php } ?>
		<li><a href="#usr_with_opts" class="ui-icon-alert" data-ajax="false"><?php _e( 'Withdrawal Methods', 'frozr-norsani' ); ?></a></li>
		<?php do_action('frozr_after_rest_set_tabs'); ?>
	</ul>
	<?php do_action('frozr_before_user_front_options'); ?>
	<div id="usr_gen_opts" class="group-opts active">
		<span class="vendor_form_group_label"><?php _e( 'General Info', 'frozr-norsani' ); ?></span>
		<?php do_action('frozr_before_user_general_options'); ?>
		<div class="frozr_options_group">
		<div class="form-group">
			<label class="form-group control-label" for="frozr_store_type"><?php _e( 'Shop type', 'frozr-norsani' ); frozr_inline_help_db('set_store_type'); ?></label>
			<input id="frozr_store_type" disabled value="<?php echo isset($default_types[$vendor_type]) ? $default_types[$vendor_type] : $vendor_type; ?>" name="frozr_store_type" class="form-control input-md" type="text">
			<?php do_action('frozr_after_vendor_type_input'); ?>
		</div>
		<div class="form-group">
			<label class="form-group control-label" for="frozr_store_name"><?php _e( 'Shop name', 'frozr-norsani' ); frozr_inline_help_db('set_store_name'); ?></label>
			<input id="frozr_store_name" required value="<?php echo $storename; ?>" name="frozr_store_name" placeholder="<?php _e('e.g. BOB\'s Pizza','frozr-norsani');  ?>" class="form-control input-md" type="text">
			<?php do_action('frozr_after_vendor_name_input'); ?>
		</div>
		<div class="form-group">
			<label class="form-group control-label" for="setting_phone"><?php _e( 'Contact number', 'frozr-norsani' ); frozr_inline_help_db('set_store_tel'); ?></label>
			<input id="setting_phone" value="<?php echo $phone; ?>" autocomplete='tel' name="setting_phone" placeholder="<?php _e('e.g. +123 456 789','frozr-norsani'); ?>" class="form-control input-md" type="text">
			<?php do_action('frozr_after_vendor_phone_input'); ?>
		</div>
		<div class="form-group">
			<label class="form-group control-label" for="setting_address"><?php _e( 'Shop address', 'frozr-norsani' ); frozr_inline_help_db('set_store_address'); ?></label>
			<div class="group_settings_input">
				<input class="form-control" autocomplete='street-address' data-geo="<?php echo $geo_loc; ?>" id="setting_address" value= "<?php echo norsani()->vendor->frozr_get_vendor_address($current_user); ?>" name="setting_address" placeholder="<?php _e('e.g. In front of cinema, Fourth floor, Seasons mall','frozr-norsani'); ?>" type="text">
				<?php do_action('frozr_after_vendor_address_input'); ?>
			</div>
		</div>
		<div class="form-group">
			<span class="form-group control-label"><?php _e( "Shop's location on map", "frozr-norsani" ); frozr_inline_help_db('set_store_geo'); ?></span>
			<div class="group_settings_input">
				<div id="setting_address_map"></div>
				<?php do_action('frozr_after_vendor_location_input'); ?>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label" for="frozr_rest_tags"><?php _e( 'Shop\'s tags', 'frozr-norsani' ); frozr_inline_help_db('set_store_tags'); ?></label>
			<input id="frozr_rest_tags" required name="frozr_rest_tags" value="<?php echo $grestypes; ?>" type="text" placeholder="<?php _e( 'i.e Italian, Indian, Fast Food, Fine Dining .. etc.','frozr-norsani'); ?>">
		</div>
		<?php /*
		<div class="form-group">
			<span class="control-label"><?php echo __( 'Available food types', 'frozr-norsani' ); frozr_inline_help_db('set_store_food_type'); ?></span>
			<div class="frozr_set_food_type">
				<label for="rest_food_type_veg"><?php _e( 'Veg.', 'frozr-norsani' ); ?>
					<input id="rest_food_type_veg" type="checkbox" name="rest_food_type[0]" value="veg" <?php checked( $rest_food_type[0], 'veg' ); ?>>
				</label>
				<label for="rest_food_type_nonveg"><?php _e( 'Non-Veg.', 'frozr-norsani' ); ?>
					<input id="rest_food_type_nonveg" type="checkbox" name="rest_food_type[1]" value="nonveg" <?php checked( $rest_food_type[1], 'nonveg' ); ?>>
				</label>
				<label for="rest_food_type_seafood"><?php _e( 'Sea Food.', 'frozr-norsani' ); ?>
					<input id="rest_food_type_seafood" type="checkbox" name="rest_food_type[2]" value="sea-food" <?php checked( $rest_food_type[2], 'sea-food' ); ?>>
				</label>
			</div>
		</div>
		*/ ?>
		<div class="form-group checkbox">
			<label class="control-label" for="rest_allow_email" >
			<input id="rest_allow_email" type="checkbox" name="setting_allow_email" value="1" <?php checked( $allow_email, 1 ); ?>>
			<?php _e( 'Allow receiving emails from the shop page.', 'frozr-norsani' ); frozr_inline_help_db('set_store_email'); ?></label>
		</div>
		<?php do_action('frozr_after_user_general_options'); ?>
		</div>
	</div>
	<?php if (!get_option('frozr_hide_menus')) { ?>
	<div id="usr_meal_types_opts" class="group-opts">
		<span class="vendor_form_group_label"><?php _e( 'Menus', 'frozr-norsani' ); frozr_inline_help_db('set_menus'); ?></span>
		<div class="form-group metyps_settings">
		<div class="multi-field-wrapper">
			<div class="multi-fields">
				<?php if (is_array($filterd_metyps) && !empty($filterd_metyps_opts)) { foreach ($filterd_metyps as $mvals){ ?>
				<div class="multi-field">
					<label><?php _e( 'Menu title', 'frozr-norsani' ); frozr_inline_help_db('set_menu_title'); ?>
					<input value="<?php echo !empty($mvals['title']) ? $mvals['title'] : ''; ?>" name="rest_meal_types[][title]" class="rest_meal_types form-control" type="text" placeholder="<?php _e('Meal Type. ie. Breakfast, Lunch & Dinner','frozr-norsani'); ?>">
					</label>
					<label><?php _e( 'Start time', 'frozr-norsani' ); frozr_inline_help_db('set_menu_star'); ?>
					<input value="<?php echo !empty($mvals['start']) ? $mvals['start'] : ''; ?>" name="rest_meal_types[][start]" class="rest_meal_types form-control" type="time" placeholder="<?php _e('Start time (24 hours format 00:00)','frozr-norsani'); ?>">
					</label>
					<label><?php _e( 'End time', 'frozr-norsani' ); frozr_inline_help_db('set_menu_end'); ?>
					<input value="<?php echo !empty($mvals['end']) ? $mvals['end'] : ''; ?>" name="rest_meal_types[][end]" class="rest_meal_types form-control" type="time" placeholder="<?php _e('End time (24 hours format 00:00)','frozr-norsani'); ?>">
					</label>
					<i class="remove-field material-icons">close</i>
				</div>
				<?php } } else { ?>
				<div class="multi-field">
					<label><?php _e( 'Menu title', 'frozr-norsani' ); frozr_inline_help_db('set_menu_title'); ?>
					<input value="" name="rest_meal_types[][title]" class="rest_meal_types form-control" type="text" placeholder="<?php _e('Meal Type. ie. Breakfast, Lunch & Dinner','frozr-norsani'); ?>">
					</label>
					<label><?php _e( 'Start time', 'frozr-norsani' ); frozr_inline_help_db('set_menu_star'); ?>
					<input value="" name="rest_meal_types[][start]" class="rest_meal_types form-control" type="time" placeholder="<?php _e('Start time (24 hours format 00:00)','frozr-norsani'); ?>">
					</label>
					<label><?php _e( 'End time', 'frozr-norsani' ); frozr_inline_help_db('set_menu_end'); ?>
					<input value="" name="rest_meal_types[][end]" class="rest_meal_types form-control" type="time" placeholder="<?php _e('End time (24 hours format 00:00)','frozr-norsani'); ?>">
					</label>
					<i class="remove-field material-icons">close</i>
				</div>
				<?php } ?>
			</div>
			<button type="button" class="add-field"><?php _e('Add new menu','frozr-norsani'); ?></button>
		</div>
		</div>
	</div>
	<?php } ?>
	<div id="usr_orders_opts" class="group-opts">
		<span class="vendor_form_group_label"><?php _e( 'Orders', 'frozr-norsani' ); ?></span>
		<?php do_action('frozr_before_user_orders_options'); ?>
		<div class="frozr_options_group">
		<div class="form-group">
			<label class="control-label" for="accept_order_types"><?php _e('Accepted orders','frozr-norsani'); frozr_inline_help_db('set_orders_accepted'); ?></label>
			<select name="accept_order_types" id="accept_order_types" multiple="multiple" data-native-menu="false">
				<?php $frozr_accepted_orders = frozr_default_accepted_orders_types();
				foreach ($frozr_accepted_orders as $val) {
					echo '<option value="'.$val.'"' . ( in_array( $val, $orders_accept ) ? 'selected="selected"' : '' ) . '>' . frozr_get_order_type_name( $val ) . '</option>';
				} ?>
				<?php do_action('frozr_after_accepted_order_types_select_option', $orders_accept); ?>
			</select>
		</div>
		<?php	if (!frozr_manual_vendor_online()) { ?>
		<div class="form-group checkbox">
			<label class="control-label" for="rest_allow_ords">
			<input id="rest_allow_ords" type="checkbox" name="setting_allow_ofline_orders" value="yes"<?php checked( $allow_ofline_orders, 'yes' ); ?>>
			<?php _e( 'Allow orders even when your shop is closed?', 'frozr-norsani' ); frozr_inline_help_db('set_orders_accepted_offline_check'); ?></label>
		</div>
		<div class="form-group frozr_dash_offline_orders_wrapper<?php if (!$allow_ofline_orders) {echo ' frozr_hide';} ?>">
			<label class="control-label" for="accept_order_types_cl"><?php _e('Accepted orders when closed','frozr-norsani'); frozr_inline_help_db('set_orders_accepted_offline'); ?></label>
			<select name="accept_order_types_cl" id="accept_order_types_cl" multiple="multiple" data-native-menu="false">
				<?php $frozr_accepted_orders = frozr_default_accepted_orders_types_closed();
				foreach ($frozr_accepted_orders as $val) {
					echo '<option value="'.$val.'"' . ( in_array( $val, $orders_accept_cl ) ? 'selected="selected"' : '' ) . '>' . frozr_get_order_type_name( $val ) . '</option>';
				} ?>
				<?php do_action('frozr_after_accepted_order_types_while_closed_select_option', $orders_accept_cl); ?>
			</select>
		</div>
		<?php } ?>
		<div class="form-group">
			<label class="control-label"><?php echo __( 'Calculate order preparation time percentage', 'frozr-norsani' ); frozr_inline_help_db('calculate_prepare_time'); ?></label>
			<input id="calculate_prepare_time" value="<?php echo $calculate_prepare_time; ?>" name="calculate_prepare_time" class="form-control" placeholder="0.0" type="number" min="0" max="100" step="any">
		</div>
		<?php do_action('frozr_after_user_orders_options'); ?>
		</div>
	</div>
	<div id="usr_delivery_opts" class="group-opts">
		<span class="vendor_form_group_label"><?php _e( 'Delivery settings.', 'frozr-norsani' ); ?></span>
		<div class="frozr_options_group">
		<?php if (!in_array( 'delivery', $orders_accept )) { ?>
		<div class="frozr_dash_notice"><?php echo __('You have chosen not to provide delivery service. You can change that from your orders settings.','frozr-norsani'); ?></div>
		<?php } ?>
		<?php do_action('frozr_before_user_delivery_options'); ?>
		<div class="form-group">
			<span class="control-label"><?php _e( 'Delivery zone', 'frozr-norsani' ); frozr_inline_help_db('set_delivery'); ?></span>
			<div class="group_settings_input">
				<div id="delivery_locations_map" data-poly="<?php if (!empty($delivery_meta)) { echo implode('/',$delivery_meta);}?>" data-polyfilterd="<?php if (!empty($delunfilterd)) { echo implode('/',$delunfilterd);}?>" class="frozr_loc_map_div"></div>
				<a href="#!" class="frozr_clear_loc_map" title="<?php _e('Erase drawing','frozr-norsani'); ?>"><i class="material-icons">clear</i></a>
			</div>
		</div>
		<div class="form-group">
			<span class="control-label"><?php echo __( 'Calculate delivery fee by', 'frozr-norsani' ); frozr_inline_help_db('set_delivery_by'); ?></span>
			<div class="delivey_by_options">
				<label for="deliveryby_order">
					<input id="deliveryby_order" name="deliveryby" value="order" <?php checked( $deliveryby, 'order' ); ?> type="radio">
					<?php _e( 'Order', 'frozr-norsani' ); ?>
				</label>
				<label for="deliveryby_item">
					<input id="deliveryby_item" name="deliveryby" value="item" <?php checked( $deliveryby, 'item' ); ?> type="radio">
					<?php _e( 'Product', 'frozr-norsani' ); ?>
				</label>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label" for="shipping_fee"><?php echo __( 'Delivery fee', 'frozr-norsani' ) .' '. get_woocommerce_currency_symbol(); frozr_inline_help_db('set_delivery_fee'); ?></label>
			<input id="shipping_fee" value="<?php echo $shipping_fee; ?>" name="shipping_fee" class="form-control" placeholder="0.0" type="number" min="0" step="any">
		</div>
		<div class="form-group<?php if ($deliveryby == 'order') { echo ' frozr_hide';} ?>">
			<label class="control-label" for="shipping_pro_adtl_cost"><?php echo __( 'Fee per additional product', 'frozr-norsani' ) .' '. get_woocommerce_currency_symbol(); frozr_inline_help_db('set_delivery_peritem'); ?></label>
			<input id="shipping_pro_adtl_cost" value="<?php echo $shipping_pro_adtl_cost; ?>" name="shipping_pro_adtl_cost" class="form-control" placeholder="0.0" type="number" min="0" step="any">
		</div>
		<div class="form-group">
			<label class="control-label" for="min_order_amt"><?php echo __( 'Minimum order amount for delivery', 'frozr-norsani' ) .' '. get_woocommerce_currency_symbol(); frozr_inline_help_db('set_delivery_minord'); ?></label>
			<input id="min_order_amt" value="<?php echo $min_order_amt; ?>" name="min_order_amt" class="form-control" placeholder="0.0" type="number" min="0" step="any">
		</div>
		<div class="frozr_delivery_set_on_peak">
		<span class="vendor_form_group_label"><?php _e( 'Delivery on peak times', 'frozr-norsani' ); frozr_inline_help_db('set_delivery_peak'); ?></span>
		<div class="frozr_options_group">
		<div class="form-group">
			<label class="control-label" for="peak_orders"><?php echo __( 'Processing orders', 'frozr-norsani' ); frozr_inline_help_db('set_delivery_po'); ?></label>
			<input id="peak_orders" value="<?php echo $peak_orders; ?>" name="peak_orders" class="form-control" placeholder="0" type="number" min="0" step="any">
		</div>
		<div class="form-group">
			<span class="control-label"><?php echo __( 'Calculate delivery fee by', 'frozr-norsani' ); frozr_inline_help_db('set_delivery_by'); ?></span>
			<div class="delivey_by_options">
				<label>
					<input name="deliveryby_peak" value="order" <?php checked( $deliveryby_peak, 'order' ); ?> type="radio">
					<?php _e( 'Order', 'frozr-norsani' ); ?>
				</label>
				<label>
					<input name="deliveryby_peak" value="item" <?php checked( $deliveryby_peak, 'item' ); ?> type="radio">
					<?php _e( 'Product', 'frozr-norsani' ); ?>
				</label>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label" for="shipping_fee_peak"><?php echo __( 'Delivery fee', 'frozr-norsani' ) .' '. get_woocommerce_currency_symbol(); frozr_inline_help_db('set_delivery_fee'); ?></label>
			<input id="shipping_fee_peak" value="<?php echo $shipping_fee_peak; ?>" name="shipping_fee_peak" class="form-control" placeholder="0.0" type="number" min="0" step="any">
		</div>
		<div class="form-group<?php if ($deliveryby_peak == 'order') { echo ' frozr_hide';} ?>">
			<label class="control-label" for="shipping_pro_adtl_cost_peak"><?php echo __( 'Fee per additional product', 'frozr-norsani' ) .' '. get_woocommerce_currency_symbol(); frozr_inline_help_db('set_delivery_peritem'); ?></label>
			<input id="shipping_pro_adtl_cost_peak" value="<?php echo $shipping_pro_adtl_cost_peak; ?>" name="shipping_pro_adtl_cost_peak" class="form-control" placeholder="0.0" type="number" min="0" step="any">
		</div>
		<div class="form-group">
			<label class="control-label" for="min_order_amt_peak"><?php echo __( 'Minimum order amount for delivery', 'frozr-norsani' ) .' '. get_woocommerce_currency_symbol(); frozr_inline_help_db('set_delivery_minord'); ?></label>
			<input id="min_order_amt_peak" value="<?php echo $min_order_amt_peak; ?>" name="min_order_amt_peak" class="form-control" placeholder="0.0" type="number" min="0" step="any">
		</div>
		</div>
		</div>
		<?php do_action('frozr_after_user_delivery_options'); ?>
		</div>
	</div>
	<div id="usr_notices_opts" class="group-opts">
		<span class="vendor_form_group_label"><?php _e( "Shop page notification", 'frozr-norsani' ); frozr_inline_help_db('set_store_notice'); ?></span>
		<div class="frozr_options_group">
		<div class="form-group">
			<label class="control-label" for="frozr_vendor_store_notice"><?php _e( 'Add message', 'frozr-norsani' ); ?></label>
			<textarea name="frozr_vendor_store_notice" cols="25" rows="6" placeholder="<?php esc_attr_e( 'Type your note...', 'frozr-norsani' ); ?>" class="form-control"><?php echo $user_notice; ?></textarea>
		</div>
		</div>
	</div>
	<div id="usr_social_profile" class="group-opts">
		<span class="vendor_form_group_label"><?php _e( 'Social accounts', 'frozr-norsani' ); frozr_inline_help_db('set_social'); ?></span>
		<?php do_action('frozr_before_user_social_profile_options'); ?>
		<div class="frozr_options_group">
		<div class="form-group">
			<label class="control-label" for="socialfb"><?php echo __( 'Facebook', 'frozr-norsani' ); ?></label>
			<input id="socialfb" value="<?php echo $fb; ?>" name="socialfb" class="form-control" placeholder="http://" type="url">
		</div>
		<div class="form-group">
			<label class="control-label" for="socialtwitter"><?php echo __( 'Twitter', 'frozr-norsani' ); ?></label>
			<input id="socialtwitter" value="<?php echo $twitter; ?>" name="socialtwitter" class="form-control" placeholder="http://" type="url">
		</div>
		<div class="form-group">
			<label class="control-label" for="socialyoutube"><?php echo __( 'Youtube', 'frozr-norsani' ); ?></label>
			<input id="socialyoutube" value="<?php echo $youtube; ?>" name="socialyoutube" class="form-control" placeholder="http://" type="url">
		</div>
		<div class="form-group">
			<label class="control-label" for="socialinsta"><?php echo __( 'Instagram', 'frozr-norsani' ); ?></label>
			<input id="socialinsta" value="<?php echo $instagram; ?>" name="socialinsta" class="form-control" placeholder="http://" type="url">
		</div>
		<?php do_action('frozr_after_user_social_profile_options'); ?>
		</div>
	</div>
	<?php if (!frozr_manual_vendor_online()) { ?>
	<div id="usr_opcl_opts" class="group-opts">
		<span class="vendor_form_group_label"><?php _e( 'Shop\'s timings', 'frozr-norsani' ); frozr_inline_help_db('set_timing'); ?></span>
		<div class="frozr_options_group">
		<?php do_action('frozr_before_user_opening_options'); ?>
		<?php foreach ($opxlar as $k => $vk) {
		$opxlxx = norsani()->vendor->frozr_vendor_timing($opxlarx[$opxlnum]); ?>
		<div class="form-group opcl_settings">
			<div class="control_label_group">
				<span class="control-label rest_<?php echo $k; ?>_opening"><strong><?php echo $vk; ?></strong></span>&nbsp;
				<label for="rest_<?php echo $k; ?>_open">
					<input id="rest_<?php echo $k; ?>_open" name="rest_<?php echo $k; ?>_open" type="checkbox" class="rest_open" value="yes" <?php checked( $opxlxx[0], 'yes' ); ?> />
					<?php _e( 'Open', 'frozr-norsani' ); ?>
				</label>
				<label for="rest_<?php echo $k; ?>_shifts" class="rest_shifts_cont <?php if($opxlxx[0] != 'yes') { echo 'frozr_hide';} ?>">
					<input name="rest_<?php echo $k; ?>_shifts" type="checkbox" class="rest_shifts" value="yes" <?php checked( $opxlxx[1], 'yes' ); ?> />
					<?php _e( 'Two shifts', 'frozr-norsani' ); ?>
				</label>
				<?php frozr_inline_help_db('set_time'); ?>
				<?php do_action('frozr_after_store_timing_checkboxes', $k ,$vk); ?>
			</div>
			<div class="opt_opts">
				<div class="rest_time_inputs <?php if($opxlxx[0] != 'yes') { echo 'frozr_hide';} ?>">
					<div class="rest_one">
						<div>
						<label class="control-label" for="rest_<?php echo $k; ?>_opening_one"><?php _e( 'Opening time for first shift', 'frozr-norsani' ); frozr_inline_help_db('set_time_inputs'); ?></label>
						<input id="rest_<?php echo $k; ?>_opening_one" value="<?php echo $opxlxx[2]; ?>" name="rest_<?php echo $k; ?>_opening_one" class="form-control" type="time" placeholder="<?php _e('Start time (24 hour format 00:00)','frozr-norsani'); ?>">
						</div>
						<div>
						<label class="control-label" for="rest_<?php echo $k; ?>_closing_one"><?php _e( 'Closing time for first shift', 'frozr-norsani' ); frozr_inline_help_db('set_time_inputs'); ?></label>
						<input id="rest_<?php echo $k; ?>_closing_one" value="<?php echo $opxlxx[3]; ?>" name="rest_<?php echo $k; ?>_closing_one" class="form-control" type="time" placeholder="<?php _e('End time (24 hour format 00:00)','frozr-norsani'); ?>">
						</div>
					</div>
					<div class="rest_two <?php if($opxlxx[1] != 'yes') { echo 'frozr_hide';} ?>">
						<div>
						<label class="control-label" for="rest_<?php echo $k; ?>_opening_two"><?php _e( 'Opening time for second shift', 'frozr-norsani' ); frozr_inline_help_db('set_time_inputs'); ?></label>
						<input id="rest_<?php echo $k; ?>_opening_two"  value="<?php echo $opxlxx[4]; ?>" name="rest_<?php echo $k; ?>_opening_two" class="form-control" type="time" placeholder="<?php _e('Start time (24 hour format 00:00)','frozr-norsani'); ?>">
						</div>
						<div>
						<label class="control-label" for="rest_<?php echo $k; ?>_closing_two"><?php _e( 'Closing time for second shift', 'frozr-norsani' ); frozr_inline_help_db('set_time_inputs'); ?></label>
						<input id="rest_<?php echo $k; ?>_closing_two" value="<?php echo $opxlxx[5]; ?>" name="rest_<?php echo $k; ?>_closing_two" class="form-control" type="time" placeholder="<?php _e('End time (24 hour format 00:00)','frozr-norsani'); ?>">
						</div>
					</div>
				</div>
				<?php do_action('frozr_after_store_timing_dates', $k ,$vk); ?>
			</div>
		</div>
		<?php $opxlnum++; } ?>
		<div class="form-group opcl_settings frozr_unavailable_dates">
			<span><strong><?php _e( 'Unavailable Dates (mm/dd/yyyy)', 'frozr-norsani' ); frozr_inline_help_db('set_time_unava'); ?></strong></span>
			<div class="multi-field-wrapper">
				<div class="multi-fields">
					<?php if (!empty($filterd_inds_opts)) { foreach($filterd_inds as $vals){ ?>
					<div class="multi-field">
						<div>
						<label class="control-label"><?php _e( 'Starting date', 'frozr-norsani' ); ?>
						<input value="<?php echo !empty($vals['start']) ? $vals['start'] : ''; ?>" name="rest_unads[][start]" class="rest_unads form-control" type="date" placeholder="<?php _e('Start Date YYYY/MM/DD','frozr-norsani'); ?>">
						</label>
						</div>
						<div>
						<label class="control-label"><?php _e( 'Ending date', 'frozr-norsani' ); ?>
						<input value="<?php echo !empty($vals['end']) ? $vals['end'] : ''; ?>" name="rest_unads[][end]" class="rest_unads form-control" type="date" placeholder="<?php _e('End Date YYYY/MM/DD','frozr-norsani'); ?>">
						</label>
						</div>
						<i class="remove-field material-icons">close</i>
					</div>
					<?php } } else { ?>
					<div class="multi-field">
						<div>
						<label class="control-label"><?php _e( 'Starting date', 'frozr-norsani' ); ?>
						<input value="" name="rest_unads[][start]" class="rest_unads form-control" type="date" placeholder="<?php _e('Start Date YYYY/MM/DD','frozr-norsani'); ?>">
						</label>
						</div>
						<div>
						<label class="control-label"><?php _e( 'Ending date', 'frozr-norsani' ); ?>
						<input value="" name="rest_unads[][end]" class="rest_unads form-control" type="date" placeholder="<?php _e('End Date YYYY/MM/DD','frozr-norsani'); ?>">
						</label>
						</div>
						<i class="remove-field material-icons">close</i>
					</div>
					<?php } ?>
				</div>
				<button type="button" class="add-field"><?php _e('Add new date','frozr-norsani'); ?></button>
			</div>
		</div>
		<?php do_action('frozr_after_user_opening_options'); ?>
		</div>
	</div>
	<?php } ?>
	<div id="usr_with_opts" class="group-opts">
		<span class="vendor_form_group_label"><?php _e( 'Withdrawal settings', 'frozr-norsani' ); frozr_inline_help_db('set_withdraw'); ?></span>
		<div class="frozr_options_group">
		<?php do_action('frozr_before_user_withdraw_options'); ?>
		<?php foreach ($withdrawl_methods as $method_key) {
			$method = norsani()->withdraw->frozr_withdraw_get_method( $method_key ); ?>
			<?php if ( is_callable( $method['callback']) ) {
				call_user_func( $method['callback'], $profile_info );
			} ?>
		<?php } ?>
		<?php do_action('frozr_after_user_withdraw_options'); ?>
		</div>
	</div>
	<?php do_action('frozr_after_user_front_options'); ?>
</div>
<div class="form-group-settings submit-frozr-settings">
	<button id="frozr_update_profile"><?php esc_attr_e('Update Settings','frozr-norsani'); ?></button>
</div>
</form>
<script type="text/javascript">
jQuery(function($) {
var poly;
var map;

function polygonCenter(poly) {
	var lowx,
		highx,
		lowy,
		highy,
		lats = [],
		lngs = [],
		vertices = poly.getPath();

	for(var i=0; i<vertices.length; i++) {
		lngs.push(vertices.getAt(i).lng());
		lats.push(vertices.getAt(i).lat());
	}

	lats.sort();
	lngs.sort();
	lowx = lats[0];
	highx = lats[vertices.length - 1];
	lowy = lngs[0];
	highy = lngs[vertices.length - 1];
	center_x = lowx + ((highx-lowx) / 2);
	center_y = lowy + ((highy - lowy) / 2);
	return (new google.maps.LatLng(center_x, center_y));
}
function initialize(region, locs, divid) {

	var geocoder = new google.maps.Geocoder;
	var addres = $('#setting_address').val();
	var marker_loc = $('#setting_address').attr('data-geo');
	var default_address = region;
	if (addres) {
		default_address = addres;
	}
	map = new google.maps.Map(document.getElementById(divid), {
		zoom: 11,
	});

	poly = new google.maps.Polygon({
	paths: [locs],
	strokeColor: '#000000',
	strokeOpacity: 1.0,
	editable: true,
	draggable: true,
	strokeWeight: 3,
	fillColor: '#FF0000',
	fillOpacity: 0.35
	});
	poly.setMap(map);
	
	/* Get the center of the poly*/
	var map_center = polygonCenter(poly);
	var marker_pos = map_center;
	if (marker_loc) {
	var marker_array = marker_loc.split(',');

	marker_pos = new google.maps.LatLng(marker_array[0], marker_array[1]);
	}

	if (poly.getPath().length > 0) {
		map.setCenter(map_center);
		new google.maps.Marker({
		  map: map,
		  position: marker_pos
		});
	} else {
		geocoder.geocode({'address': default_address}, function(results, status) {
		  if (status === 'OK') {
			if (!marker_loc) {
				marker_pos = results[0].geometry.location;
			}
			map.setCenter(results[0].geometry.location);
			new google.maps.Marker({
			  map: map,
			  position: marker_pos
			});
		  } else {
			window.alert('<?php echo __('Geocode was not successful for the following reason:','frozr-norsani'); ?> '+ status);
		  }
		});
	}			

	map.addListener('click', addLatLng);
	google.maps.event.addListener(poly.getPath(), 'set_at', editLatLng);
	google.maps.event.addListener(poly.getPath(), 'insert_at', editLatLng);
	google.maps.event.addListener(poly.getPath(), 'remove_at', editLatLng);

	/* Add a listener for the click event*/
	function addLatLng(event) {
		var path = poly.getPath();
		var contentString ='';
		var contentStringFilterd ='';
		path.push(event.latLng);

		for (var i =0; i < path.getLength(); i++) {
			var xy = path.getAt(i);
			contentString += '{lat:' + xy.lat() + ', lng:' + xy.lng() + '}/';
			contentStringFilterd += xy.lat() + ',' + xy.lng() + '/';
		}

		$('#'+divid).attr({'data-poly':contentString,'data-polyfilterd':contentStringFilterd});
	}
	/* edit a listener for the edit event*/
	function editLatLng() {
		var path = poly.getPath();
		var contentString ='';
		var contentStringFilterd ='';
		for (var i =0; i < path.getLength(); i++) {
			var xy = path.getAt(i);
			contentString += '{lat:' + xy.lat() + ', lng:' + xy.lng() + '}/';
			contentStringFilterd += xy.lat() + ',' + xy.lng() + '/';
		}
		$('#'+divid).attr({'data-poly':contentString,'data-polyfilterd':contentStringFilterd});
	}
}
	
$(document).on('click', '.frozr_clear_loc_map', function () {
	poly.setMap(null);
	poly = new google.maps.Polygon({
	strokeColor: '#000000',
	strokeOpacity: 1.0,
	editable: true,
	draggable: true,
	strokeWeight: 3,
	fillColor: '#FF0000',
	fillOpacity: 0.35
	});
	poly.setMap(map);
	$('#delivery_locations_map').attr({'data-poly':'','data-polyfilterd':''});
});
$(document.body).on('click', '.frozr_vendor_delivery_opts', function() {
google.maps.event.addDomListener(window, 'load', initialize('<?php echo frozr_get_default_country(true); ?>', [<?php echo $delivery_locations; ?>], 'delivery_locations_map'));
});
$('#frozr_rest_tags').tagator({
	autocomplete: [<?php echo $allgrestypes; ?>]
});
var marker,amap;
function initialize_address_map() {
	/*Create the map*/
	var geocoder = new google.maps.Geocoder;
	amap = new google.maps.Map(document.getElementById('setting_address_map'), {
	zoom: 17,
	});
	var current_loc = $('#setting_address').attr('data-geo');
	/*Center the map*/
	
	if (current_loc) {
		var latlngc = current_loc.split(',');
		var mapc = new google.maps.LatLng(latlngc[0], latlngc[1]);
		amap.setCenter(mapc);
		marker = new google.maps.Marker({
		  map: amap,
		  position: mapc
		});
	} else {
	geocoder.geocode({'address': '<?php echo frozr_get_default_country(); ?>'}, function(results, status) {
	  if (status === 'OK') {
		amap.setCenter(results[0].geometry.location);
	  } else {
		window.alert('<?php echo __( 'Error: The Geolocation service failed.', 'frozr-norsani' ); ?>'+': ' + status);
	  }
	});
	}

	// This event listener calls addMarker() when the map is clicked.
	google.maps.event.addListener(amap, 'click', function(event) {
	addMarker(event.latLng, amap);
	geocoder.geocode({'latLng': event.latLng}, function(results, status) {
	  if (status === 'OK') {
		$('#setting_address').val(results[0].formatted_address);
	  }
	});
	});
}

// Adds a marker to the map.
function addMarker(location,amap,cent) {
	/*clear current marker*/
	if (marker) {
	marker.setMap(null);
	}
	if(cent) {
	amap.setCenter(location);
	}
	marker = new google.maps.Marker({
	position: location,
	map: amap
	});
	/*add location info to input*/
	$('#setting_address').attr('data-geo', location.lat()+','+location.lng());
}
google.maps.event.addDomListener(window, 'load', initialize_address_map());

/*add auto complete for vendor address*/
var autocomplete,
	input = document.getElementById('setting_address'),
	options = {
	types: ['address'],
	componentRestrictions: {country: '<?php echo frozr_get_default_country(); ?>'},
	};

autocomplete = new google.maps.places.Autocomplete(input, options);
autocomplete.addListener('place_changed', function() {
	var place_obj = autocomplete.getPlace();
	var latlng = place_obj.geometry.location;
	$('#setting_address').attr('data-geo', latlng.lat()+','+latlng.lng());
	addMarker(latlng,amap,true);
});
});
</script>