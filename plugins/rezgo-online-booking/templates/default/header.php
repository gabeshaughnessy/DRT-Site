<? if(!$site->config('REZGO_HIDE_HEADERS')) { ?>
	<?
		header('Cache-Control: no-cache');
	  header('Pragma: no-cache');
	  
	  header('Content-Type: text/html; charset=utf-8');
	?>
	
	<?=$site->getHeader()?>	
<? } ?>

<!--[if lte IE 6]><script src="<?=$this->path?>/javascript/ie6/warning.js"></script><script>window.onload=function(){e("<?=$this->path?>/javascript/ie6/")}</script><![endif]-->

<link media="all" href="<?=$this->path?>/header.css" type="text/css" rel="stylesheet">
<!--[if lte IE 7]>
<link rel="stylesheet" type="text/css" href="<?=$this->path?>/header_ie.css" />
<![endif]-->

<script type="text/javascript" src="<?=$this->path?>/javascript/jquery.tools.min.js"></script>

<? if($site->exists($site->getStyles())) { ?>
<style>
<!--

	<?=$site->getStyles()?>

-->
</style>
<? } ?>
