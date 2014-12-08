<?php
/**
 * Infusion Theme Featured Posts Widget
 */

class Infusion_Featured_Posts_Widget extends WP_Widget {

	protected $widget_slug = 'infusion-featured-posts';

	public function __construct() {

		add_action( 'init', array( $this, 'infusion-featured-posts' ) );

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		parent::__construct(
			$this->get_widget_slug(),
			__( 'Infusion Featured Posts', $this->get_widget_slug() ),
			array(
				'classname'  => $this->get_widget_slug().'-class',
				'description' => __( 'Show Sticky Posts in Beautiful Masonry image grid / wall.', $this->get_widget_slug() )
			)
		);

		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) ); // todo
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) ); // todo

		add_image_size( 'masonry-thumb', 320, 320, true );

	} // end constructor


    /**
     * Return the widget slug.
     *
     * @since    1.0.0
     *
     * @return    Plugin slug variable.
     */
    public function get_widget_slug() {
        return $this->widget_slug;
    }

	/*--------------------------------------------------*/
	/* Widget API Functions
	/*--------------------------------------------------*/

	/**
	 * Outputs the content of the widget.
	 *
	 * @param array args  The array of form elements
	 * @param array instance The current instance of the widget
	 */
	public function widget( $args, $instance ) {

		if ( ! isset ( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		extract( $args, EXTR_SKIP );

		$widget_string = $before_widget;

		// The Widget!
		$r = new WP_Query( apply_filters( 'infusion_recent_posts_args', array(
			'posts_per_page'      => 12,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true
		) ) );

		if ($r->have_posts()) : ?>

		<!-- @TODO: Must declare support if using foundation columns, helps go full screen -->
		</div></div>

		<!-- @TODO: If Widget Header specified -->
		<div class="latest-articles">
			<div class="row">
				<div class="small-12">
					<h3><?php _e( 'Latest Articles') ?></h3>
				</div>
			</div>
		</div>
		<!-- @TODO: If Widget Header specified -->

		<!-- Masonry Widget Render starts here... -->
		<div id="masonry-loop">

		<?php while ( $r->have_posts() ) : $r->the_post(); ?>

				<article class="masonry-entry" id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
					<div class="masonry-thumbnail">
						<?php // if ( has_post_thumbnail() ) { ?>
							<?php //the_post_thumbnail('masonry-thumb'); ?>
						<?php //} else { ?>
							<img class="masonry-thumb" src="<?php echo get_template_directory_uri() . '/assets/img/0'. rand(1, 9) . '.jpg' ?>" alt="">
						<?php // } ?>
					</div><!--.masonry-thumbnail-->

					<div class="masonry-details">
						<h5 class="masonry-post-title"><a href="<?php the_permalink(' ') ?>" title="<?php the_title(); ?>"><span> <?php the_title(); ?></span></a></h5>
						<span class="masonry-post-date">
							<a href="<?php the_permalink(' ') ?>" title="<?php the_title(); ?>"><?php echo get_the_date( 'F j' ); ?></a>
						</span>
						<!-- <div class="masonry-post-excerpt"> -->
							<!-- <?php the_excerpt(); ?> -->
						<!-- </div>.masonry-post-excerpt -->
					</div><!--/.masonry-entry-details -->  
				</article><!--/.masonry-entry-->
			<?php endwhile; ?>

		<?php endif; ?>

		</div><!--/#masonry-loop-->

		<!-- @TODO: Must declare support if using foundation columns, helps go full screen -->
		<div class="row">
			<div class="small-12 columns">

		<?php

		$widget_string .= $after_widget;

		$cache[ $args['widget_id'] ] = $widget_string;

		wp_cache_set( $this->get_widget_slug(), $cache, 'widget' );

		print $widget_string;

	} // end widget

	/**
	 * Processes the widget's options to be saved.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		// TODO: Here is where you update your widget's old values with the new, incoming values

		return $instance;

	} // end widget

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {

		// TODO: Define default values for your variables
		$instance = wp_parse_args(
			(array) $instance
		);

		// TODO: Store the values of the widget in their own variable

		// Display the admin form
		include( plugin_dir_path(__FILE__) . 'views/admin.php' );

	} // end form

	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/

	/**
	 * Registers and enqueues widget-specific styles.
	 */
	public function register_widget_styles() {

		wp_enqueue_style( $this->get_widget_slug().'-widget-styles', plugins_url( 'css/widget.css', __FILE__ ) );

	} // end register_widget_styles

	/**
	 * Registers and enqueues widget-specific scripts.
	 */
	public function register_widget_scripts() {

		wp_enqueue_script( $this->get_widget_slug().'-script', plugins_url( 'js/widget.js', __FILE__ ), array('jquery') );
		wp_enqueue_script( 'masonry' );

	} // end register_widget_scripts

} // end class

// TODO: Remember to change 'Infusion_Masonry_Posts' to match the class name definition
add_action( 'widgets_init', create_function( '', 'register_widget("Infusion_Masonry_Posts");' ) );
