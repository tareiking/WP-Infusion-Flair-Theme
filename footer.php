<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the body tag and all content after
 *
 * @package Flair Theme
 */
?>
<!-- Footer -->

<footer class="footer">
	<section class="row collapse">
		<div class="large-12 columns footer-content">

			<?php if ( ! dynamic_sidebar( 'footer-widget-area' ) ) : ?>

			<?php endif; // end footer widget area ?>

		</div>
	</section>
</footer>
	<?php wp_footer(); ?>
</body>
</html>