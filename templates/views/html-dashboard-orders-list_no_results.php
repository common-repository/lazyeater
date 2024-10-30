<?php
/**
 * Dashboard View: Orders page list table no results
 *
 * @package Norsani/Dashboard/Order
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<tr>
	<td colspan="<?php echo $colspan; ?>">
		<div class="frozr-error">
			<?php _e( 'No orders found', 'frozr-norsani' ); ?>
		</div>
	</td>
</tr>