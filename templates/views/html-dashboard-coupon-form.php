<?php
/**
 * Dashboard View: Coupons page list
 *
 * @package Norsani/Dashboard/Coupon
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;
$button_name = __( 'Create Coupon', 'frozr-norsani' );

if ( isset( $_GET['post'] ) && $_GET['action'] == 'edit' ) {
	
	if ( !frozr_is_author( intval($_GET['post']) ) && !is_super_admin() ) {
		wp_die( __( 'Are you cheating?', 'frozr-norsani' ) );
	}

	$post = get_post( intval($_GET['post']) );
	$button_name = __( 'Update Coupon', 'frozr-norsani' );

	$discount_type = get_post_meta( $post->ID, 'discount_type', true );
	$amount = get_post_meta( $post->ID, 'coupon_amount', true );
	$products = get_post_meta( $post->ID, 'product_ids', true );
	$exclude_products = get_post_meta( $post->ID, 'exclude_product_ids', true );
	$product_categories = get_post_meta( $post->ID, 'product_categories', true );
	$exclude_product_categories = get_post_meta( $post->ID, 'exclude_product_categories', true );
	$usage_limit = get_post_meta( $post->ID, 'usage_limit', true );
	$usage_limit_per_user = get_post_meta( $post->ID, 'usage_limit_per_user', true );
	$limit_usage_to_x_items = get_post_meta( $post->ID, 'limit_usage_to_x_items', true );
	$expire = get_post_meta( $post->ID, 'expiry_date', true );
	$apply_before_tax = get_post_meta( $post->ID, 'apply_before_tax', true );
	$show_cp_inshop = get_post_meta( $post->ID, 'show_cp_inshop', true );
	$show_cp_inshop_txt = get_post_meta( $post->ID, 'show_cp_inshop_txt', true );
	$free_shipping = get_post_meta( $post->ID, 'free_shipping', true );
	$individual_uses = get_post_meta( $post->ID, 'individual_use', true );
	$exclude_sale_item = get_post_meta( $post->ID, 'exclude_sale_items', true );
	$minimum_amount = get_post_meta( $post->ID, 'minimum_amount', true );
	$maximum_amount = get_post_meta( $post->ID, 'maximum_amount', true );
	$customer_email = get_post_meta( $post->ID, 'customer_email', true );
	
	$post_id = isset( $post->ID ) ? $post->ID : 0;
	$post_title = isset( $post->post_title ) ? $post->post_title : '';
	$description = isset( $post->post_content ) ? $post->post_content : '';
} else {
	$post_id = 0;
	$post_title = '';
	$description = '';
}
$discount_type = isset( $discount_type ) ? $discount_type : '';
if ( isset( $discount_type ) ) {
	if ( $discount_type == 'coupon_percent_product' ) {
	$discount_type = 'selected';
	}
}

$amount = isset( $amount ) ? $amount : '';
$products = isset( $products ) ? $products : '';
$exclude_products = isset( $exclude_products ) ? $exclude_products : '';
$product_categories = isset( $product_categories ) ? $product_categories : '';
$exclude_product_categories = isset( $exclude_product_categories ) ? $exclude_product_categories : '';
$usage_limit = isset( $usage_limit ) ? $usage_limit : '';
$usage_limit_per_user = isset( $usage_limit_per_user ) ? $usage_limit_per_user : '';
$limit_usage_to_x_items = isset( $limit_usage_to_x_items ) ? $limit_usage_to_x_items : '';
$expire = isset( $expire ) ? $expire : '';
$show_cp_inshop_txt = isset( $show_cp_inshop_txt ) ? $show_cp_inshop_txt : '';

if ( isset( $show_cp_inshop ) && $show_cp_inshop == 'yes' ) {
	$show_cp_inshop = 'checked';
} else {
	$show_cp_inshop = '';
}

if ( isset( $free_shipping ) && $free_shipping == 'yes' ) {
	$free_shipping = 'checked';
} else {
	$free_shipping = '';
}

if ( isset( $individual_uses ) && $individual_uses == 'yes' ) {
	$individual_uses = 'checked';
} else {
	$individual_uses = '';
}

if ( isset( $apply_before_tax ) && $apply_before_tax == 'yes' ) {
	$apply_before_tax = 'checked';
} else {
	$apply_before_tax = '';
}

if ( isset( $exclude_sale_item ) && $exclude_sale_item == 'yes' ) {
	$exclude_sale_item = 'checked';
} else {
	$exclude_sale_item = '';
}

$minimum_amount = isset( $minimum_amount ) ? $minimum_amount : '';
$maximum_amount = isset( $maximum_amount ) ? $maximum_amount : '';
$customer_email = isset( $customer_email ) ? implode( ',', $customer_email ) : '';

?>
<form id="coupons_form" method="post" action="" class="coupons_form">
<div class="form-group coupons-form-group">
	<label class="coupons-control-label" for="title"><?php _e( 'Coupon Code', 'frozr-norsani' ); frozr_inline_help_db('dash_coupons_title'); ?><span class="required"> *</span></label>
	<input id="title" name="title" required value="<?php echo esc_attr( $post_title ); ?>" placeholder="Title" class="form-control input-md" type="text">
</div>
<div class="form-group coupons-form-group">
	<label class="coupons-control-label" for="description"><?php _e( 'Description', 'frozr-norsani' ); frozr_inline_help_db('dash_coupons_desc'); ?></label>
	<div class="coupons-control-input">
		<textarea class="form-control" id="description" name="description"><?php echo esc_textarea( $description ); ?></textarea>
	</div>
</div>
<div class="form-group coupons-form-group">
	<label class="coupons-control-label" for="discount_type"><?php _e( 'Discount Type', 'frozr-norsani' ); ?></label>
	<div class="coupons-control-input">
		<select id="discount_type" name="discount_type" class="form-control" data-role="none">
			<option value="fixed_product" <?php selected( $discount_type, 'fixed_product'); ?>><?php _e( 'Fixed amount', 'frozr-norsani' ); ?></option>
			<option value="percent_product" <?php selected( $discount_type, 'percent_product'); ?>><?php _e( '% Discount', 'frozr-norsani' ); ?></option>
		</select>
	</div>
</div>
<div class="form-group coupons-form-group">
	<label class="coupons-control-label" for="amount"><?php _e( 'Amount', 'frozr-norsani' ); frozr_inline_help_db('dash_coupons_amount'); ?><span class="required"> *</span></label>
	<input id="amount" required value="<?php echo esc_attr( $amount ); ?>" name="amount" placeholder="Amount" class="form-control input-md" type="text">
</div>

<?php do_action('frozr_coupons_form_ins', $post_id); ?>

<div class="form-group">
	<label class="control-label" for="email_restrictions"><?php _e( 'Email Restrictions', 'frozr-norsani' ); frozr_inline_help_db('dash_coupons_emails'); ?></label>
	<input id="email_restrictions" value="<?php echo esc_attr( $customer_email ); ?>" name="email_restrictions" placeholder="<?php _e( 'Email Restrictions', 'frozr-norsani' ); ?>" class="form-control input-md" type="text">
</div>
<div class="form-group">
	<label class="control-label" for="usage_limit"><?php _e( 'Usage Limit per coupon', 'frozr-norsani' ); frozr_inline_help_db('dash_coupons_coplimt'); ?></label>
	<input id="usage_limit" value="<?php echo esc_attr( $usage_limit ); ?>" name="usage_limit" placeholder="<?php _e( 'Unlimited usage', 'frozr-norsani' ); ?>" class="form-control input-md" type="number">
</div>
<div class="form-group">
	<label class="control-label" for="limit_usage_to_x_items"><?php _e( 'Limit usage to X products', 'frozr-norsani' ); frozr_inline_help_db('dash_coupons_itemlimt'); ?></label>
	<input id="limit_usage_to_x_items" value="<?php echo esc_attr( $limit_usage_to_x_items ); ?>" name="limit_usage_to_x_items" placeholder="<?php _e( 'Apply to all qualifying products in cart', 'frozr-norsani' ); ?>" class="form-control input-md" type="number">
</div>
<div class="form-group">
	<label class="control-label" for="usage_limit_per_user"><?php _e( 'Usage limit per user', 'frozr-norsani' ); frozr_inline_help_db('dash_coupons_usrlimt'); ?></label>
	<input id="usage_limit_per_user" value="<?php echo esc_attr( $usage_limit_per_user ); ?>" name="usage_limit_per_user" placeholder="<?php _e( 'Unlimited usage', 'frozr-norsani' ); ?>" class="form-control input-md" type="number">
</div>
<div class="form-group">
	<label class="control-label" for="frozr-expire"><?php _e( 'Expire Date', 'frozr-norsani' ); ?></label>
	<input id="frozr-expire" value="<?php echo esc_attr( $expire ); ?>" name="expire" placeholder="<?php echo __( 'Expire Date', 'frozr-norsani' ).' YYYY/MM/DD'; ?>" class="form-control input-md" type="date">
</div>
<?php
$user = is_super_admin() ? $post->post_author : get_current_user_id();
$args = apply_filters('frozr_coupon_products_list_args',array(
'post_type' => 'product',
'post_status' => array('publish'),
'posts_per_page' => -1,
'author' => $user,
));

$posts = get_posts( $args );
$products_id = str_replace( ' ', '', $products );
$products_id = explode( ',', $products_id );
?>
<div class="form-group coupons-form-group">
	<label class="coupons-control-label" for="product"><?php _e( 'Products', 'frozr-norsani' ); frozr_inline_help_db('dash_coupons_products'); ?><span class="required"> *</span></label>
	<div class="coupons-control-input">
		<select id="product" required name="product_drop_down[]" class="form-control" multiple data-role="none">
		<?php foreach ($posts as $object) {
			if ( in_array( $object->ID, $products_id ) ) {
				$select = 'selected';
			} else {
				$select = '';
			} ?>
			<option <?php echo $select; ?>  value="<?php echo $object->ID; ?>"><?php _e( $object->post_title, 'frozr-norsani' ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>
<div class="form-group coupons-form-group">
	<label class="coupons-control-label" for="minium_ammount"><?php _e( 'Minimum spend', 'frozr-norsani' ); frozr_inline_help_db('dash_coupons_minspend'); ?></label>
	<input id="minium_ammount" value="<?php echo $minimum_amount; ?>" name="minium_ammount" placeholder="<?php _e('No Minimum', 'frozr-norsani'); ?>" class="form-control input-md" type="text">
</div>
<div class="form-group">
	<label class="control-label" for="maxum_ammount"><?php _e( 'Maximum spend', 'frozr-norsani' ); frozr_inline_help_db('dash_coupons_maxspend'); ?></label>
	<input id="maxum_ammount" value="<?php echo $maximum_amount; ?>" name="maxum_ammount" placeholder="<?php _e('No Maximum', 'frozr-norsani'); ?>" class="form-control input-md" type="text">
</div>
<div class="form-group">
	<div class="coupons-control-input checkbox">
		<label class="control-label" for="checkboxes-0" >
		<input id="checkboxes-0" <?php echo $free_shipping; ?> name="enable_free_ship" class="form-control input-md" value="yes" type="checkbox">
		<?php _e( 'Enable free delivery', 'frozr-norsani' ); frozr_inline_help_db('dash_coupons_del'); ?></label>
	</div>
</div>
<div class="form-group">
	<div class="coupons-control-input checkbox">
		<label class="control-label" for="checkboxes-1" >
		<input id="checkboxes-1" <?php echo $individual_uses; ?> name="individual_use" class="form-control input-md" value="yes" type="checkbox">
		<?php _e( 'Individual use only', 'frozr-norsani' ); frozr_inline_help_db('dash_coupons_induse'); ?></label>
	</div>
</div>
<div class="form-group">
	<div class="coupons-control-input checkbox">
		<label class="control-label" for="checkboxes-2" >
		<input id="checkboxes-2" <?php echo $exclude_sale_item; ?> name="exclude_sale_items" class="form-control input-md" value="yes" type="checkbox">
		<?php _e( 'Exclude discounted products', 'frozr-norsani' ); frozr_inline_help_db('dash_coupons_exsale'); ?></label>
	</div>
</div>
<div class="form-group">
	<div class="coupons-control-input checkbox">
		<label class="control-label" for="show_cp_inshop" >
		<input id="show_cp_inshop" <?php echo $show_cp_inshop; ?> name="show_cp_inshop" class="form-control input-md" value="yes" type="checkbox">
		<?php _e( 'Go public', 'frozr-norsani' ); frozr_inline_help_db('dash_coupons_public'); ?></label>
	</div>
</div>
<div class="form-group">
	<label class="control-label" for="show_cp_inshop_txt" ><?php _e( 'Coupon text to show', 'frozr-norsani' ); frozr_inline_help_db('dash_coupons_text'); ?></label>
	<input id="show_cp_inshop_txt" value="<?php echo esc_attr( $show_cp_inshop_txt ); ?>" name="show_cp_inshop_txt" placeholder="<?php _e( 'i.e: Use (enter coupon code) coupon before checkout to get (10%) discount.', 'frozr-norsani' ); ?>" class="form-control input-md" type="text">
</div>
<?php do_action('frozr_after_add_coupon_form'); ?>
<input type="hidden" value="<?php echo $post_id; ?>" name="post_id">
<input type="submit" name="coupon_creation" value="<?php echo $button_name; ?>" class="update_coupon">
</form>