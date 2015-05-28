<?php get_header(); ?>
<?php
//    $shopQueryArgs = SearchEngine::queryShop();    
//    $shopQuery = new WP_Query($shopQueryArgs);

    global $wp_query;
    $wp_query->set('post_type', 'boutiques');
    $myQuery = $wp_query;
    var_dump($wp_query);
    if ($myQuery->have_posts()) {
        echo '<ul>';
        while($myQuery->have_posts()) {
            $myQuery->the_post();
            echo '<li>' . get_the_title() . '</li>';
        }
        echo '</ul>';
    }
?>




<?php get_footer();