<div id="rezgo" class="wrp_list">

<link rel="stylesheet" href="<?=$site->path?>/javascript/prettyPhoto/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
<script type="text/javascript" src="<?=$site->path?>/javascript/prettyPhoto/jquery.prettyPhoto.js"></script>

<script type="text/javascript">
	jQuery(document).ready(function() {
		// setup ul.tabs to work as tabs for each div directly under div.panes
		jQuery("ul.tabs").tabs("div.panes > div", { initialIndex: <?=(($_REQUEST['date']) ? 3 : 0)?> });
		
		// initialize scrollable
		jQuery(".scrollable").scrollable();
		
		jQuery(".items a[rel]").prettyPhoto({theme:'facebook'});
	});
</script>

<div id="left_panel">
	<div class="breadcrumb"><h1 class="header"><? if($item->unavailable) { ?><?=$item->name?><? } else { ?>Details<? } ?></h1><a href="<?=$site->base?>/?search=restore" class="back"><span>&lt;&lt;</span> Back to Results</a></div>
	
	<? if($item->unavailable) { ?>
		<div class="not_available">
			Sorry, The item you are looking for is no longer available.
		</div>
	<? } ?>
	
	<a name="book"></a>
	
	<? foreach( $site->getTours('t=com&q='.$_REQUEST['com'].'&f[uid]='.$_REQUEST['option'].'&d='.$_REQUEST['date'].'&limit=1') as $item ): ?>
	
	<? $site->readItem($item) ?>
	
  <div class="item last"> <!-- use last to eliminate bottom border -->
    <div class="image"><img src="http://images.rezgo.com/items/<?=$item->cid?>-<?=$item->com?>.jpg" border="0" /></div>

    <h1 class="tour_title"><?=$item->name?></h1>
    <div class="location">
    	<span>Location:&nbsp;</span>
    	<?
    		unset($loc);
    		if($site->exists($item->city)) $loc[] = $item->city;
    		if($site->exists($item->state)) $loc[] = $item->state;
    		if($site->exists($item->country)) $loc[] = ucwords($site->countryName($item->country));
    	
    		if($loc) echo implode(', ', $loc);
    	?>
    </div>
    <? if($site->exists($item->starting)) { ?>
    	<div class="price"><span>Starting From:&nbsp;</span><?=$site->formatCurrency($item->starting)?></div>
    <? } ?>
    
    <? if(count($site->getTourTags()) > 0) { ?>
    <div class="tags">
    	<span>Tags:&nbsp;</span>
    	<? foreach($site->getTourTags() as $tag): ?>
    	<span><a href="<?=$site->base?>/tag/<?=urlencode($tag)?>"><?=$tag?></a> </span>
    	<? endforeach; ?>
    </div>
    <? } ?>
    
    <? if($site->isVendor()) { ?>
    	<div class="tags">
	    	<span>Provided By:&nbsp;</span>
	    	<a href="<?=$site->base?>/supplier/<?=$item->cid?>"><?=$site->getCompanyName($item->cid)?></a>
	    </div>
    <? } ?>
 	</div> 
	
	<div id="tab_box">  
  	<ul class="tabs">  
      <li><a href="#">Overview</a></li>  
      <li><a href="#">Itinerary</a></li>  
      <li><a href="#">Schedule</a></li> 
      <? if($_REQUEST[date]) { ?><li class="book_tab"><a href="#book">Book <?=date("M d", strtotime($_REQUEST[date]))?></a></li><? } ?>
   	</ul>  
    
    <div class=panes>
	    <div class="content">
	    	<p><?=$item->details->overview?></p>
				<? if($site->exists($item->details->description)) { ?>
					<h4>Additional Information</h4>
					<p><?=$item->details->description?></p>
				<? } ?>
			</div>    
	    <div class="content">
	    	<p><?=$item->schedule->itinerary?></p>
	    	<? if($site->exists($item->schedule->bring)) { ?>
					<h4>Things To Bring</h4>
					<p><?=$item->schedule->bring?></p>
				<? } ?>
				<? if($site->exists($item->details->inclusions)) { ?>
					<h4>Inclusions</h4>
					<p><?=$item->details->inclusions?></p>
				<? } ?>
				<? if($site->exists($item->details->exclusions)) { ?>
					<h4>Exclusions</h4>
					<p><?=$item->details->exclusions?></p>
				<? } ?>
				<? if($site->exists($item->details->cancellation)) { ?>
					<h4>Cancellation Policy</h4>
					<p><?=$item->details->cancellation?></p>
				<? } ?>
	    </div>  
	    <div class="content">
	    	<? if($site->exists($item->schedule->departs)) { ?>
					<h4>Departs</h4>
					<p><?=$item->schedule->departs?></p>
				<? } ?>
				<? if($site->exists($item->schedule->unavailable)) { ?>
					<h4>Unavailable Dates</h4>
					<p><?=$item->schedule->unavailable?></p>
				<? } ?>
				<? if($site->exists($item->schedule->pick_up)) { ?>
					<h4>Pickup/Departure</h4>
					<p><?=$item->schedule->pick_up?></p>
				<? } ?>
				<? if($site->exists($item->schedule->drop_off)) { ?>
					<h4>Drop-Off</h4>
					<p><?=$item->schedule->drop_off?></p>
				<? } ?>
				<? if($site->exists($site->getTourLocations())) { ?>
					<h4>Additional Locations</h4>
					<p>
						<? foreach($site->getTourLocations() as $location) { ?>
							<?
				    		unset($loc);
				    		if($site->exists($location->city)) $loc[] = $location->city;
				    		if($site->exists($location->state)) $loc[] = $location->state;
				    		if($site->exists($location->country)) $loc[] = ucwords($site->countryName($location->country));
				    	
				    		if($loc) echo implode(', ', $loc).'<br>';
				    	?>
						<? } ?>
					</p>
				<? } ?>
			</div>  
	    <div class="content">
	    
	    	<div class="op_title">Booking for [<?=$item->time?>] <span>on <?=date("F d, Y", strtotime($_REQUEST[date]))?></span></div>
				
				<? if($site->exists($item->duration)) { ?>
				<span class="duration">Duration: <?=$item->duration?></span>
				<? } ?>
				
				<span class="memo">
					<? if($site->exists($item->date->hide_availability)) { ?>
						Available
					<? } else { ?>
						Availability: <?=$item->date->availability?>
					<? } ?>
				</span>
				
				<? if($item->date->availability > 0) { ?>
					
					<ul class="checkout_box label"><li class="price_op label">Price Option</li><li class="quantity label">Qty</li><li class="price label">Price</li></ul>
		   		
		   		<script>
		   			var fields = new Array();
		   			
		   			// validation for the inputted data
		   			
						function check() {
							var err;
							var count = 0;
							var required = 0;
							
							for(v in fields) {
								// total number of people
								count += jQuery('#' + v).val() * 1;
								
								// has a required price point been used
								if(fields[v] && jQuery('#' + v).val()) { required = 1; }
							}
							
							if(count == 0 || !count) {
								err = 'Please enter the number you would like to book.';
							} else if(required == 0) {
								err = 'At least one marked ( * ) price point is required to book.';
							} else if(count < <?=$item->per?>) {
								err = 'At least <?=$item->per?> people are required to book.';
							} else if(count > <?=$item->date->availability?>) {
								err = 'There is not enough availability to book ' + count + ' people.';
							} else if(count > 150) {
								err = 'You can not book more than 150 people in a single booking.';
							}
							
							if(err) {
								jQuery('#error_text').html(err);
								jQuery('#error_text').fadeIn().delay(2000).fadeOut();
								return false;
							}
						}
					</script>
		   		
			   	<form id="checkout_box" action="<?=$site->base?>/book">
						<input type="hidden" name="uid" value="<?=$_REQUEST['option']?>">
						<input type="hidden" name="date" value="<?=$_REQUEST['date']?>">
						
						<? if($_COOKIE['rezgo_promo']) { ?><input type="hidden" name="promo" value="<?=$_COOKIE['rezgo_promo']?>"><? } ?>
						<? if($_COOKIE['rezgo_refid_val']) { ?><input type="hidden" name="refid" value="<?=$_COOKIE['rezgo_refid_val']?>"><? } ?>
						
						<? foreach( $site->getTourPrices($item) as $price ): ?>
						
							<script>fields['<?=$price->name?>'] = <?=(($price->required) ? 1 : 0)?>;</script>
						
			   			<ul class="checkout_box">
			   				<li class="price_op"><?=$price->label?><?=(($price->required && $site->getTourRequired()) ? '<em>*</em>' : '')?></li>
			   				<li class="quantity"><input type="text" name="<?=$price->name?>_num" id="<?=$price->name?>" size="3" /> X</li>
			   				<li class="price">
			   					<? if($site->exists($price->base)) { ?><span><?=$site->formatCurrency($price->base)?></span><? } ?>
			   					<?=$site->formatCurrency($price->price)?>
			   				</li>
			   			</ul> 
				   	<? endforeach; ?>
				  
				  	<? if($site->getTourRequired()) { ?>
			   			<span class="memo">At least one marked ( * ) price point is required to book.</span>
			   		<? } ?>
			   		
			   		<? if($item->per > 1) { ?>
			   			<span class="memo">At least <?=$item->per?> people are required to book.</span>
			   		<? } ?>
			    
			    	<span class="checkout_book"><input type="submit" value="<?=$site->getBookNow()?>" class="checkout_book" onclick="return check();"></span>
						
						<div class="error_box">
							<div id="error_text" class="error_text">
							
							</div>
						</div>
						
					</form>
	
				<? } else { ?>
					<span class="no_availability">Sorry, there is no availability left for this option</span>
				<? } ?>

				<? if($site->exists($site->getTriggerState())) { ?>
					<? if($_COOKIE['rezgo_promo']) { ?>
					<div id="promo_entered" class="promo_entered">
						<span class="promocode">Promo:
							<?=$_COOKIE['rezgo_promo']?>
							<a href="javascript:void(0);" onclick="jQuery('#promo_entered').hide(); jQuery('#promo_hidden').fadeIn();">[change]</a>
						</span>
					</div>
					<? } ?>
					
					<div id="promo_hidden" class="promo_hidden" <? if($_COOKIE['rezgo_promo']) { ?>style="display:none;"<? } ?>>
						<form onsubmit="document.location.href = '<?=$_SERVER['REQUEST_URI']?><?=((strpos($_SERVER['REQUEST_URI'], '?') !== false) ? '&' : '?')?>promo=' + jQuery('#promo').val(); return false;">
				  		<span class="promocode">Promo:
				  			<input type="text" class="promo_input" name="promo" id="promo" value="<?=$_COOKIE['rezgo_promo']?>">
								<input type="submit" class="promo_submit" value="apply">
							</span>
						</form>
					</div>
					
				<? } ?>
				
				<? if(count($site->getTours('t=com&q='.$_REQUEST['com'].'&d='.$_REQUEST['date'])) > 1) { ?>
						
					<div id="alternate_op"></div>
					<div class="option_contents">
						<label>Other options for this date:</label>
						<ul class="alt_title">
							<li>
								<span class="alt_op">Option</span>
								<span class="alt_avail">Availability</span>
							</li>
						</ul>
						<ul class="alt_result">
							
							<? foreach($site->getTours('t=com&q='.$_REQUEST['com'].'&d='.$_REQUEST['date']) as $option) { ?>
								<? if($option->uid != $_REQUEST[option]) { ?>
									<li>
										<a href="<?=$site->base?>/details/<?=$item->com?>/<?=$site->seoEncode($item->name)?>/<?=$option->uid?>/<?=$_REQUEST['date']?>">
											<span class="alt_op"><?=$option->time?></span>
											<span class="alt_avail">
												<? if($site->exists($option->date->hide_availability)) { ?>
													available
												<? } elseif($option->date->availability == 0) { ?>
													full
												<? } else { ?>
													<?=$option->date->availability?>
												<? } ?>
											</span>
										</a>
									</li>
								<? } ?>
							<? } ?>
											
						</ul>
					</div>
			
				<? } ?>
				
	    </div>   
    </div>
    
  </div> 
<!-- end tabs test-->

<!-- social media buttons -->
<div class="social_media">
  <a href="javascript:void(0);" id="social_url" onclick="if(jQuery('#short_url').css('display') == 'none') { jQuery('#short_url').fadeIn(); jQuery('#short_url_result').load('<?=$site->base?>/shorturl_ajax.php?url=' + escape(document.location.href), function() { jQuery('#short_url_text').val( jQuery('#short_url_result').html() ); document.getElementById('short_url_text').focus(); document.getElementById('short_url_text').select(); }); } else { jQuery('#short_url').fadeOut(); }"><img src="<?=$site->path?>/images/icon_shorten.png" title="Get Short URL" /></a>
  <a href="javascript:void(0);" id="social_facebook" onclick="window.open('http://www.facebook.com/sharer.php?u=' + escape(document.location.href) + '&t=<?=urlencode($item->name)?>','facebook','location=1,status=1,scrollbars=1,width=600,height=400');"><img src="<?=$site->path?>/images/icon_fb.png" title="Share on Facebook"  alt="Share on Facebook" /></a>
  <a href="javascript:void(0);" id="social_twitter" onclick="window.open('http://twitter.com/share?text=<?=urlencode('I found this great thing to do! "'.$item->name.'"')?>&url=' + escape(document.location.href)<? if($site->exists($site->getTwitterName())) { ?> + '&via=<?=$site->getTwitterName()?>'<? } ?>,'tweet','location=1,status=1,scrollbars=1,width=500,height=350');"><img src="<?=$site->path?>/images/icon_twitter.png" title="Share on Twitter" /></a>	
 	<a href="javascript:void(0);" id="social_tripit" onclick="window.open('http://www.tripit.com/trip_item/createBookmark?url=' + escape(document.location.href) + '&display_name=<?=urlencode($item->name)?>','tripit','location=1,status=1,scrollbars=1,width=970,height=500');"><img src="<?=$site->path?>/images/icon_tripit.png" title="Add to TripIt Clipper" /></a>
  <a href="javascript:(function(){var%20w=window,l=w.location,d=w.document,s=d.createElement('script'),e=encodeURIComponent,x='undefined',u='http://static.travelmuse.com/assets/scripts/bm/tmbm3',b='http://www.travelmuse.com';function%20g(){if(d.readyState&&d.readyState!='complete'){setTimeout(g,200);}else{if(typeof%20TMBM==x)s.setAttribute('src',u+'.js?loc='+e(l)),d.body.appendChild(s);function%20f(){(typeof%20TMBM==x)?setTimeout(f,200):TMBM.init(b,l.href,d.title,false);}f();}}g();}());" id="social_travelmuse"><img src="<?=$site->path?>/images/icon_travel_muse.png" title="Add to Travelmuse" /></a>
  <a href="javascript:void(0);" id="social_duffelup" onclick="javascript: (function(){EN_CLIP_HOST='http://duffelup.com';CLIP_URL=escape(document.location.href);CLIP_TITLE='<?=urlencode($item->name)?>';CLIP_NOTES='';CLIP_ADDRESS='';CLIP_PHONE='';CLIP_TYPE=''; PID='rezgo'; var a=document.createElement('SCRIPT');a.type='text/javascript';a.src=EN_CLIP_HOST+'/javascripts/bookmarklet.js?'+(new Date).getTime()/1E5;document.getElementsByTagName('head')[0].appendChild(a)})(); return false;" title="Clip to Duffel"><img src="<?=$site->path?>/images/icon_duffelup.png" title="Add to Duffelup" /></a>
</div>

<div id="short_url_box">
<div class="short_url" id="short_url" style="display:none;"><span>Short URL:</span> <input type=text id="short_url_text"><div id="short_url_result" style="display:none;"> </div></div>
</div>
<!-- end social media buttons -->

<? endforeach; ?>

</div>
