<?php 
	global $comp_manager;
	$meta = get_post_meta(get_the_ID()); 
?>
<h4 class="wp_comp_intro">
<?php
	if( isset( $meta['wp_comp_type'][0] ) && $meta['wp_comp_type'][0] == 1 ) {
		echo 'To be in with a chance to win this amazing prize, simply tell us:';	
	} elseif( isset( $meta['wp_comp_type'][0] ) && $meta['wp_comp_type'][0] == 2 ) {
		echo $meta['wp_comp_type_text'][0];
	}
?>
</h4>
<p class="wp_comp_question">Q: <?php echo $meta['wp_comp_question'][0]; ?></p>
<p class="center"><strong><?php $meta['wp_comp_rules'][0]; ?></strong></p>
<p class="center"><?php printf('This competition begins on %s and ends at midnight on %s', date( 'jS F Y', strtotime( $meta['wp_comp_sdate'][0] ) ), date( 'jS F Y', strtotime( $meta['wp_comp_edate'][0] ) ) ) ; ?></p>
<p class="center"><button type="button" class="btn btn-default btn-lg show_comp">Enter Now</button></p>
<ul>
	<li>Please note that automated entries, bulk entries or third party entries will be disqualified</li>
	<li>By entering this competition, you are consenting to receive information and newsletters from SoSensational. You can unsubscribe at any time.</li>
	<li>Competition hosting site <a href="http://www.theprizefinder.com/" target="_blank" rel="nofollow">ThePrizeFinder.com</a></li>
</ul>
<div id="wp_comp_form" style="display:none;">
	<?php
		$comp_manager->frontend_form( true, 24 );
	?>
</div>
        