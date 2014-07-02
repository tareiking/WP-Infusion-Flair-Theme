<?php
/**
 * The template for displaying Home Page
 *
 * @package Flair Theme
 */

get_header(); ?>


<?php if ( class_exists( 'Infusion_Unslider' ) ) {
	$slider = Infusion_Unslider::get_instance();
	$slider->do_slider();
} ?>

	<div class="row has-top-triangle">

		<!-- Main Blog Content -->
		<div class="large-9 columns content" role="content">

			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'parts/content', 'single' ); ?>

				<?php
					// If comments are open or we have at least one comment, load up the comment template
					if ( comments_open() || '0' != get_comments_number() ) {
						comments_template();
					}
				?>

			<?php endwhile; // end of the loop. ?>

	</div>
		<!-- End Main Content -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
