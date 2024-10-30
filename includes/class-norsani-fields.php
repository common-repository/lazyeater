<?php
/**
 * All form fields functions
 *
 * @package Norsani
 */

if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/

class Norsani_Fields {
	
	/**
	 * The single instance of the class.
	 *
	 * @var Norsani_Fields
	 * @since 1.9
	 */
	protected static $_instance = null;
	
	/**
	 * Main Norsani_Fields Instance.
	 *
	 * @since 1.9
	 * @static
	 * @return Norsani_Fields - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Norsani_Fields Constructor.
	 */
	public function __construct() {
		do_action( 'norsani_fields_loaded' );
	}

	/**
	 * Generate an input field based on arguments
	 *
	 * @param int $post_id
	 * @param string $meta_key
	 * @param array $attr
	 * @param string $type
	 * @return void
	 */
	public function frozr_post_input_box( $post_id, $meta_key, $attr = array(), $type = 'text'  ) {
		$placeholder = isset( $attr['placeholder'] ) ? esc_attr( $attr['placeholder'] ) : '';
		$class = isset( $attr['class'] ) ? esc_attr( $attr['class'] ) : 'form-control';
		$name = isset( $attr['name'] ) ? esc_attr( $attr['name'] ) : $meta_key;
		if ($post_id) {
			$value = isset( $attr['value'] ) ? $attr['value'] : get_post_meta( $post_id, $meta_key, true );
		} else {
			$value = "";
		}
		$size = isset( $attr['size'] ) ? $attr['size'] : 30;

		switch ($type) {
			case 'text':
				?>
				<input type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo esc_attr( $value ); ?>" class="<?php echo $class; ?>" placeholder="<?php echo $placeholder; ?>">
				<?php
				break;

			case 'textarea':
				$rows = isset( $attr['rows'] ) ? absint( $attr['rows'] ) : 4;
				?>
				<textarea name="<?php echo $name; ?>" id="<?php echo $name; ?>" rows="<?php echo $rows; ?>" class="<?php echo $class; ?>" placeholder="<?php echo $placeholder; ?>"><?php echo esc_textarea( $value ); ?></textarea>
				<?php
				break;

			case 'checkbox':
				$label = isset( $attr['label'] ) ? $attr['label'] : '';
				?>

				<label class="checkbox-inline" for="<?php echo $name; ?>">
					<input name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo $value; ?>" type="checkbox"<?php checked( $value, 'yes' ); ?>>
					<?php echo $label; ?>
				</label>

				<?php
				break;

			case 'select':
				$options = is_array( $attr['options'] ) ? $attr['options'] : array();
				?>
				<select name="<?php echo $name; ?>" id="<?php echo $name; ?>" class="<?php echo $class; ?>">
					<?php foreach ($options as $key => $label) { ?>
						<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $value, $key ); ?>><?php echo $label; ?></option>
					<?php } ?>
				</select>

				<?php
				break;

			case 'number':
				$min = isset( $attr['min'] ) ? $attr['min'] : 0;
				$step = isset( $attr['step'] ) ? $attr['step'] : 'any';
				?>
				<input type="number" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo esc_attr( $value ); ?>" class="<?php echo $class; ?>" placeholder="<?php echo $placeholder; ?>" min="<?php echo esc_attr( $min ); ?>" step="<?php echo esc_attr( $step ); ?>" size="<?php echo esc_attr( $size ); ?>">
				<?php
				break;
				
				do_action('frozr_post_input_box_type', $name, $class, $placeholder, $attr, $value);
		}
	}
	
	/**
	 * Hidden field
	 *
	 * @param array $field
	 * @return void
	 */
	public function frozr_wp_hidden_input( $field ) {
		global $thepostid, $post;
		$thepostid = empty( $thepostid ) && is_object($post) ? $post->ID : $thepostid;
		$field['name'] = isset( $field['name'] ) ? $field['name'] : $field['id'];
		$field['value'] = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['name'], true );
		$field['class'] = isset( $field['class'] ) ? $field['class'] : '';
		echo '<input type="hidden" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) .  '" /> ';
	}
	
	/**
	 * Textarea field
	 *
	 * @param array $field
	 * @return void
	 */
	public function frozr_wp_textarea_input( $field ) {
		global $thepostid, $post;
		$thepostid              = empty( $thepostid ) && is_object($post) ? $post->ID : $thepostid;
		$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
		$field['label']			= isset( $field['label'] ) ? $field['label'] : '';
		$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['name']         = isset( $field['name'] ) ? $field['name'] : $field['id'];
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['name'], true );
		/* Custom attribute handling*/
		$custom_attributes = array();
		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
			foreach ( $field['custom_attributes'] as $attribute => $value ){
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
			}
		}
		echo '<div class="form-group '. $field['class'] . ' ' . esc_attr( $field['name'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label class="control-label" for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><textarea style="' . esc_attr( $field['style'] ) . '"  name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" rows="2" cols="20" ' . implode( ' ', $custom_attributes ) . '>' . esc_textarea( $field['value'] ) . '</textarea> ';
		if ( ! empty( $field['description'] ) ) {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}
		echo '</div>';
	}
	
	/**
	 * Select field
	 *
	 * @param array $field
	 * @return void
	 */
	public function frozr_wp_select( $field ) {
		global $thepostid, $post;
		$thepostid              = empty( $thepostid ) && is_object($post) ? $post->ID : $thepostid;
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
		$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
		$field['label']			= isset( $field['label'] ) ? $field['label'] : '';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
		$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
		$field['custom_attributes'] = isset( $field['custom_attributes'] ) ? $field['custom_attributes'] : array();
		$m = (! empty( $field['custom_attributes']) && $field['custom_attributes']['multiple'] == 'multiple') ? '[]' : '';
		
		/* Custom attribute handling*/
		$custom_attributes = array();
		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
			foreach ( $field['custom_attributes'] as $attribute => $value ){
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
			}
		}
		echo '<div class="form-group ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . $m .'" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" ' . implode( ' ', $custom_attributes ) . '>';
		foreach ( $field['options'] as $key => $value ) {
			if ($field['custom_attributes']['multiple'] == 'multiple' && is_array($field['value'])) {
				$selected = ( in_array( $key, $field['value'] ) ? 'selected="selected"' : '' );
			} else {
				$selected = ( esc_attr( $field['value'] ) == esc_attr( $key ) ) ? 'selected="selected"' : '';
			}

			echo '<option value="' . esc_attr( $key ) . '" ' . $selected . '>' . esc_html( $value ) . '</option>';
		}
		echo '</select> ';
		if ( ! empty( $field['description'] ) ) {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}
		echo '</div>';
	}

	/**
	 * Output a radio input box.
	 *
	 * @param object $product
	 * @param array $field
	 * @return void
	 */
	public function frozr_order_type_radio( $product, $field ) {
		$product_author = get_post_field( 'post_author', $product->get_id() );
		$store_info = frozr_get_store_info( $product_author );

		$thepostid              = empty( $thepostid ) && is_object($product) ? $product->get_id() : $thepostid;
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
		$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['name']          = isset( $field['name'] ) ? $field['name'] : '';
		$field['label']			= isset( $field['label'] ) ? $field['label'] : '';
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['name'], true );
		$field['custom_attributes'] = isset( $field['custom_attributes'] ) ? $field['custom_attributes'] : array();

		echo '<fieldset class="form-field ' . esc_attr( $field['name'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><legend>' . wp_kses_post( $field['label'] ) . '</legend><ul class="wc-radios">';

		
		foreach ( $field['options'] as $key => $value ) {
		if (isset($store_info['accpet_order_type']) && in_array($key, $store_info['accpet_order_type']) && frozr_is_rest_open($product_author) != false || isset($store_info['accpet_order_type_cl']) && in_array($key, $store_info['accpet_order_type_cl']) && frozr_is_rest_open($product_author) == false && !frozr_manual_vendor_online()) {
			if ('' != $value[1]) { $ricon = '<i class="material-icons">'. $value[1]  .'</i>'; } else { $ricon = ''; }
				echo '<li><label class="'.$field['name'].'"><input 
						name="' . esc_attr( $field['name'] ) . '"
						value="' . esc_attr( $key ) . '"
						type="radio"
						class="' . esc_attr( $field['class'] ) .' '. esc_attr( $value[2] ) . ' ' . esc_attr( $key ) . '_'. esc_attr( $field['name'] ) . '"
						style="' . esc_attr( $field['style'] ) . '"
						' . checked( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '
						/>'.$ricon.' '.esc_html( $value[0] ) . '</label>
				</li>';
			}
		}
		echo '</ul>';

		if ( ! empty( $field['description'] ) ) {

			if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
				echo wc_help_tip( $field['description'] );
			} else {
				echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
			}
		}

		echo '</fieldset>';
	}
	
	/**
	 * checkbox field
	 *
	 * @param array $field
	 * @return void
	 */
	public function frozr_wp_checkbox( $field ) {
		global $thepostid, $post;
		$thepostid              = empty( $thepostid ) && is_object($post) ? $post->ID : $thepostid;
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'checkbox';
		$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
		$field['label']			= isset( $field['label'] ) ? $field['label'] : '';
		$field['cbvalue']       = isset( $field['cbvalue'] ) ? $field['cbvalue'] : 'yes';
		$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
		$field['custom_attributes'] = isset( $field['custom_attributes'] ) ? $field['custom_attributes'] : array();

		/* Custom attribute handling*/
		$custom_attributes = array();
		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
			foreach ( $field['custom_attributes'] as $attribute => $value ){
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
			}
		}
		echo '<div class="form-group ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><input type="checkbox" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['cbvalue'] ) . '" ' . checked( $field['value'], $field['cbvalue'], false ) . '  ' . implode( ' ', $custom_attributes ) . '/> ';
		if ( ! empty( $field['description'] ) ) {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}
		echo '</div>';
	}

	/**
	 * Text Input field
	 *
	 * @param array $field
	 * @return void
	 */
	public function frozr_wp_text_input( $field ) {
		global $thepostid, $post;
		$thepostid				= empty( $thepostid ) && is_object($post) ? $post->ID : $thepostid;
		$field['placeholder']	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$field['class']			= isset( $field['class'] ) ? $field['class'] : 'short';
		$field['style']			= isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_class']	= isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['name']			= isset( $field['name'] ) ? $field['name'] : $field['id'];
		$field['value']			= isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['name'], true );
		$field['label']			= isset( $field['label'] ) ? $field['label'] : '';
		$field['type']			= isset( $field['type'] ) ? $field['type'] : 'text';
		$field['custom_attributes'] = isset( $field['custom_attributes'] ) ? $field['custom_attributes'] : array();
		$data_type				= empty( $field['data_type'] ) ? '' : $field['data_type'];
		switch ( $data_type ) {
			case 'price' :
				$field['class'] .= ' wc_input_price';
				$field['value']  = wc_format_localized_price( $field['value'] );
				break;
			case 'decimal' :
				$field['class'] .= ' wc_input_decimal';
				$field['value']  = wc_format_localized_decimal( $field['value'] );
				break;
			case 'stock' :
				$field['class'] .= ' wc_input_stock';
				$field['value']  = wc_stock_amount( $field['value'] );
				break;
			case 'url' :
				$field['class'] .= ' wc_input_url';
				$field['value']  = esc_url( $field['value'] );
				break;
			default :
				break;
		}
		/* Custom attribute handling*/
		$custom_attributes = array();
		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
			foreach ( $field['custom_attributes'] as $attribute => $value ){
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
			}
		}
		echo '<div class="form-group ' . esc_attr( $field['name'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label class="control-label" for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><input type="' . esc_attr( $field['type'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . implode( ' ', $custom_attributes ) . ' /> ';
		if ( ! empty( $field['description'] ) ) {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}
		echo '</div>';
	}
}