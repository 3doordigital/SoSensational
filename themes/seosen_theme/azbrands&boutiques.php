<?php
/*
Template Name: Brands and boutiques A-Z
*/

$tpl = new Template('azbrands&boutiques.php');
$queryArgs = array(
    'post_type' => array(
        'brands',
        'boutiques'
    ),
    'orderby' => 'title',
    'order' => 'ASC',
    'nopaging' => true,
    'post_status' => 'publish'
);
$wpQueryObj = new WP_Query($queryArgs);
$brandsAndBoutiques = $wpQueryObj->get_posts();
$postsByLetters = array();
foreach ($brandsAndBoutiques as $oneBrandOrBoutique) {
    $firstLetter = substr($oneBrandOrBoutique->post_title, 0, 1);
    if (!key_exists($firstLetter, $postsByLetters)) {
        $postsByLetters[$firstLetter] = [];
    }
    array_push($postsByLetters[$firstLetter], $oneBrandOrBoutique);
}
$tpl->postsByLetters = $postsByLetters;
echo $tpl;