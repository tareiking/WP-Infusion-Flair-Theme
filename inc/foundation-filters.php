<?php
/**
 * By default WordPress adds the CSS class .sticky to sticky posts. Foundation 5 uses that class name for a Sticky Top Bar so let's rename it
 */

function flair_rename_sticky_post_class( $classes ){

	$classes = preg_replace( '/^sticky$/', 'sticky-post', $classes );

	return $classes;
}

add_filter( 'post_class', 'flair_rename_sticky_post_class' );

// Add "has-dropdown" CSS class to navigation menu items that have children in a submenu.
function flair_nav_menu_item_parent_classing( $classes ) {

	$classes = preg_replace( '/^page_item_has_children$/', 'page_item_has_children has-dropdown', $classes );
	$classes = preg_replace( '/^menu-item-has-children$/', 'menu-item-has-children has-dropdown', $classes );

	return $classes;
}

add_filter( 'nav_menu_css_class', 'flair_nav_menu_item_parent_classing', 10, 2 );

// Deletes empty classes and changes the sub menu class name
function flair_change_submenu_class( $menu ) {
	$menu = preg_replace( '/ class="sub-menu"/', ' class="dropdown"', $menu );

	return $menu;
}

add_filter( 'wp_nav_menu', 'flair_change_submenu_class' );


// Use the active class of the ZURB Foundation for the current menu item. (From: https://github.com/milohuang/reverie/blob/master/functions.php)
function flair_required_active_nav_class( $classes, $item ) {
	if ( in_array(  'current_page_item', $classes) === true ) {
		$classes[] = 'active';
	}

	return $classes;
}

add_filter( 'nav_menu_css_class', 'flair_required_active_nav_class', 10, 2 );

/**
 * We need to write a custom function and a custom walker to add the dropdown class to the default <ul class='children'> created in WordPress's wp_page_menu function
 */

function flair_page_menu() { ?>
	<ul>
	<?php
		wp_list_pages( array(
		    'walker'   => new Flair_Page_Walker,
			'title_li' => ''
		));
	?>
	</ul>
<?php
}

class Flair_Page_Walker extends Walker_Page {

	/**
	 * @see Walker::end_lvl()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 * @param array $args
	 */
    function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "\n$indent<ul class='children dropdown'>\n";
	}

	/**
	 * @see Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page Page data object.
	 * @param int $depth Depth of page. Used for padding.
	 * @param int $current_page Page ID.
	 * @param array $args
	 */
	function start_el( &$output, $page, $depth = 0, $args = array(), $current_page = 0 ) {
		if ( $depth )
			$indent = str_repeat( "\t", $depth );
		else
			$indent = '';

		extract( $args, EXTR_SKIP );
		$css_class = array( 'page_item', 'page-item-'.$page->ID );

		if( isset( $args['pages_with_children'][ $page->ID ] ) )
			$css_class[] = 'page_item_has_children has-dropdown';

		if ( ! empty( $current_page ) ) {
			$_current_page = get_post( $current_page );
			if ( in_array( $page->ID, $_current_page->ancestors ) )
				$css_class[] = 'current_page_ancestor';
			if ( $page->ID == $current_page )
				$css_class[] = 'current_page_item';
			elseif ( $_current_page && $page->ID == $_current_page->post_parent )
				$css_class[] = 'current_page_parent';
		} elseif ( $page->ID == get_option( 'page_for_posts' ) ) {
			$css_class[] = 'current_page_parent';
		}

		$css_class = implode( ' ', apply_filters( 'page_css_class', $css_class, $page, $depth, $args, $current_page ) );

		if ( '' === $page->post_title )
			$page->post_title = sprintf( __( '#%d (no title)', 'flair' ), $page->ID );

		/** This filter is documented in wp-includes/post-template.php */
		$output .= $indent . '<li class="' . $css_class . '"><a href="' . get_permalink( $page->ID ) . '">' . $link_before . apply_filters( 'the_title', $page->post_title, $page->ID ) . $link_after . '</a>';

		if ( ! empty( $show_date ) ) {
			if ( 'modified' == $show_date )
				$time = $page->post_modified;
			else
				$time = $page->post_date;

			$output .= " " . mysql2date( $date_format, $time );
		}
	}

}


/**
 * Don't let the users enable Gravity Forms CSS as we have the correct CSS loaded in our theme
 */

function flair_dequeue_gravity_forms_css() {
	if	( ! get_option ( 'rg_gforms_disable_css' ) ) {
		update_option( 'rg_gforms_disable_css', TRUE );
	}
}

/**
 * We only want to add the Gravity Forms helpers for Foundation if Gravity Forms is loaded and activated.
 */

if ( class_exists( 'GFForms' ) ) {
	add_action( 'init', 'flair_dequeue_gravity_forms_css' );
	add_filter( 'gform_validation_message', 'flair_gform_form_validation_message', 10, 2 );
	add_filter( 'gform_field_choices', 'flair_gform_field_choices', 10, 2);
}
/**
 * Adds the Zurb Foundation alert classes to the Gravity Forms errors
 *
 * @param $validation_message
 * @param $form
 *
 * @return mixed
 */
function flair_gform_form_validation_message( $validation_message, $form ) {

	$form_validation_msg_classes = 'alert-box alert';

	if ( ! empty( $validation_message ) ) {


		//Add Zurb foundation alert class to validation error div
		$validation_message = preg_replace( "/validation_error/", "{$form_validation_msg_classes}", $validation_message );

	}

	return $validation_message;
}

add_filter( 'gform_field_content', 'flair_gform_field_content', 10, 5 );


function flair_gform_field_content( $content, $field, $value, $lead_id, $form_id ) {

	$force_frontend_label = false;

	if ( ! is_admin() ) {

		//Change html content for text input and address fields
		if ( ( $field['type'] === 'text' ) || ( $field['type'] === 'address' ) || ( $field['type'] === 'name' ) || ( $field['type'] === 'website' )  || ( $field['type'] === 'email' ) || ( $field['type'] === 'textarea' ) || $field['type'] === 'select' ) {

			$id = $field["id"];

			//Cache validation message html
			ob_start();
			?>
				<div>
					<small class='error gfield_description validation_message'>%s</small>
				</div>
			<?php
			$validation_message = ob_get_contents();
			ob_end_clean();

			$validation_message = ( rgget( "failed_validation", $field ) && ! empty( $field["validation_message"] ) ) ? sprintf( $validation_message, $field["validation_message"] ) : "";


			$field_label = $force_frontend_label ? $field["label"] : GFCommon::get_label( $field );
			if ( rgar( $field, "inputType" ) == "singleproduct" && ! rgempty( $field["id"] . ".1", $value ) ) {
				$field_label = rgar( $value, $field["id"] . ".1" );
			}

			$field_id = IS_ADMIN || $form_id == 0 ? "input_$id" : "input_" . $form_id . "_$id";

			$target_input_id = "";

			$required_div = IS_ADMIN || rgar( $field, "isRequired" ) ? sprintf( "<span class='gfield_required'>%s</span>", $field["isRequired"] ? "*" : "" ) : "";

			$is_description_above = rgar( $field, "descriptionPlacement" ) == "above";


			$admin_buttons = IS_ADMIN ? "<div class='gfield_admin_icons'><div class='gfield_admin_header_title'>{$field_type_title} : " . __( "Field ID", "gravityforms" ) . " {$field["id"]}</div>" . $delete_field_link . $duplicate_field_link . "<a class='field_edit_icon edit_icon_collapsed' title='" . __( "click to edit this field", "gravityforms" ) . "'>" . __( "Edit", "gravityforms" ) . "</a></div>" : "";

			if ( empty( $target_input_id ) ) {
				$target_input_id = $field_id;
			}


			//Field Description
			$description = '';

			if ( $is_description_above ) {
				$field_content = sprintf( "%s<label class='gfield_label' for='%s'>%s%s</label>%s{FIELD}%s", $admin_buttons, $target_input_id, esc_html( $field_label ), $required_div, $description, $validation_message );
			}
			else {
				$field_content = sprintf( "%s<label class='gfield_label' for='%s'>%s%s</label>{FIELD}%s%s", $admin_buttons, $target_input_id, esc_html( $field_label ), $required_div, $description, $validation_message );
			}


			//Detect if field type is text or address and call the required function to get field content
			if ( $field['type'] === 'address' ) {

				$content = str_replace( "{FIELD}", flair_gform_get_address_field( $field, $value, 0, $form_id ), $field_content );

			}
			elseif ( $field['type'] === 'name' ) {

				$content = str_replace( "{FIELD}", flair_gform_get_name_field( $field, $value, 0, $form_id ), $field_content );

			}
			elseif ( $field['type'] === 'website' ) {

				$content = str_replace( "{FIELD}", flair_gform_get_website_field( $field, $value, 0, $form_id ), $field_content );

			}
			else {
				$content = str_replace( "{FIELD}", GFCommon::get_field_input( $field, $value, 0, $form_id ), $field_content );
			}

		}

	}

	return $content;
}

function flair_gform_get_website_field( $field, $value, $lead_id, $form_id ) {

	//Init vars
	$output = null;

	//Cache css id
	$input_id = str_replace( '.', '_', $field['id'] );

	ob_start();
	?>
	<div id="input_<?php esc_attr_e( $input_id ); ?>_container" class="<?php echo apply_filters( 'flair_gforms_website_class', 'row collapse', $field, $form_id ); ?>">
		<div class="small-3 large-2 columns">
			<span class="prefix">http://</span>
		</div>
		<div class="small-9 large-10 columns">
			<input id="input_<?php esc_attr_e( $input_id ); ?>" type="text" placeholder="<?php echo apply_filters( 'flair_gforms_website_placeholder', 'Enter your URL...', $field, $form_id ); ?>" tabindex="<?php esc_attr_e( $field['id'] ); ?>" name="input_<?php esc_attr_e( $input['id'] ); ?>" class="<?php echo apply_filters( 'flair_gforms_website_field_class', 'placeholder', $field, $form_id ); ?>">
		</div>
	</div>
	<?php
	$output = ob_get_contents();
	ob_end_clean();

	return $output;

}

function flair_gform_get_name_field( $field, $value, $lead_id, $form_id ) {

	//Init vars
	$output = null;

	ob_start();
	?>
		<?php foreach ( $field['inputs'] as $key => $input ):

			//Cache css id
			$input_id = str_replace( '.', '_', $input['id'] );
			?>
			<div id="input_<?php esc_attr_e( $input_id ); ?>_container" class="<?php echo apply_filters( 'flair_gforms_name_class', 'large-6 columns', $field, $form_id, $input ); ?>">
				<input id="input_<?php esc_attr_e( $input_id ); ?>" type="text" tabindex="<?php esc_attr_e( $field['id'] ); ?>" name="input_<?php esc_attr_e( $input['id'] ); ?>"
					   <?php echo $input['label']; ?>
			<?php if ( $input['label'] == "First" ) { ?>
		placeholder="<?php echo apply_filters( 'gform_name_first', __( 'First', 'gravityforms' ), $form_id ); ?>" class="<?php echo apply_filters( 'flair_gforms_name_field_class', 'placeholder', $field, $form_id, $input ); ?>" />
			<?php }
			else { ?>
			placeholder="<?php echo apply_filters( 'gform_name_last',__( 'Last', 'gravityforms' ), $form_id ); ?>" class="<?php echo apply_filters( 'flair_gforms_name_field_class', 'placeholder', $field, $form_id, $input ); ?>" />
			<?php
			} ?>
			</div>
		<?php endforeach; ?>
	<?php
	$output = ob_get_contents();
	ob_end_clean();

	return $output;
}

function flair_gform_get_address_field( $field, $value, $lead_id, $form_id ) {

	//Cache foudantion div to start a row
	$div_row = "<div class='row'>";

	$id       = $field["id"];
	$field_id = IS_ADMIN || $form_id == 0 ? "input_$id" : "input_" . $form_id . "_$id";
	$form_id  = IS_ADMIN && empty( $form_id ) ? rgget( "id" ) : $form_id;

	$size          = rgar( $field, "size" );
	$disabled_text = ( IS_ADMIN && RG_CURRENT_VIEW != "entry" ) ? "disabled='disabled'" : "";
	$class_suffix  = RG_CURRENT_VIEW == "entry" ? "_admin" : "";
	$class         = $size . $class_suffix;

	$currency = "";
	if ( RG_CURRENT_VIEW == "entry" ) {
		$lead      = RGFormsModel::get_lead( $lead_id );
		$post_id   = $lead["post_id"];
		$post_link = "";
		if ( is_numeric( $post_id ) && self::is_post_field( $field ) ) {
			$post_link = "You can <a href='post.php?action=edit&post=$post_id'>edit this post</a> from the post page.";
		}
		$currency = $lead["currency"];
	}

	$street_value  = "";
	$street2_value = "";
	$city_value    = "";
	$state_value   = "";
	$zip_value     = "";
	$country_value = "";

	$class_suffix = '';

	if ( is_array( $value ) ) {
		$street_value  = esc_attr( rgget( $field["id"] . ".1", $value ) );
		$street2_value = esc_attr( rgget( $field["id"] . ".2", $value ) );
		$city_value    = esc_attr( rgget( $field["id"] . ".3", $value ) );
		$state_value   = esc_attr( rgget( $field["id"] . ".4", $value ) );
		$zip_value     = esc_attr( rgget( $field["id"] . ".5", $value ) );
		$country_value = esc_attr( rgget( $field["id"] . ".6", $value ) );
	}

	$address_types = GFCommon::get_address_types( $form_id );
	$addr_type     = empty( $field["addressType"] ) ? "international" : $field["addressType"];
	$address_type  = $address_types[$addr_type];

	$state_label  = empty( $address_type["state_label"] ) ? __( "State", "gravityforms" ) : $address_type["state_label"];
	$zip_label    = empty( $address_type["zip_label"] ) ? __( "Zip Code", "gravityforms" ) : $address_type["zip_label"];
	$hide_country = ! empty( $address_type["country"] ) || rgget( "hideCountry", $field );

	if ( empty( $country_value ) ) {
		$country_value = rgget( "defaultCountry", $field );
	}

	if ( empty( $state_value ) ) {
		$state_value = rgget( "defaultState", $field );
	}

	$country_list = GFCommon::get_country_dropdown( $country_value );

	//changing css classes based on field format to ensure proper display
	$address_display_format = apply_filters( "gform_address_display_format", "default" );
	$city_location          = $address_display_format == "zip_before_city" ? "right" : "left";
	$zip_location           = $address_display_format != "zip_before_city" && rgar( $field, "hideState" ) ? "right" : "left";
	$state_location         = $address_display_format == "zip_before_city" ? "left" : "right";
	$country_location       = rgar( $field, "hideState" ) ? "left" : "right";

	//address field
	$tabindex       = GFCommon::get_tabindex();
	$street_address = sprintf( "<span class='ginput_full$class_suffix' id='" . $field_id . "_1_container'><input type='text' name='input_%d.1' id='%s_1' value='%s' $tabindex %s placeholder='" . apply_filters( "gform_address_street_{$form_id}", apply_filters( "gform_address_street", __( "Street Address", "gravityforms" ), $form_id ), $form_id ) . "'/></span>", $id, $field_id, $street_value, $disabled_text, $field_id );

	//address line 2 field
	$street_address2 = "";
	$style           = ( IS_ADMIN && rgget( "hideAddress2", $field ) ) ? "style='display:none;'" : "";
	if ( IS_ADMIN || ! rgget( "hideAddress2", $field ) ) {
		$tabindex        = GFCommon::get_tabindex();
		$street_address2 = sprintf( "<span class='ginput_full$class_suffix' id='" . $field_id . "_2_container' $style><input type='text' name='input_%d.2' id='%s_2' value='%s' $tabindex %s placeholder='" . apply_filters( "gform_address_street2_{$form_id}", apply_filters( "gform_address_street2", __( "Address Line 2", "gravityforms" ), $form_id ), $form_id ) . "'/></span>", $id, $field_id, $street2_value, $disabled_text, $field_id );
	}

	if ( $address_display_format == "zip_before_city" ) {
		//zip field
		$tabindex = GFCommon::get_tabindex();
		$zip      = sprintf( "<span class='ginput_{$zip_location}$class_suffix' id='" . $field_id . "_5_container'><input type='text' name='input_%d.5' id='%s_5' value='%s' $tabindex %s placeholder='" . apply_filters( "gform_address_zip_{$form_id}", apply_filters( "gform_address_zip", $zip_label, $form_id ), $form_id ) . "'/></span>", $id, $field_id, $zip_value, $disabled_text, $field_id );

		//city field
		$tabindex = GFCommon::get_tabindex();
		$city     = sprintf( "<span class='ginput_{$city_location}$class_suffix' id='" . $field_id . "_3_container'><input type='text' name='input_%d.3' id='%s_3' value='%s' $tabindex %s placeholder='" . apply_filters( "gform_address_city_{$form_id}", apply_filters( "gform_address_city", __( "City", "gravityforms" ), $form_id ), $form_id ) . "'/></span>", $id, $field_id, $city_value, $disabled_text, $field_id );

		//state field
		$style = ( IS_ADMIN && rgget( "hideState", $field ) ) ? "style='display:none;'" : "";
		if ( IS_ADMIN || ! rgget( "hideState", $field ) ) {
			$state_field = GFCommon::get_state_field( $field, $id, $field_id, $state_value, $disabled_text, $form_id );
			$state       = sprintf( "<span class='ginput_{$state_location}$class_suffix' id='" . $field_id . "_4_container' $style>$state_field<label for='%s_4' id='" . $field_id . "_4_label'>" . apply_filters( "gform_address_state_{$form_id}", apply_filters( "gform_address_state", $state_label, $form_id ), $form_id ) . "</label></span>", $field_id );
		}
		else {
			$state = sprintf( "<input type='hidden' class='gform_hidden' name='input_%d.4' id='%s_4' value='%s'/>", $id, $field_id, $state_value );
		}
	}
	else {

		//city field
		$tabindex = GFCommon::get_tabindex();
		$city     = sprintf( "<span class='ginput_{$city_location}$class_suffix' id='" . $field_id . "_3_container'><input type='text' name='input_%d.3' id='%s_3' value='%s' $tabindex %s placeholder='" . apply_filters( "gform_address_city_{$form_id}", apply_filters( "gform_address_city", __( "City", "gravityforms" ), $form_id ), $form_id ) . "'/></span>", $id, $field_id, $city_value, $disabled_text, $field_id );

		//state field
		$style = ( IS_ADMIN && rgget( "hideState", $field ) ) ? "style='display:none;'" : "";
		if ( IS_ADMIN || ! rgget( "hideState", $field ) ) {
			$state_field = flair_gform_get_state_field( $field, $id, $field_id, $state_value, $disabled_text, $form_id, $state_label );

			$state = sprintf( "<span class='ginput_{$state_location}$class_suffix' id='" . $field_id . "_4_container' $style>$state_field</span>", $field_id );
		}
		else {
			$state = sprintf( "<input type='hidden' class='gform_hidden' name='input_%d.4' id='%s_4' value='%s'/>", $id, $field_id, $state_value );
		}

		//zip field
		$tabindex = GFCommon::get_tabindex();
		$zip      = sprintf( "<span class='ginput_{$zip_location}$class_suffix' id='" . $field_id . "_5_container'><input type='text' name='input_%d.5' id='%s_5' value='%s' $tabindex %s placeholder='" . apply_filters( "gform_address_zip_{$form_id}", apply_filters( "gform_address_zip", $zip_label, $form_id ), $form_id ) . "'/></span>", $id, $field_id, $zip_value, $disabled_text, $field_id );

	}

	if ( IS_ADMIN || ! $hide_country ) {
		$style    = $hide_country ? "style='display:none;'" : "";
		$tabindex = GFCommon::get_tabindex();
		$country  = sprintf( "<span class='ginput_{$country_location}$class_suffix' id='" . $field_id . "_6_container' $style><label for='%s_6' id='" . $field_id . "_6_label'>" . apply_filters( "gform_address_country_{$form_id}", apply_filters( "gform_address_country", __( "Country", "gravityforms" ), $form_id ), $form_id ) . "</label><select name='input_%d.6' id='%s_6' $tabindex %s>%s</select></span>", $id, $field_id, $disabled_text, $country_list, $field_id );
	}
	else {
		$country = sprintf( "<input type='hidden' class='gform_hidden' name='input_%d.6' id='%s_6' value='%s'/>", $id, $field_id, $country_value );
	}

	//Wrap city in foundation divs
	$city = "<div class='" . apply_filters( 'flair_gforms_address_city_class', 'large-5 columns', $field, $form_id ) . "'>{$city}</div>";

	//Wrap state in foundation divs
	$state = "<div class='" . apply_filters( 'flair_gforms_address_state_class', 'large-4 columns', $field, $form_id ) . "'>{$state}</div>";

	//Wrap ZIP in foundation divs
	$zip = "<div class='" . apply_filters( 'flair_gforms_address_zip_class', 'large-3 columns', $field, $form_id ) . "'>{$zip}</div>";

	$inputs = $address_display_format == "zip_before_city" ? $street_address . $street_address2 . $div_row . $zip . $city . $state . "</div>" . $country : $street_address . $street_address2 . $div_row . $city . $state . $zip . "</div>" . $country;

	return "<div class='ginput_complex$class_suffix ginput_container' id='$field_id'>" . $inputs . "</div>";

}

function flair_gform_get_state_field( $field, $id, $field_id, $state_value, $disabled_text, $form_id, $state_label ) {

	$state_dropdown_class = $state_text_class = $state_style = $text_style = $state_field_id = "";

	if ( empty( $state_value ) ) {
		$state_value = rgget( "defaultState", $field );

		//for backwards compatibility (canadian address type used to store the default state into the defaultProvince property)
		if ( rgget( "addressType", $field ) == "canadian" && ! rgempty( "defaultProvince", $field ) ) {
			$state_value = $field["defaultProvince"];
		}
	}

	$address_type        = rgempty( "addressType", $field ) ? "international" : $field["addressType"];
	$address_types       = GFCommon::get_address_types( $form_id );
	$has_state_drop_down = isset( $address_types[$address_type]["states"] ) && is_array( $address_types[$address_type]["states"] );

	if ( IS_ADMIN && RG_CURRENT_VIEW != "entry" ) {
		$state_dropdown_class = "class='state_dropdown'";
		$state_text_class     = "class='state_text'";
		$state_style          = ! $has_state_drop_down ? "style='display:none;'" : "";
		$text_style           = $has_state_drop_down ? "style='display:none;'" : "";
		$state_field_id       = "";
	}
	else {
		//id only displayed on front end
		$state_field_id = "id='" . $field_id . "_4'";
	}

	$tabindex       = GFCommon::get_tabindex();
	$states         = empty( $address_types[$address_type]["states"] ) ? array() : $address_types[$address_type]["states"];
	$state_dropdown = sprintf( "<select name='input_%d.4' %s $tabindex %s $state_dropdown_class $state_style>%s</select>", $id, $state_field_id, $disabled_text, GFCommon::get_state_dropdown( $states, $state_value ) );

	$tabindex   = GFCommon::get_tabindex();
	$state_text = sprintf( "<input type='text' name='input_%d.4' %s value='%s' $tabindex %s $state_text_class $text_style placeholder='" . apply_filters( "gform_address_state_{$form_id}", apply_filters( "gform_address_state", $state_label, $form_id ), $form_id ) . "'/>", $id, $state_field_id, $state_value, $disabled_text );

	if ( IS_ADMIN && RG_CURRENT_VIEW != "entry" ) {
		return $state_dropdown . $state_text;
	}
	else {
		if ( $has_state_drop_down ) {
			return $state_dropdown;
		}
		else {
			return $state_text;
		}
	}

}

function flair_gform_field_choices( $choices, $field ) {

	//Init vars
	$type    = 'radio';
	$choices = null;
	$disabled_text = ( IS_ADMIN && RG_CURRENT_VIEW != "entry" ) ? "disabled='disabled'" : "";

	if ( ! is_admin() ) {

		//Change html content for text input and address fields
		if ( ( $field['type'] === 'radio' ) || ( $field['type'] === 'checkbox' ) ) {

			if ( $field['type'] === 'checkbox' ) {
				$type = 'checkbox';
			}

			if ( is_array( $field["choices"] ) ) {
				$choice_id = 0;

				// add "other" choice to choices if enabled
				if ( rgar( $field, 'enableOtherChoice' ) ) {
					$other_default_value = GFCommon::get_other_choice_value();
				}

				//$logic_event = GFCommon::get_logic_event($field, "click");
				$count = 1;

				foreach ( $field["choices"] as $choice ) {
					$id = $field["id"] . '_' . $choice_id ++;

					$field_value = ! empty( $choice["value"] ) || rgar( $field, "enableChoiceValue" ) ? $choice["value"] : $choice["text"];
					$value = $field_value;

					if ( rgget( "enablePrice", $field ) ) {
						$field_value .= "|" . GFCommon::to_number( rgar( $choice, "price" ) );
					}

					if ( RG_CURRENT_VIEW != "entry" ) {
						$checked = rgar( $choice, "isSelected" ) ? "checked='checked'" : "";
					}
					else {
						$checked = RGFormsModel::choice_value_match( $field, $choice, $value ) ? "checked='checked'" : "";
					}

					$tabindex    = GFCommon::get_tabindex();
					$label       = sprintf( "%s</label>", $choice["text"] );
					$input_focus = '';

					// handle "other" choice
					if ( rgar( $choice, 'isOtherChoice' ) ) {

						$onfocus = ! IS_ADMIN ? 'jQuery(this).prev("input").attr("checked", true); if(jQuery(this).val() == "' . $other_default_value . '") { jQuery(this).val(""); }' : '';
						$onblur  = ! IS_ADMIN ? 'if(jQuery(this).val().replace(" ", "") == "") { jQuery(this).val("' . $other_default_value . '"); }' : '';

						$input_focus  = ! IS_ADMIN ? "onfocus=\"jQuery(this).next('input').focus();\"" : "";
						$value_exists = RGFormsModel::choices_value_match( $field, $field["choices"], $value );

						if ( $value == 'gf_other_choice' && rgpost( "input_{$field["id"]}_other" ) ) {
							$other_value = rgpost( "input_{$field["id"]}_other" );
						}
						else {
							if ( ! $value_exists && ! empty( $value ) ) {
								$other_value = $value;
								$value       = 'gf_other_choice';
								$checked     = "checked='checked'";
							}
							else {
								$other_value = $other_default_value;
							}
						}
						$label = "<input name='input_{$field["id"]}_other' type='text' value='" . esc_attr( $other_value ) . "' onfocus='$onfocus' onblur='$onblur' $tabindex $disabled_text />";
					}

					$choices .= sprintf( "<li class='gchoice_$id'><label for='choice_%s'><input name='input_%d' type='{$type}' value='%s' %s id='choice_%s' $tabindex %s />&nbsp;%s</li>", $field["id"], $field["id"], esc_attr( $field_value ), $checked, $id, $input_focus, $label );

					if ( IS_ADMIN && RG_CURRENT_VIEW != "entry" && $count >= 5 ) {
						break;
					}

					$count ++;
				}

				$total = sizeof( $field["choices"] );
				if ( $count < $total ) {
					$choices .= "<li class='gchoice_total'>" . sprintf( __( "%d of %d items shown. Edit field to view all", "gravityforms" ), $count, $total ) . "</li>";
				}
			}

		}

	}

	return $choices;
}

add_action( 'gform_field_css_class', 'flair_foundation_custom_class', 10, 3);

function flair_foundation_custom_class( $classes, $field, $form ){

    if ( $field["type"] == "text" || $field["type"] == "email" || $field["type"] == "select" ){
        $classes .= " large-6 columns";
    }
	if ( $field["type"] == "name" ){
        $classes .= " name";
    }
	if ( $field["type"] == "textarea" ){
        $classes .= " large-12 columns";
    }
    return $classes;
}

function flair_change_first_name( $label, $form_id ){
    return "First Name";
}
add_filter( 'gform_name_first', 'flair_change_first_name', 10, 2 );

function flair_change_last_name( $label, $form_id ){
    return "Last Name";
}
add_filter( 'gform_name_last', 'flair_change_last_name', 10, 2 );

add_filter( 'gform_confirmation_anchor', create_function( '', 'return true;' ) );

function flair_flex_video( $html, $url, $attr ) {

	// Only run this process for embeds that don't required fixed dimensions
	$resize             = false;
	/* The list of providers is in wp-includes/class-oembed.php */
	$accepted_providers = array(
		'youtube.com',
		'blip.tv',
		'vimeo.com',
		'dailymotion.com',
		'flickr.com',
		'smugmug.com',
		'hulu.com',
		'viddler.com',
		'qik.com',
		'revision3.com',
		'photobucket.com',
		'scribd.com',
		'wordpress.tv',
		'polldaddy.com',
		'funnyordie.com',
		'soundcloud.com',
		'slideshare.net',
		'instagram.com'
	);

	// Check each provider
	foreach ( $accepted_providers as $provider ) {
		if ( strstr( $url, $provider ) ) {
			$resize = true;
			break;
		}
	}

	if ( $resize === true ) {

	// Remove width and height attributes
	$attr_pattern       = '/(width|height)="[0-9]*"/i';
	$whitespace_pattern = '/\s+/';
	$embed              = preg_replace( $attr_pattern, "", $html );
	$embed              = preg_replace( $whitespace_pattern, ' ', $embed ); // Clean-up whitespace
	$embed              = trim( $embed );

	// Add container around the video
	$html = '<div class="flex-video">';
	$html .= $embed;
	$html .= '</div>';

	}

	return $html;
}

add_filter( 'embed_oembed_html', 'flair_flex_video', 99, 3 );


/**
 * Filter the Category links that WordPress generate so that the post counter is inside the link
 *
 * @param $links
 *
 * @return mixed
 */
function flair_cat_count_span( $links ) {
  $links = str_replace( '</a> (', ' (', $links );
  $links = str_replace( ')', ')</a>', $links );
  return $links;
}
add_filter( 'wp_list_categories', 'flair_cat_count_span' );

/**
 * Filter the Archive links that WordPress generate so that the post counter is inside the link
 *
 * @param $links
 *
 * @return mixed
 */
function flair_archive_count_span( $links ) {
  $links = str_replace( '</a>&nbsp;(', ' (', $links );
  $links = str_replace( ')', ')</a>', $links );
  return $links;
}
add_filter( 'get_archives_link', 'flair_archive_count_span' );

/**
 * If WP-PageNavi http://wordpress.org/plugins/wp-pagenavi/ is installed to handle pagination then let's turn it into Foundation 5 pagination http://foundation.zurb.com/docs/components/pagination.html
 *
 * @param $wp_pagenavi_output
 *
 * @return mixed
 */
function flair_pagination( $wp_pagenavi_output ) {

	// Change the wrapping div to a ul
	$wp_pagenavi_output = str_replace( "<div class='wp-pagenavi'>", "<ul class='pagination'>", $wp_pagenavi_output );
	$wp_pagenavi_output = str_replace( "</div>", "</ul>", $wp_pagenavi_output );

	// Change the spans to li's
	$wp_pagenavi_output = str_replace( "<span", "<li", $wp_pagenavi_output );
	$wp_pagenavi_output = str_replace( "</span>", "</li>", $wp_pagenavi_output );

	// Wrap a's in li's
	$wp_pagenavi_output = str_replace( "<a", "<li><a", $wp_pagenavi_output );
	$wp_pagenavi_output = str_replace( "</a>", "</a></li>", $wp_pagenavi_output );

	// Wrap the current li in an a
	$wp_pagenavi_output = preg_replace( "#<li class='current'>(.+)<\/li>#", "<li class='current'><a href=''>$1</a></li>", $wp_pagenavi_output );

	// Add a filter in case we want to wrap the pagination in a <div class="pagination-centered"> wrapper
	$wp_pagenavi_output = apply_filters( 'flair_pagination', $wp_pagenavi_output );

	return $wp_pagenavi_output;
}
add_filter( 'wp_pagenavi', 'flair_pagination' );