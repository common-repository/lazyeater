<?php
/**
 * Dashboard View: Items page list single item row
 *
 * @package Norsani/Dashboard/Items
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<td class="frozr_dashitems_post_image">
<?php $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full');
echo '<div class="list_product_image" style="background-image: url( '.$large_image_url[0].');">'; ?>
<a href="<?php the_permalink() ?>" title="<?php _e('edit product', 'frozr-norsani'); ?>"></a>
<?php echo '</div>'; ?>
</td>
<td class="frozr_dashitems_post_title">
<p><a href="<?php echo norsani()->item->frozr_edit_item_url( $post->ID ); ?>"><?php echo $product->get_title(); ?></a></p>
<div class="frozr_dashitems_title_details hide_on_desktop">
<?php
if ( $product->get_price_html() ) {echo $product->get_price_html();} else {echo '<span class="na">&ndash;</span>';}
echo '<div class="frozr_dashitems_title_status '. $post->post_status .'">'.norsani()->item->frozr_get_post_status( $post->post_status ).'</div>';
echo '<div class="frozr_dashitems_title_sales">'. __( 'Sales', 'frozr-norsani' ).' '.(int) get_post_meta( $post->ID, 'total_sales', true ).'</div>';
echo '<div class="frozr_dashitems_title_pageviews">'. __( 'Views', 'frozr-norsani' ).' '.(int) get_post_meta( $post->ID, 'frozr_item_views_count', true ).'</div>';
?>
</div>
</td>
<td class="frozr_dashitems_post_status hide_on_mobile">
<span class="item_status_label <?php echo $post->post_status; ?>"><?php echo norsani()->item->frozr_get_post_status( $post->post_status ); ?></span>
</td>
<td class="frozr_dashitems_post_price hide_on_mobile">
<?php if ( $product->get_price_html() ) {echo $product->get_price_html();} else {echo '<span class="na">&ndash;</span>';}?>
</td>
<td class="frozr_dashitems_post_sales hide_on_mobile">
<?php echo (int) get_post_meta( $post->ID, 'total_sales', true ); ?>
</td>
<td class="frozr_dashitems_post_views hide_on_mobile">
<?php
echo (int) get_post_meta( $post->ID, 'frozr_item_views_count', true );
?>
</td>
<td class="dash_tables_actions">
<i class="material-icons">more_vert</i>
<div class="frozr_item_actions_list">
<?php do_action('frozr_before_dashboard_products_actions', $post); ?>
<span class="edit"><a href="<?php echo norsani()->item->frozr_edit_item_url($post->ID); ?>"><?php _e( 'Edit', 'frozr-norsani' ); ?></a></span>
<span class="delete_item" data-item="<?php echo $post->ID; ?>"><?php _e( 'Delete', 'frozr-norsani' ); ?></span>
<span class="view"><a href="<?php echo get_permalink( $post->ID ); ?>" rel="permalink"><?php _e( 'View', 'frozr-norsani' ); ?></a></span>
<span class="frozr_add_special<?php if ($item_special_status==1) {echo ' active';} ?>"><a href="#" data-id="<?php echo $post->ID; ?>" data-sts="<?php if ($item_special_status==1) {echo 'online';}else{echo 'offline';}; ?>" title="<?php echo __('Add this product to today\'s special products list.','frozr-norsani'); ?>"><?php _e( 'Today\'s Special Product', 'frozr-norsani' ); ?></a></span>
<?php do_action('frozr_after_dashboard_products_actions', $post); ?>
</div>
</td>