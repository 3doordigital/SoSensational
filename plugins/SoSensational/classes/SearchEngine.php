<?php

add_action('pre_get_posts', 'initializeSearchEngine');

function initializeSearchEngine($mainQuery)
{          
    
    if ($mainQuery->is_search) {
            $mainQuery->set('post_type', 'brands');
            $mainQuery->set('posts_per_page', 1);

    }
}

class SearchEngine
{
    public static $mainQuery;
    
    public function __construct($mainQuery) 
    {        
        self::$mainQuery = $mainQuery;
    }
    
    static function queryShop()
    {
        $shopQuery = self::$mainQuery;
        $shopQuery->set('post_type', 'brands');
        $shopQuery->set('posts_per_page', 1);
        return $shopQuery;

    }
    
}