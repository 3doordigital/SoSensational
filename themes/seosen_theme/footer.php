<div class="container">
    <hr>
</div>
<section id="footer" class="container">
    <div class="row">
        <div class="col-md-9">
            <h2>Welcome to SoSensational</h2>
            <p>The shopping and style guide for grown up women, where you can buy daywear, posh frocks, wedding and mother-of-the-bride outfits, petites clothing, pluz size clothing and accessories.</p>
        </div>
        <div class="col-md-4 col-md-offset-1">
            <h2 class="footer-menu-heading">Menu</h2>
            <nav class="navbar-default">
                <div class="navbar-header">
                  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-navbar-collapse-footer">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                  </button>
                </div>            
                <?php
                if (function_exists('wp_nav_menu')) {
                    wp_nav_menu(array(
                        'menu' => 'footer',
                        'theme_location' => 'footer',
                        'container' => 'div',
                        'container_class' => 'collapse navbar-collapse',
                        'container_id' => 'bs-navbar-collapse-footer',
                        'menu_class' => 'nav navbar-nav',
                        'fallback_cb' => 'wp_bootstrap_navwalker::fallback',
                        'walker' => new wp_bootstrap_navwalker()
                        )
                    );
                }
                ?>            
            </nav>
        </div>
        <div class="col-md-9 col-md-offset-1">
            <div class="row">
                <div class="col-md-24">
                    <div id="footer-newsletter">
                        <h2>Join Our Newsletter</h2>
                        <form class="newsform_send">
                            <input type="email" name="email" placeholder="Your email address" /> <button class="join-now" type="submit">Join Now</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-24 social">
                    <div id="social-icons">
                        <h2>Connect With Us</h2>
                        <a href="#">
                            <span class="fa-stack fa-lg">
                                <i class="fa fa-circle fa-stack-2x"></i>
                                <i class="fa fa-facebook fa-stack-1x fa-inverse"></i>
                            </span>
                        </a>
                        <a href="#">
                            <span class="fa-stack fa-lg">
                                <i class="fa fa-circle fa-stack-2x"></i>
                                <i class="fa fa-instagram fa-stack-1x fa-inverse"></i>
                            </span>
                        </a>
                        <a href="#">
                            <span class="fa-stack fa-lg">
                                <i class="fa fa-circle fa-stack-2x"></i>
                                <i class="fa fa-pinterest fa-stack-1x fa-inverse"></i>
                            </span>
                        </a>
                        <a href="#">
                            <span class="fa-stack fa-lg">
                                <i class="fa fa-circle fa-stack-2x"></i>
                                <i class="fa fa-stumbleupon fa-stack-1x fa-inverse"></i>
                            </span>
                        </a>
                        <a href="#">
                            <span class="fa-stack fa-lg">
                                <i class="fa fa-circle fa-stack-2x"></i>
                                <i class="fa fa-instagram fa-stack-1x fa-inverse"></i>
                            </span>
                        </a>
                        <a href="#">
                            <span class="fa-stack fa-lg">
                                <i class="fa fa-circle fa-stack-2x"></i>
                                <i class="fa fa-youtube fa-stack-1x fa-inverse"></i>
                            </span>
                        </a>
                        <a href="#">
                            <span class="fa-stack fa-lg">
                                <i class="fa fa-circle fa-stack-2x"></i>
                                <i class="fa fa-rss fa-stack-1x fa-inverse"></i>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="container">
    <hr>
</div>
<div id="subfoot" class="container">
    &copy; Copyright 3 Door Digital
<?php $page_id = $wp_query->get_queried_object_id(); ?>
</div>
<?php wp_footer(); ?>
</body>
</html>