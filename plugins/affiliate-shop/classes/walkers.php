<?php
class Faceted_Category_Walker extends Walker {
	/**
	 * What the class handles.
	 *
	 * @see Walker::$tree_type
	 * @since 2.1.0
	 * @var string
	 */
	public $tree_type = 'category';

	/**
	 * Database fields to use.
	 *
	 * @see Walker::$db_fields
	 * @since 2.1.0
	 * @todo Decouple this
	 * @var array
	 */
	public $db_fields = array ('parent' => 'parent', 'id' => 'term_id');

	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker::start_lvl()
	 *
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. Will only append content if style argument value is 'list'.
	 *                       @see wp_list_categories()
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		if ( 'list' != $args['style'] )
			return;
		$indent = str_repeat("\t", $depth);
        $output .= "$indent<ul class='children'>\n"; 
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker::end_lvl()
	 *
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. Will only append content if style argument value is 'list'.
	 *                       @wsee wp_list_categories()
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		if ( 'list' != $args['style'] )
			return;

		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @since 2.1.0
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category Category data object.
	 * @param int    $depth    Depth of category in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_list_categories()
	 * @param int    $id       ID of the current category.
	 */
	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		/** This filter is documented in wp-includes/category-template.php */
		$cat_name = apply_filters(
			'list_cats',
			esc_attr( $category->name ),
			$category
		);
		global $wp_query;
		//print_var($wp_query);
		if( isset( $wp_query->query_vars['shop-brand'] ) ) {
			$link = '<a class="wp_aff_cat_link" href="/shop/search/?brand='. $wp_query->query_vars['shop-brand'] .'&category='. $category->slug .'" rel="'.$category->term_id.'" ';
		} elseif( isset( $_REQUEST['brand'] ) ) {
			$link = '<a class="wp_aff_cat_link" href="/shop/search/?brand='. $_REQUEST['brand'] .'&category='. $category->slug .'" rel="'.$category->term_id.'" ';
		} elseif( isset( $wp_query->query_vars['pagename'] ) && $wp_query->query_vars['pagename'] == 'search' ) {
			$url = add_query_arg( 'category', $category->slug, $_SERVER['REQUEST_URI']  );
			$link = '<a class="wp_aff_cat_link" href="' . $url .'" rel="'.$category->term_id.'" ';
		} else {
			$link = '<a class="wp_aff_cat_link" href="' . esc_url( '/shop/'. $category->slug.'/' ) . '" rel="'.$category->term_id.'" ';
		}
		
        
		$link .= '>';
		$link .= ucwords(strtolower($cat_name)) . '</a>';

		if ( ! empty( $args['feed_image'] ) || ! empty( $args['feed'] ) ) {
			$link .= ' ';

			if ( empty( $args['feed_image'] ) ) {
				$link .= '(';
			}

			$link .= '<a href="' . esc_url( get_term_feed_link( $category->term_id, $category->taxonomy, $args['feed_type'] ) ) . '"';

			if ( empty( $args['feed'] ) ) {
				$alt = ' alt="' . sprintf(__( 'Feed for all posts filed under %s' ), $cat_name ) . '"';
			} else {
				$alt = ' alt="' . $args['feed'] . '"';
				$name = $args['feed'];
				$link .= empty( $args['title'] ) ? '' : $args['title'];
			}

			$link .= '>';

			if ( empty( $args['feed_image'] ) ) {
				$link .= $name;
			} else {
				$link .= "<img src='" . $args['feed_image'] . "'$alt" . ' />';
			}
			$link .= '</a>';

			if ( empty( $args['feed_image'] ) ) {
				$link .= ')';
			}
		}

		if ( ! empty( $args['show_count'] ) ) {
			$link .= ' (' . number_format_i18n( $category->count ) . ')';
		}
		
        if ( 'list' == $args['style'] ) {
			$output .= "\t<li";
			$class = 'cat-item cat-item-' . $category->term_id;
			if ( ! empty( $args['current_category'] ) ) {
				$_current_category = get_term( $args['current_category'], $category->taxonomy );
				if( $category->taxonomy == 'wp_aff_categories' ) {
                    if( $_parent_category = get_term( $_current_category->parent , $category->taxonomy ) ) {
                       if( isset( $_parent_category->parent) && $category->term_id == $_parent_category->parent ) {
                            $class .= ' cat-show ';  
                        } 
                    }
                    if( isset( $_parent_category->parent) && $_grandparent_category = get_term( $_parent_category->parent , $category->taxonomy ) ) {
                        if( isset( $_grandparent_category->parent ) && $_grandparent_category->parent ==  $category->term_id  ) {
                           $class .= ' cat-show ';  
                        }
                    }
                
                //print_var($_current_category);
                //print_var($_parent_category);
                    if ( $category->term_id == $_current_category->parent ) {
                        $class .=  ' current-cat-parent';
                    }
                }
				if ( $category->term_id == $args['current_category'] ) {
					$class .=  ' current-cat';
                }
                
			} elseif( isset( $_REQUEST['category'] ) && $category->slug == $_REQUEST['category'] ) {
				$class .=  ' current-cat';
			}
			$output .=  ' class="' . $class . '"';
			$output .= ">$link\n";
		} else {
			$output .= "\t$link<br />\n";
		}
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @see Walker::end_el()
	 *
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page   Not used.
	 * @param int    $depth  Depth of category. Not used.
	 * @param array  $args   An array of arguments. Only uses 'list' for whether should append to output. @see wp_list_categories()
	 */
	public function end_el( &$output, $page, $depth = 0, $args = array() ) {
		if ( 'list' != $args['style'] )
			return;

		$output .= "</li>\n";
	}

}

class Faceted_Brand_Walker extends Walker {
	/**
	 * What the class handles.
	 *
	 * @see Walker::$tree_type
	 * @since 2.1.0
	 * @var string
	 */
	public $tree_type = 'category';

	/**
	 * Database fields to use.
	 *
	 * @see Walker::$db_fields
	 * @since 2.1.0
	 * @todo Decouple this
	 * @var array
	 */
	public $db_fields = array ('parent' => 'parent', 'id' => 'term_id');

	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker::start_lvl()
	 *
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. Will only append content if style argument value is 'list'.
	 *                       @see wp_list_categories()
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		if ( 'list' != $args['style'] )
			return;
		$indent = str_repeat("\t", $depth);
        $output .= "$indent<ul class='children'>\n"; 
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker::end_lvl()
	 *
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. Will only append content if style argument value is 'list'.
	 *                       @wsee wp_list_categories()
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		if ( 'list' != $args['style'] )
			return;

		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @since 2.1.0
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category Category data object.
	 * @param int    $depth    Depth of category in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_list_categories()
	 * @param int    $id       ID of the current category.
	 */
	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		/** This filter is documented in wp-includes/category-template.php */
		$cat_name = apply_filters(
			'list_cats',
			esc_attr( $category->name ),
			$category
		);
		
		global $wp_query;
		//print_var($wp_query);
		/*
		if( isset( $wp_query->query_vars['shop-cat'] ) ) {
			$link = '<a class="wp_aff_cat_link" href="/shop/search/?category='. $wp_query->query_vars['shop-cat'] .'&brand='. $category->slug .'" rel="'.$category->term_id.'" ';
		} elseif( isset( $wp_query->query_vars['pagename'] ) && $wp_query->query_vars['pagename'] == 'search' ) {
			$url = add_query_arg( 'brand', $category->slug, $_SERVER['REQUEST_URI']  );
			$link = '<a class="wp_aff_cat_link" href="' . $url .'" rel="'.$category->term_id.'" ';
		} else {
			$link = '<a class="wp_aff_cat_link" href="' . esc_url( '/shop/brand/'. $category->slug.'/' ) . '" rel="'.$category->term_id.'" ';
		}
		$link .= '>';
		$link .= ucwords(strtolower($cat_name)) . '</a>';
		*/
        
		$link = '<input type="checkbox" ';
		if( isset( $_REQUEST['brand'] ) ) {
			$brands = explode( ',', $_REQUEST['brand'] );
			foreach( $brands as $brand ) {
				if( $brand == $category->slug ) {
					$link .= ' checked ';
				}
			}
		}
		$link .= 'class="brand_check" value="'.$category->slug.'" name="brands[]"> <label>'.ucwords(strtolower($cat_name)).'</label>';
		
        if ( 'list' == $args['style'] ) {
			$output .= "\t<li";
			$class = 'cat-item cat-item-' . $category->term_id;
			if ( ! empty( $args['current_category'] ) ) {
				$_current_category = get_term( $args['current_category'], $category->taxonomy );
				if( $category->taxonomy == 'wp_aff_categories' ) {
                    if( $_parent_category = get_term( $_current_category->parent , $category->taxonomy ) ) {
                       if( isset( $_parent_category->parent) && $category->term_id == $_parent_category->parent ) {
                            $class .= ' cat-show ';  
                        } 
                    }
                    if( isset( $_parent_category->parent) && $_grandparent_category = get_term( $_parent_category->parent , $category->taxonomy ) ) {
                        if( isset( $_grandparent_category->parent ) && $_grandparent_category->parent ==  $category->term_id  ) {
                           $class .= ' cat-show ';  
                        }
                    }
                
                //print_var($_current_category);
                //print_var($_parent_category);
                    if ( $category->term_id == $_current_category->parent ) {
                        $class .=  ' current-cat-parent';
                    }
                }
				
				if ( $category->term_id == $args['current_category']  ) {
					$class .=  ' current-cat';
                }
                
			} elseif( isset( $_REQUEST['brand'] ) && $category->slug == $_REQUEST['brand'] ) {
				$class .=  ' current-cat';
			}
			$output .=  ' class="' . $class . '"';
			$output .= ">$link\n";
		} else {
			$output .= "\t$link<br />\n";
		}
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @see Walker::end_el()
	 *
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page   Not used.
	 * @param int    $depth  Depth of category. Not used.
	 * @param array  $args   An array of arguments. Only uses 'list' for whether should append to output. @see wp_list_categories()
	 */
	public function end_el( &$output, $page, $depth = 0, $args = array() ) {
		if ( 'list' != $args['style'] )
			return;

		$output .= "</li>\n";
	}

}