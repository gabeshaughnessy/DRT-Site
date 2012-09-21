<? if(!$_REQUEST['search_for'] AND !$_REQUEST['start_date'] AND !$_REQUEST['end_date'] AND !$_REQUEST['tags'] AND (!$_REQUEST['pg'] or $_REQUEST['pg'] == 1)) { ?>
	<div class="intro"><?=$site->getIntro()?></div>
<? } ?>

<div id="rezgo" class="wrp_list">

<div id="left_panel">
	<div class="breadcrumb">	
		<? if($_REQUEST['search_for'] OR $_REQUEST['start_date'] OR $_REQUEST['end_date'] OR $_REQUEST['tags'] OR $_REQUEST['cid']) { ?>
			<h1 class="header">
			Results
			<? if($_REQUEST['search_for']) { ?> for keyword <a href="<?=$site->base?>/?start_date=<?=$_REQUEST['start_date']?>&end_date=<?=$_REQUEST['end_date']?>">"<?=stripslashes($_REQUEST['search_for'])?>"</a><? } ?>
			<? if($_REQUEST['tags']) { ?> tagged with <a href="<?=$site->base?>/?start_date=<?=$_REQUEST['start_date']?>&end_date=<?=$_REQUEST['end_date']?>">"<?=$_REQUEST['tags']?>"</a><? } ?>
			<? if($_REQUEST['cid']) { ?> supplied by <a href="<?=$site->base?>/?start_date=<?=$_REQUEST['start_date']?>&end_date=<?=$_REQUEST['end_date']?>">"<?=$site->getCompanyName($_REQUEST['cid'])?>"</a><? } ?>
			<? if($_REQUEST['start_date'] AND $_REQUEST['end_date']) { ?>
			 between <a href="<?=$site->base?>/?search_in=<?=$_REQUEST['search_in']?>&search_for=<?=$_REQUEST['search_for']?>&tags=<?=$_REQUEST['tags']?>"><?=$_REQUEST['start_date']?> and <?=$_REQUEST['end_date']?></a>
			<? } elseif($_REQUEST['start_date']) { ?>
			 on <a href="<?=$site->base?>/?search_in=<?=$_REQUEST['search_in']?>&search_for=<?=$_REQUEST['search_for']?>&tags=<?=$_REQUEST['tags']?>"><?=$_REQUEST['start_date']?></a>
			<? } elseif($_REQUEST['end_date']) { ?>
			 on <a href="<?=$site->base?>/?search_in=<?=$_REQUEST['search_in']?>&search_for=<?=$_REQUEST['search_for']?>&tags=<?=$_REQUEST['tags']?>"><?=$_REQUEST['end_date']?></a>
			<? } ?>
			</h1>
			<a href="<?=$site->base?>/" class="clear_search">clear search</a>
		<? } else { ?>
			<h1 class="header">All Results</h1>
		<? } ?>
	</div>
	
	<form id="flight">
		
  	<label>
  		Start Date <br />  
   		<input type="date" name="start_date" data-value="<?=(($_REQUEST['start_date']) ? date("Y-m-d", strtotime($_REQUEST['start_date'])) : 0)?>" value="<?=(($_REQUEST['start_date']) ? $_REQUEST['start_date'] : 'Today')?>" /> 
		</label>
   
		<label> 
   		End Date <br /> 
   		<input type="date" name="end_date" data-value="<?=(($_REQUEST['end_date']) ? date("Y-m-d", strtotime($_REQUEST['end_date'])) : 1)?>" value="<?=(($_REQUEST['end_date']) ? $_REQUEST['end_date'] : 'Tomorrow')?>" /> 
		</label>
		
		<span class="date_apply"><input class="btn_search" type=submit value="apply"></span>
	</form>
	
	<? if(!$site->getTours()) { ?>
		<div class="item">Sorry, there were no results for your search.</div>
	<? } ?>

	<script>
		jQuery(":date").dateinput({ trigger: true, format: 'mmmm dd, yyyy', min: -1 })
		
		// use the same callback for two different events. possible with bind
		jQuery(":date").bind("onShow onHide", function()  {
			jQuery(this).parent().toggleClass("active"); 
		});
		
		// when first date input is changed
		jQuery(":date:first").data("dateinput").change(function() {
				
			// we use it's value for the seconds input min option
			jQuery(":date:last").data("dateinput").setMin(this.getValue(), true);
		});
	</script>
	
	<?		
		$tourList = $site->getTours();
		
		if($tourList[REZGO_RESULTS_PER_PAGE]) { // if the 11th (key 10) response exists, set the 'more' button and unset it
			$moreButton = 1;
			unset($tourList[REZGO_RESULTS_PER_PAGE]);
			$nextPage = $_REQUEST['pg'] + 1;
		}
		
		if($tourList[0] AND $_REQUEST['pg'] > 1) { // if we are not on page 1 and items exist, then show the past pages section
			$backButton = 1;
		}
		
	?>

	<? foreach( $tourList as $item ): ?>
	
	<? $site->readItem($item) ?>

	<div class="item">
    <div class="image">
    	<a href="<?=$site->base?>/details/<?=$item->com?>/<?=$site->seoEncode($item->name)?>">
    		<img src="http://images.rezgo.com/items/<?=$item->cid?>-<?=$item->com?>.jpg" border="0" />
    	</a>
    </div>
    <h1 class="tour_title"><a href="<?=$site->base?>/details/<?=$item->com?>/<?=$site->seoEncode($item->name)?>"><?=$item->name?></a></h1>
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
    <div class="intro"><?=$item->details->introduction?></div>
    
    <? if($site->exists($item->starting)) { ?>
    	<div class="price"><span>Starting From:&nbsp;</span><?=$site->formatCurrency($item->starting)?></div>
    <? } ?>
    <div class="btn_details"><a href="<?=$site->base?>/details/<?=$item->com?>/<?=$site->seoEncode($item->name)?>">details</a></div>
    
    <? if( $_REQUEST['start_date'] AND count($site->getTourAvailability($item)) == 0 ) { ?>
    
    	<div id="date_search_result"><span class="unavail_option">no available options during this date range</span></div>
    
    <? } else if( $_REQUEST['start_date'] AND $site->getTourAvailability($item) ) { ?>
  		<!-- slider for searched items -->
		 	<div id="date_search_result"><span class="avail_option">available options</span>
				<div class="searched_item">
				
	    		<? foreach( $site->getTourAvailability($item) as $day ): ?>
	    		
	    			<?
							// some php for handling pagination of the options
							$c = $p = 1;
							$totalPages = ceil(count($day->items) / 7);
						?>
	    		
	    			<div class="search_date" id="day_<?=$day->id?>">
	    				<a href="#" class="day_select" rel="#content_<?=$day->id?>">
			     			<span class="date_box"><?=date("M d, Y", $day->date)?></span>
			     			<span class="avail">available</span>
			     			<span class="select">select</span>
		     			</a>
		     			<div class="overlay" id="content_<?=$day->id?>">
		     			
								<div id="rezgo_popup_book">
									<div class="header">
								  	<label>Availability for</label>
								    <h3 class="popup_title"><?=date("F d, Y", $day->date)?></h3>
										<h1 class="popup_title"><?=$item->name?></h1>
								  </div>
								  <div class="wrp">
								  	<div class="modal_titles">
								  		<div class="title_01">Option</div>
								  		<div class="title_02">Availability</div>
								  		<div class="title_03">&nbsp;</div>
										</div>
								
										<div id="cal_page_<?=$day->id?>_<?=$day->date?>_<?=$p++?>">
											<? foreach( $day->items as $option ): ?>
											
												<? if($c == 8) { ?>
													<? $c = 2; ?>
													</div>
													<div style="display:none;" id="cal_page_<?=$day->id?>_<?=$day->date?>_<?=$p++?>">
												<? } else { ?>
													<? $c++; ?>
												<? } ?>
											
										  	<div class="result_01"><?=$option->name?></div><div class="result_02"><? if($option->availability == 0) { ?>full<? } elseif($option->availability == '9999' OR $option->hide_availability == 1) { ?>available<? } else { ?><?=$option->availability?><? } ?></div><div class="result_03"><span><a href="<?=$site->base?>/details/<?=$item->com?>/<?=$site->seoEncode($item->name)?>/<?=$option->uid?>/<?=date("Y-m-d", $day->date)?>">book now</a></span></div>
									  		
									  	<? endforeach; ?>
							  		</div>
							  		
								  </div>
								  <div class="paging"><a href="javascript:void(0);" onclick="prev_cal_page('<?=$day->date?>', '<?=$day->id?>', <?=$totalPages?>);"><img src="<?=$site->path?>/images/arrow_left.png" border="0" /></a>page <span id="page_<?=$day->id?>_<?=$day->date?>">1</span> of <?=$totalPages?><a href="javascript:void(0);" onclick="next_cal_page('<?=$day->date?>', '<?=$day->id?>', <?=$totalPages?>);"><img src="<?=$site->path?>/images/arrow_right.png" border="0" /></a></div>
								</div>
							
							</div>

		     		</div>
		     		
		     		<script>
		     			
		     		
		     			if(jQuery.browser.msie && jQuery.browser.version < 8) {
		     				jQuery("#day_<?=$day->id?> a[rel]").overlay({closeOnClick: true});
		     			} else {
		     				jQuery("#day_<?=$day->id?> a[rel]").overlay({effect: 'apple', closeOnClick: true});
		     			}
		     		</script>
							
					<? endforeach; ?>
					
				</div>
			</div> 
		<? } ?>
	
 	</div>
 	
 	<? endforeach; ?>
 	
 	<script>
		function next_cal_page(date, id, totalPages) {
			var current_page = jQuery('#page_' + id + '_' + date).html();
			if(current_page < totalPages) {
				jQuery('#cal_page_' + id + '_' + date + '_' + current_page).hide();
				jQuery('#cal_page_' + id + '_' + date + '_' + ++current_page).fadeIn();
				jQuery('#page_' + id + '_' + date).html(current_page);
			}
		}
		
		function prev_cal_page(date, id, totalPages) {
			var current_page = jQuery('#page_' + id + '_' + date).html();
			if(current_page > 1) {
				jQuery('#cal_page_' + id + '_' + date + '_' + current_page).hide();
				jQuery('#cal_page_' + id + '_' + date + '_' + --current_page).fadeIn();
				jQuery('#page_' + id + '_' + date).html(current_page);
			}
		}
	</script>
 	
 	<? if($backButton) { ?>
	 	<div class="prev_results">
	 		<a href="?pg=<?=($_REQUEST['pg']-1)?><? if($_REQUEST['start_date']) { ?>&start_date=<?=$_REQUEST['start_date']?>&end_date=<?=$_REQUEST['end_date']?><? } ?>">previous page</a> | jump to page:  
	 		<? for($p=1; $p < $_REQUEST['pg']; $p++) { ?>
	 			<a href="?pg=<?=$p?><? if($_REQUEST['start_date']) { ?>&start_date=<?=$_REQUEST['start_date']?>&end_date=<?=$_REQUEST['end_date']?><? } ?>"><?=$p?></a>
	 		<? } ?>
	 	</div>
 	<? } ?>
 	
 	<? if($moreButton) { ?>
	 	<div class="more_results">
	 		<a href="?pg=<?=$nextPage?><? if($_REQUEST['start_date']) { ?>&start_date=<?=$_REQUEST['start_date']?>&end_date=<?=$_REQUEST['end_date']?><? } ?>">more results</a>
	 	</div>
 	<? } ?>
 	
</div>