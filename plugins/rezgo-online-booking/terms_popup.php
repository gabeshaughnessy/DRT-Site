<?php 
	// this file needs to be handled separately since we display it in a popup
	// and don't want to include any of the wordpress header or footer content
	
	// include wp-blog-header.php to get access to WordPress config settings
	require_once( '../../../wp-blog-header.php' );

	// start a new instance of RezgoSite
	$site = new RezgoSite();
	
	$site->getDoctype();
?>

<?=$site->getTemplate('terms_popup')?>