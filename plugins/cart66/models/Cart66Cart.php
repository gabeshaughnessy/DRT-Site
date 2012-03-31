<?php
class Cart66Cart {
  
  /**
   * An array of Cart66CartItem objects
   */
  private $_items = array();
  
  private $_promotion;
  private $_promoStatus;
  private $_shippingMethodId;
  
  public function __construct($items=null) {
    if(is_array($items)) {
      $this->_items = $items;
    }
    else {
      $this->_items = array();
    }
    $this->_promoStatus = 0;
    $this->_setDefaultShippingMethodId();
  }
  
  /**
   * Add an item to the shopping cart when an Add To Cart button is clicked.
   * Combine the product options, check inventory, and add the item to the shopping cart.
   * If the inventory check fails redirect the user back to the referring page.
   * This function assumes that a form post triggered the call.
   */
  public function addToCart() {
    $itemId = Cart66Common::postVal('cart66ItemId');
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Adding item to cart: $itemId");

    $options = '';
    if(isset($_POST['options_1'])) {
      $options = Cart66Common::postVal('options_1');
    }
    if(isset($_POST['options_2'])) {
      $options .= '~' . Cart66Common::postVal('options_2');
    }

    if(Cart66Product::confirmInventory($itemId, $options)) {
      $this->addItem($itemId, 1, $options);
    }
    else {
      Cart66Common::log("Item not added due to inventory failure");
      wp_redirect($_SERVER['HTTP_REFERER']);
    }
  }
  
  public function updateCart() {
    if(Cart66Common::postVal('updateCart') == 'Calculate Shipping') {
      $_SESSION['cart66_shipping_zip'] = Cart66Common::postVal('shipping_zip');
      $_SESSION['cart66_shipping_country_code'] = Cart66Common::postVal('shipping_country_code');
    }
    $this->_setShippingMethodFromPost();
    $this->_updateQuantitiesFromPost();
    $this->_setCustomFieldInfoFromPost();
    $this->_setPromoFromPost();
  }
  
  public function addItem($id, $qty=1, $optionInfo='', $formEntryId=0) {
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Add item with options: $optionInfo");
    $optionInfo = $this->_processOptionInfo($optionInfo);
    Cart66Common::log("Option: " . $optionInfo->options);
    $product = new Cart66Product($id);
    
    if($product->id > 0) {
      $newItem = new Cart66CartItem($product->id, $qty, $optionInfo->options, $optionInfo->priceDiff);
      if($product->isGravityProduct()) {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Product being added is a Gravity Product: $formEntryId");
        if($formEntryId > 0) {
          $newItem->addFormEntryId($formEntryId);
          $this->_items[] = $newItem;
        }
      }
      elseif($product->isSubscription() && $this->hasSubscriptionProducts()) {
        // Make sure only one subscription can be added to the cart. Spreedly only allows one subscription per subscriber.
        $_SESSION['Cart66SubscriptionWarning'] = true;
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Tried to add more than one subscripton to the cart.");
        return;
      }
      else {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Product being added is NOT a Gravity Product");
        $isNew = true;
        foreach($this->_items as $item) {
          if($item->isEqual($newItem)) {
            $isNew = false;
            $newQuantity = $item->getQuantity() + $qty;
            $item->setQuantity($newQuantity);
            if($formEntryId > 0) {
              $item->addFormEntryId($formEntryId);
            }
            break;
          }
        }
        if($isNew) {
          $this->_items[] = $newItem;
        }
      }
    }
    
  }
  
  public function removeItem($itemIndex) {
    if(isset($this->_items[$itemIndex])) {
      $this->_items[$itemIndex]->detachAllForms();
      unset($this->_items[$itemIndex]);
    }
  }
  
  public function setPriceDifference($amt) {
    if(is_numeric($amt)) {
      $this->_priceDifference = $amt;
    }
  }
  
  public function setItemQuantity($itemIndex, $qty) {
    if(is_numeric($qty)) {
      if(isset($this->_items[$itemIndex])) {
        if($qty == 0) {
          unset($this->_items[$itemIndex]);
        }
        else {
          $this->_items[$itemIndex]->setQuantity($qty);
        }
      }
    }
  }
  
  public function setCustomFieldInfo($itemIndex, $info) {
    if(isset($this->_items[$itemIndex])) {
      $this->_items[$itemIndex]->setCustomFieldInfo($info);
    }
  }
  
  /**
   * Return the number of items in the shopping cart.
   * This count includes multiples of the same product so the returned value is the sum 
   * of all the item quantities for all the items in the cart.
   * 
   * @return int
   */
  public function countItems() {
    $count = 0;
    foreach($this->_items as $item) {
      $count += $item->getQuantity();
    }
    return $count;
  }
  
  public function getItems() {
    return $this->_items;
  }
  
  public function getItem($itemIndex) {
    return $this->_items[$itemIndex];
  }
  
  public function setItems($items) {
    if(is_array($items)) {
      $this->_items = $items;
    }
  }
  
  public function getSubTotal() {
    $total = 0;
    foreach($this->_items as $item) {
      $total += $item->getProductPrice() * $item->getQuantity();
    }
    return $total;
  }
  
  public function getSubscriptionAmount() {
    $amount = 0;
    if($subId = $this->getSpreedlySubscriptionId()) {
      $subscription = new SpreedlySubscription();
      $subscription->load($subId);
      if(!$subscription->hasFreeTrial()) {
        $amount = $subscription->amount;
      }
    }
    return $amount;
  }
  
  /**
   * Return the subtotal without including any of the subscription prices
   * 
   * @return float
   */
  public function getNonSubscriptionAmount() {
    $total = 0;
    foreach($this->_items as $item) {
      if(!$item->isSubscription()) {
        $total += $item->getProductPrice() * $item->getQuantity();
      }
      elseif($item->isPayPalSubscription()) {
        $total += $item->getProductPrice();
      }
    }
    return $total;
  }
  
  public function getTaxableAmount() {
    $total = 0;
    $p = new Cart66Product();
    foreach($this->_items as $item) {
      $p->load($item->getProductId());
      if($p->taxable == 1) {
        $total += $item->getProductPrice() * $item->getQuantity();
      }
    }
    $discount = $this->getDiscountAmount();
    if($discount > $total) {
      $total = 0;
    }
    else {
      $total = $total - $discount;
    }
    return $total;
  }
  
  public function getTax($state='All Sales', $zip=null) {
    $tax = 0;
    $taxRate = new Cart66TaxRate();
    
    $isTaxed = $taxRate->loadByZip($zip);
    if($isTaxed == false) {
      $isTaxed = $taxRate->loadByState($state);
    }
    
    if($isTaxed) {
      $taxable = $this->getTaxableAmount();
      if($taxRate->tax_shipping == 1) {
        $taxable += $this->getShippingCost();
      }
      $tax = number_format($taxable * ($taxRate->rate/100), 2, '.', '');
    }
    
    return $tax;
  }
  
  /**
   * Return an array of the shipping methods where the keys are names and the values are ids
   * 
   * @return array of shipping names and ids
   */
  public function getShippingMethods() {
    $method = new Cart66ShippingMethod();
    $methods = $method->getModels("where code IS NULL or code = ''", 'order by default_rate asc');
    $ship = array();
    foreach($methods as $m) {
      $ship[$m->name] = $m->id;
    }
    return $ship;
  }

  public function getCartWeight() {
    $weight = 0;
    foreach($this->_items as $item) {
      $weight += $item->getWeight()  * $item->getQuantity();
    }
    return $weight;
  }
  
  public function getShippingCost($methodId=null) {
    $setting = new Cart66Setting();

    if(!$this->requireShipping()) { 
      $shipping = 0; 
    }
    // Check to see if Live Rates are enabled and available
    elseif(isset($_SESSION['Cart66LiveRates']) && Cart66Setting::getValue('use_live_rates')) {
      $liveRate = $_SESSION['Cart66LiveRates']->getSelected();
      if(is_numeric($liveRate->rate)) {
        return number_format($liveRate->rate, 2, '.', '');
      }
    }
    // Live Rates are not in use
    else {
      if($methodId > 0) {
        $this->_shippingMethodId = $methodId;
      }
      
      if($this->_shippingMethodId < 1) {
        $this->_setDefaultShippingMethodId();
      }
      else {
        // make sure shipping method exists otherwise reset to the default shipping method
        $method = new Cart66ShippingMethod();
        if(!$method->load($this->_shippingMethodId) || !empty($method->code)) {
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Resetting the default shipping method id");
          $this->_setDefaultShippingMethodId();
        }
      }
      
      $methodId = $this->_shippingMethodId;

      // Check for shipping rules first
      $shipping = 0;
      $isRuleSet = false;
      $rule = new Cart66ShippingRule();
      $rules = $rule->getModels("where shipping_method_id = $methodId", 'order by min_amount desc');
      if(count($rules)) {
        $cartTotal = $this->getSubTotal();
        foreach($rules as $rule) {
          if($cartTotal > $rule->minAmount) {
            $shipping = $rule->shippingCost;
            $isRuleSet = true; 
            break;
          }
        }
      }
      
      if(!$isRuleSet) {
        $product = new Cart66Product();
        $shipping = 0;
        $highestShipping = 0;
        $bundleShipping = 0;
        $highestId = 0;
        foreach($this->_items as $item) {
          $product->load($item->getProductId());
          
          if($highestId < 1) {
            $highestId = $product->id;
          }
          
          if($product->isShipped()) {
            $shippingPrice = $product->getShippingPrice($methodId);
            $bundleShippingPrice = $product->getBundleShippingPrice($methodId);
            if($shippingPrice > $highestShipping) {
              $highestShipping = $shippingPrice;
              $highestId = $product->id;
            }
            $bundleShipping += $bundleShippingPrice * $item->getQuantity();
          }
        }

        if($highestId > 0) {
          $product->load($highestId);
          $shippingPrice = $product->getShippingPrice($methodId);
          $bundleShippingPrice = $product->getBundleShippingPrice($methodId);
          $shipping = $shippingPrice + ($bundleShipping - $bundleShippingPrice);
        }
      }
    }
    
    return number_format($shipping, 2, '.', '');
  }
  
  public function applyPromotion($code) {
    $code = strtoupper($code);
    $promotion = new Cart66Promotion();
    if($promotion->loadByCode($code)) {
      if(is_object($this->_promotion) && $this->_promotion->minOrder > $this->getNonSubscriptionAmount()) {
        // Order total not high enough for promotion to apply
        $this->_promoStatus = -1;
        $this->_promotion = null;
      }
      else {
        $this->_promotion = $promotion;
        $this->_promoStatus = 1;
      }
    }
    else {
      $this->_promoStatus = -1;
      $this->_promotion = null;
    }
  }
  
  public function getPromotion() {
    $promotion = false;
    if(is_a($this->_promotion, 'Cart66Promotion')) {
      $promotion = $this->_promotion;
    }
    return $promotion;
  }
  
  public function getPromoMessage() {
    $message = '&nbsp;';
    if($this->_promoStatus == -1) {
      $message = 'Invalid coupon code';
    }
    elseif($this->_promoStatus == -2) {
      $message = 'Order total not high enough for promotion to apply';
    }
    if($this->_promoStatus < 0) {
      $this->_promoStatus = 0;
    }
    return $message;
  }
  
  public function resetPromotionStatus() {
    if(is_a($this->_promotion, 'Cart66Promotion')) {
      if($this->_promotion->minOrder > $this->getSubTotal()) {
        // Order total not high enough for promotion to apply
        $this->_promoStatus = -2;
        $this->_promotion = null;
      }
      else {
        $this->_promoStatus = 1;
      }
    }
  }
  
  public function clearPromotion() {
    $this->_promotion = '';
    $this->_promoStatus = 0;
  }
  
  public function getPromoStatus() {
    return $this->_promoStatus;
  }
  
  public function getDiscountAmount() {
    $discount = 0;
    if(is_a($this->_promotion, 'Cart66Promotion')) {
      $total = $this->getNonSubscriptionAmount();
      $discountedTotal = $this->_promotion->discountTotal($total);
      $discount = number_format($total - $discountedTotal, 2, '.', '');
      // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Getting discount Total: $total -- Discounted Total: $discountedTotal -- Discount: $discount");
    }
    return $discount;
  }
  
  /**
   * Return the entire cart total including shipping costs and discounts.
   * An optional paramater can be provided to specify whether or not subscription items 
   * are included in the total.
   * 
   * @param boolean $includeSubscriptions
   * @return float 
   */
  public function getGrandTotal($includeSubscriptions=true) {
    if($includeSubscriptions) {
      $total = $this->getSubTotal() + $this->getShippingCost() - $this->getDiscountAmount();
    }
    else {
      $total = $this->getNonSubscriptionAmount() + $this->getShippingCost() - $this->getDiscountAmount();
    }
    return $total; 
  }
  
  public function storeOrder($orderInfo) {
    $order = new Cart66Order();
    $orderInfo['trans_id'] = (empty($orderInfo['trans_id'])) ? 'MT-' . Cart66Common::getRandString() : $orderInfo['trans_id'];
    $orderInfo['ip'] = $_SERVER['REMOTE_ADDR'];
    $orderInfo['discount_amount'] = $this->getDiscountAmount();
    $order->setInfo($orderInfo);
    $order->setItems($this->getItems());
    return $order->save();
  }
  
  /**
   * Return true if all products are digital
   */
  public function isAllDigital() {
    $allDigital = true;
    foreach($this->getItems() as $item) {
      if(!$item->isDigital()) {
        $allDigital = false;
        break;
      }
    }
    return $allDigital;
  }
  
  public function hasSubscriptionProducts() {
    foreach($this->getItems() as $item) {
      if($item->isSubscription()) {
        return true;
      }
    }
    return false;
  }
  
  /**
   * Return true if the cart only contains PayPal subscriptions
   */
  public function hasPayPalSubscriptions() {
    foreach($this->getItems() as $item) {
      if($item->isPayPalSubscription()) {
        return true;
      }
    }
    return false;
  }
  
  public function hasSpreedlySubscriptions() {
    foreach($this->getItems() as $item) {
      if($item->isSpreedlySubscription()) {
        return true;
      }
    }
    return false;
  }
  
  /**
   * Return the spreedly subscription id for the subscription product in the cart 
   * or false if there are no spreedly subscriptions in the cart. With Spreedly 
   * subscriptions, there may be only one subscription product in the cart.
   */
  public function getSpreedlySubscriptionId() {
    foreach($this->getItems() as $item) {
      if($item->isSpreedlySubscription()) {
        return $item->getSpreedlySubscriptionId();
      }
    }
    return false;
  }
  
  public function getPayPalSubscriptionId() {
    foreach($this->getItems() as $item) {
      if($item->isPayPalSubscription()) {
        return $item->getPayPalSubscriptionId();
      }
    }
    return false;
  }
  
  /**
   * Return the Cart66CartItem with the PayPal subscription
   * 
   * @return Cart66CartItem
   */
  public function getPayPalSubscriptionItem() {
    foreach($this->getItems() as $item) {
      if($item->isPayPalSubscription()) {
        return $item;
      }
    }
    return false;
  }
  
  /**
   * Return the index in the cart of the PayPal subscription item.
   * This number is used to know the location of the item in the cart
   * when creating the payment profile with PayPal.
   * 
   * @return int
   */
  public function getPayPalSubscriptionIndex() {
    $index = 0;
    foreach($this->getItems() as $item) {
      if($item->isPayPalSubscription()) {
        return $index;
      }
      $index++;
    }
    return false;
  }
  
  /**
   * Return false if none of the items in the cart are shipped
   */
  public function requireShipping() {
    $ship = false;
    foreach($this->getItems() as $item) {
      if($item->isShipped()) {
        $ship = true;
        break;
      }
    }
    return $ship;
  }
  
  public function requirePayment() {
    $requirePayment = true;
    if($this->getGrandTotal() < 0.01) {
      // Look for free trial subscriptions that require billing
      if($subId = $this->getSpreedlySubscriptionId()) {
        $sub = new SpreedlySubscription($subId);
        if('free_trial' == strtolower((string)$sub->planType)) {
          $requirePayment = false;
        }
      }
    }
    return $requirePayment;
  }

  public function setShippingMethod($id) {
    $method = new Cart66ShippingMethod();
    if($method->load($id)) {
      $this->_shippingMethodId = $id;
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Set shipping method id to: $id");
    }
  }

  public function getShippingMethodId() {
    if($this->_shippingMethodId < 1) {
      $this->_setDefaultShippingMethodId();
    }
    return $this->_shippingMethodId;
  }

  public function getShippingMethodName() {
    // Look for live rates
    if(isset($_SESSION['Cart66LiveRates'])) {
      $rate = $_SESSION['Cart66LiveRates']->getSelected();
      return $rate->service;
    }
    // Not using live rates
    else {
      if($this->isAllDigital()) {
        return 'Download';
      }
      elseif(!$this->requireShipping()) {
        return 'None';
      }
      else {
        if($this->_shippingMethodId < 1) {
          $this->_setDefaultShippingMethodId();
        }
        $method = new Cart66ShippingMethod($this->_shippingMethodId);
        return $method->name;
      }
    }
    
  }
  
  public function detachFormEntry($entryId) {
    foreach($this->_items as $index => $item) {
      $entries = $item->getFormEntryIds();
      if(in_array($entryId, $entries)) {
        $item->detachFormEntry($entryId);
        $qty = $item->getQuantity();
        if($qty == 0) {
          $this->removeItem($index);
        }
      }
    }
  }
  
  public function checkCartInventory() {
    $alert = '';
    foreach($this->_items as $itemIndex => $item) {
      if(!Cart66Product::confirmInventory($item->getProductId(), $item->getOptionInfo(), $item->getQuantity())) {
        Cart66Common::log("Unable to confirm inventory when checking cart.");
        $qtyAvailable = Cart66Product::checkInventoryLevelForProduct($item->getProductId(), $item->getOptionInfo());
        if($qtyAvailable > 0) {
          $alert .= '<p>We are not able to fulfill your order for <strong>' .  $item->getQuantity() . '</strong> ' . $item->getFullDisplayName() . "  because 
            we only have <strong>$qtyAvailable in stock</strong>.</p>";
        }
        else {
          $alert .= '<p>We are not able to fulfill your order for <strong>' .  $item->getQuantity() . '</strong> ' . $item->getFullDisplayName() . "  because 
            it is <strong>out of stock</strong>.</p>";
        }
        
        if($qtyAvailable > 0) {
          $item->setQuantity($qtyAvailable);
        }
        else {
          $this->removeItem($itemIndex);
        }
        
      }
    }
    
    if(!empty($alert)) {
      $alert = "<div class='Cart66Unavailable'><h1>Inventory Restriction</h1> $alert <p>Your cart has been updated based on our available inventory.</p>";
      $alert .= '<input type="button" name="close" value="Ok" class="Cart66ButtonSecondary modalClose" /></div>';
    }
    
    return $alert;
  }
  
  /**
   * Return Cart66LiveRates object. 
   * The shipping zip code must be in the session before calling this function.
   * 
   * @return Cart66LiveRates
   */
  public function getUpsRates() {
    $liveRates = new Cart66LiveRates();
    $cartWeight = $_SESSION['Cart66Cart']->getCartWeight();
    $zip = $_SESSION['cart66_shipping_zip'];
    $countryCode = $_SESSION['cart66_shipping_country_code'];
    
    if($cartWeight > 0 && isset($_SESSION['cart66_shipping_zip']) && isset($_SESSION['cart66_shipping_country_code'])) {

      // Return the live rates from the session if the zip, country code, and cart weight are the same
      if(isset($_SESSION['Cart66LiveRates'])) {
        $cartWeight = $this->getCartWeight();
        $liveRates = $_SESSION['Cart66LiveRates'];
        Cart66Common::log(  "Live Rates were found in session. Now comparing...
            $liveRates->weight --> $cartWeight
            $liveRates->toZip --> $zip
            $liveRates->toCountryCode --> $countryCode
        ");
        if($liveRates->weight == $cartWeight && $liveRates->toZip == $zip && $liveRates->toCountryCode == $countryCode) {
          Cart66Common::log("Using Live Rates from the session");
          return $liveRates; 
        }
      }

      // If there are no live rates in the session or the zip/weight has been changed then look up new rates
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Live Shipping: There are no live rates in the session or the zip/weight has been changed");
      $ups = new Cart66Ups();
      $liveRates->weight = $this->getCartWeight();
      $liveRates->toZip = $zip;
      $liveRates->toCountryCode = $countryCode;
      $rates = $ups->getAllRates($zip, $countryCode, $liveRates->weight);
      $liveRates->clearRates();
      foreach($rates as $service => $rate) {
        $liveRates->addRate($service, $rate);
      }
    }
    else {
      $liveRates->weight = 0;
      $liveRates->toZip = $zip;
      $liveRates->toCountryCode = $countryCode;
      $liveRates->addRate('Free Shipping', '0.00');
    }
    
    $_SESSION['Cart66LiveRates'] = $liveRates;
    return $liveRates;
  }
  
  protected function _setDefaultShippingMethodId() {
    // Set default shipping method to the cheapest method
    $method = new Cart66ShippingMethod();
    $methods = $method->getModels("where code IS NULL or code = ''", 'order by default_rate asc');
    if(is_array($methods) && count($methods) && get_class($methods[0]) == 'Cart66ShippingMethod') {
      $this->_shippingMethodId = $methods[0]->id;
    }
  }
  
  protected function _setShippingMethodFromPost() {
    // Not using live rates
    if(isset($_POST['shipping_method_id'])) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not using live shipping rates");
      $shippingMethodId = $_POST['shipping_method_id'];
      $this->setShippingMethod($shippingMethodId);
    }
    // Using live rates
    elseif(isset($_POST['live_rates'])) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Using live shipping rates");
      if(isset($_SESSION['Cart66LiveRates'])) {
        $_SESSION['Cart66LiveRates']->setSelected($_POST['live_rates']);
      }
    }
  }
  
  protected function _updateQuantitiesFromPost() {
    $qtys = Cart66Common::postVal('quantity');
    if(is_array($qtys)) {
      foreach($qtys as $itemIndex => $qty) {
        $item = $this->getItem($itemIndex);
        if(!is_null($item) && get_class($item) == 'Cart66CartItem') {
          if(Cart66Product::confirmInventory($item->getProductId(), $item->getOptionInfo(), $qty)) {
            $this->setItemQuantity($itemIndex, $qty);
          }
          else {
            $qtyAvailable = Cart66Product::checkInventoryLevelForProduct($item->getProductId(), $item->getOptionInfo());
            $this->setItemQuantity($itemIndex, $qtyAvailable);
            if(empty($_SESSION['Cart66InventoryWarning'])) { $_SESSION['Cart66InventoryWarning'] = ''; }
            $_SESSION['Cart66InventoryWarning'] .= '<p>The quantity for ' . $item->getFullDisplayName() . " could not be changed to $qty because 
              we only have $qtyAvailable in stock.</p>";
            Cart66Common::log("Quantity available ($qtyAvailable) cannot meet desired quantity ($qty)");
          }
        }
      }
    }
  }
  
  protected function _setCustomFieldInfoFromPost() {
    // Set custom values for individual products in the cart
    $custom = Cart66Common::postVal('customFieldInfo');
    if(is_array($custom)) {
      foreach($custom as $itemIndex => $info) {
        $this->setCustomFieldInfo($itemIndex, $info);
      }
    }
  }
  
  protected function _setPromoFromPost() {
    if(isset($_POST['couponCode']) && $_POST['couponCode'] != '') {
      $couponCode = Cart66Common::postVal('couponCode');
      $this->applyPromotion($couponCode);
    }
    else {
      $this->resetPromotionStatus();
    }
  }
  
  /**
   * Return a stdClass object with the price difference and a CSV list of options.
   *   $optionResult->priceDiff
   *   $optionResult->options
   * @return object
   */
  protected function _processOptionInfo($optionInfo) {
    $optionInfo = trim($optionInfo);
    $priceDiff = 0;
    $options = explode('~', $optionInfo);
    $optionList = array();
    foreach($options as $opt) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Working with option: $opt");
      if(preg_match('/\+\s*\$/', $opt)) {
        $opt = preg_replace('/\+\s*\$/', '+$', $opt);
        list($opt, $pd) = explode('+$', $opt);
        $optionList[] = trim($opt);
        $priceDiff += $pd;
      }
      elseif(preg_match('/-\s*\$/', $opt)) {
        $opt = preg_replace('/-\s*\$/', '-$', $opt);
        list($opt, $pd) = explode('-$', $opt);
        $optionList[] = trim($opt);
        $pd = trim($pd);
        $priceDiff -= $pd;
      }
      else {
        $optionList[] = trim($opt);
      }
    }
    $optionResult = new stdClass();
    $optionResult->priceDiff = $priceDiff;
    $optionResult->options = implode(', ', $optionList);
    return $optionResult;
  }
  
}
