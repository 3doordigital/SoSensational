<?php
namespace RelatedCarousel;

class Carousel
{
    private $dataToDisplay;
    private $currentCategory;
    
    public function __construct($dataToDisplay, $currentCategory)
    {
        if (empty($dataToDisplay)) {
            return false;
        }        
        
        $this->dataToDisplay = $dataToDisplay;
        $this->currentCategory = $currentCategory;
    }    
    
    public function display()
    {
        if (empty($this->dataToDisplay)) {
            return false;
        }   
        
        echo '<hr>';

        if ( ! empty($this->dataToDisplay) ) {
            echo '<h1>See More ' . $this->currentCategory->name . ' in Brands & Boutiques</h1>';          
        }        
        
        echo '<div class="flexslider">';
            echo '<ul class="slides">';        
                foreach ($this->dataToDisplay as $singleBox) {
                    ?>
                    <li>
                        <div class='related-item ss_border'>
                            <a href='<?php echo get_site_url() . '/brands-and-boutiques/' . $singleBox['advertiser'][0]->post_name; ?>'>
                                <img src='<?php echo $singleBox['image']; ?>' />
                                <div class='title-bar'><h2><?php echo $singleBox['advertiser'][0]->post_title; ?></h2></div>
                            </a>           
                            <div class='related-description'>
                                <p><?php echo $this->truncateDescription($singleBox); ?></p>
                            </div>
                            <a href="<?php echo $singleBox['advertiserRedirectionLink']; ?>" class='button_ss large_ss'>Visit Website</a>
                        </div>     
                    </li>
                    <?php        
                }
            echo '</div>'; // .slides
        echo '</div>'; // .flexslider          
    }
    
    private function truncateDescription($singleBox)
    {
        if (strlen($singleBox['description']) > 180) {
            // truncate string
            $shortDescription = substr($singleBox['description'], 0, 180);
            // make sure the string ends in a word
            $singleBox['description'] = substr($shortDescription, 0, strrpos($shortDescription, ' '));     
        }
            $singleBox['description'] = trim($singleBox['description']);
            $singleBox['description'] = $singleBox['description']. ".. <a href='" . get_site_url() . '/brands-and-boutiques/' . $singleBox['advertiser'][0]->post_name . "'>Read more</a>";        
            
            
            return $singleBox['description'];
    }
}