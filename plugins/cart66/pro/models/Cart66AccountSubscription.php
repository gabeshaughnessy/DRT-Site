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
    if($activeTs > $today) {
      $isActive = true;
    }
    return $isActive;
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
        $this->activeUntil = date('Y-m-d 00:00:00');
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
   * Attempt to create a Spreedly Subsriber account.
   * Return true on success otherwise false
   * 
   * @return boolean
   */
  public function createSpreedlyAccount() {
    // TODO: Refactor createSpreedlyAccount() for new account_subscriptions table
    $accountCreated = false;
    if($this->id > 0) {
      
      if($this->validate()) {
        $data = $this->_getSpreedlySubscriberDataArray();
        try {
          $subscriber = new SpreedlySubscriber();
          $subscriber->create($data);
          $this->hydrate($subscriber);
          $this->save();
          $accountCreated = true;
        }
        catch(SpreedlyException $e) {
          $this->addError('spreedly', $e->getMessage());
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Spreedly account creation failed: " . $e->getMessage() . "\nError code: " . $e->getCode());
        }
      }
      
    }
    return $accountCreated;
  }
  
  public function loadBySubscriberToken($token) {
    // TODO: Refactor loadBySubscriberToken() for new account_subscriptions table
    $sql = 'SELECT * from ' . $this->_tableName . " WHERE subscriber_token='$token'";
    if($data = $this->_db->get_row($sql, ARRAY_A)) {
      $this->setData($data);
      return true;
    }
    return false;
  }
  
  /**
   * Create a Spreedly subscription.
   * 
   * @param int $subscriptionId The id of the spreedly subscription plan
   * @param mixed $paymentMethod Either 'on-file' or a SpreedlyCreditCard object
   */
  public function createSpreedlySubscription($subscriptionId, $paymentMethod='on-file') {
    // TODO: Refactor createSpreedlySubscription() for new account_subscriptions table
    $subscriptionCreated = false;
    if($this->id > 0 && $this->validate()) {
      
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
        $invoice->create($subscriber, $subscriptionId);
        $invoice->pay($paymentMethod);
      }
    }
  }
  
  /**
   * Set all the account variables based on the given SpreedlySubscriber
   * 
   * @param SpreedlySubscriber $subscriber
   * @return void
   */
  public function hydrate(SpreedlySubscriber $subscriber) {
    // TODO: Refactor hydrate() for new account_subscriptions table
    $this->id = (int)$subscriber->customerId;
    $this->billingFirstName = (string)$subscriber->billingFirstName;
    $this->billingLastName = (string)$subscriber->billingLastName;
    $this->email = (string)$subscriber->email;
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
  
  /**
   * Return an array of the data spreedly cares about for working with subscribers.
   * This array can be passed to the SpreedlySubscriber class to create subscribers.
   */
  protected function _getSpreedlySubscriberDataArray() {
    // TODO: Refactor _getSpreedlySubscriberDataArray() for new account_subscriptions table
    $screenName = $this->billingFirstName . ' ' . $this->billingLastName;
    $data = array(
      'customer-id' => $this->id,
      'email' => $this->email,
      'token' => $this->subscriberToken,
      'billing-first-name' => $this->billingFirstName,
      'billing-last-name' => $this->billingLastName, 
      'screen-name' => $screenName,
      'feature-level' => $this->featureLevel
    );
    return $data;
  }
  
}