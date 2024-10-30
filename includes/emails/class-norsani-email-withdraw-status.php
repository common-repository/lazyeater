<?php
/**
 * Class Norsani_Email_Withdraw_Status file
 *
 * @package Norsani\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Norsani_Email_Withdraw_Status' ) ) :

	/**
	 * Withdrawal request status Email.
	 *
	 * An email sent to the vendor about his withdrawal request status.
	 *
	 * @class       Norsani_Email_Withdraw_Status
	 * @version     1.0.0
	 * @package     Norsani/Classes/Emails
	 * @extends     WC_Email
	 */
	class Norsani_Email_Withdraw_Status extends WC_Email {
		
		/**
		 * Withdraw ID.
		 *
		 * @var int
		 */
		public $withdraw_id;

		/**
		 * Vendor name.
		 *
		 * @var string
		 */
		public $vendor_name;

		/**
		 * Withdraw status.
		 *
		 * @var string
		 */
		public $withdraw_status;

		/**
		 * Amount requested.
		 *
		 * @var string
		 */
		public $amount;

		/**
		 * Payment via.
		 *
		 * @var string
		 */
		public $via;

		/**
		 * Admin reject note.
		 *
		 * @var string
		 */
		public $note;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'norsani_withdrawal_status';
			$this->title          = __( 'Withdrawal Request Status', 'frozr-norsani' );
			$this->description    = __( 'An email sent to the vendor about his withdrawal request status.', 'frozr-norsani' );
			$this->template_html  = 'emails/withdraw-status.php';
			$this->template_plain = 'emails/plain/withdraw-status.php';
			$this->placeholders   = array(
				'{site_title}'   => $this->get_blogname(),
				'{withdraw_id}'   => '',
			);

			// Triggers for this email.
			add_action( 'frozr_withdraw_saved_notification', array( $this, 'trigger' ), 10, 3 );

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Get email subject.
		 *
		 * @since  1.9
		 * @return string
		 */
		public function get_default_subject() {
			return __( '[{site_title}]: Status of your withdrawal request #{withdraw_id}', 'frozr-norsani' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  1.9
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Your withdrawal request: #{withdraw_id}', 'frozr-norsani' );
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param object $vendor.
		 * @param object $withdraw_post.
		 * @param array $posted_data.				Withdrawal request form inputs.
		 */
		public function trigger( $vendor, $withdraw_post, $posted_data ) {
			$this->setup_locale();

			$this->recipient = $vendor->user_email;
			$this->withdraw_id = $withdraw_post->ID;
			$this->placeholders['{withdraw_id}'] = $withdraw_post->ID;
			$this->vendor_name = $vendor->user_login;
			$this->withdraw_status = wc_clean($posted_data['withdraw_status']);
			$this->amount = wc_format_decimal( $posted_data['withdraw_amount']);
			$this->via = wc_clean( $posted_data['withdraw_method'] );
			$this->note = wc_clean( $posted_data['wid_reject_note'] );

			if ( $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}

		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html() {
			return frozr_get_template_html(
				$this->template_html, array(
					'email_heading' 	=> $this->get_heading(),
					'withdraw_id'		=> $this->withdraw_id,
					'vendor_name'		=> $this->vendor_name,
					'withdraw_status'	=> $this->withdraw_status,
					'amount'			=> $this->amount,
					'via'				=> $this->via,
					'note'				=> $this->note,
					'plain_text'    	=> false,
					'email'         	=> $this,
				)
			);
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			return frozr_get_template_html(
				$this->template_plain, array(
					'email_heading' 	=> $this->get_heading(),
					'withdraw_id'		=> $this->withdraw_id,
					'vendor_name'		=> $this->vendor_name,
					'withdraw_status'	=> $this->withdraw_status,
					'amount'			=> $this->amount,
					'via'				=> $this->via,
					'note'				=> $this->note,
					'plain_text'    	=> true,
					'email'         	=> $this,
				)
			);
		}
	}

endif;

return new Norsani_Email_Withdraw_Status();
