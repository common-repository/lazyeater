<?php
/**
 * Norsani General Functions
 *
 * @package Norsani
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/


/**
 * Check if WordPress text direction is LTR
 *
 * @return bool
 */
function frozr_wp_ltr() {
	$site_lang = get_option('WPLANG');
	$site_lang_rtl = array('ar','arc','dv','ha','he','khw','ks','ku','ps','ur','yi','fa','bcc','bqi','ckb','glk','lrc','mzn','pnb','sd','ug');
	if(in_array($site_lang, $site_lang_rtl)) {
		return 'false';
	} else {
		return 'true';
	}
}

/**
 * Check if current vendor is the product author
 *
 * @global WP_Post $post
 * @param int $product_id
 * @return bool
 */
if (!function_exists ('frozr_is_author') ) {
	function frozr_is_author( $product_id = 0 ) {
		global $post;

		if ( $product_id == 0 ) {
			$author = $post->post_author;
		} else {
			$author = get_post_field( 'post_author', $product_id );
		}

		if ( $author == get_current_user_id() ) {
			return true;
		}

		return false;
	}
}

/**
 * Redirect to login page if not already logged in
 *
 * @return void
 */
if (!function_exists ('frozr_redirect_login') ) {
	function frozr_redirect_login() {
		if ( ! is_user_logged_in() ) {
			wp_redirect( get_permalink(wc_get_page_id( 'myaccount' )) );
			exit;
		}
	}
}

/**
 * If vendor has been disabled for selling, hide his shop page from public.
 *
 * @param int $sellerid
 * @param string $redirect
 */
if (!function_exists ('frozr_redirect_if_disabled_seller') ) {
function frozr_redirect_if_disabled_seller( $sellerid, $redirect = '' ) {
	if ( !frozr_is_seller_enabled($sellerid) && !is_super_admin() ) {
		$redirect = empty( $redirect ) ? home_url( '/' ) : $redirect;
		?>
		<script>
		redirect_to_home();
		function redirect_to_home(){window.location="<?php echo $redirect ?>";}
		</script>
		<?php
		exit;
	}
}
}

/**
 * Display a deactivated vendor notice in shop page
 *
 * @param int $sellerid
 */
if (!function_exists ('frozr_seller_disabled_notice') ) {
function frozr_seller_disabled_notice($sellerid) {
	if ( !frozr_is_seller_enabled($sellerid) && is_super_admin() ) {
		norsani()->vendor->frozr_rest_notice_output($sellerid, __('Selling privileges of this vendor has been disabled. You are viewing this because you are an administrator.','frozr-norsani'));
	}
}
}

/**
 * If the current vendor is not seller, redirect to homepage
 *
 * @param string $redirect	Redirect URL. Default to home URL.
 */
if (!function_exists ('frozr_redirect_if_not_seller') ) {
function frozr_redirect_if_not_seller( $redirect = '' ) {
	if ( !user_can( get_current_user_id(), 'frozer' ) && !is_super_admin() || !frozr_is_seller_enabled(get_current_user_id()) && !is_super_admin() ) {
		$redirect = empty( $redirect ) ? home_url( '/' ) : $redirect;

		wp_redirect( $redirect );
		exit;
	}
}
}

/**
 * Count posts of a vendor
 *
 * @global WPDB $wpdb
 * @param string $post_type
 * @param int $user_id
 * @return array
 */ 
if (!function_exists ('frozr_count_posts') ) {
	function frozr_count_posts( $post_type, $user_id ) {

		global $wpdb;

		$cache_key = 'frozr-count-' . $post_type . '-' . $user_id;
		$counts = wp_cache_get( $cache_key, 'frozr-norsani' );

		if ( false === $counts ) {
			$query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s AND post_author = %d GROUP BY post_status";
			$results = $wpdb->get_results( $wpdb->prepare( $query, $post_type, $user_id ), ARRAY_A );
			$counts = array_fill_keys( get_post_stati(), 0 );

			$total = 0;
			foreach ( $results as $row ) {
				$counts[ $row['post_status'] ] = (int) $row['num_posts'];
				$total += (int) $row['num_posts'];
			}

			$counts['total'] = $total;
			$counts = (object) $counts;
			wp_cache_set( $cache_key, $counts, 'frozr-norsani' );
		}
		return $counts;
	}
}

/**
 * Function to get the client ip address
 *
 * @param bool $get_ccode
 * @return string
 */
if (!function_exists ('frozr_get_client_ip') ) {
function frozr_get_client_ip( $get_ccode = false) {
	global $woocommerce;
	$default_contry = frozr_get_default_country();
	$geo = new WC_Geolocation();
	$ip_address = $geo->get_ip_address();

	if ($get_ccode == true && $ip_address) {
		if (!empty ($default_contry)) {
			$code = $geo->geolocate_ip( $ip_address );
		}
	} else {
		$code = $ip_address;
	}
	return $code;
}
}

/**
 * Check if the user is seller
 *
 * @param int $user_id
 * @return boolean
 */
if (!function_exists ('frozr_is_seller') ) {
	function frozr_is_seller( $user_id ) {
		if( is_user_logged_in() ) {
			$user = get_userdata( $user_id );
			$role = ( array ) $user->roles;
			return $role[0] == 'seller';
		} else {
			return false;
		}
	}
}

/**
 * Check if the seller is activated for selling
 *
 * @param int $user_id
 * @return bool
 */
if (!function_exists ('frozr_is_seller_enabled') ) {
	function frozr_is_seller_enabled( $user_id ) {
		$selling = get_user_meta( $user_id, 'frozr_enable_selling', true );

		if ( $selling == 'yes' ) {
			return true;
		}

		return false;
	}
}

/**
 * Prevent sellers and customers from seeing the WP admin bar
 *
 * @param bool $show_admin_bar
 * @return bool
 */
if (!function_exists ('frozr_disable_admin_bar') ) {
function frozr_disable_admin_bar( $show_admin_bar ) {
	global $current_user;

	if ( $current_user->ID !== 0 ) {
		$role = reset( $current_user->roles );

		if ( in_array( $role, apply_filters('frozr_disable_admin_access_roles', array( 'seller', 'customer' )) ) ) {
			return false;
		}
	}

	return $show_admin_bar;
}
}
add_filter( 'show_admin_bar', 'frozr_disable_admin_bar' );

/**
 * Default accepted orders type
 *
 * @return array
 */
if (!function_exists ('frozr_default_accepted_orders_types') ) {
function frozr_default_accepted_orders_types() {
	$option = get_option( 'frozr_orders_settings' );
	$accepted_orders = (! empty( $option['frozr_norsani_accepted_orders']) ) ? $option['frozr_norsani_accepted_orders'] : array("delivery", "pickup", "dine-in", "curbside");

	return apply_filters('frozr_default_accepted_orders_types', $accepted_orders);
}
}

/**
 * Default accepted orders type while store is closed
 *
 * @return array
 */
if (!function_exists ('frozr_default_accepted_orders_types_closed') ) {
function frozr_default_accepted_orders_types_closed() {
	$option = get_option( 'frozr_orders_settings' );
	$accepted_orders = (! empty( $option['frozr_norsani_accepted_orders_closed']) ) ? $option['frozr_norsani_accepted_orders_closed'] : array("delivery", "pickup", "dine-in", "curbside");

	return apply_filters('frozr_default_accepted_orders_types_while_closed', $accepted_orders);
}
}

/**
 * Get order type for screen display
 *
 * @param string $order_key
 * @return string
 */
if (!function_exists ('frozr_get_order_type_name') ) {
function frozr_get_order_type_name($order_key) {
	switch ($order_key) {
		case 'delivery':
		return __('Delivery','frozr-norsani');
		case 'pickup':
		return __('Pickup','frozr-norsani');
		case 'dine-in':
		return __('Dine-in','frozr-norsani');
		case 'curbside':
		return __('Curbside','frozr-norsani');
	}
}
}

/**
 * Adds the delivery fee to the cart total
 *
 * @param object $cart
 * @return void
 */
if (!function_exists ('frozr_add_delivery_fee') ) {
function frozr_add_delivery_fee($cart) {

	$cop_vals = array();
	$cop_auths = array();
	$por_authos = array();
	$delivey_total = array();

	foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
		$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
		$product_id	= apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
	
		if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
			$auth = get_post_field('post_author', $product_id);
			$por_authos[$auth][] = $product_id;
		}
	}

	foreach ( $cart->get_applied_coupons() as $code ) {
		$coupon = new WC_Coupon( $code );
		if ( $coupon->is_valid() ) {
		$couponid = wc_get_coupon_id_by_code( $coupon->get_code() );
		$cop_vals[$couponid] = get_post_field('post_author', $couponid);
		$cop_auths[get_post_field('post_author', $couponid)] = $couponid;
		}
	}

	foreach ($por_authos as $por_autho => $pid) {
		$seller_info = frozr_get_store_info($por_autho);
		$div_total = array();
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_id		= apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
			$pauth			= get_post_field('post_author', $product_id);
			if ($pauth == $por_autho) {
				$cop_val = get_post_field('post_author', $product_id);
				$auth_cop_cal = (! empty ($cop_auths[$cop_val]) ) ? $cop_auths[$cop_val]: '';
				$delv_val = get_post_meta($auth_cop_cal, 'free_shipping', true);
				
				do_action('frozr_before_order_items_delivery_check', $cart_item_key, $cart_item);
				
				if ( empty ($cop_vals[$auth_cop_cal]) || $cop_vals[$auth_cop_cal] != $cop_val || $delv_val != 'yes' || frozr_delivery_settings($pauth,'shipping_fee',true) != 0) {
					if ($seller_info['deliveryby'] == 'item' && $cart_item['order_l_type'] == 'delivery') {

						do_action('frozr_order_item_delivery_fee_added', $cart_item_key, $cart_item);

						$div_total[] = $cart_item['quantity'];

					} elseif ($seller_info['deliveryby'] != 'item' && $cart_item['order_l_type'] == 'delivery') {

						do_action('frozr_order_cart_delivery_fee_added', $cart_item_key, $cart_item);

						$div_total[0] = 'bycart';
					}
				} else {
					
					do_action('frozr_order_free_delivery_added', $cart_item_key, $cart_item);
					
					$div_total[0] = 'free';
				}
				
				do_action('frozr_after_order_items_delivery_check', $cart_item_key, $cart_item);
			}
		}
		/*Lets check if we actually have a fee, then add it*/
		$total_div = array_sum($div_total);
		$default_shipping = frozr_delivery_settings($por_autho,'shipping_fee');
		$default_adl_shipping = frozr_delivery_settings($por_autho,'shipping_pro_adtl_cost');
		if (isset($div_total[0]) && $div_total[0] != 'free') {
			
			do_action('frozr_before_order_items_fee_check', $por_autho, $pid);
			
			if ( isset($div_total[0]) && $div_total[0] == 'bycart' || $total_div == 1) {
				$delivey_total[] = $default_shipping;
			} elseif ($total_div > 1) {
				$_adl_fees_add = ($total_div - 1) * $default_adl_shipping;
				$delivey_total[] = $default_shipping + $_adl_fees_add;
			}
			
			do_action('frozr_after_order_items_fee_check', $por_autho, $pid);
		}
	}
	$the_totla = array_sum($delivey_total);
	if ($the_totla > 0) {
		$cart->add_fee( __('Total Delivery Fee', 'frozr-norsani'), apply_filters('frozr_total_delivery_fee', $the_totla));
	}
}
}
add_action( 'woocommerce_cart_calculate_fees','frozr_add_delivery_fee', 10, 1 );

/**
 * Check if Google APIs are been used.
 *
 * @param bool $check_key		Check if Google API key is set.
 * @return bool
 */
if (!function_exists ('frozr_is_using_geolocation') ) {
function frozr_is_using_geolocation( $check_key = false) {
	$option = get_option( 'frozr_gen_settings' );
	$geo_key = (! empty( $option['frozr_lazy_google_key']) ) ? true : false;

	if ($check_key == true) {
		if ($geo_key) {
			return true;
		} else {
			return false;
		}
	} else {
		return true;
	}
}
}

/**
 * Get user location set form
 *
 * @param string $inputid		Add a unique id for the form.
 * @return bool
 */
if (!function_exists ('frozr_user_location_form') ) {
function frozr_user_location_form($inputid = 'post') {
	/*get user location*/
	$user_loc = frozr_norsani_cookies();
	$user_loc_un = frozr_norsani_cookies('locun');
	$loc =  $user_loc ? apply_filters('frozr_user_location_cookie', $user_loc) : '';
	$locs =  $user_loc_un ? $user_loc_un : __('Unset','frozr-norsani');
	$termsarray = array(); ?>

	<div class="loc_form_wrapper">
		<?php do_action('frozr_before_user_location_form'); ?>
		<?php if (frozr_is_using_geolocation()) { ?>
		<label for="user_popup_geo_location_input"><?php echo $loc ? __('Change your location','frozr-norsani') :  __('Set your location','frozr-norsani'); ?></label>
		<div class="frozr_location_input_wrapper">
			<input id="frozr_location_input_<?php echo $inputid; ?>" type="text" name="user_popup_geo_location_input" data-location_type="geo" value="<?php echo $loc; ?>" class="user_geo_location_list" placeholder="<?php echo __('Start typing your location.','frozr-norsani'); ?>">
			<?php do_action('frozr_after_geo_location_input'); ?>
			<div class="loc_loading frozr_hide"></div>
		</div>
		<?php } else {
		/*get all locations*/
		$getallocs = get_terms( 'location' );
		/*get all addresses*/
		$getallads = array_merge($getallocs, get_terms( 'vendor_addresses' ));
		?>
		<ul class="user_location_ul" data-role="listview" data-filter="true" data-filter-reveal="true" data-filter-placeholder="<?php echo apply_filters('frozr_user_location_input_placeholder_text', __('Type the first three letters and choose from the list.','frozr-norsani')); ?>" data-inset="true">
			<?php
			if ( ! empty( $getallads ) && ! is_wp_error( $getallads ) ){
				foreach ( $getallads as $term ) {
					if (!in_array($term->slug, $termsarray)) {
						$termsarray[] = $term->slug;
						echo "<li class=\"ui-screen-hidden\"><a href=\"#\" data-aft=\"refresh\" data-ajax=\"false\" data-loc=\"". $term->slug ."\">" . $term->name . "</a></li>";
					}
				}
			}
			?>
		</ul>
		<?php } ?>
		<span class="frozr_current_location"><?php echo apply_filters('frozr_your_current_location_text', __('Your Current location is: ','frozr-norsani')) . '<span class="frozr_current_location_text"><strong>' . $locs . '</strong></span>'; ?></span>
		<?php do_action('frozr_after_user_location_form'); ?>
		<div class="loc_notices"></div>
	</div>
<?php
}
}

/**
 * Override the WooCommerce add to cart template
 * Get the add to cart template for the loop.
 *
 * @param object $product
 * @return void
 */
function woocommerce_template_loop_add_to_cart( $product ) {
	$post = get_post($product->get_id());
	$product = wc_get_product( $post->ID );
	$vendor = $post->post_author;
	$store_info = frozr_get_store_info($vendor);
	$store_name = isset($store_info['store_name']) ? $store_info['store_name'] : __('This vendor', 'frozr-norsani');
	$vendor_timing = norsani()->vendor->frozr_vendors_open_close($vendor,false);
	$store_time_sts = norsani()->vendor->frozr_rest_status($vendor);
	$allow_ofline_orders = isset( $store_info['allow_ofline_orders'] ) ? esc_attr( $store_info['allow_ofline_orders'] ) : 'yes';
	
	ob_start();
	
	if (norsani()->vendor->frozr_vendor_manual_offline($vendor)) {
	echo '<div class="no_orders_allowed frozr_hide">'.$store_time_sts.'</div>';
	return ob_get_clean();
	}
	if (frozr_is_rest_open($vendor) == false && frozr_manual_vendor_online()) {
	echo '<div class="no_orders_allowed frozr_hide">'. $store_name . ' ' . apply_filters('frozr_closed_notice_text',__('will not accept orders at this time.','frozr-norsani'), $product).'</div>';
	return ob_get_clean();
	}
	if (frozr_is_rest_open($vendor) == false && !isset($store_info['accpet_order_type_cl']) || frozr_is_rest_open($vendor) == false && isset($store_info['accpet_order_type_cl']['none']) || frozr_is_rest_open($vendor) == false && $allow_ofline_orders != 'yes') {
	echo '<div class="no_orders_allowed frozr_hide">'. $store_name . ' ' . apply_filters('frozr_closed_order_notice_text',__('will not accept orders at this time please come back later','frozr-norsani'), $product) . $vendor_timing[0] .'</div>';
	return ob_get_clean();
	}

	do_action('frozr_before_product_add_to_cart_form',$product,$vendor);

	$excerpt = isset( $post->post_excerpt ) ? $post->post_excerpt : '';
	echo !empty($excerpt) ? '<span class="frozr_item_ex_pop">'.$excerpt.'</span>' : '';
	/*echo product ingredents*/
	if (get_the_term_list( $product->get_id(), 'ingredient' )) {
	echo '<div class="frozr_item_details_pop"><span class="frozr_pop_ings_icon"></span>'.__('Ingredients:','frozr-norsani').'<span>';the_terms( $product->get_id(), 'ingredient', '', ', ' );echo '</span></div>';
	}
	/*echo product preparation time*/
	echo norsani()->item->frozr_get_product_preparation_time($product->get_id());
	
	$args = apply_filters('frozr_item_add_to_cart_template_args', array(
		'_POST' => $_POST,
		'post' => $post,
		'product' => $product,
	));
	
	frozr_get_template('views/html-items-atc.php', $args);
	
	$upsel = ( null != (get_post_meta( $product->get_id(), '_upsell_ids', true )) ) ? get_post_meta( $product->get_id(), '_upsell_ids', true ) : array();
	if (!empty($upsel)) {
		echo frozr_get_related_items($upsel,__('How about..','frozr-norsani'));
	}
	
	return apply_filters('frozr_items_atc_html',ob_get_clean(), $args);
}

/**
 * Output related products list in single WC product page and Cart page
 *
 * @param array $items
 * @param string $title		List title
 * @param string $desc		List description
 * @param bool $cart_full	We are in the cart page?
 * @return void
 */
function frozr_get_related_items($items,$title=null,$desc=null,$cart_full=false) {
	ob_start();
	$counter = 1;
	$get_full_cart = $cart_full ? 'data-get="cart"' : '';
	$mobile_cnt = frozr_mobile() ? 2 : 4; 
	echo '<div class="frozr_upsel_wrapper">';
	if ($title) {
		echo '<h2>'.$title.'</h2>';
	}
	if ($desc) {
		echo '<span class="frozr_related_items_desc">'.$desc.'</span>';
	}
	echo '<div class="frozr_upsel_list_wrapper">';
	if (count($items) > $mobile_cnt) {
		echo '<i class="material-icons frozr_upsel_nav_left" style="opacity:0.2;pointer-events:none">keyboard_arrow_left</i>';
	}
	echo '<ul class="frozr_upsell_list">';
	foreach($items as $itemid) {
		$show_class = $counter <= $mobile_cnt ? ' frozr_upsel_show' : '';
		$product_ob = wc_get_product($itemid);
		$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($itemid), 'full');
		echo '<li class="frozr_upsell_single_item'.$show_class.'">';
		echo '<a class="frozr_navto_item frozr_related_item_link" '.$get_full_cart.' href="#" data-id="'.$itemid.'"><span class="rest_item_img" style="background-image:url(\''.$large_image_url[0].'\');"></span></a>';
		echo '<span class="frozr_item_details"><a class="frozr_navto_item frozr_item_title frozr_related_item_link" '.$get_full_cart.' href="#">'.get_the_title( $itemid ).'</a></span>';
		echo '<span class="item_loop_price">'.$product_ob->get_price_html().'</span>';
		echo '</li>';
		$counter++;
	}
	echo '</ul>';
	if (count($items) > $mobile_cnt) {
		echo '<i class="material-icons frozr_upsel_nav_right">keyboard_arrow_right</i>';
	}
	echo '</div>';
	echo '</div>';

	return ob_get_clean();
}

/**
 * Override the WooCommerce add to cart template in product single page.
 * Trigger the single product add to cart action.
 *
 * @return void
*/
function woocommerce_template_single_add_to_cart() {
	global $post;
	$product = wc_get_product( $post->ID );
	
	woocommerce_template_loop_add_to_cart( $product );
}

/**
 * Get template part.
 *
 * NORSANI_TEMPLATE_DEBUG_MODE will prevent overrides in themes from taking priority.
 *
 * @access public
 * @since 1.9
 * @param mixed  $slug Template slug.
 * @param string $name Template name (default: '').
 */
function frozr_get_template_part( $slug, $name = '' ) {
    $template = '';

    // Look in yourtheme/slug-name.php and yourtheme/frozr-norsani/slug-name.php.
    if ( $name && ! NORSANI_TEMPLATE_DEBUG_MODE ) {
        $template = locate_template( array( "{$slug}-{$name}.php", norsani()->template_path() . "{$slug}-{$name}.php" ) );
    }

    // Get default slug-name.php.
    if ( ! $template && $name && file_exists( norsani()->plugin_path() . "/templates/{$slug}-{$name}.php" ) ) {
        $template = norsani()->plugin_path() . "/templates/{$slug}-{$name}.php";
    }

    // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/woocommerce/slug.php.
    if ( ! $template && ! NORSANI_TEMPLATE_DEBUG_MODE ) {
        $template = locate_template( array( "{$slug}.php", norsani()->template_path() . "{$slug}.php" ) );
    }

    // Allow 3rd party plugins to filter template file from their plugin.
    $template = apply_filters( 'frozr_get_template_part', $template, $slug, $name );

    if ( $template ) {
        load_template( $template, false );
    }
}

/**
 * Get templates with extracting arguments.
 *
 * @access public
 * @since 1.9
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 */
function frozr_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
    if ( ! empty( $args ) && is_array( $args ) ) {
        extract( $args ); // @codingStandardsIgnoreLine
    }

    $located = frozr_locate_template( $template_name, $template_path, $default_path );

    if ( ! file_exists( $located ) ) {
        /* translators: %s template */
        norsani_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'frozr-norsani' ), '<code>' . $located . '</code>' ), '1.9' );
        return;
    }

    // Allow 3rd party plugin filter template file from their plugin.
    $located = apply_filters( 'frozr_get_template', $located, $template_name, $args, $template_path, $default_path );

    do_action( 'frozr_before_template_part', $template_name, $template_path, $located, $args );

    include $located;

    do_action( 'frozr_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Like frozr_get_template, but returns the HTML instead of outputting.
 *
 * @since 1.9
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 *
 * @return string
 */
function frozr_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
    ob_start();
    frozr_get_template( $template_name, $args, $template_path, $default_path );
    return ob_get_clean();
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 * yourtheme/$template_path/$template_name
 * yourtheme/$template_name
 * $default_path/$template_name
 *
 * @access public
 * @since 1.9
 * @param string $template_name Template name.
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 * @return string
 */
function frozr_locate_template( $template_name, $template_path = '', $default_path = '' ) {
    if ( ! $template_path ) {
        $template_path = norsani()->template_path();
    }

    if ( ! $default_path ) {
        $default_path = norsani()->plugin_path() . '/templates/';
    }

    // Look within passed path within the theme - this is priority.
    $template = locate_template(
        array(
            trailingslashit( $template_path ) . $template_name,
            $template_name,
        )
    );

    // Get default template/.
    if ( ! $template || NORSANI_TEMPLATE_DEBUG_MODE ) {
        $template = $default_path . $template_name;
    }

    // Return what we found.
    return apply_filters( 'frozr_locate_template', $template, $template_name, $template_path );
}

/**
 * Conditional CSS classes for closed/open order type in product atc form
 *
 * @param object $product
 * @param string $order_type Product order type option
 * @return string
 */
if (!function_exists ('frozr_orders_type_close_open') ) {
function frozr_orders_type_close_open($product, $order_type) {

	$product_author = apply_filters('frozr_orders_type_close_open_author',get_post_field( 'post_author', $product->get_id() ), $product, $order_type);
	$store_info = frozr_get_store_info( $product_author );
	$rest = '';
	
	if (frozr_is_rest_open($product_author) == false && in_array($order_type, $store_info['accpet_order_type_cl'])) {
		$rest = 'show_open_closed_order_notice';
	} elseif(frozr_is_rest_open($product_author) == false) {
		$rest = 'show_closed_order_notice';
	} elseif(frozr_is_rest_open($product_author) == true) {
		$rest = 'allow_other_orders';
	}
	
	return $rest;
}
}

/**
 * Count user object
 *
 * @param string $sts		Object/post status.
 * @param string $type		Object/post type.
 * @param int $seller_id	User to count objects. Defaults to current active user.
 * @return int
 */
if (!function_exists ('frozr_count_user_object') ) {
function frozr_count_user_object($sts, $type ="", $seller_id =""){
	$user_id = (!empty($seller_id)) ? $seller_id : get_current_user_id();
	$get_curnt_user = (is_super_admin() && empty($seller_id)) ? '' : $user_id;
	$args = apply_filters('frozr_count_user_object_args',array(
		'posts_per_page'	=> -1,
		'post_type'			=> $type,
		'orderby'			=> 'date',
		'author'			=> $get_curnt_user,
		'order'				=> 'desc',
		'post_status'		=> array($sts),
		'fields'			=> 'ids',
	));
	if ($type == 'shop_order') {
		$args['author'] = '';
		if ('' != $get_curnt_user) {
		$args['meta_key'] = '_frozr_vendor';
		$args['meta_value'] = $get_curnt_user;
		}
	}

	$coupon_query = get_posts( $args );
	$count = 0;
	foreach ($coupon_query as $coupon) {
		$sub_orders = get_children( array( 'post_parent' => $coupon, 'post_type' => $type ) );
		if ( !$sub_orders ) {
			$count++;
		}
	}
	return $count;
}
}

/**
 * Redirect from current page if not admin
 *
 */
if (!function_exists ('frozr_redirect_if_not_admin') ) {
function frozr_redirect_if_not_admin() {

	if (!is_super_admin()) {
		wp_redirect( home_url() );
	}
}
}

/**
 * Redirect from current page if admin
 *
 */
if (!function_exists ('frozr_redirect_if_admin') ) {
function frozr_redirect_if_admin() {

	if (is_super_admin()) {
		wp_redirect( home_url() );
	}
}
}

/**
 * Check if we are on a mobile device.
 *
 * @return bool
 */
if (!function_exists ('frozr_mobile') ) {
function frozr_mobile() {
	$fmobi = new Mobile_Detect;
	if ($fmobi->isMobile()) {
	return true;
	} else {
	return false;
	}
}
}

/**
 * Check if we are on a tablet device.
 *
 * @return bool
 */
if (!function_exists ('frozr_tablet') ) {
function frozr_tablet() {
	$fmobi = new Mobile_Detect;
	if ($fmobi->isTablet()) {
	return true;
	} else {
	return false;
	}
}
}

/**
 * Add a body class if we are on a mobile device.
 *
 * @param array $classes
 * @return array
 */
if (!function_exists ('mobile_class') ) {
function mobile_class( $classes ) {
	/* add 'class-name' to the $classes array*/
	$classes[] = 'on-mobile';
	/* return the $classes array*/
	return $classes;
}
}
if (frozr_mobile() && !frozr_tablet()) {
	add_filter( 'body_class', 'mobile_class' );
}

/**
 * Output Dashboard pages lists bottom navigation.
 *
 * @return void
 */
if (!function_exists ('frozr_lazy_nav_below') ) {
function frozr_lazy_nav_below() {
global $wp_query;
?>
<div class="frozr_lazy_dash_nav">
<div class="frozr_lazy_dash_nav_btn"><?php next_posts_link(__('Next Page','frozr-norsani'), $wp_query->max_num_pages); ?></div>
</div>
<?php }
}

/**
 * Get WooCommerce default country
 *
 * @param bool $show_full		Show full details like US:CA or US only?
 * @return string
 */
if (!function_exists ('frozr_get_default_country') ) {
function frozr_get_default_country($show_full = false) {
	$default_contry = get_option('woocommerce_default_country');
	$default_location_type_test = explode(':', $default_contry);
	if ($show_full == true) {
		return $default_contry;
	} else {
		return $default_location_type_test[0];
	}
}
}

/**
 * Get an array will all delivery locations of all vendors
 *
 * @param string $type		Type of location values. Default to nonfiltered.
 * @return array
 */
if (!function_exists ('frozr_get_all_sellers_locations') ) {
function frozr_get_all_sellers_locations($type = 'nonfiltered') {
	$result = array();
	$metakey = ($type == 'nonfiltered') ? 'delivery_location' : 'delivery_location_filtered';
	
	$args = apply_filters( 'frozr_get_delivery_sellers', array(
		'role' => 'seller',
		'orderby' => 'registered',
		'order' => 'ASC',
		'meta_query' => array(
			array(
				'key' => 'frozr_enable_selling',
				'value' => 'yes',
				'compare' => '='
			)
		)
	));
	$user_query = new WP_User_Query( $args );
	$sellers = $user_query->get_results();
	
	foreach($sellers as $seller) {
		$seller_locs = get_user_meta($seller->ID, $metakey, true);

		if (!empty ($seller_locs)) {
			$result[$seller->ID] = $seller_locs;
		}
	}
	
	return $result;
}
}

/**
 * Get Norsani saved cookies
 *
 * @param string $type		Type of cookie to get. Default to user location.
 * @return string|array
 */
if (!function_exists ('frozr_norsani_cookies') ) {
function frozr_norsani_cookies($type = 'user') {
	$result = false;
	
	switch ($type) {
		case 'user':
			$result = isset($_COOKIE['frozr_user_location']) ? esc_attr($_COOKIE['frozr_user_location']) : '';
		break;
		case 'del':
			$result = !empty($_COOKIE['frozr_del_sellers']) ? array_map('intval', explode('-',$_COOKIE['frozr_del_sellers'])) : apply_filters('frozr_default_delivery_sellers', array());
		break;
		case 'locun':
			$result = isset($_COOKIE['frozr_user_location_unslashed']) ? esc_attr($_COOKIE['frozr_user_location_unslashed']) : '';
		break;
		case 'geo':
			$result = isset($_COOKIE['frozr_user_geo_location']) ? esc_attr($_COOKIE['frozr_user_geo_location']) : '';
		break;
	}
	
	return apply_filters('frozr_norsani_cookies_result', $result, $type);
}
}

/**
 * Get an array of delivery vendors ids from saved Norsani cookies
 *
 * @param string $user_location
 * @param array $sellers			Pass previously detected array of delivery vendors. Mostly from JS.
 * @return array
 */
if (!function_exists ('frozr_get_delivery_sellers') ) {
function frozr_get_delivery_sellers($user_location = '', $sellers = array()) {
	if (!empty($sellers)) {
		$userslocids = $sellers;
	} elseif (frozr_is_using_geolocation()) {
		$userslocids	= frozr_norsani_cookies('del');
	} else {
		$user_loc = !empty($user_location) ? $user_location : frozr_norsani_cookies();		
		$userslocs		= get_term_by( 'slug', $user_loc, 'location');
		$userslocids	= get_objects_in_term( (int) $userslocs->term_id, 'location');
	}
	return $userslocids;
}
}

/**
 * Filter html ready for a REST call.
 *
 * @param html $html	HTML to be filterd
 * @return void
 */
if (!function_exists ('frozr_rest_html') ) {
function frozr_rest_html($html) {
	return str_replace(array("\n","\r","\t"),'',$html);
}
}

/**
 * Get product rating
 *
 * @param object $product
 * @return void
 */
if (!function_exists ('frozr_rest_get_item_rating') ) {
	function frozr_rest_get_item_rating($product) {
		$product = wc_get_product($product->ID);
		if ( 'no' === get_option( 'woocommerce_enable_review_rating' ) ) {
		return;
		}

		$rating_count = $product->get_rating_count();
		$review_count = $product->get_review_count();
		$average      = $product->get_average_rating();

		if ( $rating_count > 0 ) {

		$output = '<div class="woocommerce-product-rating">';
		$output .= wc_get_rating_html( $average, $rating_count );
		$output .= '</div>';
		} else {
		$output = '<div class="woocommerce-product-rating"><a class="star-rating" href="#" title="'.__('No Rating Yet!','frozr-norsani').'"></a></div>';
		}
		return $output;
	}
}

/**
 * Get default allowed vendors types on Norsani*
 *
 * @return array
 */
if (!function_exists ('frozr_get_default_vendors_types') ) {
function frozr_get_default_vendors_types() {
	$default_types = apply_filters('frozr_vendor_default_types',array('chef' => __('Chefs','frozr-norsani'),'restaurant' => __('Restaurants','frozr-norsani'),'grocery' => __('Groceries','frozr-norsani'),'foodtruck' => __('Food Trucks','frozr-norsani')));
	return $default_types;
}
}

/**
 * Get allowed vendors types by admin*
 *
 * @return array
 */
if (!function_exists ('frozr_get_allowed_vendors_types') ) {
function frozr_get_allowed_vendors_types() {
	$option = get_option( 'frozr_gen_settings' );
	$default_types = frozr_get_default_vendors_types();
	$default_filtered = array();
	foreach($default_types as $key => $val) {
		if (is_int($key)) {
			$default_filtered[] = sanitize_title($val);
		} else {
			$default_filtered[] = sanitize_title($key);
		}
	}
	/*backward compatibility*/
	$allowed_vends = isset($option['frozr_allowed_vendors']) && is_array($option['frozr_allowed_vendors']) ? $option['frozr_allowed_vendors'] : explode('- ',$option['frozr_allowed_vendors']);
	$options = isset( $option['frozr_allowed_vendors']) ? array_map('sanitize_title',$allowed_vends) : $default_filtered;
	return $options;
}
}

/**
 * Norsani Vendor inline Help database
 *
 * @param string $help_key		Help instruction id
 * @param bool $textonly		Return plan text or HTML?
 * @return string|html
 */
if (!function_exists ('frozr_inline_help_db') ) {
function frozr_inline_help_db($help_key,$textonly=false){
	$wid_options = get_option( 'frozr_withdraw_settings' );
	$fee_option = get_option( 'frozr_fees_settings' );
	$wid_min = (! empty( $wid_options['frozr_minimum_withdraw_balance'])) ? $wid_options['frozr_minimum_withdraw_balance'] : 50;
	$wid_auto = isset($wid_options['frozr_pay_vendors_instantly_paypal']) ? 1 : 0;
	$fees_options = (! empty( $fee_option['frozr_lazy_fees']) ) ? $fee_option['frozr_lazy_fees'] : false;
	$fees = array();
	if ($fees_options) {
	foreach ($fees_options as $fee) {
		if ($fee['rate']['action'] == 'multiply') {
			$sign = 'x';
		} elseif ($fee['rate']['action'] == 'minus') {
			$sign = '-';
		} else {
			$sign = '+';
		}
		$fees[] = $fee['rate']['rate_one'].'% '.$sign.' '.get_woocommerce_currency_symbol().$fee['rate']['rate_two'];
	}} else {
		$fees = '0%';
	}
	$fees_deducted = is_array($fees) ? implode(' '.__('plus','frozr-norsani').' ', $fees) : $fees;
	$distant_unit = norsani()->vendor->frozr_distance_divider(true);
	
	$help_text_list = apply_filters('frozr_help_text_array',array(
		'dash_home_sales' => __('Here you can get statistics info on your sales. The results table is set by default to show sales for today, but you can see results for different periods as well. You can also click the print button if you wish to print current results.', 'frozr-norsani'),
		'dash_balance' => sprintf(__("Whenever a customer makes an online payment for your products, we deduct %s from the order's total and save the remaining amount here in your account balance after the order status is changed to completed. Click the withdraw button if you wish to submit a withdrawal request.", "frozr-norsani"),$fees_deducted),
		'dash_home_orders' => __('This will show you a general count of your orders grouped by order status. You can click on the status title to navigate to the respective orders page.', 'frozr-norsani'),
		'dash_home_top_selling' => __('Here you can get information on products having the most sales. This will help you in either improving or promoting other products that have low sales.', 'frozr-norsani'),
		'dash_home_top_customers' => __('Here you can get information on top spending customers. This is only based on completed orders. This could be helpful if you wish to reward or provide them with special service.', 'frozr-norsani'),
		'dash_orders' => __('This is the main page to view and manage your incoming orders. Orders are listed from oldest to newest. Whenever your shop is open, and you are in this page, the system will automatically update your orders list every minute. Click on "Quick view" to see the products list or click on the order number to view full details. Click on the action button to process the order by changing its status. You should only manage the orders that are in the "processing" list. The Orders that are in the "On-hold" or "Pending" status are still not paid, so please wait until they are moved to the processing orders list.', 'frozr-norsani'),
		'order_discount' => __('This is the total discount. Discounts are defined per line item.', 'frozr-norsani'),
		'order_note' => __('Add a note for your reference, or add a customer note (the customer will be notified)', 'frozr-norsani'),
		'dash_coupons' => __('Here you can add discount coupons on your products to encourage customers to make orders from your shop. You can also create discount coupons for only one specific loyal customer by setting the usage limit to 1 and disabling the "Go public" option, then you can send the code to the customer by email with a thank you message.', 'frozr-norsani'),
		'dash_coupons_title' => __('Customers will need the "coupon code" to benefit from the discount when they are in the cart or the checkout page. This "coupon code" will not appear to customers by default, you need to include it in the "Coupon text to show" field.', 'frozr-norsani'),
		'dash_coupons_desc' => __('Type few lines about this coupon, describing why are you offering it.', 'frozr-norsani'),
		'dash_coupons_amount' => __('Set the amount or percentage to discount (depending on the discount type).', 'frozr-norsani'),
		'dash_coupons_emails' => __('White-list of customers emails to check against when an order is placed. Separate email addresses with commas. You can also use an asterisk (*) to match parts of an email. For example &quot;*@gmail.com&quot; would match all gmail addresses..', 'frozr-norsani'),
		'dash_coupons_coplimt' => __('How many times this coupon can be used before it becomes void?', 'frozr-norsani'),
		'dash_coupons_itemlimt' => __('The maximum number of individual products that this coupon can apply to when using products discount. Leave it blank to apply to all qualifying products in cart.', 'frozr-norsani'),
		'dash_coupons_usrlimt' => __('How many times this coupon can be used by each individual user?', 'frozr-norsani'),
		'dash_coupons_products' => __('Products that the coupon will be applied to, or that need to be in the cart in order for the &quot;Fixed cart discount&quot; to be applied..', 'frozr-norsani'),
		'dash_coupons_del' => __('Check this box if the coupon grants free delivery.', 'frozr-norsani'),
		'dash_coupons_induse' => __('Check this box if the coupon cannot be used in conjunction with other coupons.', 'frozr-norsani'),
		'dash_coupons_exsale' => __("Check this box if the coupon should not apply to products with discounted price. Per-product coupons will only work if the product is not on it's discount price. Per-cart coupons will only work if there are no discounted products in the cart.", "frozr-norsani"),
		'dash_coupons_minspend' => __('This field allows you to set the minimum spend (subtotal) allowed to use the coupon.', 'frozr-norsani'),
		'dash_coupons_maxspend' => __('This field allows you to set the maximum spend (subtotal) allowed when using the coupon.', 'frozr-norsani'),
		'dash_coupons_public' => __('Show the Coupon in your shop page?', 'frozr-norsani'),
		'dash_coupons_text' => __('Text to show in the coupon box on your shop page.', 'frozr-norsani'),
		'dash_withdraw' => $wid_auto == 1 ? sprintf(__('From this page, you can submit money withdrawal requests and view your previous requests. Whenever an order status is changed to completed, the system will automatically create a withdrawal request for you. Before it has been Processed by the website admin you can manually cancel your request. If you have at least %1$s%2$s in your current balance you can make a withdrawal request.', 'frozr-norsani'),get_woocommerce_currency_symbol(),$wid_min) :  sprintf(__('From this page, you can submit money withdrawal requests and view your previous requests. If you have at least %1$s%2$s in your account balance you can submit a withdrawal request.', 'frozr-norsani'),get_woocommerce_currency_symbol(),$wid_min),
		'set_store_type' => __('This is used to classify your shop type. You cannot change this option after registration.', 'frozr-norsani'),
		'set_store_name' => __("This is your shop name which will appear in the vendor lists and in your shop's page.", "frozr-norsani"),
		'set_store_tel' => __("This is the phone number which will appear on your shop's contact form.", "frozr-norsani"),
		'set_store_address' => __("This is the shop's address which will appear in your shop's contact form and whenever customers choose to make 'Pickup', 'Dine-in', or 'Curbside' orders.", "frozr-norsani"),
		'set_store_geo' => __('Click on the map to set the exact location to your shop. This will allow customers to quickly get directions to your shop with Google Maps. In addition, this will be used to calculate delivery fees if you offer delivery service.', 'frozr-norsani'),
		'set_store_tags' => __('Type services/foods that you provide separated with a comma or click on value if appeared in the predictions list. Values here are used in vendors searching tools. i.e: Indian, Chinese, Thai, barbecue, meats, fish, seafoods, pet-foods, snack-foods.', 'frozr-norsani'),
		'set_store_food_type' => __('Select food types available in your shop.', 'frozr-norsani'),
		'set_store_email' => __('When this is selected, customers will be able to send you email messages from your shop page.', 'frozr-norsani'),
		'set_menus' => __('If you offer products/foods on specific timings, then you should use menus to avoid customers from ordering products at the time that you do not offer them. After you create menus here, a new option called "Linked Menus" will appear in the products add/edit form where you can link the product to a specific menu or multiple menus.', 'frozr-norsani'),
		'set_menu_title' => __('Select a name for this menu. This will appear in your shop page with timings below.', 'frozr-norsani'),
		'set_menu_star' => __('When do you start offering products linked to this menu? if your browser does not support time inputs please enter the time in 24 hour format like: 16:30', 'frozr-norsani'),
		'set_menu_end' => __('When do you end offering products linked to this menu? if your browser does not support time inputs please enter the time in 24 hour format like: 18:50', 'frozr-norsani'),
		'set_orders_accepted' => __('What are the order types you accept when your shop is open (online)?', 'frozr-norsani'),
		'set_orders_accepted_offline_check' => __('Selecting this option will allow customers to create pre-orders to be processed when your shop is open.', 'frozr-norsani'),
		'set_orders_accepted_offline' => __('What are the order types you accept when your shop is closed (offline)?', 'frozr-norsani'),
		'set_delivery' => __('If you offer delivery service click on the map to start drawing a polygon that covers the area where you can offer delivery service. Click on the X button on the right to clear the drawing.', 'frozr-norsani'),
		'set_delivery_by' => __('Do you want to calculate the delivery fee separately for each individual product or as per order?', 'frozr-norsani'),
		'set_delivery_fee' => sprintf(__('Enter your delivery fee per %s. The system will use this to calculate the delivery fee depending on the distance from your shop to customer location.', 'frozr-norsani'),$distant_unit),
		'set_delivery_peritem' => __('Delivery fee per additional product after the first product.', 'frozr-norsani'),
		'set_delivery_minord' => __('What is the minimum order amount to allow customers to make delivery orders?', 'frozr-norsani'),
		'set_delivery_peak' => __('Enter values below if you wish to set different delivery fees whenever you are having a number of processing orders, or set "Processing Orders" to 0 if you do not wish to use different delivery fees.', 'frozr-norsani'),
		'set_delivery_po' => __('How many processing orders you should be having before using these delivery fees on new orders?', 'frozr-norsani'),
		'set_store_notice' => __('Add a notice or announcement on your shop page.', 'frozr-norsani'),
		'set_social' => __('If you have accounts on social media you can add their links here. Social icons will appear on your shop page header.', 'frozr-norsani'),
		'set_timing' => __('The system will use these timing settings to open or close your shop automatically every day. However, you can at any time manually set your shop to opened (online) or closed (offline) by clicking on the shop icon in the top bar.', 'frozr-norsani'),
		'set_time' => __('Click open if your shop will open in this day, and enter the opening and closing timings. Please note here that the opening and closing timings must be within the same day. i.e if your shop will open from (08:00 pm) till (02:00 am) which means it will close at the next day, you should set the timing for today from 08:00 pm (20:00) till 11:59 pm (23:59) and set the remaining two hours in the "First Shift" timing fields of the next day.', 'frozr-norsani'),
		'set_time_inputs' => __('If your browser does not support the "time" inputs please enter the time in 24 hour format like: 16:20', 'frozr-norsani'),
		'set_time_unava' => __('Do you close on national holidays or some special events? if yes, please enter days when you will not be available.', 'frozr-norsani'),
		'set_withdraw' => __('Details here are used in withdrawal requests.', 'frozr-norsani'),
		'dash_items' => __('All products available on your online shop are listed here. By default, products are marked as "online" after posting them which means they are visible on your shop page.', 'frozr-norsani'),
		'dash_item_det' => __('Describe this product in few words. This will appear in the product full page.', 'frozr-norsani'),
		'dash_item_cat' => __('Type the categories this product belongs to, hit the comma button on your keyboard after typing each category name or click on the category name if appeared in the predictions list. The system will use the products categories to group products on your shop page.', 'frozr-norsani'),
		'dash_item_menu' => __('Select the menu that includes this product. press ‘ctrl’ on your keyboard and click to select multiple menus.', 'frozr-norsani'),
		'dash_item_ing' => __('Type the main ingredients of the product so customers can see them before ordering. This information helps avoiding complains and bad reviews. Hit the comma button after typing each ingredient or select from the predictions list.', 'frozr-norsani'),
		'dash_item_pretime' => __('Enter the time required to prepare/handle this product for delivery, pickup, dine-in, and curbside. Leave 0 for immediately.', 'frozr-norsani'),
		'dash_item_max_orders' => __('The maximum numbers of orders that can be made for this product each day. leave 0 for unlimited number of orders.', 'frozr-norsani'),
		'dash_item_desc' => __("A complete description on the product. This will appear in the product's full page.", "frozr-norsani"),
		'dash_item_var' => __('Check this box if the product is available in different sizes, flavors ...etc or comes with different add-ons, drinks or side items.', 'frozr-norsani'),
		'dash_item_var_admin' => __('This variation can be edited from the admin side only.', 'frozr-norsani'),
		'dash_item_var_title' => __('Name of the variation family. i.e: Select Size, Choose Flavor, Choose a Drink, ...etc', 'frozr-norsani'),
		'dash_item_vars' => __('Values of the variation family. i.e: if "choose of protein" was the variation name, then the values could be: pork | beef | chicken. Please separate the values by |', 'frozr-norsani'),
		'dash_item_vardesc' => __('Describe this variation in few words. i.e: Explain what the customer will get when choosing this option or how this option is different from other options.', 'frozr-norsani'),
		'dash_item_promo' => __('Specify the quantity the customer MUST buy to benefit from this promotion. Please note here that the customer must buy the exact quantity, no more, no less.', 'frozr-norsani'),
		'dash_item_promo_type' => __('This indicates the promotion type. If a free product is selected the product will be added as a detail under the main product after the customer make a purchase and not as a separated cart product.', 'frozr-norsani'),
		'dash_item_promo_desc' => __('Specify the discount amount in percentage to deduct from the main product price.', 'frozr-norsani'),
		'dash_item_promo_item' => __('Select the free product the customer will get from this promotion.', 'frozr-norsani'),
		'dash_item_upsell' => __('The "Up-sell Products" are those products that you recommend to the customer instead of the currently viewed product. i.e: You can select some products that have a discount price or newly added products or products that have promotions. This is useful to convince your customers to test other products that have low sales.', 'frozr-norsani'),
		'dash_item_crsell' => __('The "Cross-sell Products" are those products that you promote to customers when they are viewing their shopping cart. You can add products that you would wish your customers to buy when they have bought this product.', 'frozr-norsani'),
		'dash_vendors' => __('All users registered as "seller" are listed and managed from this page. This is the main page to activate or deactivate selling privileges for newly registered vendors unless you have chosen to automatically activate them from the admin settings. You can also click on the vendor email address to send them an email message directly from here.', 'frozr-norsani'),
		'dash_vendors_activate' => __('When a vendor selling privileges is deactivated, their shop page and products will be hidden from customers but not deleted. Yet previous and current orders will still appear to customers but the vendor will no longer be able to manage or process them. To completely remove a vendor and his products, delete the vendor (user) from the "users page" in the WordPress admin.', 'frozr-norsani'),
		'dash_vendors_invite' => __('This will send an email message using your admin email address as sender.', 'frozr-norsani'),
		'min_del_order' => __('The minimum amount of the order to make a delivery order.', 'frozr-norsani'),
		'calculate_prepare_time' => __('When customer adds more than one of your products to cart, the system will calculate the preparation time for the order by getting the longest product preparation time multiplied by a percentage of the preparation time of other products. i.e. if you set 25, and customer has added two of your products, one has 15 mins and the second have 7 mins preparation time, the total preparation time for the order will be 17 mins', 'frozr-norsani'),
	));
	if (is_super_admin()) {
		$help_text_list['dash_home_sales'] = __('Here you can get statistics info of your vendors sales. By default, you will see results for all vendors, but you can select to see results for an individual vendor as well. The results table is set by default to show sales for today, but you can see results for different periods as well. You can also click the print button if you wish to print the current results.', 'frozr-norsani');
		$help_text_list['dash_home_orders'] = __('This will show you a general count of all orders made on your website grouped by order status. You can click on the status title to navigate to the respective orders page.', 'frozr-norsani');
		$help_text_list['dash_home_top_selling'] = __('Here you can get information on products having the most sales on your website.', 'frozr-norsani');
		$help_text_list['dash_orders'] = __('This is the main page to monitor your vendors incoming orders. Orders are listed from oldest to newest. Whenever you are in this page, the system will automatically update the orders list every minute. By default, you are only supposed to monitor and remind your vendors if some orders are taking too long to process. In addition of managing "on-hold orders" which are still not paid.', 'frozr-norsani');
		$help_text_list['dash_coupons'] = __('Vendors can create discount coupons to increase sales and drive traffic on their products or reward some customers. As an admin you will not be able to add coupons but only view existing ones and edit or delete them.', 'frozr-norsani');
	}
	if ($textonly && isset($help_text_list[$help_key])) {
		echo $help_text_list[$help_key];
	} elseif (isset($help_text_list[$help_key])) {
	?>&nbsp;<div class="frozr_popup_help_wrapper"><a href="#" title="<?php echo __('Click for more info...','frozr-norsani'); ?>"><i class="material-icons">help_outline</i></a><span class="frozr_popup_help_text"><?php echo $help_text_list[$help_key]; ?></span></div><?php
	}
}
}

/**
 * Check if PayPal payout is active
 *
 * @return bool
 */
if (!function_exists ('frozr_payout_active') ) {
function frozr_payout_active() {
$option = get_option( 'frozr_withdraw_settings' );
$pay_instantly = isset($option['frozr_pay_vendors_instantly_paypal']) ? 1 : false;
$clientid = (! empty( $option['frozr_paypal_clientid']) ) ? $option['frozr_paypal_clientid'] : false;
$clientsecret = (! empty( $option['frozr_paypal_clientsecret']) ) ? $option['frozr_paypal_clientsecret'] : false;

if(!$pay_instantly || !$clientid || !$clientsecret ) {
return false;
}
return true;
}
}

/**
 * Get the PayPal payout error
 *
 * @param object $object		Error object
 * @param string $error			Error text.
 * @return string
 */
if (!function_exists ('frozr_get_payout_error') ) {
function frozr_get_payout_error($object, $error) {
	if ($object) {
		if (is_a($object, 'PayPal\Common\PayPalModel')) {
			/** @var $object \PayPal\Common\PayPalModel */
			return $object->toJSON(128);
		} elseif (is_string($object) && \PayPal\Validation\JsonValidator::validate($object, true)) {
			return str_replace('\\/', '/', json_encode(json_decode($object), 128));
		} elseif (is_string($object)) {
			return $object;
		} else {
			return implode(',',$object);
		}
	} elseif($error) {
		return __('ERROR:','frozr-norsani').' '.$error;
	} else {
		return __("Something went wrong",'frozr-norsani');
	}
	return $output;
}
}

/**
 * Get vendors archive page title
 *
 * @param string $vendors		Vendors type.
 * @return string
 */
if (!function_exists ('frozr_vendor_archive_title') ) {
function frozr_vendor_archive_title($vendors) {
	$vendors_array = frozr_get_default_vendors_types();
	$vendor_page_name = isset($vendors_array[$vendors]) ? $vendors_array[$vendors] : __('Vendors','frozr-norsani');
	return $vendor_page_name;
}
}

/**
 * Change the default WP emails sender email address
 *
 */
function frozr_email_sender( $default ) {
	return get_option( 'admin_email' );
}
add_filter( 'wp_mail_from', 'frozr_email_sender' );

/**
 * Change the default WP emails sender name
 *
 */
function frozr_sender_name( $default ) {
	$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	return $site_name;
}
add_filter( 'wp_mail_from_name', 'frozr_sender_name' );

/**
 * Get preparation time options for order
 *
 * @param int $vendor		Vendor ID.
 * @param int $item_id		ID of any item in the customer's cart.
 * @return void
 */
if (!function_exists ('frozr_get_order_pretime_options') ) {
function frozr_get_order_pretime_options($vendor,$item_id) {
	$order_timing = norsani()->vendor->frozr_get_item_timings($vendor,$item_id);
	$timing_det = '';
	$now = date('H:i', strtotime(current_time('mysql')));
	$current = new DateTime($now);
	$current_det = new DateTime(date('y-m-d', strtotime(current_time('mysql'))));
	$day = current_time('D');
	$meal_types = get_user_meta($vendor, '_rest_meal_types', true);
	$filterd_opts = is_array($meal_types) ? array_filter($meal_types[0]) : false;
	
	if (!empty($order_timing)) {
		$sep = ' '.__('or','frozr-norsani').' ';
		$timing_det = ' '.__('Choose a time between','frozr-norsani').' '.implode($sep,$order_timing);
	}
	$order_pretime_desc = __('Select the time to start preparing/handling this order.', 'frozr-norsani').$timing_det.' ('.__('If your browser does not support the "time" inputs please enter the time in 24 hour format like: 16:20','frozr-norsani').')';
	
	/*Get available days*/
	$unds = ('' != get_user_meta($vendor, '_rest_unavds', true)) ? get_user_meta($vendor, '_rest_unavds', true) : array();
	$filterd_inds = array_filter($unds);
	$restime = get_user_meta( $vendor, 'rest_open_close_time',true );
	$ava_days = array();
	$count = 0;

	for ($x = 0; $x <= 15; $x++) {
	
	$tm = date('D', strtotime("+$x day"));
	$rest_closing_one = isset($restime[$tm]['close_one']) ? new DateTime(date('H:i', strtotime($restime[$tm]['close_one']))) : null;
	$rest_shifts = isset($restime[$tm]['restshifts']) ? $restime[$tm]['restshifts'] : null;
	$rest_closing_two = isset($restime[$tm]['close_two']) ? new DateTime(date('H:i', strtotime($restime[$tm]['close_two']))) : null;
	
	if ($count > 5) {
		break;
	}
	if (!isset($restime[$tm]['restop'])) {
		continue;
	}
	
	if (!frozr_is_rest_open($vendor) && $x == 0) {
	if (!$rest_shifts && $rest_closing_one < $current) {
		continue;
	} elseif ($rest_shifts && $current > $rest_closing_two) {
		continue;
	}
	}
	
	$date_ava = true;
	if (!empty($filterd_inds)) {
	foreach ($filterd_inds as $unava_date => $unava_value) {
		if (!empty($unava_value['start']) && !empty ($unava_value['end'])) {
		$today = new DateTime(date('d M', strtotime("+$x day")));
		$unstart_date = new DateTime(date($unava_value['start']));
		$unend_date = new DateTime(date($unava_value['end']));
		if ($unstart_date <= $today && $today < $unend_date) {
			$date_ava = false;
			break;
		}
		}
	}
	}
		
	if (apply_filters('frozr_before_adding_vendor_ava_date',$date_ava,$vendor,$x)) {
		$ava_days[date('d M', strtotime("+$x day"))] = date_i18n('d M', strtotime("+$x day"));
		if ($count == 0) {
			$day = date('D', strtotime("+$x day"));
			$day_det = new DateTime(date('y-m-d', strtotime("+$x day")));
		}
		$count++;
	}
	}

	if (!$filterd_opts && !frozr_is_rest_open($vendor)) {
		$rest_opening_one = isset($restime[$day]['open_one']) ? new DateTime(date('H:i', strtotime($restime[$day]['open_one']))) : null;
		$rest_shifts = isset($restime[$day]['restshifts']) ? $restime[$day]['restshifts'] : null;
		$rest_opening_two = isset($restime[$day]['open_two']) ? new DateTime(date('H:i', strtotime($restime[$day]['open_two']))) : null;
		if ($current_det < $day_det) {
			if ($rest_opening_one) {
				$now = $rest_opening_one->format('H:i');
			} elseif ($rest_shifts && $rest_opening_two) {
				$now = $rest_opening_two->format('H:i');
			}
		} else {
			if ($rest_opening_one && $current < $rest_opening_one) {
				$now = $rest_opening_one->format('H:i');
			} elseif ($rest_shifts && $rest_opening_two) {
				$now = $rest_opening_two->format('H:i');
			}
		}
	}
?>
<div class="frozr_pretime_form">
	<div>
	<label for="frozr_ord_pre_date_<?php echo $vendor; ?>"><i class="material-icons">event</i></label>
	<select data-vendor="<?php echo $vendor; ?>" name="frozr_ord_pre_date_<?php echo $vendor; ?>">
		<?php foreach($ava_days as $key => $val) { ?>
		<option value="<?php echo $key; ?>"><?php echo $val;?></option>
		<?php } ?>
	</select>
	</div>
	<div>
	<label for="frozr_ord_pre_time_<?php  ?>"><i class="material-icons">alarm</i><div class="frozr_popup_help_wrapper"><a href="#" title="<?php echo __('Click for more info...','frozr-norsani'); ?>"><i class="material-icons">help_outline</i></a><span class="frozr_popup_help_text"><?php echo $order_pretime_desc; ?></span></div></label>
	<input type="time" data-vendor="<?php echo $vendor; ?>" value="<?php echo apply_filters('frozr_order_default_time',$now,$vendor,$item_id); ?>" min="<?php echo apply_filters('frozr_order_default_time',$now,$vendor,$item_id); ?>"name="frozr_ord_pre_time_<?php echo $vendor; ?>"/>
	</div>
	<input type="hidden" value="0" class="frozr_order_time_changed_<?php echo $vendor; ?>" name="frozr_order_time_changed_<?php echo $vendor; ?>">
</div>
<?php
}
}

/**
 * Get the default time and dates formats
 *
 * @param string $return		Return time only or date only or both?
 * @return string
 */
if (!function_exists ('frozr_get_time_date_format') ) {
function frozr_get_time_date_format($return='time') {
	if ($return == 'date_time') {
		return get_option( 'date_format' ) . ', ' . get_option( 'time_format' );
	} elseif($return == 'date') {
		return get_option( 'date_format' );
	} else {
		return get_option( 'time_format' );
	}
}
}

/**
 * Get website custom logo image link.
 *
 * @since  1.9.0
 * @return string|bool
 */
if (function_exists( 'child_frozr_get_logo_img_url' ) ) {
	function frozr_get_logo_img_url() {child_frozr_get_logo_img_url();}
} else {
	function frozr_get_logo_img_url() {
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		$image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
		if(isset($image[0])){
			return $image[0];
		}
			return false;
	}
}

/**
 * Wrapper for norsani_doing_it_wrong.
 *
 * @since  1.9.0
 * @param string $function Function used.
 * @param string $message Message to log.
 * @param string $version Version the message was added in.
 */
function norsani_doing_it_wrong( $function, $message, $version ) {
	// @codingStandardsIgnoreStart
	$message .= ' Backtrace: ' . wp_debug_backtrace_summary();

	if ( is_ajax() ) {
		do_action( 'doing_it_wrong_run', $function, $message, $version );
		error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
	} else {
		_doing_it_wrong( $function, $message, $version );
	}
	// @codingStandardsIgnoreEnd
}


/**
 * Get orders that have delivery items
 *
 * @param array	$order_status	Get orders with specific status. Default to wc-processing
 * @return array
 */
function frozr_get_delivery_orders($order_status = array('wc-processing')) {
	global $wpdb;
	$get_order_status = implode( "','", $order_status );

	$get = "SELECT DISTINCT order_items.order_id as id
		FROM {$wpdb->prefix}woocommerce_order_items as order_items
		LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
		LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
		WHERE order_items.order_item_id IN (SELECT order_item_id FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE meta_value = 'delivery')
		AND posts.post_status IN ( '%s' )
		AND order_items.order_item_type = 'line_item'";

	$results = $wpdb->get_results( $wpdb->prepare( $get, $get_order_status ), ARRAY_A );
	
	return $results;
}

/**
 * Disable shipping request from paypal
 *
 * @param bool	$needs_shipping
 * @return bool
 */
add_filter( 'woocommerce_cart_needs_shipping', 'norsani_disable_cart_needs_shipping',10,1);
function norsani_disable_cart_needs_shipping($needs_shipping) {
	$needs_shipping = false;
	return $needs_shipping;
}

/**
 * Check if order has delivery items
 *
 * @param string	$order_id
 * @param array	$order_status	Check orders with specific status. Default to wc-processing
 * @return bool
 */
function frozr_check_delivery_order($order_id, $order_status = array('wc-processing')) {
	$del_orders = frozr_get_delivery_orders($order_status);
	
	foreach($del_orders as $key => $value) {
		if ($value['id'] == $order_id) {
			return true;
		}
	}
	return false;
}

/**
 * Dashboard Total Sales call function
 *
 * @param string	$type	Results for what period. today, week, month, lastmonth, year, custom, beginning.
 * @param string	$start	Start date for the results Y-m-d.
 * @param string	$end	End date for the results Y-m-d.
 * @param integer	$user	Vendor id to get results for.
 * @return array
 */
function frozr_dash_total_sales( $type = 'today', $start = '', $end = '', $user=null) {
	return norsani()->dashboard->frozr_dash_total_sales($type, $start, $end, $user);
}

/**
 * Output dashboard totals table call function
 *
 * @param string	$type	Results for what period. today, week, month, lastmonth, year, custom, beginning.
 * @param string	$start	Start date for the results Y-m-d.
 * @param string	$end	End date for the results Y-m-d.
 * @param integer	$user	Vendor id to get results for.
 * @return void
 */
function frozr_dashboard_totals( $type = 'today', $start = '', $end = '', $user=null) {
	return norsani()->dashboard->frozr_dashboard_totals($type, $start, $end, $user);
}

/**
 * Output dashboard totals call function
 *
 * @param string	$css_class	optional add a css class output div.
 * @return void
 */
function frozr_output_totals($css_class = '') {
	return norsani()->dashboard->frozr_output_totals($css_class);
}

/**
 * Dashboard top selling items call function
 *
 * @return void
 */
function frozr_dash_top_items() {
	return norsani()->dashboard->frozr_dash_top_items();
}

/**
 * Dashboard vendor balance call function
 *
 * @return void
 */
function frozr_dash_rest_balance() {
	return norsani()->dashboard->frozr_dash_rest_balance();
}

/**
 * Dashboard top customers
 *
 * @return void
 */
function frozr_dash_top_customers() {
	return norsani()->dashboard->frozr_dash_top_customers();
}

/**
 * Dashboard orders overview call function
 *
 * @return void
 */
function frozr_dash_orders() {
	return norsani()->dashboard->frozr_dash_orders();
}

/**
 * Generate an input field based on arguments call function
 *
 * @param int $post_id
 * @param string $meta_key
 * @param array $attr
 * @param string $type
 * @return void
 */
function frozr_post_input_box( $post_id, $meta_key, $attr = array(), $type = 'text'  ) {
	return norsani()->fields->frozr_post_input_box($post_id, $meta_key, $attr, $type);
}

/**
 * Hidden field call function
 *
 * @param array $field
 * @return void
 */
function frozr_wp_hidden_input( $field ) {
	return norsani()->fields->frozr_wp_hidden_input($field);
}

/**
 * Textarea field call function
 *
 * @param array $field
 * @return void
 */
function frozr_wp_textarea_input( $field ) {
	return norsani()->fields->frozr_wp_textarea_input($field);
}

/**
 * Select field call function
 *
 * @param array $field
 * @return void
 */
function frozr_wp_select( $field ) {
	return norsani()->fields->frozr_wp_select($field);
}

/**
 * Output a radio input box call function
 *
 * @param object $product
 * @param array $field
 * @return void
 */
function frozr_order_type_radio( $product, $field ) {
	return norsani()->fields->frozr_order_type_radio($product, $field);
}

/**
 * Text Input field call function
 *
 * @param array $field
 * @return void
 */
function frozr_wp_text_input( $field ) {
	return norsani()->fields->frozr_wp_text_input($field);
}

/**
 * Get orders total for sellers call function
 *
 * @param object $order
 * @param bool $inc_fee		Include delivery fee or not?
 * @param bool $inc_sub		Inlcude the order sub total or not?
 * @return string
 */
function frozr_get_seller_total_order( $order, $inc_fee = true, $inc_sub = true) {
	return norsani()->order->frozr_get_seller_total_order( $order, $inc_fee, $inc_sub);
}

/**
 * Get order vendor call function
 *
 * @param int $orderid
 * @return int
 */
function frozr_get_order_author($orderid) {
	return norsani()->order->frozr_get_order_author( $orderid );
}

/**
 * Get vendor info based on seller ID call function
 *
 * @param int $seller_id
 * @return array
 */
function frozr_get_store_info( $seller_id ) {
	return norsani()->vendor->frozr_get_store_info( $seller_id );
}

/**
 * Get vendor page url of a vendor call function
 *
 * @param int $user_id
 * @return string
 */
function frozr_get_store_url( $user_id ) {
	return norsani()->vendor->frozr_get_store_url( $user_id );
}

/**
 * Check if vendor is open call function
 *
 * @param int $seller_id
 * @return bool
 */
function frozr_is_rest_open($seller_id) {
	return norsani()->vendor->frozr_is_rest_open( $seller_id );
}

/**
 * Get delivery settings of vendor call function
 *
 * @param int $current_vendor	Vendor id.
 * @param string $setting 		What setting to return.
 * @param bool $no_cal 			Return a calculated result.
 * @param string $address 		Address to calculate result on.
 * @return string
 */
function frozr_delivery_settings($current_vendor, $setting, $no_cal = false, $address = null) {
	return norsani()->vendor->frozr_delivery_settings( $current_vendor, $setting, $no_cal, $address );
}

/**
 * Get data from distance table call function
 *
 * @param int $vendor_id
 * @param string $originAddresses 		Address set by the customer.
 * @param bool $get_duration			Get the duration too?
 * @return string
 */
function frozr_get_distance($vendor_id,$originAddresses=null,$get_duration=false) {
	return norsani()->vendor->frozr_get_distance( $vendor_id,$originAddresses,$get_duration );
}

/**
 * Check if vendor is manually online call function
 *
 * @return bool
 */
function frozr_manual_vendor_online() {
	return norsani()->vendor->frozr_manual_vendor_online();
}