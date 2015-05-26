<?php get_header(); ?>
<div class="container front-tiles">
    <div class="row">
        <div class="col-md-15 col-md-push-9 imgbox bigimgbox fadebox">
            <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
    <li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
    <li data-target="#carousel-example-generic" data-slide-to="1"></li>
    <li data-target="#carousel-example-generic" data-slide-to="2"></li>
  </ol>
                <!-- Wrapper for slides -->
                <div class="carousel-inner" role="listbox">
                    <?php $i = 0;
                    foreach ($seosen_options['home_slider'] as $slide) {
                        ?>
                        <div class="item <?php echo ($i == 0 ? 'active' : ''); ?>">
                            <a href="<?php echo $slide['url']; ?>"><?php echo wp_get_attachment_image($slide['attachment_id'], 'home_slider', false, array('class' => 'img-responsive')); ?></a>
                            <div class="row">
                                <div class="col-xs-10 rightcover blackback">
                                    <h2><?php echo $slide['title']; ?></h2>
                                    <p>
                                        <a href="<?php echo $slide['url']; ?>" class="btn btn-primary" role="button"><?php echo $seosen_options['hp_button_one']; ?></a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php $i ++;
                    }
                    ?>
                </div>
            </div>
        </div>        
        <div class="col-md-9 col-md-pull-15">
            <div class="row">
                <div class="col-xs-24 col-sm-12 col-md-24 imgbox fadebox">
                    <a href="<?php echo $seosen_options['home_image_1_link']; ?>">
						<?php echo wp_get_attachment_image($seosen_options['home_image_1']['id'], 'home_top_small', false, array('class' => 'img-responsive')); ?>
                    </a>
                        <div class="row">
                            <div class="col-xs-10 leftcover whiteback">
                                <h2><?php echo $seosen_options['home_image_1_text']; ?></h2>
                                <p>
                                    <a href="<?php echo $seosen_options['home_image_1_link']; ?>" class="btn btn-primary" role="button"><?php echo $seosen_options['hp_button_two']; ?></a>
                                </p>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-xs-24 col-sm-12 col-md-24 imgbox fadebox">
                    <a href="<?php echo $seosen_options['home_image_2_link']; ?>">
						<?php echo wp_get_attachment_image($seosen_options['home_image_2']['id'], 'home_top_small', false, array('class' => 'img-responsive')); ?>
                    </a>
                        <div class="row">
                            <div class="col-xs-10 rightcover whiteback">
                                <h2><?php echo $seosen_options['home_image_2_text']; ?></h2>
                                <p>
                                    <a href="<?php echo $seosen_options['home_image_2_link']; ?>" class="btn btn-primary" role="button"><?php echo $seosen_options['hp_button_three']; ?></a>
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <?php $i = 0;
        foreach ($seosen_options['home_cats'] as $cat) {
            ?>
    <?php
    ?>
            <div class="col-md-8 col-sm-12 fadebox">
                <a href="<?php echo $cat['url']; ?>">
    <?php echo wp_get_attachment_image($cat['attachment_id'], 'home_cat', false, array('class' => 'img-responsive')); ?>
                    <div class="<?php echo ($i == 0 || $i == 2 || $i == 4 ? 'whitebar' : 'blackbar'); ?>">
                        <h2><?php echo $cat['title']; ?></h2>
                    </div>
                </a>
            </div>
            <?php
            ?>
            <?php $i ++;
        }
        ?>
    </div>
</div>

<section class="container brands front-tiles">
    <h1><span>Featured Brands</span></h1>

<?php $i = 1;
foreach ($seosen_options['feat_brands'] as $brand) {
    ?>
        <div class="col-md-8 col-sm-12 fadebox brand <?php if ($i % 3 === 0) : ?> hidden-sm <?php endif ?>">
            <a href="<?php echo $brand['url']; ?>">
    <?php echo wp_get_attachment_image($brand['attachment_id'], 'home_brand', false, array('class' => 'img-responsive')); ?>
                <div class="blackbar">
                    <h2><?php echo $brand['title']; ?></h2>
                </div>
            </a>
        </div>
    <?php $i++;
}
?>
</section>
<section id="homenewsletter" class="container">
    <div class="col-md-24">
        <form role="form" class="newsform_send">
            <input type="email" required name="email" placeholder="Join the SoSensational Community" oninvalid="setCustomValidity('Please insert your email address')" /><button type="submit">Sign Up Now</button>
        </form>
    </div>
</section>
<section id="homeblog" class="container">
    <h1><span>From the Blog</span></h1>

    <?php
    $args = array(
        'post_type' => 'post',
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => 4,
    );
    $loop = new WP_Query($args);
    // The Loop
    $i = 1;
    if ($loop->have_posts()) :
        while ($loop->have_posts()) :
            $loop->the_post();
            ?>
            <?php
            if ($i == 1 || $i == 3) {
                echo '<div class="row">';
            }
            ?>
            <div class="col-md-12 <?php echo ($i == 2 || $i == 4 ? 'last' : ''); ?>">
                <div class="row">
                    <div class="col-xs-12 col-md-11">
                        <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('home-thumb', array('class' => 'img-responsive')); ?></a>
                    </div>
                    <div class="col-xs-12 col-md-13">
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <small><?php echo get_the_date('F d, Y'); ?></small>
                        <span class="hidden-portrait">
                            <?php the_excerpt(); ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php
            if ($i == 2 || $i == 4) {
                echo '</div>';
            }
            ?>
            <?php
            $i ++;
        endwhile; // end of the loop. 
    endif;
    wp_reset_postdata();
    ?>
</section>
<?php get_footer(); ?>