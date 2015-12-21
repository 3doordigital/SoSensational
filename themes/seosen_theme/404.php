<?php get_header(); ?>
<div class="container notfoundback">
    <div class="row">
        <div class="col-md-12" style="background-image: url('<?php bloginfo('stylesheet_directory'); ?>/images/404.png'); ?>; background-size: cover; min-height: 700px;">
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-24 notfound">
                    <h1>Oops!</h1>
                    <h2>Looks like you can't find what you're looking for?</h2>
                    <div class="center notfound_middle">
                        <h3>404</h3>
                        <h4>Page Not Found</h4>
                    </div>
                    <div class="notfound_search">
                        <h5>Try searching for something else:</h5>
                        <?php get_search_form( ); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-24 notfound_foot">
                    <h6>Browse Our Site</h6>
                    <div class="row">
                        <div class="col-md-12">
                            <ul>
                                <li><a href="<?php echo get_permalink(get_page_by_title('Shop'))  ?>">Browse Our Shop</a></li>
                                <li><a href="<?php echo get_permalink(get_page_by_title('Shop')) . 'collections'  ?>">Brose Our Collections</a></li>
                                <li><a href="<?php echo home_url() . '/competitions'  ?>"">Browse Our Competitions</a></li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <ul>
                                <li><a href="<?php echo get_permalink(get_page_by_title('Brands & Boutiques'))  ?>">Browse Our Brands &amp; Boutiques</a></li>
                                <li><a href="<?php echo home_url() . '/hot-deals'  ?>">Browse our Hot Deals</a></li>
                                <li><a href="<?php echo get_permalink(get_page_by_title('Style Advice Blog'))  ?>">Browse Our Blog</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_footer();