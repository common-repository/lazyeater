<?php
/**
 * The Template for displaying a single vendor page.
 *
 * @package Norsani/Templates
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

$user = get_query_var( 'vendor' );
$store_user = get_user_by( 'slug', strtok($user, " ") );

do_action('frozr_before_rest_page_header', $store_user->ID);

/*Get Header*/
get_header();

frozr_redirect_if_disabled_seller($store_user->ID);

do_action('frozr_after_rest_page_header', $store_user->ID);

/*Vendor Action Hook*/
do_action('norsani_single_vendor_page', $store_user);

do_action('frozr_after_single_vendor_page', $store_user->ID);

/* calling footer.php*/
get_footer();