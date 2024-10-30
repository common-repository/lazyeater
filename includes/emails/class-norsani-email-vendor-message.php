<?php
/**
 * Class Norsani_Email_Vendor_Msg file
 *
 * @package Norsani\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Norsani_Email_Vendor_Msg' ) ) :

	/**
	 * Vendor Email.
	 *
	 * An email sent to the vendor via his shop page contact form or by website admin from sellers dashboard page.
	 *
	 * @class       Norsani_Email_Vendor_Msg
	 * @version     1.0.0
	 * @package     Norsani/Classes/Emails
	 * @extends     \WC_Email
	 */
	class Norsani_Email_Vendor_Msg extends WC_Email {
		
		/**
		 * Subject.
		 *
		 * @var string
		 */
		public $msg_subject;
		
		/**
		 * Message.
		 *
		 * @var string
		 */
		public $msg;
		
		/**
		 * Message type.
		 *
		 * @var string
		 */
		public $msg_type;
		
		/**
		 * Sender Name.
		 *
		 * @var string
		 */
		public $sender_name;

		/**
		 * Sender Email.
		 *
		 * @var string
		 */
		public $sender_email;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'norsani_vendor_msg';
			$this->title          = __( 'Vendor Email', 'frozr-norsani' );
			$this->description    = __( 'An email sent to the vendor via his shop page contact form or by website admin from sellers dashboard page.', 'frozr-norsani' );
			$this->template_html  = 'emails/vendor-message.php';
			$this->template_plain = 'emails/plain/vendor-message.php';
			$this->placeholders   = array(
				'{site_title}'   => $this->get_blogname(),
			);

			// Triggers for this email.
			add_action( 'frozr_send_vendor_message_notification', array( $this, 'trigger' ), 10, 1 );

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
			if ($subject = $this->msg_subject) {
				return $this->placeholders['{site_title}'] .': '.$subject;
			} else {
				if ($this->msg_type == 'invite') {
					return __( '[{site_title}]: Invitation letter', 'frozr-norsani' );
				} else {
					return __( '[{site_title}]: You recieved a new message', 'frozr-norsani' );
				}
			}
		}

		/**
		 * Get email heading.
		 *
		 * @since  1.9
		 * @return string
		 */
		public function get_default_heading() {
			if ($this->msg_type == 'invite') {
				return __( 'Invitation', 'frozr-norsani' );
			} else {
				return __( 'New message', 'frozr-norsani' );
			}
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param array $msg.
		 */
		public function trigger( $msg ) {
			$this->setup_locale();

			$this->recipient = $msg['to'];
			
			$this->msg_subject = isset($msg['subject']) ? $msg['subject'] : null;
			$this->msg = $msg['msg'];
			$this->msg_type = isset($msg['type']) ? $msg['type'] : null;
			$this->sender_name = isset($msg['name']) ? $msg['name'] : null;
			$this->sender_email = isset($msg['email']) ? $msg['email']: null;

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
					'sender_name'		=> $this->sender_name,
					'sender_email'		=> $this->sender_email,
					'msg'				=> $this->msg,
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
					'sender_name'		=> $this->sender_name,
					'sender_email'		=> $this->sender_email,
					'msg'				=> $this->msg,
					'plain_text'    	=> true,
					'email'         	=> $this,
				)
			);
		}
	}

endif;

return new Norsani_Email_Vendor_Msg();
