(function($) {
    "use strict";
    $(function() {
        var container = document.querySelector('#masonry-loop');
        //create empty var msnry
        var msnry;
        if ( container ) {
            // initialize Masonry after all images have loaded
            imagesLoaded(container, function() {
            msnry = new Masonry(container, {
                itemSelector: '.masonry-entry',
            });
            });
        };
    });
}(jQuery));