<?php
/**
 * Dashboard home
 *
 * @package Norsani/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

frozr_redirect_login();
frozr_redirect_if_not_seller();
$start_date = isset($_GET['startd']) ? $_GET['startd'] : null;
$end_date = isset($_GET['endd']) ? $_GET['endd'] : null;
$userid = isset($_GET['auser']) ? intval($_GET['auser']) : null;

if (isset($_GET['print']) && $_GET['print'] == 'summary' && user_can( get_current_user_id(), 'frozer' ) || isset($_GET['print']) && $_GET['print'] == 'summary' && is_super_admin()) {
	frozr_sales_summary_report(sanitize_key($_GET['rtype']), $start_date, $end_date, $userid);
} else {
do_action('frozr_before_dash_home_header');

/*Get Header*/
get_header();

do_action('frozr_after_dash_home_header');

/*Dashboard Home Action Hook*/
do_action('norsani_dashboard_home_page');

/* calling footer.php*/
get_footer();
}