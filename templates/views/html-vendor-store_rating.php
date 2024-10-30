<?php
/**
 * Shop View: Vendor page rating
 *
 * @package Norsani/Store/Vendor
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="rest_rating_form_wrapper">
	<?php if ( ! is_user_logged_in() ) { ?>
	<form class="rest_rating_login" data-ajax="false" method="post" class="login">
		<span class="vendor_form_group_label"><i class="material-icons">star</i>&nbsp;<?php echo __('Login to make your rating on','frozr-norsani'); ?>&nbsp;<span><?php echo $store_info['store_name']; ?></span></span>
		<div class="frozr_options_group">
		<p class="form-row form-row-wide">
			<label for="rat_username"><?php _e( 'Email address', 'frozr-norsani' ); ?> <span class="required">*</span></label>
			<input type="email" class="input-text" name="rat_username" id="rat_username" value="" required="required"/>
		</p>
		<p class="form-row form-row-wide">
			<label for="rat_password"><?php _e( 'Password', 'frozr-norsani' ); ?> <span class="required">*</span></label>
			<input class="input-text" type="password" name="rat_password" id="rat_password" required="required"/>
		</p>
		<p class="form-row">
			<input type="submit" class="button" name="rat_login" value="<?php _e( 'Login', 'frozr-norsani' ); ?>"  />
		</p>			
		<?php do_action( 'frozr_after_store_rating_form' ); ?>
		</div>
	</form>
	<?php } ?>
	<form class="rest_rating_form" <?php if ( ! is_user_logged_in() ) { echo 'style="display:none;"'; } ?> method="post">
		<span class="vendor_form_group_label"><i class="material-icons">star</i>&nbsp;<?php echo __('Rate','frozr-norsani'); ?>&nbsp;<span><?php echo $store_info['store_name']; ?></span></span>
		<div class="frozr_options_group">
		<select name="restrating" id="restrating" required="required">
			<?php for ( $rating = 1; $rating <= 5; $rating++ ) {
				echo sprintf( '<option value="%1$s">%1$s '.__('Star','frozr-norsani').'</option>', $rating );
			} ?>
		</select>
		<input class="rest_rating_submit" type="submit" data-restid="<?php echo $seller; ?>" data-orderid="<?php echo $orderid; ?>" name="rest_rating_submit" value="<?php _e( 'Submit', 'frozr-norsani' ); ?>" >
		</div>
	</form>	
</div>