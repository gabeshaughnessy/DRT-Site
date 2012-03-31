<!-- PayPal Checkout -->
<?php
  $items = $_SESSION['Cart66Cart']->getItems();
  $shipping = $_SESSION['Cart66Cart']->getShippingCost();
  $shippingMethod = $_SESSION['Cart66Cart']->getShippingMethodName();
  $setting = new Cart66Setting();
  $paypalEmail = Cart66Setting::getValue('paypal_email');
  $returnUrl = Cart66Setting::getValue('paypal_return_url');
  
  $checkoutOk = true;
  if($_SESSION['Cart66Cart']->requireShipping()) {
    $liveRates = Cart66Setting::getValue('use_live_rates');
    if($liveRates) {
      if(!isset($_SESSION['Cart66LiveRates'])) {
        $checkoutOk = false;
      }
      else {
        // Check to make sure a valid shipping method is selected
        $selectedRate = $_SESSION['Cart66LiveRates']->getSelected();
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
  if (!empty($_SESSION['ap_id'])) {
    $aff .= $_SESSION['ap_id'];
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


<?php if($_SESSION['Cart66Cart']->countItems() > 0): ?>
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
        $itemTotal = $_SESSION['Cart66Cart']->getNonSubscriptionAmount() - $_SESSION['Cart66Cart']->getDiscountAmount();
        if($itemTotal == 0 && $shipping > 0) {
          echo "\n<input type='hidden' name='item_name_$i' value=\"Shipping\" />";
          echo "\n<input type='hidden' name='item_number_$i' value='SHIPPING' />";
          echo "\n<input type='hidden' name='amount_$i' value='" . $shipping . "' />";
          echo "\n<input type='hidden' name='quantity_$i' value='1' />";
          $shipping = 0;
        }
      ?>
      
      <input type="hidden" name="cmd" value="_cart" />
      <input type="hidden" name="upload" value="1" />
      <input type="hidden" name="no_shipping" value="2" />
      <input type="hidden" name="currency_code" value="<?php echo CURRENCY_CODE; ?>" id="currency_code" />
      <input type="hidden" name="custom" value="<?php echo $shippingMethod ?>|<?php echo $aff;  ?>|<?php echo $gfIds ?>" />
      
      <?php if($shipping > 0): ?>
        <input type='hidden' name='handling_cart' value='<?php echo $shipping ?>' />
      <?php endif;?>
    
      <?php if($_SESSION['Cart66Cart']->getDiscountAmount() > 0): ?>
        <input type="hidden" name="discount_amount_cart" value="<?php echo number_format($_SESSION['Cart66Cart']->getDiscountAmount(), 2, '.', ''); ?>"/>
      <?php endif; ?>
    
      <input type="hidden" name="notify_url" value="<?php echo $ipnUrl ?>">
      <?php if($returnUrl): ?>
        <input type="hidden" name="return" value="<?php echo $returnUrl ?>" />
      <?php endif; ?>
  
      <input id='PayPalCheckoutButton' type='image' src='https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif' value='Checkout With PayPal' />
    </form>
  <?php endif; ?>
<?php endif; ?>

  <?php else: ?>
    <p>You must configure your payment settings</p>
  <?php endif; ?>
