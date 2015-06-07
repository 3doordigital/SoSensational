<?php

/**
 * Helper function for the SoSensational plugin
 * 
 * @author Lukasz Tarasiewicz <lukasz.tarasiewicz@polcode.net>
 * @data March 2015
 */

function displayRelatedAdvertisersCarousel($currentCategory)
{
    $fcarousel = new RelatedCarousel($currentCategory);
    $carousel = $fcarousel->getCarousel();
    $carousel->display();
}

function displayFeaturedAdvertisers($currentCategory) 
{
    $fcarousel = new FeaturedCarousel($currentCategory);
    $carousel = $fcarousel->getCarousel();
    $carousel->display();
}

function produceMenu($items, $termMeta, $level = 0)
{
   $r = '' ;  
   foreach ( $items as $item ) {
       $checked = checkIfSelected($item->term_id, $termMeta) ? 'checked' : '';
       
       if ($item->parent == $level ) {
          $r = $r . "<li>" . '<input type="checkbox" value="'. $item->term_id .'" name="term_meta[ss_aff_categories][]" ' . $checked . '>' . $item->name . produceMenu( $items, $termMeta, $item->term_id ) . "</li>";
       }       
   }
   
   return ($r==''?'':"<ol class='aff-categories-list'>". $r . "</ol>");    
}