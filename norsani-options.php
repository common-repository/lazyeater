<?php
/**
 * Norsani Plugin WP Options
 * 
 * @package Norsani
 */

/*Main Action Hooks*/
add_action( 'admin_menu', 'frozr_add_admin_menu' );
add_action( 'admin_init', 'frozr_settings_init' );

function frozr_add_admin_menu() { 

	add_menu_page( __('Norsani Settings','frozr-norsani'), 'Norsani', 'manage_options', 'norsani', 'frozr_options_page', plugins_url( 'assets/imgs/admin_icon.png', NORSANI_FILE ), 11 );
	add_submenu_page( 'norsani', __('Norsani Settings','frozr-norsani'), __('Settings','frozr-norsani'), 'manage_options', 'norsani');
	if (!frozr_is_using_geolocation()) {
	add_submenu_page( 'norsani', __('Delivery Locations','frozr-norsani'), __('Delivery Locations','frozr-norsani'), 'manage_options', 'edit-tags.php?taxonomy=location');
	}
	add_submenu_page( 'norsani', __('Vendors Addresses','frozr-norsani'), __('Vendors Addresses','frozr-norsani'), 'manage_options', 'edit-tags.php?taxonomy=vendor_addresses');
	add_submenu_page( 'norsani', __('Vendors Tags','frozr-norsani'), __('Vendors Tags','frozr-norsani'), 'manage_options', 'edit-tags.php?taxonomy=vendorclass');
	add_submenu_page( 'norsani', __('Help','frozr-norsani'), __('Norsani Help Center','frozr-norsani'), 'manage_options', 'norsani_help_center','norsani_help_center_layout');
	
	do_action('frozr_after_norsani_options_menu');
}

function norsani_help_center_layout() {
	/*flush rewrite rules first*/
	flush_rewrite_rules();

	$theme = isset($_GET['theme']) ? $_GET['theme'] : false;
	if ($theme == 'activated') {
		echo '<div class="frozr notice updated is-dismissible">'.__('Thank you for activating a Norsani theme.','frozr-norsani').'</div>';
	} elseif ($theme == 'installed') {
		echo '<div class="frozr notice updated is-dismissible">'.__('Thank you for installing a Norsani theme.','frozr-norsani').'</div>';
	}
	$plugin_install = isset($_GET['plugin']) ? $_GET['plugin'] : false;
	if ($plugin_install == 'installed') {
	echo '<div class="frozr notice updated is-dismissible">'. sprintf(__('Norsani was installed successfully. You are almost ready to start your business. To completely become ready, please go through %1$s, and if you have not yet setup WooCommerce, please run the %2$s','frozr-norsani'),'<a href="admin.php?page=norsani">'.__('Norsani Settings','frozr-norsani').'</a>','<a href="admin.php?page=wc-setup">'.__('WooCommerce setup wizard','frozr-norsani').'</a>').'</div>';
	}
?>
	<div class="wrap frozrhelp_page_title">
		<h1><strong><?php _e('Norsani Help Center','frozr-norsani'); ?></strong></h1>
	</div>
	<div class="frozr_admin_page_inner_wrapper">
	<div class="frozr_admin_box frozrhelp_qa">
	<div class="frozr_admin_box_content">
	<h1><?php _e('Questions & Answers.','frozr-norsani'); ?></h1>
	<div class="frozrhelp_qa_content"></div>
	<div class="frozrhelp_qa_suggest"><h2><?php _e('Suggest a Question','frozr-norsani'); ?></h2>
	<span><?php _e('Didn\'t find your question? Send it to us as a suggestion to add it.','frozr-norsani'); ?></span>
	<div class="frozrhelp_qa_result"></div>
	<form id="frozr_qu_suggest">
	<input type="text" name="frozr_help_question" placeholder="<?php echo __('Type Question Here ..','frozr-norsani'); ?>"/>
	<button type="submit" class="button button-primary"><?php echo __('Submit','frozr-norsani'); ?></button>
	</form></div>
	</div>
	</div>
	<div class="frozrhelp_boxes_wrapper">
	<div class="frozr_admin_box">
	<div class="frozr_admin_box_content frozrhelp_system_status">
	<?php 
		$found_errors = false;
		$error_messages = array();
		$using_google_key = frozr_is_using_geolocation(true);
		$norsani_pages = array(
			'vendors-registration' => __('Vendor registration page was not found','frozr-norsani'),
			'vendors' => __('Vendors list page was not found','frozr-norsani'),
			'sellers' => __("Sellers page in Admin's dashboard was not found","frozr-norsani"),
		);

		/*check for Norsani pages*/
		foreach($norsani_pages as $page_key => $page_val) {
			$page_temp = $page_key.'.php';
			if (!norsani()->admin->frozr_check_page($page_temp)) {
				$error_messages[] = array($page_val, 'frozrhelp_fix_pages');
				$found_errors = true;
			}
		}
		/*check for Norsani theme*/
		if (!current_theme_supports('frozr-norsani')) {
			$theme_exisit = wp_get_theme('frozrdash');
			$action_class = $theme_exisit->exists() ? 'frozr_activate_default_theme' : 'frozr_install_default_theme';
			$found_errors = true;
			$message = __('Your current theme dose not support Norsani. Please Install and Activate a theme that support Norsani.','frozr-norsani');
			$error_messages[] = array($message, $action_class);
		}
		/*Check for Norsani google api key*/
		if (!$using_google_key) {
			$found_errors = true;
			$error_messages[] = array(__('Please enter your Google API Key','frozr-norsani'), 'frozrhelp_setup_google_key');
		}
		
		if ($found_errors) {
			echo '<span class="frozrhelp_system_check dashicons-before dashicons-warning"></span>';
			foreach ($error_messages as $error_message) {
				$button_link = $error_message[1] == 'frozrhelp_setup_google_key' ? 'admin.php?page=norsani' : '#';
				$button_txt = $error_message[1] == 'frozrhelp_setup_google_key' ? __('Set Key','frozr-norsani') : __('Fix','frozr-norsani');
				echo '<div class="frozrhelp_found_errors">'.$error_message[0].' <a href="'.$button_link.'" class="button button-primary '.$error_message[1].'">'.$button_txt.'</a></div>';
			}
		} else {
			echo '<span class="frozrhelp_system_check dashicons-before dashicons-smiley"></span>';
			echo '<div class="frozrhelp_system_ok">'.__('No errors found','frozr-norsani').'</div>';
		}

	?>
	</div>
	</div>
	<?php do_action('frozr_before_demo_data_completed'); ?>
	<div class="frozr_admin_box">
	<div class="frozr_admin_box_content demo_box">
	<span class="frozrhelp_system_check dashicons-before dashicons-admin-page"></span>
	<span class="frozr_demo_box_desc"><?php _e('Install the demo data with just one click.','frozr-norsani'); ?></span>
	<a href="#" class="frozr_install_demo_data_btn button button-primary" title="<?php _e('Install Demo Data','frozr-norsani'); ?>"><?php _e('Install Demo Data','frozr-norsani'); ?></a>
	</div>
	</div>
	</div>
	<div class="frozr_admin_box full_width">
	<div class="frozr_admin_box_content">
	<div class="frozr_contact_box_details"><h1><?php _e('Contact Support','frozr-norsani'); ?></h1><p><?php echo __('Contact our support team and get replies to your questions right from here.', 'frozr-norsani'); ?></p>
	<p><?php echo __('If you are reporting a problem please answer the following questions in your message to help us address your problem as quickly as possible.','frozr-norsani'); ?></p>
	<ol>
	<li><?php echo __('What is your question or concern?','frozr-norsani'); ?></li>
	<li><?php echo __('What steps can we take to reproduce the problem you are experiencing?','frozr-norsani'); ?></li>
	<li><?php echo __('What is the result you are experiencing when you follow these steps?','frozr-norsani'); ?></li>
	<li><?php echo __('What is the result you are expecting?','frozr-norsani'); ?></li>
	<li><?php echo __('How does this problem affect your business?','frozr-norsani'); ?></li>
	</ol>
	<p><strong><?php echo __('Please do not include any sensitive information in this form or in any subsequent case communications. (e.g. Password, credit card number, Social Security Number)','frozr-norsani'); ?></strong></p>
	</div>
	<div class="frozr_contact_box">
	<div class="frozr_contact_screen"></div>
	<form id="frozr_contact_form">
	<textarea name="frozr_help_message" placeholder="<?php echo __('Type message here ..','frozr-norsani'); ?>"></textarea>
	<button type="submit" class="button button-primary"><?php echo __('Send','frozr-norsani'); ?></button>
	</form>
	</div>
	</div>
	</div>
	</div>
	<script>
	jQuery(function($) {
		var wrapper = $('.frozr_admin_page_inner_wrapper');
		var details_wrapper = $('.frozr_contact_box_details');
		var comments_wrapper = $('.frozr_contact_screen');
		var suggest_form = $('#frozr_qu_suggest');
		var route = norsani_general_admin_script_params.help_route;
		var qa_content = $('.frozrhelp_qa_content');
		var activeq = '<?php echo isset($_GET['q']) ? esc_attr($_GET['q']) : '0'; ?>';
		var data = {};
		
		
		/*Load Q&A if saved*/
		var qas = frozr_get_qas();
		if (qas) {
			qa_content.html(qas);
		}
		
		data.email = '<?php echo get_option('admin_email'); ?>';
		data.passwd = '<?php echo get_user_meta(get_current_user_id(),'frozrhelp_user',true); ?>';
		data.website = '<?php echo home_url(); ?>';
		
		$.ajax({
			beforeSend: function() {wrapper.css({'opacity':'0.6','pointer-events':'none'});comments_wrapper.html('<div class="frozr_content_loader"></div>');},
			complete: function() {wrapper.css({'opacity':'1','pointer-events':'auto'});},
			url: route+'login',
			data: data,
			type: 'GET',
			success: function( response ) {
				if (response.qa) {
					qa_content.html(response.qa);
				}
				if (activeq) {
					setTimeout(function(){
					var active_question = $('.frozrhelp_qa_content > ul > li.'+activeq);
					var act_q_content = $('> span',active_question);
					if (active_question.length > 0) {
						act_q_content.addClass('active');
						$("html, body").animate({scrollTop: qa_content.offset().top - 85}, 300);
						qa_content.animate({scrollTop: act_q_content.position().top - qa_content.offset().top - 20}, 300);
					}
					}, 1000);
				}
				if (!$.isEmptyObject(response.comments)) {
				comments_wrapper.html('');
				$.each(response.comments, function(key,value) {
					var admin_cmt = '';
					if (value.user_id == '1') {
						admin_cmt = ' admin_reply';
					}
					comments_wrapper.append('<div class="frozr_comment_body'+admin_cmt+'">'+value.comment_content+'<span class="frozr_comment_date">'+value.comment_date+'</span></div>');
				});
				} else {
				comments_wrapper.html('<span class="frozr_no_comments dashicons-before dashicons-admin-comments">'+norsani_general_admin_script_params.frozrhelp_no_messages+'</span>');
				}
				window.sessionStorage.setItem("frozrhelp_token",response.token);
				if (response.pass) {
				$.ajax({
					beforeSend: function() {wrapper.css({'opacity':'0.6','pointer-events':'none'});},
					complete: function() {wrapper.css({'opacity':'1','pointer-events':'auto'});},
					url: norsani_general_admin_script_params.ajax_url,
					data: {
						userd: response.pass,
						action: 'frozr_save_frozrhelp_user_data',
						security: norsani_general_admin_script_params.frozrhelp_user_data_nonce,
					},
					type: 'POST',
					success: function( response ) {},
				});
				}
			},
			error: function(response) {
				if (response.responseJSON) {
				details_wrapper.prepend('<div class="frozr error notice is-dismissible">'+response.responseJSON+'</div>');
				} else {
				details_wrapper.prepend('<div class="frozr error notice is-dismissible">'+norsani_general_admin_script_params.frozrhelp_gen_error+'</div>');
				}
				comments_wrapper.html('<span class="frozr_no_comments dashicons-before dashicons-admin-comments">'+norsani_general_admin_script_params.frozrhelp_no_messages+'</span>');
			}
		}).always(function(response) {
			$.ajax({
				beforeSend: function() {wrapper.css({'opacity':'0.6','pointer-events':'none'});},
				complete: function() {wrapper.css({'opacity':'1','pointer-events':'auto'});},
				url: route+'getfaq',
				data: {},
				type: 'GET',
				success: function( response ) {
					window.localStorage.setItem("frozrhelpqa", response.qa);
					if (response.qa) {
						qa_content.html(response.qa);
					}
				}, error: function() {
				$('.frozrhelp_qa > div').prepend('<div class="frozr error notice is-dismissible"><?php echo __('Please check your internet connection.','frozr-norsani'); ?></div>');
				}
			});
		});
		function frozr_get_qas() {
			var len = window.localStorage.getItem("frozrhelpqa");

			if (len == 'undefined' || len == null) {
				return '';
			}
			return len;
		}
	});
	</script>
    <?php
}
function frozr_settings_init() {

	register_setting( 'norsani_page_general', 'frozr_gen_settings' );
	register_setting( 'norsani_page_withdraw', 'frozr_withdraw_settings' );
	register_setting( 'norsani_page_fees', 'frozr_fees_settings' );
	register_setting( 'norsani_page_tos', 'frozr_tos_settings' );
	register_setting( 'norsani_page_distance', 'frozr_dis_settings' );
	register_setting( 'norsani_page_orders', 'frozr_orders_settings' );
	
	do_action('frozr_before_norsani_options_settings');
	
	/* Sections*/
	add_settings_section(
		'frozr_general_options_section',
		__( 'General Settings', 'frozr-norsani' ),
		'',
		'norsani_page_general');

	add_settings_section(
		'frozr_orders_options_section',
		__( 'Orders Settings', 'frozr-norsani' ),
		'',
		'norsani_page_orders');

	add_settings_section(
		'frozr_distance_options_section',
		__( 'Distances calculation', 'frozr-norsani' ).norsani()->admin->frozr_admin_help_db('distance'),
		'',
		'norsani_page_distance');

	add_settings_section(
		'frozr_withdraw_options_section',
		__( 'Vendor Money Withdrawals Settings', 'frozr-norsani' ),
		'',
		'norsani_page_withdraw');

	add_settings_section(
		'frozr_fees_general_options_section',
		__( 'General options', 'frozr-norsani' ),
		'',
		'norsani_page_fees');
	
	add_settings_section(
		'frozr_fees_options_section',
		__( 'Vendor Fees', 'frozr-norsani' ).norsani()->admin->frozr_admin_help_db('fees'),
		'',
		'norsani_page_fees');

	add_settings_section(
		'frozr_tos_options_section',
		__( 'Terms & Conditions', 'frozr-norsani' ),
		'',
		'norsani_page_tos');
	
	/* General Settings*/
	add_settings_field( 
		'frozr_lazy_google_key',
		__( 'Google API Key', 'frozr-norsani' ), 
		'frozr_lazy_google_key_render',
		'norsani_page_general',
		'frozr_general_options_section' );

	add_settings_field( 
		'frozr_allowed_vendors',
		__( 'Active Vendor Types', 'frozr-norsani' ),
		'frozr_allowed_vendors_render',
		'norsani_page_general',
		'frozr_general_options_section' );
		
	add_settings_field( 
		'frozr_new_seller_status',
		__( 'Selling privileges', 'frozr-norsani' ),
		'frozr_new_seller_status_render',
		'norsani_page_general',
		'frozr_general_options_section' );
	
	add_settings_field( 
		'frozr_manual_online_seller',
		__( 'Vendor store timing', 'frozr-norsani' ),
		'frozr_manual_online_seller_render',
		'norsani_page_general',
		'frozr_general_options_section' );
	
	add_settings_field( 
		'frozr_auto_offline_max_time',
		'',
		'frozr_auto_offline_max_time_render',
		'norsani_page_general',
		'frozr_general_options_section' );
		
	add_settings_field( 
		'frozr_reco_items',
		__( 'Featured Products.', 'frozr-norsani' ),
		'frozr_reco_items_render',
		'norsani_page_general',
		'frozr_general_options_section' );
	
	add_settings_field( 
		'frozr_reco_sellers',
		__( 'Featured Vendors.', 'frozr-norsani' ),
		'frozr_reco_sellers_render',
		'norsani_page_general',
		'frozr_general_options_section' );
	
	/*Ordrs*/
	add_settings_field( 
		'frozr_norsani_accepted_orders',
		__( 'Accepted orders', 'frozr-norsani' ), 
		'frozr_norsani_accepted_orders_render',
		'norsani_page_orders',
		'frozr_orders_options_section' );
	add_settings_field( 
		'frozr_norsani_accepted_orders_closed',
		__( "Accepted orders when vendor's store is closed", "frozr-norsani" ), 
		'frozr_norsani_accepted_orders_closed_render',
		'norsani_page_orders',
		'frozr_orders_options_section' );
	
	/*Distances Settings*/
	add_settings_field( 
		'frozr_norsani_distance_travelmode',
		__( 'The mode of transport to use when calculating delivery timing.', 'frozr-norsani' ), 
		'frozr_norsani_distance_travelmode_render',
		'norsani_page_distance',
		'frozr_distance_options_section' );
	add_settings_field( 
		'frozr_norsani_distance_unitsystem',
		__( 'The unit system to use when displaying delivery timing.', 'frozr-norsani' ), 
		'frozr_norsani_distance_unitsystem_render',
		'norsani_page_distance',
		'frozr_distance_options_section' );
	add_settings_field( 
		'frozr_norsani_distance_avoidhighways',
		__( 'Avoid highways', 'frozr-norsani' ), 
		'frozr_norsani_distance_avoidhighways_render',
		'norsani_page_distance',
		'frozr_distance_options_section' );
	add_settings_field( 
		'frozr_norsani_distance_avoidtolls',
		__( 'Non-toll routes', 'frozr-norsani' ), 
		'frozr_norsani_distance_avoidtolls_render',
		'norsani_page_distance',
		'frozr_distance_options_section' );
	
	/* Withdraws*/
	add_settings_field( 
		'frozr_pay_vendors_instantly_paypal',
		__( 'Automatic Withdrawals', 'frozr-norsani' ), 
		'frozr_pay_vendors_instantly_paypal_render',
		'norsani_page_withdraw',
		'frozr_withdraw_options_section' );
	add_settings_field( 
		'frozr_paypal_clientid',
		__( 'Paypal REST API App Client ID', 'frozr-norsani' ), 
		'frozr_paypal_clientid_render',
		'norsani_page_withdraw',
		'frozr_withdraw_options_section' );
	add_settings_field( 
		'frozr_paypal_clientsecret',
		__( 'Paypal REST API App Secret.', 'frozr-norsani' ), 
		'frozr_paypal_clientsecret_render',
		'norsani_page_withdraw',
		'frozr_withdraw_options_section' );
	add_settings_field( 
		'frozr_minimum_withdraw_balance',
		__( 'The minimum vendor balance to make a withdrawal request.', 'frozr-norsani' ), 
		'frozr_minimum_withdraw_balance_render',
		'norsani_page_withdraw',
		'frozr_withdraw_options_section' );
	add_settings_field( 
		'frozr_withdraw_methods',
		__( 'Withdrawal Methods.', 'frozr-norsani' ),
		'frozr_withdraw_methods_render',
		'norsani_page_withdraw',
		'frozr_withdraw_options_section' );
	add_settings_field( 
		'frozr_withdraw_order_status',
		__( 'Withdrawal Requests Status.', 'frozr-norsani' ),
		'frozr_withdraw_order_status_render',
		'norsani_page_withdraw',
		'frozr_withdraw_options_section' );

	/* Sales Fees/Commission Settings*/
	add_settings_field( 
		'frozr_lazy_fees_cod',
		__('Cash on delivery fees','frozr-norsani'),
		'frozr_lazy_fees_cod_render',
		'norsani_page_fees',
		'frozr_fees_general_options_section' );

	add_settings_field( 
		'frozr_lazy_fees',
		'',
		'frozr_lazy_fees_render',
		'norsani_page_fees',
		'frozr_fees_options_section' );
	
	/* Terms of Service*/
	add_settings_field( 
		'frozr_tos_sellers',
		__( 'Terms of Service for Sellers', 'frozr-norsani' ),
		'frozr_tos_sellers_render',
		'norsani_page_tos',
		'frozr_tos_options_section' );

	do_action('frozr_after_norsani_options_settings');
}
function frozr_lazy_google_key_render() {
	$option = get_option( 'frozr_gen_settings' );
	$geo_options = (! empty( $option['frozr_lazy_google_key']) ) ? $option['frozr_lazy_google_key'] : '';
	echo norsani()->admin->frozr_admin_help_db('google_key'); ?>
	<input id="frozr_google_key" name="frozr_gen_settings[frozr_lazy_google_key]" type="text" value="<?php echo $geo_options; ?>" />
	<?php	
}
function frozr_allowed_vendors_render() {
	$default_types = frozr_get_default_vendors_types();
	$default_filtered = array();
	foreach($default_types as $key => $val) {
		if (is_int($key)) {
			$default_filtered[] = $val;
		} else {
			$default_filtered[] = $key;
		}
	}
	$alltypes = '"'.join( '"," ', $default_filtered ).'"';
	$options = frozr_get_allowed_vendors_types();
	$vendor_types_opts = implode( '- ', $options );
	echo norsani()->admin->frozr_admin_help_db('vendor_types'); ?>
	<input id="frozr_norsani_allowed_vendors" name="frozr_gen_settings[frozr_allowed_vendors]" type="text" value="<?php echo $vendor_types_opts; ?>"/>
	<script>
	jQuery(function($) {
	$('#frozr_norsani_allowed_vendors').tagator({
		autocomplete: [<?php echo $alltypes; ?>]
	});
	});
	</script>
	<?php
}
function frozr_new_seller_status_render() { 
	$option = get_option( 'frozr_gen_settings' );
	$new_status = isset($option['frozr_new_seller_status']) ? 1 : 0;
	echo norsani()->admin->frozr_admin_help_db('enable_auto_selling'); ?>
	<label for="frozr_new_seller_status">
	<input id="frozr_new_seller_status" type="checkbox" name="frozr_gen_settings[frozr_new_seller_status]" value="1" <?php checked(1, $new_status, true); ?> >
	<?php echo __('Automatically enable selling after vendor registration?','frozr-norsani'); ?>
	</label>
	<?php
}
function frozr_manual_online_seller_render() { 
	$option = get_option( 'frozr_gen_settings' );
	$new_status = isset($option['frozr_manual_online_seller']) ? 1 : 0;
	echo '<fieldset class="form-group">';
	echo norsani()->admin->frozr_admin_help_db('manual_online'); ?>
	<label for="frozr_manual_online_seller">
	<input id="frozr_manual_online_seller" type="checkbox" name="frozr_gen_settings[frozr_manual_online_seller]" value="1" <?php checked(1, $new_status, true); ?> >
	<?php echo __('Use manual status change?','frozr-norsani'); ?>
	</label>
	</fieldset>
	<fieldset class="form-group">
	<?php echo norsani()->admin->frozr_admin_help_db('manual_time');?>
	<label for="frozr_auto_offline_max_time">
	<input id="frozr_auto_offline_max_time" type='number' name='frozr_gen_settings[frozr_auto_offline_max_time]' value='<?php echo (! empty( $options['frozr_auto_offline_max_time'])) ? $options['frozr_auto_offline_max_time'] : 10; ?>' min="0" step="any">
	<?php echo __('Time to wait when no activity','frozr-norsani'); ?></label>
	</fieldset>
	<?php
}
function frozr_auto_offline_max_time_render() {}
function frozr_reco_items_render() { 

	$option = get_option( 'frozr_gen_settings' );
	$options = (! empty( $option['frozr_reco_items']) ) ? $option['frozr_reco_items'] : array('0');
	
	/*Get all items*/
	$items_args = array(
	'posts_per_page'=> -1,
	'offset'		=> 0,
	'post_type'		=> 'product',
	'post_status'	=> 'publish',
	'orderby'		=> 'post_date',
	'order'			=> 'DESC'
	);
	
	$items_array = get_posts( $items_args );

	echo norsani()->admin->frozr_admin_help_db('featured_items'); ?>
	<select class="frozr_admin_select" name='frozr_gen_settings[frozr_reco_items][]' multiple="multiple">
	<?php foreach($items_array as $item ) {?>
	<option value="<?php echo $item->ID; ?>" <?php echo (in_array($item->ID, $options )) ? "selected" : ""; ?> ><?php echo $item->post_title; ?></option>
	<?php } ?>
	</select>
	<?php
}
function frozr_reco_sellers_render() { 

	$option = get_option( 'frozr_gen_settings' );
	$options = (! empty( $option['frozr_reco_sellers']) ) ? $option['frozr_reco_sellers'] : array('0');
	
	$args = apply_filters('frozr_reco_sellers_args', array(
		'role'			=> 'seller',
		'meta_key'		=> 'frozr_enable_selling',
		'meta_value'	=> 'yes',
		'order'			=> 'DESC',
		'orderby'		=> 'registered',
		'fields'		=> 'ID',
	));
	$sellers_query = new WP_User_Query( $args );
	$sellers = $sellers_query->get_results();

	echo norsani()->admin->frozr_admin_help_db('featured_vendors'); ?>
	<select class="frozr_admin_select" name='frozr_gen_settings[frozr_reco_sellers][]' multiple="multiple">
		<?php foreach($sellers as $seller ) {
			$user_store = frozr_get_store_info($seller);
			$user_info = get_userdata($seller);
			$seller_store = (!empty ($user_store['store_name'])) ? ' (' . $user_store['store_name'] . ')' : '';
			?>
			<option value="<?php echo $seller; ?>" <?php echo (in_array($seller, $options )) ? "selected" : ""; ?> ><?php echo $user_info->user_login . $seller_store; ?></option>
			<?php
		} ?>
	</select>
	<?php
}
function frozr_pay_vendors_instantly_paypal_render() {
	$option = get_option( 'frozr_withdraw_settings' );
	$new_status = isset($option['frozr_pay_vendors_instantly_paypal']) ? 1 : 0;
	echo norsani()->admin->frozr_admin_help_db('auto_withdraw'); ?>
	<label for="frozr_pay_vendors_instantly_paypal">
	<input id="frozr_pay_vendors_instantly_paypal" class="frozr_use_payouts" type="checkbox" name="frozr_withdraw_settings[frozr_pay_vendors_instantly_paypal]" value="1" <?php checked(1, $new_status, true); ?> >
	<?php echo __('Automatically create withdrawal requests','frozr-norsani'); ?></label>
	<?php
}
function frozr_paypal_clientid_render() {
	$option = get_option( 'frozr_withdraw_settings' );
	$clientid = (! empty( $option['frozr_paypal_clientid']) ) ? $option['frozr_paypal_clientid'] : '';
	echo norsani()->admin->frozr_admin_help_db('client_id'); ?>
	<input type="text" id="frozr_paypal_clientid" name="frozr_withdraw_settings[frozr_paypal_clientid]" value="<?php echo $clientid; ?>" />
	<?php	
}
function frozr_paypal_clientsecret_render() {
	$option = get_option( 'frozr_withdraw_settings' );
	$clientsecret = (! empty( $option['frozr_paypal_clientsecret']) ) ? $option['frozr_paypal_clientsecret'] : '';
	echo norsani()->admin->frozr_admin_help_db('secret_id'); ?>
	<input type="text" id="frozr_paypal_clientsecret" name="frozr_withdraw_settings[frozr_paypal_clientsecret]" value="<?php echo $clientsecret; ?>" />
	<?php	
}
function frozr_minimum_withdraw_balance_render() { 
	$options = get_option( 'frozr_withdraw_settings' );
	echo norsani()->admin->frozr_admin_help_db('minimum_withdraw'); ?>
	<input type='number' class="frozr_gen_withdraw_settings" name='frozr_withdraw_settings[frozr_minimum_withdraw_balance]' value='<?php echo (! empty( $options['frozr_minimum_withdraw_balance'])) ? $options['frozr_minimum_withdraw_balance'] : 50; ?>' min="0">
	<?php
}
function frozr_withdraw_methods_render() { 
	$option = get_option( 'frozr_withdraw_settings');
	if (!empty ($option['frozr_withdraw_methods'])) {
		$option_array = ( is_array( $option['frozr_withdraw_methods']) ) ? $option['frozr_withdraw_methods'] : array($option['frozr_withdraw_methods']);
	} else {
		$option_array = '';
	}
	$options = (! empty( $option_array ) ) ? $option_array : array('paypal');
	$default_withdraws = norsani()->withdraw->frozr_withdraw_get_methods();
	echo norsani()->admin->frozr_admin_help_db('withdraw_methods'); ?>
	<select class="frozr_admin_select" name="frozr_withdraw_settings[frozr_withdraw_methods][]" multiple="multiple">
		<?php foreach($default_withdraws as $default_withdraw => $val) {
			$sel = in_array($default_withdraw, $options ) ? 'selected="selected"' : '';
			echo '<option value="'.$default_withdraw.'"'. $sel .'>'.$val.'</option>';
		} ?>
	</select>
	<?php
}
function frozr_withdraw_order_status_render() { 
	$option = get_option( 'frozr_withdraw_settings' );
	$options = (! empty( $option['frozr_withdraw_order_status']) ) ? $option['frozr_withdraw_order_status'] : 'pending';
	echo norsani()->admin->frozr_admin_help_db('withdraw_status'); ?>
	<select class="frozr_admin_select" name="frozr_withdraw_settings[frozr_withdraw_order_status]">
		<option value="completed" <?php selected( $options, 'completed' ); ?>><?php _e('Completed','frozr-norsani'); ?></option>
		<option value="processing" <?php selected( $options, 'processing' ); ?>><?php _e('Processing','frozr-norsani'); ?></option>
		<option value="pending" <?php selected( $options, 'pending' ); ?>><?php _e('Pending','frozr-norsani'); ?></option>
	</select>
	<?php
}
function frozr_norsani_accepted_orders_render() {
	$option = get_option( 'frozr_orders_settings' );
	$options = (! empty( $option['frozr_norsani_accepted_orders']) ) ? $option['frozr_norsani_accepted_orders'] : array("delivery", "pickup", "dine-in", "curbside");
	$accepted_orders = array("delivery" => __('Delivery','frozr-norsani'), "pickup" => __('Pick-up','frozr-norsani'), "dine-in" => __('Dine-in','frozr-norsani'), "curbside" => __('Curbside','frozr-norsani'));
	echo norsani()->admin->frozr_admin_help_db('accepted_orders'); ?>
	<select class="frozr_admin_select" name="frozr_orders_settings[frozr_norsani_accepted_orders][]" multiple="multiple">
		<?php foreach($accepted_orders as $accepted_order => $val) {
			$sel = in_array($accepted_order, $options ) ? 'selected="selected"' : '';
			echo '<option value="'.$accepted_order.'"'. $sel .'>'.$val.'</option>';
		} ?>
	</select>
	<?php
}
function frozr_norsani_accepted_orders_closed_render() {
	$option = get_option( 'frozr_orders_settings' );
	$options = (! empty( $option['frozr_norsani_accepted_orders_closed']) ) ? $option['frozr_norsani_accepted_orders_closed'] : array("pickup", "dine-in", "curbside");
	$accepted_orders = array("delivery" => __('Delivery','frozr-norsani'), "pickup" => __('Pick-up','frozr-norsani'), "dine-in" => __('Dine-in','frozr-norsani'), "curbside" => __('Curbside','frozr-norsani'));
	echo norsani()->admin->frozr_admin_help_db('accepted_orders_closed'); ?>
	<select class="frozr_admin_select" name="frozr_orders_settings[frozr_norsani_accepted_orders_closed][]" multiple="multiple">
		<?php foreach($accepted_orders as $accepted_order => $val) {
			$sel = in_array($accepted_order, $options ) ? 'selected="selected"' : '';
			echo '<option value="'.$accepted_order.'"'. $sel .'>'.$val.'</option>';
		} ?>
	</select>
	<?php
}
function frozr_norsani_distance_travelmode_render() {
	$option = get_option( 'frozr_dis_settings' );
	$options = (! empty( $option['frozr_norsani_distance_travelmode']) ) ? $option['frozr_norsani_distance_travelmode'] : 'DRIVING';
	?>
	<select class="frozr_admin_select" name="frozr_dis_settings[frozr_norsani_distance_travelmode]">
		<option value="DRIVING" <?php selected('DRIVING', $options, true); ?> ><?php _e('Driving','frozr-norsani'); ?></option>
		<option value="BICYCLING" <?php selected('BICYCLING', $options, true); ?> ><?php _e('Bicycling','frozr-norsani'); ?></option>
		<option value="WALKING" <?php selected('WALKING', $options, true); ?> ><?php _e('Walking','frozr-norsani'); ?></option>
	</select>
	<?php
}
function frozr_norsani_distance_unitsystem_render() {
	$option = get_option( 'frozr_dis_settings' );
	$options = (! empty( $option['frozr_norsani_distance_unitsystem']) ) ? $option['frozr_norsani_distance_unitsystem'] : 'google.maps.UnitSystem.METRIC';
	?>
	<select class="frozr_admin_select" name="frozr_dis_settings[frozr_norsani_distance_unitsystem]">
		<option value="google.maps.UnitSystem.METRIC" <?php selected('google.maps.UnitSystem.METRIC', $options, true); ?> ><?php _e('Kilometers','frozr-norsani'); ?></option>
		<option value="google.maps.UnitSystem.IMPERIAL" <?php selected('google.maps.UnitSystem.IMPERIAL', $options, true); ?> ><?php _e('Miles','frozr-norsani'); ?></option>
	</select>
	<?php
}
function frozr_norsani_distance_avoidhighways_render() {
	$option = get_option( 'frozr_dis_settings' );
	$new_status = isset($option['frozr_norsani_distance_avoidhighways']) ? 1 : 0;
	?>
	<label for="frozr_norsani_distance_avoidhighways">
	<input id="frozr_norsani_distance_avoidhighways" type="checkbox" name="frozr_dis_settings[frozr_norsani_distance_avoidhighways]" value="1" <?php checked(1, $new_status, true); ?> >
	<?php echo __('Calculate driving timing with avoiding highways where possible.','frozr-norsani'); ?></label>
	<?php
}
function frozr_norsani_distance_avoidtolls_render() {
	$option = get_option( 'frozr_dis_settings' );
	$new_status = isset($option['frozr_norsani_distance_avoidtolls']) ? 1 : 0;
	?>
	<label for="frozr_norsani_distance_avoidtolls">
	<input id="frozr_norsani_distance_avoidtolls" type="checkbox" name="frozr_dis_settings[frozr_norsani_distance_avoidtolls]" value="1" <?php checked(1, $new_status, true); ?> >
	<?php echo __('Calculate delivery timing between points using non-toll routes, wherever possible.','frozr-norsani'); ?></label>
	<?php
}
function frozr_lazy_fees_cod_render() {
	$option = get_option( 'frozr_fees_settings' );
	$cod_option = isset($option['frozr_lazy_fees_cod']) ? 1 : 0;
	echo norsani()->admin->frozr_admin_help_db('cod_info'); ?>
	<label for="frozr_lazy_fees_cod">
	<input id="frozr_lazy_fees_cod" type="checkbox" name="frozr_fees_settings[frozr_lazy_fees_cod]" value="1" <?php checked(1, $cod_option, true); ?> >
	<?php echo __('Do not deduct fees from vendor balance','frozr-norsani'); ?>
	</label>
	<p class="description"><?php echo __('ONLY check this if you will be responsible of the delivery service and reciving the total order amount.','frozr-norsani'); ?></p>
	<?php
}
function frozr_lazy_fees_render() {
	$option = get_option( 'frozr_fees_settings' );
	$fees_options = (! empty( $option['frozr_lazy_fees']) ) ? $option['frozr_lazy_fees'] : false;
	$fees_count = 0;
	$hide_empty_notice = 'style="display:none;"';
	$amount_effected_options = apply_filters('frozr_fee_amount_effected_options', array(
		'full' => __('Order total and delivery','frozr-norsani'),
		'order_total' => __('Only on order total','frozr-norsani'),
		'delivery' => __('Only on order delivery','frozr-norsani'),
	));
	
	echo '<div class="frozr_fee_settings">';
	if ($fees_options) { ?>
	<table class="frozr_sellers_fee_table">
		<thead>
			<tr>
				<th><?php _e( 'Fee&nbsp;Name', 'frozr-norsani' ); ?></th>
				<th><?php _e( 'Customers Effected', 'frozr-norsani' ); ?></th>
				<th><?php _e( 'Vendors Effected', 'frozr-norsani' ); ?></th>
				<th><?php _e( 'Fee Rate', 'frozr-norsani' ); ?></th>
				<th><?php _e( 'Payment Method', 'frozr-norsani' ); ?></th>
				<th><?php _e( 'Applied on', 'frozr-norsani' ); ?></th>
				<th><?php _e( 'Action', 'frozr-norsani' ); ?></th>
			</tr>
		</thead>
		<tbody id="rates">
			<?php foreach ($fees_options as $fee) {
				if ($fee['rate']['action'] == 'multiply') {
					$sign = 'x';
				} elseif ($fee['rate']['action'] == 'minus') {
					$sign = '-';
				} else {
					$sign = '+';
				}
				echo '<tr class="frozr_fee_rule">';
				echo '<td>'.$fee['fee_title'].'</td>';
				echo '<td>'.$fee['customers_effected'].'</td>';
				echo '<td>'.$fee['sellers_effected'].'</td>';
				if (isset($fee['rate']['rate_two']) && floatval($fee['rate']['rate_two']) > 0) {
				echo '<td>'.$fee['rate']['rate_one'].'% '.$sign.' '.get_woocommerce_currency_symbol().$fee['rate']['rate_two'].'</td>';
				} else {
				echo '<td>'.$fee['rate']['rate_one'].'%</td>';
				}
				echo '<td>'.$fee['payment_method'].'</td>';
				echo '<td>'.$amount_effected_options[$fee['amount_effect']].'</td>';
				echo '<td><a data-rule="fee_rule_'.$fees_count.'" href="#" class="frozr_edit_rule" title="'.__('Edit','frozr-norsani').'">'.__('Edit','frozr-norsani').'</a>&nbsp;<a data-rule="fee_rule_'.$fees_count.'" href="#" class="frozr_delete_rule" title="'.__('Delete','frozr-norsani').'">'.__('Delete','frozr-norsani').'</a></td>';
				echo '</tr>';
				$fees_count++;
			} ?>
		</tbody>
	</table>
	<?php frozr_get_fees_rules_body($fees_options); ?>
	<?php } else {
		$hide_empty_notice = '';
	} ?>
	<h3 class="frozr_fee_empty_notice" <?php echo $hide_empty_notice; ?>><?php esc_html_e( 'You do not charge sellers any fees.', 'frozr-norsani' ); ?></h3>
	<a href="#" class="frozr_add_new_rule button-primary"><?php _e('Add New Rule','frozr-norsani'); ?></a>
	</div>
	<?php
}
function frozr_get_fees_rules_body($fees_opts = array()) {
	
	/* General Rule Args*/
	$args = array();
	$payment_gateways = array('any' => __('Any payment gateway','frozr-norsani'));

	$default_args = apply_filters('frozr_default_seller_fee_row', array( "0" => array(
		'customers_effected' => '',
		'customers' => '',
		'sellers_effected' => '',
		'sellers' => '',
		'order_amount' => '',
		'amount_effect' => '',
		'rate' => array('rate_one'=>'','action'=>'','rate_two'=>''),
		'payment_method' => '',
		'fee_title' => '',
		'description' => '',
	)));
	$rows_title = apply_filters('frozr_default_fee_rows_titles', array(
		'customers_effected' => __( 'Apply this fee on', 'frozr-norsani' ),
		'customers' => __( 'Select Customers', 'frozr-norsani' ),
		'sellers_effected' => __( 'Apply this fee on', 'frozr-norsani' ),
		'sellers' => __( 'Select Vendors', 'frozr-norsani' ),
		'order_amount' => __( 'Order Sub-Total', 'frozr-norsani' ) . ' ' . get_woocommerce_currency_symbol(),
		'amount_effect' => __( 'Apply this rule on', 'frozr-norsani') ,
		'rate' => __( 'Rate&nbsp;%', 'frozr-norsani' ),
		'payment_method' => __( 'Payment Method', 'frozr-norsani' ),
		'fee_title' => __( 'Fee&nbsp;Name', 'frozr-norsani' ),
		'description' => __( 'Fee Description', 'frozr-norsani' ),
	));
	
	$customers_effected_options = apply_filters('frozr_fee_customers_effected_options', array(
		'all' => __('All Customers','frozr-norsani'),
		'all_but' => __('All Customers, Except...','frozr-norsani'),
		'specific' => __('Select Customers','frozr-norsani'),
	));
	$sellers_effected_options = apply_filters('frozr_fee_sellers_effected_options', array(
		'all' => __('All Vendors','frozr-norsani'),
		'all_but' => __('All Vendors, Except...','frozr-norsani'),
		'specific' => __('Select Vendors','frozr-norsani'),
	));
	$amount_effected_options = apply_filters('frozr_fee_amount_effected_options', array(
		'full' => __('Order Total and Delivery','frozr-norsani'),
		'order_total' => __('Only on Order Total','frozr-norsani'),
		'delivery' => __('Only on Order Delivery','frozr-norsani'),
	));
	$rate_math_options = apply_filters('frozr_fee_rate_math_options', array(
		'plus' => __('Plus','frozr-norsani'),
		'minus' => __('Minus','frozr-norsani'),
		'multiply' => __('Multiply','frozr-norsani'),
	));
	foreach (WC()->payment_gateways->get_available_payment_gateways() as $gateway) {
		$payment_gateways[esc_attr( $gateway->id )] = $gateway->get_title();
	}
	
	if ($fees_opts) {
		foreach ($fees_opts as $rule) {
			$args[] = apply_filters('frozr_saved_seller_fee_rows', array(
			'customers_effected' => $rule['customers_effected'],
			'customers' => isset($rule['customers']) ? $rule['customers'] : array(),
			'sellers_effected' => $rule['sellers_effected'],
			'sellers' => isset($rule['sellers']) ? $rule['sellers'] : array(),
			'order_amount' => $rule['order_amount'],
			'amount_effect' => $rule['amount_effect'],
			'rate' => $rule['rate'],
			'payment_method' => $rule['payment_method'],
			'fee_title' => $rule['fee_title'],
			'description' => $rule['description'],
			));
		}
	}
	
	$args_vals = ($fees_opts) ? $args : $default_args;
	$array_count = ($fees_opts) ? 0 : 'new';
	$required = $data_type = $min = $step = '';
	
	/* sellers option cannot be empty if seller_effected is specific*/
	foreach ($args_vals as $rule) {
		echo "<div id=\"fee_rule_$array_count\" class=\"frozr_seller_fee_rule\" style=\"display:none;\"><span class=\"frozr_back_to_fee_rules button-primary\">".__('Back','frozr-norsani')."</span><table><tbody>";
		foreach ($rule as $field_label => $field_value) {
		echo "<tr class=\"$field_label\">";
			if ($field_label == "order_amount" || $field_label == "rate") {
				$input_type = 'number';
				$data_type = 'decimal';
				$min = '0';
				$step = 'any';
			} else {
				$input_type = 'text';
			}
			echo '<td>'.$rows_title[$field_label].'</td>';
			echo '<td class="frozr_fees_td">';
			echo norsani()->admin->frozr_admin_help_db('fee_'.$field_label);
			if ($field_label == "customers_effected") {
				frozr_wp_select(array("id" => "frozr_fees_settings[frozr_lazy_fees][$array_count][$field_label]", "class"=>"frozr_admin_select", "value" => $field_value, "options" => $customers_effected_options ));
			} elseif ($field_label == "sellers_effected") {
				frozr_wp_select(array("id" => "frozr_fees_settings[frozr_lazy_fees][$array_count][$field_label]", "class"=>"frozr_admin_select", "value" => $field_value, "options" => $sellers_effected_options ));
			} elseif ($field_label == "sellers") {
				frozr_wp_select(array("id" => "frozr_fees_settings[frozr_lazy_fees][$array_count][$field_label]", "class" => "frozr_admin_select multiopt", "value" => $field_value, "options" => norsani()->vendor->frozr_get_all_sellers(), "custom_attributes" => array("multiple" => "multiple", "required" => "required") ));
			} elseif ($field_label == "customers") {
				frozr_wp_select(array("id" => "frozr_fees_settings[frozr_lazy_fees][$array_count][$field_label]", "class" => "frozr_admin_select multiopt", "value" => $field_value, "options" => norsani()->vendor->frozr_get_all_customers(), "custom_attributes" => array("multiple" => "multiple", "required" => "required") ));
			} elseif ($field_label == "amount_effect") {
				frozr_wp_select(array("id" => "frozr_fees_settings[frozr_lazy_fees][$array_count][$field_label]", "class" => "frozr_admin_select", "value" => $field_value, "options" => $amount_effected_options ));
			} elseif ($field_label == "payment_method") {
				frozr_wp_select(array("id" => "frozr_fees_settings[frozr_lazy_fees][$array_count][$field_label]", "class" => "frozr_admin_select", "value" => $field_value, "options" => $payment_gateways ));
			} elseif ($field_label == "rate") {
				echo '<div class="frozr_option_group">';
				frozr_wp_text_input(array("id" => "frozr_fees_settings[frozr_lazy_fees][$array_count][$field_label][rate_one]", "class" => "", "value" => $field_value['rate_one'], "type" => "number", "data_type" => $data_type, "custom_attributes" => array($required, "step" => $step, "min" => $min) ));
				frozr_wp_select(array("id" => "frozr_fees_settings[frozr_lazy_fees][$array_count][$field_label][action]", "class" => "frozr_admin_select", "value" => $field_value['action'], "options" => $rate_math_options ));
				frozr_wp_text_input(array("id" => "frozr_fees_settings[frozr_lazy_fees][$array_count][$field_label][rate_two]", "class" => "", "value" => $field_value['rate_two'], "type" => "number", "data_type" => $data_type, "custom_attributes" => array($required, "step" => $step, "min" => $min) ));
				echo '</div>';
			} else {
				frozr_wp_text_input(array("id" => "frozr_fees_settings[frozr_lazy_fees][$array_count][$field_label]", "class" => "", "value" => $field_value, "type" => $input_type, "data_type" => $data_type, "custom_attributes" => array($required, "step" => $step, "min" => $min) ));
			}
			echo '</td>';
		echo '</tr>';
		}
		$array_count++;
		echo "</tbody></table></div>";
	}
}
function frozr_tos_sellers_render() { 
	$options = get_option( 'frozr_tos_settings');
	echo norsani()->admin->frozr_admin_help_db('terms'); ?>
	<textarea name="frozr_tos_settings[frozr_tos_sellers]" class="frozr_tos"><?php echo $options['frozr_tos_sellers']; ?></textarea>
	<?php
}

function frozr_options_page() {
	
	$current_tab = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );

	$tabs = apply_filters('frozr_norsani_settings_page_tabs', array(
		'general' => __('General','frozr-norsani'),
		'orders' => __('Orders','frozr-norsani'),
		'withdraw' => __('Withdrawals','frozr-norsani'),
		'distance' => __('Distances','frozr-norsani'),
		'fees' => __('Fees/Commission','frozr-norsani'),
		'tos' => __('Terms of Service','frozr-norsani'),
	));
?>		
<div class="wrap norsani">
<form method="<?php echo esc_attr( apply_filters( 'norsani_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="options.php" enctype="multipart/form-data">
	<nav class="nav-tab-wrapper">
		<?php
			foreach ( $tabs as $name => $label ) {
				echo '<a href="' . admin_url( 'admin.php?page=norsani&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
			}
			do_action( 'norsani_settings_tabs' );
		?>
	</nav>
	<h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
	<?php
	
	settings_fields( 'norsani_page_' . $current_tab );

	do_settings_sections( 'norsani_page_' . $current_tab );

	do_action('frozr_after_norsani_options');

	submit_button();
	?>
</form>
</div>
<?php
}