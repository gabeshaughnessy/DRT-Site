<?php
$settingsOk = true;
$username = Cart66Setting::getValue('paypalpro_api_username');
$password = Cart66Setting::getValue('paypalpro_api_password');
$signature = Cart66Setting::getValue('paypalpro_api_signature');
if(!($username && $password && $signature)) {
  $settingsOk = false;
  ?>
  <div id='cart66Errors'>
    <p><strong>PayPal Express Checkout Is Not Configured</strong></p>
    <p>In order to use PayPal Express Checkout you must enter your PayPal API username, password and signature in the Cart66 Settings Panel</p>
  </div>
  <?php
}


if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['cart66-action'] == 'paypalexpresscheckout') {
  // Set up the PayPal object
  $pp = new Cart66PayPalExpressCheckout();
  
  // Calculate total amount to charge customer
  $total = $_SESSION['Cart66Cart']->getGrandTotal(false);
  $total = number_format($total, 2, '.', '');
  
  // Calculate total cost of all items in cart, not including tax and shipping
  $itemTotal = $_SESSION['Cart66Cart']->getNonSubscriptionAmount() - $_SESSION['Cart66Cart']->getDiscountAmount();
  $itemTotal = number_format($itemTotal, 2, '.', '');
  
  // Calculate shipping costs
  $shipping = $_SESSION['Cart66Cart']->getShippingCost();
  
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
    $pp->addItem($itemData);
    $shipping = 0;
  }
  else {
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not making shipping part of the item list. Item Total: $itemTotal");
  }
  
  // Set payment information
  $payment = array(
    'PAYMENTREQUEST_0_AMT' => $total,
    'PAYMENTREQUEST_0_CURRENCYCODE' => CURRENCY_CODE,
    'PAYMENTREQUEST_0_ITEMAMT' => $itemTotal,
    'PAYMENTREQUEST_0_SHIPPINGAMT' => $shipping,
    'PAYMENTREQUEST_0_NOTIFYURL' => $ipnUrl
  );
  $pp->setPaymentDetails($payment);
  
  // Add cart items to PayPal
  $items = $_SESSION['Cart66Cart']->getItems(); // An array of Cart66CartItem objects
  foreach($items as $i) {
    if($i->isPayPalSubscription()) {
      $plan = $i->getPayPalSubscription();
      $itemData = array(
        'BILLINGAGREEMENTDESCRIPTION' => $plan->name . ' ' . str_replace('&nbsp;', ' ', strip_tags($plan->getPriceDescription($plan->offerTrial > 0, '(trial)'))),
      );
      $pp->addItem($itemData);
      
      $chargeAmount = $i->getProductPrice();
      if($chargeAmount > 0) {
        $itemData = array(
          'NAME' => $i->getFullDisplayName(),
          'AMT' => $chargeAmount,
          'NUMBER' => $i->getItemNumber(),
          'QTY' => $i->getQuantity()
        );
      }
      $pp->addItem($itemData);
    }
    else {
      $itemData = array(
        'NAME' => $i->getFullDisplayName(),
        'AMT' => $i->getProductPrice(),
        'NUMBER' => $i->getItemNumber(),
        'QTY' => $i->getQuantity()
      );
      $pp->addItem($itemData);
    }
  }
  
  // Add a coupon discount if needed
  $discount = number_format($_SESSION['Cart66Cart']->getDiscountAmount(), 2, '.', '');
  
  if($discount > 0) {
    $negDiscount = 0 - $discount;
    $itemData = array(
      'NAME' => 'Discount',
      'AMT' => $negDiscount,
      'NUMBER' => 'DSC',
      'QTY' => 1
    );
    $pp->addItem($itemData);
  }
  
  // Set Express Checkout URLs
  $returnPage = get_page_by_path('store/express');
  $returnUrl = get_permalink($returnPage->ID);
  $cancelPage = get_page_by_path('store/checkout');
  $cancelUrl = get_permalink($cancelPage->ID);
  $ecUrls = array(
    'RETURNURL' => $returnUrl,
    'CANCELURL' => $cancelUrl
  );
  $pp->setEcUrls($ecUrls);
  
  $response = $pp->SetExpressCheckout();
  $ack = strtoupper($response['ACK']);
  if('SUCCESS' == $ack || 'SUCCESSWITHWARNING' == $ack) {
    $_SESSION['PayPalProToken'] = $response['TOKEN'];
    $expressCheckoutUrl = $pp->getExpressCheckoutUrl($response['TOKEN']);
  	header("Location: $expressCheckoutUrl");
  	exit;
  }
  elseif(empty($ack)) {
      echo '<pre>Failed to connect via curl to PayPal. The most likely cause is that your PHP installation failed to verify that the CA cert is OK</pre>';
  }
  else {
    echo "<pre>PayPal Response: $ack\n";
    print_r($response);
    echo "</pre>";
  }
}
?>

<?php if($settingsOk): ?>
<?php  
if(!isset($data['style'])) {
  $data['style'] = "clear:both; float: right; margin: 10px 10px 0px 0px;";
}
?>
<form action="" method='post' style="<?php echo $data['style']; ?>">
  <input type='hidden' name='cart66-action' value='paypalexpresscheckout'>
  <input type="image" id='PayPalExpressCheckoutButton' src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" align="left" style="margin-right:7px;" value="PayPal Express Checkout" />
</form>
<?php endif; ?>
