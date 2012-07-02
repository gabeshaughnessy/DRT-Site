<div class="row"><div id="main" class="span8">
<!-- Start the Loop. -->
 <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
     <div class="post">


 <!-- Display the Title as a link to the Post's permalink. -->
 <h3 class="lined"><?php the_title(); ?></h3>

 <!-- Display the date (November 16th, 2009 format) and a link to other posts by this posts author. -->
 <small><?php the_time('F jS, Y') ?><!-- by <?php the_author_posts_link() ?>--></small>

 <!-- Display the Post's Content in a div box. -->
 <div class="entry row">
 <?php if(has_post_thumbnail()){?>
 	 <div class="image-wrapper span3" >
 		 <?php the_post_thumbnail('three-col'); ?>
 	 </div>
 	 <div class="span5"><?php } 
 	 else { ?>
 	 
 	 <div class="span8">
 	 <?php
 	 }
 	 ?>
   <?php the_content(); ?>
 </div>
</div>
 <!-- Display a comma separated list of the Post's Categories. -->
 <!--<p class="postmetadata">Posted in <?php the_category(', '); ?></p>-->
 </div> <!-- closes the first div box -->

 <!-- Stop The Loop (but note the "else:" - see next line). -->
 <?php endwhile; else: ?>

 <!-- The very first "if" tested to see if there were any Posts to -->
 <!-- display.  This "else" part tells what do if there weren't any. -->
 <p>Sorry, no nothing here.</p>

 <!-- REALLY stop The Loop. -->
 <?php endif; ?>
 </div>
 <ul id="sidebar" class="span4">
 <?php dynamic_sidebar( 'Primary Sidebar' ); ?>
 </ul>
 </div>