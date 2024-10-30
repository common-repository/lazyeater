<?php
/**
 * Dashboard View: Home page totals report
 *
 * @package Norsani/Dashboard/Home
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="dash_totals sales_summary_wid <?php echo $css_class; ?>">
	<span class="dash_totals_title"><span class="print_summary_report"><?php _e('Print table','frozr-norsani'); ?></span><i class="material-icons">assessment</i>&nbsp;<?php echo __('Sales','frozr-norsani'); frozr_inline_help_db('dash_home_sales'); ?><?php if (is_super_admin()) { ?> <select id="seller_summary_select" data-rtype="today" name="seller_summary_select"><option value="all"><?php _e('From all sellers','frozr-norsani'); ?></option><?php if ($sellers_results) { foreach ($sellers_results as $seller_result) { ?> <option value="<?php echo $seller_result; ?>"><?php $user_store = frozr_get_store_info($seller_result); echo $user_store['store_name']; ?></option> <?php }} ?></select> <?php } ?></span>
	<div class="dash_totals_opt"><span data-rtype="beginning" class="show_resutl"><?php _e('All Time','frozr-norsani'); ?></span><span data-rtype="year" class="show_resutl"><?php _e('Year','frozr-norsani'); ?></span><span data-rtype="lastmonth" class="show_resutl"><?php _e('Last Month','frozr-norsani'); ?></span><span data-rtype="month" class="show_resutl"><?php _e('Month','frozr-norsani'); ?></span><span data-rtype="week" class="show_resutl"><?php _e('Week','frozr-norsani'); ?></span><span data-rtype="today" class="show_resutl active"><?php _e('Today','frozr-norsani'); ?></span><span class="show_custom"><i class="material-icons">date_range</i></span>
	<form class="custom_start_end" data-rtype="custom" method="post" style="display:none;">
		<label class="control-label" for="dast_sales_start"><?php echo __('Start Date, i.e:','frozr-norsani').' '.date('Y/m/d',strtotime('-1 day')); ?>
		<input id="dast_sales_start" class="dast_totals_start" value="<?php echo isset($_POST['dast_sales_start']) ? wc_clean($_POST['dast_sales_start']) : date('Y-m-d',strtotime('-1 day')); ?>" name="dast_sales_start" required type="date">
		</label>
		<label class="control-label" for="dast_sales_end"><?php echo __('End Date, i.e:','frozr-norsani').' '.date('Y/m/d',strtotime(current_time('mysql'))); ?>
		<input id="dast_sales_end" class="dast_totals_end" value="<?php echo isset($_POST['dast_sales_end']) ? wc_clean($_POST['dast_sales_end']) : date('Y-m-d',strtotime(current_time('mysql'))); ?>" name="dast_sales_end" required type="date">
		</label>
		<input class="rest_rating_submit" type="submit" value="<?php _e( 'Go', 'frozr-norsani' ); ?>" >
	</form>
	</div>
	<div class="dash_totals_results"><?php frozr_dashboard_totals(); ?></div>
</div>