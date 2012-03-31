<?
	// grab and decode the trans_num if it was set
	$trans_num = $site->decode($_REQUEST['trans_num']);

	// send the user home if they shoulden't be here
	if(!$trans_num) $site->sendTo("/".$current_wp_page."/booking-not-found");
?>

<html>
<head>

</head>
<body>

<link media="all" href="<?=$site->path?>/header.css" type="text/css" rel="stylesheet">
<link media="all" href="<?=$site->path?>/print.css" type="text/css" rel="stylesheet">

<div id="rezgo" class="wrp_book">	
	
<div id="panel_full">

	<? if(!$site->getBookings('q='.$trans_num)) { $site->sendTo("/tour"); } ?>
	
	<? foreach( $site->getBookings('q='.$trans_num) as $booking ): ?>
	
	<? $item = $site->getTours('t=uid&q='.$booking->item_id, 0); ?>
	
	<? $site->readItem($booking) ?>

<!-- use div class="btn_print_one" when you only show invoce btn as below -->
 <!--<div class="btn_print_one"><h1 class="print_invoice"><a href="#">Click to Print a Invoice</a></h1><h1 class="print_voucher"><a href="#">Click to Print a Voucher</a></h1></div>-->
 
  <div class="tour_info">
  <h2>Your Booking (booked on <?=date("F d, Y", (int)$booking->date_purchased)?> / local time)</h2>
  <ol class="tour_receipt">
  	<li class="info"><label>Transaction #</label><span><?=$booking->trans_num?></span></li>
  	<li class="info"><label>You have booked</label><span><?=$booking->tour_name?><br /><?=$booking->option_name?></span></li>

    <li class="info"><label>Date</label><span><?=date("M d, Y", (int)$booking->date)?></span></li>
    <li class="info"><label>Duration</label><span><?=$item->duration?></span></li>
    <li class="info"><label>Location</label>
    	<span>
    		<?=$item->city?><? if($site->exists($item->state)) { ?>, <?=$item->state?><? } ?><? if($site->exists($item->country)) { ?>, <?=$site->countryName($item->country)?><? } ?>
    	</span>
    </li>
    <li class="info"><label>Pickup/Departure Information</label><span><?=$item->schedule->pick_up?></span></li>
    <li class="info"><label>Drop Off/Return Information</label><span><?=$item->schedule->drop_off?></span></li>
    <li class="info"><label>Things to bring</label><span><?=$item->schedule->bring?></span></li>

    <li class="info last"><label>Itinerary</label><span><?=$item->schedule->itinerary?></span></li>
  </ol>
  </div> <!-- end of cart section -->
  <!-- start passenger infor --> <!-- use class=negative for negative price value -->

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
  <h2>Passenger Information</h2>
  	<ol>
  	
  		<? foreach( $site->getBookingForms() as $form ): ?>
  			<? if($form->type == 'checkbox') { ?>
					<? if($site->exists($form->answer)) { $form->answer = 'yes'; } else { $form->answer = 'no'; } ?>
				<? } ?>
  			<li><label><?=$form->question?>:</label><span><?=$form->answer?></span></li>
  		<? endforeach; ?>
  	
	  	<? foreach( $site->getBookingPassengers() as $passenger ): ?>
				<li class="title"><h3><?=$passenger->label?> (<?=$passenger->num?>)</h3></li>
			
				<li><label>Name:</label><span><?=$passenger->first_name?> <?=$passenger->last_name?></span></li>
	    	<li><label>Phone Number:</label><span><?=$passenger->phone_number?></span></li>
	    	<li><label>Email:</label><span><?=$passenger->email_address?></span></li>
	    
				<? foreach( $passenger->forms->form as $form ): ?>
					<? if($form->type == 'checkbox') { ?>
						<? if($site->exists($form->answer)) { $form->answer = 'yes'; } else { $form->answer = 'no'; } ?>
 					<? } ?>
					<li><label><?=$form->question?>:</label><span><?=$form->answer?></span></li>
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
					
					View terms and conditions: <span style="font-weight:bold;">http://<?=$site->getDomain()?>.rezgo.com/terms</span>
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

<script>window.print();</script>

</body>
</html>