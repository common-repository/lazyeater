<?php
/**
 * Dashboard - Sellers
 *
 * @package Norsani/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

frozr_redirect_login();
frozr_redirect_if_not_admin();

do_action('frozr_before_dash_sellers_header');

/*Get Header*/
get_header();

do_action('frozr_after_dash_sellers_header');

do_action('norsani_dashboard_sellers_page');

/* action hook for placing content below #container*/
do_action('frozr_belowcontainer');

/* calling sidebar*/
get_sidebar();

/* calling footer.php*/
get_footer();