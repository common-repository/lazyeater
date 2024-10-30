<?php
/**
 * Dashboard View: Home page top ten selling items
 *
 * @package Norsani/Dashboard/Home
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="dash_totals f-black">
	<span class="dash_totals_title"><i class="material-icons">star</i>&nbsp;<?php _e('Top Selling Products','frozr-norsani'); frozr_inline_help_db('dash_home_top_selling'); ?></span>
	<?php if ( !empty($products) ) { ?>
	<table class="dash_top_selling_items">
		<thead>
            <tr class="table_collumns">
			<th data-priority="1"><?php _e( 'Product', 'frozr-norsani' ); ?></th>
			<th data-priority="2"><?php _e( 'Sales', 'frozr-norsani' ); ?></th>
			<?php do_action('frozr_after_dash_top_items_table_header', $products); ?>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $products as $product ) { 
		$productobj = wc_get_product($product->ID);
		?>
		<tr>
			<td class="dast_dtit">
				<a href="<?php echo get_permalink( $product->ID ); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %1$s', 'frozr-norsani' ), the_title_attribute( array('echo' => 0, 'post' => $product->ID) ) ) ); ?>" rel="bookmark"><?php echo get_the_title($product->ID); ?></a>
			</td>
			<td class="dast_psales">
				<?php echo get_post_meta( $productobj->get_id(), 'total_sales', true ); ?>
			</td>
			<?php do_action('frozr_after_dash_top_items_table_body', $products); ?>
		</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
		<div class="vendors_nothing_found">
		<i class="material-icons">attach_money</i>
		<span><?php _e("Your top 10 selling products will be listed here.",'frozr-norsani'); ?></span>
		</div>
	<?php } ?>
</div>