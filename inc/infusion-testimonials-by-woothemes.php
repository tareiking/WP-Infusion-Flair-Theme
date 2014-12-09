<?php
/**
 * Testimonials by WooThemes Support for Infusion Theme
 * 
 * @uses Google Sintony Font : SIL License : http://www.google.com/fonts#UsePlace:use/Collection:Sintony
 */

if ( ! class_exists( 'Woothemes_Testimonials' ) ) {
	return;
}

function woo_custom_load_testimonials_rotation () {

	wp_enqueue_script( 'infusion-testimonials', get_template_directory_uri() . '/assets/js/infusion-testimonials.js', array( 'pico-flexslider' ), '1.0', true );

 
} // End woo_custom_load_testimonials_rotation()
add_action( 'wp_enqueue_scripts', 'woo_custom_load_testimonials_rotation' );

/**
 * Testimonials by WooThemes
 */
add_filter( 'woothemes_testimonials_args', 'infusion_woothemes_testimonials_init' );

function infusion_woothemes_testimonials_init( $defaults ){

	$custom = __return_empty_array();

	if ( is_home() || is_front_page() || is_page_template( 'templates/full-width' ) ) {

		$custom = 
		array(
			'before' 			=> '</div></div><div class="widget widget_woothemes_testimonials">',
			'after' 			=> '</div><div class="row"><div class="small-12 medium-12 columns">',
		);

	}

	return wp_parse_args( $custom, $defaults );
}

function infusion_enqueue_sintony_font(){
	wp_enqueue_style( 'infusion-sintony-font', get_template_directory_uri() . '/assets/extras/sintony-font.css' );
}
add_filter( 'wp_enqueue_scripts', 'infusion_enqueue_sintony_font' );