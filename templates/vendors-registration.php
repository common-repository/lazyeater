<?php
/**
 * Template Name: Frozr vendor registration page
 *
 * @package Norsani/Templates
 */

// calling the header.php
get_header();

$fle_gen_option = get_option( 'frozr_gen_settings' );
$fle_tos_option = get_option( 'frozr_tos_settings' );
$seller_tos = (! empty( $fle_tos_option['frozr_tos_sellers']) ) ? $fle_tos_option['frozr_tos_sellers'] : 0;
$customers_tos = (! empty( $fle_tos_option['frozr_tos_customers']) ) ? $fle_tos_option['frozr_tos_customers'] : 0;
$default_types = frozr_get_default_vendors_types();
$allowed_vendor_types = frozr_get_allowed_vendors_types();

/*get all types*/
$getalltyps= get_terms( 'vendorclass', 'fields=names&hide_empty=0' );
$rtys_slug = array();
if ( ! empty( $getalltyps ) && ! is_wp_error( $getalltyps ) ){
foreach ( $getalltyps as $term ) {
$rtys_slug[] = $term;
}
$allgrestypes = '"'.join( '"," ', $rtys_slug ).'"';
}
	
if (frozr_is_using_geolocation()) {
	$delivery_ins = __( 'Click on the map to start drawing the path for a polygon representing the area that you can provide delivery service.','frozr-norsani');
	$address_ins =  __( 'Type first two/three letters and choose from list, if you don\'t find the addresses in list or the list does not appear, make sure of the address spelling.', 'frozr-norsani' );
} else {
	/*get user locations*/
	$getallocs = get_terms( 'location', 'fields=names&hide_empty=0' );
	/*get all locations*/
	$locs_slug = array();
	if ( ! empty( $getallocs ) && ! is_wp_error( $getallocs ) ){
		 foreach ( $getallocs as $term ) {
		   $locs_slug[] = $term;
		}
		$allocs = '"'.join( '"," ', $locs_slug ).'"';
	}
	$delivery_ins = __( 'Road/Street names. Type first two/three letters and choose from list, if the list doesn\'t appear then complete typing and hit the comma button.', 'frozr-norsani' );
	$address_ins =  __( 'Type first two/three letters and choose from list, if the list doesn\'t appear, then complete typing normally.', 'frozr-norsani' );
}

/*get all addresses*/
$getallads= get_terms( 'vendor_addresses', 'fields=names&hide_empty=0' );
$addresses_slug = array();
if ( ! empty( $getallads ) && ! is_wp_error( $getallads ) ){
foreach ( $getallads as $term ) {
$addresses_slug[] = $term;
}
$alladdresses = '"'.join( '"," ', $addresses_slug ).'"';
}
?>
<div data-role="page" id="seller_reg_page" class="content-area">
<div <?php body_class(); ?> data-id="finde" style="display:none"><?php echo get_the_title($post->ID); ?></div>
<main class="site-main<?php if(frozr_mobile()) {echo ' frozr_mobile_reg_form';} ?>" role="main">
<?php
// Start the loop.
while ( have_posts() ) : the_post();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	// Post thumbnail.
	frozr_post_thumbnail();
	?>
	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
	</header><!-- .entry-header -->
	<?php if ( ! is_user_logged_in() ) { ?>
	<ul class="frozr_reg_forms_menu<?php if ($seller_tos) {echo ' frozr_hide';} ?>">
		<li data-id="frozr_reg_gen_info_menu" class="frozr_reg_active_menu"><i class="material-icons">account_circle</i><?php _e('General','frozr-norsani'); ?></li>
		<?php if (!get_option('frozr_hide_menus')) { ?>
		<li data-id="frozr_reg_menus_menu"><i class="material-icons">turned_in</i><?php _e('Menus','frozr-norsani'); ?></li>
		<?php } ?>
		<li data-id="frozr_reg_del_menu" style="display:none;"><i class="material-icons">motorcycle</i><?php _e('Delivery','frozr-norsani'); ?></li>
		<?php if (!frozr_manual_vendor_online()) {?>
		<li data-id="frozr_reg_timing_menu"><i class="material-icons">access_time</i><?php _e('Timing','frozr-norsani'); ?></li>
		<?php } ?>
		<li data-id="frozr_reg_withdraw_menu"><i class="material-icons">account_balance_wallet</i><?php _e('Withdraw','frozr-norsani'); ?></li>
	</ul>
	<?php } ?>
	<div class="entry-content">
		<div class="frozr_page_default_content">
		<div class="fro_def_cont">
		<?php the_content(); ?>
		</div>
		<?php if ( ! is_user_logged_in() ) {
		if ($seller_tos) { ?>
			<div id="frozr_reg_seller_tos" class="pop_tos">
				<?php echo $seller_tos; ?>
			</div>
			<p class="fro_reg_sel_tos_btn">
				<label for="fro_reg_sel_tos"><input type="checkbox" id="fro_reg_sel_tos" name="fro_sel_tos" value="1"><?php echo ' '.sprintf(__('I Accept %1$s vendor terms of service.','frozr-norsani'), get_bloginfo( 'name' )); ?></label>
			</p>
		<?php } ?>
		<div class="frozr_reg_form_title_wrapper"><h1 class="frozr_reg_form_title"></h1><p class="frozr_reg_form_desc"></p></div>
		<?php } elseif (!frozr_is_seller_enabled(get_current_user_id()) && frozr_is_seller(get_current_user_id())) { ?>
		<div class="style_box" style="background-color:rebeccapurple;color:#fff"><div class="style_box_content"><i class="material-icons">warning</i><?php echo __('Your registration form is under review. You will recieve updates via email.','frozr-norsani'); ?></div></div>
		<?php } elseif (is_user_logged_in()) {
		$cuser = get_userdata( get_current_user_id() )?>
		<div class="style_box" style="background-color:rebeccapurple;color:#fff"><div class="style_box_content"><i class="material-icons">warning</i><?php echo sprintf(__('You are currently logged in as (%1$s)','frozr-norsani'), $cuser->display_name); ?></div></div>
		<?php } ?>
		</div>
		<div class="frozr_reg_thankyou frozr_hide">
			<h2><?php echo __('Thanks','frozr-norsani'); ?></h2>
			<p><?php echo __('We will review your registration information as soon as possible and contact you via email.','frozr-norsani'); ?></p>
		</div>
	</div><!-- .entry-content -->
	<?php if ( ! is_user_logged_in() ) { ?>
	<div class="entry-content frozr_registration_forms_wrapper<?php if ($seller_tos) {echo ' frozr_hide';} ?>">
		<form class="frozr_registration_form">
			<div class="frozr_reg_gen_info_div frozr_reg_active_div" data-menu="frozr_reg_gen_info_menu" data-title="<?php _e('Let\'s start','frozr-norsani'); ?>" data-desc="<?php _e('Fill out the general information fields here and click next. Keep in mind that all form fields are important ot fill for a good start, so make sure you don\'t miss any one.','frozr-norsani'); ?>">
				<?php do_action('frozr_before_user_reg_general_fields'); ?>
				<div class="frozr_reg_form_group frozr_reg_basic">
					<div>
					<label class="control-label" for="frozr_reg_email"><?php _e( 'Email', 'frozr-norsani' ); ?></label>
					<input id="frozr_reg_email" value="" name="frozr_reg_email" placeholder="example@example.com" type="email">
					<?php do_action('frozr_after_reg_vendor_email_input'); ?>
					</div>
					<div>
					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) { ?>
					<label class="control-label" for="frozr_reg_password"><?php _e( 'Password', 'frozr-norsani' ); ?></label>
					<input id="frozr_reg_password" value="" name="frozr_reg_password" placeholder="<?php _e('Type a strong password','frozr-norsani'); ?>" type="password">
					<?php do_action('frozr_after_reg_vendor_password_input'); ?>
					<?php } else { ?>
					<p class="control-label"><?php _e( 'Password', 'frozr-norsani' ); ?></p>
					<p class="password_suto_generated"><?php echo __('Your password will be automatically generated and sent to your email address.','frozr-norsani'); ?></p>
					<?php } ?>
					</div>
				</div>
				<div class="frozr_reg_form_group frozr_reg_basic">
					<div>
					<label class="control-label" for="frozr_vendor_first_name"><?php _e( 'First Name', 'frozr-norsani' ); ?></label>
					<input id="frozr_vendor_first_name" value="" name="frozr_vendor_first_name" placeholder="<?php _e('Type your first name','frozr-norsani'); ?>" type="text">
					<?php do_action('frozr_after_reg_vendor_first_name_input'); ?>
					</div>
					<div>
					<label class="control-label" for="frozr_vendor_last_name"><?php _e( 'Last Name', 'frozr-norsani' ); ?></label>
					<input id="frozr_vendor_last_name" value="" name="frozr_vendor_last_name" placeholder="<?php _e('Type your Last name','frozr-norsani'); ?>" type="text">
					<?php do_action('frozr_after_reg_vendor_last_name_input'); ?>
					</div>
				</div>
				<div class="frozr_reg_form_group">
					<label class="control-label" for="frozr_vendor_type"><?php _e( 'Shop Type', 'frozr-norsani' ); frozr_inline_help_db('set_store_type'); ?></label>
					<select id="frozr_vendor_type" name='frozr_vendor_type'>
						<?php foreach ($allowed_vendor_types as $vendor_type) { ?>
						<option value="<?php echo $vendor_type; ?>"><?php echo isset($default_types[$vendor_type]) ? $default_types[$vendor_type] : $vendor_type; ?></option>
						<?php } ?>
					</select>
					<?php do_action('frozr_after_reg_store_type_select'); ?>
				</div>
				<div class="frozr_reg_form_group">
					<label class="control-label" for="frozr_vendor_shopname_name"><?php _e( 'Shop Name', 'frozr-norsani' ); frozr_inline_help_db('set_store_name'); ?></label>
					<input id="frozr_vendor_shopname_name" value="" name="frozr_vendor_shopname_name" placeholder="<?php _e('e.g. BOB\'s Pizza','frozr-norsani'); ?>" type="text">
					<?php do_action('frozr_after_reg_vendor_name_input'); ?>
				</div>
				<div class="frozr_reg_form_group">
					<label class="control-label" for="frozr_reg_phone"><?php _e( 'Contact number', 'frozr-norsani' ); frozr_inline_help_db('set_store_tel'); ?></label>
					<input id="frozr_reg_phone" value="" name="frozr_reg_phone" placeholder="<?php _e('e.g. +123 456 789','frozr-norsani'); ?>" type="tel">
					<?php do_action('frozr_after_reg_vendor_phone_input'); ?>
				</div>
				<div class="frozr_reg_form_group">
					<label class="control-label" for="frozr_reg_address"><?php _e( 'Shop address', 'frozr-norsani' ); frozr_inline_help_db('set_store_address'); ?></label>
					<div class="frozr_reg_inner_div">
						<input type="text" id="frozr_reg_address" value= "" name="frozr_reg_address" placeholder="<?php _e('e.g. Seasons Mall, Town Mall','frozr-norsani'); ?>">
						<?php do_action('frozr_after_reg_vendor_address_input'); ?>
					</div>
				</div>
				<div class="frozr_reg_form_group">
					<span class="form-group control-label"><?php _e( "Shop's location on map", "frozr-norsani" ); frozr_inline_help_db('set_store_geo'); ?></span>
					<div class="group_settings_input">
						<div id="frozr_reg_address_map"></div>
						<?php do_action('frozr_after_vendor_location_input'); ?>
					</div>
				</div>
				<div class="frozr_reg_form_group">
					<label class="control-label" for="frozr_reg_rest_tags"><?php _e( "Shop's tags", "frozr-norsani" ); frozr_inline_help_db('set_store_tags'); ?></label>
					<div class="frozr_reg_inner_div tagator_element">
					<input type="text" id="frozr_reg_rest_tags" name="frozr_reg_rest_tags" placeholder="<?php _e('Separate tags with commas','frozr-norsani'); ?>" value="">
					</div>
				</div>
				<?php /*
				<div class="frozr_reg_form_group">
					<span class="control-label"><?php echo __( 'Available food types', 'frozr-norsani' ); frozr_inline_help_db('set_store_food_type'); ?></span>
					<div>
						<label for="frozr_reg_rest_food_type[0]"><?php _e( 'Veg.', 'frozr-norsani' ); ?>
							<input type="checkbox" id="frozr_reg_rest_food_type[0]" name="frozr_reg_rest_food_type[0]" value="veg">
						</label>
						<label for="frozr_reg_rest_food_type[1]"><?php _e( 'Non-Veg.', 'frozr-norsani' ); ?>
							<input type="checkbox" id="frozr_reg_rest_food_type[1]" name="frozr_reg_rest_food_type[1]" value="nonveg">
						</label>
						<label for="frozr_reg_rest_food_type[2]"><?php _e( 'Sea Food.', 'frozr-norsani' ); ?>
							<input type="checkbox" id="frozr_reg_rest_food_type[2]" name="frozr_reg_rest_food_type[2]" value="sea-food">
						</label>
					</div>
				</div>
				*/ ?>
				<div class="frozr_reg_form_group">
					<span class="control-label"><?php _e('Accepted orders','frozr-norsani'); frozr_inline_help_db('set_orders_accepted'); ?></span>
					<div>
					<?php $frozr_accepted_orders = frozr_default_accepted_orders_types();
					foreach ($frozr_accepted_orders as $val) {
					echo '<label for="accepted_'.$val.'"><input type="checkbox" id="accepted_'.$val.'" name="frozr_reg_accept_order_types[]" value="'.$val.'"> ' . esc_attr( $val ) . '</label>';
					}
					do_action('frozr_after_reg_accepted_order_types_select_option'); ?>
					</div>
				</div>
				<?php	if (!frozr_manual_vendor_online()) { ?>
				<div class="frozr_reg_form_group">
					<span class="control-label"><?php _e('Accepted orders when closed','frozr-norsani'); frozr_inline_help_db('set_orders_accepted_offline'); ?></span>
					<div>
					<?php $frozr_accepted_orders = frozr_default_accepted_orders_types();
					foreach ($frozr_accepted_orders as $val) {
					echo '<label for="closed_accepted_'.$val.'"><input type="checkbox" id="closed_accepted_'.$val.'" name="frozr_reg_accept_order_types_cl[]" value="'.$val.'"> ' . esc_attr( $val ) . '</label>';
					}
					do_action('frozr_after_reg_accepted_order_types_while_closed_select_option'); ?>
					</div>
				</div>
				<?php } ?>
			</div>
			<?php if (!get_option('frozr_hide_menus')) { ?>
			<div class="frozr_reg_gen_info_div frozr_hide" data-menu="frozr_reg_menus_menu" data-title="<?php _e('Menus','frozr-norsani'); ?>" data-desc="<?php frozr_inline_help_db('set_menus',true); ?>">
				<div class="metyps_settings">
					<div class="input-group">
						<div class="multi-field-wrapper">
							<div class="multi-fields">
								<div class="multi-field">
									<label><?php _e( 'Menu title', 'frozr-norsani' ); frozr_inline_help_db('set_menu_title'); ?>
									<input value="" name="frozr_reg_rest_meal_types[][title]" class="frozr_reg_rest_meal_types" type="text" placeholder="<?php _e('Meal Type. ie. Breakfast, Lunch & Dinner','frozr-norsani'); ?>">
									</label>
									<label><?php _e( 'Start time', 'frozr-norsani' ); frozr_inline_help_db('set_menu_title'); ?>
									<input value="" name="frozr_reg_rest_meal_types[][start]" class="frozr_reg_rest_meal_types" type="time" placeholder="<?php _e('Start time (24 hour format 00:00)','frozr-norsani'); ?>">
									</label>
									<label><?php _e( 'End time', 'frozr-norsani' ); frozr_inline_help_db('set_menu_title'); ?>
									<input value="" name="frozr_reg_rest_meal_types[][end]" class="frozr_reg_rest_meal_types" type="time" placeholder="<?php _e('End time (24 hour format 00:00)','frozr-norsani'); ?>">
									</label>
									<i class="remove-field material-icons">close</i>
								</div>
							</div>
							<button type="button" class="add-field"><?php _e('Add new menu','frozr-norsani'); ?></button>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>
			<div class="frozr_reg_gen_info_div frozr_hide show_for_del_only frozr_reg_delivery_form" data-menu="frozr_reg_del_menu" data-title="<?php _e('Delivery Settings','frozr-norsani'); ?>" data-desc="<?php echo $delivery_ins; ?>">
				<?php do_action('frozr_before_reg_user_delivery_options'); ?>
				<div class="frozr_dash_notice"><?php echo __('You have more options to set in your settings page after registration.','frozr-norsani'); ?></div>
				<div class="frozr_reg_form_group">
				<span class="control-label"><?php echo __( 'Calculate delivery by', 'frozr-norsani' ); frozr_inline_help_db('set_delivery_by'); ?></span>
				<div>
					<label for="frozr_reg_deliveryby_order">
						<input id="frozr_reg_deliveryby_order" name="frozr_reg_deliveryby" value="order" type="radio">
						<?php _e( 'Order', 'frozr-norsani' ); ?>
					</label>
					<label for="frozr_reg_deliveryby_item">
						<input id="frozr_reg_deliveryby_item" name="frozr_reg_deliveryby" value="item" type="radio">
						<?php _e( 'Product', 'frozr-norsani' ); ?>
					</label>
				</div>
				</div>
				<div class="frozr_reg_form_group">
					<label class="control-label" for="frozr_reg_shipping_fee"><?php echo __( 'Delivery fee', 'frozr-norsani' ) .' '. get_woocommerce_currency_symbol(); frozr_inline_help_db('set_delivery_fee'); ?></label>
					<input id="frozr_reg_shipping_fee" value="" name="frozr_reg_shipping_fee" placeholder="0.0" type="number" min="0" step="any">
				</div>
				<div class="frozr_reg_form_group">
					<label class="control-label" for="frozr_reg_shipping_pro_adtl_cost"><?php echo __( 'Fee per additional product', 'frozr-norsani' ) . get_woocommerce_currency_symbol(); frozr_inline_help_db('set_delivery_peritem'); ?></label>
					<input id="frozr_reg_shipping_pro_adtl_cost" value="" name="frozr_reg_shipping_pro_adtl_cost" placeholder="0.0" type="number" min="0" step="any">
				</div>
				<div class="frozr_reg_form_group">
					<label class="control-label" for="frozr_reg_min_order_amt"><?php echo __( 'Minimum order amount for delivery', 'frozr-norsani' ) .' '. get_woocommerce_currency_symbol(); frozr_inline_help_db('set_delivery_minord'); ?></label>
					<input id="frozr_reg_min_order_amt" value="" name="frozr_reg_min_order_amt" placeholder="0.0" type="number" min="0" step="any">
				</div>
				<div class="frozr_reg_form_group">
					<span class="control-label"><?php _e( 'Delivery zone', 'frozr-norsani' ); frozr_inline_help_db('set_delivery'); ?></span>
					<?php if (frozr_is_using_geolocation()) { ?>
					<div id="fro_reg_delivery_locations_map" class="frozr_reg_loc_map_div"></div>
					<a href="#!" class="frozr_clear_loc_map" title="<?php _e('Erase drawing','frozr-norsani'); ?>"><i class="material-icons">clear</i></a>
					<?php } else { ?>
					<input id="fro_reg_delivery_locations" name="fro_reg_delivery_locations" value="" placeholder="<?php _e('Type & select from predictions and separate by commas.','frozr-norsani'); ?>" type="text">
					<?php } ?>
				</div>
				<?php do_action('frozr_after_reg_user_delivery_options'); ?>
			</div>
			<?php if (!frozr_manual_vendor_online()) { ?>
			<div class="frozr_reg_gen_info_div frozr_hide" data-menu="frozr_reg_timing_menu" data-title="<?php _e( 'Working timing', 'frozr-norsani' ); ?>" data-desc="<?php frozr_inline_help_db('set_timing',true); ?>">
				<?php do_action('frozr_before_reg_user_opening_options'); ?>
				
				<?php $opxlar = apply_filters('frozr_store_timing_week_array',array(
					'sat' => __( 'Saturday', 'frozr-norsani' ),
					'sun' => __( 'Sunday', 'frozr-norsani' ),
					'mon' => __( 'Monday', 'frozr-norsani' ),
					'tue' => __( 'Tuesday', 'frozr-norsani' ),
					'wed' => __( 'Wednesday', 'frozr-norsani' ),
					'thu' => __( 'Thursday', 'frozr-norsani' ),
					'fri' => __( 'Friday', 'frozr-norsani' ),
					));
					$opxlarx = apply_filters('frozr_store_timing_week_args',array('Sat','Sun','Mon','Tue','Wed','Thu','Fri'));
					$opxlnum = 0;
				foreach ($opxlar as $k => $vk) {
				$opxlxx = norsani()->vendor->frozr_vendor_timing($opxlarx[$opxlnum]); ?>
				<div class="frozr_reg_opcl_settings">
					<div class="control_label_group">
						<span class="control-label frozr_reg_rest_<?php echo $k; ?>_opening"><strong><?php echo $vk; ?></strong></span>
						<label for="frozr_reg_rest_<?php echo $k; ?>_open">
							<input id="frozr_reg_rest_<?php echo $k; ?>_open" name="frozr_reg_rest_<?php echo $k; ?>_open" type="checkbox" class="rest_open" value="yes" />
							<?php _e( 'Open', 'frozr-norsani' ); ?>
						</label>
						<label class="rest_shifts_cont frozr_hide" for="frozr_reg_rest_<?php echo $k; ?>_shifts">
							<input id="frozr_reg_rest_<?php echo $k; ?>_shifts" name="frozr_reg_rest_<?php echo $k; ?>_shifts" type="checkbox" class="rest_shifts" value="yes" />
							<?php _e( 'Two Shifts', 'frozr-norsani' ); ?>
						</label>
						<?php echo frozr_inline_help_db('set_time'); ?>
						<?php do_action('frozr_after_reg_store_timing_checkboxes', $k ,$vk); ?>
					</div>
					<div class="frozr_reg_opt_opts">
						<div class="rest_time_inputs <?php if($opxlxx[0] != 'yes') { echo 'frozr_hide';} ?>">
							<div class="rest_one">
								<div>
								<label class="control-label" for="frozr_reg_rest_<?php echo $k; ?>_opening_one"><?php _e( 'Opening time for first shift', 'frozr-norsani' ); frozr_inline_help_db('set_time_inputs'); ?></label>
								<input id="frozr_reg_rest_<?php echo $k; ?>_opening_one" value="" name="frozr_reg_rest_<?php echo $k; ?>_opening_one" type="time" placeholder="<?php _e('Start time (24 hour format 00:00)','frozr-norsani'); ?>">
								</div>
								<div>
								<label class="control-label" for="frozr_reg_rest_<?php echo $k; ?>_closing_one"><?php _e( 'Closing time for first shift', 'frozr-norsani' ); frozr_inline_help_db('set_time_inputs'); ?></label>
								<input id="frozr_reg_rest_<?php echo $k; ?>_closing_one" value="" name="frozr_reg_rest_<?php echo $k; ?>_closing_one" type="time" placeholder="<?php _e('End time (24 hour format 00:00)','frozr-norsani'); ?>">
								</div>
							</div>
							<div class="rest_two frozr_hide">
								<div>
								<label class="control-label" for="frozr_reg_rest_<?php echo $k; ?>_opening_two"><?php _e( 'Opening time for second shift', 'frozr-norsani' ); frozr_inline_help_db('set_time_inputs'); ?></label>
								<input id="frozr_reg_rest_<?php echo $k; ?>_opening_two"  value="" name="frozr_reg_rest_<?php echo $k; ?>_opening_two" type="time" placeholder="<?php _e('Start time (24 hour format 00:00)','frozr-norsani'); ?>">
								</div>
								<div>
								<label class="control-label" for="frozr_reg_rest_<?php echo $k; ?>_closing_two"><?php _e( 'Closing time for second shift', 'frozr-norsani' ); frozr_inline_help_db('set_time_inputs'); ?></label>
								<input id="frozr_reg_rest_<?php echo $k; ?>_closing_two" value="" name="frozr_reg_rest_<?php echo $k; ?>_closing_two" type="time" placeholder="<?php _e('End time (24 hour format 00:00)','frozr-norsani'); ?>">
								</div>
							</div>
						</div>
						<?php do_action('frozr_after_reg_store_timing_dates', $k ,$vk); ?>
					</div>
				</div>
				<?php $opxlnum++; } ?>
				<div class="frozr_reg_opcl_settings frozr_unavailable_dates">
					<span><strong><?php _e( 'Unavailable Dates (mm/dd/yyyy)', 'frozr-norsani' ); frozr_inline_help_db('set_time_unava'); ?></strong></span>
					<div class="multi-field-wrapper">
						<div class="multi-fields">
							<div class="multi-field">
								<div>
								<label class="control-label"><?php _e( 'Starting date', 'frozr-norsani' ); ?>
								<input value="" name="frozr_reg_rest_unads[][start]" name="frozr_reg_rest_unads[][start]" class="frozr_reg_rest_unads" type="date" placeholder="<?php _e('Start Date YYYY/MM/DD','frozr-norsani'); ?>">
								</label>
								</div>
								<div>
								<label class="control-label"><?php _e( 'Ending date', 'frozr-norsani' ); ?>
								<input value="" name="frozr_reg_rest_unads[][end]" class="frozr_reg_rest_unads" type="date" placeholder="<?php _e('End Date YYYY/MM/DD','frozr-norsani'); ?>">
								</label>
								</div>
								<i class="remove-field material-icons">close</i>
							</div>
						</div>
						<button type="button" class="add-field"><?php _e('Add new date','frozr-norsani'); ?></button>
					</div>
				</div>
				<?php do_action('frozr_after_reg_user_opening_options'); ?>
			</div>
			<?php } ?>
			<div class="frozr_reg_gen_info_div frozr_hide" data-menu="frozr_reg_withdraw_menu" data-title="<?php _e( 'Withdrawal Settings', 'frozr-norsani' ); ?>" data-desc="<?php frozr_inline_help_db('set_withdraw',true); ?>">
				<?php do_action('frozr_before_reg_user_withdraw_options'); ?>
				
				<?php $methods = norsani()->withdraw->frozr_withdraw_get_active_methods(); ?>
				<?php do_action('frozr_before_user_withdraw_options'); ?>
				<?php foreach ($methods as $method_key) {
				$method = norsani()->withdraw->frozr_withdraw_get_method( $method_key ); ?>
				<?php if ( is_callable( $method['callback']) ) {
					call_user_func( $method['callback'], array() );
				} ?>
				<?php } ?>

				<?php do_action('frozr_after_reg_user_withdraw_options'); ?>
				<input type="hidden" name="role" value="seller">
			</div>
		</form>
		<div class="frozr_reg_navigation">
			<span class="frozr_reg_back frozr_hide" data-act="prev"><?php _e('Back','frozr-norsani'); ?></span>
			<button class="frozr_reg_next" data-act="next"><?php _e('Next','frozr-norsani'); ?></button>
			<button class="frozr_reg_form_submit frozr_hide"><?php _e('Submit','frozr-norsani'); ?></button>
		</div>
	</div><!-- .entry-content -->
	<?php } ?>
</article><!-- #post-## -->
<?php
// End the loop.
endwhile;
?>
</main><!-- .site-main -->
<?php if (frozr_is_using_geolocation() && ! is_user_logged_in()) { ?>
<script type="text/javascript">
jQuery(function($) {
var poly;
var map;
function initialize(region, divid) {
	var geocoder = new google.maps.Geocoder;
	var addres = $('#frozr_reg_address').val();
	var marker_loc = $('#frozr_reg_address').attr('data-geo');
	var default_address = region;
	if (addres) {
		default_address = addres;
	}
	map = new google.maps.Map(document.getElementById(divid), {
		zoom: 11,
	});

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
	
	/* Get the center of the poly*/
	var marker_pos = '';
	if (marker_loc) {
	var marker_array = marker_loc.split(',');
	marker_pos = new google.maps.LatLng(marker_array[0], marker_array[1]);
	}
	
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
		window.alert('<?php echo __('Geo-location was not successful for the following reason:','frozr-norsani'); ?> '+ status);
	  }
	});

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
	$('#fro_reg_delivery_locations_map').attr({'data-poly':'','data-polyfilterd':''});
});
$(document.body).on('click', '.frozr_reg_next', function() {
	if($('.frozr_reg_delivery_form.frozr_reg_active_div').length > 0) {
	google.maps.event.addDomListener(window, 'load', initialize('<?php echo frozr_get_default_country(); ?>', 'fro_reg_delivery_locations_map'));
	}
});

$('#frozr_reg_rest_tags').tagator({
	autocomplete: [<?php echo $allgrestypes; ?>]
});

var marker,admap;
function initialize_address_map() {
	/*Create the map*/
	var geocoder = new google.maps.Geocoder;
	admap = new google.maps.Map(document.getElementById('frozr_reg_address_map'), {
	zoom: 17,
	});

	geocoder.geocode({'address': '<?php echo frozr_get_default_country(); ?>'}, function(results, status) {
	  if (status === 'OK') {
		admap.setCenter(results[0].geometry.location);
	  } else {
		window.alert('<?php echo __( 'Error: The Geo-location service failed.', 'frozr-norsani' ); ?>'+': ' + status);
	  }
	});

	// This event listener calls addMarker() when the map is clicked.
	google.maps.event.addListener(admap, 'click', function(event) {
	addMarker(event.latLng, admap);
	geocoder.geocode({'latLng': event.latLng}, function(results, status) {
	  if (status === 'OK') {
		$('#frozr_reg_address').val(results[0].formatted_address);
	  }
	});
	});
}

// Adds a marker to the map.
function addMarker(location,admap,cent) {
	/*clear current marker*/
	if (marker) {
	marker.setMap(null);
	}
	if(cent) {
	admap.setCenter(location);
	}
	marker = new google.maps.Marker({
	position: location,
	map: admap
	});
	/*add location info to input*/
	$('#frozr_reg_address').attr('data-geo', location.lat()+','+location.lng());
}
google.maps.event.addDomListener(window, 'load', initialize_address_map());

/*add auto complete for vendor address*/
var autocomplete,
	input = document.getElementById('frozr_reg_address'),
	options = {
	types: ['address'],
	componentRestrictions: {country: '<?php echo frozr_get_default_country(); ?>'},
	};

autocomplete = new google.maps.places.Autocomplete(input, options);
autocomplete.addListener('place_changed', function() {
	var place_obj = autocomplete.getPlace();
	var latlng = place_obj.geometry.location;
	$('#frozr_reg_address').attr('data-geo', latlng.lat()+','+latlng.lng());
	addMarker(latlng,admap,true);
});
});
</script>
<?php } ?>
</div><!-- .content-area -->
<?php 
// calling footer.php
get_footer();