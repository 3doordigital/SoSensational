jQuery(document).ready(function ($) {

    if ($(".wp_aff_categories .current-cat").length) {
        var cat_scroll = $('.wp_aff_categories .current-cat').position();
        $('.wp_aff_categories').animate({scrollTop: cat_scroll.top - 25}, 'slow');
    }
    if ($(".wp_aff_brands .current-cat").length) {
        var cat_scroll = $('.wp_aff_brands .current-cat').position();
        $('.wp_aff_brands').animate({scrollTop: cat_scroll.top - 25}, 'slow');
    }

    $('.wp_aff_cat_link break').live('click', function (event) {
        var thislink = $(this);
        //event.preventDefault();
        var data = {
            'action': 'change_faceted_category',
            'cat': $(this).attr('rel')
        };
        thislink.toggleClass('active');
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        $.post(ajax_object.ajax_url, data, function (response) {
            thislink.after(response);
        });
    });

    $('.brand_check').change(function (event) {
        $('#wp_aff_brand_filter').submit();
    });

    $('#wp_aff_sale_filter input').change(function (event) {
        $('#wp_aff_sale_filter').submit();
    });

    $('#shop_sort').change(function (event) {
        event.preventDefault();
        var thislink = $(this);
        var data = {
            'action': 'sort_shop',
            'sortby': thislink.val(),
            'redirect': window.location.href
        }
        $.post(ajax_object.ajax_url, data, function (response) {
            window.location.assign(response);
        });
    });

    $(document).on('click', '.remove', function (event) {
        event.preventDefault();
        var thislink = $(this);
        var data = {
            'action': 'remove_facted_element',
            'type': thislink.attr('data-type'),
            'term': thislink.attr('data-term'),
            'redirect': window.location.href
        }
        $.post(ajax_object.ajax_url, data, function (response) {
            window.location.assign(response);
        });
    });

    if (typeof (minPrice) == 'undefined')
        minPrice = 0;

    if (typeof (maxPrice) == 'undefined')
        maxPrice = 0;

    if (typeof (valuesPrice) == 'undefined')
        valuesPrice = 0;

    if ($("#slider-range").length) {
        $("#slider-range").slider({
            range: true,
            min: minPrice,
            max: maxPrice,
            values: valuesPrice,
            slide: function (event, ui) {
                $("#amount").val("£" + ui.values[0] + " - £" + ui.values[1]);
                $('#price-min').val(ui.values[0]);
                $('#price-max').val(ui.values[1]);
            }
        });


        $("#amount").val("£" + $("#slider-range").slider("values", 0) +
            " - £" + $("#slider-range").slider("values", 1));

        $('.colour_filter a').click(function (event) {
            event.preventDefault();
            var id = $(this).attr('data-id');
            if ($('.hide_check[data-id=' + id + ']').prop('checked') == true) {
                $('.hide_check[data-id=' + id + ']').prop('checked', false);
                $('.hide_check[data-id=' + id + ']').attr('checked', false);
            } else {
                $('.hide_check[data-id=' + id + ']').prop('checked', true);
                $('.hide_check[data-id=' + id + ']').attr('checked', true);
            }
            $('#wp_aff_colour_filter').submit();
        });

        $('.size_filter a').click(function (event) {
            event.preventDefault();
            var id = $(this).attr('data-id');
            if ($('.hide_check[data-id=' + id + ']').prop('checked') == true) {
                $('.hide_check[data-id=' + id + ']').prop('checked', false);
                $('.hide_check[data-id=' + id + ']').attr('checked', false);
            } else {
                $('.hide_check[data-id=' + id + ']').prop('checked', true);
                $('.hide_check[data-id=' + id + ']').attr('checked', true);
            }
            $('#wp_aff_size_filter').submit();
        });
    }


    $('.del_link').on('click', function (e) {
        //e.preventDefault();
        if (confirm('Are you sure, this action cannot be undone?')) {
            return;
        } else {
            return false;
        }
    });

    var bodyWidth = $('body').width();
    var $anchorToCategoriesFilterElem = jQuery('.wp_aff_categories');
    var $oldHtmlInSpanCategory = $anchorToCategoriesFilterElem.prev().find('span').html();
    var eventFunction = function (event) {
        _$self = $(this);
        event.preventDefault();
        var bodyWidth = $('body').width();
        if (bodyWidth < 975) {
            if (_$self.next('.children').length > 0) {
                $(this).next('.children').toggle(300);
            } else {
                window.location = _$self.attr('href');
            }
        }
        else{
            window.location = _$self.attr('href');
        }
    };
    var productCount = $('#product_count');
    if (bodyWidth < 975) {
        //change text in categories
        $anchorToCategoriesFilterElem.prev().find('span').html('<span data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" style="cursor:pointer;">Click to Filter by Category <i class="fa fa-level-down navbar-toggle collapsed visible-xs visible-sm" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" style="padding-top: 0"></i><span>');
        $anchorToCategoriesFilterElem.prev().css({
            cursor: 'pointer',
            background: 'rgb(147,93,195)',
            background: '-moz-linear-gradient(top,rgba(147,93,195,1) 0%,rgba(108,32,177,1) 100%)',
            background: '-webkit-gradient(linear,left top,left' +
            ' bottom,color-stop(0%,rgba(147,93,195,1)),color-stop(100%,rgba(108,32,177,1)))',
            background: '-webkit-linear-gradient(top,rgba(147,93,195,1) 0%,rgba(108,32,177,1) 100%)',
            background: '-o-linear-gradient(top,rgba(147,93,195,1) 0%,rgba(108,32,177,1) 100%)',
            background: '-ms-linear-gradient(top,rgba(147,93,195,1) 0%,rgba(108,32,177,1) 100%)',
            background: 'linear-gradient(to bottom,rgba(147,93,195,1) 0%,rgba(108,32,177,1) 100%)',
            filter: 'progid:DXImageTransform.Microsoft.gradient(startColorstr="#935dc3",endColorstr="#6c20b1",GradientType=0)',
            color: '#fff',
            'text-transform': 'uppercase',
            'font-family': '"Open Sans",sans-serif',
            'font-size': '11px',
            display: 'block',
            'text-shadow': 'none',
            padding: '6px 15px',
            border: 'none',
            'border-radius': '4px',
            margin: 0,
            height: '26px'
        }
    );
        productCount.hide();
        jQuery('#product_filter_bottom .col-md-17').children(0).eq(0).hide();
    } else {
        $anchorToCategoriesFilterElem.prev().find('span').html('$oldHtmlInSpanCategory');
        productCount.show();
        jQuery('#product_filter_bottom .col-md-17').children(0).eq(0).show();
    }
    $(window).on('resize', function () {
        var bodyWidth = $('body').width();
        if (bodyWidth < 975) {
            productCount.hide();
            jQuery('#product_filter_bottom .col-md-17').children(0).eq(0).hide();
            //change text in categories
            $anchorToCategoriesFilterElem.prev().find('span').html('<span data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" style="cursor:pointer;">Click to Filter by Category <i class="fa fa-level-down navbar-toggle collapsed visible-xs visible-sm" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1"style="padding-top: 0"></i></span>');
            $anchorToCategoriesFilterElem.prev().css({
                cursor: 'pointer',
                background: 'rgb(147,93,195)',
                background: '-moz-linear-gradient(top,rgba(147,93,195,1) 0%,rgba(108,32,177,1) 100%)',
                background: '-webkit-gradient(linear,left top,left' +
                ' bottom,color-stop(0%,rgba(147,93,195,1)),color-stop(100%,rgba(108,32,177,1)))',
                background: '-webkit-linear-gradient(top,rgba(147,93,195,1) 0%,rgba(108,32,177,1) 100%)',
                background: '-o-linear-gradient(top,rgba(147,93,195,1) 0%,rgba(108,32,177,1) 100%)',
                background: '-ms-linear-gradient(top,rgba(147,93,195,1) 0%,rgba(108,32,177,1) 100%)',
                background: 'linear-gradient(to bottom,rgba(147,93,195,1) 0%,rgba(108,32,177,1) 100%)',
                filter: 'progid:DXImageTransform.Microsoft.gradient(startColorstr="#935dc3",endColorstr="#6c20b1",GradientType=0)',
                color: '#fff',
                'text-transform': 'uppercase',
                'font-family': '"Open Sans",sans-serif',
                'font-size': '11px',
                display: 'block',
                'text-shadow': 'none',
                padding: '6px 15px',
                border: 'none',
                'border-radius': '4px',
                margin: 0,
                height: '26px'
            });
        } else {
            $anchorToCategoriesFilterElem.prev().find('span').html($oldHtmlInSpanCategory);
            jQuery('#product_filter_bottom .col-md-17').children(0).eq(0).show();
            productCount.show();
        }
    });
    $(document).on('click', '.shop-filter .cat-item > a', eventFunction);
});