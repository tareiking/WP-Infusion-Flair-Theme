<?php
/**
 * Template Name: TEST: Recent Posts Template
 *
 * The template for displaying the Sidebar on the left hand side of the page
 *
 * @package Flair Theme
 */

get_header(); ?>

	<div class="row recent-posts">
		<div class="small-12 medium-12 columns">

			<?php if ( ! dynamic_sidebar( 'test-recent-posts-widget-area' ) ) : ?>

			<?php endif; // end sidebar widget area ?>

		</div>
	</div>

	<div class="row">

		<!-- Main Blog Content -->
		<div class="medium-12 large-12 columns content" role="content">

			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'parts/content', 'page' ); ?>

				<?php
					// If comments are open or we have at least one comment, load up the comment template
					if ( comments_open() || '0' != get_comments_number() ) {
						comments_template();
					}
				?>

			<?php endwhile; // end of the loop. ?>

	</div>
		<!-- End Main Content -->

<?php get_footer(); ?>
