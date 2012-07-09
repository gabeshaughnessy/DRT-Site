<div class="row"><div id="main" class="span12">
<!-- Start the Loop. -->
 <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

           <div class="post">


 <!-- Display the Title as a link to the Post's permalink. -->
 <h2 class="lined"><?php the_title(); ?></h2>

  <!-- Display the Post's Content in a div box. -->
 <div class="entry row">
 <?php if(has_post_thumbnail()){?>
	 <div class="image-wrapper span4" >
	 <div class="matted">
		 <?php the_post_thumbnail('three-col'); ?>
		 </div>
	 </div>
	 <div class="span8"><?php } 
	 else { ?>
	 
	 <div class="span12">
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
 </div>