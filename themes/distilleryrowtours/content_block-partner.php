<div class="row"><div id="main" class="span8">
<!-- Start the Loop. -->
 <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<div class="post">


 <!-- Display the Title as a link to the Post's permalink. -->
 

  <!-- Display the Post's Content in a div box. -->
 <div class="entry row">
	 <?php if(has_post_thumbnail()){?>
	 	 <div class="image-wrapper span8" >
	 	 <div class="matted">
	 		 <?php the_post_thumbnail('eight-col'); ?>
	 		 </div>
	 	 </div>
	 	 <div class="span8"><?php } 
	 	 else { ?>
	 	 
	 	 <div class="span8">
	 	 <?php
	 	 }
	 	 ?>
 
   <?php the_content(); ?>
 </div>
</div>
 </div> <!-- closes the first div box -->

 <!-- Stop The Loop (but note the "else:" - see next line). -->
 <?php endwhile; else: ?>

 <?php endif; ?>
 </div>
 <div id="partner-details" class="span4">
 <h3 class="lined"><?php the_title(); ?></h3>
 <?php the_excerpt();
 if(get_post_meta($post->ID, 'drt_address', true)){
 ?>
 <div id="address"><strong>
 <?php 
 echo get_post_meta($post->ID, 'drt_address', true);
 ?></strong>
 </div>
 <?php
 }
 if(get_post_meta($post->ID, 'drt_phone', true)){
 ?>
 
 <div id="phone"><strong>
 <?php 
 echo get_post_meta($post->ID, 'drt_phone', true);
 ?></strong>
 </div>
 <?php
 }
 if(get_post_meta($post->ID, 'drt_website', true)){
 ?>
 
 <a href="<?php echo get_post_meta($post->ID, 'drt_website', true); ?>" class="action">Visit their Website</a>
 <?php } ?>
	 <div class="hours">
	 <h4 class="lined">Hours of Operation</h4>
	 <dl>
	 
	 <dt>Mon:</dt>
	 <dd>
	 <?php if(get_post_meta($post->ID, 'drt_monday_hours', true)){ ?>
	 <?php echo get_post_meta($post->ID, 'drt_monday_hours', true);  
	 }
	 else {
	 ?>
		 <span class="closed" >Closed</span>
	 <?php
	 }?>
	 </dd>
	<dt>Fri:</dt>
		 <dd>
	<?php if(get_post_meta($post->ID, 'drt_friday_hours', true)){ ?>
	<?php echo get_post_meta($post->ID, 'drt_friday_hours', true);  
	}
	else {
	?>
		 <span class="closed" >Closed</span>
	<?php
	}?>	 </dd>
	 <dt>Tues:</dt>
	 <dd>
	 <?php if(get_post_meta($post->ID, 'drt_tuesday_hours', true)){ ?>
	 <?php echo get_post_meta($post->ID, 'drt_tuesday_hours', true);  
	 }
	 else {
	 ?>
	 	 <span class="closed" >Closed</span>
	 <?php
	 }?>	 </dd>
	  <dt>Sat:</dt>
	 	 <dd>
	 <?php if(get_post_meta($post->ID, 'drt_saturday_hours', true)){ ?>
	 <?php echo get_post_meta($post->ID, 'drt_saturday_hours', true);  
	 }
	 else {
	 ?>
	 	 <span class="closed" >Closed</span>
	 <?php
	 }?>	 </dd>
	 
	 <dt>Wed:</dt>
	 <dd>
		<?php if(get_post_meta($post->ID, 'drt_wednesday_hours', true)){ ?>
		<?php echo get_post_meta($post->ID, 'drt_wednesday_hours', true);  
		}
		else {
		?>
			 <span class="closed" >Closed</span>
		<?php
		}?>	 
	</dd>
	 <dt>Sun:</dt>
		 <dd>
	<?php if(get_post_meta($post->ID, 'drt_sunday_hours', true)){ ?>
	<?php echo get_post_meta($post->ID, 'drt_sunday_hours', true);  
	}
	else {
	?>
		 <span class="closed" >Closed</span>
	<?php
	}?>	 </dd>
		
	
	 <dt>Thurs:</dt>
	 <dd>
<?php if(get_post_meta($post->ID, 'drt_thursday_hours', true)){ ?>
<?php echo get_post_meta($post->ID, 'drt_thursday_hours', true);  
}
else {
?>
	 <span class="closed" >Closed</span>
<?php
}?>	 </dd>
	 
			 </dl>
	 </div>
	 
	  <?php if(get_post_meta($post->ID, 'drt_description', true)){ ?>
	 <div id="passport-discount">
	 <h4 class="lined">Passport Discount</h4>
	 <p class="discount">
	<?php
	 echo get_post_meta($post->ID, 'drt_description', true);
	 };
	 ?></p>
	 </div>
 </div>
 </div>