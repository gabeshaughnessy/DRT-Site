<?php 
	// this file accepts a string from the plugin file (rezgo.php) and displays it on a clean page for the ajax
	
	// send 200 response to prevent 404 ajax error (this is a wordpress quirk)
	header("HTTP/1.1 200 OK");
	
	echo $_REQUEST['response'];
?>