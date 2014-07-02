<?php
/**
 * A class responsible for rendering all custom template areas
 * temporarily, while we figure out where else to put them.
 */

if ( ! class_exists( 'Infusion_Template_Tags' ) ){

	class Infusion_Template_Tags
	{
		private static $instance;

		static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new Infusion_Unslider;
			}

			return self::$instance;
		}

		public function __construct() {
			add_action( 'init', array( $this, 'init' ) );
			// add_action( 'init', array( $this, 'slider_rewrite_flush' ) );
			// add_action( 'wp_enqueue_scripts', array( $this, 'slider_scripts' ) );
		}

		/**
		 * Init
		 */
		function init(){
			add_image_size( 'featured_folio_thumbnail', 265, 166, true );
		}

		/**
		 * Render Portfolio Feature
		 *
		 * Renders a blockgrid of 4 items with picture, avatar, header and text
		 */
		public static function render_featured_folio( $args = array() ){
			$defaults = array (
				'limit' => 0,
			);
			wp_parse_args( $args, $defaults );	?>

			<div class="row has-top-triangle featured-folio">

			<?php
			// Working with dummy data

			$i = 1;
			while ( $i <= 4 ) { ?>
				<div class="small-12 medium-3 columns featured-folio-item">

					<div class="folio-image">
						<img class="small-12" src="<?php echo get_template_directory_uri() . '/assets/img/c02.jpg' ?>" alt="">
					</div>

					<div class="folio-avatar"></div>

					<div class="folio-content">
						<h3>Lorem ipsum dolor sit amet consectetur</h3>
						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris mattis suscipit est, ut imperdiet tortor. Proin sed molestie massa.</p>
					</div>


				</div>

			<?php $i++; } ?>

			</div><!-- #featured-portfolio -->

			<?php
		}
	}
}
Infusion_Template_Tags::get_instance();
