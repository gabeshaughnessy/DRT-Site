<?php
require_once dirname(__FILE__) . '/SpreedlyCommon.php';
require_once dirname(__FILE__) . '/SpreedlyException.php';
require_once dirname(__FILE__) . '/SpreedlyXmlObject.php';

class SpreedlySubscription extends SpreedlyXmlObject {
  
  /**
   * @var array
   * An array of SpreedlySubscription objects
   */
  protected static $_subscriptionPlans;
  
  public function __construct($id=null) {
    $this->_subscriptionPlans = array();
    if(isset($id) && is_numeric($id)) {
      $this->load($id);
    }
  }
  
  public function loadPlans() {
    $this->_subscriptionPlans = self::getSubscriptions();
  }
  
  public function load($id) {
    // Load plans if not already loaded
    if(empty($this->_subscriptionPlans)) {
      $this->loadPlans();
    }
    
    foreach($this->_subscriptionPlans as $plan) {
      if($plan->id == $id) {
        $this->setData($plan->getData());
      }
    }
  }
  
  public function getPriceDescription() {
    $product = new Cart66Product();
    $spreedlyProduct = $product->getOne("where spreedly_subscription_id = $this->id");
    if(!empty($spreedlyProduct->priceDescription)) {
      $out = $spreedlyProduct->priceDescription;
    }
    else {
      $price = $this->price;
      $out = CART66_CURRENCY_SYMBOL . number_format($price, 2) . ' / ' . $this->terms;
      if($this->hasFreeTrial()) {
        $duration = $this->chargeLaterDurationQuantity . '&nbsp;' . $this->chargeLaterDurationUnits;
        $out .= " <span class='Cart66FreePeriod'>(first $duration free)</span>";
      }
    }
    
    return $out;
  }
  
  public function hasFreeTrial() {
    $hasFreeTrial = false;
    if($this->chargeAfterFirstPeriod == 'true') {
      $hasFreeTrial = true;
    }
    return $hasFreeTrial;
  }
  
  public function _getPrice() {
    return (float)$this->_data->price;
  }
  
  /**
   * Return an array of enabled spreedly subscriptions
   * 
   * @return array
   */
  public static function getSubscriptions() {
    if(empty(self::$_subscriptionPlans)) {
      $result = SpreedlyCommon::curlRequest("/subscription_plans.xml", "get");
      if($result->code == '200') {
        $subscriptions = array();
        $plans = new SimpleXmlElement($result->response);
        foreach($plans as $plan) {
          $subscription = new SpreedlySubscription();
          $subscription->setData($plan);
          /// Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Spreedly subscription enabled: " . $subscription->enabled);
          if('true' == (string)$subscription->enabled) {
            $subscriptions[] = $subscription;
          }
        }
        self::$_subscriptionPlans = $subscriptions;
      }
      else {
        throw new SpreedlyException('Spreedly Subscription: Unable to retrieve remote list of subscriptions', 66003);
      }
    }
    return self::$_subscriptionPlans;
  }
  
}



/*  
  <subscription-plan>
    <amount type="decimal">10.0</amount>
    <charge-after-first-period type="boolean">true</charge-after-first-period>
    <charge-later-duration-quantity type="integer">7</charge-later-duration-quantity>
    <charge-later-duration-units>days</charge-later-duration-units>
    <created-at type="datetime">2010-10-19T18:20:40Z</created-at>
    <currency-code>USD</currency-code>
    <description></description>
    <duration-quantity type="integer">1</duration-quantity>
    <duration-units>months</duration-units>
    <enabled type="boolean">true</enabled>
    <feature-level>priority</feature-level>
    <force-recurring type="boolean">true</force-recurring>
    <id type="integer">7653</id>
    <minimum-needed-for-charge type="decimal">0.0</minimum-needed-for-charge>
    <name>Priority Support and Upgrades</name>
    <needs-to-be-renewed type="boolean">true</needs-to-be-renewed>
    <plan-type>regular</plan-type>
    <return-url>http://oliver.phpoet.com/spreedly-thanks</return-url>
    <updated-at type="datetime">2010-10-19T18:20:40Z</updated-at>
    <terms type="string">1 month</terms>
    <price type="decimal">10.0</price>
  </subscription-plan>
  
  Free Subscription 
  [amount] => 0.0
  [charge-after-first-period] => false
  [charge-later-duration-quantity] => 
  [charge-later-duration-units] => 
  [created-at] => 2010-11-09T21:46:46Z
  [currency-code] => USD
  [description] => This is a free account
  [duration-quantity] => 1
  [duration-units] => days
  [enabled] => true
  [feature-level] => free
  [force-recurring] => false
  [id] => 7972
  [minimum-needed-for-charge] => 0.0
  [name] => Free Support
  [needs-to-be-renewed] => true
  [plan-type] => free_trial
  [return-url] => http://oliver.phpoet.com/spreedly-thanks
  [updated-at] => 2010-11-09T21:46:46Z
  [terms] => 1 day
  [price] => 0.0
*/