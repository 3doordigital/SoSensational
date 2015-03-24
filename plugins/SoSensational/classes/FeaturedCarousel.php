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
               
        foreach ($this->metaData as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    if ($v === $this->currentCategory[0]->term_id) {
                        $this->featuredAdvertisersIds[] = $key;
                    }
                }            
            } 
        }    
        
    }

    private function getDataForDisplay()
    {        
        $ids = empty($this->featuredAdvertisersIds) ? array(0) : $this->featuredAdvertisersIds;

        $args = array(
            'post_type' => array('brands', 'boutiques'),
            'post__in'  =>  $ids,
        );

        $advertisersData = get_posts($args);  


        foreach ($advertisersData as $advertiserData) {
            
            $args = array(      
              'post_type'   =>  array('advertisers_cats'),
              'post_status' =>  array('publish', 'draft'),
              'ss_category' =>  $this->currentCategory[0]->name,
              'author' =>  $advertiserData->post_author
            );             
            
            $featuredAdvertiserCategory = get_posts($args);
            
            var_dump($featuredAdvertiserCategory);
            
            if( ! isset ($featuredAdvertiserCategory[0]->ID)) {                    
                continue;
            }            
            
            $this->dataForDisplay[$advertiserData->post_title]['post_name'] = $advertiserData->post_name;
            $this->dataForDisplay[$advertiserData->post_title]['post_title'] = $advertiserData->post_title;            
            $this->dataForDisplay[$advertiserData->post_title]['image'] = bfi_thumb( get_post_meta( $featuredAdvertiserCategory[0]->ID, 
                                                                                    'ss_advertisers_cats_image', true ), 
                                                                                    $this->imgDimensions 
                                                                            );
            $this->dataForDisplay[$advertiserData->post_title]['description'] = get_post_meta($featuredAdvertiserCategory[0]->ID,
                                                                                            'ss_advertisers_cats_description', 
                                                                                            true 
                                                                                );   
            $this->dataForDisplay[$advertiserData->post_title]['advertiser_redirection_link'] = get_post_meta($featuredAdvertiserCategory[0]->ID, 
                                                                                                'ss_advertisers_cats_link', 
                                                                                                true 
                                                                                                ); 
        }     
    }

    public function displayCarousel()
    {
        new FeaturedCarousel\Carousel($this->dataForDisplay);
    }
    
    
}