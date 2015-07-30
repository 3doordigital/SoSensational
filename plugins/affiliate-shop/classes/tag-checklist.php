<?php
class Walker_Tag_Checklist extends Walker {
    var $tree_type = 'tag';
    var $db_fields = array ('parent' => 'parent', 'id' => 'term_id');
    
    function __construct( $count ) {
        $this->counter  = $count;
    }
    
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}

	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}
    function start_el( &$output, $tax_term, $depth = 0, $args = array(), $id = 0) {
	//function start_el( &$output, $tax_term, $depth, $args, $id = 0 ) {
		extract($args);
		if ( empty($taxonomy) )
			$taxonomy = 'tag';

		if ( $taxonomy == 'tag' )
			$name = 'post_tag';
		else
			$name = $taxonomy.'['.$this->counter.'][]';
        if( $tax_term->term_group <= 1 ) {
            $class = in_array( $tax_term->term_id, $popular_cats ) ? ' class="popular-category"' : '';
            $output .= "\n<li id='{$taxonomy}-{$tax_term->term_id}'$class>";
			if($taxonomy == 'wp_aff_categories' && $depth == 0 ) {
				$output .= '<a href="#" class="drop_cats"><i class="fa fa-plus-square-o"></i> '.esc_html( apply_filters('the_category', $tax_term->name ) ).'</a>';
			} else {
				$output .='<label class="selectit"> ';
	
				if( ($this->counter === 'faceted' && $depth == 0 ) ) {
					$output .= ' ' . esc_html( apply_filters('the_category', $tax_term->name )) . '</label>';
				} else {
					$output .= '<input value="' . $tax_term->term_id . '" type="checkbox" ' . 'class="product_tag_checkbox_' . $tax_term->term_id . ' " ';
					//$output .= ' disabled ';
					$output .= ' name="'.$name.'" id="in-'.$taxonomy.'-' . $tax_term->term_id . '"' . checked( in_array( $tax_term->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters('the_category', $tax_term->name ));
					if( $tax_term->term_group >1 ) {
						$output .= ' (alias)';   
					}
					$output .= '</label>';
				}
            }
        }
	}

	function end_el( &$output, $tax_term, $depth = 0, $args = array() ) {
		if( $tax_term->term_group <= 1 ) {
            $output .= "</li>\n";
        }
	}
}
class Tag_Checklist {

    private $taxonomy;
    private $post_type;

	function __construct( $taxonomy, $count, $post_id = null, $selected = false ) {
		$this->taxonomy = $taxonomy;
		$this->counter = $count;
		$this->post_id = $post_id;
        $this->selected = array($selected);
        $this->metabox_content();
	}

	/**
	 * Generate metabox content
	 * @param  obj $post Post object
	 * @return void
	 */
	public function metabox_content(  ) {
        $taxonomy = $this->taxonomy;
        $tax = get_taxonomy( $taxonomy );
        $walker = new Walker_Tag_Checklist( $this->counter );
		?>
		<div id="taxonomy-<?php echo $taxonomy; ?>" class="categorydiv">
            
			<ul id="<?php echo $taxonomy; ?>-tabs" class="category-tabs">
				<li class="tabs"><a href="#<?php echo $taxonomy; ?>-all"><?php echo $tax->labels->all_items; ?></a></li>
			</ul>
                
		    <div id="<?php echo $taxonomy; ?>-all" class="tabs-panel">
                <input type="text" placeholder="Search" rel="<?php echo $taxonomy; ?>checklist" class="widefat searchList" style="margin-top: 5px;">
		       <input type="hidden" name="tax_input[<?php echo $taxonomy; ?>][]" value="0" />
		       <ul id="<?php echo $taxonomy; ?>checklist" data-wp-lists="list:<?php echo $taxonomy; ?>" class="categorychecklist form-no-clear">
					<?php wp_terms_checklist($this->post_id, array( 'taxonomy' => $taxonomy, 'walker' => $walker, 'checked_ontop' => false ) ) ?>
				</ul>
		   </div>
			
		</div>
		<?php
	}
}
class Sidebar_Checklist {

    private $taxonomy;
    private $post_type;

	function __construct( $taxonomy, $count ) {
		$this->taxonomy = $taxonomy;
		$this->counter = $count;
        
        $this->metabox_content();
	}

	/**
	 * Generate metabox content
	 * @param  obj $post Post object
	 * @return void
	 */
	public function metabox_content(  ) {
        $taxonomy = $this->taxonomy;
        $tax = get_taxonomy( $taxonomy );
        $walker = new Walker_Tag_Checklist( $this->counter );
		?>
		       <ul id="<?php echo $taxonomy; ?>_search" data-wp-lists="list:<?php echo $taxonomy; ?>" class="facetedCategory form-no-clear">
					<?php wp_terms_checklist(null, array( 'taxonomy' => $taxonomy, 'walker' => $walker ) ) ?>
				</ul>
		   
		<?php
	}
}