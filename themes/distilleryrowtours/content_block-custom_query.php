<div id="content">
	<?php
	
	// The Query
	$args = array(
	);
	$the_query = new WP_Query( $args );
	
	// The Loop
	while ( $the_query->have_posts() ) : $the_query->the_post();
		?>
		
				<a class="close" data-dismiss="modal">×</a>
				<h3><?php the_title(); ?></h3>
			</div>
			<div class="modal-body">
				<p><?php the_content(); ?></p>
			</div>
			
		
		<?php
	endwhile;
	
	// Reset Post Data
	wp_reset_postdata();
	?>
	
</div>
