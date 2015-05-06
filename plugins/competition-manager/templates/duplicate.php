<?php
	//define('WP_USE_THEMES', false);
	//require($_SERVER['DOCUMENT_ROOT'].'/wp-blog-header.php');
?>
<div class="duplicate-inner">
	<h1>Oops!</h1>
    <h2>You have already entered this competition.</h2>
    	<div class="dup-detail">
        	<p>The lucky winner(s) will be announced shortly after the close of the competition on <strong><?php echo date('l jS F Y', strtotime( $_REQUEST['date'] ) ) ; ?>.</strong>. The winner will be notified by email.</p>
        </div>
        <div class="other_comps">
            	<?php 
					
					$posts_array = get_posts( array(
							'post_type' 	=> 'wp_comp_man',
							'exclude'       => $_REQUEST['comp'],	
							'posts_per_page'=> 4,
							'meta_query' 	=> array(
								 array( 'relation' => 'AND' ),
								 array(
									'key'=>'wp_comp_sdate',
									'value'=> date("Y-m-d"),
									'compare'=>'<=',
									'type' => 'date'
								),
								array(
									'key'=>'wp_comp_edate',
									'value'=> date("Y-m-d"),
									'compare'=>'>=',
									'type' => 'date'
								)
							)
						)
					); 
					if( $posts_array ) {
						echo '<h3>Why not check out our other competitions?</h3>';
						echo '<ul>';
						foreach( $posts_array as $post ) {
							setup_postdata( $post );
							echo '<li><a href="'.get_permalink().'">'.get_the_title().'</a></li>';
						}
						echo '</ul>';
					}
				?>
            <p><a class="popup_see_all" href="/competitions/">Click here to see all competitions</a></p>
        </div>
        	
</div>