<!--right side -->
<div id="right_panel">

	<? if($site->exists($site->getTriggerState())) { ?>
		<div id="promo">
			<h1>Have a promotional code?</h1>
			<? if($_SESSION['rezgo_promo']) { ?>
			<div id="promo_entered_sidebar" class="promo_entered_sidebar">
				<?=$_SESSION['rezgo_promo']?>
				<a href="javascript:void(0);" onclick="$('#promo_entered_sidebar').hide(); $('#promo_hidden_sidebar').fadeIn();">[change]</a>
			</div>
			<? } ?>
			
			<div id="promo_hidden_sidebar" class="promo_hidden_sidebar" <? if($_SESSION['rezgo_promo']) { ?>style="display:none;"<? } ?>>
				<form class="item" onsubmit="document.location.href = '<?=$_SERVER['REQUEST_URI']?><?=((strpos($_SERVER['REQUEST_URI'], '?') !== false) ? '&' : '?')?>promo=' + $('#promo_sidebar').val(); return false;">
		  		<input type="text" class="promo_input_sidebar" name="promo" id="promo_sidebar" value="<?=$_SESSION['rezgo_promo']?>">
					<input type="submit" class="promo_submit_sidebar" value="apply">
				</form>
			</div>
		</div>
	<? } ?>
	
	<!-- search -->
	<div id="right_search">
		<h1>Search by Keyword</h1>
			
		<form class=item onsubmit="document.location.href='<?=$site->base?>/keyword/'+$('#search_for').val(); return false;">
			<input type="text" id="search_for" name="search_for" class="keyword_search" value="<?=stripslashes(htmlentities($_REQUEST['search_for']))?>" />
			<input class="btn_search" type=submit value="find">
		</form>
	</div> 
	<!-- search end-->
	
	<!-- tag container -->
	<div id="right_tags">
		<h1>Browse by Tag</h1>
		<p class="item">
		
			<?
				// get the high and low points in the tag cloud
				list($high, $low) = $site->getTagSizes();
				
				// calculate out the spread of sizes, in this case we use 5
				$spread = round(($high - $low) / 5, 0);
			?>
		
			<a href="<?=$site->base?>/" style="font-size:20px;" alt="everything" title="everything">everything</a>&nbsp;&nbsp;&nbsp; 
		
			<? foreach( $site->getTags() as $tag ): ?>
			
				<?
					// figure out what size to make this tag
					
					if($tag->count > ($low + ($spread * 4))) { $size = 22; }
					elseif($tag->count > ($low + ($spread * 3))) { $size = 20; }
					elseif($tag->count > ($low + ($spread * 2))) { $size = 18; }
					elseif($tag->count > ($low + ($spread * 1))) { $size = 16; }
					else { $size = 14; }
				?>
				
				<a href="<?=$site->base?>/tag/<?=urlencode($tag->name)?>" style="font-size:<?=$size?>px;" alt="<?=$tag->count?>" title="<?=$tag->count?>"><?=$tag->name?></a>&nbsp;&nbsp;&nbsp; 
				
			<? endforeach; ?>
		
		</p>
	</div> 
	<!-- tag container end-->

</div>
<!--end right panel-->

<!-- this is necessary for this case -->
<div class="clear"></div>

<? if($_COOKIE['rezgo_refid_val']) { ?>
	<div id="refid">
		RefID: <?=$_COOKIE['rezgo_refid_val']?>
	</div>
<? } ?>

</div><!--end rezgo wrp-->