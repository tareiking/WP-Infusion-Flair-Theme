
(function($) {
	$(document).foundation();
	var doc = document.documentElement;
	doc.setAttribute('data-useragent', navigator.userAgent);

	// Flexslider loads after foundation init's, so lets reshuffle
	if ( $( '.flexslider' ).length > 0 ) {
		$(window).load(function(){
			$(document).foundation({
				equalizer : {
					equalize_on_stack: true
				}
			});
		});
	};
})(jQuery);