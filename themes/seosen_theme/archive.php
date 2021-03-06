<?php get_header(); ?>
<div class="container pagetop">
    <h2 class="pagetitle"><span>Style Advice &amp; Blog</span>
    <?php
    if (function_exists('wp_nav_menu')) {
        wp_nav_menu(array(
            'menu' => 'blog',
            'theme_location' => 'blog',
            'container' => 'div',
            'container_class' => '',
            'container_id' => 'topcats',
            'menu_class' => '',
            'fallback_cb' => 'wp_bootstrap_navwalker::fallback',
            'walker' => new wp_bootstrap_navwalker()
            )
        );
    }
    ?>
    </h2>
    <?php
    if (function_exists('yoast_breadcrumb')) {
        //yoast_breadcrumb('<div id="breadcrumbs">', '</div>');
    }
    ?>
    <div id="breadcrumbs" class="breadcrumbs" xmlns:v="http://rdf.data-vocabulary.org/#">
		<?php if(function_exists('bcn_display'))
        {
            bcn_display();
        }?>
    </div>
</div>
<div class="container blog">
    <div class="row">
        <div class="col-sm-24 col-md-24 col-lg-16" id="">
            <h1 class="cattitle"><?php single_cat_title('', true); ?></h1>
            <?php
            $i = 1;
            $x = 0;
            if (have_posts()) : while (have_posts()) : the_post();
                    ?>

                    <?php if ($i == 1) { ?>
                        <div class="row">
                            <div class="col-md-24">
                                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                                <?php sosen_post_meta(); ?>
                                <?php
                                if (has_post_thumbnail()) { // check if the post has a Post Thumbnail assigned to it.
                                    ?>
                                    <a href="<?php the_permalink(); ?>">
                                        <?php
                                        the_post_thumbnail('blog-large', array('class' => 'img-responsive'));
                                        ?>
                                    </a>
                                    <?php
                                }
                                ?>

                                <?php
                                the_excerpt_max_charlength(340);
                                ?>
                            </div>
                        </div>
                    <?php } else { ?>
                        <?php if ($x == 0) { ?>
                            <div class="row">
                            <?php } ?>
                            <div class="col-sm-12 blog-small">

                                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                                <?php sosen_post_meta(); ?>
                                <?php
                                if (has_post_thumbnail()) { // check if the post has a Post Thumbnail assigned to it.
                                    ?>
                                    <a href="<?php the_permalink(); ?>">
                                        <?php
                                        the_post_thumbnail('blog-small', array('class' => 'img-responsive'));
                                        ?>
                                    </a>
                                    <?php
                                }
                                ?>

                                <?php
                                the_excerpt_max_charlength(170);
                                ?>
                            </div>   
                            <?php if ($x == 1 || $wp_query->post_count == $i) { ?>
                            </div>

                        <?php } ?>
                        <?php
                        if ($x == 1) {
                            $x = 0;
                        } else {
                            $x++;
                        }
                        ?>
                        <?php
                    }
                    $i++;
                    ?>
                <?php endwhile; ?>
    <?php wp_pagenavi(); ?>
<?php endif; ?>
        </div>

        <div class="col-lg-7 col-sm-offset-1 visible-lg">
            <div class="clearfix"></div>
<?php dynamic_sidebar('blog_sidebar'); ?> 
        </div>
    </div>
</div>
<?php get_footer(); ?>