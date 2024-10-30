<?php
/**
 * Vendor message email
 *
 * This template can be overridden by copying it to yourtheme/frozr-norsani/emails/vendor-message.php.
 *
 * @package Norsani/Templates/Emails/HTML
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>
<?php if (isset($sender_name)) { ?>
<p><strong><?php echo __('Sender Name','frozr-norsani'); ?>:</strong>&nbsp;<?php echo $sender_name; ?></p>
<?php } ?>
<?php if (isset($sender_email)) { ?>
<p><strong><?php echo __('Sender Email','frozr-norsani'); ?>:</strong>&nbsp;<?php echo $sender_email; ?></p>
<?php } ?>

<p><?php echo $msg; ?></p>

<?php
do_action( 'woocommerce_email_footer', $email );