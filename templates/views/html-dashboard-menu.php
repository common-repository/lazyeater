<?php
/**
 * Dashboard View: Dashboard Menu
 *
 * @package Norsani/Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div <?php if($show_title){ echo 'data-role="collapsible" data-iconpos="right" data-collapsed-icon="gear" data-expanded-icon="minus"'; } ?> class="usr_dash_menu_content">
	<?php if($show_title){ echo '<h2 class="widget-title frozr_ly_dash_menu_title">'. apply_filters('frozr_your_dashboard_text', sprintf(__('%1$s Dashboard','frozr-norsani'), $title), $title) . '</h2>'; } ?>
	<?php do_action('frozr_before_norsani_dash_menu'); ?>
	
	<div class="frozr_dash_menu_wrapper">
	<ul data-role="listview">
	<?php
	foreach ($urls as $key => $item) {
		if($key == 'dashboard') {$xn = 'dashboard';} elseif ($key == 'items') {$xn = 'shopping_basket';} elseif ($key == 'order') {$xn = 'receipt';} elseif ($key == 'coupon') {$xn = 'card_giftcard';} elseif ($key == 'withdraw') {$xn = 'local_atm';} elseif ($key == 'settings') {$xn = 'perm_identity';} elseif ($key == 'sellers') {$xn = 'people';}
		echo '<li><a href="'.$item['url'].'"><i class="material-icons">'.$xn.'</i>&nbsp;'.$item['title'].'</a></li>';
	} ?>
	</ul>
	</div>
	<?php do_action('frozr_after_norsani_dash_menu'); ?>

</div>