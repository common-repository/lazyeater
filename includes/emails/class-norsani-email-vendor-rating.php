<?php
/**
 * Class Norsani_Email_Vendor_Rating file
 *
 * @package Norsani\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Norsani_Email_Vendor_Rating' ) ) :

	/**
	 * Vendor rating request email.
	 *
	 * An email sent to the customer asking him to rating a vendor.
	 *
	 * @class       Norsani_Email_Vendor_Rating
	 * @version     1.0.0
	 * @package     Norsani/Classes/Emails
	 * @extends     WC_Email
	 */
	class Norsani_Email_Vendor_Rating extends WC_Email {
		
		/**
		 * Order ID.
		 *
		 * @var int
		 */
		public $order_id;
		
		/**
		 * Order date.
		 *
		 * @var string
		 */
		public $order_date;
		
		/**
		 * Vendor shop name.
		 *
		 * @var string
		 */
		public $vendor_name;
		
		/**
		 * Customer name.
		 *
		 * @var string
		 */
		public $customer_name;
		
		/**
		 * Review page link.
		 *
		 * @var string
		 */
		public $revlink;
	
		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'norsani_vendor_rating';
			$this->title          = __( 'Vendor rating request', 'frozr-norsani' );
			$this->description    = __( 'An email sent to the customer asking him to rating a vendor.', 'frozr-norsani' );
			$this->template_html  = 'emails/vendor-rating.php';
			$this->template_plain = 'emails/plain/vendor-rating.php';
			$this->placeholders   = array(
				'{site_title}'   => $this->get_blogname(),
				'{vendor_name}'   => '',
			);

			// Triggers for this email.
			add_action( 'frozr_send_customer_rating_request_email_notification', array( $this, 'trigger' ), 10, 1 );

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
			return __( '[{site_title}]: Make your rating on {vendor_name}', 'frozr-norsani' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  1.9
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Rate our service', 'frozr-norsani' );
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param object $order.
		 */
		public function trigger( $order ) {
			$this->setup_locale();
			$order_id = $order->get_id();
			$order_seller = frozr_get_order_author($order_id);
			$seller_info = frozr_get_store_info($order_seller);
			$seller_user = get_userdata( $order_seller );
			$store_name = !empty($seller_info['store_name']) ? $seller_info['store_name'] : $seller_user->display_name;
			
			$this->recipient = sanitize_email($order->get_billing_email());
			
			$this->order_id = $order_id;
			$this->order_date = $order->get_date_completed();
			$this->vendor_name = sanitize_text_field($store_name);
			$this->placeholders['{vendor_name}'] = sanitize_text_field($store_name);
			$this->customer_name = sanitize_text_field($order->get_billing_first_name());
			$this->revlink = frozr_get_store_url($order_seller) . '?make_review=' . $order_id;

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
					'order_id'			=> $this->order_id,
					'order_date'		=> $this->order_date,
					'vendor_name'		=> $this->vendor_name,
					'customer_name'		=> $this->customer_name,
					'revlink'			=> $this->revlink,
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
					'order_id'			=> $this->order_id,
					'order_date'		=> $this->order_date,
					'vendor_name'		=> $this->vendor_name,
					'customer_name'		=> $this->customer_name,
					'revlink'			=> $this->revlink,
					'plain_text'    	=> true,
					'email'         	=> $this,
				)
			);
		}
	}

endif;

return new Norsani_Email_Vendor_Rating();
