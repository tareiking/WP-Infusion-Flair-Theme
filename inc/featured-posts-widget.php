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

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number )
			$number = 12;

		$widget_string = $before_widget;

		// The Widget!
		$r = new WP_Query( apply_filters( 'infusion_recent_posts_args', array(
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'post__in'  => get_option( 'sticky_posts' ),
		) ) );

		if ($r->have_posts()) : 

			echo $before_widget;

			$title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base );

			echo apply_filters( 'infusion_featured_posts_opening_tag', __return_empty_string() ); ?>

		<?php if ( '' != $title ) { ?>
			<div class="latest-articles">
				<div class="row">
					<div class="small-12">
						<h3 class="widget-title"><?php _e( $title ); ?></h3>
					</div>
				</div>
			</div>
		<?php } ?>

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

		<?php

		echo apply_filters( 'infusion_featured_posts_closing_tag', __return_empty_string() );

		wp_reset_postdata();

		echo $after_widget;

	} // end widget

	/**
	 * Processes the widget's options to be saved.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title']  = strip_tags( $new_instance['title'] );
		$instance['number'] = (int) $new_instance['number'];

		return $instance;

	} // end widget

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );

		$title     = strip_tags( $instance['title'] );
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 4; ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of sticky posts to show:' ); ?></label><br>	
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" />
		</p>

		<p>
			<strong>Recommended</strong>: You should have at least 6 <a href="http://www.wpbeginner.com/beginners-guide/how-to-make-sticky-posts-in-wordpress/">Sticky Posts</a>. Otherwise rows will be partially empty.
		</p>

		<?php

		return $instance;

	} // end form

	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/

	/**
	 * Registers and enqueues widget-specific scripts.
	 */
	public function register_widget_scripts() {

		wp_enqueue_script( 'masonry' );
		wp_enqueue_script( 'infusion-featured-posts', get_template_directory_uri() . '/assets/js/featured-posts.js', array( 'jquery' ), '1.0', true );

	} // end register_widget_scripts

} // end class

/**
 * Initialize our Replacement Recent Posts Widget
 *
 * @todo : add filter to disable
 */
function infusion_init_featured_posts_widget(){
	register_widget( 'Infusion_Featured_Posts_Widget' );
}

add_action( 'widgets_init', 'infusion_init_featured_posts_widget' );
