<?php get_header(); ?>
<div class="container">
    <h1><span>Hot Deals &amp; Sensational Offers</span></h1>
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
<div class="container">
    <div class="row">
        <div class="col-md-19">
            <div id="hot_deals" class="row">
                <?php
                $options = array(
                    'posts_per_page' => 10,
                    'post_type' => 'hot-deals',
                    'order_by' => 'post_date',
                    'order' => 'DESC'
                );
                $posts = get_posts($options);
                $i = 0;
                $n = 0;
                foreach ($posts as $post) {
                    setup_postdata($post);
                    ?>   

                    <div class="col-sm-12 col-xs-24">
                        <div class="deal-block">
                            <div class="deal-logo">
                                <?php
                                $image = get_field('deal_logo');
                                $size = 'hot-deal'; // (thumbnail, medium, large, full or custom size)

                                if ($image) {

                                    echo wp_get_attachment_image($image, $size);
                                }
                                ?>
                            </div>
                            <div class="deal-title"><h2><?php the_title(); ?></h2></div>
                            <div class="deal-summary"><?php the_field('deal_summary'); ?></div>
                            <div class="deal-button"><a href="<?php the_field('deal_link'); ?>" target="_blank" class="btn btn-deal">Get Offer And Open Site</a></div>
                            <div class="deal-share"><a data-toggle="modal" data-target="#myModal<?php echo $n; ?>" href="#"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/share.png"></a></div>

                            <div class="modal fade" id="myModal<?php echo $n; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title" id="myModalLabel">Share <?php the_title(); ?></h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row"><div class="shareme col-md-24" data-url="<?php the_field('deal_link'); ?>" data-text="<?php the_title(); ?> from <?php the_field('deal_provider'); ?>"></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    $n++;
                    if ($i == 0) {
                        $i = 1;
                    } else {
                        $i = 0;
                    }
                }
                ?>
            </div>
        </div>
        <div class="col-md-3 col-md-offset-2 hidden-sm hidden-xs">
            <?php dynamic_sidebar('deals_sidebar'); ?>
        </div>
    </div>
</div>
<?php get_footer(); ?>