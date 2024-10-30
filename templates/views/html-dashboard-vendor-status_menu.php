<?php
/**
 * Dashboard View: Vendor dashboard status menu
 *
 * @package Norsani/Dashboard/Vendor
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="frozr_vendor_status_menu">
	<div class="frozr_vendor_current_status<?php echo ' '.$cstatus; ?>"><span><?php echo __('Status:','frozr-norsani'); ?>&nbsp;<?php echo $cstatus; ?></span><a href="#!" title="<?php echo __('Change Status?','frozr-norsani'); ?>" class="frozr_vendor_change_sts" data-change="status" data-sts="<?php echo ($active) ? 'offline' : 'online'; ?>"><?php echo __('Go','frozr-norsani'); ?>&nbsp;<?php echo $sstatus; ?></a></div>
	<div class="frozr_vendor_notices_status<?php echo $notice_status; ?>"><span><?php echo __('Play tune on new orders','frozr-norsani'); ?></span><a href="#!" title="<?php echo __('Turn on notifications','frozr-norsani'); ?>" data-sts="1" data-change="notices" class="frozr_vendor_change_sts frozr_vendor_notices_off"><i class="material-icons">notifications_active</i><?php echo __('Active','frozr-norsani'); ?></a><a href="#!" title="<?php echo __('Turn off notifications','frozr-norsani'); ?>" data-sts="0" data-change="notices" class="frozr_vendor_change_sts frozr_vendor_notices_on frozr_hide"><i class="material-icons">notifications_active</i><?php echo __('Active','frozr-norsani'); ?></a></div>
	<p class="frozr_status_inst"><?php printf('<span>'.__('Website time: %1$s','frozr-norsani').' </span>', $nw_display);
		if ($manual_online && !$active) {echo '<span>'.__('Set your status to online if you wish to receive orders.','frozr-norsani').' </span>';}
		if ($manual_status['online'] && !$timing[1] && $max_unactive_time > 0 ) {printf('<span>'.__('Your status will be automatically set to "offline" if you closed or navigated away from the website for %1$s minutes.','frozr-norsani').' </span>', $max_unactive_time);}
		if (!$manual_online && $rstst && $rsts && !$already_closed) {printf('<span>'.__('%1$s from %2$s to %3$s.','frozr-norsani').' </span>',$message, $rstst, $rsts);}
		if (!$manual_online && $already_closed) {'<span>'.$message.' </span>';}
		if (!$rstst || !$rsts) {printf('<span>'.__('No %1$s timings for today.','frozr-norsani').' </span>', '<a href="'.home_url('/dashboard/settings').'" title="'.__('set timing','frozr-norsani').'">'.__('opeining and closing','frozr-norsani').'</a>');}
	?>
	</p>
</div>