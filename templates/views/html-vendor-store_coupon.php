<?php
/**
 * Shop View: Vendor page coupons
 *
 * @package Norsani/Store/Vendor
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="vendor_coupons_wrapper">
<h2 class="rests_list_title"><i class="material-icons">card_giftcard</i>&nbsp;<?php echo __('Coupons','frozr-norsani'); ?></h2>
<?php foreach($all_coupons as $post) {
	$rest_coupons_attrs = 'class="restu_cpos"';
	$post_id = $post->ID;
	?>
	<div data-id="<?php echo $post_id; ?>" <?php echo apply_filters('frozr_vendor_coupons_attrs', $rest_coupons_attrs, $post_id); ?>>
		<span class="rest_cops"><?php echo esc_attr (get_post_meta( $post_id, 'show_cp_inshop_txt', true )); ?></span>
	</div>
<?php } ?>
</div>