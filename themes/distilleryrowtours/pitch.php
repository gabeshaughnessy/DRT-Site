<div id="pitch" class="">
<div id="main-cta-container">
<div class="flexslider">
  <div class="slides">
<?php
// The Pitch Query

$args = array(
'post_type' => 'drt_pitch_box',
'order' => 'ASC',
'limit' => '1'
);
$the_query = new WP_Query( $args );

// The Loop
while ( $the_query->have_posts() ) : $the_query->the_post();
	?>
	<div class="pitch-content">
			<?php the_post_thumbnail( 'pitch'); ?>		
		</div>
	
	<?php
endwhile;

// Reset Post Data
wp_reset_postdata();
?>


</div><!-- end flexslider -->
</div><!-- end CTA Container -->

<?php
// The Call To Action Query

$args = array(
'post_type' => 'drt_call_to_action',
'order' => 'ASC',
'limit' => '3',
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
	<div id="main_cta" >
	<div class="menu-tab left-tab"></div>
	<div class="menu-tab right-tab"></div>
	<div class="main row cta ">

			<h3 class="cta_title span6"><?php the_title(); ?></h3>
		
		<div class="cta-body span6 ">
		<a class="cta_btn pull-right" href="./store" title="Distillery Passport Store">Order a Passport</a>
			
		</div>
	
		</div>
		</div>
	<div class="cta-content">
	<?php the_content(); ?></div>
	<div class="cta-image matted"><?php the_post_thumbnail('featured'); ?>
	
	<?php
endwhile;

// Reset Post Data
wp_reset_postdata();
?>
</div>
</div>
