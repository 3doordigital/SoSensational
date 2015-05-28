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
                    foreach($products as $product) {
                    ?>
                        <div class="col-md-8 product">        
                            <div>
                                <div>
                                    <a target="_blank" href="http://click.linksynergy.com/link?id=RIYrAqooNEU&amp;offerid=225471.30656&amp;type=15&amp;murl=http%3A%2F%2Fwww.follifollie.co.uk%2Fgb-en%2Fonline-shop%2Fwatches%2Fceramic%2Fwf0b032btw_xx-ceramic-4-seasons-watch" title="Ceramic 4 Seasons Watch"><img src="http://www.follifollie.co.uk/FilesFF/products/WF0B032BTW_XX-1000x1000.jpg"></a>
                                </div>
                                <div class="row product-info">
                                    <div class="prod_title col-md-16">
                                        <h3><a target="_blank" href="http://click.linksynergy.com/link?id=RIYrAqooNEU&amp;offerid=225471.30656&amp;type=15&amp;murl=http%3A%2F%2Fwww.follifollie.co.uk%2Fgb-en%2Fonline-shop%2Fwatches%2Fceramic%2Fwf0b032btw_xx-ceramic-4-seasons-watch" title="Ceramic 4 Seasons Watch">Ceramic 4 Seasons Watch...</a></h3>
                                        <h4>Folli Follie UK</h4>
                                    </div>
                                    <div class="prod_price col-md-8">
                                        <div class="price">
                                            <div class="amount">£295.00</div>
                                            <a target="_blank" href="http://click.linksynergy.com/link?id=RIYrAqooNEU&amp;offerid=225471.30656&amp;type=15&amp;murl=http%3A%2F%2Fwww.follifollie.co.uk%2Fgb-en%2Fonline-shop%2Fwatches%2Fceramic%2Fwf0b032btw_xx-ceramic-4-seasons-watch" class="button">Shop Now</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                    
                    <?php
                    }
                    ?>
                </div>
            </div>   
        </div>
    </div>               

<?php get_footer();