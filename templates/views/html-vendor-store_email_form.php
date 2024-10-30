<?php
/**
 * Shop View: Vendor page email form
 *
 * @package Norsani/Store/Vendor
 * @since 1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<form id="frozr-form-contact-seller<?php if ($rest) {echo '_'.$seller_id;} ?>" action="" method="post" class="seller-form clearfix">
	<?php if (!$hide_title) { ?>
	<span class="vendor_form_group_label frozr_dash_seller_msg_form_title"><i class="material-icons">email</i>&nbsp;<?php echo __('Send Message to','frozr-norsani'); ?>&nbsp;<span><?php echo $sellerstore; ?></span></span>
	<?php } ?>
	<div class="frozr_options_group">
	<div class="ajax-response"></div>
	<?php if (! $admin) { ?>

	<?php do_action('frozr_befor_vendor_email_form', $seller_id, $admin); ?>

	<?php if ($rest) {echo '<div class="ui-input-text ui-body-inherit ui-corner-all ui-shadow-inset">';} ?>
	<div class="form-group">
	<label class="control-label" for="name"><?php _e( 'Name', 'frozr-norsani' ); ?></label>
	<input <?php if ($rest) {echo 'data-role="none"';} ?> type="text" name="name" value="" placeholder="<?php esc_attr_e( 'Your Name', 'frozr-norsani' ); ?>" class="form-control" minlength="5" required="required">
	</div>
	<?php if ($rest) {echo '</div>';} ?>
	<?php if ($rest) {echo '<div class="ui-input-text ui-body-inherit ui-corner-all ui-shadow-inset">';} ?>
	<div class="form-group">
	<label class="control-label" for="email"><?php _e( 'Email', 'frozr-norsani' ); ?></label>
	<input <?php if ($rest) {echo 'data-role="none"';} ?> type="email" name="email" value="" placeholder="<?php esc_attr_e( 'you@example.com', 'frozr-norsani' ); ?>" class="form-control" required="required">
	</div>
	<?php if ($rest) {echo '</div>';} ?>
	<?php } else { ?>
	<?php if ($rest) {echo '<div class="ui-input-text ui-body-inherit ui-corner-all ui-shadow-inset">';} ?>
	<div class="form-group">
	<label class="control-label" for="subject"><?php _e( 'Subject', 'frozr-norsani' ); ?></label>
	<input <?php if ($rest) {echo 'data-role="none"';} ?> type="text" name="subject" value="" placeholder="<?php esc_attr_e( 'Email Subject', 'frozr-norsani' ); ?>" class="form-control">
	</div>
	<?php if ($rest) {echo '</div>';} ?>
	<?php } ?>
	<div class="form-group">
	<label class="control-label" for="message"><?php _e( 'Message', 'frozr-norsani' ); ?></label>
	<textarea name="message" maxlength="1000" cols="25" rows="6" value="" placeholder="<?php esc_attr_e( 'Type your message...', 'frozr-norsani' ); ?>" class="form-control" required="required"></textarea>
	</div>

	<?php do_action('frozr_after_vendor_email_form', $seller_id, $admin); ?>

	<?php  if (!$rest) { wp_nonce_field( 'frozr_contact_seller' ); } ?>
	<input type="hidden" class="frozr_seller_id_msg" name="seller_id" value="<?php echo intval($seller_id); ?>">
	<input type="hidden" name="action" value="frozr_contact_seller">
	<?php if ($rest) {echo '<div class="ui-btn ui-input-btn ui-corner-all ui-shadow">'. esc_attr( "Send Message", 'frozr-norsani' ); } ?>
	<input <?php if ($rest) {echo 'data-role="none"';} ?> type="submit" name="store_message_send" value="<?php esc_attr_e( 'Send Message', 'frozr-norsani' ); ?>" class="frozr_seller_form_btn"/>
	<?php if ($rest) {echo '</div>';} ?>
	</div>
</form>