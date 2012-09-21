<?php
/**
 * Failure:
 * Array (
 *     [TIMESTAMP] => 2009-12-09T02:13:43Z
 *     [CORRELATIONID] => cade14d44146b
 *     [ACK] => Failure
 *     [VERSION] => 60
 *     [BUILD] => 1073465
 *     [L_ERRORCODE0] => 10759
 *     [L_SHORTMESSAGE0] => Gateway Decline
 *     [L_LONGMESSAGE0] => This transaction cannot be processed. Please enter a valid credit card number and type.
 *     [L_SEVERITYCODE0] => Error
 *     [AMT] => 10.00
 *     [CURRENCYCODE] => USD
 * )
 * 
 * Success:
 * Array (
 *     [TIMESTAMP] => 2009-12-09T02:20:10Z
 *     [CORRELATIONID] => 3b777d1b6490e
 *     [ACK] => Success
 *     [VERSION] => 60
 *     [BUILD] => 1113251
 *     [AMT] => 10.00
 *     [CURRENCYCODE] => USD
 *     [AVSCODE] => X
 *     [CVV2MATCH] => M
 *     [TRANSACTIONID] => 3C577405EM5115349
 * )
 *
 * Test Credit Card Numbers: 
 * VISA:
 *   4300383194735048
 *   4916810462305860
 *   4916451735830564
 *   4916102912485884
 *   4539752166674034
 *   4929851053066458
 *   4929942519259686
 *   4994640874183604
 *   4532397127865128
 *   4716659742341142
 */
class Cart66PayPalPro extends Cart66PayPalExpressCheckout {
  
  protected $_response; // The response for attempting a transaction via PayPalPro gateway
  
  public function __construct() {
    $username = Cart66Setting::getValue('paypalpro_api_username');
    $password = Cart66Setting::getValue('paypalpro_api_password');
    $signature = Cart66Setting::getValue('paypalpro_api_signature');
    if(!($username && $password && $signature)) {
      throw new Cart66Exception('Invalid PayPal Pro Configuration', 66502);
    }
    parent::__construct();
  }
  
  public function getCreditCardTypes() {
    $cardTypes = array();
    $setting = new Cart66Setting();
    $cards = Cart66Setting::getValue('auth_card_types');
    if($cards) {
      $cards = explode('~', $cards);
      if(in_array('mastercard', $cards)) {
        $cardTypes['MasterCard'] = 'mastercard';
      }
      if(in_array('visa', $cards)) {
        $cardTypes['Visa'] = 'visa';
      }
      if(in_array('amex', $cards)) {
        $cardTypes['American Express'] = 'amex';
      }
      if(in_array('discover', $cards)) {
        $cardTypes['Discover'] = 'discover';
      }
    }
    return $cardTypes;
  }

  public function initCheckout($total) {
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] PayPal Pro Checkout init total: $total");
    
    $this->setCreditCardData();
    $this->setPayerInfo();
    $this->setPayerName();
    $this->setAddress();
    $this->setShipToAddress();
    
    // Calculate tax
    $tax = $this->getTaxAmount();
    
    // Calculate total cost of all items in cart, not including tax and shipping
    $itemSubTotal = Cart66Session::get('Cart66Cart')->getSubTotal() - Cart66Session::get('Cart66Cart')->getDiscountAmount() - Cart66Session::get('Cart66Cart')->getSubscriptionAmount();
    $itemTotal = number_format($itemSubTotal, 2, '.', '');
    $itemTotal = ($itemTotal < 0) ? 0 : $itemTotal;
    
    // Calculate shipping costs
    $shipping = Cart66Session::get('Cart66Cart')->getShippingCost();
    
    // Set payment information
    // 'NOTIFYURL' => $ipnUrl
    $currencyCode = Cart66Setting::getValue('currency_code');
    if($currencyCode == false) { $currencyCode = 'USD'; }
    $payment = array(
      'AMT' => $total,
      'ITEMAMT' => $itemTotal,
      'SHIPPINGAMT' => $shipping,
      'TAXAMT' => $tax,
      'CURRENCYCODE' => $currencyCode
    );
    
    // Add cart items to PayPal
    $items = Cart66Session::get('Cart66Cart')->getItems(); // An array of Cart66CartItem objects
    foreach($items as $i) {
      
      if($i->isSpreedlySubscription()) {
        $amount = $i->getBaseProductPrice();
      }
      else {
        $amount = $i->getProductPrice();
      }
      
      $itemData = array(
        'NAME' => $i->getFullDisplayName(),
        'AMT' => $amount,
        'NUMBER' => $i->getItemNumber(),
        'QTY' => $i->getQuantity()
      );
      $this->addItem($itemData);
    }
    
    // Add a coupon discount if needed
    $discount = Cart66Session::get('Cart66Cart')->getDiscountAmount();
    if($discount > 0) {
      $negDiscount = 0 - $discount;
      $itemData = array(
        'NAME' => 'Discount',
        'AMT' => $negDiscount,
        'NUMBER' => 'DSC',
        'QTY' => 1
      );
      $this->addItem($itemData);
    }
    
    // Store the shipping price as an "item" if the item total is $0.00. Otherwise paypal will not accept the transaction.
    if($payment['ITEMAMT'] == 0 && $payment['SHIPPINGAMT'] > 0) {
      $payment['ITEMAMT'] = $payment['SHIPPINGAMT'] + (($itemSubTotal < 0) ? $itemSubTotal : 0);
      $itemData = array(
        'NAME' => 'Shipping',
        'AMT' => $payment['SHIPPINGAMT'],
        'NUMBER' => 'SHIPPING',
        'QTY' => 1
      );
      $this->addItem($itemData);
      $payment['SHIPPINGAMT'] = 0;
    }
    
    $this->setPaymentDetails($payment);
  }
  
  public function doSale() {
    $transactionId = false;
    $this->_response = $this->DoDirectPayment();
    $result = strtoupper($this->_response['ACK']);
    if('SUCCESS' == $result || 'SUCCESSWITHWARNING' == $result || isset($this->_response['TRANSACTIONID'])) {
      $transactionId = $this->_response['TRANSACTIONID'];
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] PayPal Transaction Success: " . print_r($this->_response, true));
    }
    else {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] PayPal Transaction Failure: " . print_r($this->_response, true));
    }
    return $transactionId;
  }
  
  public function getTransactionResponseDescription() {
    $description['errormessage'] = $this->_response['L_SHORTMESSAGE0'] . ': ' . $this->_response['L_LONGMESSAGE0'];
    $description['errorcode'] = $this->_response['L_ERRORCODE0'];
    return $description;
  }
  
  /**
   * Set the credit card data.
   *  
   * The passed in array must contain the following keys
   *  -- CREDITCARDTYPE: (Visa,Mastercard,Discover,Amex)
   *  -- ACCT: Credit card number
   *  -- EXPDATE: format MMYYYY
   *  -- CVV2: Card verification value. Character length for Visa, MasterCard, and Discover: exactly three digits.
   *     Character length for American Express: exactly four digits.To comply with credit card processing regulations, 
   *     you must not store this value after a transaction has been completed.
   */
  public function setCreditCardData($data=null) {
    if(!is_array($data)) {
      $p = $this->getPayment();
      $data = array(
        'CREDITCARDTYPE' => $p['cardType'],
        'ACCT' => $p['cardNumber'], //'4532497022010364',
        'EXPDATE' => $p['cardExpirationMonth'] . $p['cardExpirationYear'],
        'CVV2' => $p['securityId']
      );
    }
    $this->_creditCardData = $data;
  }
  
  /**
   * Set the payer information data.
   * 
   * The passed in array must contain the following keys
   *  -- EMAIL: Email address of the payer
   *  -- PAYERID: Unique PayPal customer account identification number. 13 alphanumeric chars
   *  -- PAYERSTATUS: verified or unverified
   *  -- COUNTRYCODE: Payer's country of residence. Two chars
   *  -- BUSINESS: Payer's business name. 127 chars.
   */
  public function setPayerInfo($data=null) {
    if(!is_array($data)) {
      $p = $this->getPayment();
      $b = $this->getBilling();
      $data = array(
        'EMAIL' => $p['email']
      );
      // 'COUNTRYCODE' => $b['country']
    }
    $this->_payerInfo = $data;
  }
  
  /**
   * Set the payer name information
   * 
   * The passed in data must contain the following keys.
   *  -- SALUTATION: Payer's salutation. 20 chars.
   *  -- FIRSTNAME: Payer's first name
   *  -- MIDDLENAME: Payer's middle name
   *  -- LASTNAME: Payer's last name
   *  -- SUFFIX: Payer's suffix
   */
  public function setPayerName($data=null) {
    if(!is_array($data)) {
      $b = $this->getBilling();
      $data = array(
        'FIRSTNAME' => $b['firstName'],
        'LASTNAME' => $b['lastName']
      );
    }
    $this->_payerName = $data;
  }
  
  /**
   * Set the address of the payer.
   * 
   * The passed in array must contain the following keys.
   *  -- STREET: First street address (required)
   *  -- STREET2: Second Street address
   *  -- CITY (required)
   *  -- STATE (required)
   *  -- COUNTRYCODE (required)
   *  -- ZIP (required)
   *  -- SHIPTOPHONENUM
   */
  public function setAddress($data=null) {
    if(!is_array($data)) {
      $b = $this->getBilling();
      $p = $this->getPayment();
      $data = array(
        'STREET' => $b['address'],
        'STREET2' => $b['address2'],
        'CITY' => $b['city'],
        'STATE' => $b['state'],
        'COUNTRYCODE' => $b['country'],
        'ZIP' => $b['zip'],
        'SHIPTOPHONENUM' => $p['phone']
      );
    }
    $this->_payerAddress = $data;
  }
  
  /**
   * Set the ship to address of the payer.
   * 
   * The passed in array must contain the following keys.
   *  -- SHIPTONAME: First Name and Last Name
   *  -- SHIPTOSTREET: First street address (required)
   *  -- SHIPTOSTREET2: Second Street address
   *  -- SHIPTOCITY (required)
   *  -- SHIPTOSTATE (required)
   *  -- SHIPTOCOUNTRYCODE (required)
   *  -- SHIPTOZIP (required)
   *  -- SHIPTOPHONENUM
   */
  public function setShipToAddress($data=null) {
    if(!is_array($data)) {
      $s = $this->getShipping();
      $data = array(
        'SHIPTONAME' => $s['firstName'] . ' ' . $s['lastName'],
        'SHIPTOSTREET' => $s['address'],
        'SHIPTOSTREET2' => $s['address2'],
        'SHIPTOCITY' => $s['city'],
        'SHIPTOSTATE' => $s['state'],
        'SHIPTOCOUNTRYCODE' => $s['country'],
        'SHIPTOZIP' => $s['zip']
      );
    }
    $this->_payerShipToAddress = $data;
  }
  
  public function DoDirectPayment() {
    $this->_requestFields = array(
      'METHOD' => 'DoDirectPayment',
      'PAYMENTACTION' => 'Sale',
      'CURRENCYCODE' => CURRENCY_CODE,
      // Change the following depending on IPV6
      'IPADDRESS' => $_SERVER['REMOTE_ADDR']
    );
    $nvp = $this->_buildNvpStr();
    
    $nvpLog = str_replace('&', "\n", $nvp);
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] API END POINT: $this->_apiEndPoint \nNVP: $nvpLog");
    
    $result = $this->_decodeNvp($this->_sendRequest($this->_apiEndPoint, $nvp));
    return $result;
  }
  

  
  /**
   * Create a recurring payments profile
   * The initial payment is charged with DoExpressCheckout so that the first payment is received immediately
   */
  public function CreateRecurringPaymentsProfile($token, $cartItem, $index) {
    $plan = new Cart66PayPalSubscription($cartItem->getPayPalSubscriptionId());
    $queryString = array(
      'TOKEN' => $token,
      'METHOD' => 'CreateRecurringPaymentsProfile',
      'PROFILESTARTDATE' => date('Y-m-d\Tg:i:s', strtotime($plan->getStartTimeFormula(), Cart66Common::localTs())),
      'BILLINGPERIOD' => ucwords(rtrim($plan->billingIntervalUnit, 's')),
      'BILLINGFREQUENCY' => $plan->billingInterval,
      'TOTALBILLINGCYCLES' => $plan->billingCycles,
      'AMT' => $plan->price,
      'INITAMT' => 0,
      'CURRENCYCODE' => CURRENCY_CODE,
      'FAILEDINITAMTACTION' => 'CancelOnFailure',
      'L_BILLINGTYPE' . $index => 'RecurringPayments',
      'DESC' => $plan->name . ' ' . str_replace('&nbsp;', ' ', strip_tags($plan->getPriceDescription($plan->offerTrial > 0, '(trial)'))),
    );
    
    if($plan->offerTrial == 1) {
      $queryString['TRIALBILLINGPERIOD'] = ucwords(rtrim($plan->trialPeriodUnit, 's'));
      $queryString['TRIALBILLINGFREQUENCY'] = $plan->trialPeriod;
      $queryString['TRIALAMT'] = $plan->trialPrice;
      $queryString['TRIALTOTALBILLINGCYCLES'] = $plan->trialCycles;
    }
    
    $params = array();
    $queryString = array_merge($this->_apiData, $queryString);
    foreach($queryString as $key => $value) {
      $params[] = $key . '=' . urlencode($value);
    }
    $nvp = implode('&', $params);
    
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Creating recurring payments profile NVP: " .  str_replace('&', "\n", $nvp));
    $result = $this->_decodeNvp($this->_sendRequest($this->_apiEndPoint, $nvp));
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Creating recurring payments raw result: " . print_r($result, true));
    return $result;
  }
  
  /**
   * Manage the status of the recurring payments profile identified by the profileId
   * Action may be: Cancel, Suspend, or Reactivate.
   *   Cancel - Only profiles in Active or Suspended state can be canceled.
   *   Suspend - Only profiles in Active state can be suspended.
   *   Reactivate - Only profiles in a suspended state can be reactivated.
   */
  public function ManageRecurringPaymentsProfileStatus($profileId, $action, $note) {
    $this->_requestFields = array(
      'METHOD' => 'ManageRecurringPaymentsProfileStatus',
      'PROFILEID' => $profileId,
      'ACTION' => $action,
      'NOTE' => $note
    );
    
    $nvp = $this->_buildNvpStr();
    Cart66Common::log("Manage recurring payments profile request NVP: " . str_replace('&', "\n", $nvp));
    $result = $this->_decodeNvp($this->_sendRequest($this->_apiEndPoint, $nvp));
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Manage recurring payments profile response: " . print_r($result, true));
    return $result;
  }
  

 
}
