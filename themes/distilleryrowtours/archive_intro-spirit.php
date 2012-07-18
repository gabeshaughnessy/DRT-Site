<?php 
			$args = array(
				
						'pagename' => 'spirits'
						
					
			);
			$custom_query = new WP_Query( $args );
			if ( $custom_query->have_posts() ) : ?>

			

				<?php /* Start the Loop */ ?>
				
				<?php while ( $custom_query->have_posts() ) : $custom_query->the_post(); 
				?>
				<div id="post_<?php echo get_the_ID(); ?>" class="post section_page">
				<h2 class="lined"><?php the_title(); ?></h2>
				<?php the_content(); ?>
				</div><!--end of the post -->
				<?php endwhile; ?>

				

			<?php else : ?>

				<p> nothing for you here</p>

			<?php endif; 
			// Reset Post Data
			wp_reset_postdata();
			?>
