<?php
/**
 * Testimonials by WooThemes Support for Infusion Theme
 */

if ( ! class_exists( 'Woothemes_Testimonials' ) ) {
	return;
}

function woo_custom_load_testimonials_rotation () {

	wp_enqueue_script( 'infusion-testimonials', get_template_directory_uri() . '/assets/js/infusion-testimonials.js', array( 'pico-flexslider' ), '1.0', true );

 
} // End woo_custom_load_testimonials_rotation()
add_action( 'wp_enqueue_scripts', 'woo_custom_load_testimonials_rotation' );