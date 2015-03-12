/*
 * Custom scripts for SoSensational Wordpress plugin
 * 
 * @author Lukasz Tarasiewicz <lukasz.tarasiewicz@polcode.net>
 * 
 * 
 */

/*
 * A plugin that limits the number of characters a user can insert
 */               
(function($) {
    $.fn.extend( {
        limiter: function(limit, elem) {
            $(this).on("keyup focus", function() {
                setCount(this, elem);
            });
            function setCount(src, elem) {
                var chars = src.value.length;
                if (chars > limit) {
                    src.value = src.value.substr(0, limit);
                    chars = limit;
                }
                elem.html( limit - chars + ' of ' + limit +' Characters Left' );
            }
            setCount($(this)[0], elem);
        }
    });
})(jQuery);

jQuery(document).ready(function($) {
    var elem = $('#charNum');
    $('#advertiser_co_desc').limiter(180, elem);
});