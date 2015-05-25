<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width">
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
        <title><?php wp_title(''); ?></title>
        <?php wp_head(); ?>
        <script src='https://www.google.com/recaptcha/api.js'></script>
    </head>
    <body <?php body_class(); ?>>
<?php global $seosen_options; echo $seosen_options['custom_html']; ?>
    	<div id="top">
        	<div class="container">
            	<div class="row">
                    <div class="blog-masthead">
                        <div class="col-xs-24 col-md-16"><span>THE</span> FASHION & BEAUTY SHOP FOR GROWN UP WOMEN</div>                
                        <div class="col-md-8 text-right hidden-xs">
                            <a href="https://www.facebook.com/sosensational"><i class="fa fa-facebook"></i></a>
                            <a href="https://instagram.com/_sosensational/"><i class="fa fa-instagram"></i></a>
                            <a href="http://www.stumbleupon.com/submit?url=http%3A%2F%2Fwww.sosensational.co.uk%2F"><i class="fa fa-stumbleupon"></i></a>
                            <a href="http://pinterest.com/sosensational/"><i class="fa fa-pinterest"></i></a>
                            <a href="/feed/"><i class="fa fa-rss"></i></a>
                            <a href="https://twitter.com/_sosensational"><i class="fa fa-twitter"></i></a>
                            <a href="https://www.youtube.com/channel/UCAveovGSdh3nxaNI3HYW7sg"><i class="fa fa-youtube"></i></a>
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
        </div>        
