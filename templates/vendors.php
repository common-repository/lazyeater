<?php
/**
 * Vendors List - All
 *
 * @package Norsani/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

$vendor_type = get_query_var( 'vendors' );

/*Get Header*/
get_header();

/*Vendors Action Hook*/
do_action('norsani_vendors_page', $vendor_type);

/* calling footer.php*/
get_footer();