<?php get_header(); ?>
<div class="container">
    <h1><span>Style Advice &amp; Blog</span></h1>
    <?php if ( function_exists('wp_nav_menu') ) { wp_nav_menu( array(
                'menu'              => 'blog',
                'theme_location'    => 'blog',
                'container'         => 'div',
                'container_class'   => '',
        		'container_id'      => 'topcats',
                'menu_class'        => '',
                'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
                'walker'            => new wp_bootstrap_navwalker()
				)
            ); } ?>
    
    <?php 
        if ( function_exists('yoast_breadcrumb') ) {
            yoast_breadcrumb('<div id="breadcrumbs">','</div>');
        } 
    ?>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-16" id="content">
            <?php
                if( have_posts() ) : while( have_posts() ) : the_post();
            ?>
                <div class="row">
                    <div class="col-md-24">
                        <?php 
                            if ( has_post_thumbnail( ) ) { // check if the post has a Post Thumbnail assigned to it.
                                the_post_thumbnail( 'blog-large', array( 'class' => 'img-responsive' ) );
                            } 
                        ?>
                        <?php sosen_post_meta(); ?>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <?php
                            the_content();
                        ?>
                    </div>
                </div>
           
            <?php endwhile; ?>
            <?php sosen_related_posts( $cur_cat_id, get_the_ID() ); ?>
            <?php comments_template(); ?>
            <?php endif; ?>
        </div>
        <div class="col-md-1"></div>
        <div class="col-md-7">
            <?php dynamic_sidebar( 'blog_sidebar' ); ?> 
        </div>
    </div>
</div>
<?php get_footer(); ?>