<?
	// grab and decode the trans_num if it was set
	$trans_num = $site->decode($_REQUEST['trans_num']);

	// send the user home if they shoulden't be here
	if(!$trans_num) $site->sendTo("/".$current_wp_page."/booking-not-found");

	// start a session so we can grab the analytics code
	session_start();
?>

<? if($_SESSION['REZGO_CONVERSION_ANALYTICS']) { ?>
	<?=$_SESSION['REZGO_CONVERSION_ANALYTICS']?>
			
	<script type="text/javascript">
		var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
		document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
	</script>

	<script type="text/javascript">
		try {
			var pageTracker = _gat._getTracker("<?=(($site->isVendor()) ? 'UA-1943654-6' : 'UA-1943654-4')?>");
			pageTracker._trackPageview();
		} catch(err) {}
	</script>
	
	<? unset($_SESSION['REZGO_CONVERSION_ANALYTICS']); ?>
<? } ?>

<div id="rezgo" class="wrp_book">
	
<div id="panel_full">

	<? if(!$site->getBookings('q='.$trans_num)) { $site->sendTo("/booking-not-found"); } ?>
	
	<!--
		XML Dump for TripIt
		<rezgo> 
			
			<?=$site->get?>
			
		</rezgo>
	-->
	
	<? foreach( $site->getBookings('q='.$trans_num) as $booking ): ?>
	
	<? $item = $site->getTours('t=uid&q='.$booking->item_id, 0); ?>
	
	<? $site->readItem($booking); ?>

	<div class="message">
		<? if($booking->status == 1 OR $booking->status == 4) { ?>
			Thank you for your booking.<br />
    	Your booking is CONFIRMED and is subject to the booking terms and conditions.<br />
    	Click on the following button for your printable voucher.
		<? } ?>
		
		<? if($booking->status == 2) { ?>
			Thank you for your booking.<br />
    	Your booking is PENDING and will be confirmed once payment has been received and 
    	processed based upon the booking terms and conditions.
    <? } ?>
		
		<? if($booking->status == 3) { ?>
			This booking has been CANCELLED.
		<? } ?>
	</div>
	
	<? if($site->exists($booking->paypal_owed)) { ?>
 	<div class="paypal_button">
 	
 		<? $company_paypal = $site->getCompanyPaypal(); ?>
 		
 		<form method="post" action="<?=REZGO_DIR?>/php_paypal/process.php">		
			<input type="hidden" name="firstname" id="firstname" value="<?=$booking->first_name?>">
			<input type="hidden" name="lastname" id="lastname" value="<?=$booking->last_name?>">
			<input type="hidden" name="address1" id="address1" value="<?=$booking->address_1?>"> 
			<input type="hidden" name="address2" id="address2" value="<?=$booking->address_2?>">
			<input type="hidden" name="city" value="<?=$booking->city?>">
			<input type="hidden" name="state" value="<?=$booking->stateprov?>">
			<input type="hidden" name="country" value="<?=$site->countryName($booking->country)?>">
			<input type="hidden" name="zip" value="<?=$booking->postal_code?>">
			<input type="hidden" name="email" id="email" value="<?=$booking->email_address?>">
			<input type="hidden" name="phone" id="phone" value="<?=$booking->phone_number?>">
			
			<input type="hidden" name="item_name" id="item_name" value="<?=$booking->tour_name?> - <?=$booking->option_name?>">
			<input type="hidden" name="encoded_transaction_id" id="encoded_transaction_id" value="<?=$_REQUEST['trans_num']?>">
			<input type="hidden" name="item_number" id="item_number" value="<?=$trans_num?>">
			<input type="hidden" name="amount" id="amount" value="<?=$booking->paypal_owed?>">
			<input type="hidden" name="quantity" id="quantity" value="1">	
			<input type="hidden" name="business" value="<?=$company_paypal?>">
			<input type="hidden" name="currency_code" value="<?=$site->getBookingCurrency()?>">
			<input type="hidden" name="domain" value="<?=$site->getDomain()?>.rezgo.com">
		
			<input type="hidden" name="cid" value="<?=REZGO_CID?>">
			<input type="hidden" name="paypal_signature" value="">
			<input type="image" name="submit_image" src="https://www.paypal.com/en_US/i/btn/x-click-but6.gif" />
			<input type="hidden" name="base_url" value="rezgo.com">
			<input type="hidden" name="cancel_return" value="http://<?=$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']?>">
		</form>
 	
 	</div>
 	<? } ?>
	
	<? if($booking->status == 1 OR $booking->status == 4) { ?>
 	<div class="btn_print_one"><h1 class="print_voucher"><a href="http://<?=$site->getDomain()?>.rezgo.com/voucher/<?=$site->encode($trans_num)?>" target="_blank">Click to Print a Voucher</a></h1></div>
 	<? } ?>
 	
  <div class="tour_info">
	  <div class="header_print">
	  <h2>
	  	Your Booking (booked on <?=date("F d, Y", (int)$booking->date_purchased_local)?> / local time)
	  </h2>
	  <span class="print_receipt"><a href="<?=$_SERVER['REQUEST_URI']?>/print">print</a></span>
	</div>
  <ol class="tour_receipt">
  	<li class="info"><label>Transaction #</label><span><?=$booking->trans_num?></span></li>
  	<li class="info"><label>You have booked</label><span><?=$booking->tour_name?><br /><?=$booking->option_name?></span></li>

    <li class="info"><label>Date</label><span><?=date("F d, Y", (int)$booking->date)?></span></li>
    <li class="info"><label>Duration</label><span><?=$item->duration?></span></li>
    <li class="info"><label>Location</label>
    	<span>
    		<?
	    		unset($loc);
	    		if($site->exists($item->city)) $loc[] = $item->city;
	    		if($site->exists($item->state)) $loc[] = $item->state;
	    		if($site->exists($item->country)) $loc[] = ucwords($site->countryName($item->country));
	    	
	    		if($loc) echo implode(', ', $loc);
	    	?>
    	</span>
    </li>
    <li class="info"><label>Pickup/Departure Information</label><span><?=$item->schedule->pick_up?></span></li>
    <li class="info"><label>Drop Off/Return Information</label><span><?=$item->schedule->drop_off?></span></li>
    <li class="info"><label>Things to bring</label><span><?=$item->schedule->bring?></span></li>

    <li class="info last"><label>Itinerary</label><span><?=$item->schedule->itinerary?></span></li>
  </ol>
  </div> <!-- end of cart section -->
  <!-- start passenger information --> <!-- use class=negative for negative price value -->

	<div style="page-break-after:always;"></div>

  <div class="tour_info">
  <h2>Payment Information</h2>
  	<ol class="tour_receipt">
    <li class="info"><label>Name</label><span><?=$booking->first_name?> <?=$booking->last_name?></span></li>
    <li class="info"><label>Address</label>
    	<span>
    		<?=$booking->address_1?><? if($site->exists($booking->address_2)) { ?>, <?=$booking->address_2?><? } ?><? if($site->exists($booking->city)) { ?>, <?=$booking->city?><? } ?><? if($site->exists($booking->stateprov)) { ?>, <?=$booking->stateprov?><? } ?><? if($site->exists($booking->postal_code)) { ?>, <?=$booking->postal_code?><? } ?>, <?=$site->countryName($booking->country)?>
    	</span>
    </li>

    <li class="info"><label>Phone Number</label><span><?=$booking->phone_number?></span></li>
    <li class="info"><label>Email Address</label><span><?=$booking->email_address?></span></li>
    
    <? if($booking->overall_total > 0) { ?>
    	<li class="info"><label>Payment Method</label><span><?=$booking->payment_method?></span></li>
    	<? if($booking->payment_method == 'Credit Cards') { ?><li class="info"><label>Card Number</label><span><?=$booking->card_number?></span></li><? } ?>
   	<? } ?>
   	
   	<? if($site->exists($booking->trigger_code)) { ?><li class="info"><label>Promotional Code</label><span><?=$booking->trigger_code?></span></li><? } ?>
    
    <li class="info last"><label>Charges</label>

    	<fieldset>
    	<ol class="price">
      	<li class="info">
      	<label class="type">type</label><label class="qty">qty</label><label class="cost">cost</label><label class="line_total">total</label>
        </li>
        
        <? foreach( $site->getBookingPrices() as $price ): ?>
        	<li class="info">
        		<span class="type"><? if($site->exists($price->base)) { ?><span class="discount"></span><? } ?><?=$price->label?></span>
        		<span class="qty"><? if($site->exists($price->base)) { ?><span class="discount"></span><? } ?><?=$price->number?></span>
        		<span class="cost">
        			<? if($site->exists($price->base)) { ?><span class="discount"><?=$site->formatCurrency($price->base)?></span><? } ?>
        			<?=$site->formatCurrency($price->price)?>
        		</span>
        		<span class="line_total"><? if($site->exists($price->base)) { ?><span class="discount"></span><? } ?><?=$site->formatCurrency($price->total)?></span>
        	</li>
				<? endforeach; ?>
        
        <li class="info"><label class="subtotal">Subtotal</label><span class="subtotal"><?=$site->formatCurrency($booking->sub_total)?></span></li>
        
        <? foreach( $site->getBookingLineItems() as $line ): ?>
        	<li class="info"><label class="tax_fees"><?=$line->label?></label><span class="tax_fees"><?=$site->formatCurrency($line->amount)?></span></li>
        <? endforeach; ?>
        
        <? foreach( $site->getBookingFees() as $fee ): ?>
        	<? if( $site->exists($fee->total_amount) ): ?>
        		<li class="info"><label class="extra"><?=$fee->label?></label><span class="extra negative"><?=$site->formatCurrency($fee->total_amount)?></span></li>
        	<? endif; ?>
        <? endforeach; ?>
        
        <li class="info"><label class="total">TOTAL</label><span class="total"><?=$site->formatCurrency($booking->overall_total)?></span></li>
        
        <? if($site->exists($booking->deposit)) { ?>
        <li class="info"><label class="deposit">Deposit</label><span class="deposit"><?=$site->formatCurrency($booking->deposit)?></span></li>
      	<? } ?>
      	
      	<? if($site->exists($booking->overall_paid)) { ?>
        <li class="info"><label class="paid">Total Paid</label><span class="paid"><?=$site->formatCurrency($booking->overall_paid)?></span></li>
      	
      	<li class="info"><label class="owing">OWING</label><span class="owing"><?=$site->formatCurrency(((float)$booking->overall_total - (float)$booking->overall_paid))?></span></li>
      	<? } ?>
      </ol>
      </fieldset>
    </li>
    </ol>
	</div>

	<? if(count($site->getBookingForms()) > 0 OR count($site->getBookingPassengers()) > 0) { ?>
  
  <div class="passenger_info">
  <h2>Customer Information</h2>
  	<ol>
  	
  		<? foreach( $site->getBookingForms() as $form ): ?>
  			<? if($form->type == 'checkbox') { ?>
					<? if($site->exists($form->answer)) { $form->answer = 'yes'; } else { $form->answer = 'no'; } ?>
				<? } ?>
  			<li><label><?=$form->question?></label><span><?=$form->answer?></span></li>
  		<? endforeach; ?>
  	
	  	<? foreach( $site->getBookingPassengers() as $passenger ): ?>
				<li class="title"><h3><?=$passenger->label?> (<?=$passenger->num?>)</h3></li>
			
				<li><label>Name</label><span><?=$passenger->first_name?> <?=$passenger->last_name?></span></li>
	    	<li><label>Phone Number</label><span><?=$passenger->phone_number?></span></li>
	    	<li><label>Email</label><span><?=$passenger->email_address?></span></li>
	    
				<? foreach( $passenger->forms->form as $form ): ?>
					<? if($form->type == 'checkbox') { ?>
						<? if($site->exists($form->answer)) { $form->answer = 'yes'; } else { $form->answer = 'no'; } ?>
 					<? } ?>
					<li><label><?=$form->question?></label><span><?=$form->answer?></span></li>
				<? endforeach; ?>
			<? endforeach; ?>
		
    </ol>
  </div>
  <? } ?>
  
	<div class="customer_service">
  	<h2>Customer Service</h2>
  	<fieldset>
		  <ol>
				<li>Cancellation Policy</li>
				
				<li class="payment_terms">
					<? if($site->exists($booking->rezgo_gateway)) { ?>
						
						Canceling a booking with Rezgo can result in cancellation fees being
						applied by Rezgo, as outlined below. Additional fees may be levied by
						the individual supplier/operator (see your Rezgo Voucher for specific
						details). When canceling any booking you will be notified via email,
						facsimile or telephone of the total cancellation fees.<br>
						<br>
						1. Event, Attraction, Theater, Show or Coupon Ticket<br>
						These are non-refundable in all circumstances.<br>
						<br>
						2. Gift Certificate<br>
						These are non-refundable in all circumstances.<br>
						<br>
						3. Tour or Package Commencing During a Special Event Period<br>
						These are non-refundable in all circumstances. This includes,
						but is not limited to, Trade Fairs, Public or National Holidays,
						School Holidays, New Year's, Thanksgiving, Christmas, Easter, Ramadan.<br>
						<br>
						4. Other Tour Products & Services<br>
						If you cancel at least 7 calendar days in advance of the
						scheduled departure or commencement time, there is no cancellation
						fee.<br>
						If you cancel between 3 and 6 calendar days in advance of the
						scheduled departure or commencement time, you will be charged a 50%
						cancellation fee.<br>
						If you cancel within 2 calendar days of the scheduled departure
						or commencement time, you will be charged a 100% cancellation fee.
						<br><br>
					<? } else { ?>
						<? if($site->exists($item->details->cancellation)) { ?>
							<?=$item->details->cancellation?>
							<br><br>
						<? } ?>
					<? } ?>
					
					<a href="javascript:void(0);" onclick="javascript:window.open('<?=$site->base?>/terms_popup', 'mywindow', 'menubar=1,resizable=1,scrollbars=1,width=800,height=600');">Click here to view terms and conditions.</a>
				</li>
				
				<? if($site->exists($booking->rid)) { ?>
				<li>Customer Service</li>
				<li class="receipt_contact">
					<? if($site->exists($booking->rezgo_gateway)) { ?>
						
						Rezgo.com<br>
						Attn: Partner Bookings<br>
						92 Lonsdale Avenue<br>
						Suite 200<br>
						North Vancouver, BC<br>
						Canada V7M 2E6<br>
						(604) 983-0083<br>
						bookings@rezgo.com
						
					<? } else { ?>
									
			    	<? $company = $site->getCompanyDetails('p'.$booking->rid); ?>
			    	
			    	<?=$company->company_name?><br>
						<?=$company->address_1?> <?=$company->address_2?><br>
						<?=$company->city?>, <? if($site->exists($company->state_prov)) { ?><?=$company->state_prov?>, <? } ?><?=$site->countryName($company->country)?><br>
			    	<?=$company->postal_code?><br>
			    	<?=$company->phone?><br>
			    	<?=$company->email?>
			    	<? if($site->exists($company->tax_id)) { ?>
		  			<br>
		  			<br>
		  			<?=$company->tax_id?>
		  			<? } ?>

					<? } ?>

				</li>
				<? } ?>
				
				<li>Service Provided By</li>
				<li class="receipt_contact">
		    	<? $company = $site->getCompanyDetails($booking->cid); ?>
		    	
		    	<?=$company->company_name?><br>
					<?=$company->address_1?> <?=$company->address_2?><br>
					<?=$company->city?>, <? if($site->exists($company->state_prov)) { ?><?=$company->state_prov?>, <? } ?><?=$site->countryName($company->country)?><br>
		    	<?=$company->postal_code?><br>
		    	<?=$company->phone?><br>
		    	<?=$company->email?>
		    	<? if($site->exists($company->tax_id)) { ?>
	  			<br>
	  			<br>
	  			<?=$company->tax_id?>
	  			<? } ?>
				</li>

			</ol>
	  </fieldset> 
  </div>

	<? endforeach; ?>

</div><!-- end of panel_full--> 
<div class="clear"></div> <!-- do not take this out -->
</div><!--end rezgo wrp-->