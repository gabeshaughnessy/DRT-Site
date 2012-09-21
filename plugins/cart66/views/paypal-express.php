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
if(Cart66Session::get('Cart66Cart')->hasSubscriptionProducts() || Cart66Session::get('Cart66Cart')->hasMembershipProducts() ) {
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


$delivery = Cart66Session::get('Cart66Cart')->getShippingMethodName();
$tax = 0;

if($_SERVER['REQUEST_METHOD'] == "POST") {
  
  if($_POST['task'] == 'doexpresscheckout') {
    $createAccount = false;
    $keepGoing = true; // Change to false if a critical step in the checkout fails.
    $token = Cart66Common::postVal('token');
    $payerId = Cart66Common::postVal('PayerID');
    $promotion = Cart66Session::get('Cart66Promotion');
    $discount = Cart66Session::get('Cart66Cart')->getDiscountAmount();
    $itemTotal = Cart66Session::get('Cart66Cart')->getNonSubscriptionAmount();
    $shipping = Cart66Session::get('Cart66Cart')->getShippingCost();
    if(is_object($promotion) && $promotion->apply_to == 'total') {
      
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
    if(isset($_POST['tax']) && $_POST['tax'] > 0) {
      $tax = Cart66Common::postVal('tax');
    }
    
    
    // Create a new account if the account is not already saved
    if($account !== false && $account->id < 1) {
      $errors = $account->validate();
      if($acctData['password'] != $acctData['password2']) {
        $errors[] = __("Passwords do not match","cart66");
      }
      if(count($errors) == 0) {
        $createAccount = true;
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Noted that a new account is needed for $account->firstName $account->lastName $account->email");
      }
      else {
        $keepGoing = false;
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account validation failed: " . print_r($errors, true));
      }
    }
    
    
    // DoExpressCheckout for non-subscription products
    if($keepGoing) {
      
      // Look for constant contact opt-in
      if(CART66_PRO) { include(CART66_PATH . "/pro/Cart66ConstantContactOptIn.php"); }
      if(CART66_PRO) { include(CART66_PATH . "/pro/Cart66MailChimpOptIn.php"); }
      
      if($itemTotal > 0 || $shipping > 0) {
        // Send shipping as the item amount if the item amount is $0.00 otherwise paypal will refuse the transaction
        if($itemTotal == 0 && $shipping > 0) {
          $itemTotal = $shipping;
          $shipping = 0;
        }
        
        $pp->populatePayPalCartItems();
        
        Cart66Common::log("Preparing DoExpressCheckout:\nToken: $token\nPayerID: $payerId\nItem Amount: $itemTotal\nShipping: $shipping\nTax: $tax");
        $response = $pp->DoExpressCheckout($token, $payerId, $itemTotal, $shipping, $tax);
      }
      else {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Bypassing DoExpressCheckout because item amount is not greater than zero: $itemTotal");
        $response['ACK'] = 'SUCCESS'; // Forcing success since DoExpressCheckout wasn't called
      }
      $ack = strtoupper($response['ACK']);


      if('SUCCESS' == $ack || 'SUCCESSWITHWARNING' == $ack) {

        // Wait to make sure the transaction is a success before creating the account
        if($createAccount) {
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Creating account after successful PayPal transaction");
          $account->save();
        }
        
        // Create Recurring Payment Profile if a subscription has been sold
        $profileResponse = array('ACK' => 'SKIPPED');
        if($cartItem = Cart66Session::get('Cart66Cart')->getPayPalSubscriptionItem()) {
          $planIndex = Cart66Session::get('Cart66Cart')->getPayPalSubscriptionIndex();
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
        elseif($cartItem = Cart66Session::get('Cart66Cart')->getMembershipProductItem()) {
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Got membership product from the cart after a PayPal transaction.");
          $product = new Cart66Product($cartItem->getProductId());
          $account->attachMembershipProduct($product, $details['FIRSTNAME'], $details['LASTNAME']);
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
          $promo = Cart66Session::get('Cart66PromotionCode');
          $promoMsg = "none";
          if($promo) {
            $promoMsg = $promo . ' (-' . CART66_CURRENCY_SYMBOL . number_format(Cart66Session::get('Cart66Promotion')->getDiscountAmount(Cart66Session::get('Cart66Cart')), 2) . ')';
          }
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Details:\n" . print_r($details,true));

          list($shipFirstName, $shipLastName) = split(' ', $details['SHIPTONAME'], 2);
          $orderInfo['ship_first_name'] = $shipFirstName;
          $orderInfo['ship_last_name'] = $shipLastName;
          $orderInfo['ship_address'] = $details['SHIPTOSTREET'];
          $orderInfo['ship_address2'] = isset($details['SHIPTOSTREET2']) ? $details['SHIPTOSTREET2'] : '';
          $orderInfo['ship_city'] = $details['SHIPTOCITY'];
          $orderInfo['ship_state'] = $details['SHIPTOSTATE'];
          $orderInfo['ship_zip'] = $details['SHIPTOZIP'];
          $orderInfo['ship_country'] = $details['SHIPTOCOUNTRYNAME'];

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
          $orderInfo['shipping'] = Cart66Session::get('Cart66Cart')->getShippingCost();
          $orderInfo['subtotal'] = Cart66Session::get('Cart66Cart')->getSubTotal();
          
          $taxAmt = isset($response['TAXAMT']) ? $response['TAXAMT'] : '';
          $orderInfo['total'] = number_format(Cart66Session::get('Cart66Cart')->getGrandTotal() + $taxAmt, 2, '.', '');
          
          $orderInfo['non_subscription_total'] = number_format(Cart66Session::get('Cart66Cart')->getNonSubscriptionAmount(), 2, '.', '');
          $orderInfo['trans_id'] = $response['TRANSACTIONID'];
          $orderInfo['status'] = $status;
          $orderInfo['ordered_on'] = date('Y-m-d H:i:s', Cart66Common::localTs());
          $orderInfo['shipping_method'] = Cart66Session::get('Cart66Cart')->getShippingMethodName();

          if($account) {
            $orderInfo['account_id'] = $account->id;
          }
          else {
            $orderInfo['account_id'] = 0;
          }

          $orderId = Cart66Session::get('Cart66Cart')->storeOrder($orderInfo);  
          Cart66Session::set('order_id', $orderId);
          $receiptLink = Cart66Common::getPageLink('store/receipt');
          $newOrder = new Cart66Order($orderId);
          
          // Send email receipts
          if(CART66_PRO && Cart66Setting::getValue('enable_advanced_notifications') ==1) {
            $notify = new Cart66AdvancedNotifications($orderId);
            $notify->sendAdvancedEmailReceipts();
          }
          else {
            $notify = new Cart66Notifications($orderId);
            $notify->sendEmailReceipts();
          }
          
          // Send buyer to receipt page
          $receiptVars = strpos($receiptLink, '?') ? '&' : '?';
          $receiptVars .= "ouid=" . $newOrder->ouid;
          wp_redirect($receiptLink . $receiptVars);
          exit;
        } 
        else {
          $paymentProfileError = $profileResponse['L_SHORTMESSAGE0'] . ': ' . $profileResponse['L_LONGMESSAGE0'];
          echo "<p class='Cart66Error'>$paymentProfileError</p>";
        }
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
    $taxable = Cart66Session::get('Cart66Cart')->getTaxableAmount();
    if($taxRate->tax_shipping == 1) {
      $taxable += Cart66Session::get('Cart66Cart')->getShippingCost();
    }
    $tax = number_format($taxable * ($taxRate->rate/100), 2, '.', '');
  }
}
?>

<?php echo do_shortcode('[cart mode="read" tax="'. $tax .'" rate="'. $taxRate->rate .'"]'); ?>

<?php if(isset($details['EMAIL'])): ?>
  <div id="Cart66ExpressReview">
	<div id="billingInfo">
        <ul id="billingAddress">
          <li class="title"><strong>Billing Information</strong></li>
          <li><?php echo $details['FIRSTNAME'] ?> <?php echo $details['LASTNAME'] ?></li>
          <li><?php echo "PayPal Status: " . $details['PAYERSTATUS'] ?></li>
          <?php if(isset($details['PHONENUM'])): ?>
          <li>Phone: <?php echo $details['PHONENUM'] ?></li>
          <?php endif; ?>
          <li>Email: <?php echo $details['EMAIL'] ?></li>
        </ul>
	</div><!-- #billingInfo -->
        <?php if($delivery != "Download"): ?>
	<div id="shippingInfo">
		<ul>
          <li class="title"><strong>Shipping Information</strong></li>
          <li><?php echo $details['SHIPTONAME'] ?></li>
          <li><?php echo $details['SHIPTOSTREET'] ?></li>
    
          <?php if(!empty($details['SHIPTOSTREET2'])): ?>
            <li><?php echo $details['SHIPTOSTREET2'] ?></li>
          <?php endif; ?>
    
          <li><?php echo $details['SHIPTOCITY'] ?> <?php echo $details['SHIPTOSTATE'] ?>, <?php echo $details['SHIPTOZIP'] ?></li>
    
          <?php if(!empty($details['SHIPTOCOUNTRYCODE'])): ?>
            <li><?php echo $details['SHIPTOCOUNTRYCODE'] ?></li>
          <?php endif; ?>
		</ul>
		</div><!-- #shippingInfo -->
        <?php else: ?>
        <?php endif; ?>
</div>    
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
      if(Cart66Common::isLoggedIn()) {
        $name = $account->firstName . '&nbsp;' . $account->lastName;
        $logout = Cart66Common::appendQueryString('cart66-task=logout');
        echo "<p id='Cart66PayPalExpressLoggedIn'><strong>You Are Logged In As $name</strong><br/>If you are not $name <a href='$logout'>Log out</a></p>";
        
        if(Cart66Session::get('Cart66Cart')->hasSubscriptionProducts()) {
          if($mySub = $account->getCurrentAccountSubscription()) {
            echo "<p id='Cart66PayPalExpressCurrentSubscription'>Your current subscription: $mySub->subscriptionPlanName<br/> $mySub->subscriptionPlanName will be canceled when your new subscription is activated.</p>";
          }
        }
      }
    ?>
    
    <?php if($lists = Cart66Setting::getValue('constantcontact_list_ids')): ?>
        <?php
          if(!$optInMessage = Cart66Setting::getValue('opt_in_message')) {
            $optInMessage = 'Yes, I would like to subscribe to:';
          }
          echo "<p id='Cart66OptInMessage'>$optInMessage</p>";
        
          $lists = explode('~', $lists);
          echo '<ul class="Cart66NewsletterList">';
          foreach($lists as $list) {
            list($id, $name) = explode('::', $list);
            echo "<li><input class=\"Cart66CheckboxList\" type=\"checkbox\" name=\"constantcontact_subscribe_ids[]\" value=\"$id\" /> $name</li>";
          }
          echo '<li><label for="constantcontact_email">Email:</label><input type="text" id="constantcontact_email" name="constantcontact_email" value="' . $details['EMAIL'] . '" /></li>';
          echo '</ul>';
          
          echo '<input type="hidden" name="constantcontact_first_name" value="' . $details['FIRSTNAME'] . '" />';
          echo '<input type="hidden" name="constantcontact_last_name"  value="' . $details['LASTNAME'] . '" />';
        ?>
    <?php endif; ?>
    
    <?php if($lists = Cart66Setting::getValue('mailchimp_list_ids')): ?>
      <li>
        <?php
          if(!$optInMessage = Cart66Setting::getValue('mailchimp_opt_in_message')) {
            $optInMessage = 'Yes, I would like to subscribe to:';
          }
          echo "<p>$optInMessage</p>";
          $lists = explode('~', $lists);
          echo '<ul class="Cart66NewsletterList MailChimpList">';
          foreach($lists as $list) {
            list($id, $name) = explode('::', $list);
            echo "<li><input class=\"Cart66CheckboxList\" type=\"checkbox\" name=\"mailchimp_subscribe_ids[]\" value=\"$id\" /> $name</li>";
          }
          
          echo '<li><label for="mailchimp_email">Email:</label><input type="text" id="mailchimp_email" name="mailchimp_email" value="' . $details['EMAIL'] . '" /></li>';
          echo '</ul>';
          
          echo '<input type="hidden" name="mailchimp_first_name" value="' . $details['FIRSTNAME'] . '" />';
          echo '<input type="hidden" name="mailchimp_last_name"  value="' . $details['LASTNAME'] . '" />';
          echo '</ul>';
          
        
        
          if(isset($_POST['mailchimp_subscribe_ids']) && !empty($_POST['mailchimp_subscribe_ids'])){
              ?>
              <script type="text/javascript" charset="utf-8">
                (function($){
                  $(document).ready(function(){
                    <?php
                    foreach($_POST['mailchimp_subscribe_ids'] as $id) {
                      ?>
                      $(".MailChimpList input[value=<?php echo $id; ?>]").attr('checked','true');
                    <?php 
                    }

                    ?>
                  })
                })(jQuery);
              </script> 
        <?php
          }
        ?>
      </li>
    <?php endif; ?>
      

    <?php if($account !== false && $account->id < 1 && (Cart66Session::get('Cart66Cart')->hasSubscriptionProducts() || Cart66Session::get('Cart66Cart')->hasMembershipProducts()) ): ?>
	<div id="createAccountDiv">
		<h3><?php _e( 'Create Your Account' , 'cart66' ); ?></h3>
	</div>
    <ul>    
      <li>
        <label for="account-first_name"><?php _e( 'First name' , 'cart66' ); ?>:</label><input type="text" name="account[first_name]" value="<?php echo $account->firstName ?>" id="account-first_name">
      </li>
      <li>
        <label for="account-last_name"><?php _e( 'Last name' , 'cart66' ); ?>:</label><input type="text" name="account[last_name]" value="<?php echo $account->lastName ?>" id="account-last_name">
      </li>
      <li>
        <label for="account-email"><?php _e( 'Email' , 'cart66' ); ?>:</label><input type="text" name="account[email]" value="<?php echo $account->email ?>" id="account-email">
      </li>
      <li>
        <label for="account-username"><?php _e( 'Username' , 'cart66' ); ?>:</label><input type="text" name="account[username]" value="<?php echo $account->username ?>" id="account-username">
      </li>
      <li>
        <label for="account-password"><?php _e( 'Password' , 'cart66' ); ?>:</label><input type="password" name="account[password]" value="" id="account-password">
      </li>
      <li>
        <label for="account-password2"><?php _e( 'Repeat Password' , 'cart66' ); ?>:</label><input type="password" name="account[password2]" value="" id="account-password2">
      </li>
      <li>
        <label class="Cart66Hidden"><?php _e( 'Complete Order' , 'cart66' ); ?></label>
        <?php
          $cartImgPath = Cart66Setting::getValue('cart_images_url');
          if($cartImgPath) {
            if(strpos(strrev($cartImgPath), '/') !== 0) {
              $cartImgPath .= '/';
            }
            $completeImgPath = $cartImgPath . 'complete-order.png';
            echo "<input type='image' src='$completeImgPath' value='Complete Order' />";
          }
          else {
            echo "<input type='submit' class='Cart66ButtonPrimary' value='Complete Order' />";
          }
        ?>
      </li>
    </ul>
    <p id="Cart66ReceiptExpectation"><?php _e( 'Your receipt will be on the next page and also emailed to you.' , 'cart66' ); ?></p>
  <?php else: ?>
    <?php 
      $cartImgPath = Cart66Setting::getValue('cart_images_url');
      if($cartImgPath) {
        if(strpos(strrev($cartImgPath), '/') !== 0) {
          $cartImgPath .= '/';
        }
        $completeImgPath = $cartImgPath . 'complete-order.png';
        echo "<input type='image' src='$completeImgPath' value='Complete Order' />";
      }
      else {
        echo "<input type='submit' class='Cart66ButtonPrimary Cart66CompleteOrderButton' value='Complete Order' />";
      }
    ?>
    <p id="Cart66ReceiptExpectation"><?php _e( 'Your receipt will be on the next page and also emailed to you.' , 'cart66' ); ?></p>
  <?php endif; ?>
    
    
  </form>
<?php endif; ?>