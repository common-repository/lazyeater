<?php
/**
 * Class Norsani_Email_Vendor_Status file
 *
 * @package Norsani\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Norsani_Email_Vendor_Status' ) ) :

	/**
	 * Vendor Status Update Email.
	 *
	 * An email sent to the vendor to inform him about his selling status.
	 *
	 * @class       Norsani_Email_Vendor_Status
	 * @version     1.0.0
	 * @package     Norsani/Classes/Emails
	 * @extends     WC_Email
	 */
	class Norsani_Email_Vendor_Status extends WC_Email {
		
		/**
		 * Vendor ID.
		 *
		 * @var int
		 */
		public $vendor_id;
		
		/**
		 * Vendor email.
		 *
		 * @var string
		 */
		public $vendor_email;
		
		/**
		 * Vendor first name.
		 *
		 * @var string
		 */
		public $vendor_first_name;
		
		/**
		 * Vendor first name.
		 *
		 * @var string
		 */
		public $vendor_last_name;

		/**
		 * Vendor Shop Name.
		 *
		 * @var string
		 */
		public $shopname;

		/**
		 * Vendor Shop URL.
		 *
		 * @var string
		 */
		public $shopurl;

		/**
		 * Vendor Shop phone.
		 *
		 * @var string
		 */
		public $shopphone;
		
		/**
		 * Message type.
		 *
		 * @var string
		 */
		public $msg_type;
	
		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'norsani_vendor_status';
			$this->title          = __( 'Vendor Status Update', 'frozr-norsani' );
			$this->description    = __( 'An email sent to the vendor to inform him about his selling status.', 'frozr-norsani' );
			$this->template_html  = 'emails/vendor-status.php';
			$this->template_plain = 'emails/plain/vendor-status.php';
			$this->placeholders   = array(
				'{site_title}'   => $this->get_blogname(),
			);

			// Triggers for this email.
			add_action( 'frozr_send_vendor_status_message_notification', array( $this, 'trigger' ), 10, 1 );
			add_action( 'frozr_send_vendor_registration_message_notification', array( $this, 'trigger' ), 10, 1 );
			add_action( 'frozr_send_admin_new_vendor_message_notification', array( $this, 'trigger' ), 10, 1 );
			add_action( 'frozr_send_vendor_auto_active_message_notification', array( $this, 'trigger' ), 10, 1 );
			add_action( 'frozr_send_admin_auto_new_vendor_message_notification', array( $this, 'trigger' ), 10, 1 );

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
			$type = $this->msg_type;
			if ($type == 'to_new_seller' || $type == 'to_new_seller_auto') {
				return __( '[{site_title}]: Thank you for registering', 'frozr-norsani' );
			} elseif ($type == 'privileges') {
				return __( '[{site_title}]: Status Update', 'frozr-norsani' );
			} else {
				return __( '[{site_title}]: New Vendor Registration', 'frozr-norsani' );
			}
		}

		/**
		 * Get email heading.
		 *
		 * @since  1.9
		 * @return string
		 */
		public function get_default_heading() {
			$type = $this->msg_type;
			if ($type == 'to_new_seller' || $type == 'to_new_seller_auto') {
				return __( 'Welcome to {site_title}', 'frozr-norsani' );
			} elseif ($type == 'privileges') {
				return __( 'Status Update', 'frozr-norsani' );
			} else {
				return __( 'New Vendor', 'frozr-norsani' );
			}
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param array $msg.
		 */
		public function trigger( $msg ) {
			$this->setup_locale();

			$this->recipient = isset($msg['to']) ? $msg['to'] : get_option( 'admin_email' );
			
			$this->vendor_id = isset($msg['id']) ? $msg['id'] : null;
			$this->vendor_email = isset($msg['uemail']) ? $msg['uemail'] : null;
			$this->vendor_first_name = isset($msg['fname']) ? $msg['fname'] : null;
			$this->vendor_last_name = isset($msg['lname']) ? $msg['lname'] : null;
			$this->shopname = $msg['shopname'];
			$this->shopurl = isset($msg['shopurl']) ? $msg['shopurl'] : null;
			$this->shopphone = isset($msg['shopphone']) ? $msg['shopphone'] : null;
			$this->msg_type = isset($msg['type']) ? $msg['type'] : null;

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
					'vendor_id'			=> $this->vendor_id,
					'vendor_email'		=> $this->vendor_email,
					'vendor_first_name'	=> $this->vendor_first_name,
					'vendor_last_name'	=> $this->vendor_last_name,
					'shopname'			=> $this->shopname,
					'shopurl'			=> $this->shopurl,
					'shopphone'			=> $this->shopphone,
					'msg_type'			=> $this->msg_type,
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
					'vendor_id'			=> $this->vendor_id,
					'vendor_email'		=> $this->vendor_email,
					'vendor_first_name'	=> $this->vendor_first_name,
					'vendor_last_name'	=> $this->vendor_last_name,
					'shopname'			=> $this->shopname,
					'shopurl'			=> $this->shopurl,
					'shopphone'			=> $this->shopphone,
					'msg_type'			=> $this->msg_type,
					'plain_text'    	=> true,
					'email'         	=> $this,
				)
			);
		}
	}

endif;

return new Norsani_Email_Vendor_Status();
