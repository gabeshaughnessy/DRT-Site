<?php
	// this is your footer template, you can either grab the Rezgo footer from XML or create your own here
	
	echo $site->getAnalytics();
	
	// figure out if we need a supplier or vendor google analytics code
	$code = ($site->isVendor()) ? 'UA-1943654-5' : 'UA-1943654-2';
?>

<script type="text/javascript">
	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>

<script type="text/javascript">
	try {
		var pageTracker = _gat._getTracker("<?=$code?>");
		pageTracker._trackPageview();
	} catch(err) {}
</script>

<!-- Start Quantcast tag -->
<script type="text/javascript" src="//secure.quantserve.com/quant.js"></script>
<script type="text/javascript">_qacct="p-17lYOUFt-JH2A";quantserve();</script>
<noscript>
	<a href="http://www.quantcast.com/p-17lYOUFt-JH2A" target="_blank"><img src="//secure.quantserve.com/pixel/p-17lYOUFt-JH2A.gif" style="display: none;" border="0" height="1" width="1" alt="Quantcast"/></a>
</noscript>
<!-- End Quantcast tag -->

<? if(!$site->config('REZGO_HIDE_HEADERS')) { ?>
	<?=$site->getFooter()?>
<? } ?>