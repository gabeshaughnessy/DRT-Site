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
	
	<? if(!$_REQUEST['uid'] || !$_REQUEST['date'] || !$site->getTours('t=uid&q='.$_REQUEST['uid'].'&d='.$_REQUEST['date'].$site->getPaxString())) { $site->sendTo("/tour"); } ?>
	
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
					$("#fee_box").html( $("#fee_box").html() + content );
				} else {
					if(document.getElementById('element_' + id).style.display == 'none') document.getElementById('element_' + id).style.display = '';
					$("#val_" + id).html(display_price);
				}	
				elements[id] = price;
				
				// add to total amount
				total = parseFloat(total) + add_price;
				total = total.toFixed(<?=$item->currency_decimals?>);
				$("#total_value").html('<?=$item->currency_symbol?>' + total);
			
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
				$("#total_value").html('<?=$item->currency_symbol?>' + total);
			
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
		<h2>Payment Information</h2>
			<fieldset>
				<ol>
					<li>
						<label class="left">Payment Method</label>
						<select name="payment_method" id="payment_method" onchange="toggleCard();">
							<? foreach( $site->getPaymentMethods() as $name ): ?>
								<option value="<?=$name?>"><?=$name?></option>
							<? endforeach; ?>
						</select>
					</li>
				</ol>
				
				<? if($site->getPaymentMethods('Credit Cards')) { ?>
					<div id="payment_cards">
						<ol>
							<li>
								<label class="left">Credit Card Type</label>
								<select name="tour_card_type">
									<? foreach( $site->getPaymentCards() as $name ): ?>
										<option value="<?=$name?>" <?=(($name == 'visa') ? 'selected' : '')?>><?=ucwords($name)?></option>
									<? endforeach; ?>
								</select>
							</li>
							
							<li><label class="left">Name of Card Holder<em>*</em></label><input type="text" id="tour_card_name" name="tour_card_name" value="" /></li>
							<li><label class="left">Card Number<em>*</em></label><input type="text" id="tour_card_number" name="tour_card_number" value="" /></li>
							<li>
								<label class="left">Card Expiry<em>*</em></label>
								<select name="tour_card_expiry_month">
									<option value="01">Jan</option>
									<option value="02">Feb</option>
									<option value="03">Mar</option>
									<option value="04">Apr</option>
									<option value="05">May</option>
									<option value="06">Jun</option>
									<option value="07">Jul</option>
									<option value="08">Aug</option>
									<option value="09">Sep</option>
									<option value="10">Oct</option>
									<option value="11">Nov</option>
									<option value="12">Dec</option>
								</select>
								<select name="tour_card_expiry_year">
									<? for($d=date("Y"); $d <= date("Y")+12; $d++): ?>
										<option value="<?=substr($d, -2)?>"><?=$d?></option>
									<? endfor; ?>
								</select>	
							</li>
					
							<? if($site->getCVV()) { ?>
							<li><label class="left">CVV Number<em>*</em></label><input type="text" name=tour_card_cvv value="" style="width:50px;" />&nbsp;<a href="javascript:void(0);" onclick="javascript:window.open('<?=$site->path?>/images/cv_card.jpg',null,'width=600,height=300,status=no,toolbar=no,menubar=no,location=no');">what is this ?</a></li>
							<? } ?>
						</ol>
					</div>
				<? } ?>
			
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

	$.tools.validator.localize("en", {
		'[required]' : 'required',
		':email' : 'enter a valid email'
	});
	
	function close_modal() {
		$('#prompt').data("overlay").close();
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
		
		$('#prompt').html(body);
	}
	
	// this function delays the output so we see the loading graphic
	function delay_response(responseText) {
		response = responseText;
		setTimeout('show_response();', 800);
	}
	
	function start_validate() {
	
		$("#book").validator({ 
			position: 'center left', 
			offset: [-15, -70],
			message: '<div id="rezgo_error"><em></em></div>' // em element is the arrow
		}).submit(function(e) {
			// when data is invalid 
			if (e.isDefaultPrevented()) {
			  $('#errors').fadeIn();
			  $.scrollTo('#errors');
			  setTimeout("$('#errors').fadeOut();", 4000);
			} else {
				if(toComplete == 1) {
				
					$('#prompt').html('<h2>Your booking is being completed</h2><br><center><img src="<?=$site->path?>/images/booking_load.gif"><br><br>Please wait a moment...</center>');
				
					$('#prompt').overlay({
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
					$('#prompt').data("overlay").load();
					
					// the form submits normally if everything checked out, via ajaxSubmit (jquery.form.js)					
					$('#book').ajaxSubmit({
        		success: delay_response
 					});
					
					// return false to prevent normal browser submit and page navigation 
    			return false; 
    			
				} else {
					toComplete = 1;
				
					$('#errors').fadeOut();
				
					document.getElementById("step_1").setAttribute("class", "off");
					document.getElementById("step_2").setAttribute("class", "on");
					
					$('#content_1').hide();
					$('#content_2').fadeIn();
					
					$.scrollTo('#panel_full');
					
					document.getElementById("tour_first_name").setAttribute("required", "required");
					document.getElementById("tour_last_name").setAttribute("required", "required");
					
					document.getElementById("tour_address_1").setAttribute("required", "required");
					document.getElementById("tour_city").setAttribute("required", "required");
					document.getElementById("tour_country").setAttribute("required", "required");
					document.getElementById("tour_postal_code").setAttribute("required", "required");
					
					document.getElementById("tour_phone_number").setAttribute("required", "required");
					document.getElementById("tour_email_address").setAttribute("required", "required");
					
					document.getElementById("agree_terms").setAttribute("required", "required");
					
					<? if($site->getPaymentMethods('Credit Cards')) { ?>
						if(parseFloat(total) > 0) {
							document.getElementById("tour_card_name").setAttribute("required", "required");
							document.getElementById("tour_card_number").setAttribute("required", "required");
						}
					<? } ?>
					
					return false;
				}
			}
		});

	}
	
	start_validate();
	
	function stepForward() {
		start_validate();
		$('#book').submit();
	}
	
	function stepBack() {
		toComplete = 0;
	
		$('#errors').fadeOut();
	
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
		
		<? if($site->getPaymentMethods('Credit Cards')) { ?>
			document.getElementById("tour_card_name").removeAttribute("required");
			document.getElementById("tour_card_number").removeAttribute("required");
		<? } ?>
		
		start_validate();
		
		$('#content_2').hide();
		$('#content_1').fadeIn();
	}
	
	function toggleCard() {
		if($('#payment_method').val() == 'Credit Cards') {
			$('#payment_cards').slideDown();
			
			document.getElementById("tour_card_name").setAttribute("required", "required");
			document.getElementById("tour_card_number").setAttribute("required", "required");
			
			document.getElementById("terms_other").style.display = 'none';
			document.getElementById("terms_credit_card").style.display = '';			
		} else {
			$('#payment_cards').slideUp();
			
			document.getElementById("tour_card_name").removeAttribute("required");
			document.getElementById("tour_card_number").removeAttribute("required");
			
			document.getElementById("terms_credit_card").style.display = 'none';
			document.getElementById("terms_other").style.display = '';
		
			start_validate();
		}
	}
	
	// this function checks through each element on the form, if that element is
	// a checkbox and has a price value and is checked (thanks to browser form retention)
	// then we go ahead and add that to the total like it was clicked
	function saveForm(form) {
	  $(':input', form).each(function() {
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