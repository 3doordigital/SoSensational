<?php get_header(); ?>
<div class="container notfoundback">
    <div class="row">
        <div class="col-md-12" style="background-image: url('<?php bloginfo('stylesheet_directory'); ?>/images/404.png'); ?>; background-size: cover; min-height: 700px;">
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-24 notfound">
                    <h1>Oops!</h1>
                    <h2>It looks like we don't have any results for that search.</h2>
                    <div class="center notfound_middle">
                        <h3>NO</h3>
                        <h4>RESULTS FOUND</h4>
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
                                <li><a href="#">Browse Our Shop</a></li>
                                <li><a href="#">Brose Our Collections</a></li>
                                <li><a href="#">Browse Our Competitions</a></li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <ul>
                                <li><a href="#">Browse Our Brands &amp; Boutiques</a></li>
                                <li><a href="#">Browse our Hot Deals</a></li>
                                <li><a href="#">Browse Our Blog</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_footer();