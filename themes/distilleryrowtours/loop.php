<div id="main" class="row">
	<div id="blog" class="span10">
		<!-- Start the Loop. -->
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		 <div class="post">
			 <!-- Display the Title as a link to the Post's permalink. -->
			 <h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
			
			 <!-- Display the Post's Content in a div box. -->
			 <div class="entry">
			   <?php the_content(); ?>
			 </div>
		 </div> <!-- closes the post div box -->
		 
		<?php endwhile; ?>
	 </div>
	 
	 <div id="sidebar" class="span2">
	 </div>
	 
</div>
