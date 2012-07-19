<?php
//Change the Default post order for the archive
// Runs before the posts are fetched
add_filter( 'pre_get_posts' , 'my_change_order' );
// Function accepting current query
function my_change_order( $query ) {
	// Check if the query is for an archive
if($query->is_archive)
		// Query was for archive, then set order
		$query->set('orderby', 'title');
		$query->set( 'order' , 'desc' );
	// Return the query (else there's no more query, oops!)
	return $query;
}
 
get_header();
get_template_part('archive_intro', 'partner');
get_template_part('content_block', 'partner_archive');
get_footer();
?>