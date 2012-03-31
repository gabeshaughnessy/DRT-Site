<div id="pitch" class="">
pitch
</div>

<div id="main_cta" >
<?php
// The Call To Action Query

$args = array(
'post_type' => 'drt_call_to_action',
'order' => 'ASC',
'limit' => '1',
'tax_query' => array(
		array(
			'taxonomy' => 'cta_position',
			'field' => 'slug',
			'terms' => 'main'
		)
	)
);
$the_query = new WP_Query( $args );

// The Loop
while ( $the_query->have_posts() ) : $the_query->the_post();
	?>
	<div class="main row cta ">
			<h3 class="cta_title span12"><?php the_title(); ?></h3>
		
		<div class="cta-body span12">
			<p><?php the_content(); ?></p>
		</div>
		
		</div>
	
	<?php
endwhile;

// Reset Post Data
wp_reset_postdata();
?>
</div>

<div class="row">
	
