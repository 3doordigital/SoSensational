<?php
require_once 'inc/FeaturedCarousel/Carousel.php';

class FeaturedCarousel
{

    private $imgDimensions = array( 'width' => 380, 'height' => 250, 'crop' => false );
    private $currentCategory;
    private $allFeaturedAdvertisers = array();
    private $metaData = array();
    private $featuredAdvertisersIds = array();
    private $dataForDisplay = array();
    
    public function __construct($currentCategory)
    {
        $this->currentCategory = $currentCategory;
        $this->collectAllFeaturedAdvertisers();
        $this->collectAllAdvertisersMetaData();
        $this->collectFeaturedAdvertisersInCurrentCategory();
        $this->collectDataForDisplay(); 
    }
    
    private function collectAllFeaturedAdvertisers()
    {
        $args = array(
            'numberposts'   =>  -1,
            'post_type'     =>  array('brands', 'boutiques'),
            'meta_key'      =>  '_categories_featured',
        );

        $this->allFeaturedAdvertisers = get_posts($args);             
        
    }
    
    private function collectAllAdvertisersMetaData()
    {
        foreach ($this->allFeaturedAdvertisers as $featuredAdvertiser) {
            $this->metaData[$featuredAdvertiser->ID] = get_post_meta($featuredAdvertiser->ID, '_categories_featured', true);
        }                         
    }
    
    private function collectFeaturedAdvertisersInCurrentCategory()
    {        
        
        if (!isset($this->metaData) || empty($this->metaData)) {
            return false;            
        }                 
        
        if ( ! in_array_r($this->currentCategory[0]->term_id, $this->metaData) && ! in_array($this->currentCategory[0]->term_id, $this->metaData)) {     
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

    private function collectDataForDisplay()
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
              'author' =>  $advertiserData->post_author,
              'ss_category' =>  $this->currentCategory[0]->slug              
            );             
            
            $featuredAdvertiserCategory = get_posts($args);            
            
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

    public function getCarousel()
    {
        return new FeaturedCarousel\Carousel($this->dataForDisplay);
    }
    
    
}