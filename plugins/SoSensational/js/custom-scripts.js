/*
 * Custom scripts for SoSensational Wordpress plugin
 * 
 * @author Lukasz Tarasiewicz <lukasz.tarasiewicz@polcode.net>
 * @date March 2015
 * 
 */

/*------------------------------------------------------------------------------
 A plugin that limits the number of characters a user can insert
 Source: http://www.scriptiny.com/2012/09/jquery-input-textarea-limiter/
 -----------------------------------------------------------------------------*/ 
                
(function($) {
    $.fn.extend({
        limiter: function(limit, elem) {
            $(this).on("keyup focus", function() {
                setCount(this, elem);
                //$.each(elem, setCount.bind(this));
            });
            function setCount(src, elem) {
                var chars = src.value.length;
                if (chars >= limit) {
                    src.value = src.value.substr(0, limit);
                    chars = limit;
                    elem.css('color', '#FF0000');
                } else {
                    elem.css('color', '#555');
                }
                elem.html( limit - chars + ' of ' + limit +' Characters Left' );
            }
            setCount($(this)[0], elem);
        }
    });
})(jQuery);

jQuery(document).ready(function($) {
    
    /*--------------------------------------------------------------------------
      Call the "Limiter" plugin function      
      Source: http://www.scriptiny.com/2012/09/jquery-input-textarea-limiter/
     -------------------------------------------------------------------------*/  
    /* ------------------ One line description limiter -----------------------*/
    var oneLineCounter = $('#oneLineDescCounter');
    var oneLineDescription = $('#advertiser_co_desc'); 
    /* Make sure that the element is present on the page in order to attach the 
       function call */
    if (oneLineDescription.length) {
        oneLineDescription.limiter(300, oneLineCounter);        
    }    
    
    /* ------------------------ Full description -----------------------------*/
    var fullDescriptionCounter = $('#fullDescCounter');
    var fullDescription = $('#advertiser_desc');
    
    if (fullDescription.length) {
        fullDescription.limiter(1000, fullDescriptionCounter);
    }
    
    /* --------------------- Category description ----------------------------*/

    var categoryDescription = $('.category-description');
    if (categoryDescription.length) {
        /* Loop over each element on the page and attach the plugin function */
        $.each(categoryDescription, function(index, value) {
            var counter = $('.catDescriptionCounter-' + index);
            $(this).limiter(180, counter);
        });
    }
    
    /*--------------------------------------------------------------------------
      Limit the number of tags a user can input to 5 items     
      Source: http://timschlechter.github.io/bootstrap-tagsinput/examples/
     -------------------------------------------------------------------------*/
    var tagsInputField = $("#post_tags");
    var tagsLimit = 5;
           
    tagsInputField.tagsinput({
       maxTags: tagsLimit,
       trimValue: true
    });
    
    /*-------------------------------------------------------------------------- 
       Make the single input placeholder wider so that text does not slide under
       the previous tag 
     -------------------------------------------------------------------------*/
    $('.bootstrap-tagsinput input').attr('style', 'width: 10em !important');
    
    /*--------------------------------------------------------------------------
      Display a counter to indicate how meny tags are left to enter
     -------------------------------------------------------------------------*/
    // Check if a user is on the single product page
    if (tagsInputField.length) {
        var tagsLeftTmp = tagsLimit - tagsInputField.tagsinput('items').length;
        var tagsCounter = $("#tags-counter");
        tagsCounter.html(tagsLeftTmp + ' of ' + tagsLimit + ' tags left'); 

        tagsInputField .on('itemAdded', function(e) {       
           tagsLeftTmp = tagsLeftTmp - 1;
           tagsCounter.html(tagsLeftTmp + ' of ' + tagsLimit + ' tags left'); 
           return tagsLeftTmp;
        });

        tagsInputField .on('itemRemoved', function(e) {       
           tagsLeftTmp = tagsLeftTmp + 1;
           tagsCounter.html(tagsLeftTmp + ' of ' + tagsLimit + ' tags left'); 
           return tagsLeftTmp;
        });    
    }
    /*--------------------------------------------------------------------------
      Ajax call for deleting products from /view-products/
     -------------------------------------------------------------------------*/
        
    $('.ajax-delete').on('click', function(e) {
        e.preventDefault();
        var productId = $(this).attr('data');
        var sampleData = {
            'action': 'ss_delete_action',
            'productToDelete': productId
        };
        $.ajax({
            data: sampleData,
            type: 'POST',
            url: AjaxObject.ss_ajax_url,
            success: function(msg, statusText) {
                var pattern = /\?/;
                var location = window.location.href;
                var n = location.search(pattern);
                console.log(statusText);
                /*  Compose a query var based on whether query variables are 
                    already present or not */
                var queryVar = n === -1 ? '?adminmsg=d' : '&adminmsg=d';
                window.location.href = window.location.href + queryVar;
            }               
        });
    });  
    
    /*--------------------------------------------------------------------------
      Attaching a flexslider to 'related advertisers' module on the shop page.
     -------------------------------------------------------------------------*/
    
    $('.advertisers-carousel > .flexslider').flexslider({
        animation: 'slide',
        itemWidth: 365,
        itemMargin: 15,
        controlNav: false,
        prevText: "",
        nextText: "",
        animationLoop: true,
        slideshow: false,
        start: function() {
            $('.slides').show();
        }        
    });
    
    /*--------------------------------------------------------------------------
     Enable submit button in a form that changed on categories edit page.
     -------------------------------------------------------------------------*/     
          
    var categoryEditForms = $('form.category-edit-block');
    categoryEditForms.change(function(event) {
        var submitButton = $(this).find('button[type=submit]');
        submitButton.removeAttr('disabled');
    });

});
