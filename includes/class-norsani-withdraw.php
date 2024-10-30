<?php
/**
 * Norsani withdraw methods
 *
 * @package Norsani
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

class Norsani_Withdraw {
	
	/**
	 * The single instance of the class.
	 *
	 * @var Norsani_Withdraw
	 * @since 1.9
	 */
	protected static $_instance = null;
	
	/**
	 * Main Norsani_Withdraw Instance.
	 *
	 * @since 1.9
	 * @static
	 * @return Norsani_Withdraw - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Norsani_Withdraw Constructor.
	 */
	public function __construct() {
		add_action('frozr_after_profits_distributed', array($this, 'frozr_create_auto_withdraws'),10,3);

		do_action( 'norsani_withdraw_loaded' );
	}
	
	/**
	 * Dashbaord withdraw pages nav
	 *
	 * @return void
	 */
	public function frozr_withdraws_page_nav() {
		$permalink = home_url( '/dashboard/withdraw/');
		$status_class = isset( $_GET['withdraw_status'] ) ? sanitize_key($_GET['withdraw_status']) : 'pending';
		$seller_id = !is_super_admin() ? get_current_user_id() : '';
		
		$pending_w = frozr_count_user_object('pending', 'frozr_withdraw', $seller_id);
		$completed_w = frozr_count_user_object('completed', 'frozr_withdraw', $seller_id);
		$trashed_w = frozr_count_user_object('trash', 'frozr_withdraw', $seller_id);
		?>
		<?php if (!is_super_admin()) { ?>
		<div class="frozr_dash_add_new new_withdraw">
		<a class="pull-right" href="#!"><i class="material-icons">add</i> <?php echo __( 'New Withdrawal Request', 'frozr-norsani' ); ?></a>
		</div>
		<?php } ?>
		<ul class="ly_dash_listing_status_filter">
			<li <?php echo $status_class == 'pending' ? "class=\"active pending\"" : ''; ?> data-count="<?php echo $pending_w; ?>">
				<a href="<?php echo add_query_arg( array( 'withdraw_status' => 'pending' ), $permalink ); ?>"><?php echo __( 'Pending', 'frozr-norsani' ).' <span class="frozr_nav_post_cnt pending">('.$pending_w.')</span>'; ?></a>
			</li>
			<li <?php echo $status_class == 'completed' ? "class=\"active\"" : ''; ?> data-count="<?php echo $completed_w; ?>">
				<a href="<?php echo add_query_arg( array( 'withdraw_status' => 'completed' ), $permalink ); ?>"><?php echo __( 'Completed', 'frozr-norsani' ).' <span class="frozr_nav_post_cnt completed">('.$completed_w.')</span>'; ?></a>
			</li>
			<li <?php echo $status_class == 'trash' ? "class=\"active\"" : ''; ?> data-count="<?php echo $trashed_w; ?>">
				<a href="<?php echo add_query_arg( array( 'withdraw_status' => 'trash' ), $permalink ); ?>"><?php echo __( 'Canceled', 'frozr-norsani' ).' <span class="frozr_nav_post_cnt trashed">('.$trashed_w.')</span>'; ?></a>
			</li>
			<?php do_action('frozr_after_withdraw_page_filter'); ?>
		</ul> <!-- .post-statuses-filter -->
		<?php
	}

	/**
	 * Dashboard withdraw page lists body
	 *
	 * @return void
	 */
	public function frozr_withdraws_page_body() { 
		
		global $post, $wp_query; ?>
		<?php if (!is_super_admin()) { ?>
		<div class="withdraw-current-balance">
			<i class="material-icons">account_balance_wallet</i><strong><?php _e('Current Balance:','frozr-norsani'); ?>&nbsp;<span class="amount"><?php echo get_woocommerce_currency_symbol() . floatval(get_user_meta(get_current_user_id(),"_vendor_balance", true)); ?></span></strong>
		</div>
		<?php }
		$withdraw_status = array('completed', 'trash', 'pending');
		$status_class = isset( $_GET['withdraw_status'] ) ? sanitize_key($_GET['withdraw_status']) : 'pending'; ?>
		<div class="frozr_dash_withdraw_list">
		<?php if ( in_array( $status_class, $withdraw_status ) ) {
		$withdraws_table_atts = 'data-role="table" id="withdraws-table" data-mode="reflow"';
		if ( have_posts() ) { ?>
		<?php do_action('frozr_before_withdraws_table'); ?>
		<table <?php echo apply_filters('frozr_withdraws_table_atts', $withdraws_table_atts); ?> class="ui-responsive dash_tables">
			<thead>
				<tr>
				<?php do_action('frozr_before_withdraw_table_header'); ?>
				<th data-priority="2"><?php _e('Request','frozr-norsani'); ?></th>
				<th data-priority="1" class="frozr_dash_withdraw_amount_header"><?php _e('Amount','frozr-norsani'); ?></th>
				<th data-priority="3" class="hide_on_mobile"><?php _e('Via','frozr-norsani'); ?></th>
				<th data-priority="4" class="hide_on_mobile"><?php _e('Date','frozr-norsani'); ?></th>
				<th data-priority="7" class="dash_tables_actions"><?php _e('Actions','frozr-norsani'); ?></th>
				<?php do_action('frozr_after_withdraw_table_header'); ?>
				</tr>
			</thead>
			<tbody>
			<?php
			while (have_posts()) { the_post();
			$withdraw_via = get_post_meta($post->ID, 'wid_req_via', true);
			$withdraw_amt = get_post_meta($post->ID, 'wid_req_amount', true);
			if ($post->post_status == 'pending') {
				$title = __('Edit','frozr-norsani');
				$icon = 'edit';
			} else {
				$title = __('View','frozr-norsani');
				$icon = 'pageview';
			}
			$payoutBatchId = !empty(get_post_meta($post->ID, 'wid_tras_id',true)) ? get_post_meta($post->ID, 'wid_tras_id',true) : false;
			$payoutStatus = !empty(get_post_meta($post->ID, 'wid_tras_sts',true)) ? get_post_meta($post->ID, 'wid_tras_sts',true) : __('pending','frozr-norsani');
			$order_link = !empty(get_post_meta($post->ID, 'wid_ord_id',true)) ? ' - <a href="' . wp_nonce_url( add_query_arg( array( 'order_id' => get_post_meta($post->ID, 'wid_ord_id',true) ), home_url( '/dashboard/orders/') ), 'frozr_view_order' ) . '" title="'. __('More Details','frozr-norsani') .'"><strong>' . sprintf( __( 'Order %s', 'frozr-norsani' ), get_post_meta($post->ID, 'wid_ord_id',true) ) . '</strong></a>' : '';
			?>
			<tr data-id="<?php echo $post->ID; ?>">
				<?php do_action('before_withdraw_table_loop', $withdraw_via, $withdraw_amt); ?>
				<td class="withdraw_summary">
					<div class="frozr_wid_loop_number">#<?php echo $post->ID; ?></div>
					<?php if (is_super_admin()) { ?><div data-id="<?php echo $post->ID; ?>_rest_balance" class="withdraw_vendor_pop"><?php echo 'Vendor ID: ' . $post->post_author . ' Vendor Balance: ' . get_woocommerce_currency_symbol() . floatval(get_user_meta($post->post_author,"_vendor_balance", true)); ?></div><a href="#<?php echo $post->ID; ?>_rest_balance" class="frozr_dash_withdraw_view_seller" title="<?php _e('View Vendor Balance','frozr-norsani'); ?>"><?php echo __('By','frozr-norsani') . '&nbsp;'; the_author(); ?></a><?php } ?>
					<?php if (!$payoutBatchId) { ?>
					<?php echo '<a href="#'.$post->ID.'_wid_pop" class="edit_wid_btn" title="' . $title . '"><i class="material-icons">'.$icon.'</i></a><div data-id="'.$post->ID.'_wid_pop" class="edit_wid">'; $this->frozr_withdraw_form(false, $post->ID); echo '</div>'; ?>
					<?php } ?>
					<?php if($payoutBatchId) { echo '<span class="frozr_payout_sts"><span>'.$payoutStatus.'</span>'.$order_link.'</span>'; } ?>
					<div class="hide_on_desktop">
					<span><?php echo date_i18n( frozr_get_time_date_format('date_time'), strtotime($post->post_date) ); ?></span>
					</div>
				</td>
				<td><span><?php echo wc_price($withdraw_amt); ?></span>
				<div class="hide_on_desktop">
				<div class="frozr_dash_withdraw_payment_det">
				<div data-id="<?php echo $post->ID; ?>_wid_details" class="withdraw_vendor_pop"><?php norsani()->withdraw->frozr_get_seller_withdraw_details($post->post_author, $withdraw_via); ?></div><a href="#<?php echo $post->ID; ?>_wid_details" title="<?php _e('View Details','frozr-norsani'); ?>"><?php echo $withdraw_via; ?></a>
				</div>
				</div>
				</td>
				<td class="hide_on_mobile"><div class="frozr_dash_withdraw_payment_det"><div data-id="<?php echo $post->ID; ?>_wid_details" class="withdraw_vendor_pop"><?php norsani()->withdraw->frozr_get_seller_withdraw_details($post->post_author, $withdraw_via); ?></div><a href="#<?php echo $post->ID; ?>_wid_details" title="<?php _e('View Details','frozr-norsani'); ?>"><?php echo $withdraw_via; ?></a></div></td>
				<td class="hide_on_mobile"><span><?php echo date_i18n( frozr_get_time_date_format('date_time'), strtotime($post->post_date) ); ?></span></td>
				<td class="dash_tables_actions">
				<div>
				<?php if ($post->post_status == 'pending' && is_super_admin() && $withdraw_via == 'paypal') {
				echo '<span class="pay_wid"><a req_id="'. $post->ID .'" href="#" title="'. __( 'Pay', 'frozr-norsani' ) . '">'. __( 'Pay', 'frozr-norsani' ) . '</a></span>';
				}
				if ($post->post_status == 'pending' && $payoutBatchId) {
				echo '<span class="cancel_wid"><a req_id="'. $post->ID .'" href="#" title="'. __( 'Cancel Payout', 'frozr-norsani' ) . '">'. __( 'Cancel Payout', 'frozr-norsani' ) . '</a></span>';
				} else {
				echo '<span class="delete_wid"><a req_id="'. $post->ID .'" href="#" title="'. __( 'Delete', 'frozr-norsani' ) . '">'. __( 'Delete', 'frozr-norsani' ) . '</a></span>';
				}
				do_action('frozr_after_withdraw_actions', $withdraw_via, $withdraw_amt, $post->post_status); ?>
				</div>
				</td>
				<?php do_action('frozr_after_withdraw_table_loop', $withdraw_via, $withdraw_amt, $post->post_status); ?>
			</tr>
			<?php } ?>
			</tbody>
		</table>
		<?php } else { ?>
		<div class="frozr_dash_no_results">
			<i class="material-icons">attach_money</i>
			<h2><?php _e( 'No withdrawal requests found', 'frozr-norsani' ); ?></h2>
		</div>
		<?php }
		if ( $wp_query->max_num_pages > 1 ) {
			frozr_lazy_nav_below(true);
		}

		wp_reset_query();
		do_action('frozr_after_withdraw_table');
		wp_reset_postdata();
		?></div>
		<?php }
		echo $this->frozr_withdraw_vendor_form();
	}

	/**
	 * Get withdraw form
	 *
	 * @return void
	 */
	public function frozr_withdraw_vendor_form() {
		ob_start();
		
		echo '<div class="frozr_dash_new_withdraw frozr_hide">';
		
		/*Get withdraw form*/
		$fle_option = get_option( 'frozr_withdraw_settings' );
		$minimum_withdraw_balance = (! empty( $fle_option['frozr_minimum_withdraw_balance']) ) ? $fle_option['frozr_minimum_withdraw_balance'] : 50;
		$args = array(
			'post_type' => 'frozr_withdraw',
			'post_status' => 'pending',
			'author' => get_current_user_id(),
		);
		$withdraw_query = get_posts( apply_filters( 'frozr_withdraw_listing_query', $args ) );
		
		if ( !empty($withdraw_query) ) {
		
			echo '<div class="frozr_dash_no_results"><i class="material-icons">monetization_on</i><h2>' . __('You already have a pending withdrawal request. Until it\'s been canceled or completed, you can not submit a new request.','frozr-norsani') . '</h2></div>';

		} elseif ( $minimum_withdraw_balance > floatval(get_user_meta(get_current_user_id(),"_vendor_balance", true)) ) {
			
			echo '<div class="frozr_dash_no_results"><i class="material-icons">money_off</i><h2>'. __( 'You don\'t have sufficient balance for a withdrawal request!', 'frozr-norsani' ) .'</h2></div>';

		} else {
		$this->frozr_withdraw_form();
		}
		
		echo '</div>';
		
		return ob_get_clean();
	}

	/**
	 * Get active withdraw methods.
	 * Default is paypal 
	 * 
	 * @return array
	 */
	public function frozr_withdraw_get_active_methods() {
		$fle_option = get_option( 'frozr_withdraw_settings' );
		$methods_opt = (! empty( $fle_option['frozr_withdraw_methods']) ) ? $fle_option['frozr_withdraw_methods'] : 'paypal';
		$methods = !is_array( $methods_opt ) ? array( $methods_opt ) : $methods_opt;

		return apply_filters('frozr_get_withdraw_active_methods',$methods);
	}

	/**
	 * Withdraw request form
	 *
	 * @param bool $new		Are we call the form for a new withdrawal request or existing one?
	 * @param int $id		ID of the withdrawal request post.
	 * @return void
	 */
	public function frozr_withdraw_form($new = true, $id = 0) {
		global $post;
		$payment_methods = $this->frozr_withdraw_get_active_methods();
		$fle_option = get_option( 'frozr_withdraw_settings' );
		
		if ($new != true) {
			$ogp = get_post($id);
			$wid_status = $ogp->post_status;
		} else {
			$wid_status = (! empty( $fle_option['frozr_withdraw_order_status']) ) ? $fle_option['frozr_withdraw_order_status'] : 'pending';
		}
		
		if ($ogp->post_status == 'pending') {
			$title = __('Editing request #','frozr-norsani');
		} else {
			$title = __('Viewing request #','frozr-norsani');
		}

		$wid_image_id = 0;
		if ( has_post_thumbnail( $id ) ) {
			$wid_image_id = get_post_thumbnail_id( $id );
		}
		
		$withdraw_note = get_post_meta($ogp->ID, 'wid_req_del_note', true);	
		$minimum_withdraw_balance = (! empty( $fle_option['frozr_minimum_withdraw_balance']) ) ? $fle_option['frozr_minimum_withdraw_balance'] : 50;
		?>
		<form id="<?php echo $id . '_form'; ?>" class="form-horizontal withdraw" role="form" method="post">
		<?php do_action('frozr_before_withdraw_form'); ?>
		<?php if ( $new == true ) { ?>
		<span class="dash_title_withdraw"><?php echo __('Make a withdrawal request.','frozr-norsani'); ?></span>
		<?php } else { ?>
		<span class="dash_title_withdraw"><?php echo $title . '&nbsp;' . $id; ?></span>
		<?php } if ($ogp->post_status != 'completed') { ?>
		<div class="wid_gen_info <?php if ($wid_status != "pending") { echo "frozr_hide"; } ?>">
		<div class="form-group">
		<label class="control-label" for="withdraw_amount"><?php _e( 'Amount to withdraw', 'frozr-norsani' ); ?>&nbsp;<?php echo get_woocommerce_currency_symbol(); ?></label>
		<input name="withdraw_amount" <?php if ( $new == true ) { echo 'required="required"'; } ?> type="number" <?php if (!is_super_admin()) { ?> min="<?php echo esc_attr( $minimum_withdraw_balance ); ?>" max="<?php echo floatval(get_user_meta(get_current_user_id(),"_vendor_balance", true)); ?>" <?php } ?> class="form-control<?php if ($new == true) { echo ' new_wid_req'; } ?>" id="withdraw_amount" placeholder="<?php echo esc_attr( $minimum_withdraw_balance ); ?>" value="<?php echo get_post_meta($id, 'wid_req_amount', true); ?>"/>
		</div>
		<div class="form-group">
		<label class="control-label" for="withdraw_method"><?php _e( 'Payment method', 'frozr-norsani' ); ?></label>
		<select class="form-control" <?php if ( $new == true ) { echo 'required="required"'; } ?> name="withdraw_method" id="withdraw_method">
			<?php foreach ($payment_methods as $method) { ?>
				<option value="<?php echo $method; ?>" <?php selected( get_post_meta($id, 'wid_req_via', true), $method); ?>><?php echo $method; ?></option>
			<?php } ?>
		</select>
		</div>
		</div>
		<?php } if ( is_super_admin() && $new != true ) { ?>
		<div class="withdraw_invoice <?php if ($wid_status != "completed") { echo "frozr_hide"; } ?>">
			<?php
			$wrap_class = ' frozr_hide';
			$instruction_class = '';
			if ( has_post_thumbnail( $id ) ) {
				$wrap_class = '';
				$instruction_class = ' frozr_hide';
			} ?>
			<div class="image-wrap<?php echo $wrap_class; ?>">
				<input type="hidden" name="wid_image_id" class="frozr-wid-image-id" value="<?php echo $wid_image_id; ?>">
				<div class="withdraw_img" <?php if ( $wid_image_id ) { $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($id), 'full'); echo 'style="background-image: url( '.$large_image_url[0].');"'; } ?>></div>
				<a class="close frozr-remove-wid-image"><i class="material-icons">image</i><?php _e('Change Invoice Image','frozr-norsani'); ?></a>
			</div>
			<div class="instruction-inside<?php echo $instruction_class; ?>">
				<i class="material-icons">cloud_upload</i>
				<a href="#" class="frozr-wid-image-btn btn btn-sm"><?php _e( 'Upload Invoice Image', 'frozr-norsani' ); ?></a>
			</div>
		</div>
		<?php if ($id != 0 && $ogp->post_status != 'completed') { ?>
		<div class="wid_req_sts">
			<span class="control-label"><?php _e('Update status','frozr-norsani'); ?></span>
			<div>
			<label for="<?php echo $id . '_withdraw_pending'; ?>">
			<input type="radio" class="pend_wid_req" name="withdraw_status" id="<?php echo $id . '_withdraw_pending'; ?>" value="pending" <?php checked( $wid_status, 'pending' ); ?> >
			<?php _e('Pending','frozr-norsani'); ?>
			</label>
			<label for="<?php echo $id . '_withdraw_trash'; ?>">
			<input type="radio" class="reject_wid_req" name="withdraw_status" id="<?php echo $id . '_withdraw_trash'; ?>" value="trash" <?php checked( $wid_status, 'trash' ); ?> >
			<?php _e('Canceled','frozr-norsani'); ?></label>
			<label for="<?php echo $id . '_withdraw_completed'; ?>">
			<input type="radio" class="com_wid_req" name="withdraw_status" id="<?php echo $id . '_withdraw_completed'; ?>" value="completed" <?php checked( $wid_status, 'completed' ); ?> >
			<?php _e('Paid','frozr-norsani'); ?></label>
			</div>
		</div>
		<div class="form-group wid_reject_div <?php if ($wid_status != "trash") { echo "frozr_hide"; } ?>">
			<label class="form-group control-label" for="wid_reject_note"><?php _e( 'Cancel note', 'frozr-norsani' ); ?></label>
			<input name="wid_reject_note" type="text" class="form-control" id="wid_reject_note" placeholder="<?php _e('Few words on why you\'ve canceled the withdrawal request','frozr-norsani'); ?>" value="<?php echo isset($withdraw_note) ? $withdraw_note : __('No Data','frozr-norsani'); ?>"/>
		</div>
		<?php } } elseif ($ogp->post_status == 'completed') { ?>
		<div class="withdraw_img" <?php if ( $wid_image_id ) { $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($id), 'full'); echo 'style="background-image: url( '.$large_image_url[0].');"'; } ?>></div>
		<?php } ?>
		<?php do_action('frozr_after_withdraw_form'); ?>
		<?php if ($ogp->post_status == 'pending' || is_super_admin() || $new == true) { ?>
		<input type="hidden" name="withdraw_id" value="<?php echo $id; ?>">
		<input type="submit" id="withdraw_submit" name="withdraw_submit" value="<?php if ($new == true) { _e( 'Submit Request', 'frozr-norsani' ); } else { _e('Update Request','frozr-norsani'); } ?>" >
		<?php } ?>
		</form>
	<?php
	}

	/**
	 * Get total withdrawals
	 *
	 * @param string $seller	Show result for all vendors or a specific vendor (ID).
	 * @return array
	 */
	public function frozr_print_total_withdraws($seller = '') {

		if (is_super_admin()) {
			$user = ($seller == 'all') ? '' : $seller;
		} else {
			$user = get_current_user_id();
		}
		$totals = array();
		$args = array(
			'post_type' => 'frozr_withdraw',
			'post_status' => 'completed',
			'posts_per_page' => -1,
			'author' => $user,
			'fields' => 'ids',
		);
		$withdraws = get_posts( apply_filters( 'frozr_print_total_withdraws_arge', $args ) );
		foreach ($withdraws as $withdraw) {
			$totals[] = floatval(get_post_meta($withdraw, 'wid_req_amount', true));
		}
		
		return array_sum($totals);
	}

	/**
	 * Redirect from withdraw form if admin
	 *
	 */
	public function frozr_wid_redirect_if_admin() {

		if (is_super_admin() && ! isset($_GET['withdraw_status'])) {
			wp_redirect( add_query_arg( array( 'withdraw_status' => 'pending' ), home_url( '/dashboard/withdraw/') ));
		}
	}

	/**
	 * Register default withdrawal methods
	 *
	 * @return array
	 */
	public function frozr_withdraw_register_methods() {
		$methods = array(
			'paypal' => array(
				'title' =>  __( 'PayPal', 'frozr-norsani' ),
				'callback' => array($this,'frozr_withdraw_method_paypal')
			),
			'bank' => array(
				'title' => __( 'Bank Transfer', 'frozr-norsani' ),
				'callback' => array($this,'frozr_withdraw_method_bank')
			),
			'skrill' => array(
				'title' => __( 'Skrill', 'frozr-norsani' ),
				'callback' => array($this,'frozr_withdraw_method_skrill')
			),
		);

		return apply_filters( 'frozr_withdraw_register_methods', $methods );
	}
	
	/**
	 * Get registered withdraw methods suitable for Settings Api
	 * 
	 * @return array
	 */
	public function frozr_withdraw_get_methods() {
		$methods = array();
		$registered = $this->frozr_withdraw_register_methods();

		foreach ($registered as $key => $value) {
			$methods[$key] = $value['title'];
		}

		return $methods;
	}

	/**
	 * Get a single withdraw method based on key
	 * 
	 * @param string $method_key
	 * @return boolean|array
	 */
	public function frozr_withdraw_get_method( $method_key ) {
		$methods = $this->frozr_withdraw_register_methods();

		if ( isset( $methods[$method_key] ) ) {
			return $methods[$method_key];
		}

		return false;
	}

	/**
	 * Callback for PayPal in vendor settings
	 * 
	 * @param array $store_settings
	 * @return void
	 */
	public function frozr_withdraw_method_paypal( $store_settings ) {
		$current_user_id = get_current_user_id();
		$vendor = get_user_by( 'id', $current_user_id );
		$vendor_email = '';
		if ($current_user_id && $vendor) {
		$vendor_email = $vendor->user_email;
		}
		$email = isset( $store_settings['payment']['paypal']['email'] ) ? esc_attr( $store_settings['payment']['paypal']['email'] ) : $vendor_email;
		?>
		<div class="form-group">
		<label class="control-label"><?php echo __('PayPal','frozr-norsani'); ?></label>
		<input value="<?php echo $email; ?>" autocomplete='email' name="settings[paypal][email]" class="form-control" placeholder="example@domain.com" type="email">
		</div>

		<?php do_action('frozr_after_withdraw_paypal_method_input', $store_settings);
	}

	/**
	 * Callback for Skrill in vendor settings
	 * 
	 * @param array $store_settings
	 * @return void
	 */
	public function frozr_withdraw_method_skrill( $store_settings ) {
		$current_user_id = get_current_user_id();
		$vendor = get_user_by( 'id', $current_user_id );
		$vendor_email = '';
		if ($current_user_id && $vendor) {
		$vendor_email = $vendor->user_email;
		}

		$email = isset( $store_settings['payment']['skrill']['email'] ) ? esc_attr( $store_settings['payment']['skrill']['email'] ) : $vendor_email;
		?>
		<div class="form-group">
		<label class="control-label"><?php echo __('Skrill','frozr-norsani'); ?></label>
		<input value="<?php echo $email; ?>" name="settings[skrill][email]" class="form-control" placeholder="example@domain.com" type="email">
		</div>

		<?php do_action('frozr_after_withdraw_skrill_method_input', $store_settings);
	}

	/**
	 * Callback for Bank in vendor settings
	 * 
	 * @param array $store_settings
	 * @return void
	 */
	public function frozr_withdraw_method_bank( $store_settings ) {

		$account_name = isset( $store_settings['payment']['bank']['ac_name'] ) ? esc_attr( $store_settings['payment']['bank']['ac_name'] ) : '';
		$account_number = isset( $store_settings['payment']['bank']['ac_number'] ) ? esc_attr( $store_settings['payment']['bank']['ac_number'] ) : '';
		$bank_name = isset( $store_settings['payment']['bank']['bank_name'] ) ? esc_attr( $store_settings['payment']['bank']['bank_name'] ) : '';
		$bank_addr = isset( $store_settings['payment']['bank']['bank_addr'] ) ? esc_textarea( $store_settings['payment']['bank']['bank_addr'] ) : '';
		$swift_code = isset( $store_settings['payment']['bank']['swift'] ) ? esc_attr( $store_settings['payment']['bank']['swift'] ) : '';
		?>
		<div class="form-group frozr_wid_wire_transfer">
		<span class="control-label"><?php echo __( 'Bank Transfer', 'frozr-norsani' ); ?></span>
		<div class="frozr_inner_form_group">
		<div>
		<label class="control-label"><?php echo __('Account Name','frozr-norsani'); ?></label>
		<input name="settings[bank][ac_name]" value="<?php echo $account_name; ?>" class="form-control" placeholder="<?php esc_attr_e( 'Your bank account name', 'frozr-norsani' ); ?>" type="text">
		</div>
		<div>
		<label class="control-label"><?php echo __('Account Number','frozr-norsani'); ?></label>
		<input name="settings[bank][ac_number]" value="<?php echo $account_number; ?>" class="form-control" placeholder="<?php esc_attr_e( 'Your bank account number', 'frozr-norsani' ); ?>" type="text">
		</div>
		<div>
		<label class="control-label"><?php echo __('Bank Name','frozr-norsani'); ?></label>
		<input name="settings[bank][bank_name]" value="<?php echo $bank_name; ?>" class="form-control" placeholder="<?php _e( 'Name of bank', 'frozr-norsani' ) ?>" type="text">
		</div>
		</div>
		<div class="frozr_inner_form_group">
		<div>
		<label class="control-label"><?php echo __('Bank Address','frozr-norsani'); ?></label>
		<textarea name="settings[bank][bank_addr]" class="form-control" placeholder="<?php esc_attr_e( 'Bank Address', 'frozr-norsani' ) ?>"><?php echo $bank_addr; ?></textarea>
		</div>
		<div>
		<label class="control-label"><?php echo __('Swift code','frozr-norsani'); ?></label>
		<input value="<?php echo $swift_code; ?>" name="settings[bank][swift]" class="form-control" placeholder="<?php esc_attr_e( 'Swift code', 'frozr-norsani' ); ?>" type="text">
		</div>
		</div>
		</div>

		<?php do_action('frozr_after_withdraw_bank_method_input', $store_settings);
	}

	/**
	 * Get withdraw account details based on seller ID and type
	 *
	 * @param int $seller_id
	 * @param string $type
	 * @return string
	 */
	public function frozr_get_seller_withdraw_details( $seller_id, $type = 'paypal' ) {
		$info = frozr_get_store_info( $seller_id );
		$vendor = get_user_by('id',$seller_id);
		$payment_info = '';
		if ($type == 'PayPal Payouts') {
			if (isset($info['payment']['paypal']['email'])) {
				$type = 'paypal';
				$payment_info = __('The payment claiming instructions has been sent by email.','frozr-norsani');
			} else {
				echo __('Sent to','frozr-norsani').' '.$vendor->user_email;
				return;
			}
		}
		$payment_details = $info['payment'][$type];
		foreach ($payment_details as $payment_detail => $val) {
			echo str_replace('_', ' ', $payment_detail) . ': ' . $val.'</br>'.$payment_info;
		}
	}

	/**
	 * Auto create withdrawal request
	 *
	 * @param array $seller_profit
	 * @param object $order
	 * @param int $order_seller
	 * @return string
	 */
	public function frozr_create_auto_withdraws($seller_profit,$order,$order_seller) {

		if (floatval($seller_profit['total_profit']) <= 0) {
		return;
		}

		$option = get_option( 'frozr_withdraw_settings' );
		$pay_instantly = isset($option['frozr_pay_vendors_instantly_paypal']) ? 1 : null;
		$feeoption = get_option( 'frozr_fees_settings' );
		$cod_option = isset($feeoption['frozr_lazy_fees_cod']) ? 1 : 0;

		if(!isset($pay_instantly) || $order->get_payment_method() == 'cod' && $cod_option != 1 ) {
		return;
		}
		$order_id = $order->get_id();
		$vendor_profit = floatval($seller_profit['total_profit']);
		$payout_title = __('Payment for order: #','frozr-norsani').$order_id;
		/*create withdraw post*/
		$withdraw_info = apply_filters('frozr_save_new_withdraw_data',array(
			'post_type' => 'frozr_withdraw',
			'post_title' => $payout_title,
			'post_status' => 'pending',
			'post_author' => $order_seller,
			'comment_status' => 'closed'
		));

		$withdraw_id = wp_insert_post( $withdraw_info );
		update_post_meta( $withdraw_id, 'wid_req_via', 'paypal');
		update_post_meta( $withdraw_id, 'wid_req_amount', $vendor_profit);
		update_post_meta( $withdraw_id, 'wid_ord_id', $order_id);
	}
}