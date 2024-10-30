<?php
/**
 * Dashboard - Withdraw
 *
 * @package Norsani/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

frozr_redirect_login();
frozr_redirect_if_not_seller();
norsani()->withdraw->frozr_wid_redirect_if_admin();

/*Get Header*/
get_header();

/*Dashboard Action Hook*/
do_action('norsani_dashboard_withdraw_page');

/*calling footer.php*/
get_footer();