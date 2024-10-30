<?php
/**
 * Dashboard View: Coupons page Header
 *
 * @package Norsani/Dashboard/Coupon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$permalink = home_url( '/dashboard/coupons/');
$is_edit_page = isset( $_GET['view'] ) && $_GET['view'] == 'add_coupons';
if ( !$is_edit_page && !is_super_admin() || $is_edit_page ) { ?>
<div class="coupons-listing-header">
	<?php do_action('frozr_before_coupons_header_list'); ?>
	<?php if ( $is_edit_page ) { ?>
	<div class="coupons_title">
	<a href="<?php echo $permalink; ?>" class="ol_coupons_title"><?php _e( '&larr; Coupons', 'frozr-norsani' ); ?></a>
	</div>
	<?php } ?>
	<?php if ( !$is_edit_page ) { ?>
	<div class="frozr_dash_add_new">
	<a href="<?php echo add_query_arg( array( 'view' => 'add_coupons'), $permalink ); ?>" class="pull-left"><i class="material-icons">add</i><?php _e( 'New Coupon', 'frozr-norsani' ); ?></a>
	</div>
	<?php } ?>
	<?php do_action('frozr_after_coupons_header_list'); ?>
</div>
<?php } ?>