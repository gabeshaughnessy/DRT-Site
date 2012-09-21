<?php
class Cart66AccountSubscription extends Cart66ModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = Cart66Common::getTableName('account_subscriptions');
    parent::__construct($id);
  }
  
  public function isActive() {
    $isActive = false;
    $activeTs = strtotime($this->activeUntil);
    $today = strtotime('now');
    if($activeTs > $today || $this->lifetime == 1) {
      $isActive = true;
    }
    return $isActive;
  }
  
  public function validate(){
    $this->_is_product_id_valid();
    
    // Debugging code to display errors 
    if($this->hasErrors()) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Subscription validation errors: " . print_r($this->_errors, true) . print_r($this->_data, true));
    }
    
    return $this->_errors;
  }
  
  /**
   *
   */
  protected function _is_product_id_valid(){
    $is_valid = true;
    if(empty($this->product_id)){
      $is_valid = false;
      $this->addError('product_id', __("A membership or subscription product is required","cart66"), 'account-product_id');
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account validation error: membership product is required. $this->product_id");
    }
    return $is_valid;
  }
  
  
  public function getProductId(){
    global $wpdb;
    $output = false;
    $sql = 'SELECT id from ' . Cart66Common::getTableName('products') . " WHERE feature_level = '" . $this->feature_level . "' AND name = '" . $this->subscription_plan_name . "'";
    $matchedProducts = $wpdb->get_results($sql);
    if(count($matchedProducts) == 1){
      // one match found
      $output = $matchedProducts[0]->id;
    }
    if(count($matchedProducts) > 1){
      // multiple matches found
      foreach($matchedProducts as $pid){
        $output[] = $pid->id;
      }
    }
    return $output;
  }
  
  public function findLatestProductId($ids){
    global $wpdb;
    $output = false;
    if(is_array($ids)){
      $sql = 'SELECT id from ' . Cart66Common::getTableName('orders') . " WHERE account_id = '" . $this->account_id . "'"; 
      $orders = $wpdb->get_col($sql);
      if(count($orders) > 0){
        $orderIds = implode($orders,',');
        $sql = 'SELECT product_id from ' . Cart66Common::getTableName('order_items') . " WHERE `order_id` IN ($orderIds) ORDER BY id desc";
        $products  = $wpdb->get_results($sql);
        if(count($products) > 0){
          foreach($products as $product){
            $productIds[] = $product->product_id;
            if(in_array($product->product_id, $ids)){
              Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] order item id: $product->product_id found in list of subscription products matching feature level and subscription name.");
              $output = $product->product_id;
              break;
            }
          }
        }
        
      }
      
    }
    
    return $output;
  }
  
  public function updateProductId($id){
    if(!empty($id)){
      $this->product_id = $id;
      $this->save();
    }
    return $id;
  }
  
  public function isPayPalSubscription() {
    $isPayPal = false;
    if(strlen($this->paypalBillingProfileId) > 2) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] PayPal Billing Profile ID: $this->paypalBillingProfileId");
      $isPayPal = true;
    }
    return $isPayPal;
  }
  
  public function getSubscriptionManagementLink($returnUrl=null) {
    $url = false;
    if($this->isSpreedlySubscription()) {
      if(!isset($returnUrl)) {
        $returnUrl = '?return_url=' . Cart66Common::getCurrentPageUrl();
      }
      $spreedly = Cart66Setting::getValue('spreedly_shortname');
      $url = "https://spreedly.com/$spreedly/subscriber_accounts/" . $this->subscriberToken . $returnUrl;
    }
    elseif($this->isPayPalSubscription()) {
      if($this->isActive()) {
        $url = Cart66Common::replaceQueryString('cart66-task=CancelRecurringPaymentsProfile');
      }
    }
    return $url;
  }
  
  /**
   * Cancel remote PayPal subscription and set local status to canceled.
   * If expire is set to true, also change the active until date to today.
   * 
   * @param string $note The note to send to PayPal describing the reason for cancelation
   * @param boolean $expire If true, change the active_until date to today
   */
  public function cancelPayPalSubscription($note='Your subscription has been canceled per your request.', $expire=false) {
    if($this->id > 0) {
      $pp = new Cart66PayPalPro();
      $profileId = $this->paypalBillingProfileId;
      $pp->ManageRecurringPaymentsProfileStatus($profileId, 'Cancel', $note);
      $this->active = 0;
      $this->status = 'canceled';
      
      if($expire) {
        $this->activeUntil = date('Y-m-d 00:00:00', Cart66Common::localTs());
      }
      
      $this->save();
    }
  }
  
  public function loadByPayPalBillingProfileId($profileId) {
    $sql = 'SELECT * from ' . $this->_tableName . " WHERE paypal_billing_profile_id='$profileId'";
    if($data = $this->_db->get_row($sql, ARRAY_A)) {
      $this->setData($data);
      return true;
    }
    return false;
  }
  
  /**
   * If the given date is in the future, extend the active_until date to the new date.
   * 
   * Return true on success, false on failure.
   * 
   * @param string A string representation of a date
   * @return boolean
   */
  public function extendActiveUntil($date) {
    $isExtended = false;
    // Check strlen of date to see if it has any chance of being a real date. IPN may send 'N/A'
    if($this->id > 0 and strlen($date) > 5) {
      $newDate = date('Y-m-d H:i:s', strtotime($date));
      if(strtotime($newDate) > strtotime('today')) {
        $this->activeUntil = $newDate;
        $this->save();
        $isExtended = true;
      }
    }
    else {
      // Look for N/A which should mean that this is the last recurring payment
      $date = trim(strtoupper($date));
      if('N/A' == $date) {
        $increment = $this->activeUntil . ' + ' . $this->billingInterval;
        $this->activeUntil = date('Y-m-d H:i:s', strtotime($increment));
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Increment subscription date to $increment -- $this->activeUntil");
        $this->save();
        $isExtended = true;
      }
    }
    return $isExtended;
  }
  
  
  // ===================================
  // = Spreedly Subscription Functions =
  // ===================================
  
  
  public function isSpreedlySubscription() {
    $isSpreedly = false;
    if(strlen($this->subscriberToken) > 2) {
      $isSpreedly = true;
    }
    return $isSpreedly;
  }
  
  /**
   * Attempt to create a Spreedly Subsriber account subscription.
   * Return true on success otherwise false
   * 
   * @param int The Cart66 account id associated with this subscription
   * @return boolean
   */
  public function createSpreedlyAccountSubscription($accountId) {
    $subscriptionCreated = false;
    $this->accountId = $accountId;
    $data = $this->_getSpreedlySubscriberDataArray();
    
    try {
      $subscriber = new SpreedlySubscriber();
      $subscriber->create($data);
      $this->hydrate($subscriber);
      $this->save();
      $subscriptionCreated = true;
    }
    catch(SpreedlyException $e) {
      $this->addError('spreedly', $e->getMessage());
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Spreedly account creation failed: " . $e->getMessage() . "\nError code: " . $e->getCode());
    }
      
    return $subscriptionCreated;
  }
  
  public function loadBySubscriberToken($token) {
    $sql = 'SELECT * from ' . $this->_tableName . " WHERE subscriber_token='$token'";
    if($data = $this->_db->get_row($sql, ARRAY_A)) {
      $this->setData($data);
      return true;
    }
    return false;
  }
  
  public function loadByAccountId($accountId) {
    $isLoaded = false;
    $sql = 'SELECT * from ' . $this->_tableName . ' WHERE account_id = %d order by id desc';
    $sql = $this->_db->prepare($sql, $accountId);
    if($data = $this->_db->get_row($sql, ARRAY_A)) {
      $this->setData($data);
      $isLoaded = true;
    }
    return $isLoaded;
  }
  
  /**
   * Create a Spreedly subscription.
   * 
   * @param int $accountId The primary key from the Cart66 accounts table associated with this subscription
   * @param int $subscriptionId The id of the spreedly subscription plan
   * @param mixed $paymentMethod Either 'on-file' or a SpreedlyCreditCard object
   */
  public function createSpreedlySubscription($accountId, $subscriptionId, $productId, $paymentMethod='on-file') {
    $subscriptionCreated = false;
    if(is_numeric($accountId) && $accountId > 0) {
      
      if(!$this->loadByAccountId($accountId)) {
        $this->accountId = $accountId;
      }
      
      
      $subscriber = new SpreedlySubscriber();
      $subscriber->hydrate($this->_getSpreedlySubscriberDataArray());
      $subscription = new SpreedlySubscription($subscriptionId);
      
      if('free_trial' == strtolower((string)$subscription->planType)) {
        $subscriberData = $subscriber->toArray(true); // prune the empty data
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Creating a new subscriber before assigning free trial. " . print_r($subscriberData, true));
        $subscriber->create($subscriberData);
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Preparing to assign free trial plan ($subscriptionId) to new subscriber: " . 
          print_r($subscriber->toArray(), true));
        $subscriber->assignFreeTrialPlan($subscriptionId);
      }
      else {
        $invoice = new SpreedlyInvoice();
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Creating a Spreedly invoice for subscription id: $subscriptionId");
        $invoice->create($subscriber, $subscriptionId);
        $invoice->pay($paymentMethod);
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Spreedly invoice has been created and paid.");
      }
      $this->productId = $productId;
      $this->save();
    }
  }
  
  /**
   * Set all the account variables based on the given SpreedlySubscriber
   * 
   * @param SpreedlySubscriber $subscriber
   * @return void
   */
  public function hydrate(SpreedlySubscriber $subscriber) {
    $this->accountId = (int)$subscriber->customerId; 
    $this->billingFirstName = (string)$subscriber->billingFirstName;
    $this->billingLastName = (string)$subscriber->billingLastName;
    // Moved to account: $this->email = (string)$subscriber->email;
    $this->subscriberToken = (string)$subscriber->token;
    $this->featureLevel = (string)$subscriber->featureLevel;
    $this->activeUntil = date('Y-m-d H:i:s', strtotime((string)$subscriber->activeUntil));
    $this->createdAt = date('Y-m-d H:i:s', strtotime((string)$subscriber->createdAt));
    $this->updatedAt = date('Y-m-d H:i:s', strtotime((string)$subscriber->updatedAt));
    $this->graceUntil = date('Y-m-d H:i:s', strtotime((string)$subscriber->graceUntil));
    $this->readyToRenew = ('true' == strtolower((string)$subscriber->readyToRenew)) ? '1' : '0';
    $this->cardExpiresBeforeNextAutoRenew = ('true' == strtolower((string)$subscriber->cardExpiresBeforeNextAutoRenew)) ? '1' : '0';
    $this->subscriptionPlanName = (string)$subscriber->subscriptionPlanName;
    $this->recurring = ('true' == strtolower((string)$subscriber->recurring)) ? '1' : '0';
    $this->active = ('true' == strtolower((string)$subscriber->active)) ? '1' : '0';
  }
  
  public function getUsername() {
    $account = new Cart66Account($this->accountId);
    return $account->useranme;
  }
  
  /**
   * Return an array of the data spreedly cares about for working with subscribers.
   * This array can be passed to the SpreedlySubscriber class to create subscribers.
   */
  protected function _getSpreedlySubscriberDataArray() {
    $account = new Cart66Account($this->accountId);
    
    $data = array(
      'customer-id' => $this->accountId,
      'email' => $account->email,
      'token' => $this->subscriberToken,
      'billing-first-name' => $account->firstName,
      'billing-last-name' => $account->lastName, 
      'screen-name' => $account->username,
      'feature-level' => $this->featureLevel
    );
    return $data;
  }
  
}