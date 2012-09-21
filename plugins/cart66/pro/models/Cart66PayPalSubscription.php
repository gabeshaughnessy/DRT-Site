<?php
class Cart66PayPalSubscription extends Cart66Product {
  
  public function __construct($id=null) {
    $this->_tableName = Cart66Common::getTableName('products');
    parent::__construct($id);
  }
  
  public function validate() {
    
    // Verify that the item number is present
    if(empty($this->item_number)) {
      $this->addError('item_number', __("Item number is required","cart66"), 'subscription_item_number');
    }
    
    // Verify that no other products have the same item number
    if(empty($errors)) {
      $sql = "SELECT count(*) from $this->_tableName where item_number = %s and id != %d";
      $sql = $this->_db->prepare($sql, $this->item_number, $this->id);
      $count = $this->_db->get_var($sql);
      if($count > 0) {
        $this->addError('item_number', __("The item number must be unique","cart66"), 'subscription_item_number');
      }
    }
    
    if(empty($this->name)) {
      $this->addError('name', __("Subscription name is required","cart66"), 'subscription_name');
    }
    
    if(empty($this->featureLevel)) {
      $this->addError('feature_level', __("Feature level is required","cart66"), 'subscription_feature_level');
    }
    
    if($this->price <= 0  || !is_numeric($this->price)) {
      $this->addError('price', __("The price must be greater than zero","cart66"), 'subscription_price');
    }
    
    if($this->billing_interval <= 0 || !is_numeric($this->billing_interval)) {
      $this->addError('billing_interval', __("The billing interval must be a number greater than zero","cart66"), 'subscription_billing_interval');
    }
    
    return $this->getErrors();
  }
  
  public function getPriceDescription($showTrialPricing = true, $trialMessage='') {
    $description = '';
    if($this->id > 0) {
      if(empty($this->priceDescription)) {
        $oneTime = $this->setupFee;
        if($oneTime > 0) {
          $description .= CART66_CURRENCY_SYMBOL . number_format($oneTime, 2) . "&nbsp;(one&nbsp;time) + ";
        }

        if($this->offerTrial && $showTrialPricing) {
          $description .= ' <span class="trialDescription">' . $this->getTrialPriceDescription() .  ' ' . $trialMessage . '</span> then ';
        }

        $description .= CART66_CURRENCY_SYMBOL . $this->price . ' / ';
        if($this->billingInterval > 1) {
          $description .= $this->billingInterval . '&nbsp;' . $this->billingIntervalUnit;
        }
        else {
          $description .= rtrim($this->billingIntervalUnit, 's');
        }
      }
      else {
        $description = $this->priceDescription;
      }
    }
    return $description;
  }
  
  public function getTrialPriceDescription() {
    $description = 'No trial';
    if($this->offerTrial > 0) {
      $description = CART66_CURRENCY_SYMBOL . number_format($this->trialPrice, 2) . ' / ';
      if($this->trialPeriod > 1) {
        $description .= $this->trialPeriod . '&nbsp;';
      }
      $description .= $this->getTrialPeriodUnit();
    }
    return $description;
  }
  
  public function getBillingCycleDescription() {
    $cycles = $this->billingCycles;
    if($cycles < 1) {
      $cycles = "Continuous billing";
    }
    elseif($cycles == 1) {
      $cycles .= '&nbsp;payment';
    }
    else {
      $cycles .= '&nbsp;payments';
    }
    return $cycles;
  }
  
  public function getBillingIntervalUnit() {
    $unit = $this->billingIntervalUnit;
    if($this->billingInterval == 1) {
      $unit = str_replace('s', '', $unit);
    }
    return $unit;
  }
  
  public function getTrialPeriodUnit() {
    $unit = $this->trialPeriodUnit;
    if($this->trialPeriod == 1) {
      $unit = str_replace('s', '', $this->trialPeriodUnit);
    }
    return $unit;
  }
  
  public function getStartRecurringDescription() {
    $description = 'At Checkout';
    if($this->startRecurringNumber == 1) {
      $description = $this->startRecurringNumber . ' ' . rtrim($this->startRecurringUnit, 's');
    }
    elseif($this->startRecurringNumber > 1) {
      $description = $this->startRecurringNumber . ' ' . $this->startRecurringUnit;
    }
    return $description;
  }
  
  /**
   * Return a string suitable for use as a parameter to strtotime()
   * For example: +1 months
   * 
   * @return string
   */
  public function getStartTimeFormula() {
    $formula = '+5 minutes';
    if($this->startRecurringNumber > 0) {
      $formula = '+' . $this->startRecurringNumber . ' ' . $this->startRecurringUnit;
    }
    return $formula;
  }
  
  /**
   * Return an array of Cart66PayPalSubscription objects representing all of the defined subscription plans.
   * 
   * @return array
   */
  public function getSubscriptionPlans() {
    $subscriptions = $this->getModels("where is_paypal_subscription=1");
    return $subscriptions;
  }
    
}