<?php
/**
 * Dashboard View: Items page item edit/add form body
 *
 * @package Norsani/Dashboard/Items
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="product-edit-<?php echo $post_id; ?>" class="content-area-product-edit">
<form id="form-<?php echo $post_id; ?>" action="" method="post" class="product_form" data-ajax="false">
	<?php do_action('frozr_product_edit_before_main', $post_id); ?>
	<?php norsani()->item->frozr_output_item_data($post_id, $new); ?>
	<?php do_action('frozr_product_edit_after_main', $post_id); ?>
	<input type="hidden" name="original_post_title" value="<?php echo $product_title; ?>">
	<?php  if ($post_id) { ?>
	<input type="submit" name="submit_product_form" class="button-primary update_product" data-ajax="false" value="<?php _e( 'Update', 'frozr-norsani' ); ?>">
	<?php } else { ?>
	<div class="frozr_dash_new_item_submit">
	<input type="submit" name="submit_product_form" class="button-primary update_product" data-ajax="false" value="<?php _e( 'Post', 'frozr-norsani' ); ?>">
	<input type="button" name="submit_product_form_new" data-new="new_item" data-ajax="false" class="button-primary update_product_new" value="<?php _e( 'Post & Add New', 'frozr-norsani' ); ?>">
	</div>
	<?php } ?>
</form>
</div><!-- #product-edit .content-area-product-edit -->