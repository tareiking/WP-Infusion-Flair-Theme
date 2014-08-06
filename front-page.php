<?php
/**
 * The template for displaying Home Page
 *
 * @uses Infusion_Unslider
 *
 * @todo How do we inject the widget areas?
 *
 * @package Flair Theme
 */

get_header(); ?>


	<?php if ( class_exists( 'Pico_Slider' ) ) {
		$slider = Pico_Slider::get_instance();
		$slider->do_slider( '' );
	} ?>

	<!-- Recent Posts Tempoary Widget Area -->
	<div class="row">
		<div class="small-12 medium-12 columns">

			<?php if ( ! dynamic_sidebar( 'homepage-widgets-area' ) ) : ?>

			<?php endif; // end homepage widgets ?>

		</div>
	</div>

	<div class="row">

		<!-- Main Blog Content -->
		<div class="large-12 columns content" role="content">

			<?php while ( have_posts() ) : the_post(); ?>

				<div class="entry-content">
					<?php the_content(); ?>
					<?php
						wp_link_pages( array(
							'before' => '<div class="page-links">' . __( 'Pages:', 'flair' ),
							'after'  => '</div>',
						) );
					?>
				</div><!-- .entry-content -->

			<?php endwhile; // end of the loop. ?>

		</div>
		<!-- End Main Content -->
	</div>

<?php get_footer(); ?>
