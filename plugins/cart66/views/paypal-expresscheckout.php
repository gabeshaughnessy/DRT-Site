<?php
$settingsOk = true;
$username = Cart66Setting::getValue('paypalpro_api_username');
$password = Cart66Setting::getValue('paypalpro_api_password');
$signature = Cart66Setting::getValue('paypalpro_api_signature');
if(!($username && $password && $signature)) {
  $settingsOk = false;
  throw new Cart66Exception('Invalid PayPal Express Configuration', 66501);
}

if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['cart66-action']) && $_POST['cart66-action'] == 'paypalexpresscheckout') {
  // Set up the PayPal object
  $pp = new Cart66PayPalExpressCheckout();
  
  // Calculate total amount to charge customer
  $total = Cart66Session::get('Cart66Cart')->getGrandTotal(false);
  $total = number_format($total, 2, '.', '');
  Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] PayPal Express Checkout grand total: $total");
  
  // Calculate total cost of all items in cart, not including tax and shipping
  $itemTotal = Cart66Session::get('Cart66Cart')->getNonSubscriptionAmount();
  $itemTotal = number_format($itemTotal, 2, '.', '');
  Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] PayPal Express Checkout item total: $itemTotal");
  
  // Calculate shipping costs
  $shipping = Cart66Session::get('Cart66Cart')->getShippingCost();
  $promotion = Cart66Session::get('Cart66Promotion');
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
  
  // Calculate IPN URL
  $ipnPage = get_page_by_path('store/ipn');
  $ipnUrl = get_permalink($ipnPage->ID);

  // Set shipping as an item if the item total is $0.00, otherwise PayPal will fail
  if($itemTotal == 0 && $shipping > 0) {
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Setting shipping to be an item because the item total would otherwise be $0.00");
    $itemTotal = $shipping;
    $itemData = array(
      'NAME' => 'Shipping',
      'AMT' => $shipping,
      'NUMBER' => 'SHIPPING',
      'QTY' => 1
    );
    //$pp->addItem($itemData);
    $shipping = 0;
  }
  else {
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not making shipping part of the item list. Item Total: $itemTotal");
  }
  
  // Set payment information
  $payment = array(
    'AMT' => $total,
    'CURRENCYCODE' => CURRENCY_CODE,
    'ITEMAMT' => $itemTotal,
    'SHIPPINGAMT' => $shipping,
    'NOTIFYURL' => $ipnUrl
  );
  Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Setting Payment Details:\n".print_r($payment,true));
  $pp->setPaymentDetails($payment);
  
  // Add cart items to PayPal
  $pp->populatePayPalCartItems();
  
  // Set Express Checkout URLs
  $returnPage = get_page_by_path('store/express');
  $returnUrl = get_permalink($returnPage->ID);
  $cancelPage = get_page_by_path('store/checkout');
  $cancelUrl = get_permalink($cancelPage->ID);
  $localeCode = Cart66Common::getLocaleCode();
  $ecUrls = array(
    'RETURNURL' => $returnUrl,
    'CANCELURL' => $cancelUrl,
    'LOCALECODE' => $localeCode
  );
  $pp->setEcUrls($ecUrls);
  
  $response = $pp->SetExpressCheckout();
  $ack = strtoupper($response['ACK']);
  if('SUCCESS' == $ack || 'SUCCESSWITHWARNING' == $ack) {
    Cart66Session::set('PayPalProToken', $response['TOKEN']);
    $expressCheckoutUrl = $pp->getExpressCheckoutUrl($response['TOKEN']);
  	wp_redirect($expressCheckoutUrl);
  	exit;
  }
  elseif(empty($ack)) {
      echo '<pre>Failed to connect via curl to PayPal. The most likely cause is that your PHP installation failed to verify that the CA cert is OK</pre>';
  }
  else {
    try {
      throw new Cart66Exception(ucwords($response['L_SHORTMESSAGE0']), 66503);
    }
    catch(Cart66Exception $e) {
      $exception = Cart66Exception::exceptionMessages($e->getCode(), $e->getMessage(), array('Error Number: ' . $response['L_ERRORCODE0'], $response['L_LONGMESSAGE0']));
      echo Cart66Common::getView('views/error-messages.php', $exception);
    }
  }
}
?>

<?php if($settingsOk): ?>
<form action="" method='post' id="paypalexpresscheckout">
  <input type='hidden' name='cart66-action' value='paypalexpresscheckout'>
  <?php
    $paypalImageUrl = 'https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif';
    if(CART66_PRO && Cart66Setting::getValue('custom_paypal_express_image')) {
      $paypalImageUrl = Cart66Setting::getValue('custom_paypal_express_image');
    }
    ?>
    <input type="image" id='PayPalExpressCheckoutButton' src="<?php echo $paypalImageUrl; ?>" value="PayPal Express Checkout" name="PayPal Express Checkout" />
</form>
<?php endif; ?>