<?php
/**
 * Dashboard View: Orders page main nav
 *
 * @package Norsani/Dashboard/Order
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<ul class="ly_dash_listing_status_filter">
	<li <?php echo $status_class == 'processing' ? ' class="active fa-caret-up"' : ''; ?> >
		<a href="<?php echo add_query_arg( array( 'order_status' => 'processing' ), $orders_url ); ?>">
			<?php printf( __( 'Processing (%d)', 'frozr-norsani' ), frozr_count_user_object('wc-processing', 'shop_order') ); ?></span>
		</a>
	</li>
	<li <?php echo $status_class == 'completed' ? ' class="active fa-caret-up"' : ''; ?> >
		<a href="<?php echo add_query_arg( array( 'order_status' => 'completed' ), $orders_url ); ?>">
			<?php printf( __( 'Completed (%d)', 'frozr-norsani' ), frozr_count_user_object('wc-completed', 'shop_order') ); ?></span>
		</a>
	</li>
	<li <?php echo $status_class == 'on-hold' ? ' class="active fa-caret-up"' : ''; ?> >
		<a href="<?php echo add_query_arg( array( 'order_status' => 'on-hold' ), $orders_url ); ?>">
			<?php printf( __( 'On-hold (%d)', 'frozr-norsani' ), frozr_count_user_object('wc-on-hold', 'shop_order') ); ?></span>
		</a>
	</li>
	<li <?php echo $status_class == 'pending' ? ' class="active fa-caret-up"' : ''; ?> >
		<a href="<?php echo add_query_arg( array( 'order_status' => 'pending' ), $orders_url ); ?>">
			<?php printf( __( 'Pending Payment (%d)', 'frozr-norsani' ), frozr_count_user_object('wc-pending', 'shop_order')); ?></span>
		</a>
	</li>
	<li <?php echo $status_class == 'cancelled' ? ' class="active fa-caret-up"' : ''; ?> >
		<a href="<?php echo add_query_arg( array( 'order_status' => 'cancelled' ), $orders_url ); ?>">
			<?php printf( __( 'Cancelled (%d)', 'frozr-norsani' ), frozr_count_user_object('wc-cancelled', 'shop_order') ); ?></span>
		</a>
	</li>
	<li <?php echo $status_class == 'refunded' ? ' class="active fa-caret-up"' : ''; ?> >
		<a href="<?php echo add_query_arg( array( 'order_status' => 'refunded' ), $orders_url ); ?>">
			<?php printf( __( 'Refunded (%d)', 'frozr-norsani' ), frozr_count_user_object('wc-refunded', 'shop_order') ); ?></span>
		</a>
	</li>
	<li <?php echo $status_class == 'failed' ? ' class="active fa-caret-up"' : ''; ?> >
		<a href="<?php echo add_query_arg( array( 'order_status' => 'failed' ), $orders_url ); ?>">
			<?php printf( __( 'failed (%d)', 'frozr-norsani' ), frozr_count_user_object('wc-failed', 'shop_order') ); ?></span>
		</a>
	</li>
	<?php do_action('frozr_after_dashoboard_order_listing_status_filter'); ?>
</ul>