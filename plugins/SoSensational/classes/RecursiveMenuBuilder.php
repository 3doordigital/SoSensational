<?php
/*
 * A menu builder for the ss_category edit admin page - "Categories in the shop"
 * 
 * This is a menu with multiple, nested checkbox fields that show categories
 * from the affiliate shop plugin. Thanks to this menu ss_categories can be 
 * corelated with ss_aff_categories so that RelatedCarousel would display
 * advertisers from Brands and Boutiques under the corresponding category in 
 * the shop
 * 
 * @author Łukasz Tarasiewicz <lukasza.tarsiewicz@polcode.net>
 * @data April 2015
 * 
 */

class RecursiveMenuBuilder
{
    
    private $sortedArray;
    private $termMeta;
    
    public function __construct($sortedArray, $termMeta)
    {
        $this->sortedArray = $sortedArray;
        $this->termMeta = $termMeta;
    }
    
    public function display()
    {
        array_walk_recursive($this->sortedArray, array($this, 'buildTree'));
    }
    
    private function buildTree($value, $key)
    {
        $checked = checkIfSelected($value->term_id, $this->termMeta) ? 'checked' : '';
        echo '<div class="pull-left" style="width: 250px;">';
            echo '<input type="checkbox" value="' . $value->term_id . '" name="term_meta[ss_aff_categories][]"' . $checked . '>' . $value->name . '<br />';
            if (is_array($value->children)) {
                $this->attachChildren($value->children);
            }       
            echo '<br />';
        echo '</div>';
    }
    
    private function attachChildren($items)
    {        
        echo "&nbsp;&nbsp;";
        foreach ($items as $item) {
            $checked = checkIfSelected($item->term_id, $this->termMeta) ? 'checked' : '';
            echo '<input type="checkbox" value="' . $item->term_id . 
                    '" name="term_meta[ss_aff_categories][]"' . $checked . '>' . $item->name . ' ';
            if (is_array($item->children)) {
                echo "<br />" . "&nbsp;&nbsp;";
                $this->attachChildren($item->children);
            }
        }        
    }
}