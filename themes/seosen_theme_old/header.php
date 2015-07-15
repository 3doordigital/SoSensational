<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<title><?php wp_title(''); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div class="container" id="topbar">
	<div class="row">
        <div class="col-md-12">SHOPPING, STYLE AND BEAUTY FOR GROWN UP WOMEN</div>
        <div class="col-md-12 text-right">
            <a href="#"><i class="fa fa-facebook"></i></a>
            <a href="#"><i class="fa fa-instagram"></i></a>
            <a href="#"><i class="fa fa-linkedin"></i></a>
            <a href="#"><i class="fa fa-pinterest"></i></a>
            <a href="#"><i class="fa fa-rss"></i></a>
            <a href="#"><i class="fa fa-twitter"></i></a>
            <a href="#"><i class="fa fa-youtube"></i></a>
        </div>
    </div>
</div>
<nav class="navbar navbar-default container" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->
  <div class="navbar-header">
    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </button>
    <a class="navbar-brand" href="#"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/logo.png"  alt=""/></a>
    </div>
   
    <?php if ( function_exists('wp_nav_menu') ) { wp_nav_menu( array(
                'menu'              => 'primary',
                'theme_location'    => 'primary',
                'container'         => 'div',
                'container_class'   => 'collapse navbar-collapse bs-navbar-collapse2',
        		'container_id'      => 'bs-navbar-collapse2',
                'menu_class'        => 'nav navbar-nav',
                'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
                'walker'            => new wp_bootstrap_navwalker()
				)
            ); } ?>
            <div class="nav navbar-nav navbar-right">
      	<button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
      </div> 
</nav>