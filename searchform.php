<?php
 /**
 * Search Form Template
 *
 * @since Infusion 1.0
 */
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ) ; ?>">
	<section class="search-input row collapse">
		<label for="s" class="screen-reader-text"><?php _e( 'Search', 'bambino' ); ?></label>
		<div class="small-10 columns">
			<input type="text" class="field" name="s" id="s" placeholder="<?php esc_attr_e( 'Search', 'bambino' ); ?>" />
		</div>
		<button type="submit" class="button submit small-2 columns" name="submit" id="searchsubmit"></button>
	</section>
</form>