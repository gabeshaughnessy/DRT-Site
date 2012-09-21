<!--right side -->
<div id="right_panel">

	<? if($site->exists($site->getTriggerState())) { ?>
		<div id="promo">
			<h1>Have a promotional code?</h1>
			<? if($_COOKIE['rezgo_promo']) { ?>
			<div id="promo_entered_sidebar" class="promo_entered_sidebar">
				<?=$_COOKIE['rezgo_promo']?>
				<a href="javascript:void(0);" onclick="jQuery('#promo_entered_sidebar').hide(); jQuery('#promo_hidden_sidebar').fadeIn();">[change]</a>
			</div>
			<? } ?>
			
			<div id="promo_hidden_sidebar" class="promo_hidden_sidebar" <? if($_COOKIE['rezgo_promo']) { ?>style="display:none;"<? } ?>>
				<form class="item" onsubmit="document.location.href = '<?=$_SERVER['REQUEST_URI']?><?=((strpos($_SERVER['REQUEST_URI'], '?') !== false) ? '&' : '?')?>promo=' + jQuery('#promo_sidebar').val() + '<? if($_REQUEST[date]) { ?>#book<? } ?>'; return false;">
		  		<input type="text" class="promo_input_sidebar" name="promo" id="promo_sidebar" value="<?=$_COOKIE['rezgo_promo']?>">
					<input type="submit" class="promo_submit_sidebar" value="apply">
				</form>
			</div>
		</div>
	<? } ?>
		
	<!-- calendar start -->
	<div id="calendar">
		<h1>Click a date to book</h1>
		
		<div class="legend">
			<div class="legend_item"><div class="legend_available"><span>Available</span></div></div>
			<div class="legend_item"><div class="legend_unavailable"><span>Unavailable</span></div></div>
			<div class="legend_item"><div class="legend_full"><span>Full</span></div></div>
		</div>
		
		<div class="legend_memo" id="legend_memo"></div>
		
		<p class="item" id="calendar_content">
		
		</p>
		
		<? if(!$_REQUEST['option'] && !$_REQUEST['date']) { ?><div id="calendar_marker" style="display:none;" onclick="remove_arrow();"></div><? } ?>
	   
	  <script>
			function change_cal(url) {
				close_overlay();
				
				// this it the loading graphic
				jQuery('#calendar_content').html('<table border=0 cellspacing=0 cellpadding=0 id="calendar_container" class="calendar_load"><tr><td align=center valign=center><img src="<?=$site->path?>/images/loader.gif"></td></tr></table>');
				
				jQuery('#calendar_content').load('<?=$site->base?>/calendar.php?' + url);
			}
			
			function close_overlay() {
				// this function closes all overlays attached to a[rel] in #calendar_container
				jQuery("#calendar_container a[rel]").each(function() {
					jQuery(this).overlay().close();
				});
			
				// it also closes the "click to book" arrow, the fadein below acts after it
				// so it will only close the arrow on any close_overlay calls that follow
				remove_arrow();
			}
			
			function remove_arrow() {
				jQuery('#calendar_marker').fadeOut();
			}
		
			function next_cal_page(date, totalPages) {
				var current_page = jQuery('#page_' + date).html();
				if(current_page < totalPages) {
					jQuery('#cal_page_' + date + '_' + current_page).hide();
					jQuery('#cal_page_' + date + '_' + ++current_page).fadeIn();
					jQuery('#page_' + date).html(current_page);
				}
			}
			
			function prev_cal_page(date, totalPages) {
				var current_page = jQuery('#page_' + date).html();
				if(current_page > 1) {
					jQuery('#cal_page_' + date + '_' + current_page).hide();
					jQuery('#cal_page_' + date + '_' + --current_page).fadeIn();
					jQuery('#page_' + date).html(current_page);
				}
			}	
			
			change_cal('item_id=<?=$item->uid?>&date=<?=$_REQUEST['date']?>');
		
			jQuery('#calendar_marker').delay(800).fadeIn();
		</script>
	  
	</div>
	<!-- calendar end-->
		
		<? 
		$gallery_count = $item->image_gallery->attributes()->value + $item->video_gallery->attributes()->value;
		$g = 0;
		if($gallery_count > 0) { ?>

		<!-- gallery start -->
		<div id="carousel">

			<h1>View images and videos</h1>
		
			<a class="prev browse left"<? if($gallery_count < 5) { ?> style="visibility:hidden;" <? } ?>></a>
    	
    	<div class="scrollable">
   
			  <!-- root element for the items -->
				<div class="items">
					
					<div>
			   		<? foreach( $site->getTourMedia($item) as $media ): ?>
			   			
			   			<? if($g == 4) { ?></div><div><? $g = 1; } else { $g++; } ?> 
			   			
			   			<? if($media->type == 'image') { ?>
			   				<a href="http://images.rezgo.com/gallery/<?=$media->path?>" rel="gallery[gal]" title="<?=$media->caption?>">
			   					<img src="http://images.rezgo.com/gallery/thumbs/<?=$media->image?>">
			   				</a>
			        <? } ?> 
			        
			        <? if($media->type == 'video') { ?>
			        	<a href="<?=$media->path?>" rel="gallery[gal]" title="<?=$media->caption?>" style="position:relative;">
			   					<img src="http://images.rezgo.com/video/<?=$media->image?>" alt="Video">
			   					<div style="height:32px; width:32px; background:url(<?=$site->path?>/images/play.png); position:absolute; bottom:-35px; right:9px;"></div>
			   				</a>
			        <? } ?>
			         
			   		<? endforeach; ?>
			   	</div>
			   		
			   </div>
			   
			</div>
			
			<a class="next browse right"<? if($gallery_count < 5) { ?> style="visibility:hidden;" <? } ?>></a>  
    
    </div>
		<!-- gallery end--> 
    
  <? } ?>

	<? if($site->exists($item->lat)) { ?>

	<!-- map start -->
	<div id="map">
		<h1>Location map</h1>
		<p class="item">
			<a href="http://maps.google.com/maps?f=q&hl=en&geocode=&q=<?=$item->lat?>,<?=$item->lon?>&ie=UTF8&ll=<?=$item->lat?>,<?=$item->lon?>&iwloc=addr&z=<? if($site->exists($item->zoom)) { ?><?=$item->zoom?><? } else { ?>14<? } ?>" target ="_blank">
				<img src="http://images.rezgo.com/geotag/<?=$item->lat?>,<?=$item->lon?><? if($site->exists($item->zoom)) { ?>,<?=$item->zoom?><? } ?>.jpg" align="middle" />
			</a>
		</p>
	</div>
	<!-- map end-->

	<? } ?>

</div><!--end right panel-->

<div class="clear"></div>

<? if($_COOKIE['rezgo_refid_val']) { ?>
	<div id="refid">
		RefID: <?=$_COOKIE['rezgo_refid_val']?>
	</div>
<? } ?>

</div><!--end rezgo wrp-->