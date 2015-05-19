<?php 

get_header();
?>
<div class="container">
    <h1><span><?php the_title(); ?></span></h1>
    
    <div id="breadcrumbs">
        You Are Here:
		<span prefix="v: http://rdf.data-vocabulary.org/#">
			<span typeof="v:Breadcrumb">
                <a href="/" rel="v:url" property="v:title">Home</a>
            </span> / 
            <?php if( !isset( $wp_query->query_vars['shop-cat'] ) ) { ?>
            
            <?php if( is_page( 'shop/search ') ) {
					echo '
						  <span property="v:title">
							<a href="/shop/">Shop</a>
						  </span> / 
						  <span typeof="v:Breadcrumb">
                			<span class="breadcrumb_last" property="v:title">Search</span>
						  </span>';
					} else {
						echo '<span property="v:title">
							Shop
						  </span>';
					}
			?>
            <?php } else { ?>
            <span typeof="v:Breadcrumb">
                <a href="/shop/" rel="v:url" property="v:title">Shop</a>
            </span>
            
             / 
			
            <?php 
                $term = get_term_by( 'slug', $wp_query->query_vars['shop-cat'], 'wp_aff_categories' );
                //echo get_term_link( $term->term_id, 'wp_aff_categories' );

                $catname = $term->name;
                $catparent = $term->parent;
                if($catparent != '') {
                    $parent = get_term_by( 'id', $catparent, 'wp_aff_categories' );
                    $parentname = $parent->name;
                    $parentslug = $parent->slug;
                    $parentid = $parent->term_id;
                }
                if( isset( $parentid ) ) {
                    $subparent = get_term_by( 'id', $parentid, 'wp_aff_categories' ); 
                    $parentparent = $subparent->parent;
                    $subparentparent = get_term_by( 'id', $parentparent, 'wp_aff_categories' ); 
                    if( $parentparent != '' ) { 
                        $level3 = get_term_by( 'id', $parentparent, 'wp_aff_categories' ); 
                        if( $level3->parent != '' ) {
                            $level4 = get_term_by( 'id', $level3->parent, 'wp_aff_categories' );
                            ?>
                        <span typeof="v:Breadcrumb">
                            <a href="/shop/<?php echo $level4->slug; ?>/" rel="v:url" property="v:title"><?php echo $level4->name; ?></a>
                        </span>
            /
                        <?php 
                        }
                    ?>
                        <span typeof="v:Breadcrumb">
                            <a href="/shop/<?php echo $subparentparent->slug; ?>/" rel="v:url" property="v:title"><?php echo $subparentparent->name; ?></a>
                        </span>
            /  
                    <?php }
                }
            ?>
            <?php
                if( isset( $parent ) ) {
            ?>
            <span typeof="v:Breadcrumb">
                <a href="/shop/<?php echo $parentslug; ?>/" rel="v:url" property="v:title"><?php echo $parentname; ?></a>
            </span>
            /
            <?php } ?>
            <span typeof="v:Breadcrumb">
                <span class="breadcrumb_last" property="v:title"><?php echo str_replace('-', ' ', $wp_query->query_vars['shop-cat']); ?></span>
            </span>
            <?php } ?>
<!-- Endd breadcrumb code -->
		</span>
    </div>
</div>
<div class="container">
    <div class="row">        
        <div class="col-md-4">
            <button id="shop-controls-toggle" class="visible-xs">Toggle Search Filters</button>
            <?php dynamic_sidebar( 'shop_sidebar' ); ?> 
        </div>
        <div class="col-md-19 col-md-offset-1">
            <?php
                //$terms = get_terms('wp_aff_categories', array( 'orderby' => 'term_group', 'order'=>'DESC' ));
                //var_dump($terms);
                //print_var($wp_query->query_vars);
                if( isset( $wp_query->query_vars['shop-cat'] ) ) {
                    $term = get_term_by( 'slug', $wp_query->query_vars['shop-cat'], 'wp_aff_categories' );
                    if( $term->term_group < 2 ) {
                        $catID = $term->term_id;
                        $parent = $catID;
                    } else {
                        $catID = $term->term_group;
                        $parent = $catID;
                    }
                } elseif( isset( $wp_query->query_vars['shop-brand'] ) ) {
                    $term = get_term_by( 'slug', $wp_query->query_vars['shop-brand'], 'wp_aff_brands' );
                    $catID = $term->term_id;
                    $parent = $catID;
                } else {
                    $catID = 0;
                    $parent = '0';
                }
                
            ?>
            <div class="cat-intro">
            <?php if( $parent != 0 ) { ?>
            <h1><?php echo $term->name; ?></h1>
            
				<?php echo wpautop(htmlspecialchars_decode($term->description)); ?>
            <?php } elseif( $_SERVER['REQUEST_URI'] == '/shop/' ) {
				global $wp_aff;
				$option = $wp_aff->get_option();
				echo '<h1>'.( isset( $option['faceted']['home']['title'] ) ? $option['faceted']['home']['title'] : 'Shop' ).'</h1>';
							if( isset( $option['faceted']['home']['intro'] ) ) echo wpautop(htmlspecialchars_decode( $option['faceted']['home']['intro'] ));
			}
				if( isset( $wp_query->query_vars['shop-option']	) ) {
					global $wp_aff;
					$option = $wp_aff->get_option();
					switch ( $wp_query->query_vars['shop-option'] ) {
						case 'new' :
							echo '<h1>'.( isset( $option['faceted']['newin']['title'] ) ? $option['faceted']['newin']['title'] : 'New In' ).'</h1>';
							if( isset( $option['faceted']['newin']['intro'] ) ) echo wpautop(htmlspecialchars_decode( $option['faceted']['newin']['intro'] ));
							break;
						case 'sale' :
							echo '<h1>'.( isset( $option['faceted']['sale']['title'] ) ? $option['faceted']['sale']['title'] : 'Sale Items' ).'</h1>';
							if( isset( $option['faceted']['sale']['intro'] ) ) echo wpautop(htmlspecialchars_decode( $option['faceted']['sale']['intro'] ));
							break;
							
						case 'picks' :
							echo '<h1>'.( isset( $option['faceted']['picks']['title'] ) ? $option['faceted']['picks']['title'] : 'Top Picks' ).'</h1>';
							if( isset( $option['faceted']['picks']['intro'] ) ) echo wpautop(htmlspecialchars_decode( $option['faceted']['picks']['intro'] ));
							break;	
					}
				}
			?>
            </div>
            <div class="products">
                <?php
                    global $wp_aff;
					global $paged;
					$args = $wp_aff->shop_args();
					//print_var($args);
					$per_page = $args['posts_per_page'];
					$query = new WP_Query( $args );
					//print_var($query);
                    if( $args['paged'] == 1 || !isset( $args['paged'] ) ) {
                        $start = '1';
                    } else {
                        $start = ( ( $args['paged'] - 1 ) * $per_page ) + 1;
                    }
                    if( $per_page == -1 ) {
                        $end = $query->post_count;
                    } else {
                        $end = $args['paged'] * $per_page;
                    }
                    ?>
                        <div id="product_filter" class="row">
                            <div class="col-md-17">
                                <div id="product_count" class="col-md-12">
                    <?php
                    if( $query->found_posts == 0 ) { 
                        echo 'No Products';
                    } elseif( $query->found_posts != 0 && $query->found_posts < 12 ) {
                        echo 'Viewing '.$start.' - '.$query->found_posts.' of '.$query->found_posts.' Products';
                    } else {
                        echo 'Viewing '.$start.' - '.$end.' of '.$query->found_posts.' Products';
                    }
                    $i = 0;
                    ?>  
                                </div>
                                <div id="count_select" class="col-md-12">
                                    <?php
                                        $url18 = add_query_arg( 'per_page', 18, $_SERVER['REQUEST_URI'] );
                                    ?>
                                    View: 
                                    <?php if( $query->found_posts >= 18 ) { ?><a href="<?php echo add_query_arg( 'per_page', 18, $_SERVER['REQUEST_URI'] ); ?>">18</a> /<?php } ?>
                                    <?php if( $query->found_posts >= 36 ) { ?><a href="<?php echo add_query_arg( 'per_page', 36, $_SERVER['REQUEST_URI'] ); ?>">36</a> /<?php } ?>
                                    <a href="<?php echo add_query_arg( 'per_page', 'all', $_SERVER['REQUEST_URI'] ); ?>">ALL</a>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <select class="form-control" id="shop_sort">
                                    <option value="new" <?php echo( !isset( $_REQUEST['new'] ) || $_REQUEST['sortby'] == 'new' ? ' selected ' : '' ); ?>>Newest In</option>
                                    <option value="priceasc" <?php echo( isset( $_REQUEST['sortby'] ) && $_REQUEST['sortby'] == 'priceasc' ? ' selected ' : '' ); ?>>Sort by Price: Low to High</option>
                                    <option value="pricedesc" <?php echo( isset( $_REQUEST['sortby'] ) && $_REQUEST['sortby'] == 'pricedesc' ? ' selected ' : '' ); ?>>Sort by Price: High to Low</option>
                                    
                                </select>
                            </div>
                        </div>
                    <?php
					$n = 0;
                    foreach($query->posts AS $post) {
                        if($i == 0) {
                            echo '<div class="row">';
                        }
                        $post_meta = get_post_meta($post->ID);
                        $brand = wp_get_post_terms($post->ID, 'wp_aff_brands');
						$price = get_post_meta( $post->ID, 'wp_aff_product_price', true );
						$rrp = get_post_meta( $post->ID, 'wp_aff_product_rrp', true );
				
		
                        //print_var($post_meta);
						//print_var($brand);
                            echo '
                            <div class="col-md-8 product">';
							if( isset( $rrp ) && ( $price < $rrp ) ) {
								echo '<div class="product-sale"><span class="sr-only">Sale!</span></div>';
							} else {
								if( isset( $post_meta['wp_aff_product_picks'][0] ) && $post_meta['wp_aff_product_picks'][0] == 1 ) {
									echo '<div class="product-picks"><span class="sr-only">Hot Pick!</span></div>';
								} else {
									global $wp_aff;
									$options = $wp_aff->get_option();
									//print_var($options);
									$pastdate = strtotime('-'.$options['new_days'].' days');
									if ( $pastdate <= strtotime( $post->post_date ) ) {
										echo '<div class="product-new"><span class="sr-only">New In!</span></div>';
									}	
								}
							}
								
                            echo '        <div>
                                            <div>
                                                <a target="_blank" href="'.$post_meta['wp_aff_product_link'][0].'" title="'.$post->post_title.'"><img src="'.$post_meta['wp_aff_product_image'][0].'"></a>
                                            </div>
                                            <div class="row product-info">
                                                <div class="prod_title col-md-16">
                                                    <h3><a target="_blank" href="'.$post_meta['wp_aff_product_link'][0].'" title="'.$post->post_title.'">'.get_snippet($post->post_title,4).'...</a></h3>
                                                    <h4>'. ( isset( $brand[0]->name ) ? $brand[0]->name : '' ).'</h4>
							';
                            if ( current_user_can('edit_posts') ) {
							 	echo '<a class="edit_link" href="/wp-admin/admin.php?page=affiliate-shop/products&action=edit&referrer='.$_SERVER['REQUEST_URI'].'&product='.$post->ID.'">Edit</a>';
								echo '<a class="del_link" href="/wp-admin/admin.php?page=affiliate-shop/products&action=delete&referrer='.$_SERVER['REQUEST_URI'].'&product='.$post->ID.'">Delete</a>';
							}
							echo '           </div>
                                                <div class="prod_price col-md-8">
                                                    <div class="price">
                                                        <div class="amount">';
                                                        
														$wp_aff->product_price();
                                                        
														echo '</div>
                                                        <a target="_blank" href="'.$post_meta['wp_aff_product_link'][0].'" class="button">Shop Now</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                  </div>';
                        $i++;
						$n++;
                        if($i == 3 || $n == $query->post_count) {
							
                            echo '</div>';
                            $i = 0;
                        }
                    }
                ?>
                <?php wp_pagenavi( array( 'query' => $query ) ); ?>                
            </div>
        </div>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="advertisers-carousel">            
            <?php             
                /**
                 * If the term is the lowest level category nad has no children,
                 * display advertisers from the parent category
                 */
                if ( isset($term) ) {
                    $children = get_term_children($term->term_id, "wp_aff_categories");
                    if ( empty($children) && $term->parent != NULL) {
                        $term = get_term($term->parent, "wp_aff_categories");
                    }       
                    displayRelatedAdvertisersCarousel($term);                     
                }
            ?>
        </div>
    </div>
</div>

<?php get_footer();