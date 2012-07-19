<div class="row">

<div id="main">
<!-- Start the Loop. -->

 <?php 
 
 if ( have_posts() ) : while ( have_posts() ) : the_post(); 
 
 ?>

 <div class="post span4">


 <!-- Display the Title as a link to the Post's permalink. -->
 <h4 class="lined"> <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h4>

  <!-- Display the Post's Content in a div box. -->
 <div class="entry">
 <?php if(has_post_thumbnail()){?>
	 <div class="image-wrapper" >
		 <a class="post-link" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
			 <div class="matted">
			 <?php the_post_thumbnail('three-col'); ?>
			 </div>
		 </a>
	 </div>
	<?php } ?>
  
   </div>
 </div>


 <!-- Stop The Loop (but note the "else:" - see next line). -->
 <?php endwhile; else: ?>

 <?php endif; ?>
 </div>
 </div>
 