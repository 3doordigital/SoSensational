<?php
namespace FeaturedCarousel;

class Carousel
{
    public function __construct($dataToDisplay, $currentCategory)
    {
        $this->displayCarousel($dataToDisplay, $currentCategory);
    }
    
    private function displayCarousel($dataToDisplay, $currentCategory)
    {
        var_dump($dataToDisplay);        
    }    
}