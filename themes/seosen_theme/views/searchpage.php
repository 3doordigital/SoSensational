<?php get_header(); ?>

<div class="container">
    <div class="row">
        <div class="col-24-sm">
            <div class="search-section products">                
                <div class="search-section-head">
                    <h1>Results from our Shop:</h1>
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
                    <div class="more-results">
                        <a href="<?php echo esc_url(get_permalink(get_page_by_title('Search Results')) . 'shop/' . get_query_var('s')) ?>">Click here to see more shop results >></a>
                    </div>                        
                    <?php
                    else:
                        echo '<p>There were no resutls found</p>';
                    endif;
                    ?>            
                </div>                       
            </div>               
        </div>
    </div>    
    <div class="row">
        <div class="col-24-sm">    
            <div class="search-section products">
                <div class="search-section-head">
                    <h1>Results from Brands & Boutiques:</h1>
                </div>
                <div class="search-results-section">
                    <?php
                    if ($bbProducts) :
                    foreach($bbProducts as $bbProduct) {
                    ?>
                        <div class="col-md-8 product">        
                            <div>
                                <a target="_blank" href="<?php echo $bbProduct['link'] ?>" title="<?php echo $bbProduct['title'] ?>"><img src="<?php echo $product['picture'] ?>"></a>
                            </div>
                            <div class="row product-info">
                                <div class="prod_title col-md-16">
                                    <h3><a target="_blank" href="<?php echo $bbProduct['link'] ?>" title="<?php echo $bbProduct['title'] ?>"><?php echo get_snippet($bbProduct['title'], 4)  . '...' ?></a></h3>
                                    <h4><?php echo $bbProduct['brand'] ?></h4>
                                </div>
                                <div class="prod_price col-md-8">
                                    <div class="price">
                                        <div class="amount">&pound;<?php echo $bbProduct['price'] ?></div>
                                        <a target="_blank" href="<?php echo $bbProduct['link'] ?>" class="button">Shop Now</a>
                                    </div>
                                </div>
                            </div>
                        </div> 
                    <?php
                    }
                    ?>
                    <div class="clearfix"></div>
                    <div class="more-results">
                        <a>Click here to see more Brands & Boutiques results >></a>
                    </div>                        
                    <?php
                    else:
                        echo '<p>There were no resutls found</p>';
                    endif;
                    ?>                       
                </div>
            </div>   
        </div>
    </div>
    <div class="row">
        <div class="col-24-sm">    
            <div class="search-section blog-posts last-section">
                <div class="search-section-head">
                    <h1>Results from our Blog:</h1>
                </div>
                <div class="search-results-section">
                    <?php
                    if ($blogPosts) :
                    foreach($blogPosts as $blogPost) {
                    ?>
                        <div class="col-sm-8 blog-small">
                            <h2><a href="<?php echo $blogPost['link'] ?>"><?php echo $blogPost['title'] ?></a></h2>
                            <?php echo $blogPost['meta'] ?>
                                    <img width="<?php echo $blogPost['thumbnail'][1] ?>" height="<?php echo $blogPost['thumbnail'][2] ?>" src="<?php echo $blogPost['thumbnail'][0] ?>" class="img-responsive wp-post-image" alt="<?php echo $blogPost['alt-text'] ?>">
                            <?php echo $blogPost['exerpt'] ?>                           
                        </div>
                    <?php
                    }
                    ?>
                    <div class="clearfix"></div>
                    <div class="more-results">
                        <a>Click here to see more Blog results >></a>
                    </div>                        
                    <?php
                    else:
                        echo '<p>There were no resutls found</p>';
                    endif;
                    ?>                      
                </div>
            </div> 
        </div>
    </div>
</div>
  

<?php get_footer();