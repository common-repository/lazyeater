<?php
/**
 * Orders - Order Details
 *
 * @package Norsani/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

global $woocommerce, $wpdb;

$order_id = apply_filters('frozr_detailed_order_id',isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0, $_GET['order_id']);

$orders = get_post($order_id);

if ( get_current_user_id() != frozr_get_order_author($order_id) && !is_super_admin()) {
    echo '<div class="alert alert-danger">' . __( 'This is not yours, I swear!', 'frozr-norsani' ) . '</div>';
    return;
}
$order = wc_get_order( $order_id );
?>
<div class="order_datails_container">
<div class="order_table">
<div class="or or-default">
	<div class="or-heading"><strong><?php printf( 'Order#%d', $order->get_id() ); ?></strong> &rarr; <?php _e( 'Order items', 'frozr-norsani' ); ?></div>
	<div class="or-body order_items_tbl">
	<?php norsani()->order->frozr_order_items_table($order); ?>
	</div>
</div>
<div class="or or-default order_details">
	<div class="or-heading"><strong><?php _e( 'Customer Details', 'frozr-norsani' ); ?></strong></div>
	<?php norsani()->order->frozr_order_customer_details($order); ?>
</div>
<div class="or or-default order_details">
	<div class="or-heading"><strong><?php _e( 'General Details', 'frozr-norsani' ); ?></strong></div>
	<?php norsani()->order->frozr_order_general_details($order); ?>
</div>
</div>
<div class="or_notes">
<div class="or or-default">
	<div class="or-heading"><strong><?php _e( 'Order Notes', 'frozr-norsani' ); ?></strong></div>
	<div class="or-body" id="frozr-order-notes">
		<?php
		$args = array(
		'post_id'   => $orders->ID,
		'orderby'   => 'comment_ID',
		'order'     => 'DESC',
		'approve'   => 'approve',
		'type'      => 'order_note'
		);

		remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );

		$notes = get_comments( $args );

		add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );

		echo '<ul class="order_notes">';

		if ( $notes ) {

			foreach( $notes as $note ) {

			$note_classes = get_comment_meta( $note->comment_ID, 'is_customer_note', true ) ? array( 'customer-note', 'note' ) : array( 'note' );

			?>
			<li rel="<?php echo absint( $note->comment_ID ) ; ?>" class="<?php echo implode( ' ', $note_classes ); ?>">
				<div class="note_content">
					<?php echo wpautop( wptexturize( wp_kses_post( $note->comment_content ) ) ); ?>
				</div>
				<p class="order_meta">
					<abbr class="exact-date" title="<?php echo $note->comment_date; ?>"><?php printf( __( 'added on %1$s at %2$s', 'frozr-norsani' ), date_i18n( wc_date_format(), strtotime( $note->comment_date ) ), date_i18n( wc_time_format(), strtotime( $note->comment_date ) ) ); ?></abbr>
					<?php if ( $note->comment_author !== __( 'WooCommerce', 'frozr-norsani' ) ) printf( ' ' . __( 'by %1$s', 'frozr-norsani' ), $note->comment_author ); ?>
					<a href="#" class="delete_note"><?php _e( 'Delete note', 'frozr-norsani' ); ?></a>
				</p>
			</li>
			<?php }

		} else {
			echo '<li>' . __( 'There are no notes yet.', 'frozr-norsani' ) . '</li>';
		}

		echo '</ul>';
		?>
		<div class="add_note">
			<h4><?php _e( 'Add note', 'frozr-norsani' ); frozr_inline_help_db('order_note'); ?></h4>
			<p>
				<textarea type="text" name="order_note" id="add_order_note" class="input-text" cols="20" rows="5"></textarea>
			</p>
			<p>
				<select name="order_note_type" id="order_note_type">
					<option value=""><?php _e( 'Private note', 'frozr-norsani' ); ?></option>
					<option value="customer"><?php _e( 'Note to customer', 'frozr-norsani' ); ?></option>
				</select>
				<a href="#" class="add_note button"><?php _e( 'Add', 'frozr-norsani' ); ?></a>
			</p>
		</div>
	</div> <!-- .or-body -->
</div> <!-- .or -->
</div>
</div>