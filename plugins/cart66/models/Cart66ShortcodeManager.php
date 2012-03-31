<?php

class Cart66ShortcodeManager {

  /**
   * Short code for displaying shopping cart including the number of items in the cart and links to view cart and checkout
   */
  public function shoppingCart($attrs) {
    $cartPage = get_page_by_path('store/cart');
    $checkoutPage = get_page_by_path('store/checkout');
    $cart = $_SESSION['Cart66Cart'];
    if(is_object($cart) && $cart->countItems()) {
      ?>
      <div id="Cart66scCartContents">
        <a id="Cart66scCartLink" href='<?php echo get_permalink($cartPage->ID) ?>'>
        <span id="Cart66scCartCount"><?php echo $cart->countItems(); ?></span>
        <span id="Cart66scCartCountText"><?php echo $cart->countItems() > 1 ? ' items' : ' item' ?></span> 
        <span id="Cart66scCartCountDash">&ndash;</span>
        <span id="Cart66scCartPrice"><?php echo CURRENCY_SYMBOL . 
          number_format($cart->getSubTotal() - $cart->getDiscountAmount(), 2); ?>
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
    $urlOptions = isset($attrs['options']) ? '&options=' . urlencode($options) : '';
    
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
        'url' => $cartLink . $joinChar . "task=add-to-cart-anchor&cart66ItemId=${id}${options}",
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
        $_SESSION['Cart66Cart']->detachFormEntry($entryId);
      }
    }
    $view = Cart66Common::getView('views/cart.php', $attrs);
    return $view;
  }

  public function showReceipt($attrs) {
    $view = Cart66Common::getView('views/receipt.php', $attrs);
    return $view;
  }

  public function paypalCheckout($attrs) {
    if(!$_SESSION['Cart66Cart']->hasSubscriptionProducts()) {
      if($_SESSION['Cart66Cart']->getGrandTotal()) {
        $view = Cart66Common::getView('views/paypal-checkout.php', $attrs);
        return $view;
      }
      else {
        return $this->manualCheckout();
      }
    }
  }

  public function manualCheckout($attrs=null) {
    if(!$_SESSION['Cart66Cart']->hasSubscriptionProducts()) {
      require_once(WP_PLUGIN_DIR . "/cart66/gateways/Cart66ManualGateway.php");
      $manual = new Cart66ManualGateway();
      $view = $this->_buildCheckoutView($manual);
    }
    else {
      $view = "<p>Unable to sell subscriptions using the manual checkout gateway.</p>";
    }
    return $view;
  }

  public function authCheckout($attrs) {
    if(!$_SESSION['Cart66Cart']->hasPayPalSubscriptions()) {
      require_once(WP_PLUGIN_DIR . "/cart66/pro/gateways/Cart66AuthorizeNet.php");
      $authnet = new Cart66AuthorizeNet();
      $view = $this->_buildCheckoutView($authnet);
      return $view;
    }
    else {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering Authorize.net checkout form because the cart contains a PayPal subscription");
    }
  }

  public function payPalProCheckout($attrs) {
    if(!$_SESSION['Cart66Cart']->hasPayPalSubscriptions()) {
      if($_SESSION['Cart66Cart']->getGrandTotal() > 0) {
        $paypal = new Cart66PayPalPro();
        $view = $this->_buildCheckoutView($paypal);
        return $view;
      }
      elseif($_SESSION['Cart66Cart']->countItems() > 0) {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of PayPal Pro Checkout because the cart value is $0.00");
        return $this->manualCheckout();
      }
    }
    else {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering PayPal Pro checkout form because the cart contains a PayPal subscription");
    }
  }

  public function payPalExpressCheckout($attrs) {
    if($_SESSION['Cart66Cart']->hasSpreedlySubscriptions()) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering PayPal Express checkout form because the cart contains Spreedly subscriptions");
      $errorMessage = "<p class='Cart66Error'>Spreedly subscriptions cannot be processed through PayPal Express Checkout</p>";
      return $errorMessage;
    }
    else {
      if($_SESSION['Cart66Cart']->getGrandTotal() > 0) {
        $view = Cart66Common::getView('views/paypal-expresscheckout.php', $attrs);
        return $view;
      }
      elseif($_SESSION['Cart66Cart']->countItems() > 0) {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Displaying manual checkout instead of PayPal Pro Express Checkout because the cart value is $0.00");
        return $this->manualCheckout();
      }
    }
  }

  public function payPalExpress($attrs) {
    $view = Cart66Common::getView('views/paypal-express.php', $attrs);
    return $view;
  }

  public function processIPN($attrs) {
    require_once(WP_PLUGIN_DIR. "/cart66/models/Cart66PayPalIpn.php");
    require_once(WP_PLUGIN_DIR. "/cart66/gateways/Cart66PayPalStandard.php");
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
    $view = Cart66Common::getView('tests/environment/tests.php');
    $view = "<pre>$view</pre>";
    return $view;
  }

  public function clearCart() {
    unset($_SESSION['Cart66Cart']);
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
        $_SESSION['Cart66AccountId'] = $account->id;
        
        // Send logged in user to the appropriate page after logging in
        $url = Cart66Common::getCurrentPageUrl();
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account Login: " . print_r($attrs, true));
        if(isset($attrs['url']) && !empty($attrs['url'])) {
          if('stay' != strtolower($attrs['url'])) {
            $url = $attrs['url'];
          }
        }
        else {
          // Locate logged in user home page
          $pgs = get_posts('numberposts=1&post_type=any&meta_key=cart66_member&meta_value=home');
          if(count($pgs)) {
            $url = get_permalink($pgs[0]->ID);
          }
        }
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Redirecting after login to: $url");
        wp_redirect($url);
        exit();
      }
      else {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Login failed: " . $_POST['login']['email'] . ' -- ' . $_POST['login']['password']);
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
      $account = new Cart66Account($_SESSION['Cart66AccountId']);
      
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
        $text = isset($attrs['text']) ? $attrs['text'] : 'Manage your subscription.';
        $returnUrl = isset($attrs['return']) ? $attrs['return'] : null;
        $url = $account->getSubscriptionManagementLink($returnUrl);
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
  
  public function cancelPayPalSubscription($attrs) {
    $link = '';
    if(Cart66Common::isLoggedIn()) {
      $account = new Cart66Account($_SESSION['Cart66AccountId']);
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
      $account = new Cart66Account($_SESSION['Cart66AccountId']);
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
      $account = new Cart66Account($_SESSION['Cart66AccountId']);
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
  
  public function showTo($attrs, $content='null') {
    $isAllowed = false;
    if(Cart66Common::isLoggedIn()) {
      $account = new Cart66Account();
      if($account->load($_SESSION['Cart66AccountId'])) {
        if($account->isActive() && in_array($account->getFeatureLevel(), explode(',', $attrs['level']))) {
          $isAllowed = true;
        }
      }
    }
    $content = $isAllowed ? $content : '';

    return do_shortcode($content);
  }
  
  
  public function hideFrom($attrs, $content='null') {
    $isAllowed = true;
    if(Cart66Common::isLoggedIn()) {
      $account = new Cart66Account();
      if($account->load($_SESSION['Cart66AccountId'])) {
        if($account->isActive() && in_array($account->getFeatureLevel(), explode(',', $attrs['level']))) {
          $isAllowed = false;
        }
      }
    }
    $content = $isAllowed ? $content : '';

    return do_shortcode($content);
  }
  
  public function gravityFormToCart($entry) {
    $formId = Cart66GravityReader::getGravityFormIdForEntry($entry['id']);
    if($formId) {
      $productId = Cart66Product::getProductIdByGravityFormId($formId);
      if($productId > 0) {
        $product = new Cart66Product($productId);
        $qty = $product->gravityCheckForEntryQuantity($entry);
        $options = $product->gravityGetVariationPrices($entry);
        $_SESSION['Cart66Cart']->addItem($productId, $qty, $options, $entry['id']);
        $cartPage = get_page_by_path('store/cart');
        $cartPageLink = get_permalink($cartPage->ID);
        $_SESSION['Cart66LastPage'] = $_SERVER['HTTP_REFERER'];
        wp_redirect($cartPageLink);
        exit;
      }
    }
  }
  
  public function zendeskRemoteLogin() {
    if(Cart66Common::isLoggedIn() && isset($_GET['timestamp'])) {
      $account = new Cart66Account($_SESSION['Cart66AccountId']);
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
  
  protected function _buildCheckoutView($gateway) {
    $ssl = Cart66Setting::getValue('auth_force_ssl');
    if($ssl == 'yes') {
      if(!Cart66Common::isHttps()) {
        $sslUrl = "https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        wp_redirect($sslUrl);
        exit();
      }
    }
    
    if(!$_SESSION['Cart66Cart']->requirePayment()) {
      require_once(WP_PLUGIN_DIR . "/cart66/gateways/Cart66ManualGateway.php");
      $gateway = new Cart66ManualGateway();
    }
    
    $view = Cart66Common::getView('views/checkout.php', array('gateway' => $gateway));
    return $view;
  }
    
}
