<?php	
	$site->getCalendar($_REQUEST['item_id'], $_REQUEST['date']);
	
	// if this calendar is not active and jump=1 is passed, make up to 10 attempts to find availability
	if(!$site->getCalendarActive() && $_REQUEST['jump'] == 1) {
		$attempt = $_REQUEST['attempt'] + 1;
		
		if($attempt <= 12) {
			$site->sendTo($site->base.'/calendar.php?item_id='.$_REQUEST['item_id'].'&date='.$site->getCalendarNext().'&jump=1&attempt='.$attempt);
		}
	}

	$months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"); 
?>

<table border=0 cellspacing=1 cellpadding=1 id="calendar_container">	
	<tr>
		<td colspan=7 align=center>
			<table border=0 cellspacing=0 cellpadding=0 width=100%>
				<tr>
					<td id="prev_button" class="close" onclick="change_cal('item_id=<?=$_REQUEST[item_id]?>&date=<?=$site->getCalendarPrev()?>');"> </td>
					<td align=center nowrap id="date_selection">
						
						<select name=month onchange="change_cal('item_id=<?=$_REQUEST[item_id]?>&date=' + this.value);">
							<? foreach( $site->getCalendarMonths() as $month ): ?>
									<option <?=$month->selected?> value="<?=$month->value?>"><?=$months[$month->label]?></option>
							<? endforeach; ?>
						</select>
						
						<select name=year onchange="change_cal('item_id=<?=$_REQUEST[item_id]?>&date=' + this.value);">
							<? foreach( $site->getCalendarYears() as $year ): ?>
								<option <?=$year->selected?> value="<?=$year->value?>"><?=$year->label?></option>
							<? endforeach; ?>				
						</select>
						
					</td>
					<td align=right id="next_button" onclick="change_cal('item_id=<?=$_REQUEST[item_id]?>&date=<?=$site->getCalendarNext()?>');"> </td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="daysofweek" align=center>Su</td>
		<td class="daysofweek" align=center>M</td>
		<td class="daysofweek" align=center>Tu</td>
		<td class="daysofweek" align=center>W</td>
		<td class="daysofweek" align=center>Th</td>
		<td class="daysofweek" align=center>F</td>
		<td class="daysofweek" align=center>Sa</td>
	</tr>

	<? 
		$w = 1;
		foreach( $site->getCalendarDays() as $day ): 
	?>
		<? if($w == 1) { ?><tr><? } ?>
		
		<?
			// some php for handling pagination of the options
			$c = $p = 1;
			$totalPages = ceil(count($day->items) / 7);
		?>
		
		<? if(!$day->type) { ?>
			<td class="day leads" align=center><?=$day->day?></td>
		<? } else { ?>
		
			<?
				if($day->cond == 'a') { $class = 'available'; }
				elseif($day->cond == 'f') { $class = 'full'; }
				else { $class = 'unavailable'; }
			?>
			
			
			<td id="cal_box_<?=$day->day?>" class="day <?=$class?>" align=center>
				<? if($class == 'available') { ?>
					<span id="day_<?=$day->day?>"><a class="" rel="#content_<?=$day->day?>" onclick="remove_arrow()"><?=$day->day?></a></span>
						
					<div class="overlay" id="content_<?=$day->day?>">
					
						<div id="rezgo_popup_book">
							<div class="header">
						  	<label>Availability for</label>
						    <h3 class="popup_title"><?=date("F d, Y", $day->date)?></h3>
								<h1 class="popup_title"><?=$site->getCalendarName()?></h1>
						  </div>
						  <div class="wrp">
						  	<div class="modal_titles">
						  		<div class="title_01">Option</div>
						  		<div class="title_02">Availability</div>
						  		<div class="title_03">&nbsp;</div>
								</div>
								
								<div id="cal_page_<?=$day->date?>_<?=$p++?>">
									<? foreach( $day->items as $option ): ?>
										
										<? if($c == 8) { ?>
											<? $c = 2; ?>
											</div>
											<div style="display:none;" id="cal_page_<?=$day->date?>_<?=$p++?>">
										<? } else { ?>
											<? $c++; ?>
										<? } ?>
					
					  				<div class="result_01"><?=$option->name?></div>
					  				<div class="result_02"><? if($option->availability == 'h') { ?>available<? } elseif($option->availability == 0) { ?>full<? } else { ?><?=$option->availability?><? } ?></div>
					  				<div class="result_03"><span><a href="<?=$site->base?>/details/<?=$site->getCalendarId()?>/<?=$site->seoEncode($site->getCalendarName())?>/<?=$option->uid?>/<?=date("Y-m-d", $day->date)?>">book now</a></span></div>
					  		
					  			<? endforeach; ?>
					  		</div>
					  	
					  	</div>
					  <div class="paging"><a href="javascript:void(0);" onclick="prev_cal_page('<?=$day->date?>', <?=$totalPages?>);"><img src="<?=$site->path?>/images/arrow_left.png" border="0" /></a>page <span id="page_<?=$day->date?>">1</span> of <?=$totalPages?><a href="javascript:void(0);" onclick="next_cal_page('<?=$day->date?>', <?=$totalPages?>);"><img src="<?=$site->path?>/images/arrow_right.png" border="0" /></a></div>
					</div>
						
					<script>
						if($.browser.msie && $.browser.version < 8) {
	     				$("#day_<?=$day->day?> a[rel]").overlay({closeOnClick: true});
	     			} else {
	     				$("#day_<?=$day->day?> a[rel]").overlay({effect: 'apple', closeOnClick: true});
	     			}
					</script>
					
				</div>
				<? } else { ?>
					<?=$day->day?>
				<? } ?>	
			</td>
		
		<? } ?>
		
		<? if($w == 7) { ?></tr><? $w = 1; } else { $w++; } ?>
	<? endforeach; ?>			
</table>