<?php
/**
 * Dashboard View: Coupons page list
 *
 * @package Norsani/Dashboard/Coupon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $wp_query;
$permalink = home_url( '/dashboard/coupons/');
$coupons_table_atts = 'data-role="table" id="coupons-table" data-mode="reflow"';

if ( have_posts() ) {
do_action('frozr_before_coupons_table'); ?>
<table <?php echo apply_filters('frozr_coupons_table_atts', $coupons_table_atts); ?> class="table_coupons_list ui-responsive table-stroke dash_tables">
	<thead>
		<tr class="table_collumns">
		<th data-priority="1" class="frozr_dash_coupon_code"><?php _e('Code', 'frozr-norsani'); ?></th>
		<th data-priority="5" class="hide_on_mobile"><?php _e('Type', 'frozr-norsani'); ?></th>
		<th data-priority="3" class="hide_on_mobile"><?php _e('Amount', 'frozr-norsani'); ?></th>
		<th data-priority="4"><?php _e('Product IDs', 'frozr-norsani'); ?></th>
		<th data-priority="6"><?php _e('Usage / Limit', 'frozr-norsani'); ?></th>
		<th data-priority="2" class="hide_on_mobile"><?php _e('Expiry date', 'frozr-norsani'); ?></th>
		<th data-priority="7" class="dash_tables_actions"><?php _e('Actions', 'frozr-norsani'); ?></th>
		<?php if(is_super_admin()) { ?>
		<th data-priority="8"><?php _e('Vendor', 'frozr-norsani'); ?></th>
		<?php } ?>
		<?php do_action('frozr_after_list_user_coupons_table_header'); ?>
		</tr>
	</thead>
	<tbody>
	<?php
	while (have_posts()) { the_post();
		$coupon_author = frozr_get_store_info($post->post_author);
		?>
		<tr>
		<td class="coupon-code"><?php $edit_url = add_query_arg( array('post' => $post->ID, 'action' => 'edit', 'view' => 'add_coupons'), $permalink ); ?><div class="code"><a href="<?php echo $edit_url; ?>"><span><?php echo esc_attr( $post->post_title ); ?></span></a></div>
		<div class="frozr_dash_coupon_details hide_on_desktop">
		<span><?php if (get_post_meta( $post->ID, 'discount_type', true ) == 'fixed_product') {echo get_woocommerce_currency_symbol() . esc_attr( get_post_meta( $post->ID, 'coupon_amount', true ) ); } elseif(get_post_meta( $post->ID, 'discount_type', true ) == 'percent_product') {echo esc_attr( get_post_meta( $post->ID, 'coupon_amount', true ) ) .'%';} ?></span>
		<span class="frozr_dash_coupon_expiry_date"><?php echo __('Expiry:','frozr-norsani'); $expiry_date = get_post_meta($post->ID, 'expiry_date', true);if ( $expiry_date ) {echo '<span>' . esc_html( date_i18n( frozr_get_time_date_format('date'), strtotime( $expiry_date ) ) ) . '</span>';} else {echo '<span>N/A</span>';} ?></span>
		</div>
		</td>
		<td class="hide_on_mobile"><span><?php echo esc_html( wc_get_coupon_type( get_post_meta( $post->ID, 'discount_type', true ) ) ); ?></span></td>
		<td class="hide_on_mobile"><span><?php echo esc_attr( get_post_meta( $post->ID, 'coupon_amount', true ) ); ?></span></td>
		<td>
			<?php
			$product_ids = get_post_meta( $post->ID, 'product_ids', true );
			$product_ids = $product_ids ? array_map( 'absint', explode( ',', $product_ids ) ) : array();

			if ( sizeof( $product_ids ) > 0 ) {
				echo '<span>' . esc_html( implode( ', ', $product_ids ) ) . '</span>';
			} else {
				echo '<span>N/A</span>';
			} ?>
		</td>
		<td>
			<?php $usage_count = absint( get_post_meta( $post->ID, 'usage_count', true ) );
			$usage_limit = esc_html( get_post_meta($post->ID, 'usage_limit', true) );

			if ( $usage_limit ) {
				echo '<span>'.$usage_count.' / '.$usage_limit.'</span>';
			} else {
				echo '<span>'.$usage_count.' / &infin;</span>';
			} ?>
		</td>
		<td class="hide_on_mobile">
			<?php $expiry_date = get_post_meta($post->ID, 'expiry_date', true);
			if ( $expiry_date ) {
				echo '<span>' . esc_html( date_i18n( frozr_get_time_date_format('date'), strtotime( $expiry_date ) ) ) . '</span>';
			} else {
				echo '<span>N/A</span>';
			} ?>
		</td>
		<td class="dash_tables_actions">
		<div>
			<?php do_action('frozr_before_coupon_actions', $post); ?>
			<span class="edit"><a href="<?php echo $edit_url; ?>" title="<?php _e( 'Edit', 'frozr-norsani' ); ?>"><?php echo __('Edit','frozr-norsani'); ?></a></span>
			<span class="delete_coupon" data-coupid="<?php echo $post->ID; ?>"><a href="#" title="<?php _e('delete', 'frozr-norsani'); ?>"><?php echo __('Delete','frozr-norsani'); ?></a></span>
			<?php do_action('frozr_after_coupon_actions', $post); ?>
		</div>
		</td>
		<?php if (is_super_admin()) { ?>
		<td><a href="<?php echo frozr_get_store_url($post->post_author); ?>" title="<?php _e('Visit Store','frozr-norsani'); ?>"><?php echo get_the_author_meta('login', $post->post_author) . ' (' . $coupon_author['store_name'] . ')'; ?></a></td>
		<?php } ?>
		<?php do_action('frozr_after_list_user_coupons_table_body'); ?>
		</tr>
	<?php } ?>
	</tbody>
</table>
<?php
if ( $wp_query->max_num_pages > 1 ) {
	frozr_lazy_nav_below(true);
}
wp_reset_query();
?>
<?php } else { ?>
	<div class="style_box alert alert-warning fa-warning-sign">
		<p><?php _e( 'Sorry, no coupons found!', 'frozr-norsani' ); ?></p>
	</div>
<?php
} ?>