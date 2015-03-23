<?php
require_once 'inc/FeaturedCarousel/Carousel.php';

class FeaturedCarousel
{

    private $imgDimensions = array( 'width' => 366, 'height' => 240, 'crop' => true );
    private $currentCategory;
    private $allFeaturedAdvertisers = array();
    private $metaData = array();
    private $featuredAdvertisersIds = array();
    private $dataForDisplay = array();
    


    
    public function __construct($currentCategory)
    {
        $this->currentCategory = $currentCategory;
        $this->getAllFeaturedAdvertisers();
        $this->getAllAdvertisersMetaData();
        $this->getFeaturedAdvertisersInCurrentCategory();
        $this->getDataForDisplay();
        
        
    }
    
    private function getAllFeaturedAdvertisers()
    {
        $args = array(
            'numberposts'   =>  9,
            'post_type'     =>  array('brands', 'boutiques'),
            'meta_key'      =>  '_categories_featured',
        );

        $this->allFeaturedAdvertisers = get_posts($args);
        
    }
    
    private function getAllAdvertisersMetaData()
    {
        foreach ($this->allFeaturedAdvertisers as $featuredAdvertiser) {
            $this->metaData[$featuredAdvertiser->ID] = get_post_meta($featuredAdvertiser->ID, '_categories_featured', true);
        }           
        
    }
    
    private function getFeaturedAdvertisersInCurrentCategory()
    {
        
        if (!isset($this->metaData) || empty($this->metaData)) {
            return false;
        }        
        
        if ( ! in_array_r($this->currentCategory[0]->term_id, $this->metaData)) {
            return false;
        }           
        
        foreach($this->metaData as $key => $values) {
            foreach ($values as $value) {
                if ($value === $this->currentCategory[0]->term_id) {
                    $this->eaturedAdvertisersIds[] = $key;
                }
            }            
        }
    }

    private function getDataForDisplay()
    {
        $args = array(
            'post__in'  =>  $this->featuredAdvertisersIds,
        );
        
        $advertisersData = get_posts($args);        
        
        $this->dataForDisplay = $advertisersData;
        
        
    }

    public function displayCarousel()
    {
        new FeaturedCarousel\Carousel($this->dataForDisplay, $this->currentCategory);
    }
    
    
}