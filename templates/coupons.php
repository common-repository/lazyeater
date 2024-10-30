<?php
/**
 * Dashboard - Coupon
 *
 * @package Norsani/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

frozr_redirect_login();
frozr_redirect_if_not_seller();

/*Get Header*/
get_header();

/*Dashboard Action Hook*/
do_action('norsani_dashboard_coupons_page');

/* calling footer.php*/
get_footer();