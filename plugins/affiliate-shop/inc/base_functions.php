<?php
	if( !function_exists( 'print_var' ) ) {
		function print_var($var) {
			echo '<pre>'.print_r($var, true).'</pre>';   
		}
	}

    function get_snippet( $str, $wordCount = 10 ) {
      return implode( 
        '', 
        array_slice( 
          preg_split(
            '/([\s,\.;\?\!]+)/', 
            $str, 
            $wordCount*2+1, 
            PREG_SPLIT_DELIM_CAPTURE
          ),
          0,
          $wordCount*2-1
        )
      );
    }

    function sort_terms_hierarchicaly(Array &$cats, Array &$into, $parentId = 0)
    {
        foreach ($cats as $i => $cat) {
            if ($cat->parent == $parentId) {
                $into[$cat->term_id] = $cat;
                unset($cats[$i]);
            }
        }

        foreach ($into as $topCat) {
            $topCat->children = array();
            sort_terms_hierarchicaly($cats, $topCat->children, $topCat->term_id);
        }
    }

function get_meta_values( $key = '', $type = 'post', $status = 'publish' ) {

    global $wpdb;

    if( empty( $key ) )
        return;

    $r = $wpdb->get_col( $wpdb->prepare( "
        SELECT pm.meta_value FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = '%s' 
        AND p.post_status = '%s' 
        AND p.post_type = '%s'
    ", $key, $status, $type ) );

    return $r;
}

function get_cat_hierchy($parent,$args){
		$cats = get_categories($args);
		$ret = new stdClass;
		foreach($cats as $cat){
			if($cat->parent==$parent){
				$id = $cat->cat_ID;
				$ret->$id = $cat;
				$ret->$id->children = get_cat_hierchy($id,$args);
			}
		}
		return $ret;
	}