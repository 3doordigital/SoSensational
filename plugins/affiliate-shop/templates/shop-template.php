<?php 

get_header();
?>
<div class="container">
    <h1><span><?php the_title(); ?></span></h1>
    
    <div id="breadcrumbs">
        You Are Here:
		<span prefix="v: http://rdf.data-vocabulary.org/#">
			<span typeof="v:Breadcrumb">
                <a href="http://sosen.3doordigital.com" rel="v:url" property="v:title">Home</a>
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
            <?php dynamic_sidebar( 'shop_sidebar' ); ?> 
        </div>
        <div class="col-md-19 col-md-offset-1">
            <?php
                $terms = get_terms('wp_aff_categories', array( 'orderby' => 'term_group', 'order'=>'DESC' ));
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
            <?php if( $parent != 0 ) { ?>
            <h1><?php echo $term->name; ?></h1>
            <?php echo wpautop(htmlspecialchars_decode($term->description)); ?>
            <?php } ?>
            <div class="products">
                <?php
                    global $wp_aff;
					$args = $wp_aff->shop_args();
					//print_var($args);
					$per_page = $args['posts_per_page'];
					$query = new WP_Query( $args );
                    if( $paged == 1 ) {
                        $start = '1';
                    } else {
                        $start = ( ( $paged - 1 ) * $per_page ) + 1;
                    }
                    if( $per_page == -1 ) {
                        $end = $query->post_count;
                    } else {
                        $end = $paged * $per_page;
                    }
                    ?>
                        <div id="product_filter" class="row">
                            <div class="col-md-17">
                                <div id="product_count" class="col-md-12">
                    <?php
                    if( $query->post_count == 0 ) { 
                        echo 'No Products';
                    } elseif( $query->post_count != 0 && $query->post_count < 12 ) {
                        echo 'Viewing '.$start.' - '.$query->post_count.' of '.$query->post_count.' Products';
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
                                    View: <a href="<?php echo add_query_arg( 'per_page', 18, $_SERVER['REQUEST_URI'] ); ?>">18</a>/<a href="<?php echo add_query_arg( 'per_page', 36, $_SERVER['REQUEST_URI'] ); ?>">36</a>/<a href="<?php echo add_query_arg( 'per_page', 'all', $_SERVER['REQUEST_URI'] ); ?>">ALL</a>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <select class="form-control">
                                    <option>Sort by Price: Low to High</option>
                                    <option>Sort by Price: High to Low</option>
                                    <option>Sort by Name: A-Z</option>
                                    <option>Sort by Name: Z-A</option>
                                </select>
                            </div>
                        </div>
                    <?php
					
                    foreach($query->posts AS $post) {
                        if($i == 0) {
                            echo '<div class="row">';
                        }
                        $post_meta = get_post_meta($post->ID);
                        $brand = wp_get_post_terms($post->ID, 'wp_aff_brands');
                        //print_var($brand);
                            echo '
                            <div class="col-md-8 product">
                                        <div>
                                            <div>
                                                <img src="'.$post_meta['wp_aff_product_image'][0].'">
                                            </div>
                                            <div class="row product-info">
                                                <div class="prod_title col-md-17">
                                                    <h3><a href="'.$post_meta['wp_aff_product_link'][0].'" title="'.$post->post_title.'">'.get_snippet($post->post_title,4).'...</a></h3>
                                                    <h4>'.$brand[0]->name.'</h4>
                                                </div>
                                                <div class="prod_price col-md-7">
                                                    <div class="price">
                                                        <div class="amount">&pound;'.
                                                            $post_meta['wp_aff_product_price'][0].
                                                        '</div>
                                                        <a href="'.$post_meta['wp_aff_product_link'][0].'" class="button">Shop Now</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                  </div>';
                        $i++;
                        if($i == 3) {
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
                if ( isset($term) ) {
                    displayRelatedAdvertisersCarousel($term);                     
                }
            ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>