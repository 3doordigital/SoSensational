<?php

/**
 * Helper function for the SoSensational plugin
 * 
 * @author Lukasz Tarasiewicz <lukasz.tarasiewicz@polcode.net>
 * @data March 2015
 */


/**
 * A function that outputs custom system messages based on the URL query var
 * 
 * @return string
 */
function displaySystemNotice()
{
    $actionStatus = isset($_GET['adminmsg']) ? $_GET['adminmsg'] : '';
    
    if ( empty($actionStatus) ) {
        return;
    }
    
    if ( $actionStatus === 's' ) {
        $displayMessage =  'You have successfully saved a product.';
        $alertClass = 'success';
    } elseif ( $actionStatus === 'f' )         {
        $displayMessage =  'Something went wrong when saving a product. Please try again.';
        $alertClass = 'warning';
        
    } elseif ($actionStatus === 'd') {
        $displayMessage =  'A product has been deleted.';
        $alertClass = 'success';        
    }
        
    return "<div class='alert alert-$alertClass' role='alert'>$displayMessage</div>";
    
}

/**
 * A function that checks checkboxes by comparing the current value to the values
 * saved in the database.
 * 
 * This function does the sama what the built-in 'checked()' function does,
 * but it works with multidimensional arrays
 * 
 * @param integer $currentCategoryId Current value of the checkbox
 * @param array $selectedCategories All selected categories
 * @return boolean
 */
function checkIfSelected($currentCategoryId, $selectedCategories)
{
    if (is_array($selectedCategories)) {
        if (in_array($currentCategoryId, $selectedCategories)) {
            return true;
        }           
    }
    return false;
}

/**
 * Make the default in_array() function recursive
 * 
 * @author jwueller
 * @source http://stackoverflow.com/questions/4128323/in-array-and-multidimensional-array
 * 
 * @param mixed $needle
 * @param array $haystack
 * @param bool $strict
 * @return boolean
 */
function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

function truncateDescription($description, $slug)
{
    if (strlen($description) > 180) {
        // truncate string
        $shortDescription = substr($description, 0, 180);
        // make sure the string ends in a word
        $description = substr($shortDescription, 0, strrpos($shortDescription, ' '));     
    }
        $description = trim($description);
        $description = $description. ".. <a href='" . get_site_url() . '/brands-and-boutiques/' . $slug . "'>Read more</a>";        


        return $description;        
}

function isPreview($array) 
{
    if (key_exists('preview', $array)) {
        return true;
    }    
    return false;
}

/**
 * Remove published categories with the term ID that a user unchecks on Step 1
 * 
 * @param integer $post_id ID of the current post needed for retrieving the author
 * @param array $add_cats Categories that will be saved for the current advertiser
 */
function removeCategoryPostOnCategoryUnselect($post_id, $add_cats) 
{
    $currentPost = get_post($post_id);

    $arguments = array(
        'post_type' =>  'advertisers_cats',
        'status'    =>  'publish',
        'author'    =>  $currentPost->post_author,
    );
    
    $publishedCategories = get_posts($arguments); 
    
    foreach($publishedCategories as $publishedCategory) {
        $currentTerm = wp_get_post_terms($publishedCategory->ID, 'ss_category');
        $publishedCategory->term_id = $currentTerm[0]->term_id;
        $publishedCategoriesWithTerms[] = $publishedCategory;
    }    
    
    
    foreach($publishedCategoriesWithTerms as $publishedCategoryWithTerm) {
        if ( ! in_array($publishedCategoryWithTerm->term_id, $add_cats)) {
            wp_delete_post($publishedCategoryWithTerm->ID);
        }
    }    
}

/**
 * Append 'ss_cat_priority' key to each category. Then sort the categories
 * from highest to lowest by the value of 'ss_cat_priority'
 */
function sortCategoriesByPriority($categories)
{
    foreach($categories as $singleCategory) {
        $singleCategoriesMeta = get_option( "taxonomy_$singleCategory->term_id" );
        $priority = isset($singleCategoriesMeta['ss_cat_priority']) ? $singleCategoriesMeta['ss_cat_priority'] : 20;
        $ssAffCategories = isset($singleCategoriesMeta['ss_aff_categories']) ? $singleCategoriesMeta['ss_aff_categories'] : false;
        $singleCategory->ss_cat_priority = $priority;
        $singleCategory->ss_aff_categories = $ssAffCategories;
        $categoriesWithPriority[] = $singleCategory;
        
    }    
    usort($categoriesWithPriority, function($a, $b) {
       return $a->ss_cat_priority - $b->ss_cat_priority;
    });   
 
    return $categoriesWithPriority;
}

/**
 * Find children of each memebr of the array recursively and build a tree structure
 * 
 * @link http://stackoverflow.com/questions/29415723
 * @author kainaw
 * 
 * @param array $shopCategories Input array - unsorted
 * @param type $parent Parent ID to check the current member against
 * @return array
 */
function sortShopCategories(&$shopCategories, $parent = 0)
{
    $tmp_array = array();
    foreach($shopCategories as $obj)
    {
        if($obj->parent == $parent)
        {
            // The next line adds all children to this object
            $obj->children = sortShopCategories($shopCategories, $obj->term_id);
            $tmp_array[] = $obj;
        }
    }

    // You *could* sort the temp array here if you wanted.
    return $tmp_array;   
}

/**
 * Check if the currently displayed category has and advertisers assigned to it.
 * 
 * Later, display the category on the listing page only if there are advertsiers
 * assigned to it.
 * 
 * @param WP_Post $category Currently displayed category
 * @return boolean
 */
function hasAdvertisers($category)
{
    $args = array(
        'post_type' => array('brands', 'boutiques'),
        'post_status'   =>  'publish',
        'ss_category'   =>  $category->slug,
        'posts_per_page'    =>  1
    );
    if (get_posts($args)) {
        return true;
    }
    return false;
}

/**
 * Pull the existing query and add a custom post type.
 * 
 * Fixes the menu on the competitions page
 * 
 * @global string $query_string
 * @source https://wordpress.org/support/topic/wp-nav-menu-dissapears-in-category-pages-1?replies=15#post-1859168
 */
function fixMenuOnCompetitionsPage()
{
    global $query_string;    
    parse_str($query_string, $args);           
    if ($args['post_type'] === 'wp_comp_man') {
        $args['post_type'] = array('wp_comp_man');
        query_posts( $args );    
        return true;
    }
    return false;

}