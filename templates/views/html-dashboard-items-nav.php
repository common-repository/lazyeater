<?php
/**
 * Dashboard View: Items page filter nav
 *
 * @package Norsani/Dashboard/Items
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="frozr_dash_add_new">
<a class="pull-right" href="<?php echo norsani()->item->frozr_edit_item_url(0); ?>"><i class="material-icons">add</i> <?php _e( 'New product', 'frozr-norsani' ); ?></a>
</div>
<ul class="ly_dash_listing_status_filter">
	<li <?php echo $status_class == 'all' ? "class=\"active\"" : ''; ?> >
		<a href="<?php echo $permalink; ?>"><?php printf( __( 'All (%d)', 'frozr-norsani' ), $post_total ); ?></a>
	</li>
	<li <?php echo $status_class == 'publish' ? "class=\"active\"" : ''; ?> >
		<a class="frozr_dash_total_online_items" href="<?php echo add_query_arg( array( 'post_status' => 'publish' ), $permalink ); ?>"><?php printf( __( 'Online (%d)', 'frozr-norsani' ), $post_counts->publish ); ?></a>
	</li>
	<li <?php echo $status_class == 'offline' ? "class=\"active\"" : ''; ?> >
		<a class="frozr_dash_total_offline_items" href="<?php echo add_query_arg( array( 'post_status' => 'offline' ), $permalink ); ?>"><?php printf( __( 'Offline (%d)', 'frozr-norsani' ), $post_counts->offline ); ?></a>
	</li>
	<?php do_action('frozr_after_dash_products_list_filter'); ?>
</ul> <!-- .post-statuses-filter -->