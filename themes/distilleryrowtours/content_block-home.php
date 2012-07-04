<div id="content" >
<?php
get_template_part('pitch');
	?>
	<div class="row tri-cta"><?php			
				// The Call To Action Query
				
				$query_args = array(
				'post_type' => 'drt_distillery',
				'orderby' => 'rand',
				'posts_per_page' => '1'
				);
				$image_query = new WP_Query ($query_args);
				while ($image_query->have_posts()) : $image_query->the_post();
				$featured_distillery = get_the_post_thumbnail($post->ID,'three-col');
				endwhile;
				wp_reset_postdata();
				
				$query_args = array(
				'post_type' => 'drt_partner',
				'orderby' => 'rand',
				'posts_per_page' => '1'
				);
				$image_query = new WP_Query ($query_args);
				while ($image_query->have_posts()) : $image_query->the_post();
				
					$featured_partner = get_the_post_thumbnail($post->ID, 'three-col');
				endwhile;
				wp_reset_postdata();
				
				$args = array(
				'post_type' => 'drt_call_to_action',
				'order' => 'ASC',
				'limit' => '3',
				'tax_query' => array(
						array(
							'taxonomy' => 'cta_position',
							'field' => 'slug',
							'terms' => '3-column-home'
						)
					)
				);
				$the_query = new WP_Query( $args );
				$i = 1;
				// The Loop
				while ( $the_query->have_posts() ) : $the_query->the_post();
					?>
					<div class="cta span4">
					<div class="matted">
					<?php 
					if($i == 1){
						echo $featured_distillery;
					}
					elseif($i == 2){
					 echo $featured_partner;
					}
					else{
					the_post_thumbnail('three-col');
					}
					 ?>
					</div>
							<h3><?php the_title(); ?></h3>
						
						<div class="cta-body">
							<p><?php the_content(); ?></p>
						</div>
						
						</div>
					
					<?php
					$i++;
				endwhile;
				
				// Reset Post Data
				wp_reset_postdata();
				?>
				
		
			
		
	</div>

	
</div>
