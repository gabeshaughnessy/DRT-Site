<script type="text/javascript" src="<?=$site->path?>/javascript/jquery.scrollTo.min.js"></script>

<div id="rezgo" class="wrp_list">

<div id="left_panel">
	<div class="breadcrumb"><h1 class="header">Contact Us</h1></div>

	<div id="contact_us">
		
		<? $company = $site->getCompanyDetails(); ?>
	  <div class="company_info">
  	<span class="co_name"><?=$company->company_name?></span><br>
		
		<?=$company->address_1?> <?=$company->address_2?><br>
		<?=$company->city?>, <? if($site->exists($company->state_prov)) { ?><?=$company->state_prov?>, <? } ?><?=$site->countryName($company->country)?><br>
  	<?=$company->postal_code?><br>
  	<br>
  	<? if($site->exists($company->phone)) { ?>Phone: <?=$company->phone?><br><? } ?>
  	<? if($site->exists($company->fax)) { ?>Fax: <?=$company->fax?><br><? } ?>
  	Email: <?=$company->email?>
  	<? if($site->exists($company->tax_id)) { ?>
  	<br>
  	<br>
  	Tax ID: <?=$company->tax_id?>
  	<? } ?>
</div>
		<? if($result->status == 1) { ?>
		
		<div class="contact_msg">
			Thank you for your message, one of our sales representatives will contact you shortly.
		</div>
		
		<? } else { ?>

		<div class="contact_instruction">
			Please use the following form to send us a message and one of our friendly sales representatives will contact you shortly.
		</div>
		<div class="contact_form">
			<form id="contact" method="post">
			<input type="hidden" name="rezgoAction" value="contact">
			
			<ul>
      	<li><label>Name:<em>*</em></label><input type="text" required="required" name="full_name" value="<?=$_REQUEST['full_name']?>"></li>
				<li><label>Email:<em>*</em></label><input type="email" required="required" name="email" value="<?=$_REQUEST['email']?>"></li>
				<li><label>Phone:</label><input type="text" name="phone" value="<?=$_REQUEST['phone']?>"></li>
				<li><label>Address:</label><input type="text" name="address" value="<?=$_REQUEST['address']?>"></li>
				<li><label>Address 2:</label><input type="text" name="address2" value="<?=$_REQUEST['address2']?>"></li>
				<li><label>City:</label><input type="text" name="city" value="<?=$_REQUEST['city']?>"></li>
				<li><label>State/Province:</label><input type="text" name="state_prov" value="<?=$_REQUEST['state_prov']?>"></li>
				<li><label>Country:</label>
        		<select name="country">
							<? foreach( $site->getRegionList() as $iso => $name ): ?>
			    			<option value="<?=$iso?>" <?=(($iso == $site->getCompanyCountry()) ? 'selected' : '')?>><?=ucwords($name)?></option>
			    		<? endforeach; ?>
						</select>
				</li>
				<li><label>Message:<em>*</em></label>
					<textarea name="body" rows="8" cols="40" wrap="on" required="required"><?=$_REQUEST['body']?></textarea>
				</li>
				
				<? if($site->exists(REZGO_CAPTCHA_PUB_KEY)) { ?>
				
				<li><label>Verification:</label>
					<div id="captcha">
						<span id="captcha_error" class="captcha_error"><?=$result->captchaError?></span>
						<?=recaptcha_get_html(REZGO_CAPTCHA_PUB_KEY, null, 1)?>
					</div></li>
					
				<? } ?>
				
				<li><input type="submit" class="submit btn_search" value="Send Request"></li>
			</ul>
			</form>
		</div>
		
		<script>
			<? if($site->exists($captcha_error)) { ?>jQuery.scrollTo('#captcha_error');<? } ?>
		
			jQuery.tools.validator.localize("en", {
				'[required]' : 'required',
				':email' : 'enter a valid email'
			});
		
			jQuery("#contact").validator({ 
				position: 'center left', 
				offset: [-15, -70],
				message: '<div id="rezgo_error"><em></em></div>' // em element is the arrow
			})
		</script>
		
		<? } ?>
		
	</div> <!-- end contact_us div -->
	
</div>