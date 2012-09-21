<?php

class Cart66ShortcodeManager {
  
  public $manualIsOn;
  
  /**
   * Short code for displaying shopping cart including the number of items in the cart and links to view cart and checkout
   */
  public function shoppingCart($attrs) {
    $cartPage = get_page_by_path('store/cart');
    $checkoutPage = get_page_by_path('store/checkout');
    $cart = Cart66Session::get('Cart66Cart');
    if(is_object($cart) && $cart->countItems()) {
      ?>
      <div id="Cart66scCartContents">
        <a id="Cart66scCartLink" href='<?php echo get_permalink($cartPage->ID) ?>'>
        <span id="Cart66scCartCount"><?php echo $cart->countItems(); ?></span>
        <span id="Cart66scCartCountText"><?php echo $cart->countItems() > 1 ? ' items' : ' item' ?></span> 
        <span id="Cart66scCartCountDash">&ndash;</span>
        <span id="Cart66scCartPrice"><?php echo CART66_CURRENCY_SYMBOL . 
          number_format($cart->getSubTotal(), 2); ?>
        </span></a>
        <a id="Cart66scViewCart" href='<?php echo get_permalink($cartPage->ID) ?>'>View Cart</a>
        <span id="Cart66scLinkSeparator"> | </span>
        <a id="Cart66scCheckout" href='<?php echo get_permalink($checkoutPage->ID) ?>'>Check out</a>
      </div>
      <?php
    }
    else {
      $emptyMessage = isset($attrs['empty_msg']) ? $attrs['empty_msg'] : 'Your cart is empty';
      echo "<p id=\"Cart66scEmptyMessage\">$emptyMessage</p>";
    }
  }

  public static function showCartButton($attrs, $content) {
    $product = new Cart66Product();
    $product->loadFromShortcode($attrs);
    return Cart66ButtonManager::getCartButton($product, $attrs, $content);
  }
  
  public static function showCartAnchor($attrs, $content) {
    $product = new Cart66Product();
    $product->loadFromShortcode($attrs);
    $options = isset($attrs['options']) ? $attrs['options'] : '';
    $urlOptions = isset($attrs['options']) ? '&amp;options=' . urlencode($options) : '';
    
    $content = do_shortcode($content);
    
    $iCount = true;
    $iKey = $product->getInventoryKey($options);
    if($product->isInventoryTracked($iKey)) {
      $iCount = $product->getInventoryCount($iKey);
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] iCount: $iCount === iKey: $iKey");
    }
    else {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not tracking inventory for: $iKey");
    }
    
    if($iCount) {
      $id = $product->id;
      $class = isset($attrs['class']) ? $attrs['class'] : '';
      $cartPage = get_page_by_path('store/cart');
      $cartLink = get_permalink($cartPage->ID);
      $joinChar = (strpos($cartLink, '?') === FALSE) ? '?' : '&';
      

      $data = array(
        'url' => $cartLink . $joinChar . "task=add-to-cart-anchor&amp;cart66ItemId=${id}${urlOptions}",
        'text' => $content,
        'class' => $class
      );

      $view = Cart66Common::getView('views/cart-button-anchor.php', $data);
    }
    else {
      $view = $content;
    }
    
    return $view;
  }

  public function showCart($attrs, $content) {
    if(isset($_REQUEST['cart66-task']) && $_REQUEST['cart66-task'] == 'remove-attached-form') {
      $entryId = $_REQUEST['entry'];
      if(is_numeric($entryId)) {
        Cart66Session::get('Cart66Cart')->detachFormEntry($entryId);
      }
    }
    $view = Cart66Common::getView('views/cart.php', $attrs);
    return $view;
  }

  public function showReceipt($attrs) {
    $account = null;
    
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ouid'])) {
      if(CART66_PRO && isset($_POST['account'])) {
        $acctData = Cart66Common::postVal('account');
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] New Account Data: " . print_r($acctData, true));
        $account = new Cart66Account();
        $account->firstName = $acctData['first_name'];
        $account->lastName = $acctData['last_name'];
        $account->email = $acctData['email'];
        $account->username = $acctData['username'];
        $account->password = md5($acctData['password']);
        $errors = $account->validate();
        $jqErrors = $account->getJqErrors();
        
        if($acctData['password'] != $acctData['password2']) {
          $errors[] = __("Passwords do not match","cart66");
          $jqErrors[] = 'account-password';
          $jqErrors[] = 'account-password2';
        }
        
        if(count($errors) == 0) { 
          // Attach account to order
          $order = new Cart66Order();
          $ouid = Cart66Common::postVal('ouid');
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Trying to load order with OUID: $ouid");
          if($order->loadByOuid($ouid)) {
            
            // Make sure the order can be loaded, then save the account
            $account->save();
            
            // Attach membership to account and account to the order
            if($mp = $order->getMembershipProduct()) {
              $account->attachMembershipProduct($mp, $account->firstName, $account->lastName);
              $order->account_id = $account->id;
              $order->save();
              $account->clear();
            }
          }
          
        }
        else {
          $attrs['errors'] = $errors;
          $attrs['jqErrors'] = $jqErrors;
        }
      }
    }
    
    $attrs['account'] = $account;
    $view = Cart66Common::getView('views/receipt.php', $attrs);
    return $view;
  }

  public function paypalCheckout($attrs) {
    if(Cart66Session::get('Cart66Cart')->countItems() > 0) {
      if(!Cart66Session::get('Cart66Cart')->hasSubscriptionProducts() && !Cart66Session::get('Cart66Cart')->hasMembershipProducts()) {
        if(Cart66Session::get('Cart66Cart')->getGrandTotal()) {
          try {
            $view = Cart66Common::getView('views/paypal-checkout.php', $attrs);
          }
          catch(Cart66Exception $e) {
            $exception = Cart66Exception::exceptionMessages($e->getCode(), $e->getMessage());
            $view = Cart66Common::getView('views/error-messages.php', $exception);
          }
          return $view;
        }
        else {
          return $this->manualCheckout();
        }
      }
    }
  }

  public function manualCheckout($attrs=null) {
    
    if($this->manualIsOn=="active"){
      return;
    }
    
    if(Cart66Session::get('Cart66Cart')->countItems() > 0) {
      $gatewayName = Cart66Common::postVal('cart66-gateway-name');
      if($_SERVER['REQUEST_METHOD'] == 'POST' && $gatewayName != 'Cart66ManualGateway') {
        return;
      }
      
      if(!Cart66Session::get('Cart66Cart')->hasSubscriptionProducts()) {
        require_once(CART66_PATH . "/gateways/Cart66ManualGateway.php");
        $manual = new Cart66ManualGateway();
        $view = $this->_buildCheckoutView($manual);
        $this->manualIsOn = "active";
      }
      else {
        $view = "<p>Unable to sell subscriptions using the manual checkout gateway.</p>";
      }
      
      return $view;
    }
  }

  public function authCheckout($attrs) {
    if(Cart66Session::get('Cart66Cart')->countItems() > 0) {
      $gatewayName = Cart66Common::postVal('cart66-gateway-name');
      if($_SERVER['REQUEST_METHOD'] == 'POST' && $gatewayName != 'Cart66AuthorizeNet') {
        return ($gatewayName == "Cart66ManualGateway") ? $this->manualCheckout() : "";
      }

      if(!Cart66Session::get('Cart66Cart')->hasPayPalSubscriptions()) {
        require_once(CART66_PATH . "/pro/gateways/Cart66AuthorizeNet.php");

        if(Cart66Session::get('Cart66Cart')->getGrandTotal() > 0 || Cart66Session::get('Cart66Cart')->hasSpreedlySubscriptions()) {
          try {
            $authnet = new Cart66AuthorizeNet();
            $view = $this->_buildCheckoutView($authnet);
          }
          catch(Cart66Exception $e) {
            $exception = Cart66Exception::exceptionMessages($e->getCode(), $e->getMessage());
            $view = Cart66Common::getView('views/error-messages.php', $exception);
          }
          return $view;
        }
        elseif(Cart66Session::get('Cart66Cart')->countItems() > 0) {
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of Authorize.net Checkout because the cart value is $0.00");
          return $this->manualCheckout();
        }

      }
      else {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering Authorize.net checkout form because the cart contains a PayPal subscription");
      }
    }
  }
  
  public function payLeapCheckout($attrs) {
    if(Cart66Session::get('Cart66Cart')->countItems() > 0) {
        $gatewayName = Cart66Common::postVal('cart66-gateway-name');  
        if($_SERVER['REQUEST_METHOD'] == 'POST' && $gatewayName != 'Cart66PayLeap') {
          return ($gatewayName == "Cart66ManualGateway") ? $this->manualCheckout() : "";
        }

        if(!Cart66Session::get('Cart66Cart')->hasPayPalSubscriptions()) {
          require_once(CART66_PATH . "/pro/gateways/Cart66PayLeap.php");

          if(Cart66Session::get('Cart66Cart')->getGrandTotal() > 0 || Cart66Session::get('Cart66Cart')->hasSpreedlySubscriptions()) {
            try {
              $payleap = new Cart66PayLeap();
              $view = $this->_buildCheckoutView($payleap);
            }
            catch(Cart66Exception $e) {
              $exception = Cart66Exception::exceptionMessages($e->getCode(), $e->getMessage());
              $view = Cart66Common::getView('views/error-messages.php', $exception);
            }
            return $view;
          }
          elseif(Cart66Session::get('Cart66Cart')->countItems() > 0) {
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of PayLeap Checkout because the cart value is $0.00");
            return $this->manualCheckout();
          }

        }
        else {
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering PayLeap checkout form because the cart contains a PayPal subscription");
        }
      }
  }
  
  public function ewayCheckout($attrs) {
     if(Cart66Session::get('Cart66Cart')->countItems() > 0) {
      $gatewayName = Cart66Common::postVal('cart66-gateway-name');
      if($_SERVER['REQUEST_METHOD'] == 'POST' && $gatewayName != 'Cart66Eway') {
        return ($gatewayName == "Cart66ManualGateway") ? $this->manualCheckout() : "";
      }

      if(!Cart66Session::get('Cart66Cart')->hasPayPalSubscriptions()) {
        require_once(CART66_PATH . "/pro/gateways/Cart66Eway.php");

        if(Cart66Session::get('Cart66Cart')->getGrandTotal() > 0 || Cart66Session::get('Cart66Cart')->hasSpreedlySubscriptions()) {
          try {
            $eway = new Cart66Eway();
            $view = $this->_buildCheckoutView($eway);
          }
          catch(Cart66Exception $e) {
            $exception = Cart66Exception::exceptionMessages($e->getCode(), $e->getMessage());
            $view = Cart66Common::getView('views/error-messages.php', $exception);
          }
          return $view;
        }
        elseif(Cart66Session::get('Cart66Cart')->countItems() > 0) {
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of Eway Checkout because the cart value is $0.00");
          return $this->manualCheckout();
        }

      }
      else {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering Eway checkout form because the cart contains a PayPal subscription");
      }
    }
  }  
  
  public function mwarriorCheckout($attrs) {
      if(Cart66Session::get('Cart66Cart')->countItems() > 0) {
        $gatewayName = Cart66Common::postVal('cart66-gateway-name');
        if($_SERVER['REQUEST_METHOD'] == 'POST' && $gatewayName != 'Cart66MerchantWarrior') {
          return ($gatewayName == "Cart66ManualGateway") ? $this->manualCheckout() : "";
        }

        if(!Cart66Session::get('Cart66Cart')->hasPayPalSubscriptions()) {
          require_once(CART66_PATH . "/pro/gateways/Cart66MWarrior.php");

          if(Cart66Session::get('Cart66Cart')->getGrandTotal() > 0 || Cart66Session::get('Cart66Cart')->hasSpreedlySubscriptions()) {
            try {
              $mwarrior = new Cart66MerchantWarrior();
              $view = $this->_buildCheckoutView($mwarrior);
            }
            catch(Cart66Exception $e) {
              $exception = Cart66Exception::exceptionMessages($e->getCode(), $e->getMessage());
              $view = Cart66Common::getView('views/error-messages.php', $exception);
            }
            return $view;
          }
          elseif(Cart66Session::get('Cart66Cart')->countItems() > 0) {
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of Merchant Warrior Checkout because the cart value is $0.00");
            return $this->manualCheckout();
          }

        }
        else {
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering Merchant Warrior checkout form because the cart contains a PayPal subscription");
        }
      }
  }
  
  public function stripeCheckout($attrs) {
    if(Cart66Session::get('Cart66Cart')->countItems() > 0) {
      $gatewayName = Cart66Common::postVal('cart66-gateway-name');
      if($_SERVER['REQUEST_METHOD'] == 'POST' && $gatewayName != 'Cart66Stripe') {
        return ($gatewayName == "Cart66ManualGateway") ? $this->manualCheckout() : "";
      }

      if(!Cart66Session::get('Cart66Cart')->hasPayPalSubscriptions()) {
        require_once(CART66_PATH . "/pro/gateways/Cart66Stripe.php");

        if(Cart66Session::get('Cart66Cart')->getGrandTotal() > 0 || Cart66Session::get('Cart66Cart')->hasSpreedlySubscriptions()) {
          try {
            $stripe = new Cart66Stripe();
            $view = $this->_buildCheckoutView($stripe);
          }
          catch(Cart66Exception $e) {
            $exception = Cart66Exception::exceptionMessages($e->getCode(), $e->getMessage());
            $view = Cart66Common::getView('views/error-messages.php', $exception);
          }
          return $view;
        }
        elseif(Cart66Session::get('Cart66Cart')->countItems() > 0) {
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of Stripe Checkout because the cart value is $0.00");
          return $this->manualCheckout();
        }

      }
      else {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering Stripe checkout form because the cart contains a PayPal subscription");
      }
    }
  }

  public function payPalProCheckout($attrs) {
    if(Cart66Session::get('Cart66Cart')->countItems() > 0) {
      $gatewayName = Cart66Common::postVal('cart66-gateway-name');
      if($_SERVER['REQUEST_METHOD'] == 'POST' && $gatewayName != 'Cart66PayPalPro') {
        return ($gatewayName == "Cart66ManualGateway") ? $this->manualCheckout() : "";
      }

      if(!Cart66Session::get('Cart66Cart')->hasPayPalSubscriptions()) {
        if(Cart66Session::get('Cart66Cart')->getGrandTotal() > 0 || Cart66Session::get('Cart66Cart')->hasSpreedlySubscriptions()) {
          try {
            $paypal = new Cart66PayPalPro();
            $view = $this->_buildCheckoutView($paypal);
          }
          catch(Cart66Exception $e) {
            $exception = Cart66Exception::exceptionMessages($e->getCode(), $e->getMessage());
            $view = Cart66Common::getView('views/error-messages.php', $exception);
          }
          return $view;
        }
        elseif(Cart66Session::get('Cart66Cart')->countItems() > 0) {
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of PayPal Pro Checkout because the cart value is $0.00");
          return $this->manualCheckout();
        }
      }
      else {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering PayPal Pro checkout form because the cart contains a PayPal subscription");
      }
    }
  }

  public function payPalExpressCheckout($attrs) {
    if(Cart66Session::get('Cart66Cart')->countItems() > 0) {
      $cart = Cart66Session::get('Cart66Cart');

      if($cart->hasSpreedlySubscriptions()) {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering PayPal Express checkout form because the cart contains Spreedly subscriptions");
        $errorMessage = "<p class='Cart66Error'>Spreedly subscriptions cannot be processed through PayPal Express Checkout</p>";
        return $errorMessage;
      }
      else {
        if($cart->getGrandTotal() > 0 || $cart->hasPayPalSubscriptions()) {
          try {
            $view = Cart66Common::getView('views/paypal-expresscheckout.php', $attrs);
          }
          catch(Cart66Exception $e) {
            $exception = Cart66Exception::exceptionMessages($e->getCode(), $e->getMessage());
            $view = Cart66Common::getView('views/error-messages.php', $exception);
          }
          return $view;
        }
        elseif($cart->countItems() > 0) {
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of PayPal Pro Express Checkout because the cart value is $0.00");
          return $this->manualCheckout();
        }
      }
    }
  }

  public function payPalExpress($attrs) {
    try {
      $view = Cart66Common::getView('views/paypal-express.php', $attrs);
    }
    catch(Cart66Exception $e) {
      $exception = Cart66Exception::exceptionMessages($e->getCode(), $e->getMessage());
      $view = Cart66Common::getView('views/error-messages.php', $exception);
    }
    return $view;
  }

  public function processIPN($attrs) {
    require_once(CART66_PATH . "/models/Cart66PayPalIpn.php");
    require_once(CART66_PATH . "/gateways/Cart66PayPalStandard.php");
    $ipn = new Cart66PayPalIpn();
    if($ipn->validate($_POST)) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Working with  IPN transaction type: " . $_POST['txn_type']);
      switch($_POST['txn_type']) { 
        case 'cart':              // Payment received for multiple items; source is Express Checkout or the PayPal Shopping Cart.
          $ipn->saveCartOrder($_POST);
          break;
        case 'recurring_payment':
          $ipn->logRecurringPayment($_POST);
          break;
        case 'recurring_payment_profile_cancel':
          $ipn->cancelSubscription($_POST);
          break;
        default:
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] IPN transaction type not implemented: " . $_POST['txn_type']);
      }
    }
    else {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] IPN verification failed");
    }
  }

  public function cart66Tests() {
    $view = Cart66Common::getView('views/tests.php');
    $view = "<pre>$view</pre>";
    return $view;
  }

  public function clearCart() {
    Cart66Session::drop('Cart66Cart');
    Cart66Session::drop('Cart66Promotion');
    Cart66Session::drop('terms_acceptance');
    Cart66Session::drop('Cart66ProRateAmount');
  }
  
  public function accountLogin($attrs) {
    $account = new Cart66Account();
    if($accountId = Cart66Common::isLoggedIn()) {
      $account->load($accountId);
    }
    
    $data = array('account' => $account);
    
    // Look for password reset task
    if(isset($_POST['cart66-task']) && $_POST['cart66-task'] == 'account-reset') {
      $data['resetResult'] = $account->passwordReset();
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Attempted to reset password: " . $data['resetResult']->message);
    }
    
    // Build the account login view
    $view = Cart66Common::getView('views/account-login.php', $data);
    
    if(isset($_POST['cart66-task']) && $_POST['cart66-task'] == 'account-login') {
      if($account->login($_POST['login']['username'], $_POST['login']['password'])) {
        Cart66Session::set('Cart66AccountId', $account->id);
        
        // Send logged in user to the appropriate page after logging in
        $url = Cart66Common::getCurrentPageUrl();
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account Login: " . print_r($attrs, true));
        if(isset($attrs['url']) && !empty($attrs['url'])) {
          if('stay' != strtolower($attrs['url'])) {
            $url = $attrs['url'];
          }
        }
        else {
          if(Cart66Session::get('Cart66AccessDeniedRedirect')) {
            $url = Cart66Session::get('Cart66AccessDeniedRedirect');
          }
          else {
            // Locate logged in user home page
            $pgs = get_posts('numberposts=1&post_type=any&meta_key=cart66_member&meta_value=home');
            if(count($pgs)) {
              $url = get_permalink($pgs[0]->ID);
            }
          }
        }
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Redirecting after login to: $url");
        Cart66Session::drop('Cart66AccessDeniedRedirect');
        wp_redirect($url);
        exit;
      }
      else {
        $view .= "<p class='Cart66Error'>Login failed</p>";
      }
    }
    
    return $view;
  }
  
  /**
   * Unset the Cart66AccountId from the session and redirect to $attr['url'] if the url attribute is provided.
   * If no redirect url is provided, look for the page with the custom field cart66_member=logout
   * If no custom field is set then redirect to the current page after logging out
   */
  public function accountLogout($attrs) {
    // Save zendesk error to session
    if(isset($_GET['kind']) && $_GET['kind'] == 'error' && isset($_GET['message'])){
      $zendeskError = $_GET['message'];
      Cart66Session::set('zendesk_logout_error',$zendeskError,true);
    }
    $url = Cart66Common::getCurrentPageUrl();
    if(isset($attrs['url']) && !empty($attrs['url'])) {
      $url = $attrs['url'];
    }
    else {
      $url = Cart66ProCommon::getLogoutUrl();
    }
    Cart66Account::logout($url);
  }
  
  public function accountLogoutLink($attrs) {
    $url = Cart66Common::replaceQueryString('cart66-task=logout');
    $linkText = isset($attrs['text']) ? $attrs['text'] : 'Log out';
    $link = "<a href='$url'>$linkText</a>";
    return $link;
  }
  
  /**
   * Return the Spreedly url to manage the subscription or the
   * PayPal url to cancel the subscription. 
   * If the visitor is not logged in, return false.
   * You can pass in text for the link and a custom return URL
   * 
   * $attr = array(
   *   text => 'The link text for the subscription management link'
   *   return => 'Customize the return url for the spreedly page'
   * )
   * 
   * @return string Spreedly subscription management URL
   */
  public function accountInfo($attrs) {
    if(Cart66Common::isLoggedIn()) {
      $data = array();
      $account = new Cart66Account(Cart66Session::get('Cart66AccountId'));
      if(isset($_POST['cart66-task']) && $_POST['cart66-task'] == 'account-update') {
        $login = $_POST['login'];
        if($login['password'] == $login['password2']) {
          $account->firstName = $login['first_name'];
          $account->lastName = $login['last_name'];
          $account->email = $login['email'];
          $account->password = empty($login['password']) ? $account->password : md5($login['password']);
          $account->username = $login['username'];
          $errors = $account->validate();
          if(count($errors) == 0) {
            $account->save();
            if($account->isSpreedlyAccount()) {
              SpreedlySubscriber::updateRemoteAccount($account->id, array('email' => $account->email));
            }
            $data['message'] = 'Your account is updated';
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account was updated: " . print_r($account->getData, true));
          }
          else {
            $data['errors'] = $account->getErrors();
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account validation failed: " . print_r($data['errors'], true));
          }
        }
        else {
          $data['errors'] = "Account not updated. The passwords entered did not match";
        }
      }
      
      $data['account'] = $account;
      $data['url'] = false;
      
      if($account->isSpreedlyAccount()) {
        $accountSub = $account->getCurrentAccountSubscription();
        $text = isset($attrs['text']) ? $attrs['text'] : 'Manage your subscription.';
        $returnUrl = isset($attrs['return']) ? $attrs['return'] : null;
        $url = $accountSub->getSubscriptionManagementLink($returnUrl);
        $data['url'] = $url;
        $data['text'] = $text;
      }
      
      $view = Cart66Common::getView('views/account-info.php', $data);
      return $view;
    }
    else {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Trying to view account subscription short code but account holder is not logged into Cart66.");
    }
  }
  
  public function accountDetails($attrs) {
    if(Cart66Common::isLoggedIn()) {
      $display = '';
      $account = new Cart66Account(Cart66Session::get('Cart66AccountId'));
      $text = isset($attrs['display']) ? $attrs['display'] : null;
      if(isset($attrs['display']) && $attrs['display'] != '' && isset($account->$attrs['display'])) {
        $display = $account->$attrs['display'];
      }
      return $display;
    }
    else {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Trying to view account details but account holder is not logged into Cart66.");
    }
  }
  
  public function cancelPayPalSubscription($attrs) {
    $link = '';
    if(Cart66Common::isLoggedIn()) {
      $account = new Cart66Account(Cart66Session::get('Cart66AccountId'));
      if($account->isPayPalAccount()) {
        
        // Look for account cancelation request
        if(isset($_GET['cart66-task']) && $_GET['cart66-task'] == 'CancelRecurringPaymentsProfile') {
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Caught task: CancelPaymentsProfileStatus");
          $sub = new Cart66AccountSubscription($account->getCurrentAccountSubscriptionId());
          $profileId = $sub->paypalBillingProfileId;
          $note = "Your subscription has been canceled per your request.";
          $action = "Cancel";
          $pp = new Cart66PayPalPro();
          $pp->ManageRecurringPaymentsProfileStatus($profileId, $action, $note);
          $url = str_replace('cart66-task=CancelRecurringPaymentsProfile', '', Cart66Common::getCurrentPageUrl());
          $link = "We sent a cancelation request to PayPal. It may take a minute or two for the cancelation process to complete and for your account status to be changed.";
        }
        elseif($subId = $account->getCurrentAccountSubscriptionId()) {
          $sub = new Cart66AccountSubscription($subId);
          if($sub->status == 'active') {
            $url = $sub->getSubscriptionManagementLink();
            $text = isset($attrs['text']) ? $attrs['text'] : 'Cancel your subscription';
            $link = "<a id='Cart66CancelPayPalSubscription' href=\"$url\">$text</a>";
          }
          else {
            $link = "Your account is $sub->status but will remain active until " . date('m/d/Y', strtotime($sub->activeUntil));
          }
        }
        
        
        
      }
    }
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Cancel paypal account link for logged in user: $link");
    
    return $link;
  }
  
  public function currentSubscriptionPlanName() {
    $name = 'You do not have an active subscription';
    if(Cart66Common::isLoggedIn()) {
      $account = new Cart66Account(Cart66Session::get('Cart66AccountId'));
      if($subId = $account->getCurrentAccountSubscriptionId()) {
        $sub = new Cart66AccountSubscription($subId);
        $name = $sub->subscriptionPlanName;
      }
    }
    return $name;
  }
  
  public function currentSubscriptionFeatureLevel() {
    $level = 'No access';
    if(Cart66Common::isLoggedIn()) {
      $account = new Cart66Account(Cart66Session::get('Cart66AccountId'));
      if($subId = $account->getCurrentAccountSubscriptionId()) {
        $sub = new Cart66AccountSubscription($subId);
        $level = $sub->featureLevel;
      }
    }
    return $level;
  }

  public function spreedlyListener() {
    if(isset($_POST['subscriber_ids'])) {
      $ids = explode(',', $_POST['subscriber_ids']);
      foreach($ids as $id) {
        try {
          $subscriber = SpreedlySubscriber::find($id);
          $subscriber->updateLocalAccount();
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Updated local account id: $id");
        }
        catch(SpreedlyException $e) {
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] I heard that subscriber $id was changed but I can't do anything about it. " . $e->getMessage());
        }
        
      }
    }
    else {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] This is not a valid call to the spreedly listener.");
    }
    
    ob_clean();
    header('HTTP/1.1 200 OK');
    die();
  }
  
  /**
   * Show content to qualifying feature levels.
   * 
   * The $attrs parameter should contain the key "level" which contains
   * a CSV list of feature levels which are allowed to see the enclosed content.
   * 
   * A special feature level of "all_members" may be provided to show the content to
   * any logged in member regardless of feature level. Note that expired accounts may log
   * in but will not have a feature level. Therefore providing "all_members" as the required
   * level will show the content to logged in members with expired accounts.
   * 
   * Because Cart66Common::trmmedExplode is used to parse the feature levels, the 
   * feature level list may include spaces. The following two lists are the same:
   *   one,two,three,four
   *   one, two, three, four
   */
  public function showTo($attrs, $content='null') {
    $isAllowed = false;
    if(Cart66Common::isLoggedIn()) {
      $levels = Cart66Common::trimmedExplode(',', $attrs['level']);
      $account = new Cart66Account();
      if($account->load(Cart66Session::get('Cart66AccountId'))) {
        if(in_array('all_members', $levels)) {
          $isAllowed = true;
        }
        elseif($account->isActive() && in_array($account->getFeatureLevel(), $levels)) {
          $isAllowed = true;
        }
      }
    }
    $content = $isAllowed ? $content : '';

    return do_shortcode($content);
  }
  
  /**
   * This is the inverse of the showTo shortcode function above.
   */
  public function hideFrom($attrs, $content='null') {
    $isAllowed = true;
    if(Cart66Common::isLoggedIn()) {
      $levels = Cart66Common::trimmedExplode(',', $attrs['level']);
      $account = new Cart66Account();
      if(in_array('all_members', $levels)) {
        $isAllowed = false;
      }
      elseif($account->load(Cart66Session::get('Cart66AccountId'))) {
        if($account->isActive() && in_array($account->getFeatureLevel(), $levels)) {
          $isAllowed = false;
        }
      }
    }
    $content = $isAllowed ? $content : '';

    return do_shortcode($content);
  }
  
  public function postSale($attrs, $content='null') {
    $postSale = false;
    if(isset($_GET['ouid'])) {
      $order = new Cart66Order();
      $order->loadByOuid($_GET['ouid']);
      if($order->viewed == 0) {
        $postSale = true;
      }
    }
    $content = $postSale ? $content : '';
    return do_shortcode($content);
  }
  
  public function gravityFormToCart($entry) {
    if(CART66_PRO) {
      $formId = Cart66GravityReader::getGravityFormIdForEntry($entry['id']);
      if($formId) {
        $productId = Cart66Product::getProductIdByGravityFormId($formId);
        if($productId > 0) {
          $product = new Cart66Product($productId);
          $qty = $product->gravityCheckForEntryQuantity($entry);
          $options = $product->gravityGetVariationPrices($entry);
          $productUrl = Cart66Common::getCurrentPageUrl();
          $cart = Cart66Session::get('Cart66Cart');
          $item = $cart->addItem($productId, $qty, $options, $entry['id'], $productUrl);
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Cart Item Value: " . print_r($item, true));
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Should we use the gravity forms price? " . $product->gravity_form_pricing . 
            ' :: Session value: ' . Cart66Session::get('userPrice_' . $product->id));
          
          if($product->gravity_form_pricing == 1) {
            $price = Cart66GravityReader::getPrice($entry['id']) / $qty;
            $entry_id = $item->getFirstFormEntryId();
            $user_price_name = 'userPrice_' . $productId . '_' . $entry_id;
            Cart66Session::set($user_price_name, $price, true); // Setting the price of a Gravity Forms pricing product
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Using gravity forms pricing for product: Price: $price :: Name: " . $product->name . 
              " :: Session variable name: $user_price_name");
          }
          
          $cartPage = get_page_by_path('store/cart');
          $cartPageLink = get_permalink($cartPage->ID);
          Cart66Session::set('Cart66LastPage', $_SERVER['HTTP_REFERER']);
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Cart66 Session Dump: " . Cart66Session::dump());
          
          $entry["status"] = 'unpaid';
          RGFormsModel::update_lead($entry);

          wp_redirect($cartPageLink);
          exit;
        }
      }
    }
  }
  
  public function zendeskRemoteLogin() {
    if(Cart66Common::isLoggedIn()) {
      $account = new Cart66Account(Cart66Session::get('Cart66AccountId'));
      if($account) {
        ZendeskRemoteAuth::login($account);
      }
    }
  }
  
  public function downloadFile($attrs) {
    $link = false;
    if(isset($attrs['path'])) {
      $path = urlencode($attrs['path']);
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] encoded $path");
      $nvp = 'task=member_download&path=' . $path;
      $url = Cart66Common::replaceQueryString($nvp);
      
      if(Cart66Common::isLoggedIn()) {
        $link = '<a class="Cart66DownloadFile" href="' . $url . '">' . $attrs['text'] . '</a>';
      }
      else {
        $link = $attrs['text'];
      }
    }
    return $link;
  }
  
  public function termsOfService($attrs) {
    if(CART66_PRO) {
      $attrs = array("location"=>"Cart66ShortcodeTOS");
      $view = Cart66Common::getView('/pro/views/terms.php', $attrs);
      return $view;
    }
  }
  
  public function accountExpiration($attrs, $content = null){
    $output = false;
    if(Cart66Common::isLoggedIn()) {
      $data = array();
      $account = new Cart66Account(Cart66Session::get('Cart66AccountId'));
      $subscription = $account->getCurrentAccountSubscription($account->id);
      $expirationDate = $subscription->active_until;
      $format = "m/d/Y";
      if(isset($attrs['format'])){
        $format = $attrs['format'];
      }
      
      $output = date($format,strtotime($expirationDate));
      
      // expired?
      if(strtotime($expirationDate) <= strtotime("now")){
        if(isset($attrs['expired'])){
          $output = $attrs['expired'];          
        }
        if(!empty($content)){
          $output = $content;
        }
      }
      
      //lifetime?
      if($subscription->lifetime == 1){
        $output = "Lifetime";
        if(isset($attrs['lifetime'])){
          $output = $attrs['lifetime'];          
        }
      }
      
    }
    
    return do_shortcode($output);
  }
  
  public function mijirehCheckout() {
    if(Cart66Session::get('Cart66Cart')->countItems() > 0) {
      $gatewayName = Cart66Common::postVal('cart66-gateway-name');
      if($_SERVER['REQUEST_METHOD'] == 'POST' && $gatewayName != 'Cart66Mijireh') {
        return ($gatewayName == "Cart66ManualGateway") ? $this->manualCheckout() : "";
      }

      if(!Cart66Session::get('Cart66Cart')->hasPayPalSubscriptions()) {
        require_once(CART66_PATH . "/gateways/Cart66Mijireh.php");

        if(Cart66Session::get('Cart66Cart')->getGrandTotal() > 0 || Cart66Session::get('Cart66Cart')->hasSpreedlySubscriptions()) {
          try {
            $mj = new Cart66Mijireh();
            $view = $this->_buildCheckoutView($mj);
          }
          catch(Cart66Exception $e) {
            $exception = Cart66Exception::exceptionMessages($e->getCode(), $e->getMessage());
            $view = Cart66Common::getView('views/error-messages.php', $exception);
          }
          return $view;
        }
        elseif(Cart66Session::get('Cart66Cart')->countItems() > 0) {
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of Mijireh Checkout because the cart value is $0.00");
          return $this->manualCheckout();
        }

      }
      else {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering Mijireh checkout form because the cart contains a PayPal subscription");
      }
    }
  }
  
  protected function _buildCheckoutView($gateway) {
    $ssl = Cart66Setting::getValue('auth_force_ssl');
    if($ssl) {
      if(!Cart66Common::isHttps()) {
        $sslUrl = "https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        wp_redirect($sslUrl);
        exit;
      }
    }
    
    if(!Cart66Session::get('Cart66Cart')->requirePayment()) {
      require_once(CART66_PATH . "/gateways/Cart66ManualGateway.php");
      $gateway = new Cart66ManualGateway();
    }
    
    $view = Cart66Common::getView('views/checkout.php', array('gateway' => $gateway));
    return $view;
  }
  
  public function emailShortcodes($attrs) {
    $output = '';
    if($attrs['source'] == 'receipt' || $attrs['source'] == 'fulfillment' || $attrs['source'] == 'status' || $attrs['source'] == 'followup') {
      $order = new Cart66Order($attrs['id']);
      $data = array(
        'bill_first_name', 
        'bill_last_name',
        'bill_address',
        'bill_address2',
        'bill_city',
        'bill_state',
        'bill_country',
        'bill_zip',
        'ship_first_name',
        'ship_last_name',
        'ship_address',
        'ship_address2',
        'ship_city',
        'ship_state',
        'ship_country',
        'ship_zip',
        'phone',
        'email',
        'coupon',
        'discount_amount',
        'trans_id',
        'shipping',
        'subtotal',
        'tax',
        'total',
        'non_subscription_total',
        'ordered_on',
        'status',
        'ip',
        'products',
        'fulfillment_products',
        'receipt',
        'receipt_link',
        'ouid',
        'shipping_method',
        'account_id',
        'tracking_number'
      );
      if(in_array($attrs['att'], $data)) {
        switch ($attrs['att']) {
          case 'bill_first_name': // Intentional falling through
          case 'bill_last_name':
          case 'ship_first_name':
          case 'ship_last_name':
            $output = ucfirst(strtolower($order->$attrs['att']));
            break;
          case 'bill_address':
            if($order->bill_address2 != ''){
              $output = $order->$attrs['att'] . '<br />' . $order->bill_address2;
            }
            else {
              $output = $order->$attrs['att'];
            }
            break;
          case 'ship_address':
            if($order->ship_address2 != ''){
              $output = $order->$attrs['att'] . '<br />' . $order->ship_address2;
            }
            else {
              $output = $order->$attrs['att'];
            }
            break;
          case 'products':
            $output = Cart66Common::getView('/pro/views/emails/email-products.php', array('order' => $order, 'type' => $attrs['type'], 'code' => 'products'));
            break;
          case 'fulfillment_products':
            $output = Cart66Common::getView('/pro/views/emails/email-products.php', array('order' => $order, 'type' => $attrs['type'], 'code' => 'fulfillment_products', 'variable' => $attrs['variable']));
            break;
          case 'receipt':
            $output = Cart66Common::getView('/pro/views/emails/email-receipt.php', array('order' => $order, 'type' => $attrs['type']));
            break;
          case 'phone':
            $output = Cart66Common::formatPhone($attrs['att']);
            break;
          case 'total':
            $output = CART66_CURRENCY_SYMBOL_TEXT . number_format($order->$attrs['att'], 2);
            break;
          case 'tax':
            $output = CART66_CURRENCY_SYMBOL_TEXT . number_format($order->$attrs['att'], 2);
            break;
          case 'receipt_link':
            $receiptPage = get_page_by_path('store/receipt');
            $link = get_permalink($receiptPage->ID);
            if(strstr($link,"?")){
              $link .= '&ouid=';
            }
            else{
              $link .= '?ouid=';
            }
            $output = $link . $order->ouid;
            break;
          default:
            $output = $order->$attrs['att'];
        }

      }
      elseif(substr($attrs['att'], 0, 8) == 'tracking') {
        $output = Cart66AdvancedNotifications::updateTracking($order, $attrs);
      }
      elseif(substr($attrs['att'], 0, 5) == 'date:') {
        $output = Cart66AdvancedNotifications::updateDate($attrs);
      }
      elseif(substr($attrs['att'], 0, 12) == 'date_ordered') {
        $output = Cart66AdvancedNotifications::updateDateOrdered($order, $attrs);
      }
      $shipping_options = array(
        'ship_first_name',
        'ship_last_name',
        'ship_address',
        'ship_address2',
        'ship_city',
        'ship_state',
        'ship_country',
        'ship_zip',
      );
      if(in_array($attrs['att'], $shipping_options) && $order->shipping_method == 'None') {
        $output = '';
      }
    }
    elseif($attrs['source'] == 'reminder') {
      $sub = new Cart66AccountSubscription($attrs['id']);
      $account = new Cart66Account();
      $account->load($sub->account_id);
      $data = array(
        'billing_first_name', 
        'billing_last_name',
        'feature_level',
        'subscription_plan_name',
        'active_until',
        'billing_interval',
        'username',
        'opt_out_link'
      );
      if(in_array($attrs['att'], $data)) {
        switch ($attrs['att']) {
          case 'billing_first_name': // Intentional falling through
          case 'billing_last_name':
            $output = ucfirst(strtolower($sub->$attrs['att']));
            break;
          case 'active_until':
            $output = date('F d, Y', strtotime($sub->$attrs['att']));
            break;
          case 'username':
            $output = $account->$attrs['att'];
            break;
          case 'opt_out_link':
            $output = Cart66ProCommon::generateUnsubscribeLink($account->id);
            break;
          default;
            $output = $sub->$attrs['att'];
        }
        
      }
    }
    
    return $output;
  }
  
  public function emailOptOut() {
    if(isset($_GET['cart66-task']) && $_GET['cart66-task'] == 'opt_out') {
      if(isset($_GET['e']) && isset($_GET['t'])) {
        $email = base64_decode(urldecode($_GET['e']));
        $verify = Cart66ProCommon::verifyEmailToken($_GET['t'], $email);
        if($verify == 1) {
          $data = array(
            'form' => 'form',
            'email' => $email,
            'token' => $_GET['t']
          );
          echo Cart66Common::getView('pro/views/unsubscribe.php', $data);
        }
        else {
          if($verify == -1) {
            $message = __('This email has already been unsubscribed', 'cart66');
          }
          if($verify == -2) {
            $message = __('This email does not exist in our system', 'cart66');
          }
          $data = array(
            'form' => 'error',
            'message' => $message
          );
          echo Cart66Common::getView('pro/views/unsubscribe.php', $data);
        }
      }
    }
    elseif(isset($_GET['cart66-action']) && $_GET['cart66-action'] == 'opt_out') {
      Cart66ProCommon::unsubscribeEmailToken($_POST['token'], $_POST['email']);
      $data = array(
        'form' => 'opt_out',
        'email' => $_POST['email']
      );
      echo Cart66Common::getView('pro/views/unsubscribe.php', $data);
    }
    elseif(isset($_GET['cart66-action']) && $_GET['cart66-action'] == 'cancel_opt_out') {
      $data = array(
        'form' => 'cancel'
      );
      echo Cart66Common::getView('pro/views/unsubscribe.php', $data);
    }
  }
  
}
