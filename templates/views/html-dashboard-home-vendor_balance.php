<?php
/**
 * Dashboard View: Home page vendor balance
 *
 * @package Norsani/Dashboard/Home
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="dash_totals f-light-blue">
<span class="dash_totals_title"><i class="material-icons">attach_money</i>&nbsp;<?php _e('Account Balance','frozr-norsani'); frozr_inline_help_db('dash_balance'); ?></span>
<div class="dash_current_balance">
	<?php echo wc_price(get_user_meta(get_current_user_id(), '_vendor_balance', true)); ?>
	<?php if (!is_super_admin()) { ?>
	<span class="dash_with_link"><a href="<?php echo home_url( '/dashboard/withdraw/'); ?>" title="<?php _e('Withdraw','frozr-norsani'); ?>"><?php _e('Withdraw Money','frozr-norsani'); ?></a></span>
	<?php } ?>
	<?php do_action('frozr_after_vendor_balance'); ?>
</div>
</div>