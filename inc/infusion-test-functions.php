<?php
/**
 * Infusion Test Functions
 * A placeholder for test functions for Infusion widgets and plugins
 */

	register_sidebar( array(
		'name'          => __( 'Recent Posts Test Area', 'flair' ),
		'id'            => 'test-recent-posts-widget-area',
		'description'   => __( 'Displayed in TEST: Recent posts template pages', 'flair' ),
		'before_widget' => '<aside id="%1$s" class="widget test-recent-posts-widget-area %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h5 class="widget-title">',
		'after_title'   => '</h5>',
	) );