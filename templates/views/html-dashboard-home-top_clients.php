<?php
/**
 * Dashboard View: Home page top ten spending clients
 *
 * @package Norsani/Dashboard/Home
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="dash_totals f-black">
	<span class="dash_totals_title"><i class="material-icons">show_chart</i>&nbsp;<?php _e('Top Customers','frozr-norsani'); frozr_inline_help_db('dash_home_top_customers'); ?></span>
	<?php if (!empty($clients)) { ?>
	<table class="dash_top_selling_items">
	<thead>
		<tr class="table_collumns">
			<th data-priority="1"><?php _e( 'Customer', 'frozr-norsani' ); ?></th>
			<th data-priority="2"><?php _e( 'Money Spent', 'frozr-norsani' ); ?></th>
			<?php do_action('frozr_after_dash_top_customers_table_header'); ?>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach ($clients as $key => $val) { ?>
	<tr>
		<td class="dast_dtit">
			<?php echo $key; ?>
		</td>
		<td class="dast_psales">
			<?php echo wc_price($val); ?>
		</td>
		<?php do_action('frozr_after_dash_top_customers_table_body', $key, $val); ?>
	</tr>
	<?php } ?>
	</tbody>
	</table>
	<?php } else { ?>
		<div class="vendors_nothing_found">
		<i class="material-icons">show_chart</i>
		<span><?php _e("Your top 10 clients will be listed here.","frozr-norsani"); ?></span>
		</div>
	<?php } ?>
</div>