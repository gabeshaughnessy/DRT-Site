<?php 
	// this file needs to be handled separately since we fetch it via AJAX and don't 
	// want to include any of the wordpress header or footer content
	
	// include wp-blog-header.php to get access to everything
	require_once( '../../../wp-blog-header.php' );
	
	// send 200 response to prevent 404 ajax error (this is a wordpress quirk)
	header("HTTP/1.1 200 OK");
	
	// start a new instance of RezgoSite
	$site = new RezgoSite();
?>

<?=$site->getTemplate('calendar')?>