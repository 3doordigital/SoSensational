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

function buildMenuRecursively($sortedArray, $termMeta)
{
    $menu = new RecursiveMenuBuilder($sortedArray, $termMeta);
    $menu->display();
}