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

    (function (w,i,d,g,e,t,s) {w[d] = w[d]||[];t= i.createElement(g);
        t.async=1;t.src=e;s=i.getElementsByTagName(g)[0];s.parentNode.insertBefore(t, s);
    })(window, document, '_gscq','script','//widgets.getsitecontrol.com/21036/script.js');

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
    var tagsLimit = 6;
    var usedTags = 0;
           
    if (tagsInputField.length) {
        tagsInputField.tagsinput({
           maxTags: tagsLimit,
           trimValue: true
        });
        usedTags = tagsInputField.tagsinput('items').length;
    }
    
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
        var tagsCounter = $("#tags-counter");
        tagsCounter.html(usedTags + ' of ' + tagsLimit + ' tags'); 

        tagsInputField .on('beforeItemAdd', function(e) {       
           usedTags++;
           tagsCounter.html(usedTags + ' of ' + tagsLimit + ' tags');
           if(usedTags > tagsLimit){
               tagsLimit;
                e.cancel = true;
           }
        });

        tagsInputField .on('itemRemoved', function(e) {       
           usedTags--;
           tagsCounter.html(usedTags + ' of ' + tagsLimit + ' tags'); 
           return usedTags;
        });    
    }else{
        tagsLimit = 5;
    }
    /*--------------------------------------------------------------------------
      Ajax call for deleting products from /view-products/
     -------------------------------------------------------------------------*/
        
    $('.remove-product').on('click', function(e) {
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
                /*  Compose a query var based on whether query variables are 
                    already present or not */
                var queryVar = n === -1 ? '?adminmsg=d' : '&adminmsg=d';
                window.location.href = window.location.href + queryVar;
            }               
        });
    });  
     
    /*--------------------------------------------------------------------------
     Enable submit button in a form that changed on categories edit page.
     -------------------------------------------------------------------------*/     
          
    function removeDisabled(currentForm) {
        var submitButton = $(currentForm).find('button[type=submit]');
        submitButton.removeAttr('disabled');
    }
    
    var categoryEditForms = $('form.category-edit-block');
    categoryEditForms.on('change keyup', function(event) {
          var currentForm = this;
          removeDisabled(currentForm);
    });
    
    /*--------------------------------------------------------------------------
     Fix breadcrumbs on category listings
     -------------------------------------------------------------------------*/
    var str = $('#breadcrumbs span > a');
    var urls = [];
    var mainCategory;

     str.each(function(index) {
         var url = $(this).attr('href');
         urls.push(url);      
         if (urls.length === 3) {
             var slug = urls[2].slice(7);            
             $(this).attr('href', urls[1] + slug);  
             mainCategory = $(this).attr('href');
         }         
         if (urls.length === 4) {
             var slug = urls[3].slice(7);    
             $(this).attr('href', mainCategory + '/' + slug);                 
         }
     });
    
    /*--------------------------------------------------------------------------
     Preview functionality on advertiser edit page
     -------------------------------------------------------------------------*/ 
    
    /**
     * Ajax call to a save routine is triggered on mouseover in order to save
     * data before a 'Preview' link is clicked.
     */  
    $('#ajax-preview').on('mouseover', function(e) {
        var formData =  $('#advertiser-edit-form').serializeArray();
        
        /* Pass another key/value pair for a later AJAX check in the script */
        formData.push({name: 'ajaxPreview', value: true});
        
        $.ajax({
           type: 'post' ,
           url: "../wp-content/plugins/SoSensational/web/edit-advertiser-action.php",
           data: formData,
           success: function(data, status, jqXHR) {
                previewURL = data;
                $('#ajax-preview').attr('href', previewURL);        
           }           
        });        
    });
    var previewURL = $('.preview-anchor-text').attr('href');
    $('.ajax-preview').attr('href', previewURL);
    
    /*--------------------------------------------------------------------------
     Max Mega Menu - make parent elements clickable on mobile devices
     -------------------------------------------------------------------------*/     
    
    $('li.mega-menu-megamenu.mega-menu-item-has-children > a, li.mega-menu-flyout.mega-menu-item-has-children > a, li.mega-menu-flyout li.mega-menu-item-has-children > a').off('click');     
    
    //Shop controls toggle
    
    var toggleButton = $('#shop-controls-toggle');
    if (toggleButton.length) {
        toggleButton.on('click', function() {
            $('.shop_side').toggleClass('hidden-xs').toggleClass('hidden-sm');
        });
    }
    
    //Toggle the searchform on the search icon click event

    jQuery('#mega-menu-primary > li:last-child > a').click(function(e) {
        e.preventDefault();
        jQuery('#sosen-searchform').toggle();
    }); 
    
    if (jQuery('.search-row').length) {
        $(jQuery('.search-row')).each(function(index) {
            if ($(this).find('.no-results').length) {
                $(this).insertAfter(jQuery('.search-row.last'));
            }
        });
    }
    
});


/*------------------------------------------------------------------------------
 Dynamic Flexslider - adapts to the viewport size
 -----------------------------------------------------------------------------*/ 
var sliderMode;
var sliderArguments;

function jqUpdateSize() {
    var width = jQuery(window).width();
    return width;
}

function attachSlider(sliderArguments) {
    jQuery('.flexslider').flexslider(sliderArguments);
}

function getDesktopSliderSettings() {
    return sliderArguments = {
        animation: "slide",
        animationLoop: false,
        controlNav: false,
        itemWidth: 265,
        itemMargin: 20,
        prevText: " ",
        nextText: " ",
        slideshow: false,
        start: function () {
            jQuery('.slides').show();
        }            
    };
}

function getMobileSliderSettings() {
    return sliderArguments = {
        animation: "slide",
        prevText: " ",
        nextText: " ",    
        controlNav: false,   
        itemMargin: 0,
        start: function () {
            jQuery('.slides').show();
        }
    };                
}

function getTabletSliderSettings() {
    return sliderArguments = {
        animation: "slide",
        animationLoop: false,
        controlNav: false,
        itemWidth: 340,
        itemMargin: 20,
        prevText: " ",
        nextText: " ",
        slideshow: false,
        start: function () {
            jQuery('.slides').show();
        }            
    };              
}   
// Featured and related sliders
function getDesktopSliderSettingsB() {
    return sliderArguments = {
        animation: "slide",
        animationLoop: false,
        controlNav: false,
        itemWidth: 365,
        itemMargin: 15,
        prevText: " ",
        nextText: " ",
        slideshow: false,
        start: function () {
            jQuery('.slides').show();
        }            
    };
}

function getMobileSliderSettingsB() {
    return sliderArguments = {
        animation: "slide",
        prevText: " ",
        nextText: " ",    
        controlNav: false,   
        itemMargin: 0,
        start: function () {
            jQuery('.slides').show();
        }
    };                
}

function getTabletSliderSettingsB() {
    return sliderArguments = {
        animation: "slide",
        animationLoop: false,
        controlNav: false,
        itemWidth: 355,
        itemMargin: 15,
        prevText: " ",
        nextText: " ",
        slideshow: false,
        start: function () {
            jQuery('.slides').show();
        }            
    };              
} 

function loadSlider(sliderArguments, sliderMode) {
    var clonedSliderDOM = jQuery('.flexslider').clone();
    clonedSliderDOM.flexslider(sliderArguments);
    var oldSliderMode = function() {
        var classes = clonedSliderDOM.attr('class').split(/\s+/);
        console.log(classes[1]);
        return classes[1];
    }();
    jQuery('.flexslider').replaceWith(clonedSliderDOM);
    jQuery('.flexslider').removeClass(oldSliderMode);
    jQuery('.flexslider').addClass(sliderMode);

}   


// Dynamic flexslider - advertiser profile page


    jQuery(document).ready(function ($) {
        if (jQuery('.flexslider-container.advertiser-profile').length) {

            if (jqUpdateSize() < 768) {
                sliderArguments = getMobileSliderSettings();
                sliderMode = 'mobile';
            } else if (jqUpdateSize() >= 768 && jqUpdateSize() < 992) {
                sliderArguments = getTabletSliderSettings();
                sliderMode = 'tablet';
            } else {
                sliderArguments = getDesktopSliderSettings();
                sliderMode = 'desktop';
            }
            $('.flexslider').flexslider(sliderArguments).addClass(sliderMode);

            jQuery(window).resize(function ($) {
                console.log('resizing');
                if (jQuery('.flexslider-container.advertiser-profile').length) {
                    if (jqUpdateSize() < 768 && sliderMode !== 'mobile') {
                        sliderArguments = getMobileSliderSettings();
                        sliderMode = 'mobile';
                        loadSlider(sliderArguments, sliderMode);
                    } else if (jqUpdateSize() >= 768 && jqUpdateSize() < 992 && sliderMode !== 'tablet') {
                        sliderArguments = getTabletSliderSettings();
                        sliderMode = 'tablet';
                        loadSlider(sliderArguments, sliderMode);
                    } else if (jqUpdateSize() >= 992 && sliderMode !== 'desktop') {
                        sliderArguments = getDesktopSliderSettings();
                        sliderMode = 'desktop';
                        loadSlider(sliderArguments, sliderMode);
                    }
                }
            });

        }
    });




// Dynamic flexslider - featured and related sliders (category and shop pages)

    jQuery(document).ready(function ($) {
        if (jQuery('.advertisers-carousel').length) {
            if (jqUpdateSize() < 768) {
                sliderArguments = getMobileSliderSettingsB();
                sliderMode = 'mobile';
            } else if (jqUpdateSize() >= 768 && jqUpdateSize() < 992) {
                sliderArguments = getTabletSliderSettingsB();
                sliderMode = 'tablet';
            } else {
                sliderArguments = getDesktopSliderSettingsB();
                sliderMode = 'desktop';
            }
            $('.flexslider').flexslider(sliderArguments).addClass(sliderMode);
            
            jQuery(window).resize(function () {
                if (jqUpdateSize() < 768 && sliderMode !== 'mobile') {
                    sliderArguments = getMobileSliderSettingsB();
                    sliderMode = 'mobile';
                    loadSlider(sliderArguments, sliderMode);

                } else if (jqUpdateSize() >= 768 && jqUpdateSize() < 992 && sliderMode !== 'tablet') {
                    sliderArguments = getTabletSliderSettingsB();
                    sliderMode = 'tablet';
                    loadSlider(sliderArguments, sliderMode);

                } else if (jqUpdateSize() >= 992 && sliderMode !== 'desktop') {
                    sliderArguments = getDesktopSliderSettingsB();
                    sliderMode = 'desktop';
                    loadSlider(sliderArguments, sliderMode);

                }
            });
        }
    });


    

jQuery('body').on('click', '.mega-menu-item-11807 > a', function(e){
    var w = jQuery(window).width();
    if(w < 800) {
        e.preventDefault();
        jQuery(jQuery(this).parent().find('ul')[0]).toggleClass('visible');
    }

});