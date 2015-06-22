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
           
    if (tagsInputField.length) {
        tagsInputField.tagsinput({
           maxTags: tagsLimit,
           trimValue: true
        });        
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
var arguments;

function jqUpdateSize() {
    var width = jQuery(window).width();
    return width;
}

function attachSlider(arguments) {
    jQuery('.flexslider').flexslider(arguments);
}

function getDesktopSliderSettings() {
    return arguments = {
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
    return arguments = {
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
    return arguments = {
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
    return arguments = {
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
    return arguments = {
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
    return arguments = {
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

function loadSlider(arguments, sliderMode) {     
    // on mobile there was a problem with  
    var clonedSliderDOM = jQuery('.flexslider').clone();  
    clonedSliderDOM.flexslider(arguments);
    var oldSliderMode = function() {
        var classes = clonedSliderDOM.attr('class').split(/\s+/);
        return classes[1];
    };
    jQuery('.flexslider').replaceWith(clonedSliderDOM);
    jQuery('.flexslider').removeClass(oldSliderMode);
    jQuery('.flexslider').addClass(sliderMode);        
}   


// Dynamic flexslider - advertiser profile page
//if (jQuery('.flexslider-container.advertiser-profile').length) {

    jQuery(document).ready(function ($) {
        if (jqUpdateSize() < 768) {
            arguments = getMobileSliderSettings();
            sliderMode = 'mobile';
        } else if (jqUpdateSize() >= 768 && jqUpdateSize() < 992) {
            arguments = getTabletSliderSettings();
            sliderMode = 'tablet';
        } else {
            arguments = getDesktopSliderSettings();
            sliderMode = 'desktop';
        }
        //$('.flexslider').flexslider(arguments).addClass(sliderMode); 
		loadSlider(arguments, sliderMode);     
    });

    jQuery(window).resize(function () {  
        if (jqUpdateSize() < 768 && sliderMode !== 'mobile') {                                    
            arguments = getMobileSliderSettings(); 
            sliderMode = 'mobile';
            loadSlider(arguments, sliderMode);
        } else if (jqUpdateSize() >= 768 && jqUpdateSize() < 992 && sliderMode !== 'tablet') {
            arguments = getTabletSliderSettings();
            sliderMode = 'tablet';
            loadSlider(arguments, sliderMode);         
        } else if (jqUpdateSize() >= 992 && sliderMode !== 'desktop') {          
            arguments = getDesktopSliderSettings();
            sliderMode = 'desktop';
            loadSlider(arguments, sliderMode);
        }
    });
    
//}    

// Dynamic flexslider - featured and related sliders (category and shop pages)

    /*jQuery(window).ready(function ($) {
        //if (jQuery('.advertisers-carousel').length) {
            if (jqUpdateSize() < 768) {
                arguments = getMobileSliderSettingsB();
                sliderMode = 'mobile';
            } else if (jqUpdateSize() >= 768 && jqUpdateSize() < 992) {
                arguments = getTabletSliderSettingsB();
                sliderMode = 'tablet';
            } else {
                arguments = getDesktopSliderSettingsB();
                sliderMode = 'desktop';
            }
            $('.flexslider').flexslider(arguments).addClass(sliderMode);  
			loadSlider(arguments, sliderMode);  
       // }
    });*/

    /*jQuery(window).resize(function () {  
        if (jqUpdateSize() < 768 && sliderMode !== 'mobile') {                                    
            arguments = getMobileSliderSettingsB(); 
            sliderMode = 'mobile';
            loadSlider(arguments, sliderMode);
        } else if (jqUpdateSize() >= 768 && jqUpdateSize() < 992 && sliderMode !== 'tablet') {
            arguments = getTabletSliderSettingsB();
            sliderMode = 'tablet';
            loadSlider(arguments, sliderMode);         
        } else if (jqUpdateSize() >= 992 && sliderMode !== 'desktop') {          
            arguments = getDesktopSliderSettingsB();
            sliderMode = 'desktop';
            loadSlider(arguments, sliderMode);
        }
    });*/
    

jQuery('body').on('click', '.mega-menu-item-11807 > a', function(e){
    var w = jQuery(window).width();
    if(w < 800) {
        e.preventDefault();
        jQuery(jQuery(this).parent().find('ul')[0]).toggleClass('visible');
    }

});




