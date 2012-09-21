<!-- PayPal Checkout -->
<?php
  $items = Cart66Session::get('Cart66Cart')->getItems();
  $shipping = Cart66Session::get('Cart66Cart')->getShippingCost();
  $shippingMethod = Cart66Session::get('Cart66Cart')->getShippingMethodName();
  $setting = new Cart66Setting();
  $paypalEmail = Cart66Setting::getValue('paypal_email');
  if(!$paypalEmail) {
    throw new Cart66Exception('Invalid PayPal Standard Configuration', 66504); 
  }
  $returnUrl = Cart66Setting::getValue('paypal_return_url');
  $promotion = Cart66Session::get('Cart66Promotion');
 
  
  $checkoutOk = true;
  if(Cart66Session::get('Cart66Cart')->requireShipping()) {
    $liveRates = Cart66Setting::getValue('use_live_rates');
    if($liveRates) {
      if(!Cart66Session::get('Cart66LiveRates')) {
        $checkoutOk = false;
      }
      else {
        // Check to make sure a valid shipping method is selected
        $selectedRate = Cart66Session::get('Cart66LiveRates')->getSelected();
        if($selectedRate->rate === false) {
          $checkoutOk = false;
        }
      }
    }
  }
  
  
  $ipnPage = get_page_by_path('store/ipn');
  $ipnUrl = get_permalink($ipnPage->ID);
  
  // Start affiliate program integration
  $aff = '';
  if (Cart66Session::get('ap_id')) {
    $aff .= Cart66Session::get('ap_id');
  }
  elseif(isset($_COOKIE['ap_id'])) {
    $aff .= $_COOKIE['ap_id'];
  }
  // End affilitate program integration
  
  if(!empty($paypalEmail)):
?>

<?php if(!empty($data['style'])): ?>
<style type='text/css'>
  #paypalCheckout {
    <?php $styles = explode(';', $data['style']); ?>
    <?php foreach($styles as $style): ?>
      <?php if(!empty($style)) echo $style . ";\n"; ?>
    <?php endforeach; ?>
  }
</style>
<?php else: ?>
<style type='text/css'>
  #paypalCheckout {
    clear:both; 
    float: right; 
    margin: 10px 10px 0px 0px;";
  }
</style>
<?php endif; ?>


<?php if(Cart66Session::get('Cart66Cart')->countItems() > 0): ?>
  <?php
    $paypalAction = 'https://www.paypal.com/cgi-bin/webscr';
    if(SANDBOX) {
      $paypalAction = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    }
  ?>
  <?php if($checkoutOk): ?>
    <form id='paypalCheckout' action="<?php echo $paypalAction ?>" method="post">
      <?php 
        $i = 1;
        $gfIds = array();
        foreach($items as $item) {
          $name  = $item->getFullDisplayName() .  ' ' . $item->getCustomFieldInfo();
          $escapedName = htmlentities($name);
          echo "\n<input type='hidden' name='item_name_$i' value=\"" . $escapedName . "\" />";
          echo "\n<input type='hidden' name='item_number_$i' value='" . $item->getItemNumber() . "' />";
          echo "\n<input type='hidden' name='amount_$i' value='" . $item->getProductPrice() . "' />";
          echo "\n<input type='hidden' name='quantity_$i' value='" . $item->getQuantity() . "' />";
          $itemGfIds = $item->getFormEntryIds();
          if(count($itemGfIds) > 0) {
            $gfIds[] = $i . ':' . $itemGfIds[0];
          }
          $i++;
        }
        $gfIds = count($gfIds) > 0 ? implode(',', $gfIds) : '';
        
        echo "\n<input type='hidden' name='business' value='" . Cart66Setting::getValue('paypal_email'). "' />";
        echo "\n<input type='hidden' name='shopping_url' value='" . Cart66Setting::getValue('shopping_url') . "' />\n";
      
        // Send shipping price as an item amount if the item total - discount amount = $0.00 otherwise paypal will ignore the discount
        $discount = Cart66Session::get('Cart66Cart')->getDiscountAmount();
        
        if(is_object($promotion) && $promotion->apply_to == 'total') {
          $itemTotal = Cart66Session::get('Cart66Cart')->getNonSubscriptionAmount();
          $itemDiscount = Cart66Session::get('Cart66Cart')->getDiscountAmount();
          if($itemDiscount > 0) {
            $itemTotal = $itemTotal - $itemDiscount;            
          }
          if($itemTotal <= 0) {
            $discount = Cart66Session::get('Cart66Cart')->getNonSubscriptionAmount();
            $shipping = $shipping + $itemTotal;
            $itemTotal = 0;
          }
          
        }
        
        if(is_object($promotion) && $promotion->apply_to == 'products'){
          $itemTotal = Cart66Session::get('Cart66Cart')->getNonSubscriptionAmount() - Cart66Session::get('Cart66Cart')->getDiscountAmount();
        }
        
        if(is_object($promotion) && $promotion->apply_to == 'shipping'){
          $shipping = $shipping - Cart66Session::get('Cart66Cart')->getDiscountAmount();
          $discount = 0;
        }
        
        
        if(isset($itemTotal) && $itemTotal == 0 && $shipping > 0) {
          echo "\n<input type='hidden' name='item_name_$i' value=\"Shipping\" />";
          echo "\n<input type='hidden' name='item_number_$i' value='SHIPPING' />";
          echo "\n<input type='hidden' name='amount_$i' value='" . $shipping . "' />";
          echo "\n<input type='hidden' name='quantity_$i' value='1' />";
          $shipping = 0;
        }
      ?>
      
      <input type='hidden' name='cmd' value='_cart' />
      <input type='hidden' name='charset' value='utf-8'>
      <input type='hidden' name='upload' value='1' />
      <input type='hidden' name='no_shipping' value='2' />
      <input type='hidden' name='currency_code' value='<?php echo CURRENCY_CODE; ?>' id='currency_code' />
      <input type='hidden' name='custom' value='<?php echo $shippingMethod ?>|<?php echo $aff;  ?>|<?php echo $gfIds ?>|<?php if(Cart66Session::get('Cart66PromotionCode')) { echo Cart66Session::get('Cart66PromotionCode'); } ?>' />
      <?php if($shipping > 0): ?>
        <input type='hidden' name='handling_cart' value='<?php echo $shipping ?>' />
      <?php endif;?>
    
      <?php if(Cart66Session::get('Cart66Promotion') && Cart66Session::get('Cart66Promotion')->getDiscountAmount(Cart66Session::get('Cart66Cart')) > 0): ?>
        <input type='hidden' name='discount_amount_cart' value='<?php echo $discount; ?>'/>
      <?php endif; ?>
    
      <input type='hidden' name='notify_url' value='<?php echo $ipnUrl ?>'>
      <?php if($returnUrl): ?>
        <input type='hidden' name='return' value='<?php echo $returnUrl ?>' />
      <?php endif; ?>
  
      <?php
      $paypalImageUrl = 'https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif';
      if(CART66_PRO && Cart66Setting::getValue('custom_paypal_standard_image')) {
        $paypalImageUrl = Cart66Setting::getValue('custom_paypal_standard_image');
      }
      ?>
      <input id='PayPalCheckoutButton' type='image' src='<?php echo $paypalImageUrl; ?>' value='Checkout With PayPal' />
    </form>
  <?php endif; ?>
<?php endif; ?>

  <?php else: ?>
    <p><?php _e( 'You must configure your payment settings' , 'cart66' ); ?></p>
  <?php endif; ?>
