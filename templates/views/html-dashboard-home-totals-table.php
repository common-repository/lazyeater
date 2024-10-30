<?php
/**
 * Dashboard View: Home page totals table
 *
 * @package Norsani/Dashboard/Home
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$totals = frozr_dash_total_sales( $type, $start, $end, $user );
?>
<table data-role="table" data-mode="reflow" class="ui-responsive">
	<thead>
		<tr>
		<th data-priority="1"><?php _e('Gross','frozr-norsani'); ?></th>
		<th data-priority="3"><?php _e('Total Coupon Usage','frozr-norsani'); ?></th>
		<?php if (wc_tax_enabled()) { ?>
		<th data-priority="5"><?php _e('Taxes','frozr-norsani'); ?></th>			
		<th data-priority="5"><?php _e('Taxes Refunded','frozr-norsani'); ?></th>			
		<?php } ?>
		<?php if ($type == 'beginning') { ?>
		<th data-priority="6"><?php _e('Uncompleted Orders','frozr-norsani'); ?></th>
		<th data-priority="7"><?php _e('Refunded Orders','frozr-norsani'); ?></th>
		<?php } ?>
		<th data-priority="4"><?php if (!is_super_admin()) echo get_bloginfo( 'name' ) . ' ' .__('Fees','frozr-norsani'); else echo __('Seller Fees','frozr-norsani'); ?></th>
		<?php do_action('frozr_before_dashboard_total_sales_table_header_net'); ?>
		<th data-priority="2"><?php _e('Net Profit','frozr-norsani'); ?></th>
		<?php do_action('frozr_after_dashboard_total_sales_table_header_net'); ?>
		</tr>
	</thead>
	<tbody>
		<tr>
		<td><?php echo wc_price($totals[0]+$totals[18]+$totals[19]); ?></td>
		<td><?php echo wc_price($totals[2]); ?></td>
		<?php if (wc_tax_enabled()) { ?>
		<td><?php echo wc_price($totals[18]); ?></td>
		<td><?php echo wc_price($totals[19]); ?></td>
		<?php } ?>
		<?php if ($type == 'beginning') { ?>
		<td><?php echo wc_price($totals[8]+$totals[10]+$totals[12]+$totals[14]); ?></td>
		<td><?php echo wc_price($totals[4]); ?></td>
		<?php } ?>
		<td><?php echo wc_price($totals[16]); ?></td>
		<?php do_action('frozr_before_dashboard_total_sales_table_body_net'); ?>
		<td><?php echo wc_price($totals[17]); ?></td>
		<?php do_action('frozr_after_dashboard_total_sales_table_body_net'); ?>
		</tr>
	</tbody>
</table>