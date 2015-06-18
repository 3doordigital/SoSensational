<?php get_header(); ?>
<?php 
    $nextPage = get_query_var('page-no') + 1;
    $previousPage = get_query_var('page-no') - 1;
    $section = $shop ? 'shop' : 'brands';
?>

<?php if (isset($products)) : ?>
<div class="container">
    <div class="row">
        <div class="col-24-sm">
            <div class="search-section products">                
                <div class="search-section-head">
                    <h1>Results from <?php if($shop) : ?>our Shop:<?php else : ?>Brands & Boutiques<?php endif; ?></h1>
                </div>
                <div class="search-results-section">
                    <?php
                    if ($products) :
                    foreach($products as $product) {

                    ?>
                        <div class="col-md-8 product">        
                            <div>
                                <a target="_blank" href="<?php echo $product['link'] ?>" title="<?php echo $product['title'] ?>"><img src="<?php echo $product['picture'] ?>"></a>
                            </div>
                            <div class="row product-info">
                                <div class="prod_title col-md-16">
                                    <h3><a target="_blank" href="<?php echo $product['link'] ?>" title="<?php echo $product['title'] ?>"><?php echo get_snippet($product['title'], 4)  . '...' ?></a></h3>
                                    <h4><?php echo $product['brand'] ?></h4>
                                </div>
                                <div class="prod_price col-md-8">
                                    <div class="price">
                                        <div class="amount">&pound;<?php echo $product['price'] ?></div>
                                        <a target="_blank" href="<?php echo $product['link'] ?>" class="button">Shop Now</a>
                                    </div>
                                </div>
                            </div>
                        </div> 
                    <?php                    
                    }     
                    ?>
                    <div class="clearfix"></div>
                    <div class="pagination">
                        <?php if (get_query_var('page-no') > 1) : ?>
                        <a href="<?php echo esc_url(get_permalink(get_page_by_title('Search Results')) . $section . '/' . get_query_var('search-term')) . '/' . $previousPage ?>"><< Previous results</a>   
                        <?php endif; ?>
                        <?php if (get_query_var('page-no') < $max_num_pages) : ?>
                        <a href="<?php echo esc_url(get_permalink(get_page_by_title('Search Results')) . $section . '/' . get_query_var('search-term')) . '/' . $nextPage ?>">Next results >></a>                     
                        <?php endif; ?>
                    </div>                        
                    <?php
                    else:
                        echo '<p>There were no results found</p>';
                    endif;
                    ?>            
                </div>                       
            </div>               
        </div>
    </div>  
</div>    
<?php elseif (isset($posts)) : ?>
<div class="container">
    <div class="row">
        <div class="col-24-xs">
            <div class="search-section products">                
                <div class="search-section-head">
                    <h1>Results from our Blog:</h1>
                </div>
                <div class="search-results-section">
                    <?php
                    if ($posts) :
                    $i = 1;
                    foreach($posts as $post) {
                    ?>
                        <div class="col-sm-8 blog-small">
                            <h2><a href="<?php echo $post['link'] ?>"><?php echo $post['title'] ?></a></h2>
                            <?php echo $blogPost['meta'] ?>
                                    <img width="<?php echo $post['thumbnail'][1] ?>" height="<?php echo $post['thumbnail'][2] ?>" src="<?php echo $post['thumbnail'][0] ?>" class="img-responsive wp-post-image" alt="<?php echo $post['alt-text'] ?>">
                            <?php echo $post['exerpt'] ?>                           
                        </div>
                    <?php  
                    $i++;
                    }     
                    ?>
                    <div class="clearfix"></div>
                    <div class="pagination">
                        <?php if (get_query_var('page-no') > 1) : ?>
                        <a href="<?php echo esc_url(get_permalink(get_page_by_title('Search Results')) . 'blog/' . get_query_var('search-term')) . '/' . $previousPage ?>"><< Previous results</a>   
                        <?php endif; ?>
                        <?php if (get_query_var('page-no') < $max_num_pages) : ?>
                        <a href="<?php echo esc_url(get_permalink(get_page_by_title('Search Results')) . 'blog/' . get_query_var('search-term')) . '/' . $nextPage ?>">Next results >></a>                     
                        <?php endif; ?>
                    </div>                          
                    <?php
                    else:
                        echo '<p>There were no results found</p>';
                    endif;
                    ?>            
                </div>                       
            </div>               
        </div>
    </div>  
</div>  

<?php endif; ?>

<?php get_footer();
