<?php
$supportedGateways = array (
  'Cart66AuthorizeNet',
  'Cart66PayPalPro',
  'Cart66ManualGateway'
);

$errors = array();

$gateway = $data['gateway']; // Object instance inherited from Cart66GatewayAbstract 

if($_SERVER['REQUEST_METHOD'] == "POST") {
  
  $gatewayName = Cart66Common::postVal('cart66-gateway-name');
  if(in_array($gatewayName, $supportedGateways)) {
    $gateway->validateCartForCheckout();
    $gateway->setBilling($_POST['billing']);
    $gateway->setPayment($_POST['payment']);
    if(isset($_POST['sameAsBilling'])) {
      $gateway->setShipping($_POST['billing']);
    }
    elseif(isset($_POST['shipping'])) {
      $gateway->setShipping($_POST['shipping']);
    }

    $errors = $gateway->getErrors();     // Error info for server side error code
    $jqErrors = $gateway->getJqErrors(); // Error info for client side error code
    
    // Charge credit card for one time transaction using Authorize.net API
    if(count($errors) == 0 && empty($_SESSION['Cart66InventoryWarning'])) {
      $taxLocation = $gateway->getTaxLocation();
      $tax = $gateway->getTaxAmount();
      $total = $_SESSION['Cart66Cart']->getGrandTotal() + $tax;
      $oneTimeTotal = $total - $_SESSION['Cart66Cart']->getSubscriptionAmount();
      $customerId = 0;
      
      
      // Process subscription charges
      if($_SESSION['Cart66Cart']->hasSubscriptionProducts()) {
        $billing = $_POST['billing'];
        $payment = $_POST['payment'];
        
        $account = new Cart66Account();
        
        if(isset($_SESSION['Cart66SubscriberToken'])) {
          $account->loadBySubscriberToken($_SESSION['Cart66SubscriberToken']);
        }
        elseif(Cart66Common::isLoggedIn()) {
          $account->load($_SESSION['Cart66AccountId']);
        }
        
        $accountData = array();
        $accountData['billing_first_name'] = $billing['firstName'];
        $accountData['billing_last_name'] = $billing['lastName'];
        $accountData['email'] = $payment['email'];
        
        if(isset($payment['password']) && !empty($payment['password'])) {
          $accountData['password'] = md5($payment['password']);
        }
        
        $account->setData($accountData); // Merge form field values 
        
        if($account->validate()) {
          $account->save(); // Save account data locally which will create an account id and/or update local values
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account data validated and saved for account id: " . $account->id);
          
          try {
            $spreedlyCard = new SpreedlyCreditCard();
            $spreedlyCard->hydrateFromCheckout();
            $subscriptionId = $_SESSION['Cart66Cart']->getSpreedlySubscriptionId();
            $account->createSpreedlySubscription($subscriptionId, $spreedlyCard);
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Just finshed creating subscription. Account data: " . print_r($account->getData(), true));
            $customerId = $account->id;
          }
          catch(SpreedlyException $e) {
            $account->refresh();
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Failed to checkout: " . $e->getCode() . ' ' . $e->getMessage());
            $errors['spreedly failed'] = $e->getMessage();
            if(empty($account->subscriberToken)) {
              Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] About to delete local account after spreedly failure: " . print_r($account->getData(), true));
              $account->deleteMe();
            }
            else {
              // Set the subscriber token in the session for repeat attempts to create the subscription
              $_SESSION['Cart66SubscriberToken'] = $account->subscriberToken;
            }
          }
          
        }
        else {
          $errors = $account->getErrors();
          $jqErrors = $account->getJqErrors();
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account validation failed. " . print_r($errors, true));
        }
      }

      if(count($errors) == 0) {
        
        // Look for constant contact opt-in
        if(CART66_PRO) { include(WP_PLUGIN_DIR . "/cart66/pro/Cart66ConstantContactOptIn.php"); }
        
        $gatewayName = get_class($gateway);
        $gateway->initCheckout($oneTimeTotal);
        if($oneTimeTotal > 0 || $gatewayName == 'Cart66ManualGateway') {
          $transactionId = $gateway->doSale();
        }
        else {
          // Do not attempt to charge $0.00 transactions to live gateways
          $transactionId = $transId = 'MT-' . Cart66Common::getRandString();
        }
        
        if($transactionId) {
          // Set order status based on Cart66 settings
          $statusOptions = Cart66Common::getOrderStatusOptions();
          $status = $statusOptions[0];

          // Save the order locally
          $orderId = $gateway->saveOrder($total, $tax, $transactionId, $status, $customerId);

          // Send buyer to receipt page
          unset($_SESSION['Cart66SubscriberToken']);
          $_SESSION['order_id'] = $orderId;
          $receiptLink = Cart66Common::getPageLink('store/receipt');
          header("Location: " . $receiptLink);
        }
        else {
          // Attempt to discover reason for transaction failure
          $errors['Could Not Process Transaction'] = $gateway->getTransactionResponseDescription();
        }
      }
      
    }
    
  } // End if supported gateway 
} // End if POST


// Show errors
if(count($errors)) {
  echo Cart66Common::showErrors($errors);
}

// Show inventory warning if there is one
if(!empty($_SESSION['Cart66InventoryWarning'])) {
  echo $_SESSION['Cart66InventoryWarning'];
  unset($_SESSION['Cart66InventoryWarning']);
}


// Build checkout form action URL
$checkoutPage = get_page_by_path('store/checkout');
$ssl = Cart66Setting::getValue('auth_force_ssl');
$url = get_permalink($checkoutPage->ID);
if(Cart66Common::isHttps()) {
  $url = str_replace('http:', 'https:', $url);
}

// Determine which gateway is in use
$gatewayName = get_class($data['gateway']);

// Make it easier to get to payment, billing, and shipping data
$p = $gateway->getPayment();
$b = $gateway->getBilling();
$s = $gateway->getShipping();

// Include the HTML markup for the checkout form
include(WP_PLUGIN_DIR . '/cart66/views/checkout-form.php');

// Include the client side javascript validation                 
include(WP_PLUGIN_DIR . '/cart66/views/client/checkout.php'); 