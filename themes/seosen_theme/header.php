<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width">
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
        <title><?php wp_title(''); ?></title>
        <?php
            $pageId = get_the_ID();
        ?>
        <?php if($pageId === 22) : ?>
            <script> var noskim = 'true'; </script>
        <?php endif; ?>
        <?php wp_head(); ?>
        <!--<script src='https://www.google.com/recaptcha/api.js'></script>-->
        <?php if(is_front_page()) : ?><!-- Facebook Pixel Code -->
            <script>
                !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
                    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
                    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
                    document,'script','//connect.facebook.net/en_US/fbevents.js');

                fbq('init', '425393637658585');
                fbq('track', "PageView");</script>
            <noscript><img height=1" width="1" style="display:none"
                src="https://www.facebook.com/tr?id=425393637658585&ev=PageView&noscript=1"
                /></noscript>
            <!-- End Facebook Pixel Code -->
        <?php endif; ?>
        <?php wp_head(); ?>
        <!--<script src='https://www.google.com/recaptcha/api.js'></script>-->
        <meta name="avgthreatlabs-verification" content="e62dfc8e18b63d354a4f693629876df909f37b3b" />
    </head>
    <body <?php body_class(); ?>>
<?php global $seosen_options; echo $seosen_options['custom_html']; ?>
    	<div id="top">
        	<div class="container">
            	<div class="row">
                    <div class="blog-masthead">
                        <div class="col-xs-24 col-md-16">ALL THE FASHION IN ONE PLACE, EDITED FOR WHO YOU ARE NOW</div>
                        <div class="col-md-8 text-right hidden-xs">
                            <a href="https://www.facebook.com/sosensational"><i class="fa fa-facebook"></i></a>
                            <a href="https://instagram.com/_sosensational/"><i class="fa fa-instagram"></i></a>
                            <a href="http://www.stumbleupon.com/submit?url=http%3A%2F%2Fwww.sosensational.co.uk%2F"><i class="fa fa-stumbleupon"></i></a>
                            <a href="http://pinterest.com/sosensational/"><i class="fa fa-pinterest"></i></a>
                            <a href="/feed/"><i class="fa fa-rss"></i></a>
                            <a href="https://twitter.com/_sosensational"><i class="fa fa-twitter"></i></a>
                            <a href="https://www.youtube.com/channel/UCAveovGSdh3nxaNI3HYW7sg"><i class="fa fa-youtube"></i></a>
                            <a href="https://plus.google.com/114715929781995055714/"><i class="fa fa-google-plus"></i> </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container" id="topbar">
            <div class="row">
            <nav class="navbar navbar-default container" role="navigation">

                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <a class="navbar-brand" href="/"><img class="img-responsive" src="<?php bloginfo('stylesheet_directory'); ?>/images/logo-new.png"  alt=""/></a>
                </div>

                <?php
                if (function_exists('wp_nav_menu')) {   
                    global $query_string;
                    fixMenuOnCompetitionsPage();    
                    wp_nav_menu(array(
                        'menu' => 'primary',
                        'theme_location' => 'primary',
                        'container' => 'div',
                        'container_class' => 'collapse navbar-collapse bs-navbar-collapse2',
                        'container_id' => 'bs-navbar-collapse2',
                        'menu_class' => 'nav navbar-nav',
                        'fallback_cb' => 'wp_bootstrap_navwalker::fallback',
                        'walker' => new wp_bootstrap_navwalker()
                        )
                    );
                }
                ?>
            </nav>
        </div>
            <div class="row">
                <div class="col-xs-24" id="sosen-searchform">
                  <?php get_template_part('header-searchform'); ?>                    
                </div>
            </div>
    </div>
