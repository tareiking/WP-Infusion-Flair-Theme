<?php
/**
 * Infusion Recent Posts Widget
 * 
 * This widget is used to replace the default Reccent Posts Widget bundled in WordPress. It initiates a mo
 *
 * @package   Infusion Recent Posts Widget
 * @author    Tarei King
 * @license   GPL-2.0+
 * @link      http://tarei.me
 * @copyright 2014 Tarei King
 *
 */

if ( ! class_exists( 'Infusion_Recent_Posts_Widget' ) ) {

	class Infusion_Recent_Posts_Widget extends WP_Widget {

		protected $widget_slug = 'infusion-recent-posts-widget';

		public function __construct() {

			parent::__construct(
				$this->get_widget_slug(),
				__( 'Infusion Recent Posts', $this->get_widget_slug() ),
				array(
					'classname'  => $this->get_widget_slug(),
					'description' => __( 'Displays your recent posts with a featured image and an avatar in the center.', $this->get_widget_slug() )
				)
			);

		}

		/**
		 * Return the widget slug.
		 */
		public function get_widget_slug() {
		    return $this->widget_slug;
		}

		/**
		 * Outputs the content of the widget.
		 */
		public function widget( $args, $instance ) {

			$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
			if ( ! $number )
				$number = 4;

			/**
			 * Filter the arguments for the Recent Posts widget.
			 */
			$r = new WP_Query( apply_filters( 'widget_posts_args', array(
				'posts_per_page'      => $number,
				'no_found_rows'       => true,
				'post_status'         => 'publish',
				'ignore_sticky_posts' => true
			) ) );

			// Filter for Opening Markup
			$opentag = apply_filters( 'infusion_recent_posts_f5_opening_tag' );
			echo $opentag['markup'];

			if ($r->have_posts()) :

				echo $before_widget;

				$title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base );

				if ( '' != $title ) : ?>
				<div class="featured-folio-header full-width-header">
					<div class="row">
						<div class="small-12">
							<h3><?php _e( $title ); ?></h3>
						</div>
					</div>
				</div>
				<?php endif; ?>

				<div class="row featured-folio" data-equalizer>

					<?php while ( $r->have_posts() ) : $r->the_post(); ?>

					<div class="small-12 medium-6 large-3 columns featured-folio-item" data-equalizer-watch>
						<a href="<?php echo the_permalink(); ?>">
						<div class="carousel-overlay"></div>

						<div class="folio-image">
							<?php if ( has_post_thumbnail() ) {
								the_post_thumbnail( 'featured_folio_thumbnail', array( 'class' => ' small-12' ) );
							} else { ?>
								<img class="small-12" src="<?php echo get_template_directory_uri() . '/assets/img/c02.jpg' ?>" alt="">
							<?php } ?>
						</div>

						<div class="folio-avatar">
							<?php echo get_avatar( '60' ); ?>
						</div>

						<div class="folio-content">
							<h3>
								<a href="<?php the_permalink(); ?>"><?php get_the_title() ? the_title() : the_ID(); ?></a>
							</h3>
							<p><?php the_excerpt(); ?></p>
						</div>

						</a>

					</div>

					<!-- Old -->
					<?php endwhile; ?>

				</div><!-- #featured-portfolio -->

			<?php

			// Filter for Closing Markup
			$closetag = apply_filters( 'infusion_recent_posts_f5_closing_tag' );
			echo $closetag['markup'];

			// Reset the global $the_post as this query will have stomped on it
			wp_reset_postdata();

			endif;

		} // End widget

		/**
		 * Processes the widget's options to be saved.
		 */
		public function update( $new_instance, $old_instance ) {

			$instance = $old_instance;

			$instance['title']  = strip_tags( $new_instance['title'] );
			$instance['number'] = (int) $new_instance['number'];

			return $instance;

		}

		/**
		 * Generates the administration form for the widget.
		 */
		public function form( $instance ) {

			$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
			$title = strip_tags( $instance['title'] );
			$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 4; ?>

			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
				<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" />
			</p>

			<?php
		}

	}

}

/**
 * Initialize our Replacement Recent Posts Widget
 *
 * @todo : if people want to use the original WordPress widget, make affordances for them.
 */
function infusion_init_recent_posts_widget(){
	unregister_widget( 'WP_Widget_Recent_Posts' );
	register_widget( 'Infusion_Recent_Posts_Widget' );
}

add_action( 'widgets_init', 'infusion_init_recent_posts_widget' );