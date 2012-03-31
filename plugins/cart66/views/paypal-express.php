<?php
$username = Cart66Setting::getValue('paypalpro_api_username');
$password = Cart66Setting::getValue('paypalpro_api_password');
$signature = Cart66Setting::getValue('paypalpro_api_signature');

// Look for the Express Checkout token
$token = Cart66Common::postVal('token');
if(empty($token)) {
  $token = Cart66Common::getVal('token');
}

// Get details about the buyer
$pp = new Cart66PayPalExpressCheckout();
if(CART66_PRO) {
  $pp = new Cart66PayPalPro();
}
$details = $pp->GetExpressCheckoutDetails($token);

$account = false;
if($_SESSION['Cart66Cart']->hasSubscriptionProducts()) {
  // Set up a new Cart66Account and start by pre-populating the data or load the logged in account
  if($accountId = Cart66Common::isLoggedIn()) {
    $account = new Cart66Account($accountId);
  }
  else {
    $account = new Cart66Account();
    $account->firstName = $details['FIRSTNAME'];
    $account->lastName = $details['LASTNAME'];
    $account->email = $details['EMAIL'];
    if(isset($_POST['account'])) {
      $acctData = $_POST['account'];
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] New Account Data: " . print_r($acctData, true));
      $account->firstName = $acctData['first_name'];
      $account->lastName = $acctData['last_name'];
      $account->email = $acctData['email'];
      $account->username = $acctData['username'];
      $account->password = md5($acctData['password']);
    }
  }
}


$delivery = $_SESSION['Cart66Cart']->getShippingMethodName();
$tax = 0;

if($_SERVER['REQUEST_METHOD'] == "POST") {
  
  if($_POST['task'] == 'doexpresscheckout') {
    $keepGoing = true; // Change to false if a critical step in the checkout fails.
    $token = Cart66Common::postVal('token');
    $payerId = Cart66Common::postVal('PayerID');
    $itemAmount = $_SESSION['Cart66Cart']->getNonSubscriptionAmount() - $_SESSION['Cart66Cart']->getDiscountAmount();
    $shipping = $_SESSION['Cart66Cart']->getShippingCost();
    if(isset($_POST['tax']) && $_POST['tax'] > 0) {
      $tax = Cart66Common::postVal('tax');
    }
    
    
    // Create a new account if the account is not already saved
    if($account !== false && $account->id < 1) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Creating new Cart66Account");
      $errors = $account->validate();
      if($acctData['password'] != $acctData['password2']) {
        $errors[] = "Passwords do not match";
      }
      if(count($errors) == 0) {
        $account->save();
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Saved account for $account->firstName $account->lastName $account->email");
      }
      else {
        $keepGoing = false;
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account creation failed: " . print_r($errors, true));
      }
    }
    
    
    // DoExpressCheckout for non-subscription products
    if($keepGoing) {
      
      // Look for constant contact opt-in
      if(CART66_PRO) { include(WP_PLUGIN_DIR . "/cart66/pro/Cart66ConstantContactOptIn.php"); }
      
      if($itemAmount > 0 || $shipping > 0) {
        // Send shipping as the item amount if the item amount is $0.00 otherwise paypal will refuse the transaction
        if($itemAmount == 0 && $shipping > 0) {
          $itemAmount = $shipping;
          $shipping = 0;
        }
        
        Cart66Common::log("Preparing DoExpressCheckout:\nToken: $token\nPayerID: $payerId\nItem Amount: $itemAmount\nShipping: $shipping\nTax: $tax");
        $response = $pp->DoExpressCheckout($token, $payerId, $itemAmount, $shipping, $tax);
      }
      else {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Bypassing DoExpressCheckout because item amount is not greater than zero: $itemAmount");
        $response['ACK'] = 'SUCCESS'; // Forcing success since DoExpressCheckout wasn't called
      }
      $ack = strtoupper($response['ACK']);


      if('SUCCESS' == $ack || 'SUCCESSWITHWARNING' == $ack) {

        // Create Recurring Payment Profile if a subscription has been sold
        $profileResponse = array('ACK' => 'SKIPPED');
        if($cartItem = $_SESSION['Cart66Cart']->getPayPalSubscriptionItem()) {
          $planIndex = $_SESSION['Cart66Cart']->getPayPalSubscriptionIndex();
          $plan = new Cart66PayPalSubscription($cartItem->getPayPalSubscriptionId());
          $profileResponse = $pp->CreateRecurringPaymentsProfile($token, $cartItem, $planIndex);

          if('FAILURE' != strtoupper($profileResponse['ACK'])) {
            $paypalPaymentProfileId = $profileResponse['PROFILEID'];
            if(Cart66Common::isLoggedIn() && $account->isPayPalAccount()) {
              // Expire the current subscription and attach a new subscription
              $account->cancelSubscription('Your subscription has been canceled because you changed to a new subscription.', true);
            }
            $activeUntil = $plan->getStartTimeFormula();
            $account->attachPayPalSubscription($details, $paypalPaymentProfileId, $plan, $activeUntil);
          }
        }

        // Save the order
        if('FAILURE' != strtoupper($profileResponse['ACK'])) {
          $token = Cart66Common::postVal('token');
          $payerId = Cart66Common::postVal('PayerID');
          $opts = Cart66Setting::getValue('status_options');
          $status = '';
          if(!empty($opts)) {
            $opts = explode(',', $opts);
            $status = trim($opts[0]);
          }
          $transId = isset($response['TRANSACTIONID']) ? $response['TRANSACTIONID'] : '';
          $promo = $_SESSION['Cart66Cart']->getPromotion();
          $promoMsg = "none";
          if($promo) {
            $promoMsg = $promo->code . ' (-' . CURRENCY_SYMBOL . number_format($_SESSION['Cart66Cart']->getDiscountAmount(), 2) . ')';
          }

          list($shipFirstName, $shipLastName) = split(' ', $details['SHIPTONAME'], 2);
          $orderInfo['ship_first_name'] = $shipFirstName;
          $orderInfo['ship_last_name'] = $shipLastName;
          $orderInfo['ship_address'] = $details['SHIPTOSTREET'];
          $orderInfo['ship_address2'] = isset($details['SHIPTOSTREET2']) ? $details['SHIPTOSTREET2'] : '';
          $orderInfo['ship_city'] = $details['SHIPTOCITY'];
          $orderInfo['ship_state'] = $details['SHIPTOSTATE'];
          $orderInfo['ship_zip'] = $details['SHIPTOZIP'];

          $orderInfo['bill_first_name'] = $details['FIRSTNAME'];
          $orderInfo['bill_last_name'] = $details['LASTNAME'];
          $orderInfo['bill_address'] = '';
          $orderInfo['bill_address2'] = '';
          $orderInfo['bill_city'] = '';
          $orderInfo['bill_state'] = '';
          $orderInfo['bill_zip'] = '';

          $orderInfo['phone'] = preg_replace("/[^0-9]/", "", $details['PHONENUM']);
          $orderInfo['email'] = $details['EMAIL'];
          $orderInfo['coupon'] = $promoMsg;
          $orderInfo['tax'] = isset($response['TAXAMT']) ? $response['TAXAMT'] : '';
          $orderInfo['shipping'] = $_SESSION['Cart66Cart']->getShippingCost();
          $orderInfo['subtotal'] = $_SESSION['Cart66Cart']->getSubTotal();
          
          $taxAmt = isset($response['TAXAMT']) ? $response['TAXAMT'] : '';
          $orderInfo['total'] = number_format($_SESSION['Cart66Cart']->getGrandTotal() + $taxAmt, 2, '.', '');
          
          $orderInfo['non_subscription_total'] = number_format($_SESSION['Cart66Cart']->getNonSubscriptionAmount(), 2, '.', '');
          $orderInfo['trans_id'] = $response['TRANSACTIONID'];
          $orderInfo['status'] = $status;
          $orderInfo['ordered_on'] = date('Y-m-d H:i:s');
          $orderInfo['shipping_method'] = $_SESSION['Cart66Cart']->getShippingMethodName();

          if($account) {
            $orderInfo['account_id'] = $account->id;
          }
          else {
            $orderInfo['account_id'] = 0;
          }

          $orderId = $_SESSION['Cart66Cart']->storeOrder($orderInfo);  

          $receiptPage = get_page_by_path('store/receipt');
          $_SESSION['order_id'] = $orderId;
          header("Location: " . get_permalink($receiptPage->ID));
        } 
        else {
          $paymentProfileError = $profileResponse['L_SHORTMESSAGE0'] . ': ' . $profileResponse['L_LONGMESSAGE0'];
          echo "<p class='Cart66Error'>$paymentProfileError</p>";
        }
      }
      else {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] ");
        echo "<pre>";
        echo "Amount: $amount --- Tax: $tax\n";
        print_r($response);
        echo "</pre>";
      }
    }
    
  } // End if doexpresscheckout
}
elseif(isset($_GET['token']) || isset($_GET['PayerID'])) {
  $token = Cart66Common::getVal('token');
  $payerId = Cart66Common::getVal('PayerID');
  $details = $pp->GetExpressCheckoutDetails($token);
  $state = $details['SHIPTOSTATE'];
  
  // Calculate tax
  $tax = 0;
  $taxRate = new Cart66TaxRate();
  
  $isTaxed = $taxRate->loadByZip($details['SHIPTOZIP']);
  if($isTaxed == false) {
    $isTaxed = $taxRate->loadByState($state);
  }
  
  if($isTaxed) {
    $taxable = $_SESSION['Cart66Cart']->getTaxableAmount();
    if($taxRate->tax_shipping == 1) {
      $taxable += $_SESSION['Cart66Cart']->getShippingCost();
    }
    $tax = number_format($taxable * ($taxRate->rate/100), 2, '.', '');
  }
}
?>

<?php echo do_shortcode('[cart mode="read" tax="'. $tax .'"]'); ?>

<?php if(isset($details['EMAIL'])): ?>
  <table id="Cart66ExpressReview" border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td valign="top">
        <p>
          <strong>Billing Information</strong><br/>
          <?php echo $details['FIRSTNAME'] ?> <?php echo $details['LASTNAME'] ?><br/>
          <?php echo "PayPal Status: " . $details['PAYERSTATUS'] ?><br/>
          <?php if(isset($details['PHONENUM'])): ?>
            Phone: <?php echo $details['PHONENUM'] ?><br/>
          <?php endif; ?>
          Email: <?php echo $details['EMAIL'] ?>
        </p>
      </td>
      <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
      <td>
        <?php if($delivery != "Download"): ?>
          <p>
            <strong>Shipping Information</strong><br/>
          <?php echo $details['SHIPTONAME'] ?><br/>
          <?php echo $details['SHIPTOSTREET'] ?><br/>
    
          <?php if(!empty($details['SHIPTOSTREET2'])): ?>
            <?php echo $details['SHIPTOSTREET2'] ?><br/>
          <?php endif; ?>
    
          <?php echo $details['SHIPTOCITY'] ?> <?php echo $details['SHIPTOSTATE'] ?>, <?php echo $details['SHIPTOZIP'] ?>
    
          <?php if(!empty($details['SHIPTOCOUNTRYCODE'])): ?>
            <?php echo $details['SHIPTOCOUNTRYCODE'] ?>
          <?php endif; ?>
          </p>
        <?php else: ?>
          &nbsp;
        <?php endif; ?>
      </td>
    </tr>
  </table>
    
  <?php 
  if(isset($errors) && count($errors) > 0) {
    echo Cart66Common::showErrors($errors, 'Unable to create account');
    echo Cart66Common::getJqErrorScript($account->getJqErrors());
  }
  ?>

  <form class="phorm2" action="" method='post' style="<?php if(isset($data['completestyle'])) { echo $data['completestyle']; } ?>">
    <input type="hidden" name="task" value="doexpresscheckout">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <input type="hidden" name="PayerID" value="<?php echo $payerId; ?>">
    <input type="hidden" name="CURRENCYCODE" value="<?php echo CURRENCY_CODE ?>">
    <input type="hidden" name="tax" value="<?php echo $tax; ?>">
    
    <?php 
      if($_SESSION['Cart66Cart']->hasSubscriptionProducts() && Cart66Common::isLoggedIn()) {
        echo "<p>Your current subscription: $mySub->subscriptionPlanName<br/> $mySub->subscriptionPlanName will be canceled when your new subscription is activated.</p>";
      } 
    ?>
    
    <?php if($lists = Cart66Setting::getValue('constantcontact_list_ids')): ?>
        <?php
          if(!$optInMessage = Cart66Setting::getValue('opt_in_message')) {
            $optInMessage = 'Yes, I would like to subscribe to:';
          }
          echo "<p>$optInMessage</p>";
        
          $lists = explode('~', $lists);
          echo '<ul id="Cart66NewsletterList">';
          foreach($lists as $list) {
            list($id, $name) = explode('::', $list);
            echo "<li><input class=\"Cart66CheckboxList\" type=\"checkbox\" name=\"constantcontact_subscribe_ids[]\" value=\"$id\" /> $name</li>";
          }
          echo '<li><label style="width: auto;">Email:</label><input type="text" name="constantcontact_email" value="' . $details['EMAIL'] . '" /></li>';
          echo '</ul>';
          
          echo '<input type="hidden" name="constantcontact_first_name" value="' . $details['FIRSTNAME'] . '" />';
          echo '<input type="hidden" name="constantcontact_last_name"  value="' . $details['LASTNAME'] . '" />';
        ?>
    <?php endif; ?>
      

    <?php if($_SESSION['Cart66Cart']->hasSubscriptionProducts()): ?>
    <ul>    
      <li><h3>Create Your Account</h3></li>
      <li>
        <label for="account-first_name">First name:</label><input type="text" name="account[first_name]" value="<?php echo $account->firstName ?>" id="account-first_name">
      </li>
      <li>
        <label for="account-last_name">Last name:</label><input type="text" name="account[last_name]" value="<?php echo $account->lastName ?>" id="account-last_name">
      </li>
      <li>
        <label for="account-email">Email:</label><input type="text" name="account[email]" value="<?php echo $account->email ?>" id="account-email">
      </li>
      <li>
        <label for="account-username">Username:</label><input type="text" name="account[username]" value="<?php echo $account->username ?>" id="account-username">
      </li>
      <li>
        <label for="account-password">Password:</label><input type="password" name="account[password]" value="" id="account-password">
      </li>
      <li>
        <label for="account-password2">Repeat Password:</label><input type="password" name="account[password2]" value="" id="account-password2">
      </li>
      <li>
        <label>&nbsp;</label>
        <?php
          $cartImgPath = Cart66Setting::getValue('cart_images_url');
          if($cartImgPath) {
            if(strpos(strrev($cartImgPath), '/') !== 0) {
              $cartImgPath .= '/';
            }
            $completeImgPath = $cartImgPath . 'complete-order.png';
            echo "<input type='image' style='width:auto; height:auto; padding: 10px 0px 10px 0px;' src='$completeImgPath' value='Complete Order' />";
          }
          else {
            echo "<input type='submit' class='Cart66ButtonPrimary' style='' value='Complete Order' />";
          }
        ?>
        <p id="Cart66ReceiptExpectation">Your receipt will be on the next page and also emailed to you.</p>
      </li>
    </ul>
  <?php else: ?>
    <?php
      $cartImgPath = Cart66Setting::getValue('cart_images_url');
      if($cartImgPath) {
        if(strpos(strrev($cartImgPath), '/') !== 0) {
          $cartImgPath .= '/';
        }
        $completeImgPath = $cartImgPath . 'complete-order.png';
        echo "<input type='image' style='width:auto; height:auto; padding: 10px 0px 10px 0px;' src='$completeImgPath' value='Complete Order' />";
      }
      else {
        echo "<input type='submit' class='Cart66ButtonPrimary' style='' value='Complete Order' />";
      }
    ?>
    <p id="Cart66ReceiptExpectation">Your receipt will be on the next page and also emailed to you.</p>
  <?php endif; ?>
    
    
  </form>
<?php endif; ?>