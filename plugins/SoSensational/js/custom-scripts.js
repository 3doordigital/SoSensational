/*
 * Custom scripts for SoSensational Wordpress plugin
 * 
 * @author Lukasz Tarasiewicz <lukasz.tarasiewicz@polcode.net>
 * 
 * 
 */

/*
 * A plugin that limits the number of characters a user can insert
 * 
 * @source http://www.scriptiny.com/2012/09/jquery-input-textarea-limiter/
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
    
    /**
     * Call the "Limiter" plugin function      
     * @source http://www.scriptiny.com/2012/09/jquery-input-textarea-limiter/
     */   
    var elem = $('#charNum');
    var input = $('#advertiser_co_desc'); 
    
    /* Make sure that the element is present on the page in order to attach the function call */
    if (input.length) {
        input.limiter(180, elem);        
    }    
    
    /**
     * Limit the number of tags a user can input to 5 items     
     * @source http://timschlechter.github.io/bootstrap-tagsinput/examples/
     */
    var tagsInputField = $("#post_tags");
    var tagsLimit = 5;
    
    /* Declare tagsInputField as input for tags */
    tagsInputField.tagsinput('items');    
        
    $(".bootstrap-tagsinput").tagsinput({
       maxTags: tagsLimit,
       trimValue: true
    });
    
    
    var tagsLeftTmp = tagsLimit;
    var tagsCounter = $("#tags-counter");
    tagsCounter.html(tagsLeftTmp + ' of ' + tagsLimit + ' tags left'); 
    
    $(".bootstrap-tagsinput").on('itemAdded', function(e) {       
       tagsLeftTmp = tagsLeftTmp - 1;
       tagsCounter.html(tagsLeftTmp + ' of ' + tagsLimit + ' tags left'); 
       return tagsLeftTmp;
    });
    
    $(".bootstrap-tagsinput").on('itemRemoved', function(e) {       
       tagsLeftTmp = tagsLeftTmp + 1;
       tagsCounter.html(tagsLeftTmp + ' of ' + tagsLimit + ' tags left'); 
       return tagsLeftTmp;
    });    
     
    
});

