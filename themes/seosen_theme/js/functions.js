jQuery(document).ready(function ($) {

    $(document).scroll(function () {
        if ($(this).scrollTop() > 0) {
            $('.top.page-scroll').fadeIn(2000)
        } else {
            $('.top.page-scroll').fadeOut(2000)
        }
    });
    $('.top.page-scroll').click(function (e) {
        e.preventDefault();
        $('body,html').animate({
            scrollTop: 0,
            easing: 'easeInOutCubic'
        }, 800);
    });
    $('a').each(function () {
        var a = new RegExp('/' + window.location.host + '/');
        if (!a.test(this.href)) {
            $(this).click(function (event) {
                event.preventDefault();
                event.stopPropagation();
                window.open(this.href, '_blank');
            });
        }
    });

    $('#mega-menu-item-5375').click(function (e) {
        e.preventDefault();
    });

    imagesLoaded('.fadebox', function () {
        $('.fadebox').css('visibility', 'visible');
        jQuery('.fadebox').addClass("hideme").viewportChecker({
            classToAdd: 'showme animated fadeIn',
            classToRemove: 'hideme',
            offset: 100,
            repeat: false,
            callbackFunction: function (elem, action) {
                $('.showme .row').fadeIn(1500);
                $('.showme .whitebar, .showme .blackbar').fadeIn(1500);
            },
            scrollHorizontal: false
        });
    });
    $('.showme .row').fadeIn(5000);
    $('.fadebox').hover(
        function () {
            $(this).find('img').animate({opacity: 0.7}, 700);
        },
        function () {
            $(this).find('img').animate({opacity: 1}, 700);
        });

    $('#homenewsletter button').hover(function () {
        $('#homenewsletter input').addClass('newshover');
    }, function () {
        $('#homenewsletter input').removeClass('newshover');
    });

    $('.shareme').sharrre({
        share: {
            facebook: true,
            twitter: true,
            linkedin: true,
            pinterest: true
        },
        buttons: {
            googlePlus: {size: 'tall', annotation: 'bubble'},
            facebook: {layout: 'box_count'},
            twitter: {count: 'vertical'},
            digg: {type: 'DiggMedium'},
            delicious: {size: 'tall'},
            stumbleupon: {layout: '5'},
            linkedin: {counter: 'top'},
            pinterest: {
                media: 'http://sharrre.com/img/example1.png',
                description: $('#shareme').data('text'),
                layout: 'vertical'
            }
        },
        enableHover: false,
        enableCounter: false,
        enableTracking: true
    });
    function centerModal() {
        $(this).css('display', 'block');
        var $dialog = $(this).find(".modal-dialog");
        var offset = ($(window).height() - $dialog.height()) / 2;
        // Center modal vertically in window
        $dialog.css("margin-top", offset);
    }

    $('.modal').on('show.bs.modal', centerModal);
    $(window).on("resize", function () {
        $('.modal:visible').each(centerModal);
    });

    imagesLoaded('.deal', function () {
        $('#hot_deals').masonry({
            gutter: 30,
            itemSelector: '.deal'
        });
    });


    if ($(".wp_aff_categories").length) {
        $(".wp_aff_categories").niceScroll({cursorcolor: '#ae984a', cursoropacitymin: '1'});
        $(".wp_aff_brands").niceScroll({cursorcolor: '#ae984a', cursoropacitymin: '1'});
    }

    jQuery(function () {
        jQuery('a[href*=#]:not([href=#],.btn-circle)').click(function () {
            $('a[href*=#]:not([href=#]),.btn-circle').each(
                function () {
                    $(this).parent().attr('style', '');
                }
            );
            if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
                var target = jQuery(this.hash);
                target = target.length ? target : jQuery('[name=' + this.hash.slice(1) + ']');
                if (target.length) {
                    jQuery('html,body').animate({
                        scrollTop: target.offset().top - 100
                    }, 1000);
                    $(this).parent().css({
                        "color": "#63531a",
                        "background-color": "#eee",
                        "border-color": "#ddd"
                    });
                    window.location.hash = this.hash;
                    return false;
                }
            }
        });
    });
    $('#searchBrands').on('keyup', function () {
        $('.button-search').addClass('button-clear').html("<span class=\"glyphicon glyphicon-remove\"></span>Clear");
        var self = $(this);
        var searchString = self.val();
        var advertisersCats = $('.ss_advertisers_cats');
        var lettersWithItems = $('.category_ss_title_under');
        lettersWithItems.each(function () {
            var countOfHiddenLetters = 0;
            var letterWithItems = $(this);
            var lettersBrands = letterWithItems.find('.ss_advertisers_cats');
            var countOfLetterBrands = lettersBrands.length;
            lettersBrands.each(function () {
                var self = $(this);
                var title = self.find('.advertiser-title').first().text();
                if (!title.match(new RegExp(searchString, 'gi'))) {
                    self.hide();
                    countOfHiddenLetters++;
                } else {
                    self.show();
                }
                if (searchString.length === 0) {
                    self.show();
                    $('.button-search').html('<span class="glyphicon glyphicon-search" aria-hidden="true">Search</span>');
                }
                if (countOfHiddenLetters === countOfLetterBrands) {
                    letterWithItems.hide();
                } else {
                    letterWithItems.show();
                }
            });
        });
    });
    $(document).on('click', '.button-clear', function () {
        $('#searchBrands').val('');
        $('#searchBrands').trigger('keyup');
    });
    $(document).on('click', '.display-alphabet', function () {
        $('.alphabet-links .pagination').toggle();
    });
    $(window).scroll(function () {
        var scroll = $(window).scrollTop();
        if (scroll >= 200) {
            $(".alphabet-links-first").addClass("panel panel-default fixed");
        } else {
            $(".alphabet-links-first").removeClass("panel panel-default fixed");
        }
    });
}); // closes ready