<?php
/**
 * All Related Norsani Product Management Functions
 *
 * @package Norsani
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

class Norsani_Item {
	
	/**
	 * The single instance of the class.
	 *
	 * @var Norsani_Item
	 * @since 1.9
	 */
	protected static $_instance = null;
	
	/**
	 * Main Norsani_Item Instance.
	 *
	 * @since 1.9
	 * @static
	 * @return Norsani_Item - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Norsani_Item Constructor.
	 */
	public function __construct() {
		add_action('frozr_before_custom_description_tab', array($this, 'frozr_custom_product_tab_desc') );
		add_action('frozr_after_dashboard_products_actions', array($this, 'frozr_dash_offline_product_btn'), 10,1);
		add_action('frozr_simple_add_to_cart_form',array($this, 'frozr_order_type_options'),10,2);
		add_action('woocommerce_before_variations_form',array($this, 'frozr_order_type_options'),10,2);
		add_action('woocommerce_before_add_to_cart_quantity', array($this, 'frozr_add_norsani_product_atc_fields'), 10, 2);
		add_action( 'woocommerce_add_to_cart', array($this, 'frozr_add_item_discount'), 10, 6 ); 
		add_action( 'woocommerce_cart_loaded_from_session', array($this, 'frozr_add_item_sdiscount_session'), 10, 1 ); 
		add_action( 'woocommerce_checkout_create_order_line_item', array($this, 'frozr_save_norsani_atc_fields_checkout'), 10, 4 );
		
		add_filter( 'woocommerce_product_tabs', array($this, 'frozr_product_tabs') );
		add_filter( 'woocommerce_add_cart_item_data', array($this, 'frozr_save_norsani_product_atc_fields'), 10, 3 );
		add_filter( 'woocommerce_get_cart_item_from_session', array($this, 'frozr_support_norsani_atc_fields_session'), 10, 3 );
		add_filter( 'woocommerce_get_item_data', array($this, 'frozr_support_norsani_atc_fields_checkout'), 10, 2 );

		do_action( 'norsani_item_loaded' );
	}

	/**
	 * Output products page filter nav.
	 *
	 * @return void.
	 */
	public function frozr_product_listing_status_filter() {
		$permalink = home_url('/dashboard/items/');
		$status_class = isset( $_GET['post_status'] ) ? $_GET['post_status'] : 'all';
		$post_counts = frozr_count_posts( 'product', get_current_user_id() );
		$post_total = $post_counts->publish + $post_counts->pending + $post_counts->draft + $post_counts->offline;
		
		ob_start();
		frozr_get_template('views/html-dashboard-items-nav.php', array('post_total' => $post_total, 'post_counts' => $post_counts, 'status_class' => $status_class, 'permalink' => $permalink));
		echo apply_filters('frozr_dashboard_home_page_items_nav_html',ob_get_clean(), $post_total, $post_counts, $status_class, $permalink);
	}
	
	/**
	 * Get user friendly post status based on post
	 *
	 * @param string $status
	 * @return string
	 */
	public function frozr_get_post_status( $status ) {
		switch ($status) {
		case 'publish':
			return __( 'Online', 'frozr-norsani' );
			break;

		case 'draft':
			return __( 'Draft', 'frozr-norsani' );
			break;

		case 'pending':
			return __( 'Pending', 'frozr-norsani' );
			break;

		case 'offline':
			return __( 'Offline', 'frozr-norsani' );
			break;

		default:
			return '';
			break;
		}
	}
	
	/**
	 * Add offline product button
	 *
	 * @param object $post
	 * @return void
	 */
	public function frozr_dash_offline_product_btn($post) {
		$item_current_sts = $post->post_status;
		$btn_txt = $item_current_sts == 'publish' ? __('Make offline','frozr-norsani') : __('Make online','frozr-norsani');
		$sts = $item_current_sts == 'publish' ? 'offline' : 'publish';
		if ($item_current_sts == 'publish' || $item_current_sts == 'offline' ) {
		echo '<span class="frozr_dash_product_change_status"><a href="#!" data-change="'.$sts.'" data-id="'.$post->ID.'" title="'.$btn_txt.'">'.$btn_txt.'</a></span>';
		}
	}
	
	/**
	 * Get edit item url
	 *
	 * @param int $product_id
	 * @return URL
	 */
	public function frozr_edit_item_url( $product_id ) {
		return add_query_arg( array( 'product_id' => $product_id, 'action' => 'edit' ), home_url('/dashboard/items/') );
	}
	
	/**
	 * Item edit/add form body
	 *
	 * @param int $post_id
	 * @param bool|false $new	are we calling a new or existing product form?
	 * @return void
	 */
	public function frozr_edit_add_item_body($post_id = 0, $new = false) {
		if ($new == false) {
			$seller_id = get_current_user_id();
			$post = get_post( $post_id );

			/* bail out if not author*/
			if ( $post->post_author != $seller_id ) {
				wp_die( __( 'Access Denied', 'frozr-norsani' ) );
			}
		} else {
			$post_id = 0;
		}

		$product_title = isset( $post->post_title ) && $new == false ? $post->post_title : '';
		
		ob_start();
		frozr_get_template('views/html-dashboard-items-form.php', array('post_id' => $post_id, 'product_title' => $product_title, 'new' => $new));
		echo apply_filters('frozr_dashboard_home_page_items_form_html',ob_get_clean(), $post_id, $product_title, $new);
	}
	
	/**
	 * Item edit/add form body data
	 *
	 * @param int $post_id
	 * @param bool|false $new	are we calling a new or existing product form?
	 * @return void
	 */
	public function frozr_output_item_data( $post_id, $new = false) {
		global $post;
		
		$post = get_post($post_id);
		
		$product_obj = $new == false ? wc_get_product($post->ID) : '';

		$product_title = isset( $post->post_title ) && $new == false ? $post->post_title : '';
		$product_content = isset( $post->post_content ) && $new == false ? $post->post_content : '';
		$product_excerpt = isset( $post->post_excerpt )&& $new == false ? $post->post_excerpt : '';

		/*item cats*/
		$discts = get_terms( 'product_cat', 'fields=names&hide_empty=0' );
		$itemcats = wp_get_post_terms( $post_id, 'product_cat', array("fields" => "names") );
		$itemcats_slug = array();
		if (is_array($itemcats)) {
			foreach ( $itemcats as $itemcat ) {
				$itemcats_slug[] = $itemcat;
			}
			$item_cats = join( '- ', $itemcats_slug );
		} elseif ( ! empty( $discts ) && ! is_wp_error( $discts )) {
			$item_cats = $itemcats;
		}
		
		/*get all item cats*/
		$dc_slug = array();
		if ( ! empty( $discts ) && ! is_wp_error( $discts ) ){
			 foreach ( $discts as $term ) {
			   $dc_slug[] = $term;
		}
		$product_cats = '"'.join( '"," ', $dc_slug ).'"';
		}
		$ingres = get_terms( 'ingredient', 'fields=names&hide_empty=0' );
		
		/*item ingredients*/
		$ingredients = wp_get_post_terms( $post_id, 'ingredient', array("fields" => "names") );
		$ingredients_slug = array();
		if (is_array($ingredients)) {
			foreach ( $ingredients as $ingredient ) {
				$ingredients_slug[] = $ingredient;
			}
			$ingreds = join( '- ', $ingredients_slug );
		} elseif ( ! empty( $ingres ) && ! is_wp_error( $ingres )) {
			$ingreds = $ingredients;
		}
		/*get all ingredients*/
		$ings_slug = array();
		if ( ! empty( $ingres ) && ! is_wp_error( $ingres ) ){
			 foreach ( $ingres as $term ) {
			   $ings_slug[] = $term;
		}
		$ings = '"'.join( '"," ', $ings_slug ).'"';
		}

		/*get products for linking item*/
		$upsel = ( null != (get_post_meta( $post_id, '_upsell_ids', true )) ) ? get_post_meta( $post_id, '_upsell_ids', true ) : array();
		$crsel = ( null != (get_post_meta( $post_id, '_crosssell_ids', true )) ) ? get_post_meta( $post_id, '_crosssell_ids', true ) : array();
		$vegp = ( null != (get_post_meta( $post_id, '_item_veg', true )) ) ? get_post_meta( $post_id, '_item_veg', true ) : 'veg';
		$spicp = ( null != (get_post_meta( $post_id, '_item_spicy', true )) ) ? get_post_meta( $post_id, '_item_spicy', true ) : '';
		$fatp = ( null != (get_post_meta( $post_id, '_item_fat', true )) ) ? get_post_meta( $post_id, '_item_fat', true ) : '';
		$fatrp = ( null != (get_post_meta( $post_id, '_item_fat_rate', true )) ) ? get_post_meta( $post_id, '_item_fat_rate', true ) : '';
		$argsupco = array(
			'posts_per_page'	=> -1,
			'exclude'			=> $post_id,
			'post_type'			=> 'product',
			'author'			=> get_current_user_id(),
			'post_status'		=> 'publish',
		);
		$linking_posts = get_posts( $argsupco );

		/* Item options*/
		$pro_attrs = ( $new == false && $product_obj != '' && $product_obj->is_type( 'variable' )) ? $product_obj->get_variation_attributes(): array();
		$item_has_options = ( $new == false && $product_obj != '' && $product_obj->is_type( 'variable' ) && !empty($pro_attrs) ) ? 'yes' : get_post_meta( $post_id, '_item_has_options', true );	
		
		/* Item promotions*/
		$item_promotions = ( null != (get_post_meta( $post_id, 'item_promotions', true )) ) ? get_post_meta( $post_id, 'item_promotions', true ) : array();
		
		/*item meal type*/
		$product_meal_type = ( null != (get_post_meta( $post_id, 'product_meal_type', true )) ) ? get_post_meta( $post_id, 'product_meal_type', true ) : array();
		
		/*item maximum orders*/
		$max_orders = ( null != (get_post_meta( $post_id, 'max_ords_day', true )) ) ? get_post_meta( $post_id, 'max_ords_day', true ) : 0;
		
		/*pretime*/
		$pretime = ( null != (get_post_meta( $post_id, 'pretime', true )) ) ? get_post_meta( $post_id, 'pretime', true ) : 0;

		$wrap_class = ' frozr_hide';
		$instruction_class = '';
		$feat_image_id = 0;
		if ( $new == false && has_post_thumbnail( $post_id ) ) {
			$wrap_class = '';
			$instruction_class = ' frozr_hide';
			$feat_image_id = get_post_thumbnail_id( $post_id );
		}
		
		/* Price*/
		$in_disabled = ( $item_has_options ) ? array('disabled' => "disabled") : array();
		$on_disabled = ( $item_has_options ) ? 'disabled="disabled"' : '';
		$regular_price = ( $item_has_options ) ? '' : get_post_meta( $post_id, '_regular_price', true );
		$sale_price = ( $item_has_options ) ? '' : get_post_meta( $post_id, '_sale_price', true );
		
		/* Special Price date range*/
		$sale_price_dates_from = ( $date = get_post_meta( $post_id, '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
		$sale_price_dates_to   = ( $date = get_post_meta( $post_id, '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
		
		$_sale_price_dates_from = ( $item_has_options ) ? '' : $sale_price_dates_from;
		$_sale_price_dates_to   = ( $item_has_options ) ? '' : $sale_price_dates_to;
		
		$get_options = apply_filters('frozr_item_promotion_get_options',array(
			"discount" => __('Discount','frozr-norsani'),
			"free_item" => __('Free Product','frozr-norsani'),
		));
		
		$args = apply_filters('frozr_product_form_html_args',array(
			'post' => $post,
			'post_id' => $post_id,
			'new' => $new,
			'product_obj' => $product_obj,
			'product_title' => $product_title,
			'product_content' => $product_content,
			'product_excerpt' => $product_excerpt,
			'item_cats' => $item_cats,
			'product_cats' => $product_cats,
			'ingreds' => $ingreds,
			'ings' => $ings,
			'upsel' => $upsel,
			'crsel' => $crsel,
			'vegp' => $vegp,
			'spicp' => $spicp,
			'fatp' => $fatp,
			'fatrp' => $fatrp,
			'linking_posts' => $linking_posts,
			'pro_attrs' => $pro_attrs,
			'item_has_options' => $item_has_options,
			'item_promotions' => $item_promotions,
			'product_meal_type' => $product_meal_type,
			'max_orders' => $max_orders,
			'pretime' => $pretime,
			'wrap_class' => $wrap_class,
			'instruction_class' => $instruction_class,
			'feat_image_id' => $feat_image_id,
			'in_disabled' => $in_disabled,
			'on_disabled' => $on_disabled,
			'regular_price' => $regular_price,
			'sale_price' => $sale_price,
			'sale_price_dates_from' => $sale_price_dates_from,
			'sale_price_dates_to' => $sale_price_dates_to,
			'_sale_price_dates_from' => $_sale_price_dates_from,
			'_sale_price_dates_to' => $_sale_price_dates_to,
			'get_options' => $get_options,
		));
		
		ob_start();
		frozr_get_template('views/html-dashboard-items-form_data.php', $args);
		echo apply_filters('frozr_dashboard_home_page_items_form_data_html',ob_get_clean(), $args);
	}
	
	/**
	 * Item edit/add form body variation section data
	 *
	 * @param int $post_id
	 * @return void
	 */
	public function frozr_get_product_variations( $post_id ) {
		global $post;

		$loop           = 0;
		$product_id     = absint( $post_id );
		$post           = get_post( $product_id );
		$product_obj	= wc_get_product( $product_id );
		$variations     = wc_get_products( array(
			'status'         => array( 'public', 'publish' ),
			'type'           => 'variation',
			'parent'         => $product_id,
			'limit'          => -1,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'ID'         => 'DESC',
			),
			'return'         => 'objects',
		) );

		$item_attributes = $product_obj->get_attributes( 'edit' );
		if ( $variations ) {
		ob_start();
		foreach ( $variations as $variation_object ) {
			$variation_id   = $variation_object->get_id();
			$variation      = get_post( $variation_id );
			$discription = $variation_object->get_description( 'edit' );
			$regular_price = wc_format_localized_price( $variation_object->get_regular_price( 'edit' ) );
			$price = wc_format_localized_price( $variation_object->get_sale_price( 'edit' ) );
			$attribute_values = $variation_object->get_attributes( 'edit' );
			
			/* Special Price date range*/
			$sale_price_dates_from = $variation_object->get_date_on_sale_from( 'edit' ) && ( $date = $variation_object->get_date_on_sale_from( 'edit' )->getOffsetTimestamp() ) ? date_i18n( 'Y-m-d', $date ) : '';
			$sale_price_dates_to   = $variation_object->get_date_on_sale_to( 'edit' ) && ( $date = $variation_object->get_date_on_sale_to( 'edit' )->getOffsetTimestamp() ) ? date_i18n( 'Y-m-d', $date ) : '';
			
			$args = apply_filters('frozr_item_form_variation_html_args',array(
				'item_attributes' => $item_attributes,
				'variation_id' => $variation_id,
				'variation' => $variation,
				'discription' => $discription,
				'regular_price' => $regular_price,
				'price' => $price,
				'attribute_values' => $attribute_values,
				'_sale_price_dates_from' => $sale_price_dates_from,
				'_sale_price_dates_to' => $sale_price_dates_to,
			));
			
			frozr_get_template('views/html-dashboard-items-form_variation.php', $args);
			
			$loop++;
		}
		echo apply_filters('frozr_dashboard_home_page_items_form_variation_html',ob_get_clean(), $variations, $item_attributes, $post_id);
		}
	}
	
	/**
	 * Save the item data meta box.
	 *
	 * @access public
	 * @param int $post_id
	 * @return void
	 */
	public function frozr_process_item_meta( $post_id ) {
		global $wpdb;
		
		$product_obj = wc_get_product($post_id);
		$vars = $product_obj->is_type( 'variable' ) ? $product_obj->get_variation_attributes(): '';
		
		$item_has_options = ( isset( $_POST[ 'item_has_options' ] ) || $product_obj->is_type( 'variable' ) && !empty($vars) ) ? 'yes' : '';
		
		/*get Product type*/
		$product_type = ( $item_has_options ) ? 'variable' : 'simple';
		$classname    = WC_Product_Factory::get_product_classname( $post_id, $product_type ? $product_type : 'simple' );
		$product      = new $classname( $post_id );
			
		/* Get post*/
		$get_post = get_post( $post_id );
			
		/* Product Author*/
		$product_author = $get_post->post_author;

		/** set images **/
		$featured_image = isset($_POST['feat_image_id']) ? absint( $_POST['feat_image_id'] ) : null;
		if ( $featured_image ) {
			set_post_thumbnail( $post_id, $featured_image );
		}

		/*item category*/
		$dvals = explode('-', $_POST['product_cat']);
		foreach($dvals as $key => $val) {
			$dvals[$key] = trim($val);
		}
		$item_vals = array_diff($dvals, array(""));
		$item_cat = array_map( 'wc_clean', $item_vals );

		wp_set_object_terms( $post_id, $item_cat, 'product_cat' );

		/*item ingredients*/
		$vals = explode('-', $_POST['item_ingredients']);
		foreach($vals as $key => $val) {
			$vals[$key] = trim($val);
		}
		$ing_vals = array_diff($vals, array(""));
		$cat_ids = array_map( 'wc_clean', $ing_vals );
		
		wp_set_object_terms( $post_id, $cat_ids, 'ingredient' );

		/*Set item details*/
		/* Update the meta field.
		if( isset( $_POST[ 'item_veg' ] ) ) {
			update_post_meta( $post_id, '_item_veg', esc_attr($_POST[ 'item_veg' ]) );
		} else {
			update_post_meta( $post_id, '_item_veg', 'veg' );
		}
		if( isset( $_POST[ 'item_spicy' ] ) ) {
			update_post_meta( $post_id, '_item_spicy', 'yes' );
		} else {
			update_post_meta( $post_id, '_item_spicy', '' );
		}
		if( isset( $_POST[ 'item_fat' ] ) ) {
			update_post_meta( $post_id, '_item_fat', 'yes' );
		} else {
			update_post_meta( $post_id, '_item_fat', '' );
		}
		if( isset( $_POST[ 'item_fat_rate' ] ) ) {
			update_post_meta( $post_id, '_item_fat_rate', esc_attr($_POST[ 'item_fat_rate' ]) );
		} else {
			update_post_meta( $post_id, '_item_fat_rate', '' );
		}
		*/
		
		/*item preparation time*/
		update_post_meta( $post_id, 'pretime', intval($_POST['item_pretime']));
		
		/*Maximum order per day*/
		update_post_meta( $post_id, 'max_ords_day', intval($_POST['item_maxords']));
		
		update_post_meta( $post_id, '_manage_stock', 'no');
		update_post_meta( $post_id, '_backorders', '' );
				
		if ( isset( $_POST['_purchase_note'] ) ) {
			update_post_meta( $post_id, '_purchase_note', wp_kses_post( stripslashes( $_POST['_purchase_note'] ) ) );
		}
			
		if ( $item_has_options ) {
			update_post_meta( $post_id, '_item_has_options', 'yes' );
		} else {
			$data_store = WC_Data_Store::load( 'product-variable' );
			$data_store->delete_variations( $product->get_id() );
			update_post_meta( $post_id, '_item_has_options', '' );
		}
		$meal_types = is_array($_POST['product_meal_type']) ? array_map( 'wc_clean', $_POST['product_meal_type']) : array(wc_clean($_POST['product_meal_type']));
		update_post_meta( $post_id, 'product_meal_type', $meal_types);
		
		/* Save product promotions*/
		$promotions = array_filter(array_map( 'wc_clean', $_POST['item_promotions'] ) );
		$final_promotions = array();

		foreach($promotions as $promotion) {
			if (!empty ($promotion['buy'])) {
				$final_promotions[] = $promotion;
			}
		}
		update_post_meta( $post_id, 'item_promotions', $final_promotions);
		
		/* Price handling*/
		$regular_price = isset($_POST['_regular_price']) && $_POST['_regular_price'] != '' && !$item_has_options ? wc_format_decimal( $_POST['_regular_price'] ) : '';
		$sale_price = isset($_POST['_sale_price']) && $_POST['_sale_price'] != '' && !$item_has_options ? wc_format_decimal( $_POST['_sale_price'] ) : '';			
		$date_from = (!empty( $_POST['_sale_price_dates_from'] ) && !$item_has_options) ? wc_clean( $_POST['_sale_price_dates_from'] ) : '';
		$date_to   = (!empty( $_POST['_sale_price_dates_to'] ) && !$item_has_options) ? wc_clean( $_POST['_sale_price_dates_to'] ) : '';
		$attributes = array();
		
		if ( $item_has_options ) {
			$item_attrs = array_filter(array_map( 'wc_clean', $_POST['attribute_names'] ));
			$item_attrs_values = array_filter(array_map( 'wc_clean', $_POST['attribute_values'] ));
			
			if ( !empty($item_attrs) ) {
				
				$position = 0;
				
				foreach ( $item_attrs as $attribute_key => $attribute_value ) {

					$attribute_id   = 0;
					$attribute_name = wc_clean( $attribute_value );

					if ( 'pa_' === substr( $attribute_name, 0, 3 ) ) {
						$attribute_id = wc_attribute_taxonomy_id_by_name( $attribute_name );
					}
					
					$options = isset( $item_attrs_values[$attribute_key] ) ? $item_attrs_values[$attribute_key] : '';
					
					if ( is_array( $options ) ) {
						/* Term ids sent as array.*/
						$options = wp_parse_id_list( $options );
					} else {
						/* Terms or text sent in textarea.*/
						$options = 0 < $attribute_id ? wc_sanitize_textarea( wc_sanitize_term_text_based( $options ) ) : wc_sanitize_textarea( $options );
						$options = wc_get_text_attributes( $options );
					}

					if ( empty( $options ) ) {
						continue;
					}

					$attribute = new WC_Product_Attribute();
					$attribute->set_id( $attribute_id );
					$attribute->set_name( $attribute_name );
					$attribute->set_options( $options );
					$attribute->set_position( $position );
					$attribute->set_visible( 1 );
					$attribute->set_variation( 1 );
					$attributes[] = $attribute;
					
					$position++;
				}
			}
		}
		
		$errors = $product->set_props( array(
			'purchase_note'			=> isset($_POST['_purchase_note']) ? wp_kses_post( stripslashes( $_POST['_purchase_note'] ) ) : '',
			'catalog_visibility'	=> 'visible',
			'regular_price'    		=> $regular_price,
			'sale_price'        	=> $sale_price,
			'date_on_sale_from'		=> $date_from,
			'date_on_sale_to'		=> $date_to,
			'upsell_ids'			=> isset( $_POST['upsell_ids'] ) ? array_map( 'intval', (array) $_POST['upsell_ids'] ) : array(),
			'cross_sell_ids'		=> isset( $_POST['crosssell_ids'] ) ? array_map( 'intval', (array) $_POST['crosssell_ids'] ) : array(),
			'attributes'			=> $attributes,
			'manage_stock'			=> false,
			'reviews_allowed'		=> true,
		));

		if ( is_wp_error( $errors ) ) {
			wp_send_json( array(
				'msg' => __('Something went wrong!','frozr-norsani'),
			));
			die(-1);
		}

		/**
		* @since 3.0.0 to set props before save.
		*/
		do_action( 'woocommerce_admin_process_product_object', $product );
		
		$product->save();
		
		if ( $product->is_type( 'variable' ) ) {
			$original_title = isset($_POST['original_post_title']) ? $_POST['original_post_title'] : '';
			$product->get_data_store()->sync_variation_names( $product, wc_clean( $original_title ), wc_clean( $_POST['post_title'] ) );
		}
		
		/* Options*/
		if ( $item_has_options ) {
			
			update_post_meta( $post_id, '_item_has_options', 'yes' );
			$item_options = array_filter(array_map( 'wc_clean', $_POST['item_options'] ));
			$data_store = $product->get_data_store();
			$data_store->sort_all_product_variations( $product->get_id() );
			
			$prev_vars = array();
			
			if ($product->is_type( 'variable' ) && $product->get_available_variations()) {
				foreach ($product->get_available_variations() as $variation_data) {
					$prev_vars[] = $variation_data['variation_id'];
				}
			}

			$countv = 0;		
			foreach ($item_options as $item_option) {

				$attr_desc = $item_option['description'];

				/*Checkboxes*/
				$post_status = 'publish';
				
				$get_attr_var_id = !empty($item_option['id']) ? $item_option['id'] : '';

				if ($get_attr_var_id) {
					
					$varkey = array_search($get_attr_var_id, $prev_vars);

					unset($prev_vars[$varkey]);

					$variation_id = $get_attr_var_id;

					/* Generate a useful post title*/
					$variation_post_title = sprintf( __( 'Variation #%1$s of %2$s', 'frozr-norsani' ), absint( $variation_id ), esc_html( get_the_title( $post_id ) ) );

					$modified_date = date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) );

					$wpdb->update( $wpdb->posts, array(
						'post_status'       => $post_status,
						'post_title'        => $variation_post_title,
						'post_author'		=> $product_author,
						'post_modified'     => $modified_date,
						'post_modified_gmt' => get_gmt_from_date( $modified_date )
					), array( 'ID' => $variation_id ) );

					clean_post_cache( $variation_id );

				} else {

					/* Generate a useful post title*/
					$variation_post_title = sprintf( __( 'Variation #%1$s of %2$s', 'frozr-norsani' ), absint( $countv ), esc_html( get_the_title( $post_id ) ) );
					
					$variation = array(
						'post_title'   => $variation_post_title,
						'post_content' => '',
						'post_status'  => $post_status,
						'post_author'  => $product_author,
						'post_parent'  => $post_id,
						'post_type'    => 'product_variation',
					);

					$variation_id = wp_insert_post( $variation );
						
				}

				/* Only continue if we have a variation ID*/
				if ( ! $variation_id ) {
					continue;
				}
				/* Price handling*/
				$regular_price = wc_format_decimal( $item_option['regular_price'] );
				$sale_price    = isset($item_option['price']) && $item_option['price'] === '' ? '' : wc_format_decimal( $item_option['price'] );
				$date_from     = !empty($item_option['sale_price_dates_from']) ? wc_clean( $item_option['sale_price_dates_from'] ) : '';
				$date_to       = !empty($item_option['sale_price_dates_to']) ? wc_clean( $item_option['sale_price_dates_to'] ) : '';

				$variation    = new WC_Product_Variation( $variation_id );
				$errors       = $variation->set_props( array(
					'status'            => 'publish',
					'regular_price'     => $regular_price,
					'sale_price'        => $sale_price,
					'date_on_sale_from' => $date_from,
					'date_on_sale_to'   => $date_to,
					'virtual'           => 'no',
					'downloadable'      => 'no',
					'description'       => wp_kses_post($attr_desc),
					'manage_stock'      => 'no',
					'backorders'        => '',
					'attributes'        => $this->frozr_prepare_set_attributes( $product->get_attributes(), $item_option ),
					'sku'               => $product_author . '_' .$variation_id,
				) );

				if ( is_wp_error( $errors ) ) {
					wp_send_json( array(
						'msg' => __('Something went wrong!','frozr-norsani'),
					));
					die(-1);
				}

				$variation->save();
				
				do_action( 'woocommerce_save_product_variation', $variation_id, $countv );

			$countv++;
			}
			
			foreach ($prev_vars as $var_id) {
				wp_delete_post($var_id);
			}
			/* Update parent if variable so price sorting works and stays in sync with the cheapest child*/
			WC_Product_Variable::sync( $post_id );

			/* Do action for product type*/
			do_action( 'woocommerce_process_product_meta_variable', $post_id );
		}
		
		/*finally update basic item details*/
		$product_info = array(
			'ID' => $post_id,
			'post_title' => wc_clean($_POST['post_title']),
			'post_content' => wp_kses_post($_POST['post_content']),
			'post_excerpt' => wp_kses_post($_POST['post_excerpt']),
		);

		wp_update_post( $product_info );

	}
	
	/**
	 * Helper function to process item variation options.
	 *
	 * @access public
	 * @param array $all_attributes	Attributes associated with the product single option.
	 * @param array $item_option	Product single option.
	 * @return array
	 */
	public function frozr_prepare_set_attributes( $all_attributes, $item_option) {
		$attributes = array();

		if ( $all_attributes ) {
			foreach ( $all_attributes as $attribute ) {
				$attribute_key = sanitize_title( $attribute['name'] );

				$value = !empty( $item_option[$attribute_key] ) ? wp_unslash($item_option[$attribute_key]) : '';
				
				$value                        = $attribute['id'] > 0 ? sanitize_title( $value ) : html_entity_decode( wc_clean( $value ), ENT_QUOTES, get_bloginfo( 'charset' ) ); // Don't use wc_clean as it destroys sanitized characters in terms.
				$attributes[ $attribute_key ] = $value;
			}
		}
		return $attributes;
	}
	
	/**
	 * Check of a product is set as a special product for the day.
	 *
	 * @param int $item_id	Product id to check.
	 * @return bool
	 */
	public function frozr_is_item_special($item_id) {
		$item_current_status = get_post_meta($item_id, 'frozr_special_item', true) ? get_post_meta($item_id, 'frozr_special_item', true) : 0;
		$item_current_time = get_post_meta($item_id, 'frozr_special_item_save_time', true) ? get_post_meta($item_id, 'frozr_special_item_save_time', true) : date(current_time('mysql'));
		$nw = new DateTime(date(current_time('mysql')));
		$item_offline_status = get_post_meta($item_id, 'frozr_special_item_status', true);
		$vendor_id = get_post_field( 'post_author', $item_id );
		$vendor_is_online = frozr_is_rest_open($vendor_id);
		$savetime = new DateTime($item_current_time);
		$savediff = $savetime->diff($nw);
		$time_from_save = apply_filters('frozr_specials_pin_compare_time', $savediff->d);
		$time_for_special = apply_filters('frozr_specials_pin_days', 1);

		if ($item_current_status == 1 && $vendor_is_online && $time_from_save < $time_for_special) {
			if ($item_offline_status == 'offline') {
			update_post_meta($item_id, 'frozr_special_item_status', 'online');
			}
			return true;
		} elseif (!$vendor_is_online && $item_current_status == 1 && $time_from_save < $time_for_special) {
			if ($item_offline_status == 'online') {
			update_post_meta($item_id, 'frozr_special_item_status', 'offline');
			}
			return false;
		} else {
			$item_new_sts = 0;
			update_post_meta($item_id, 'frozr_special_item', $item_new_sts);
			update_post_meta($item_id, 'frozr_special_item_status', 'offline');
			return false;
		}
	}
	
	/**
	 * Get the items page list single item row.
	 *
	 * @param int|object $post	Product object or id.
	 * @return void
	 */
	public function frozr_get_dash_item($post) {
		if (!is_object($post)) {
			$post = get_post($post);
		}
		$product = wc_get_product( $post->ID );
		$item_special_status = get_post_meta($post->ID, 'frozr_special_item', true) ? get_post_meta($post->ID, 'frozr_special_item', true) : 0;
		
		ob_start();
		frozr_get_template('views/html-dashboard-items-list-single_item.php', array('item_special_status' => $item_special_status, 'product' => $product, 'post' => $post));
		do_action('frozr_after_items_listing_table_body', $post);
		echo apply_filters('frozr_dashboard_home_page_items_list_single_row',frozr_rest_html(ob_get_clean()), $item_special_status, $product, $post );
	}
	
	/**
	 * Get today's orders count for a product.
	 *
	 * @param int $product_id	Product id.
	 * @param array $order_status	Orders status to include in the check.
	 * @return array
	 */
	public function frozr_get_products_orders_number( $product_id, $order_status = array('wc-processing','wc-completed') ){
		global $wpdb;
		$time = current_time('Y-m-d');

		$results = $wpdb->get_col("
			SELECT order_items.order_id
			FROM {$wpdb->prefix}woocommerce_order_items as order_items
			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
			LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
			WHERE posts.post_type = 'shop_order'
			AND posts.post_status IN ( '" . implode( "','", $order_status ) . "' )
			AND posts.post_date LIKE '%$time%'
			AND order_items.order_item_type = 'line_item'
			AND order_item_meta.meta_key = '_product_id'
			AND order_item_meta.meta_value = '$product_id'
		");

		return $results;
	}
	
	/**
	 * Check if a product has reach max orders for today.
	 *
	 * @param int $product_id	Product id.
	 * @return bool
	 */
	public function frozr_max_orders_reached($product_id) {
		$max_orders = get_post_meta( $product_id, 'max_ords_day', true ) ? get_post_meta( $product_id, 'max_ords_day', true ) : 0;
		$orders_number = $this->frozr_get_products_orders_number($product_id);
		
		if ($max_orders && count($orders_number) >= intval($max_orders)) {
			return true;
		}
		return false;
	}
	
	/**
	 * Get the number of maximum orders allowed for a product per day.
	 *
	 * @param int $product_id	Product id.
	 * @return int
	 */
	public function frozr_product_max_orders($product_id) {
		$max_orders = get_post_meta( $product_id, 'max_ords_day', true ) ? get_post_meta( $product_id, 'max_ords_day', true ) : 0;
		if ($max_orders) {
			$orders_number = $this->frozr_get_products_orders_number($product_id);
			$orders_count = intval($max_orders) - count($orders_number); 
			
			if ($orders_count > 0) {
				return $orders_count;
			} else {
				return 0;
			}
		} else {
			$product = wc_get_product($product_id);
			return $product->get_max_purchase_quantity();
		}
	}
	
	/**
	 * Get the order preparation time.
	 *
	 * @param int $product_id
	 * @param bool $html			Return a number or html result.
	 * @return int|void
	 */
	public function frozr_get_product_preparation_time($productid, $html=true) {
		$content = false;
		/*echo product preparation time*/
		if ($pretime = get_post_meta( $productid, 'pretime', true )) {
			if ($html) {
				$content = '<div class="frozr_item_details_pop"><i class="material-icons">alarm</i>'.__('Preparation/Handling Duration:','frozr-norsani').'<span> '.$pretime.' '.__('Minutes','frozr-norsani').'</span></div>';
			} else {
				$content = $pretime;
			}
		}
		return $content;
	}

	/**
	 * Norsani custom product description content
	 *
	 * @return void
	 */
	public function frozr_custom_product_tab_desc() {
		global $post;
		
		if (get_the_term_list( $post->ID, 'ingredient' )) {
			echo '<div class="frozr_item_details_pop"><span class="frozr_pop_ings_icon"></span>'.__('Ingredients:','frozr-norsani').'<span>';
			the_terms( $post->ID, 'ingredient', '', ', ' );
			echo '</span></div>';
		}
		
		/*echo product preparation time*/
		echo $this->frozr_get_product_preparation_time($post->ID);
	}
	
	/**
	 * Norsani custom product tabs
	 *
	 * @param array $tabs
	 * @return array
	 */
	public function frozr_product_tabs( $tabs = array() ) {
		global $product, $post;
			
		$tabs['description']['title'] = __( 'Description', 'frozr-norsani' );
		$tabs['description']['callback'] = array($this, 'frozr_custom_description_tab');
		$tabs['description']['priority'] = 1;

		return $tabs;
		
	}
	
	/**
	 * Norsani custom product description tab content
	 *
	 * @return void
	 */
	public function frozr_custom_description_tab() {
		global $product;
			
		do_action('frozr_before_custom_description_tab');
			
		the_content();
	}

	/**
	 * Output the add to cart button template
	 *
	 * @param object $product
	 * @param array $data
	 * @return void
	 */
	public function frozr_add_to_cart_btn($product, $data) {
		if ($this->frozr_max_orders_reached($product->get_id())) {
			echo '<span class="frozr_max_orders_info max_reached">'.__('Maximum orders per day has reached for this product, please come back another day.','frozr-norsani').'</span>';
			return false;
		}
		$max_orders = get_post_meta( $product->get_id(), 'max_ords_day', true ) ? get_post_meta( $product->get_id(), 'max_ords_day', true ) : 0;
		$max_qty = $this->frozr_product_max_orders($product->get_id());
		$max_qty_info = $max_orders > 0 ? '<span class="frozr_max_orders_info">'.__('Remaining orders for today:','frozr-norsani').' <strong>' .$max_qty.'</strong></br>'.__('Maximum orders per day:','frozr-norsani').' <strong>'.$max_orders.'</strong></span>' : null;
		if ($product->get_type() == 'simple') {
			do_action( 'woocommerce_before_add_to_cart_quantity',$product, $data );
			echo '<div class="single_atc_wrap">';
			woocommerce_quantity_input( array(
				'min_value'		=> apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
				'max_value'		=> apply_filters( 'woocommerce_quantity_input_max', $max_qty, $product ),
				'input_value'	=> isset( $data['quantity'] ) ? wc_stock_amount( $data['quantity'] ) : $product->get_min_purchase_quantity(),
				'orders_info'	=> $max_qty_info,
			),$product);
			do_action( 'woocommerce_after_add_to_cart_quantity',$product, $data );
			echo '<button type="submit" class="single_add_to_cart_button button alt"></span>'. esc_html( $product->single_add_to_cart_text() ) .'</button>';
			echo '</div>';
		} elseif ($product->get_type() == 'variable') {
			echo '<div class="single_variation_wrap" style="display:none;">';
				do_action( 'woocommerce_before_single_variation',$product, $data );
				echo '<div class="woocommerce-variation single_variation"></div>';
				echo '<div class="woocommerce-variation-add-to-cart variations_button">';
				do_action( 'woocommerce_before_add_to_cart_quantity',$product, $data );
				echo '<div class="single_atc_wrap">';
				woocommerce_quantity_input( array(
					'min_value'		=> apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
					'max_value'		=> apply_filters( 'woocommerce_quantity_input_max', $max_qty, $product ),
					'input_value'	=> isset( $data['quantity'] ) ? wc_stock_amount( $data['quantity'] ) : $product->get_min_purchase_quantity(),
					'orders_info'	=> $max_qty_info,
				),$product );
				do_action( 'woocommerce_after_add_to_cart_quantity',$product, $data );
				echo '<button type="submit" class="single_add_to_cart_button button alt"></span>'. esc_html( $product->single_add_to_cart_text() ).'</button>';
				echo '<input type="hidden" name="product_id" value="'. absint( $product->get_id() ).'" />';
				echo '<input type="hidden" name="variation_id" class="variation_id" value="0" />';
				echo '</div>';
				echo '</div>';
				do_action( 'woocommerce_after_single_variation',$product, $data );
			echo '</div>';
		}
	}
	
	/**
	 * Add order type options to product add to cart form
	 *
	 * @param object $product
	 * @param array $data
	 */
	public function frozr_order_type_options($product, $data) {
		$product_author = apply_filters('frozr_add_order_type_author',get_post_field( 'post_author', $product->get_id() ),$product );
		$store_info = frozr_get_store_info( $product_author );
		$first_accepted_order = $store_info['accpet_order_type'][0];
		frozr_order_type_radio($product, array ( 'name' => 'order_l_type', 'value' => $first_accepted_order, 'options' => array('delivery' => array(__('Delivery','frozr-norsani'), 'motorcycle', frozr_orders_type_close_open($product, 'delivery')), 'pickup' => array(__('Pickup','frozr-norsani'), 'shopping_basket', frozr_orders_type_close_open($product, 'pickup')), 'dine-in' => array(__('Dine-in','frozr-norsani'), 'keyboard_arrow_down', frozr_orders_type_close_open($product, 'dine-in')), 'curbside' => array(__('Curbside','frozr-norsani'), 'directions_car', frozr_orders_type_close_open($product, 'curbside')))));
	}

	/**
	 * Add custom Norsani fields to product add to cart form
	 *
	 * @param object $product
	 * @param array $data
	 */
	public function frozr_add_norsani_product_atc_fields($product, $data) {
		$user_loc = frozr_norsani_cookies();
		$product_author = apply_filters('frozr_add_special_comments_author',get_post_field( 'post_author', $product->get_id() ),$product );
		$store_info = frozr_get_store_info( $product_author );
		$usersads = get_term_by( 'slug', $user_loc, 'vendor_addresses');
		$usersadsids = is_object($usersads) ? get_objects_in_term( (int) $usersads->term_id, 'vendor_addresses') : array();
		$userslocids = frozr_get_delivery_sellers($user_loc);
		$vendor_timing = norsani()->vendor->frozr_vendors_open_close($product_author, false);
		$item_timing = norsani()->vendor->frozr_get_item_timing($product->get_id()) ? norsani()->vendor->frozr_get_item_timing($product->get_id()) : $vendor_timing[0];

		$closed_timing_notice = apply_filters('frozr_pickup_closed_order_notice_text',__('The order will be processed','frozr-norsani'), $product) .' '. $item_timing;
		$first_accepted_order = $store_info['accpet_order_type'][0];
		$rest_address = '<p>'. apply_filters('frozr_addresses_text',__('Addresses:','frozr-norsani')) .' '. norsani()->vendor->frozr_get_vendor_address($product_author).'</p>';
		$no_del_loc = '<span class="no_delivery_location">'.__('Please set your location to make a delivery order.','frozr-norsani').'</span>';
		$store_name = $store_info['store_name'] ? $store_info['store_name'] : __('This vendor', 'frozr-norsani');

		?>
		<div class="item_ly_options">
		<?php if (isset($store_info['accpet_order_type']) && in_array('delivery', $store_info['accpet_order_type']) && frozr_is_rest_open($product_author) != false || isset($store_info['accpet_order_type_cl']) && in_array('delivery', $store_info['accpet_order_type_cl']) && frozr_is_rest_open($product_author) == false && !frozr_manual_vendor_online()) { ?>
			<div class="item_del_option <?php if ($first_accepted_order != 'delivery') { echo 'frozr_hide';} ?>">
				<?php if (frozr_is_rest_open($product_author)) {
					if (!$user_loc) {
						echo $no_del_loc;
					} elseif (! in_array($product_author, $userslocids)) {
						echo '<span class="no_delivery_location">'.apply_filters('frozr_no_delivery_notice_text',__('The Vendor will not deliver to your location, please choose another option.','frozr-norsani'), $product).'</span>';
					} elseif (in_array($product_author, $usersadsids)) {
						echo apply_filters('frozr_vendor_in_neighbourhood_text',__('This Vendor is in your neighbourhood.','frozr-norsani'), $product);
					}
				} elseif (in_array('delivery', $store_info['accpet_order_type_cl']) && frozr_is_rest_open($product_author) == false && !frozr_manual_vendor_online()) {
					if (!$user_loc) {
						echo $no_del_loc;
					} elseif (in_array($product_author, $usersadsids) || in_array($product_author, $userslocids)) {
						echo $closed_timing_notice;
					} elseif (! in_array($product_author, $userslocids)) {
						echo '<span class="no_delivery_location">'.apply_filters('frozr_no_delivery_notice_text',__('The Vendor will not deliver to your location, please choose another option.','frozr-norsani'), $product).'</span>';
					}
				} ?>
			</div>
			<?php } ?>
			<?php if (isset($store_info['accpet_order_type']) && in_array('pickup', $store_info['accpet_order_type']) && frozr_is_rest_open($product_author) != false || isset($store_info['accpet_order_type_cl']) && in_array('pickup', $store_info['accpet_order_type_cl']) && frozr_is_rest_open($product_author) == false && !frozr_manual_vendor_online()) { ?>
			<div class="item_pickup_option <?php if ($first_accepted_order != 'pickup') { echo 'frozr_hide';} ?>">
				<?php echo $rest_address;
				if (isset($store_info['accpet_order_type_cl']) && in_array('pickup', $store_info['accpet_order_type_cl']) && frozr_is_rest_open($product_author) == false && !frozr_manual_vendor_online()) {
					echo $closed_timing_notice;
				} ?>
			</div>
			<?php } ?>
			<?php if (isset($store_info['accpet_order_type']) && in_array('dine-in', $store_info['accpet_order_type']) && frozr_is_rest_open($product_author) != false || isset($store_info['accpet_order_type_cl']) && in_array('dine-in', $store_info['accpet_order_type_cl']) && frozr_is_rest_open($product_author) == false && !frozr_manual_vendor_online()) { ?>
			<div class="item_dinein_option <?php if ($first_accepted_order != 'dine-in') { echo 'frozr_hide';} ?>">
				<?php if (frozr_is_rest_open($product_author)) {
					frozr_wp_text_input(array( 'label' => __('Number of People invited?','frozr-norsani'), 'placeholder' => 5, 'name' => 'order_ppl_num', 'id' => 'order_ppl_num_' . $product->get_id(), 'type' => 'number', 'custom_attributes' => array('min'=>0)));
				}
				if (isset($store_info['accpet_order_type_cl']) && in_array('dine-in', $store_info['accpet_order_type_cl']) && frozr_is_rest_open($product_author) == false) {
					echo $closed_timing_notice;
				} ?>
			</div>
			<?php } ?>
			<?php if (isset($store_info['accpet_order_type']) && in_array('curbside', $store_info['accpet_order_type']) && frozr_is_rest_open($product_author) != false || isset($store_info['accpet_order_type_cl']) && in_array('dine-in', $store_info['accpet_order_type_cl']) && frozr_is_rest_open($product_author) == false && !frozr_manual_vendor_online()) { ?>
			<div class="item_curbside_option <?php if ($first_accepted_order != 'curbside') { echo 'frozr_hide';} ?>">
				<?php if (frozr_is_rest_open($product_author)) {
				frozr_wp_textarea_input (array('label' => __('Car Information','frozr-norsani'), 'name' => 'order_car_info', 'id' => 'order_car_info_' . $product->get_id(), 'placeholder' => __('Car\'s make, model & color','frozr-norsani')));
				}
				if (isset($store_info['accpet_order_type_cl']) && in_array('dine-in', $store_info['accpet_order_type_cl']) && frozr_is_rest_open($product_author) == false) {
					echo $closed_timing_notice;
				} ?>
			</div>
			<?php } ?>
		</div>
		<?php

		do_action('frozr_before_special_comments_input');

		frozr_wp_textarea_input(  array( 'name' => 'item_special_comments', 'class' => 'frozr_hide', 'id' => 'item_special_comments_' . $product->get_id(), 'label' => __( 'Add special comments or a person name.', 'frozr-norsani' ), 'placeholder' => __( 'Don\'t add products names, that have separate prices, Just add instructions for your current product like "add extra sauces or toppings" ..etc.', 'frozr-norsani' ) ) );

		do_action('frozr_after_special_comments_input');

		frozr_wp_hidden_input(array('name' => 'product_type', 'id' => 'product_type_' . $product->get_id(), 'value' => $product->get_type()));
	}
	
	/**
	 * Save custom Norsani fields for products on add to cart
	 *
	 * @param array $cartItemData
	 * @param int $productId
	 * @param int $variationId
	 */
	public function frozr_save_norsani_product_atc_fields( $cartItemData, $productId, $variationId ) {
		
		if (!empty ($variationId)) {
			$post_id = $variationId;
		} else {
			$post_id = $productId;
		}

		$applied_promotions = get_post_meta($post_id, 'item_applied_promotions', true);
		$display_promotions = array();
		
		if ('' != $_POST['item_special_comments']) {
			$cartItemData['item_comments'] = $_POST['item_special_comments'];
		}
		if ('' != $_POST['order_l_type']) {
			$cartItemData['order_l_type'] = $_POST['order_l_type'];
		}
		if ($_POST['order_l_type'] == 'dine-in' && '' != $_POST['order_ppl_num']) {
			$cartItemData['order_ppl_num'] = $_POST['order_ppl_num'];
		}
		if ($_POST['order_l_type'] == 'curbside' && '' != $_POST['order_car_info']) {
			$cartItemData['order_car_info'] = $_POST['order_car_info'];
		}
		if (!empty($applied_promotions)) {
			foreach($applied_promotions as $applied_promotion) {
				if ($applied_promotion['get'] == 'discount') {
				$display_promotions[] = '<div class="applied_promotions">'.__('Buy','frozr-norsani') . ' ' . $applied_promotion['buy'].' '. __('Get','frozr-norsani') . ' %' . floatval($applied_promotion['discount']). ' ' . __('Discount','frozr-norsani') .'</div>';
				} else {
				$display_promotions[] = '<div class="applied_promotions">'.__('Buy','frozr-norsani') . ' ' . $applied_promotion['buy'].' '. __('Get a free','frozr-norsani') . ' ' . $applied_promotion['item'].'</div>';
				}
			}
			$cartItemData['applied_promotions'] = implode('', $display_promotions);
		}
		return $cartItemData;
	}
	
	/**
	 * Support Norsani custom product atc fields in sessions
	 *
	 * @param array $cartItemData
	 * @param array $cartItemSessionData
	 * @param int $cartItemKey
	 */
	public function frozr_support_norsani_atc_fields_session( $cartItemData, $cartItemSessionData, $cartItemKey ) {
		
		if ( isset( $cartItemSessionData['item_comments'] ) ) {
			$cartItemData['item_comments'] = $cartItemSessionData['item_comments'];
		}
		if ( isset( $cartItemSessionData['order_l_type'] ) ) {
			$cartItemData['order_l_type'] = $cartItemSessionData['order_l_type'];
		}
		if ( isset( $cartItemSessionData['order_ppl_num'] ) ) {
			$cartItemData['order_ppl_num'] = $cartItemSessionData['order_ppl_num'];
		}
		if ( isset( $cartItemSessionData['order_car_info'] ) ) {
			$cartItemData['order_car_info'] = $cartItemSessionData['order_car_info'];
		}
		if ( isset( $cartItemSessionData['applied_promotions'] ) ) {
			$cartItemData['applied_promotions'] = $cartItemSessionData['applied_promotions'];
		}
				
		return $cartItemData;
	}
	
	/**
	 * Add product discount according to product promotion settings when add to cart
	 *
	 * @param int $cart_item_key
	 * @param int $product_id
	 * @param int $quantity
	 * @param int $variation_id
	 * @param object $variation
	 * @param array $cart_item_data
	 */
	public function frozr_add_item_discount( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {

		$promotion_rules = get_post_meta($product_id, 'item_promotions', true);
		$applied_promotions = array();

		if (!empty ($variation_id)) {
			$post_id = $variation_id;
		} else {
			$post_id = $product_id;
		}

		$price = floatval(get_post_meta($post_id, '_price', true));

		if (!empty (WC()->cart->cart_contents[$cart_item_key]['quantity'])) {
			$new_qty = WC()->cart->cart_contents[$cart_item_key]['quantity'];
		} else {
			$new_qty = $quantity;
		}

		$total_discount = 0;

		if (!empty ($promotion_rules)) {
			foreach ($promotion_rules as $promotion_rule) {
				if ($promotion_rule['buy'] == $new_qty && $promotion_rule['get'] == 'discount') {
					
					$total_discount += floatval($promotion_rule['discount']);
					$applied_promotions[] = $promotion_rule;
				
				} elseif ($promotion_rule['buy'] == $quantity && $promotion_rule['get'] == 'free_item') {
						
					$applied_promotions[] = $promotion_rule;
				}
			}
		}

		if ($total_discount > 0) {
			$discount = ($price * $total_discount) / 100;
			$amount = $price - $discount;

			WC()->cart->cart_contents[$cart_item_key]['data']->price = $amount;
		}

		if (!empty($applied_promotions)) {
			foreach($applied_promotions as $applied_promotion) {
				if ($applied_promotion['get'] == 'discount') {
				$display_promotions[] = '<div class="applied_promotions">'.__('Buy','frozr-norsani') . ' ' . $applied_promotion['buy'].' '. __('Get','frozr-norsani') . ' %' . floatval($applied_promotion['discount']). ' ' . __('Discount','frozr-norsani') .'</div>';
				} else {
				$display_promotions[] = '<div class="applied_promotions">'.__('Buy','frozr-norsani') . ' ' . $applied_promotion['buy'].' '. __('Get a free','frozr-norsani') . ' ' . get_the_title($applied_promotion['item']).'</div>';
				}
			}
			$cart->cart_contents[$cart_item_key]['applied_promotions'] = implode('', $display_promotions);
		}

		update_post_meta($post_id, 'item_applied_promotions', $applied_promotions);	
	}
	
	/**
	 * Support product discount according to product promotion settings when cart is loaded from session
	 *
	 * @param object $cart
	 */
	public function frozr_add_item_sdiscount_session( $cart ) {
		
		$cart_items = $cart->get_cart();
		
		foreach ($cart_items as $cart_item_key => $cart_item) {
			$quantity = $cart_item['quantity'];
			$id = $cart_item['product_id'];
			$promotion_rules = get_post_meta($id, 'item_promotions', true);
			$price = floatval($cart_item['data']->get_price());
			
			if (!empty ($cart_item['variation_id'])) {
				$post_id = $cart_item['variation_id'];
			} else {
				$post_id = $id;
			}
			
			$total_discount = 0;
			$applied_promotions = array();
			$display_promotions = array();

			if (!empty ($promotion_rules)) {
				foreach ($promotion_rules as $promotion_rule) {
					if ($promotion_rule['buy'] == $quantity && $promotion_rule['get'] == 'discount') {
						
						$total_discount += floatval($promotion_rule['discount']);
						$applied_promotions[] = $promotion_rule;
					
					} elseif ($promotion_rule['buy'] == $quantity && $promotion_rule['get'] == 'free_item') {
						
						$applied_promotions[] = $promotion_rule;
					
					}
				}
			}

			if ($total_discount > 0) {
				$discount = ($price * $total_discount) / 100;
				$amount = $price - $discount;

				$cart->cart_contents[$cart_item_key]['data']->price = $amount;
			}
			
			if (!empty($applied_promotions)) {
				foreach($applied_promotions as $applied_promotion) {
					if ($applied_promotion['get'] == 'discount') {
					$display_promotions[] = '<div class="applied_promotions">'.__('Buy','frozr-norsani') . ' ' . $applied_promotion['buy'].' '. __('Get','frozr-norsani') . ' %' . floatval($applied_promotion['discount']). ' ' . __('Discount','frozr-norsani') .'</div>';
					} else {
					$display_promotions[] = '<div class="applied_promotions">'.__('Buy','frozr-norsani') . ' ' . $applied_promotion['buy'].' '. __('Get a free','frozr-norsani') . ' ' . get_the_title($applied_promotion['item']).'</div>';
					}
				}
				$cart->cart_contents[$cart_item_key]['applied_promotions'] = implode('', $display_promotions);
			}
			
			update_post_meta($post_id, 'item_applied_promotions', $applied_promotions);
		}
	}
	
	/**
	 * Show Norsani custom atc product fields on checkout
	 *
	 * @param array $data
	 * @param array $cartItem
	 */
	public function frozr_support_norsani_atc_fields_checkout( $data, $cartItem ) {

		if ( isset( $cartItem['item_comments'] ) ) {
			$data[] = apply_filters('frozr_item_comments_args',array(
				'name' => __('Comments', 'frozr-norsani'),
				'value' => $cartItem['item_comments']
			));
		}
		if ( isset( $cartItem['order_l_type'] ) ) {
			$data[] = apply_filters('frozr_order_type_args',array(
				'name' => __('Order Type', 'frozr-norsani'),
				'value' => $cartItem['order_l_type']
			));
		}
		if ( isset( $cartItem['order_ppl_num'] ) ) {
			$data[] = apply_filters('frozr_order_people_number_args',array(
				'name' => __('People In', 'frozr-norsani'),
				'value' => $cartItem['order_ppl_num']
			));
		}
		if ( isset( $cartItem['order_car_info'] ) ) {
			$data[] = apply_filters('frozr_order_car_info_args',array(
				'name' => __('Car Info', 'frozr-norsani'),
				'value' => $cartItem['order_car_info']
			));
		}
		if ( isset( $cartItem['applied_promotions'] ) ) {
			$data[] = apply_filters('frozr_order_applied_promotions_args',array(
				'name' => __('Promotions', 'frozr-norsani'),
				'value' => $cartItem['applied_promotions']
			));
		}

		return $data;
	}
	
	/**
	 * Save Norsani custom atc product fields on checkout
	 *
	 * @param object $item
	 * @param int $cart_item_key
	 * @param array $values
	 * @param object $order
	 */
	public function frozr_save_norsani_atc_fields_checkout( $item, $cart_item_key, $values, $order ) {

	   if ( isset( $values['item_comments'] ) ) {
			$item->add_meta_data( 'special-comments', $values['item_comments'], true );
		}
		if ( isset( $values['order_l_type'] ) ) {
			$item->add_meta_data( 'order-type', $values['order_l_type'], true );
		}
		if ( isset( $values['order_ppl_num'] ) ) {
			$item->add_meta_data( 'people-in', $values['order_ppl_num'], true );
		}
		if ( isset( $values['order_car_info'] ) ) {
			$item->add_meta_data( 'car-info', $values['order_car_info'], true );
		}
		if ( isset( $values['applied_promotions'] ) ) {
			$item->add_meta_data( 'promotions', $values['applied_promotions'], true );
		}
	}
}