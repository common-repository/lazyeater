<?php
/**
 * Class Norsani_Email_Vendor_Order_Refund file
 *
 * @package Norsani\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Norsani_Email_Vendor_Order_Refund' ) ) :

	/**
	 * Vendor order refund email.
	 *
	 * An email sent to the vendor informing him on a refund for one of his orders.
	 *
	 * @class       Norsani_Email_Vendor_Order_Refund
	 * @version     1.0.0
	 * @package     Norsani/Classes/Emails
	 * @extends     WC_Email
	 */
	class Norsani_Email_Vendor_Order_Refund extends WC_Email {
		
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
		 * Order amount.
		 *
		 * @var string
		 */
		public $order_amount;
		
		/**
		 * New status of the order.
		 *
		 * @var string
		 */
		public $new_sts;

		/**
		 * New vendor balance.
		 *
		 * @var string
		 */
		public $current_balance;
	
		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'norsani_vendor_order_refund';
			$this->title          = __( "Order refund", "frozr-norsani" );
			$this->description    = __( 'An email sent to the vendor informing him on a refund for one of his orders.', 'frozr-norsani' );
			$this->template_html  = 'emails/vendor-order-refund.php';
			$this->template_plain = 'emails/plain/vendor-order-refund.php';
			$this->placeholders   = array(
				'{site_title}'   => $this->get_blogname(),
				'{order_id}'   => '',
			);

			// Triggers for this email.
			add_action( 'frozr_send_vendor_order_refund_email_notification', array( $this, 'trigger' ), 10, 2 );

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
			return __( '[{site_title}]: Order Refunded', 'frozr-norsani' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  1.9
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Order #{order_id} Refunded', 'frozr-norsani' );
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param object $order.
		 * @param string $new_status.
		 */
		public function trigger( $order, $new_status ) {
			$this->setup_locale();

			$order_id = $order->get_id();
			$order_seller = frozr_get_order_author($order_id);
			$seller_info = frozr_get_store_info($order_seller);
			$seller_user = get_userdata( $order_seller );
			$store_name = !empty($seller_info['store_name']) ? $seller_info['store_name'] : $seller_user->display_name;
			
			$this->recipient = sanitize_email($seller_user->user_email);
			
			$this->placeholders['{order_id}'] = $order_id;
			
			$this->order_id = $order_id;
			$this->order_date = $order->get_date_completed();
			$this->vendor_name = sanitize_text_field($store_name);
			$this->order_amount = frozr_get_seller_total_order($order);
			$this->new_sts = esc_attr($new_status);
			$this->current_balance = wc_price(get_user_meta($order_seller,"_vendor_balance", true));

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
					'order_amount'		=> $this->order_amount,
					'new_sts'			=> $this->new_sts,
					'current_balance'	=> $this->current_balance,
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
					'order_amount'		=> $this->order_amount,
					'new_sts'			=> $this->new_sts,
					'current_balance'	=> $this->current_balance,
					'plain_text'    	=> true,
					'email'         	=> $this,
				)
			);
		}
	}

endif;

return new Norsani_Email_Vendor_Order_Refund();
