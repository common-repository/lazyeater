<?php
/**
* Functions for the print page template
*
* @package Norsani
*/

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

function frozr_summary_template($type = 'today', $start = '', $end = '', $user = '') {
	$logo_url = frozr_get_logo_img_url();
	$site_logo_img = $logo_url ? '<img width="auto" height="auto" alt="'. get_bloginfo( 'name' ) .'" style="max-width:2em;" src="'. $logo_url .'"/>' : get_bloginfo( 'name' );

	if (is_super_admin()) {
		if (esc_attr($type) != 'custom') {
			$report_values = frozr_dash_total_sales($type, '', '', $user);
		} else {
			$report_values = frozr_dash_total_sales($type, $start, $end, $user);
		}
		if ($type == 'begging') {
			$actual_balance = $report_values[0] - $report_values[4] - norsani()->withdraw->frozr_print_total_withdraws($user);
		}
	} else {
		if (esc_attr($type) != 'custom') {
			$report_values = frozr_dash_total_sales($type);
		} else {
			$report_values = frozr_dash_total_sales($type, $start, $end);
		}
		if ($type == 'begging') {
			$actual_balance = $report_values[17] - norsani()->withdraw->frozr_print_total_withdraws();
		}		
	}
	$seller = is_super_admin() ? $user : get_current_user_id();
	if ($seller) {
		$store_info = frozr_get_store_info( $seller );
		$get_user = get_userdata( $seller );
		$registered = $get_user->user_registered;
	}
	$total_note = is_super_admin() ? __('Net profit from completed orders after all sellers commissions are paid.','frozr-norsani') : __('Net of any withdrawals made during this period.','frozr-norsani');
	/*Readable summary period*/
	switch ($type) {
        case 'begging':
            $period =  __( 'All Time:', 'frozr-norsani' ) . ' ' . date( "d, M, Y", strtotime($registered) ) . ' - ' . date('d, M, Y', strtotime("today"));
            break;
        case 'today':
            $period =  __( 'Today:', 'frozr-norsani' ) . ' ' . date('d, M, Y', strtotime("today"));
            break;
        case 'week':
            $period =  __( 'This Week:', 'frozr-norsani' ) . ' ' . date('d, M, Y', strtotime("-6 days")) . ' - ' . date('d, M, Y', strtotime("today"));
            break;
        case 'month':
            $period =  __( 'This Month:', 'frozr-norsani' ) . ' ' . date('M, Y', strtotime("this month"));
            break;
        case 'lastmonth':
            $period =  date('d, M, Y', strtotime("first day of last month")) . ' - ' . date('d, M, Y', strtotime("last day of last month"));
            break;
        case 'year':
            $period =  __('This Year:','frozr-norsani') . ' ' . date('Y', strtotime("this year"));
            break;
        case 'custom':
            $period =  date('d, M, Y', strtotime($start)) . ' - ' . date('d, M, Y', strtotime($end));
            break;
	}
	?>
	<div class="frozr_dash_report">

		<table class="summary_report_header">
			<tbody>
			<tr class="summary_logo">
				<td>
					<a href="<?php echo home_url(); ?>/" title="<?php bloginfo( 'name' ); ?>" rel="home"><?php echo $site_logo_img; ?></a>
				</td>
				<td><?php echo apply_filters('frozr_general_summary_report_title',__('General Summary Report','frozr-norsani')) . ' - ' . date('d, M, Y', strtotime("today")); ?>&nbsp;-&nbsp;<span><?php _e('Beta','frozr-norsani'); ?></span></td>
			</tr>
			<tr class="summary_report_details">
				<td>
				<?php if ($seller) { ?>

					<span><?php echo __('Vendor:','frozr-norsani') . ' ' .$store_info['store_name']; ?></span><br/>
					<span><?php echo __('Address:','frozr-norsani'); ?></span>
					<address>
					<?php
						$address = apply_filters( 'frozr_summary_report_address', array(
							'first_name'  => get_user_meta( $seller, 'billing' . '_first_name', true ),
							'last_name'   => get_user_meta( $seller, 'billing' . '_last_name', true ),
							'company'     => get_user_meta( $seller, 'billing' . '_company', true ),
							'address_1'   => get_user_meta( $seller, 'billing' . '_address_1', true ),
							'address_2'   => get_user_meta( $seller, 'billing' . '_address_2', true ),
							'city'        => get_user_meta( $seller, 'billing' . '_city', true ),
							'state'       => get_user_meta( $seller, 'billing' . '_state', true ),
							'postcode'    => get_user_meta( $seller, 'billing' . '_postcode', true ),
							'country'     => get_user_meta( $seller, 'billing' . '_country', true )
						), $seller);

						$formatted_address = WC()->countries->get_formatted_address( $address );

						if ( ! $formatted_address )
							if (is_super_admin()) {
							_e( 'This seller has not set up this type of address yet.', 'frozr-norsani' );
							} else {
							_e( 'You have not set up this type of address yet.', 'frozr-norsani' );
							}
						else
							echo $formatted_address;
					?>
					</address>
				<?php } elseif (is_super_admin()) { ?>
					<span><?php echo __('The Report is based on all sellers activity.','frozr-norsani'); ?></span>
				<?php } ?>
				</td>
				<td><?php echo __('Period:','frozr-norsani') . ' ' . $period; ?></td>
			</tr>
			</tbody>
		</table>
		<div class="summary_report_body">
			<div class="summary_report_container">
				<table>
					<thead>
						<tr>
							<th><?php echo apply_filters('frozr_income_summary_to_earnings_account',__('Income Summary to Earnings Account','frozr-norsani')); ?></th>
							<th><?php _e('Amount','frozr-norsani'); ?></th>
						</tr>
					</thead>

					<tbody>
						<tr>
							<td><?php echo __('Total Orders:','frozr-norsani') . $report_values[1]; ?></td>
							<td><?php echo wc_price($report_values[0]+$report_values[2]+$report_values[18]+$report_values[19]); ?></td>
						</tr>
						<?php if ($type == 'begging') { ?>
						<tr>
							<td><?php echo __('Total "Pending" Orders:','frozr-norsani') . $report_values[9]; ?></td>
							<td>-&nbsp;<?php echo wc_price($report_values[8]); ?></td>
						</tr>
						<tr>
							<td><?php echo __('Total "Processing" Orders:','frozr-norsani') . $report_values[11]; ?></td>
							<td>-&nbsp;<?php echo wc_price($report_values[10]); ?></td>
						</tr>
						<tr>
							<td><?php echo __('Total "on-hold" Orders:','frozr-norsani') . $report_values[15]; ?></td>
							<td>-&nbsp;<?php echo wc_price($report_values[14]); ?></td>
						</tr>
						<tr>
							<td><?php echo __('Total "Cancelled" Orders:','frozr-norsani') . $report_values[13]; ?></td>
							<td>-&nbsp;<?php echo wc_price($report_values[12]); ?></td>
						</tr>
						<tr>
							<td><?php echo __('Total refunded Orders:','frozr-norsani') . $report_values[5]; ?></td>
							<td>-&nbsp;<?php echo wc_price($report_values[4]); ?></td>
						</tr>
						<?php } ?>
						<tr>
							<td><?php echo __('Total Coupons Usage:','frozr-norsani'); ?></td>
							<td>-&nbsp;<?php echo wc_price($report_values[2]); ?></td>
						</tr>
						<?php if (wc_tax_enabled() && is_super_admin() || wc_tax_enabled() && get_option('woocommerce_prices_include_tax') == 'yes') { ?>
						<?php foreach ( $report_values[20] as $code => $tax ) { ?>
						<tr>
							<td><?php echo $code; ?>:</td>
							<td>-&nbsp;<?php echo $tax; ?></td>
						</tr>
						<?php } ?>
						<?php } ?>
						<tr>
							<td><?php if (!is_super_admin()) echo __('Total','frozr-norsani') . ' ' . get_bloginfo( 'name' ) . ' ' .__('Fees','frozr-norsani'); else echo __('Seller Fees','frozr-norsani'); ?></td>
							<td>-&nbsp;<?php echo wc_price($report_values[16]); ?></td>
						</tr>
						<?php do_action('frozr_after_summary_report_body',$type, $start, $end, $user); ?>
					</tbody>
				</table>

				<div class="summary_total">
					<span class="summary_total_amount"><?php echo __('Total:','frozr-norsani') . ' ' . wc_price($report_values[17]); ?></span>
					<span class="summary_total_notice"><?php echo apply_filters('frozr_summary_report_total_notice',$total_note); ?></span>
					<?php if ($type == 'begging') { ?>
					<span class="summary_total_notice"><strong><?php echo __('Total Withdrawals:','frozr-norsani') . ' -' . wc_price(norsani()->withdraw->frozr_print_total_withdraws($user)); ?></strong></span>
					<span class="summary_total_notice"><?php echo __('Actual Balance in Account:','frozr-norsani') . ' <strong>' . wc_price($actual_balance) . '</strong> '; if (wc_tax_enabled() && is_super_admin()) { echo __('- Exclusive of Tax','frozr-norsani'); } ?></span>
					<?php } ?>
					<?php do_action('frozr_after_summary_report_total',$type, $start, $end, $user); ?>
				</div>
			</div>
		</div>

		<div class="summary_footnotes">

			<div class="summary_notice">
			<?php echo apply_filters('frozr_sales_summary_report_one',__('Profits are only gained from completed orders.','frozr-norsani')); ?>
			</div>

		</div>
	</div>
<?php
}
/* Set head title for the summary print template*/
function frozr_summary_report_page_title($title) {
	$title .= '<title>'. get_bloginfo( 'name' ) . ' - ' . __('Summary Sales Report.','frozr-norsani') .'</title>';
	return $title;
}

/* Summary report template*/
function frozr_sales_summary_report($type, $start, $end, $user) {

	frozr_print_template_header('frozr_summary_report_page_title');

	if (is_super_admin()) {
		if (esc_attr($type) != 'custom') {
			frozr_summary_template($type, '', '', $user);
		} else {
			frozr_summary_template($type, $start, $end, $user);
		}
	} elseif (esc_attr($type) != 'custom') {
		frozr_summary_template($type);
	} else {
		frozr_summary_template($type, $start, $end);
	}

	frozr_print_template_footer();

}

/* print pages header*/
function frozr_print_template_header($page_title) {

/*add cache control - it's off to use change $use to true.*/
$use = false;
$offset = 60 * 60 * 24 * 1;
$ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
if ($use == true) {
	Header("Cache-Control: must-revalidate");
	Header($ExpStr);
}

$content = '<!DOCTYPE html>' . "\n";
$content .= '<html data-ajax="false"';
echo apply_filters( 'frozr_create_doctype', $content );
echo " ";
language_attributes();
echo ">\n";

add_filter('frozr_head_profile',$page_title);

/* Opens the head tag*/
$head_content = '<head>' . "\n";
echo apply_filters('frozr_head_profile', $head_content );

/* Create the meta content type*/
$meta_content = "<meta http-equiv=\"Content-Type\" content=\"";
$meta_content .= get_bloginfo('html_type'); 
$meta_content .= "; charset=";
$meta_content .= get_bloginfo('charset');
$meta_content .= "\" />";
$meta_content .= "\n";
echo apply_filters('frozr_create_contenttype', $meta_content);

/* mobile support*/
$port_content = "<meta name=\"viewport\" content=\"";
$port_content .= "width=device-width, ";
$port_content .= "initial-scale=1, ";
$port_content .= "maximum-scale=1, ";
$port_content .= "user-scalable=no";
$port_content .= "\"/>";
$port_content .= "\n";
echo apply_filters('frozr_viewport', $port_content);

/* Create the tag <meta name="robots"*/
if ( !class_exists('All_in_One_SEO_Pack') && !class_exists('HeadSpace_Plugin') && !class_exists('Platinum_SEO_Pack') && !class_exists('wpSEO') && !defined('WPSEO_VERSION') ) {
$robot_content = '<meta name="robots" content="noindex,nofollow" />';
$robot_content .= "\n";
if ( get_option('blog_public') ) {
echo apply_filters('frozr_create_robots', $robot_content);
}
}

/*Show Theme Favicon*/
$site_favicon = ('' != get_theme_mod('site_favicon')) ? get_theme_mod('site_favicon') : '';
$fav_content = '<link type="image/x-icon" rel="shortcut icon" href="';
$fav_content .= $site_favicon;
$fav_content .= '" />';
$fav_content .= "\n";
echo apply_filters('frozr_favicon', $fav_content);

?>
<link rel="stylesheet" href="<?php echo plugins_url( 'assets/css/print.css', NORSANI_FILE ); ?>" type="text/css" media="all">
</head>
<?php
echo '<body ';
body_class();
echo ' data-ajax="false">' . "\n\n";
}

/* print pages footer*/
function frozr_print_template_footer() {

?>
</body>
</html>
<?php
}

/* print order template*/
function frozr_print_order_template($order) {
	frozr_print_template_header('frozr_order_page_title');
	frozr_print_order($order);
	frozr_print_template_footer();
}
/* print order*/
function frozr_print_order($order) {
	$get_post = get_post($order->get_id());
	$logo_url = frozr_get_logo_img_url();
	$site_logo_img = $logo_url ? '<img width="auto" height="auto" alt="'. get_bloginfo( 'name' ) .'" style="max-width:2em;" src="'. $logo_url .'"/>' : get_bloginfo( 'name' );
    $info = frozr_get_store_info(frozr_get_order_author($order->get_id()));

?>
<div class="frozr_dash_print_order">
	<div class="order_print_header">
	<a href="<?php echo home_url(); ?>/" title="<?php bloginfo( 'name' ); ?>" class="order_print_logo" rel="home"><?php echo $site_logo_img. ' - ' . $info['store_name']; ?></a>
	<?php echo '<a data-ajax="false" href="' . wp_nonce_url( add_query_arg( array( 'order_id' => $order->get_id() ), home_url( '/dashboard/orders/') ), 'frozr_view_order' ) . '"><strong>' . sprintf( __( 'Order %s', 'frozr-norsani' ), esc_attr( $order->get_order_number() ) ) . '</strong></a>'; ?>
	</div>
	<?php norsani()->order->frozr_order_items_table($order); ?>
	<?php norsani()->order->frozr_order_general_details($order); ?>
	<?php norsani()->order->frozr_order_customer_details($order); ?>
</div>
<?php
}

/*Set head title for the summary print template*/
function frozr_order_page_title($title) {
	$title .= '<title>'. get_bloginfo( 'name' ) . ' - ' . __('Print','frozr-norsani') .'</title>';
	return $title;
}