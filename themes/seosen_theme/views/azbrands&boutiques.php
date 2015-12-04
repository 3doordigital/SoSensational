<?php get_header(); ?>

<div class="container">
    <div class="row">
        <div class="col-md-24" id="content">
            <?php
            do_action('ss_css');
            ?>
            <h1><span><?php echo get_the_title(); ?></span></h1>
            <?php
            if (function_exists('yoast_breadcrumb')) {
                yoast_breadcrumb('<div id="breadcrumbs">', '</div>');
            }
            ?>
            <?php foreach ($postsByLetters as $letter => $posts): ?>
                <div class="category_ss_title_under">
                    <span class="left_ss"> </span>

                    <p class="ss_description category_ss">

                        <b class=""><?php echo $letter; ?></b>
                    </p>

                    <div class="ss_clear"></div>
                    <?php foreach ($posts as $onePost): ?>
                        <div class="col-md-4">
                            <?php var_dump($onePost); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php get_footer(); ?>
