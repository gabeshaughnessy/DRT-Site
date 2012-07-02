<?php 
	// This file fetches company info from Rezgo for the settings page to verify everything is working
		
	// send 200 response to prevent 404 ajax error (this is a wordpress quirk)
	header("HTTP/1.1 200 OK");
	
	function getPage($url) {
		include('include/fetch.rezgo.php');
		return $result;
	}
	
	$file = getPage('http://xml.rezgo.com/xml?transcode='.$_REQUEST['cid'].'&key='.$_REQUEST['key'].'&i=company');
	
	$result = simplexml_load_string(utf8_encode($file));
	
	if((string)$result->company_name) {
		echo '<span class="ajax_success">XML API Connected</span><br>
		<span class="ajax_success_message">'.((string)$result->company_name).'</span> 
		<a href="http://'.((string)$result->domain).'.rezgo.com" class="ajax_success_url" target="_blank">'.((string)$result->domain).'.rezgo.com</a>';
	} else {
		echo '<span class="ajax_error">XML API Error</span><br> 
		<span class="ajax_error_message">'.((string)$result[0]).'</span>';
	}
	
?>