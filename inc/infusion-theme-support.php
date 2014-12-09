<?php
/**
 * Add Support for various scripts used in Infusion
 *
 * @subpackage built on ./foundation-theme-support.php
 */

function infusion_check_theme_support() {
}

add_action( 'init', 'infusion_check_theme_support' );

/**
 * Page Builder Support
 */
function infusion_remove_siteorigin_widgets(){
	unregister_widget('SiteOrigin_Panels_Widgets_Gallery');
	unregister_widget('SiteOrigin_Panels_Widgets_PostContent');
	unregister_widget('SiteOrigin_Panels_Widgets_Image');
	unregister_widget('SiteOrigin_Panels_Widgets_PostLoop');
	unregister_widget('SiteOrigin_Panels_Widgets_EmbeddedVideo');
	unregister_widget('SiteOrigin_Panels_Widgets_Video');
	unregister_widget('SiteOrigin_Panels_Widget_Price_Box');
}

/**
 * Remove PageBuilder Widgets
 */
if ( function_exists( 'siteorigin_panels_init' ) ) {
	remove_action('widgets_init', 'origin_widgets_init');
	add_action('widgets_init', 'infusion_remove_siteorigin_widgets');
}
