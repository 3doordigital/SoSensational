<?php
/*
Template Name: Brands and boutiques A-Z
*/

$tpl = new Template('azbrands&boutiques.php');
$queryArgs = array(
    'post_type' => array(
        'brands',
        'boutiques',
        'custom_advertisers'
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
    if(preg_match('/^[\d]/',ltrim($oneBrandOrBoutique->post_title),$matches)){
        $firstLetter = $matches[0];
    }elseif(preg_match('/^[\w]/',ltrim($oneBrandOrBoutique->post_title))){
        $firstLetter = strtoupper(substr($oneBrandOrBoutique->post_title, 0, 1));
    }else{
        $firstLetter = '.';
    }
    if (!key_exists(ltrim($firstLetter), $postsByLetters)) {
            $postsByLetters[$firstLetter] = [];
        }
    $postsByLetters[$firstLetter][] = $oneBrandOrBoutique;
}
$tpl->postsByLetters = $postsByLetters;
echo $tpl;