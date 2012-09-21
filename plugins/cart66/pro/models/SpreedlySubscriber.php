<?php
require_once dirname(__FILE__) . '/SpreedlyCommon.php';
require_once dirname(__FILE__) . '/SpreedlyException.php';
require_once dirname(__FILE__) . '/SpreedlyXmlObject.php';

class SpreedlySubscriber extends SpreedlyXmlObject {
  
  
  public function hydrate(array $data, $wrapper='subscriber') {
    return parent::hydrate($data, $wrapper);
  }
  
  public function create(array $subscriberData) {
    // Remove attributes that are not allowed to be changed
    if(isset($subscriberData['token'])) { unset($subscriberData['token']); }
    if(isset($subscriberData['feature-level'])) { unset($subscriberData['feature-level']); }
    
    $subscriberXml = Cart66Common::arrayToXml($subscriberData, 'subscriber');
    $result = SpreedlyCommon::curlRequest('/subscribers.xml', 'post', $subscriberXml);
    if($result->code == '201') {
      $data = new SimpleXmlElement($result->response);
      $this->setData($data);
      $this->updateLocalAccount();
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Successfully created spreedly subscriber: " . print_r($this->toArray(), true));
    }
    elseif($result->code == '403') {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . '] Tried to create spreedly subscriber but failed. ' . 
        print_r($subscriberData, true). "\nResult code: [" . $result->code . '] ' . $result->response);
      $sub = self::find($subscriberData['customer-id']);
      $this->setData($sub->getData());
    }
    else {
      throw new SpreedlyException("Spreedly Subscriber: Unable to create subscriber.\n" . $result->response, $result->code);
    }
  }
  
  public function assignFreeTrialPlan($subscriptionId) {
    if($this->customerId > 0) {
      $xml = "<subscription-plan><id>$subscriptionId</id></subscription-plan>";
      $result = SpreedlyCommon::curlRequest('/subscribers/' . $this->customerId . '/subscribe_to_free_trial.xml', 'post', $xml);
      if($result->code != '200') {
        throw new SpreedlyException('Spreedly Subscriber: Unable to assign free trial plan. ' . $result->response, 66005);
      }
    }
    else {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Failed to assign free trial plan to subscriber: " . print_r($this->toArray(), true));
      throw new SpreedlyException('Spreedly Subscriber: Unable to assign free trial plan because subscriber does not exist. ' . $result->response, 66006);
    }
  }
  
  public function addFee($name, $description, $group, $amount) {
    $fee = array(
      'name' => $name,
      'description' => $description,
      'group' => $group,
      'amount' => $amount
    );
    $feeXml = Cart66Common::arrayToXml($fee, 'fee');
    $customerId = (int)$this->customerId;
    echo "Calling: " . "/subscribers/{$customerId}/fees.xml\n";
    $result = SpreedlyCommon::curlRequest("/subscribers/{$customerId}/fees.xml", 'post', $feeXml);
    if($result->code == '201') {
      echo "\nSuccessfully added a fee to the subscriber.";
      echo "\nCode: " . $result->code;
      echo "\n" . $result->response;      
    }
    else {
      throw new SpreedlyException("Spreedly Subscriber: Unable to add fees to customer $customerId\n" . $result->response, 66004);
    }
  }
  
  public static function find($customerId) {
    $result = SpreedlyCommon::curlRequest("/subscribers/{$customerId}.xml", "get");
    if($result->code == '200') {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Spreedly Customer XML:\n" . $result->response);
      $xmlElement = new SimpleXMLElement($result->response);
      $subscriber = new SpreedlySubscriber();
      $subscriber->setData($xmlElement);
      return $subscriber;
    }
    else {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Tried to find spreedly subscriber but failed to locate subscriber id: $customerId");
      throw new SpreedlyException("Spreedly Subscriber: Could not load subscriber.", 66007);
    }
  }
  
  public function toArray($prune=false, $omit=null) {
    return parent::toArray($prune, $omit);
  }
  
  public function updateLocalAccount() {
    $accountSub = new Cart66AccountSubscription();
    if($accountSub = $accountSub->getOne("WHERE account_id = $this->customerId")) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Hydrating account subscription with: " . print_r($this, true));
      $accountSub->hydrate($this);
      $accountSub->save();
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Updated local account information: " . print_r($accountSub->getData(), true));
    }
    else {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Unable to update local account with new spreedly information - looking for account id: $this->customerId");
    }
  }
  
  /**
   * All fields are optional - any field that is not provided will not be updated.
   */
  public static function updateRemoteAccount($customerId, $subscriberData) {
    $subscriberXml = Cart66Common::arrayToXml($subscriberData, 'subscriber');
    $result = SpreedlyCommon::curlRequest("/subscribers/{$customerId}.xml", "put", $subscriberXml);
    if($result->code != '200') {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Spreedly Subscriber: Account information could not be updated. " . 
        $result->response . "\nCode: $result->code");
      throw new SpreedlyException('Spreedly Subscriber: Account information could not be updated.', $result->code);
    }
  }
  
}




/*
   <subscriber>
      <active-until type="datetime" nil="true"></active-until>
      <billing-first-name nil="true"></billing-first-name>
      <billing-last-name nil="true"></billing-last-name>
      <created-at type="datetime">2010-07-07T21:20:39Z</created-at>
      <customer-id>7388</customer-id>
      <eligible-for-free-trial type="boolean">true</eligible-for-free-trial>
      <email nil="true"></email>
      <grace-until type="datetime" nil="true"></grace-until>
      <lifetime-subscription type="boolean">false</lifetime-subscription>
      <on-gift type="boolean" nil="true"></on-gift>
      <on-metered type="boolean">false</on-metered>
      <on-trial type="boolean">false</on-trial>
      <ready-to-renew type="boolean">false</ready-to-renew>
      <ready-to-renew-since type="datetime" nil="true"></ready-to-renew-since>
      <recurring type="boolean" nil="true"></recurring>
      <screen-name></screen-name>
      <store-credit type="decimal">0.0</store-credit>
      <store-credit-currency-code>USD</store-credit-currency-code>
      <token>2b83780b907248eb1e305af11d2c92f7b1f7ec76</token>
      <updated-at type="datetime">2010-07-07T21:20:39Z</updated-at>
      <card-expires-before-next-auto-renew type="boolean">false</card-expires-before-next-auto-renew>
      <subscription-plan-name></subscription-plan-name>
      <active type="boolean">false</active>
      <in-grace-period type="boolean" nil="true"></in-grace-period>
      <feature-level type="string" nil="true"></feature-level>
      <payment-account-on-file type="boolean">false</payment-account-on-file>
      <invoices type="array"/>
    </subscriber>
 */