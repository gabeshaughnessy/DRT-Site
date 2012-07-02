<?
	// this file is called from the short-url bookmark button, it calls rezgo.me (the rezgo shortening service)
	// and fetches a short url to be displayed in the dropdown.  This script can also be used for any other
	// url shortening api call (bit.ly, tinyurl, etc)

	if($_REQUEST[url]) {
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://rezgo.me/api?format=simple&action=shorturl&url='.urlencode($_REQUEST[url]));
		curl_setopt($ch, CURLOPT_FRESH_CONNECT,TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT,30);
		$result = curl_exec($ch);
		curl_close($ch);
		
		echo $result;
	}
?>