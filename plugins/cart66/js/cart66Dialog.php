<?php
if ( !defined( 'ABSPATH' ) )
  die( 'Do not include this file directly' );

$product= new Cart66Product();

$tinyURI = get_bloginfo('wpurl')."/wp-includes/js/tinymce";

?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Cart66</title>
	<link type="text/css" rel="stylesheet" href="<?php echo CART66_URL; ?>/js/cart66.css" />
	<script language="javascript" type="text/javascript" src="<?php echo $tinyURI; ?>/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo $tinyURI; ?>/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo $tinyURI; ?>/utils/form_utils.js"></script>
	
  <script type="text/javascript" src="<?php echo get_bloginfo('wpurl')?>/wp-includes/js/jquery/jquery.js"></script>
	<script language="javascript" type="text/javascript">
	<!--
  var $jq = jQuery.noConflict();
	tinyMCEPopup.onInit.add( function(){window.setTimeout(function(){$jq('#productName').focus();},500);} );

	<?php
	$prices = array();
	$types = array(); 
	$options='';
	$products = $product->getModels("where id>0", "order by name");
	if(count($products)):
	  $i=0;
	  foreach($products as $p) {
	      if($p->itemNumber==""){
          $id=$p->id;
          $type='id';
          $description = "";
        }
        else{
          $id=$p->itemNumber;
          $type='item';
          $description = '(# '.$p->itemNumber.')';
        }
        
  	    $types[] = htmlspecialchars($type);
  	    
  	    if(CART66_PRO && $p->isPayPalSubscription()) {
  	      $sub = new Cart66PayPalSubscription($p->id);
  	      $subPrice = strip_tags($sub->getPriceDescription($sub->offerTrial > 0, '(trial)'));
  	      $prices[] = htmlspecialchars($subPrice);
  	      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] subscription price in dialog: $subPrice");
  	    }
  	    else {
  	      $prices[] = htmlspecialchars(strip_tags($p->getPriceDescription()));
  	    }
  	    
  	    
  	    $options .= '<option value="'.$id.'">'.$p->name.' '.$description.'</option>';
  	    $i++;
	    
	  }
	
	else:
	  $options .= '<option value="">No products</option>';
	endif;
	 
	 $prodTypes = implode("\",\"",$types);
	 $prodPrices = implode("\",\"", $prices);
  ?>
  
  var prodtype = new Array("<?php echo $prodTypes; ?>");
  var prodprices = new Array("<?php echo $prodPrices; ?>");
  
 

	function init() {
		mcTabs.displayTab('tab', 'panel');
	}
	
	function preview(){
	   	
	  var productIndex = jQuery("#productNameSelector option:selected").index();
	  
	  var priceDescription = jQuery("<div/>").html(prodprices[productIndex]).text();
    var price = "<p style='margin-top:2px;'><label id='priceLabel'>" + priceDescription + "</label></p>";
	  if(jQuery("input[@name='showPrice']:checked").val()=="no"){
	    price = "";
	  }
	  
	  var style = "";
	  if(jQuery("#productStyle").val()!="") {
	    style = jQuery("#productStyle").val();
	  }
	  
    <?php 
      $setting = new Cart66Setting();
      $cartImgPath = Cart66Setting::getValue('cart_images_url');
      if($cartImgPath) {
        if(strpos(strrev($cartImgPath), '/') !== 0) {
          $cartImgPath .= '/';
        }
        $buttonPath = $cartImgPath . 'add-to-cart.png';
      }
    ?>

    var button = '';

    <?php if($cartImgPath): ?>
      var buttonPath = '<?php echo $buttonPath ?>';
      button = "<img src='"+buttonPath+"' title='<?php _e( 'Add to Cart' , 'cart66' ); ?>' alt='<?php _e( 'Cart66 Add To Cart Button' , 'cart66' ); ?>'>";
    <?php else: ?>
      button = "<input type='button' class='Cart66ButtonPrimary' value='<?php _e( 'Add To Cart' , 'cart66' ); ?>' />";
    <?php endif; ?>

	  if($jq("#buttonImage").val()!=""){
	    button = "<img src='"+jQuery("#buttonImage").val()+"' title='<?php _e( 'Add to Cart' , 'cart66' ); ?>' alt='<?php _e( 'Cart66 Add To Cart Button' , 'cart66' ); ?>'>";
	  } 
    
    if($jq("input[@name='showPrice']:checked").val()=="only"){
      button= "";
    }
    
    var prevBox = "<div style='"+style+"'>"+price+button+"</div>";
	  
	  jQuery("#buttonPreview").html(prevBox).text();
	}

	function insertProductCode() {
		prod  = jQuery("#productNameSelector option:selected").val();

    showPrice = $jq("input[@name='showPrice']:checked").val();
    if(showPrice == 'no') {
      showPrice = 'showprice="no"';
    }
    else if(showPrice == 'only'){
      showPrice = 'showprice="only"';
    }
    else {
      showPrice = '';
    }

    buttonImage = '';
		if($jq("#buttonImage").val() != "") {
      buttonImage = 'img="' + $jq("#buttonImage").val() + '"';
    }

		type =  prodtype[jQuery("#productNameSelector option:selected").index()];
		if($jq("#productStyle").val()!=""){
		  style  = 'style="'+$jq("#productStyle").val()+'"';
		}
		else {
		  style = '';
		}
		html = '&nbsp;[add_to_cart '+type+'="'+prod+'" '+style+' ' +showPrice+' '+buttonImage+' ]&nbsp;';

		tinyMCEPopup.execCommand("mceBeginUndoLevel");
		tinyMCEPopup.execCommand('mceInsertContent', false, html);
	 	tinyMCEPopup.execCommand("mceEndUndoLevel");
	  tinyMCEPopup.close();
	}
	
	function toggleInsert(){
	  if($jq("#panel2").is(":visible")){
	    $jq("#insertProductButton").hide();
	    
	  }
	  else{
	    $jq("#insertProductButton").show();
	  }
	}
	
	function shortcode(code){
	  html = '&nbsp;['+code+']&nbsp;';

		tinyMCEPopup.execCommand("mceBeginUndoLevel");
		tinyMCEPopup.execCommand('mceInsertContent', false, html);
	 	tinyMCEPopup.execCommand("mceEndUndoLevel");
	  tinyMCEPopup.close();
	}

	function shortcode_wrap(open, close){
	  html = '&nbsp;['+open+"]&nbsp;<br/>[/"+close+']';

		tinyMCEPopup.execCommand("mceBeginUndoLevel");
		tinyMCEPopup.execCommand('mceInsertContent', false, html);
	 	tinyMCEPopup.execCommand("mceEndUndoLevel");
	  tinyMCEPopup.close();
	}
	
	jQuery(document).ready(function(){
	  preview();
	  jQuery("input").change(function(){preview();});
	  jQuery("input").click(function(){preview();});
	  
	  
	  jQuery("#productNameSelector").change(function(){
	    preview();
	  })
	})
	
	//-->
	</script>
	<style type="text/css" media="screen">
	 #buttonPreview{
	   padding:5px;
	 }
	</style>
	<base target="_self" />
	
	<style type="text/css">
	#shortCodeList,
	#systemShortCodeList {
	  border-collapse: collapse;
	}
	#systemShortCodeList tr td,
	#shortCodeList tr td {
    padding: 5px;
    border-spacing: 0px;
  }
  .smallText{
    font-size:11px;
    text-decoration:underline;
    cursor:pointer;
  }
  .gfProductMessage{
    display:none;
    position:absolute;
    background-color:#fff;
    border:1px solid;
    font-size:12px;
    padding:0px 15px;
    width:430px;
    margin:-10px 0px 0px 0px;
  }
  .closeMessage{
    font-size:14px;
  }
	</style>
</head>
<body id="cart66" onLoad="tinyMCEPopup.executeOnLoad('init();');" style="display: none">
	<form onSubmit="insertSomething();" action="#">
	<div class="tabs">
		<ul>
			<li id="tab"><span><a href="javascript:mcTabs.displayTab('tab','panel');toggleInsert();"><?php  _e('Pick a product'); ?></a></span></li>
			<li id="tab2"><span><a href="javascript:mcTabs.displayTab('tab2','panel2');toggleInsert();"><?php  _e('Shortcode Reference'); ?></a></span></li>
		</ul>
	</div>
	<div class="panel_wrapper">
		<div id="panel" class="panel current">
		  
			<table border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td class="phplabel"><label for="productNameSelector"><?php  _e('Your products'); ?>:</label></td>
					<td class="phpinput"><select id="productNameSelector" name="productName"><?php echo $options; ?></select><br>
				</tr>
				<tr>
				  <td class="phplabel"><label for="productStyle"><?php  _e('CSS style'); ?>:</label></td>
				  <td class="phpinput"><input id="productStyle" name="productStyle" size="34"></td>
				</tr>
				<tr>
				  <td class="phplabel"><label for="showPrice"><?php  _e('Show price'); ?>:</label></td>
          <td class="phpinput">
            <input type='radio' style="border: none;" id="showPrice" name="showPrice" value='yes' checked> Yes
            <input type='radio' style="border: none;" id="showPrice" name="showPrice" value='no'> No
            <input type='radio' style="border: none;" id="showPrice" name="showPrice" value='only'> Price Only
          </td>
				</tr>
				<tr>
				  <td class="phplabel"><label for="buttonImage"><?php  _e('Button path'); ?>:</label></td>
				  <td class="phpinput"><input id="buttonImage" name="buttonImage" size="34"></td>
				</tr>
				<tr>
				  <td class="phplabel" valign="top"><label for="buttonImage"><?php  _e('Preview'); ?>:</label></td>
				  <td class="" valign="top" id="buttonPreview"> 
				  </td>
				</tr>
			</table>
		</div>
    
    <div id="panel2" class="panel">
      <p>Click on a short code to insert it into your content.</p>
      
      <table id="shortCodeList" class="66altColor" cellpadding="0" width="95%">
        <tr>
          <td colspan="2"><br/><strong>Shortcode Quick Reference:</strong></td>
        </tr>
        
        <?php if(CART66_PRO): ?>
        <tr>
          <td><div class="shortcode" onclick="shortcode('account_info');"><a title="Insert [account_info]">[account_info]</a></div></td>
          <td>Show link to manage subscription account information</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('account_login');"><a title="Insert [account_login]">[account_login]</a></div></td>
          <td>Account login form</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('account_logout');"><a title="Insert [account_logout]">[account_logout]</a></div></td>
          <td>Logs user out of account</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('account_logout_link');"><a title="Insert [account_logout_link]">[account_logout_link]</a></div></td>
          <td>Show link to log out of account</td>
        </tr>
        <?php endif; ?>
        
        
        <tr>
          <td><div class="shortcode" onclick="shortcode('add_to_cart item=&quot;&quot;');"><a title="Insert [add_to_cart]">[add_to_cart item=""]</a></div></td>
          <td>Create add to cart button</td>
        </tr>
        
        
        <?php if(CART66_PRO): ?>
        <tr>
          <td><div class="shortcode" onclick="shortcode('cancel_paypal_subscription');"><a title="Insert [cancel_paypal_subscription]">[cancel_paypal_subscription]</a></div></td>
          <td>Link to cancel PayPal subscription</td>
        </tr>
        <?php endif; ?>
        
        
        <tr>
          <td><div class="shortcode" onclick="shortcode('cart');"><a title="Insert [cart]">[cart]</a></div></td>
          <td>Show the shopping cart</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('cart mode=&quot;read&quot;');"><a title="Insert [cart mode=&quot;read&quot;]">[cart mode="read"]</a></div></td>
          <td>Show the shopping cart in read-only mode</td>
        </tr>
        
        
        <?php if(CART66_PRO): ?>
        <tr>
          <td><div class="shortcode" onclick="shortcode('checkout_authorizenet');"><a title="Insert [checkout_authorizenet]">[checkout_authorizenet]</a></div></td>
          <td>Authorize.net (or AIM compatible gateway) checkout form</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('checkout_eway');"><a title="Insert [checkout_eway]">[checkout_eway]</a></div></td>
          <td>Eway checkout form</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('checkout_mwarrior');"><a title="Insert [checkout_mwarrior]">[checkout_mwarrior]</a></div></td>
          <td>Merchant Warrior checkout form</td>
        </tr>
        <tr>
          <td><div class="shortcode" onClick="shortcode('checkout_payleap');"><a title="Insert [checkout_mwarrior]">[checkout_payleap]</a></div></td>
          <td>PayLeap checkout form</td>
        </tr>
        <?php endif; ?>
        
        <tr>
          <td><div class="shortcode" onclick="shortcode('checkout_mijireh');"><a title="Insert [checkout_mijireh]">[checkout_mijireh]</a></div></td>
          <td>Mijireh Checkout (Accept Credit Cards - PCI Compliant)</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('checkout_manual');"><a title="Insert [checkout_manual]">[checkout_manual]</a></div></td>
          <td>Checkout form that does not process credit cards</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('checkout_paypal');"><a title="Insert [checkout_paypal]">[checkout_paypal]</a></div></td>
          <td>PayPal Website Payments Standard checkout button</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('checkout_paypal_express');"><a title="Insert [checkout_paypal_express]">[checkout_paypal_express]</a></div></td>
          <td>PayPal Express checkout button</td>
        </tr>
        
        
        <?php if(CART66_PRO): ?>
        <tr>
          <td><div class="shortcode" onclick="shortcode('checkout_paypal_pro');"><a title="Insert [checkout_paypal_pro]">[checkout_paypal_pro]</a></div></td>
          <td>PayPal Pro checkout form</td>
        </tr>
        <?php endif; ?>
        
        
        <tr>
          <td><div class="shortcode" onclick="shortcode('clear_cart');"><a title="Insert [clear_cart]">[clear_cart]</a></div></td>
          <td>Clear the contents of the shopping cart</td>
        </tr>
        
        <?php if(CART66_PRO): ?>
        <tr>
          <td><div class="shortcode" onclick="shortcode('email_opt_out');"><a title="Insert [email_opt_out]">[email_opt_out]</a></div></td>
          <td>Allow Cart66 members to opt out of receiving notifications about the status of their account.</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('hide_from level');"><a title="Insert [hide_from]">[hide_from level=""]</a></div></td>
          <td>Hide content from members without the listed feature levels - opposite of [show_to]</td>
        </tr>
        <?php endif; ?>
        
        
        <tr>
          <td><div class="shortcode" onclick="shortcode('shopping_cart');"><a title="Insert [shopping_cart]">[shopping_cart]</a></div></td>
          <td>Show the Cart66 sidebar widget</td>
        </tr>


        <?php if(CART66_PRO): ?>
        <tr>
          <td><div class="shortcode" onclick="shortcode('terms_of_service');"><a title="Insert [terms_of_service]">[terms_of_service]</a></div></td>
          <td>Show the terms of service agreement.</td> 
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('show_to');"><a title="Insert [show_to]">[show_to]</a></div></td>
          <td>Show content only to members with the listed feature levels - opposite of [hide_from]</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('subscription_feature_level');"><a title="Insert [subscription_feature_level]">[subscription_feature_level]</a></div></td>
          <td>Show the name of the subscription feature level for the currently logged in user</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('subscription_name');"><a title="Insert [subscription_name]">[subscription_name]</a></div></td>
          <td>Show the name of the subscription for the currently logged in user</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('zendesk_login');"><a title="Insert [zendesk_login]">[zendesk_login]</a></div></td>
          <td>Listens for remote login calls from Zendesk</td>
        </tr>
        <?php endif; ?>
        
      </table>
      
      <br/>
      
      <table id="systemShortCodeList" class="66altColor" cellpadding="0" width="95%">
        <tr>
          <td colspan="2"><strong>System Shortcodes</strong></td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('express');"><a title="Insert [express]">[express]</a></div></td>
          <td>Listens for PayPal Express callbacks <br/>Belongs on system page store/express</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('ipn');"><a title="Insert [ipn]">[ipn]</a></div></td>
          <td>PayPal Instant Payment Notification <br/>Belongs on system page store/ipn</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('receipt');"><a title="Insert [receipt]">[receipt]</a></div></td>
          <td>Shows the customer's receipt after a successful sale <br/>Belongs on system page store/receipt</td>
        </tr>
        
        <?php if(CART66_PRO): ?>
        <tr>
          <td><div class="shortcode" onclick="shortcode('spreedly_listener');"><a title="Insert [spreedly_listener]">[spreedly_listener]</a></div></td>
          <td>Listens for spreedly account changes <br/>Belongs on system page store/spreedly</td>
        </tr>
        <?php endif; ?>
        
      </table>
      
    </div>
	</div>
	<div class="mceActionPanel">
		<div id="insertProductButton" style="float: right">
				<input type="button" id="insert" name="insert" value="<?php  _e('Insert'); ?>" onClick="insertProductCode();" />
		</div>
		<div style="float: left">
			<input type="button" id="cancel" name="cancel" value="<?php  _e('Cancel'); ?>" onClick="tinyMCEPopup.close();" />
		</div>
	</div>
</form>

<script type="text/javascript">
  (function($){
    $(document).ready(function(){
      $(".66altColor tr:even").css("background-color", "#fff");
      $(".66altColor tr:odd").css("background-color", "#eee");
    })
  })(jQuery);
</script>
</body>
</html>