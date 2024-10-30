<?php
/**
 * Admin dashboard sellers class
 *
 * @package Norsani
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

class Norsani_Sellers {
	
	/**
	 * The single instance of the class.
	 *
	 * @var Norsani_Sellers
	 * @since 1.9
	 */
	protected static $_instance = null;
	
	/**
	 * Main Norsani_Sellers Instance.
	 *
	 * @since 1.9
	 * @static
	 * @return Norsani_Sellers - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Norsani_Sellers Constructor.
	 */
	public function __construct() {

		do_action( 'norsani_sellers_loaded' );
	}

	/**
	 * Dashboard sellers page nav
	 *
	 * @return void
	 */
	public function sellers_page_nav() {
		$permalink = home_url( '/dashboard/sellers/');
		$status_class = isset( $_GET['sellers'] ) ? sanitize_key($_GET['sellers']) : 'all';
		$nav_array = array('all', 'yes', 'no');
		$nav_array_out = array();
		foreach ($nav_array as $nx) {
		if ($nx == 'all') {
		$args = array(
			'role'			=> 'seller',
			'orderby'		=> 'registered',
			'order'			=> 'DESC',
			'cunt_total'	=> true,
			'fields'		=> 'ID'
		);
		} else {
		$args = array(
			'role'			=> 'seller',
			'meta_key'		=> 'frozr_enable_selling',
			'meta_value'	=> $nx,
			'orderby'		=> 'registered',
			'order'			=> 'DESC',
			'cunt_total'	=> true,
			'fields'		=> 'ID'
		);
		}
		$user_query = new WP_User_Query( apply_filters('frozr_sellers_page_nav_args',$args) );
		$nav_array_out[] = $user_query->get_total();
		}
		if (frozr_mobile()) { $active_icon='fa-caret-right'; } else {  $active_icon='fa-caret-up'; }

		$this->frozr_vendor_invitation(); ?>
		<?php do_action('frozr_before_sellers_page_nav'); ?>
		<div class="frozr_dash_add_new">
		<a href="#rest_invit_form_wid" class="frozr_dash_popup_link" data-transition="fade" data-rel="popup" data-position-to="window"><i class="material-icons">send</i>&nbsp;<?php _e('Invitation','frozr-norsani'); ?></a>
		</div>
		<ul class="ly_dash_listing_status_filter">
			<?php do_action('frozr_before_sellers_filter_list'); ?>
			<li <?php echo $status_class == 'all' ? "class=\"active $active_icon\"" : ''; ?> >
			<a href="<?php echo $permalink; ?>"><?php printf( __( 'All (%d)', 'frozr-norsani' ), $nav_array_out[0] ); ?></a>
			</li>
			<li <?php echo $status_class == 'yes' ? "class=\"active $active_icon\"" : ''; ?> >
			<a href="<?php echo add_query_arg( array( 'sellers' => 'yes' ), $permalink ); ?>"><?php printf( __( 'Active Vendors (%d)', 'frozr-norsani' ), $nav_array_out[1] ); ?></a>
			</li>
			<li <?php echo $status_class == 'no' ? "class=\"active $active_icon\"" : ''; ?> >
			<a href="<?php echo add_query_arg( array( 'sellers' => 'no' ), $permalink ); ?>"><?php printf( __( 'Inactive Vendors (%d)', 'frozr-norsani' ), $nav_array_out[2] ); ?></a>
			</li>
			<li <?php echo $status_class == 'top-sellers' ? "class=\"active $active_icon\"" : ''; ?> >
			<a href="<?php echo add_query_arg( array( 'sellers' => 'top-sellers' ), $permalink ); ?>"><?php _e( 'Top Sellers', 'frozr-norsani' ); ?></a>
			</li>
			<?php do_action('frozr_after_sellers_filter_list'); ?>
		</ul> <!-- .seller-filter -->
		<?php do_action('frozr_after_sellers_page_nav');
	}

	/**
	 * Vendor invitation form
	 *
	 * @param string $id	Add a unique ID for the form.
	 * @param bool $sellers	Are we sending to current sellers?
	 * @return void
	 */
	public function frozr_vendor_invitation($id="rest_invit_form", $sellers = false) {
		?>
		<div id="<?php echo $id; ?>_wid" data-history="false" data-role="popup">
		<span class="vendor_form_group_label"><i class="material-icons">send</i></i>&nbsp;<?php echo __('Send Invitation','frozr-norsani'); ?></span>
		<div class="frozr_options_group">
		<span class="dash_totals_title_desc"><?php _e('Send an invitation to a vendor to join your website.','frozr-norsani'); frozr_inline_help_db('dash_vendors_invite'); ?></span>
		<form id="<?php echo $id; ?>" method="post">
			<?php do_action('frozr_before_vendor_invitation_form'); ?>
			<?php if (!$sellers) { ?>
			<div class="form-group">
			<label class="form-group control-label" for="rest_invit_email"><?php echo __('Email','frozr-norsani'); ?></label>
			<input class="rest_invit_email" value="<?php echo isset($_POST['rest_invit_email']) ? sanitize_email($_POST['rest_invit_email']) : ''; ?>" placeholder="<?php _e('Email of recipient','frozr-norsani'); ?>" name="rest_invit_email" required type="email">
			</div>
			<?php } ?>
			<div class="form-group">
			<label class="form-group control-label" for="rest_invit_subject"><?php echo __('Subject','frozr-norsani'); ?></label>
			<input class="rest_invit_subject" value="<?php echo isset($_POST['rest_invit_subject']) ? sanitize_text_field($_POST['rest_invit_subject']) : ''; ?>" placeholder="<?php _e('Invitation Subject','frozr-norsani'); ?>" name="rest_invit_subject" required type="text">
			</div>
			<div class="form-group">
			<label class="form-group control-label" for="rest_invit_text"><?php echo __('Message','frozr-norsani'); ?></label>
			<textarea class="rest_invit_text" name="rest_invit_text" required placeholder="<?php _e('Invitation Message','frozr-norsani'); ?>"><?php echo isset($_POST['rest_invit_text']) ? wc_clean($_POST['rest_invit_text']) : ''; ?></textarea>
			</div>
			<?php do_action('frozr_after_vendor_invitation_form'); ?>
			<input class="rest_invit_wid_btn" type="submit" value="<?php _e( 'Send', 'frozr-norsani' ); ?>" >
		</form>
		</div>
		</div>
		<?php
	}

	/**
	 * Dashboard sellers page lists body
	 *
	 * @return void
	 */
	public function frozr_sellers_page_body() {
		global $post;
		$sellers_table_atts = 'data-role="table" id="sellers-table" data-mode="reflow"'; ?>
		<div id="seller_mgs" class="common_pop" data-history="false" data-role="popup"><?php norsani()->vendor->frozr_vendor_email_form(0, true); ?></div>
		<?php do_action('frozr_before_sellers_table'); ?>
		<table <?php echo apply_filters('frozr_sellers_table_atts', $sellers_table_atts); ?> class="ui-responsive dash_tables">
			<thead>
				<tr>
				<?php do_action('frozr_before_sellers_table_header'); ?>
				<th data-priority="1" class="frozr_dash_sellers_list_header_name"><?php _e('Name','frozr-norsani'); ?></th>
				<th data-priority="2" class="hide_on_mobile"><?php _e('Email','frozr-norsani'); ?></th>
				<th data-priority="3"><?php _e('Active Seller','frozr-norsani'); ?></th>
				<th data-priority="5" class="hide_on_mobile"><?php _e('Balance','frozr-norsani'); ?></th>
				<th data-priority="6" class="hide_on_mobile"><?php _e('Phone & Address','frozr-norsani'); ?></th>
				<th data-priority="7" class="hide_on_mobile"><?php _e('Products','frozr-norsani'); ?></th>
				<th data-priority="8" class="hide_on_mobile"><?php _e('Orders','frozr-norsani'); ?></th>
				<th data-priority="9" class="hide_on_mobile"><?php _e('Coupons','frozr-norsani'); ?></th>
				<?php do_action('frozr_after_sellers_table_header'); ?>
				</tr>
			</thead>
			<tbody>
			<?php
			$paged = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1;
			$limit = 12;
			$offset = ( $paged - 1 ) * $limit;
			if (!isset($_GET['sellers']) || $_GET['sellers'] == 'top-sellers') {
			$args = array(
				'role'			=> 'seller',
				'orderby'		=> 'registered',
				'order'			=> 'DESC',
				'fields'		=> 'ID',
				'number'		=> $limit,
				'offset'		=> $offset,
				'paged'			=> $paged
			 );
			} elseif ($_GET['sellers'] != 'top-sellers') {
			$args = array(
				'role'			=> 'seller',
				'meta_key'		=> 'frozr_enable_selling',
				'meta_value'	=> sanitize_key($_GET['sellers']),
				'orderby'		=> 'registered',
				'order'			=> 'DESC',
				'fields'		=> 'ID',
				'number'		=> $limit,
				'offset'		=> $offset,
				'paged'			=> $paged
			 );
			}
			$sellers_query = new WP_User_Query( apply_filters( 'frozr_sellers_listing_query', $args ) );
			
			$sellers_results = $sellers_query->get_results();
			if (!empty($sellers_results)) {
				if (isset($_GET['sellers']) && $_GET['sellers'] == 'top-sellers') {
					$topsellers = array(); 
					foreach ($sellers_results as $seller_result) {
						$com_ords = frozr_count_user_object('wc-completed','shop_order',$seller_result );
						if ($com_ords != 0) {
							$topsellers[$seller_result] = $com_ords;
						}
					}
					arsort($topsellers); ?>
						<div class="top_sellers_notice style_box fa-trophy">
							<p><?php _e('Vendors listed by highest number of completed orders.','frozr-norsani'); ?></p>
						</div>
					<?php
					foreach ($topsellers as $topseller => $sellerv) {
						$this->frozr_top_sellers_list_body($topseller);
					}
				} else {
				foreach ($sellers_results as $seller_result) {
					$this->frozr_top_sellers_list_body($seller_result);
				}
				}
			} else { ?>
			<tr>
			<td colspan="8">
			<div class="style_box alert alert-warning fa-warning-sign">
				<p><?php _e( 'Sorry, no vendors found!', 'frozr-norsani' ); ?></p>
			</div>
			</td>
			</tr>
			<?php } ?>
			</tbody>
		</table>
		<?php do_action('frozr_after_sellers_table');
		$user_count = $sellers_query->total_users;
		$num_of_pages = ceil( $user_count / $limit );

		if ( $num_of_pages > 1 ) {
			$page_num = 999999999;
			echo '<div class="pagination-container clearfix">';
			$page_links = paginate_links( array(
				'current' => $paged,
				'total' => $num_of_pages,
				'base' => str_replace( $page_num, '%#%', esc_url( get_pagenum_link( $page_num ) ) ),
				'type' => 'array',
				'prev_text' => __( '&larr; Previous', 'frozr-norsani' ),
				'next_text' => __( 'Next &rarr;', 'frozr-norsani' ),
			));

			echo "<ul class='frozr_pagination'>\n\t<li>";
			echo join("</li>\n\t<li>", $page_links);
			echo "</li>\n</ul>\n";
			echo '</div>';
		}
	}

	/**
	 * Top Sellers list body
	 *
	 * @param int $seller_id
	 * @return void
	 */
	public function frozr_top_sellers_list_body($seller_id) {
		$user_store = frozr_get_store_info($seller_id);
		$user_is_seller = ('' != (get_user_meta($seller_id, 'frozr_enable_selling', true))) ? get_user_meta($seller_id, 'frozr_enable_selling', true) : 'no';
		$user_balance = ('' != (get_user_meta($seller_id, '_vendor_balance', true))) ? get_user_meta($seller_id, '_vendor_balance', true) : 0;
		$seller_store = (!empty ($user_store['store_name'])) ? ' (' . $user_store['store_name'] . ')' : '';
		$user_product_counts = frozr_count_posts( 'product', $seller_id );
		$user_info = get_userdata($seller_id);
		$user_pro_orders = frozr_count_user_object('wc-processing','shop_order',$seller_id );
		$user_com_orders = frozr_count_user_object('wc-completed','shop_order',$seller_id );
		$user_coupons = frozr_count_user_object('publish','shop_coupon',$seller_id );
		$user_address = (norsani()->vendor->frozr_get_vendor_address($seller_id)) ? norsani()->vendor->frozr_get_vendor_address($seller_id) : null;
		?>
		<tr <?php echo ($user_is_seller == 'no') ? ' class="seller_not_active"' : ''; ?>>
			<?php do_action('frozr_before_seller_table_loop', $seller_id); ?>
			<td>
			<?php do_action('frozr_before_seller_table_name', $seller_id); ?>
			<a href="<?php echo frozr_get_store_url($seller_id); ?>" class="frozr_dash_sellers_name"><strong><?php echo $user_info->user_login . $seller_store; ?></strong></a></br>
			<?php do_action('frozr_after_seller_table_name', $seller_id); ?>
			<div class="hide_on_desktop">
				<a href="#seller_mgs" class="send_seller_msg_pop frozr_dash_popup_link" data-transition="fade" data-rel="popup" data-position-to="window" data-userid="<?php echo $seller_id; ?>"><?php echo $user_info->user_email; ?></a>
				<div class="frozr_dash_sellers_balance"><?php printf(__('Balance: %s','frozr-norsani'), $user_balance); ?></div>
				<div class="frozr_dash_sellers_det"><i class="material-icons">phone</i>&nbsp;<?php echo $user_store['phone']; ?></br><i class="material-icons">person_pin_circle</i>&nbsp;<?php echo $user_address; ?></div>
			</div>
			</td>
			<td class="hide_on_mobile"><a href="#seller_mgs" class="send_seller_msg_pop frozr_dash_popup_link" data-transition="fade" data-rel="popup" data-position-to="window" data-userid="<?php echo $seller_id; ?>"><?php echo $user_info->user_email; ?></a></td>
			<td><span class="frozr_vendor_sts"><?php echo $user_is_seller; ?></span>&nbsp;<div id="seller_edit_pop_<?php echo $seller_id; ?>" class="common_pop" data-history="false" data-role="popup"><?php $this->frozr_seller_edit_form($seller_id); ?></div><a href="#seller_edit_pop_<?php echo $seller_id; ?>" data-rel="popup" data-position-to="window" class="seller_active_change_link frozr_dash_popup_link"><i class="material-icons">settings</i></a></td>
			<td class="frozr_seller_balance hide_on_mobile"><?php echo $user_balance; ?></td>
			<td class="hide_on_mobile"><?php echo $user_store['phone']; echo $user_address ? '</br>'.$user_address : ''; ?></td>
			<td class="hide_on_mobile"><?php echo $user_product_counts->publish; ?></td>
			<td class="hide_on_mobile"><?php echo __('Processing:','frozr-norsani') . ' ' . $user_pro_orders . '</br>' . __('Completed:','frozr-norsani') . ' ' . $user_com_orders; ?></td>
			<td class="hide_on_mobile"><?php echo $user_coupons; ?></td>
			<?php do_action('frozr_after_seller_table_loop', $seller_id); ?>
		</tr>
		<?php
	}

	/**
	 * Seller edit form
	 *
	 * @param int $seller_id
	 * @return void
	 */
	public function frozr_seller_edit_form($seller_id) {
		$user_store = frozr_get_store_info($seller_id);
		$user_info = get_userdata($seller_id);
		$seller_store = (!empty ($user_store['store_name'])) ? ' (' . $user_store['store_name'] . ')' : '';
		?>
		<form id="seller_<?php echo $seller_id; ?>_edit" action="" method="post" class="seller_edit_form clearfix">
			<span class="vendor_form_group_label"><i class="material-icons">account_circle</i>&nbsp;<?php echo $user_info->user_login . $seller_store; ?></span>
			<div class="frozr_options_group">
			<div class="ajax-response"></div>
			<div class="form-group frozr_seller_change_sts">
				<span class="control-label"><?php echo __( 'Activate selling', 'frozr-norsani' ); frozr_inline_help_db('dash_vendors_activate'); ?></span>
				<div>
					<label for="seller_edit_selling" ><?php _e( 'Yes', 'frozr-norsani' ); ?>
						<input type="radio" name="seller_edit_selling" value="yes" <?php checked( frozr_is_seller_enabled($seller_id), true ); ?>>
					</label>
					<label for="seller_edit_selling" ><?php _e( 'No', 'frozr-norsani' ); ?>
						<input type="radio" name="seller_edit_selling" value="no" <?php checked( frozr_is_seller_enabled($seller_id), false ); ?>>
					</label>
				</div>
			</div>

			<?php do_action('frozr_after_seller_edit_form', $seller_id); ?>

			<input type="hidden" class="seller_edit_id" name="seller_edit_id" value="<?php echo $seller_id; ?>">
			<input type="submit" name="seller_edit_form_submit" value="<?php esc_attr_e( 'Save Settings', 'frozr-norsani' ); ?>" class="frozr_seller_form_btn">
			</div>
		</form>
		<?php
	}
}