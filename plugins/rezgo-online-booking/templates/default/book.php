<script type="text/javascript" src="<?=$site->path?>/javascript/jquery.scrollTo.min.js"></script>
<script type="text/javascript" src="<?=$site->path?>/javascript/jquery.form.js"></script>

<div id="rezgo" class="wrp_book">

<div class="modal" id="prompt">
	<h2>Your booking is being completed</h2>
	
	<center>
		<img src="<?=$site->path?>/images/booking_load.gif">
		Please wait a moment...
	</center>
</div>

<div id="panel_full">
	<div class="breadcrumb"><a href="<?=$site->base?>/?search=restore" class="back"><span><<</span> Back to Results</a></div>
	<div class="header"><h1>Your Booking Details</h1><h2 class="on" id="step_1" onclick="stepBack();">{ 1. BOOK }</h2><h2 class="off" id="step_2" onclick="stepForward();">{ 2. PAY }</h2></div>

  <div class="header_shadow"><img src="<?=$site->path?>/images/header_crumb_left.png" style="float:left" /><img src="<?=$site->path?>/images/header_crumb_right.png" style="float:right;" /></div>
	
	<? if(!$_REQUEST['uid'] || !$_REQUEST['date'] || !$site->getTours('t=uid&q='.$_REQUEST['uid'].'&d='.$_REQUEST['date'].$site->getPaxString())) { $site->sendTo("/tour"); } // #jm - should this be hardcoded as "/tour"? ?>
	
	<? foreach( $site->getTours('t=uid&q='.$_REQUEST['uid'].'&d='.$_REQUEST['date'].$site->getPaxString()) as $item ): ?>
	
		<? $site->readItem($item) ?>
		
		<script>
			var elements = new Array();
			var total = <?=$item->overall_total?>;
			
			function add_element(id, name, price) {		
				var num = add_price = parseFloat(price);
				if(elements[id]) num = num + parseFloat(elements[id]);
				
				var price = num.toFixed(<?=$item->currency_decimals?>);

				var display_price = '<?=$item->currency_symbol?>' + price;
				
				if(!elements[id]) {
					var content = '<li class="info" id="element_' + id + '"><label class="extra">' + name + '</label><span class="extra price_neg" id="val_' + id + '">' + display_price + '</span></li>';
					jQuery("#fee_box").html( jQuery("#fee_box").html() + content );
				} else {
					if(document.getElementById('element_' + id).style.display == 'none') document.getElementById('element_' + id).style.display = '';
					jQuery("#val_" + id).html(display_price);
				}	
				elements[id] = price;
				
				// add to total amount
				total = parseFloat(total) + add_price;
				total = total.toFixed(<?=$item->currency_decimals?>);
				jQuery("#total_value").html('<?=$item->currency_symbol?>' + total);
			
				// if total is greater than 0 then appear payment section
				if(total > 0) document.getElementById('payment_info').style.display = '';
			}
			
			function sub_element(id, price) {
				if(!elements[id] || elements[id] == 0) return false;
			
				var num = sub_price = parseFloat(price);
				num = parseFloat(elements[id]) - num;
				
				var price = num.toFixed(<?=$item->currency_decimals?>);
				if(price < 0) price = 0;
				
				var display_price = '<?=$item->currency_symbol?>' + price;
				
				if(price == 0) {	
					document.getElementById('element_' + id).style.display = 'none';
				} else {
					document.getElementById('val_' + id).innerHTML = display_price;
				}	
				elements[id] = price;
				
				// sub from total amount
				total = parseFloat(total) - sub_price;
				total = total.toFixed(<?=$item->currency_decimals?>);
				jQuery("#total_value").html('<?=$item->currency_symbol?>' + total);
			
				// if total is less than 0 then disappear payment section
				if(total <= 0) document.getElementById('payment_info').style.display = 'none';
			}
		</script>
	
		<form method="post" id="book" action="<?=$site->base?>/book_ajax.php">
	  
	  <!-- pass some hidden data to our form -->
	  <input type="hidden" name="rezgoAction" value="book">
	  
	  <input type="hidden" name="book" value="<?=$_REQUEST['uid']?>"> 
	  <input type="hidden" name="date" value="<?=$_REQUEST['date']?>">
	  
	  <input type="hidden" name="adult_num" value="<?=$_REQUEST['adult_num']?>">
	  <input type="hidden" name="child_num" value="<?=$_REQUEST['child_num']?>">
	  <input type="hidden" name="senior_num" value="<?=$_REQUEST['senior_num']?>">
	  <input type="hidden" name="price4_num" value="<?=$_REQUEST['price4_num']?>">
	  <input type="hidden" name="price5_num" value="<?=$_REQUEST['price5_num']?>">
	  <input type="hidden" name="price6_num" value="<?=$_REQUEST['price6_num']?>">
	  <input type="hidden" name="price7_num" value="<?=$_REQUEST['price7_num']?>">
	  <input type="hidden" name="price8_num" value="<?=$_REQUEST['price8_num']?>">
	  <input type="hidden" name="price9_num" value="<?=$_REQUEST['price9_num']?>">
	  
	  <div id="content_1">
	  
	  <!-- cart section -->
	  <div class="tour_info">
		  <h2>Your Booking</h2>
		  <fieldset>
		  <ol class="tour_receipt">
		  	<li class="info"><label>You are booking</label><span><?=$item->name?><br /><?=$item->time?></span></li>
		
		    <li class="info"><label>Date</label><span><?=date("F d, Y", strtotime($_REQUEST['date']))?></span></li>
		    <li class="info"><label>Duration</label><span><?=$item->duration?></span></li>
		   	<? if($_COOKIE['rezgo_promo']) { ?>
		   	<li class="info"><label>Promotional Code</label><span><?=$_COOKIE['rezgo_promo']?></span></li>
		   	<? } ?>
		    <li class="info last"><label>Price</label>
		    	<ol class="price">
		      	<li class="info">
		          <label class="type">type</label>
		
		          <label class="qty">qty</label>
		          <label class="cost">cost</label>
		          <label class="line_total">total</label>
		        </li>
		        
		        <? foreach( $site->getTourPrices() as $price ): ?>
		        	<? if($_REQUEST[$price->name.'_num']) { ?>
			        	<li class="info">
				        	<span class="type"><? if($site->exists($price->base)) { ?><span class="discount"></span><? } ?><?=$price->label?></span>
				        	<span class="qty"><? if($site->exists($price->base)) { ?><span class="discount"></span><? } ?><?=$_REQUEST[$price->name.'_num']?></span>
				        	<span class="cost">
				        		<? if($site->exists($price->base)) { ?><span class="discount"><?=$site->formatCurrency($price->base)?></span><? } ?>
				        		<?=$site->formatCurrency($price->price)?>
				        	</span>
				        	<span class="line_total"><? if($site->exists($price->base)) { ?><span class="discount"></span><? } ?><?=$site->formatCurrency($price->total)?></span>
				        </li>
		        	<? } ?>
		        <? endforeach; ?>
		      	
						<li class="info"><label class="subtotal">Subtotal</label><span class="subtotal"><?=$site->formatCurrency($item->sub_total)?></span></li>
					        
		      </ol>
		    </li>
		  </ol>
		  </fieldset>
	  </div>
	  <!-- end of receipt section -->
	
		<? if($item->group != 'hide') { ?>
		
		<!-- start passenger information --> <!-- use class=negative for negative price value -->
	  <p class="notation">To finish your booking, please complete the following form. Please note that fields marked with <em>*</em> are required.</p>
	  
	  <div class="booking_info">
	  
	  <? foreach( $site->getTourPrices($item) as $price ): ?>
	  	<? foreach( $site->getTourPriceNum($price) as $num ): ?>
	  
			  <h2 class="title"><?=$price->label?> <?=$num?></h2>
			  <fieldset>
			  	<ol>
			
			      <li class="half"><label for="fname">First Name<? if($item->group == 'require') { ?><em>*</em><? } ?></label><input type="text" id="fname" name="tour_group[<?=$price->name?>][<?=$num?>][first_name]" value=""<? if($item->group == 'require') { ?> required="required"<? } ?> /></li>
			      <li class="half"><label for="lname">Last Name<? if($item->group == 'require') { ?><em>*</em><? } ?></label><input type="text" id="lname" name="tour_group[<?=$price->name?>][<?=$num?>][last_name]" value=""<? if($item->group == 'require') { ?> required="required"<? } ?> /></li>
			      <li class="half"><label for="phone">Phone Number</label><input type="text" id="phone" name="tour_group[<?=$price->name?>][<?=$num?>][phone]" value="" /></li>
			      <li class="half"><label for="email">Email Address</label><input type="email" id="email" name="tour_group[<?=$price->name?>][<?=$num?>][email]" value="" /></li>
			  		
			  		<? foreach( $site->getTourForms(group) as $form ): ?>
	  					
	  					<? if($form->type == 'text') { ?>
	  						<li class="hr"></li>
	  						<li>
	  							<label><?=$form->label?><? if($form->require) { ?><em>*</em><? } ?></label>
	  							<span><?=$form->comments?></span>
			    				<input type="text" name="tour_group[<?=$price->name?>][<?=$num?>][forms][<?=$form->id?>]"<? if($form->require) { ?> required="required"<? } ?> />
			    			</li>
	  					<? } ?>
	  					
	  					<? if($form->type == 'select') { ?>
	  						<li class="hr"></li>
	  						<li>
	  							<label><?=$form->label?><? if($form->require) { ?><em>*</em><? } ?></label>
	  							<span><?=$form->comments?></span>
			    				<select name="tour_group[<?=$price->name?>][<?=$num?>][forms][<?=$form->id?>]"<? if($form->require) { ?> required="required"<? } ?>>
							    	<? foreach($form->options as $option) { ?>
							    		<option><?=$option?></option>
							    	<? } ?>
							    </select>
			    			</li>
	  					<? } ?>
	  					
	  					<? if($form->type == 'multiselect') { ?>
	  						<li class="hr"></li>
	  						<li>
	  							<label><?=$form->label?><? if($form->require) { ?><em>*</em><? } ?></label>
	  							<span><?=$form->comments?></span>
			    				<select multiple="multiple" name="tour_group[<?=$price->name?>][<?=$num?>][forms][<?=$form->id?>][]"<? if($form->require) { ?> required="required"<? } ?>>
							    	<? foreach($form->options as $option) { ?>
							    		<option><?=$option?></option>
							    	<? } ?>
							    </select>
			    			</li>
	  					<? } ?>
	  					
	  					<? if($form->type == 'textarea') { ?>
	  						<li class="hr"></li>
	  						<li>
	  							<label><?=$form->label?><? if($form->require) { ?><em>*</em><? } ?></label>
	  							<span><?=$form->comments?></span>
			    				<textarea name="tour_group[<?=$price->name?>][<?=$num?>][forms][<?=$form->id?>]" cols="40" rows="4"<? if($form->require) { ?> required="required"<? } ?>></textarea>
			    			</li>
	  					<? } ?>
	  					
	  					<? if($form->type == 'checkbox') { ?>
	  						<li class="hr"></li>
	  						<li>
	  							<input type="checkbox" class="checkbox" id="<?=$form->id?>|<?=addslashes($form->label)?>|<?=$form->price?>" name="tour_group[<?=$price->name?>][<?=$num?>][forms][<?=$form->id?>]"<? if($form->require) { ?> required="required"<? } ?> <? if($form->price) { ?>onclick="if(this.checked) { add_element('<?=$form->id?>', '<?=addslashes($form->label)?>', '<? if($form->price_mod == '-') { ?><?=$form->price_mod?><? } ?><?=$form->price?>'); } else { sub_element('<?=$form->id?>', '<? if($form->price_mod == '-') { ?><?=$form->price_mod?><? } ?><?=$form->price?>'); }"<? } ?> />
	  							<label class="checkbox"><?=$form->label?><? if($form->require) { ?><em>*</em><? } ?><? if($form->price) { ?> <em><?=$form->price_mod?> <?=$site->formatCurrency($form->price)?></em><? } ?> 
	  								<span class="checkbox"><?=$form->comments?></span>
	  							</label>
	  						</li>
	  					<? } ?>

						<? endforeach; ?>

			   	</ol>
			 	</fieldset>
			   
			<? endforeach; ?>
		<? endforeach; ?>
	
	  </div> 
	  <!-- end of passnger info-->
	  
		<? } ?>
	 
		<? if($site->getTourForms(primary)) { ?>
		  
		<!---- additional info ---->
		  <div class="booking_info">
		  <h2 class="title">Additional Information</h2>
		<!-- start extra form field -->
		  <fieldset>
				<ol>
					<? foreach( $site->getTourForms(primary) as $form ): ?>
		  					
		  			<? if($first_line) { ?><li class="hr"></li><? } else { $first_line = 1; } ?>
		  					
						<? if($form->type == 'text') { ?>
							<li>
								<label><?=$form->label?><? if($form->require) { ?><em>*</em><? } ?></label>
								<span><?=$form->comments?></span>
		    				<input type="text" name="tour_forms[<?=$form->id?>]"<? if($form->require) { ?> required="required"<? } ?> />
		    			</li>
						<? } ?>
						
						<? if($form->type == 'select') { ?>
							<li>
								<label><?=$form->label?><? if($form->require) { ?><em>*</em><? } ?></label>
								<span><?=$form->comments?></span>
		    				<select name="tour_forms[<?=$form->id?>]"<? if($form->require) { ?> required="required"<? } ?>>
		    					<? foreach($form->options as $option) { ?>
						    		<option><?=$option?></option>
						    	<? } ?>
						    </select>
		    			</li>
						<? } ?>
						
						<? if($form->type == 'multiselect') { ?>
							<li>
								<label><?=$form->label?><? if($form->require) { ?><em>*</em><? } ?></label>
								<span><?=$form->comments?></span>
								<select multiple="multiple" name="tour_forms[<?=$form->id?>]"<? if($form->require) { ?> required="required"<? } ?>>
						    	<? foreach($form->options as $option) { ?>
						    		<option><?=$option?></option>
						    	<? } ?>
						    </select>
		    			</li>
						<? } ?>
						
						<? if($form->type == 'textarea') { ?>
							<li>
								<label><?=$form->label?><? if($form->require) { ?><em>*</em><? } ?></label>
								<span><?=$form->comments?></span>
		    				<textarea name="tour_forms[<?=$form->id?>]"<? if($form->require) { ?> required="required"<? } ?> cols="40" rows="4"></textarea>
		    			</li>
						<? } ?>
						
						<? if($form->type == 'checkbox') { ?>
							<li>
								<input type="checkbox" class="checkbox" id="<?=$form->id?>|<?=addslashes($form->label)?>|<?=$form->price?>" name="tour_forms[<?=$form->id?>]"<? if($form->require) { ?> required="required"<? } ?> <? if($form->price) { ?>onclick="if(this.checked) { add_element('<?=$form->id?>', '<?=addslashes($form->label)?>', '<? if($form->price_mod == '-') { ?><?=$form->price_mod?><? } ?><?=$form->price?>'); } else { sub_element('<?=$form->id?>', '<? if($form->price_mod == '-') { ?><?=$form->price_mod?><? } ?><?=$form->price?>'); }"<? } ?> />
								<label class="checkbox"><?=$form->label?><? if($form->require) { ?><em>*</em><? } ?><? if($form->price) { ?> <em><?=$form->price_mod?> <?=$site->formatCurrency($form->price)?></em><? } ?> 
									<span class="checkbox"><?=$form->comments?></span>
								</label>
							</li>
						<? } ?>
		
					<? endforeach; ?>
				</ol>
			</fieldset>
		  </div>
			<!-- end of additional info -->
		  
		  <? } ?>
	  
	  <? endforeach; ?>
  
	<!----- submit button ----->

	<div><input class="submit" type="submit" value="Next Step"></div>

	</div>

	<div id="content_2" style="display:none;">

		<!-- cart section -->
	  <div class="tour_info">
		  <h2>Your Booking</h2>
		  <fieldset>
		  <ol class="tour_receipt">
		  	<li class="info"><label>You are booking</label><span><?=$item->name?><br /><?=$item->time?></span></li>
		
		    <li class="info"><label>Date</label><span><?=date("F d, Y", strtotime($_REQUEST['date']))?></span></li>
		    <li class="info"><label>Duration</label><span><?=$item->duration?></span></li>
		    <? if($_COOKIE['rezgo_promo']) { ?>
		   	<li class="info"><label>Promotional Code</label><span><?=$_COOKIE['rezgo_promo']?></span></li>
		   	<? } ?>
		    <li class="info last"><label>Price</label>
		    	<ol class="price">
		      	<li class="info">
		          <label class="type">type</label>
		
		          <label class="qty">qty</label>
		          <label class="cost">cost</label>
		          <label class="line_total">total</label>
		        </li>
		        
		        <? foreach( $site->getTourPrices($item) as $price ): ?>
		        	<? if($_REQUEST[$price->name.'_num']) { ?>
			        	<li class="info">
				        	<span class="type"><? if($site->exists($price->base)) { ?><span class="discount"></span><? } ?><?=$price->label?></span>
				        	<span class="qty"><? if($site->exists($price->base)) { ?><span class="discount"></span><? } ?><?=$_REQUEST[$price->name.'_num']?></span>
				        	<span class="cost">
				        		<? if($site->exists($price->base)) { ?><span class="discount"><?=$site->formatCurrency($price->base)?></span><? } ?>
				        		<?=$site->formatCurrency($price->price)?>
				        	</span>
				        	<span class="line_total"><? if($site->exists($price->base)) { ?><span class="discount"></span><? } ?><?=$site->formatCurrency($price->total)?></span>
				        </li>
		        	<? } ?>
		        <? endforeach; ?>
		      	
						<li class="info"><label class="subtotal">Subtotal</label><span class="subtotal"><?=$site->formatCurrency($item->sub_total)?></span></li>
						
						<? if($site->exists($item->tax_calc)) { ?>
						<li class="info"><label class="tax_fees">Taxes & Fees</label><span class="tax_fees price_pos"><?=$site->formatCurrency($item->tax_calc)?></span></li>
						<? } ?>
						
						<div id="fee_box">
	
						</div>
						
						<li class="info"><label class="total">TOTAL</label><span class="total" id="total_value"><?=$site->formatCurrency($item->overall_total)?></span></li>
						
						<? if($site->exists($item->deposit)) { ?>
						<li class="info"><label class="total">Deposit to Pay Now</label><span class="total" id="total_value"><?=$site->formatCurrency($item->deposit_value)?></span></li>
		        <? } ?>
		        
		      </ol>
		    </li>
		  </ol>
		  </fieldset>
	  </div>
	  <!-- end of receipt section -->


		<p class="notation">To finish your booking, please complete the following form. Please note that fields marked with "<em>*</em>" are required.</p>
	
	  <div class="billing_info">
	  <h2>Billing / Primary Contact Information</h2>
	  <fieldset>
	  	<ol>
	    <li class="half"><label for="fname">First Name<em>*</em></label><input id="tour_first_name" name="tour_first_name" type="text" value="" /></li>
	    <li class="half"><label for="lname">Last Name<em>*</em></label><input id="tour_last_name" name="tour_last_name" type="text" value="" /></li>
	
	    <li class="half"><label for="address1">Address<em>*</em></label><input id="tour_address_1" name="tour_address_1" type="text" value="" /></li>
	    <li class="half"><label for="address2">Address 2</label><input id="tour_address_2" name="tour_address_2" type="text" value="" /></li>
	    
	    <li class="half"><label for="city">City<em>*</em></label><input id="tour_city" name="tour_city" type="text" value="" /></li>
	    <li class="half"><label for="states">State/Province</label><input id="tour_stateprov" name="tour_stateprov" type="text" value="" /></li>
	    
	    <li class="half"><label for="country">Country<em>*</em></label>
	    	<? $companyCountry = $site->getCompanyCountry(); ?>
				<select name="tour_country" id="tour_country" />
	    		<? foreach( $site->getRegionList() as $iso => $name ): ?>
	    			<option value="<?=$iso?>" <?=(($iso == $companyCountry) ? 'selected' : '')?>><?=ucwords($name)?></option>
	    		<? endforeach; ?>
	    	</select>
	    </li>
	    <li class="half"><label for="postal">Zip/Postal Code<em>*</em></label><input id="tour_postal_code" name="tour_postal_code" type="text" value="" /></li>
			
			<li class="half"><label for="phone">Phone Number<em>*</em></label><input id="tour_phone_number" name="tour_phone_number" type="text" value="" /></li>
	    <li class="half"><label for="email">Email Address<em>*</em></label><input id="tour_email_address" name="tour_email_address" type="email" value="" /></li>
	    </ol>
	   </fieldset>
		</div>
		
		<div class="payment_info" id="payment_info" style="<?=(($item->overall_total > 0) ? '' : 'display:none;')?>">
		<h2>Select Your Payment Method</h2>
			<fieldset>
				<ol>
					<li>
						
						<div style="float:left;">
							
							<table border="0" cellspacing="0" cellpadding="0" width="100%">
						
							<? 
								foreach( $site->getPaymentMethods() as $pay ) {
								
									if($pay[name] == 'Credit Cards') {
										echo '<tr><td><input type="radio" name="payment_method" id="payment_method_credit" value="Credit Cards" checked onclick="toggleCard();">&nbsp;&nbsp;</td><td style="height:42px;">
											<label for="payment_method_credit">';
										
											foreach( $site->getPaymentCards() as $card ) {
												echo '<img src="'.$site->path.'/images/logos/'.$card.'.png" style="margin:0px;">';
												
											}
										
										echo '</label>
										<input type="hidden" name="tour_card_token" id="tour_card_token" value="">
										<script>
											jQuery(\'#tour_card_token\').val(\'\');
											setTimeout(function() {
												jQuery(\'#payment_method_credit\').attr(\'checked\', true);
											}, 600);
										</script>
										</td></tr>';
										
									} elseif($pay[name] == 'PayPal' && !$site->exists($site->getCompanyPaypal())) {
									
										echo '<tr><td><input type="radio" name="payment_method" id="payment_method_paypal" value="PayPal" onclick="getPaypalToken(); toggleCard();">&nbsp;&nbsp;</td><td style="height:42px;">
											<label for="payment_method_paypal"><img src="'.$site->path.'/images/logos/paypal.png" style="margin:0px;"></label>
											<input type="hidden" name="paypal_token" id="paypal_token" value="">
											<input type="hidden" name="paypal_payer_id" id="paypal_payer_id" value="">
										</td></tr>';
										
									} else {
										$pmc++;
										echo '<tr><td><input type="radio" name="payment_method" id="payment_method_'.$pmc.'" value="'.$pay[name].'" onclick="toggleCard();">&nbsp;&nbsp;</td>
											<td style="font-size:18px; font-weight:bold; color:#666; margin-bottom:10px; height:35px;">
												<label for="payment_method_'.$pmc.'">'.$pay[name].'</label>
											</td>
										</tr>';
									}		
								}
								
							?>
							
							</table>
						</div>
						<div style="width:48%; float:right;">
							
							<? foreach( $site->getPaymentMethods() as $pay ) { ?>
								
								<? if($pay[name] == 'Credit Cards') { ?>
									
									<div id="payment_cards">
										<iframe style="height:150px; width:345px; border:0px;" scrolling="no" frameborder="0" name="tour_payment" id="tour_payment" src="<?=$site->base?>/booking_payment.php">
										
										</iframe>
									</div>
									
								<? } elseif($pay[name] == 'PayPal' && !$site->exists($site->getCompanyPaypal())) { ?>
									
									<div id="payment_paypal" style="font-weight:bold; color:#666; text-align:center; display:none;">
										<br><br><img src="<?=$site->path?>/images/booking_load.gif">
									</div>	
									
								<? } else { ?>
									
									<? $pmdc++; ?>
										
									<div id="payment_method_<?=$pmdc?>_box" style="font-weight:bold; color:#666; text-align:center; display:none;">
										
										<? if($pay[add]) { ?>
											
											<div class="payment_info" id="payment_method_<?=$pmdc?>_container" style="width:310px; padding:8px 0 8px 0;">
												<?=$pay[add]?><br>
												<input type="text" id="payment_method_<?=$pmdc?>_field" name="payment_method_add" style="width:85%; margin-top:5px;" value="" disabled="disabled" />										
											</div>
													
										<? } ?>
										
									</div>
									
								<? } ?>
									
							<? } ?>		
							
						</div>
						
					</li>
				</ol>
			
			</fieldset>
		</div>
	
		<!--   make a lightbox popup for terms and conditions -->
	  <div class="terms">
	  <h2>Terms and Conditions</h2>
	  <fieldset>
	  <ol>
	  	<li><input type="checkbox" class="checkbox" id="agree_terms" name="agree_terms" value="1" \>&nbsp;&nbsp;I agree to the <a href="javascript:void(0);" onclick="javascript:window.open('<?=$site->base?>/terms_popup.php', 'mywindow', 'menubar=1,resizable=1,scrollbars=1,width=800,height=600');">Terms and Condtions</a></li>
	  	<li class="payment_terms">
	  		<div id="terms_credit_card" style="display:<? if(!$site->getPaymentMethods('Credit Cards')) { ?>none<? } ?>;">
	  			<? if($site->getGateway() OR $site->isVendor()) { ?>
	  				<? if($item->overall_total > 0) { ?>Please note that your credit card will be charged. <? } ?>If you are satisfied with your entries, please click the "Complete Booking" button.
	  			<? } else { ?> 
	  				<? if($item->overall_total > 0) { ?>Please note that your credit card will not be charged now. Your transaction information will be stored until your payment is processed. Please see the Terms and Conditions for more information. <? } ?>If you are satisfied with your entries, please click the "Complete Booking" button.
	  			<? } ?>
	  		</div>
	  		<div id="terms_other" style="display:<? if($site->getPaymentMethods('Credit Cards')) { ?>none<? } ?>;">
	  			If you are satisfied with your entries, please click the "Complete Booking" button.
	  		</div>
	  	</li>
		</ol>
	  </fieldset>
	  </div>
	
	<input class="previous" type="submit" value="Previous Step" onclick="stepBack(); return false;">
	
	<input class="submit" type="submit" value="Complete Booking">
	
</div>

<div id="errors">Some required fields are missing.<br>Fields marked with a * are required.</div>

</form>

<script>
	var toComplete = 0;
	var response; // needs to be global to work in timeout
	var paypalAccount = 0;

	jQuery.tools.validator.localize("en", {
		'[required]' : 'required',
		':email' : 'enter a valid email'
	});
	
	function close_modal() {
		jQuery('#prompt').data("overlay").close();
	}
	
	// change the modal dialog box or pass the user to the receipt depending on the response
	function show_response()  {
		
		if(response == '2') {
			var body = '<h2>No Availability Left</h2>Sorry, there is not enough availability left for this item on this date.<br><br><button type="button" class="close" onclick="close_modal();">Close This</button>';
		} else if(response == '3') {
			var body = '<h2>Payment Error</h2>Sorry, your payment could not be completed. Please verify your card details and try again.<br><br><button type="button" class="close" onclick="close_modal();">Close This</button>';
		} else if(response == '4') {
			var body = '<h2>Booking Error</h2>Sorry, there has been an error with your booking and it can not be completed at this time.<br><br><button type="button" class="close" onclick="close_modal();">Close This</button>';
		} else if(response == '5') {
			// this error should only come up in preview mode without a valid payment method set
			var body = '<h2>Booking Error</h2>Sorry, you must have a payment method attached to your Rezgo Account in order to complete a booking.<br><br>Please go to "Settings > My Rezgo Account" to attach a payment method.<br><br><button type="button" class="close" onclick="close_modal();">Close This</button>';
		} else {
		
			// this section is mostly for debug handling
			if(response.indexOf('STOP::') != -1) {	
				var split = response.split('<br><br>');
				if(split[1] == '2' || split[1] == '3' || split[1] == '4') {
					split[1] = '<br><br>Error Code: ' + split[1] + '<br><br><button type="button" class="close" onclick="close_modal();">Close This</button>';
				} else {
					split[1] = '<br><br>BOOKING COMPLETED WITHOUT ERRORS<br><br><button type="button" class="close" onclick="close_modal();">Close This</button><br><br><button type="button" class="close" onclick="window.location.replace(\'<?=$site->base?>/complete/' + split[1] + '\');">Contine to Receipt</button>';
				}
			
				var body = 'DEBUG-STOP ENCOUNTERED<br><br>' + split[0] + split[1];
			} else {
				// send the user to the receipt page
				window.location.replace("<?=$site->base?>/complete/" + response);
				return true; // stop the html replace
			}
		}
		
		jQuery('#prompt').html(body);
	}
	
	// this function delays the output so we see the loading graphic
	function delay_response(responseText) {
		response = responseText;
		setTimeout('show_response();', 800);
	}
	
	function start_validate() {
		jQuery("#book").validator({ 
			position: 'center left', 
			offset: [-15, -70],
			message: '<div id="rezgo_error"><em></em></div>' // em element is the arrow
		}).submit(function(e) {
			
			// only activate on actual form submission, check payment info
			if(toComplete == 1 && total != 0) {
			
				var force_error = 0;
				
				var payment_method = jQuery('input:radio[name=payment_method]:checked').val();				
				
				if(payment_method == 'Credit Cards') {
					if(!jQuery('#tour_payment').contents().find('#name').val() || !jQuery('#tour_payment').contents().find('#pan').val()) {
						force_error = 1;
						jQuery('#tour_payment').contents().find('#payment_info').css('border-color', '#990000');
					}
				} else {
					// other payment methods need their additional fiends filled
					var id = jQuery('input:radio[name=payment_method]:checked').attr('id');
					if(jQuery('#' + id + '_field').length != 0 && !jQuery('#' + id + '_field').val()) { // this payment method has additional data that is empty
						force_error = 1;
						jQuery('#' + id + '_container').css('border-color', '#990000');
					}
				}
			}
			
			// when data is invalid 
			if(e.isDefaultPrevented() || force_error) {
				jQuery('#errors').fadeIn();
			  jQuery.scrollTo('#errors');
			  setTimeout("jQuery('#errors').fadeOut();", 4000);
			  return false;
			} else {
				if(toComplete == 1) {
					
					jQuery('#prompt').html('<h2>Your booking is being completed</h2><br><center><img src="<?=$site->path?>/images/booking_load.gif"><br><br>Please wait a moment...</center>');
				
					jQuery('#prompt').overlay({
						mask: {
							color: '#FFFFFF',
							loadSpeed: 200,
							opacity: 0.75
						},
						closeOnEsc : false, 
						closeOnClick: false
					});
					
					// open the overlay this way, rather than load:true in the overlay itself
					// so that it will be forced to open again even if it already exists
					jQuery('#prompt').data("overlay").load();
					
					// set the action to book, in case paypal changed it to get it's payment token
					jQuery('#rezgoAction').val('book'); 
					
					var payment_method = jQuery('input:radio[name=payment_method]:checked').val();
					
					if(payment_method == 'Credit Cards' && total != 0) {
						// clear the existing credit card token, just in case one has been set from a previous attempt
						jQuery('#tour_card_token').val('');
						
						// submit the card token request and wait for a response
						jQuery('#tour_payment').contents().find('#payment').submit();
						
						// wait until the card token is set before continuing (with throttling)
						
						function check_card_token() {
							var card_token = jQuery('#tour_card_token').val();
							if(card_token == '') {
								// card token has not been set yet, wait and try again
								setTimeout(function() {
									check_card_token();
								}, 200);
							} else {
								
								// the field is present? submit normally								
								jQuery('#book').ajaxSubmit({ success: delay_response });
								
							}
						}
						
						check_card_token();	
					} else {
											
						// not a credit card payment (or $0) and everything checked out, submit via ajaxSubmit (jquery.form.js)					
						jQuery('#book').ajaxSubmit({ success: delay_response });

	 				}
					
					// return false to prevent normal browser submit and page navigation 
    			return false; 
    			
				} else {
					toComplete = 1;
				
					jQuery('#errors').fadeOut();
				
					document.getElementById("step_1").setAttribute("class", "off");
					document.getElementById("step_2").setAttribute("class", "on");
					
					jQuery('#content_1').hide();
					jQuery('#content_2').fadeIn();
					
					jQuery.scrollTo('#panel_full');
					
					document.getElementById("tour_first_name").setAttribute("required", "required");
					document.getElementById("tour_last_name").setAttribute("required", "required");
					
					document.getElementById("tour_address_1").setAttribute("required", "required");
					document.getElementById("tour_city").setAttribute("required", "required");
					document.getElementById("tour_country").setAttribute("required", "required");
					document.getElementById("tour_postal_code").setAttribute("required", "required");
					
					document.getElementById("tour_phone_number").setAttribute("required", "required");
					document.getElementById("tour_email_address").setAttribute("required", "required");
					
					document.getElementById("agree_terms").setAttribute("required", "required");
					
					return false;
				}
			}
		});

	}
	
	start_validate();
	
	function stepForward() {
		start_validate();
		jQuery('#book').submit();
	}
	
	function stepBack() {
		toComplete = 0;
	
		jQuery('#errors').fadeOut();
	
		document.getElementById("step_1").setAttribute("class", "on");
		document.getElementById("step_2").setAttribute("class", "off");
		
		document.getElementById("tour_first_name").removeAttribute("required");
		document.getElementById("tour_last_name").removeAttribute("required");
		
		document.getElementById("tour_address_1").removeAttribute("required");
		document.getElementById("tour_city").removeAttribute("required");
		document.getElementById("tour_country").removeAttribute("required");
		document.getElementById("tour_postal_code").removeAttribute("required");
		
		document.getElementById("tour_phone_number").removeAttribute("required");
		document.getElementById("tour_email_address").removeAttribute("required");
		
		document.getElementById("agree_terms").removeAttribute("required");
		
		<? if($site->getPaymentMethods('PayPal')) { ?>
			paypalAccount = 0; // set to 0 to let the page know we need an account
		
			jQuery('#payment_paypal').fadeOut();
			jQuery('#payment_method_paypal').attr('checked', false);
			<? if(!$site->exists($site->getCompanyPaypal())) { ?>
			jQuery('#payment_paypal').html('<br><br><img src="<?=$site->path?>/images/booking_load.gif">');
			<? } ?>
			
			jQuery('#paypal_token').val('');
			jQuery('#paypal_payer_id').val('');		
		<? } ?>
		
		<? if($site->getPaymentMethods('Credit Cards')) { ?>
			//document.getElementById("tour_card_name").removeAttribute("required");
			//document.getElementById("tour_card_number").removeAttribute("required");
		<? } ?>
		
		start_validate();
		
		jQuery('#content_2').hide();
		jQuery('#content_1').fadeIn();
	}
	
	function toggleCard() {
		if(jQuery('input[name=payment_method]:checked').val() == 'Credit Cards') {
			<? $pmn = 0; ?>
			<? foreach( $site->getPaymentMethods() as $pay ) { ?>	
				<? if($pay[name] == 'Credit Cards') { ?>
				<? } elseif($pay[name] == 'PayPal' && !$site->exists($site->getCompanyPaypal())) { ?>
					jQuery('#payment_paypal').fadeOut();
				<? } else { ?>
					<? $pmn++; ?>
					jQuery('#payment_method_<?=$pmn?>_box').fadeOut();
					jQuery('#payment_method_<?=$pmn?>_field').attr('disabled', 'disabled');
				<? } ?>
			<? } ?>	
			
			setTimeout(function() {
				jQuery('#payment_cards').fadeIn();
			}, 450);
			
			document.getElementById("terms_other").style.display = 'none';
			document.getElementById("terms_credit_card").style.display = '';			
		} else if(jQuery('input[name=payment_method]:checked').val() == 'PayPal') {
			<? $pmn = 0; ?>
			<? foreach( $site->getPaymentMethods() as $pay ) { ?>	
				<? if($pay[name] == 'Credit Cards') { ?>
					jQuery('#payment_cards').fadeOut();
				<? } elseif($pay[name] == 'PayPal' && !$site->exists($site->getCompanyPaypal())) { ?>
				<? } else { ?>
					<? $pmn++; ?>
					jQuery('#payment_method_<?=$pmn?>_box').fadeOut();
					jQuery('#payment_method_<?=$pmn?>_field').attr('disabled', 'disabled');
				<? } ?>
			<? } ?>	
			
			setTimeout(function() {
				jQuery('#payment_paypal').fadeIn();
			}, 450);
			
			document.getElementById("terms_credit_card").style.display = 'none';
			document.getElementById("terms_other").style.display = '';
			
		} else {
			<? $pmn = 0; ?>
			<? foreach( $site->getPaymentMethods() as $pay ) { ?>	
				<? if($pay[name] == 'Credit Cards') { ?>
					jQuery('#payment_cards').fadeOut();
				<? } elseif($pay[name] == 'PayPal' && !$site->exists($site->getCompanyPaypal())) { ?>
					jQuery('#payment_paypal').fadeOut();
				<? } else { ?>
					<? $pmn++; ?>
					jQuery('#payment_method_<?=$pmn?>_box').fadeOut();
					jQuery('#payment_method_<?=$pmn?>_field').attr('disabled', 'disabled');
				<? } ?>
			<? } ?>	
			
			setTimeout(function() {
				var id = jQuery('input[name=payment_method]:checked').attr('id');
				jQuery('#' + id + '_box').fadeIn();
				jQuery('#' + id + '_field').attr('disabled', '');
			}, 450);
			
			document.getElementById("terms_credit_card").style.display = 'none';
			document.getElementById("terms_other").style.display = '';
		
			start_validate();
		}
		
	}
	
	// these functions do a soft-commit when you click on the paypal option so they
	// can get an express payment token from the paypal API via the XML gateway
	function getPaypalToken(force) {
		
		// if we aren't forcing it, don't load if we already have an id
		if(!force && paypalAccount == 1) {
			// an account is set, don't re-open the box
			return false;
		}
		
		jQuery('#rezgoAction').val('get_paypal_token');
		
		jQuery('#book').ajaxSubmit({
			success: function(token) {
				jQuery('#payment_paypal').fadeOut();
				
				// this section is mostly for debug handling
				if(token.indexOf('STOP::') != -1) {
					var split = token.split('<br><br>');
					
					//alert("DEBUG-STOP ENCOUNTERED\n\n" + split[0] + "\n\nToken Returned:" + split[1] + " (close this to proceed)");
					
					if(split[1] == '0') {
						alert('The system encountered an error with PayPal. Please try again in a few minutes or select another payment method.');
						return false;
					}
					
					token = split[1];
				}
				
				dg.startFlow("https://www.paypal.com/incontext?token=" + token);
				
			}
		});
		
	}
	
	function paypalCancel() {
		// the paypal transaction was cancelled, uncheck the radio and close the box
		dg.closeFlow();
		jQuery('#payment_method_paypal').attr('checked', false);
	}
	
	function paypalConfirm(token, payerid, name, email) {
		// the paypal transaction was completed, show us the details and fade in the box
		dg.closeFlow();
		
		if(token == 0) {
			token = '';
			payerid = '';
			var string = 'There appears to have been an error with your transaction<br>Please try again.';
		} else {	
			var string = '<div class="payment_info" style="width:280px; padding:8px 0 8px 0;">Using PayPal Account: <span style="color:#000;">' + name + '<br>' + email + '</span><br><br><a href="javascript:void(0);" onclick="getPaypalToken(1);">Use a different account to pay</a></div>';	
			paypalAccount = 1; // set to 1 to let the page know we have an account on file
		}
			
		jQuery('#payment_paypal').html(string);
		jQuery('#payment_paypal').fadeIn();
		
		jQuery('#paypal_token').val(token);
		jQuery('#paypal_payer_id').val(payerid);
	}
	
	function creditConfirm(token) {
		// the credit card transaction was completed, give us the token
		jQuery('#tour_card_token').val(token);
	}
	
	// this function checks through each element on the form, if that element is
	// a checkbox and has a price value and is checked (thanks to browser form retention)
	// then we go ahead and add that to the total like it was clicked
	function saveForm(form) {
	  jQuery(':input', form).each(function() {
	    if (this.type == 'checkbox' && this.checked == true) {
	    	var split = this.id.split("|");
	    	// if the ID contains a price value then add it
	    	if(split[2]) add_element(split[0], split[1], split[2]);
	    }
	   });
	};
	
	saveForm('#book');
</script>

</div><!-- end of panel_full--> 
<div class="clear"></div> <!-- do not take this out -->

<div id="rezgo_footer">

</div>


</div><!--end rezgo wrp-->