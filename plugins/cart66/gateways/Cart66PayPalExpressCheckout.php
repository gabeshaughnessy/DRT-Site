<?php
class Cart66PayPalExpressCheckout extends Cart66GatewayAbstract {
  
  protected $_apiData;
  protected $_apiEndPoint;
  protected $_apiExpressCheckoutUrl;
  protected $_creditCardData;
  protected $_payerInfo;
  protected $_payerName;
  protected $_payerAddress;
  protected $_payerShipToAddress;
  protected $_paymentDetails;
  protected $_requestFields;
  protected $_ecUrls;
  protected $_items = array();
  
  
  public function __construct() {
    parent::__construct();
    
    $mode = 'LIVE';
    if(Cart66Setting::getValue('paypal_sandbox')) {
      $mode = 'TEST';
    }

    $this->clearErrors();
    
    // Set end point
    $apiEndPoint = 'https://api-3t.paypal.com/nvp';
    if("TEST" == $mode) {
      $apiEndPoint = 'https://api-3t.sandbox.paypal.com/nvp';
    }
    $this->_apiEndPoint = $apiEndPoint;
    
    // Set express checkout url
    $expressCheckoutUrl = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';
    if("TEST" == $mode) {
      $expressCheckoutUrl = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
    }
    $this->_apiExpressCheckoutUrl = $expressCheckoutUrl;
    
    // Set api data
    $this->_apiData['USER'] = Cart66Setting::getValue('paypalpro_api_username');
    $this->_apiData['PWD'] = Cart66Setting::getValue('paypalpro_api_password');
    $this->_apiData['SIGNATURE'] = Cart66Setting::getValue('paypalpro_api_signature');
    $this->_apiData['VERSION'] = '65';

    if(!($this->_apiData['USER'] && $this->_apiData['PWD'] && $this->_apiData['SIGNATURE'])) {
      throw new Cart66Exception('Invalid PayPal Express Configuration', 66501);
    }
  }
  
  
  public function getCreditCardTypes() {
    // Express checkout does not use credit cards
    return array();
  }
  
  public function initCheckout($total) {
    // Express checkout doesn't require any initialization from this function
    return 0;
  }
  
  public function doSale() {
    // Express checkout has a multi-step sale process and is implemented apart from this function
    return 0;
  }
  
  public function getTransactionResponseDescription() {
    // Express checkout handles errors in a way that is implemented without this function.
    return '';
  }
  
  
  /**
   * Add an item from the shopping cart
   * 
   * The passed in array should contain the following keys.
   *  -- NAME: Item name
   *  -- AMT: The price of the item
   *  -- NUMBER: Item number
   *  -- QTY: Item quantity
   *  -- TAXAMT: Item sales tax
   */
  public function addItem(array $data) {
    $this->_items[] = $data;
  }
 
  /**
   * Set the Express Checkout required URLs.
   * 
   * The passed in array must contain the following to keys.
   *  -- RETURNURL: URL to which the customer’s browser is returned after choosing to pay with PayPal.
   *  -- CANCELURL: URL to which the customer is returned if he does not approve the use of PayPal to pay you.
   */
  public function setEcUrls(array $data) {
    $this->_ecUrls = $data;
  }
  
  /**
   * Set the payment details
   * 
   * The passed in array must contain the following keys.
   *  -- AMT: The total cost to the customer (required)
   *  -- CURRENCYCODE: default USD
   *  -- ITEMAMT: Sum of cost of all items in this order.
   *  -- SHIPPINGAMT: Total shipping costs for this order.
   *  -- INSURANCEAMT: Total shipping insurance costs for this order.
   *  -- SHIPPINGDISCOUNT: Shipping discount for this order, specified as a negative number.
   *  -- INSURANCEOPTIONOFFERED: true or false
   *  -- HANDLINGAMT: Total handling costs for this order.
   *  -- TAXAMT: Sum of tax for all items in this order.
   *  -- DESC: Description of items the customer is purchasing. (127 chars)
   *  -- CUSTOM: A free-form field for your own use. (256 alphanumeric chars)
   *  -- INVNUM: Your own invoice or tracking number.
   *  -- BUTTONSOURCE: An identification code for use by third-party applications to identify transactions.
   *  -- NOTIFYURL: Your URL for receiving Instant Payment Notification (IPN) about this transaction.
   *  -- NOTETEXT: Note to seller
   *  -- TRANSACTIONID: Transaction identification number of the transaction that was created.
   *  -- ALLOWEDPAYMENTMETHOD: InstantPaymentOnly
   */
  public function setPaymentDetails(array $data) {
    $this->_paymentDetails = $data;
  }
  
  public function SetExpressCheckout() {
    $this->_requestFields = array(
      'METHOD' => 'SetExpressCheckout',
      'PAYMENTACTION' => 'Sale'
    );
    if(CART66_PRO && !Cart66Setting::getValue('disable_landing_page')){
      $this->_requestFields['LANDINGPAGE'] = 'Billing';
    }

    if(CART66_PRO && Cart66Setting::getValue('express_force_paypal')){
      $this->_requestFields['SOLUTIONTYPE'] = 'Sole';
    }

    $nvp = $this->_buildNvpStr();
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Set Express Checkout Request NVP: " . str_replace('&', "\n", $nvp));
    $result = $this->_decodeNvp($this->_sendRequest($this->_apiEndPoint, $nvp));
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] SetExpressCheckout result: " . print_r($result, true));
    return $result;
  }
  
  public function getExpressCheckoutUrl($token) {
    return $this->_apiExpressCheckoutUrl . urlencode($token);
  }
  
  public function GetExpressCheckoutDetails($token) {
    $token = urlencode($token);
    $params = array();
    foreach($this->_apiData as $key => $value) {
      $valuey = urlencode($value);
      $params[] = "$key=$value";
    }
    $nvp = implode('&', $params);
    $nvp .= "&METHOD=GetExpressCheckoutDetails&TOKEN=$token";
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] GetExpressCheckoutDetails NVP: " . str_replace('&', "\n", $nvp));
    $result = $this->_decodeNvp($this->_sendRequest($this->_apiEndPoint, $nvp));
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] GetExpressCheckoutDetails result: " . print_r($result, true));
    return $result;
  }
  
  /**
   * A successful return result looks like this:
   * Array
   * (
   *     [TOKEN] => EC-2K187552EW5520044
   *     [SUCCESSPAGEREDIRECTREQUESTED] => false
   *     [TIMESTAMP] => 2009-12-13T22:56:18Z
   *     [CORRELATIONID] => f05cc1a35c955
   *     [ACK] => Success
   *     [VERSION] => 60
   *     [BUILD] => 1077585
   *     [TRANSACTIONID] => 7PD84087WY2993410
   *     [TRANSACTIONTYPE] => expresscheckout
   *     [PAYMENTTYPE] => instant
   *     [ORDERTIME] => 2009-12-13T22:56:17Z
   *     [AMT] => 18.00
   *     [FEEAMT] => 0.82
   *     [TAXAMT] => 0.00
   *     [CURRENCYCODE] => USD
   *     [PAYMENTSTATUS] => Completed
   *     [PENDINGREASON] => None
   *     [REASONCODE] => None
   *     [PROTECTIONELIGIBILITY] => Eligible
   * )
   */
  public function DoExpressCheckout($token, $payerId, $itemAmount, $shipping, $tax=0) {    
    $amount = $itemAmount + $shipping + $tax;
    $promotion = Cart66Session::get('Cart66Promotion');
    $discount = Cart66Session::get('Cart66Cart')->getDiscountAmount();
    $itemTotal = $itemAmount;
    
    if(is_object($promotion) && $promotion->apply_to == 'total') {
      $itemTotal = Cart66Session::get('Cart66Cart')->getNonSubscriptionAmount();
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
      // make sure shipping is not negative
      $shipping = ($shipping < 0) ? 0 : $shipping;
      $discount = 0;
    }
    
    if(isset($itemTotal) && $itemTotal == 0 && $shipping > 0) {
      
    }
    
    $this->_requestFields = array(
      'METHOD' => 'DoExpressCheckoutPayment',
      'PAYMENTACTION' => 'Sale',
      'TOKEN' => $token,
      'PAYERID' => $payerId,
      'AMT' => number_format($amount, 2, '.', ''),
      'ITEMAMT' => number_format($itemTotal, 2, '.', ''),
      'SHIPPINGAMT' => number_format($shipping, 2, '.', ''),
      'TAXAMT' => number_format($tax, 2, '.', ''),
      'CURRENCYCODE' => CURRENCY_CODE
    );
    $nvp = $this->_buildNvpStr();
    
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Do Express Checkout Request NVP: " . str_replace('&', "\n", $nvp));
    
    $result = $this->_decodeNvp($this->_sendRequest($this->_apiEndPoint, $nvp));
    return $result;
  }
  
  public function populatePayPalCartItems() {
    $items = Cart66Session::get('Cart66Cart')->getItems(); // An array of Cart66CartItem objects
    $promotion = Cart66Session::get('Cart66Promotion');
    $shipping = Cart66Session::get('Cart66Cart')->getShippingCost();
    
    foreach($items as $i) {
      if($i->isPayPalSubscription()) {
        $plan = $i->getPayPalSubscription();
        $itemData = array(
          'BILLINGAGREEMENTDESCRIPTION' => $plan->name . ' ' . str_replace('&nbsp;', ' ', strip_tags($plan->getPriceDescription($plan->offerTrial > 0, '(trial)'))),
        );
        $this->addItem($itemData);

        $chargeAmount = $i->getProductPrice();
        if($chargeAmount > 0) {
          $itemData = array(
            'NAME' => $i->getFullDisplayName(),
            'AMT' => $chargeAmount,
            'NUMBER' => $i->getItemNumber(),
            'QTY' => $i->getQuantity()
          );
        }
        $this->addItem($itemData);
      }
      else {
        $itemData = array(
          'NAME' => $i->getFullDisplayName(),
          'AMT' => $i->getProductPrice(),
          'NUMBER' => $i->getItemNumber(),
          'QTY' => $i->getQuantity()
        );
        $this->addItem($itemData);
      }
    }

    // Add a coupon discount if needed
      //$discount = number_format(Cart66Session::get('Cart66Cart')->getDiscountAmount(), 2, '.', '');
    
    $discount = Cart66Session::get('Cart66Cart')->getDiscountAmount();
    
    if(is_object($promotion) && $promotion->apply_to == 'total') {
      $itemTotal = Cart66Session::get('Cart66Cart')->getNonSubscriptionAmount();
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
    
    
    if(isset($itemTotal) && $itemTotal == 0 && $shipping > 0) {     
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] adding shipping as an item"); 
      $itemData = array(
        'NAME' => 'Shipping',
        'AMT' => number_format($shipping,2,'.',''),
        'NUMBER' => 'SHIPPING',
        'QTY' => '1'
      );
      $this->addItem($itemData);
      $shipping = 0;
    }
    

    if($discount > 0) {
      $negDiscount = 0 - number_format($discount, 2, '.', '');
      $itemData = array(
        'NAME' => 'Discount',
        'AMT' => $negDiscount,
        'NUMBER' => 'DSC',
        'QTY' => 1
      );
      $this->addItem($itemData);
    }
  }
  
  protected function _buildNvpStr() {
    $nvp = false;
    $dataSources = array(
      '_apiData',
      '_requestFields',
      '_ecUrls',
      '_creditCardData',
      '_payerInfo',
      '_payerName',
      '_payerAddress',
      '_paymentDetails',
      '_payerShipToAddress'
    );
    
    $params = array();
    foreach($dataSources as $source) {
      if(is_array($this->$source) && count($this->$source) > 0) {
        foreach($this->$source as $key => $value) {
          // Only add values that contain a value
          if(isset($value) && strlen($value) > 0) {
            $value = urlencode($value);
            $params[] = "$key=$value";
          }
        }
      }
    }
    
    // Add information about individual items
    if(is_array($this->_items) && count($this->_items) > 0) {
      $counter = 0;
      
      // Look for subscriptions first. PayPal feels like this is important.
      foreach($this->_items as $itemInfo) {
        if(isset($itemInfo['BILLINGAGREEMENTDESCRIPTION'])) {
          $params[] = 'L_BILLINGAGREEMENTDESCRIPTION' . $counter . '=' . urlencode($itemInfo['BILLINGAGREEMENTDESCRIPTION']);
          $params[] = 'L_BILLINGTYPE' . $counter . '=' . 'RecurringPayments';
        }
        
      }
      
      // Look for non-subscription products
      foreach($this->_items as $itemInfo) {
        if(!isset($itemInfo['BILLINGAGREEMENTDESCRIPTION'])) {
          $params[] = 'L_NAME' . $counter . '=' . urlencode($itemInfo['NAME']);
          $params[] = 'L_AMT' . $counter . '=' . urlencode(number_format($itemInfo['AMT'], 2, '.', ''));
          $params[] = 'L_NUMBER' . $counter . '=' . urlencode($itemInfo['NUMBER']);
          $params[] = 'L_QTY' . $counter . '=' . urlencode($itemInfo['QTY']);
          $counter++;
        }
      }
    }
    else {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not adding information about individual products because this items array is empty: " . print_r($this->_items, true));
    }
    
    $nvp = implode('&', $params);
    
    return $nvp;
  }
  
  protected function _sendRequest($url, $data) {
    $numParams = substr_count($data, '&') + 1;
    
    //open connection
    $ch = curl_init();
    
    //set the url, number of POST vars, POST data
    
    // Do not worry about checking for SSL certs
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, $numParams);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$data);

    //execute post
    $response = curl_exec($ch);

    //close connection
    curl_close($ch);
    
    return $response;
  }
  
  /**
   * Return an array of decoded NVP data
   * 
   * @return array
   */
  protected function _decodeNvp($nvpstr) {
		$intial=0;
		$nvpArray = array();
		
		while(strlen($nvpstr)) {
			// postion of Key
			$keypos= strpos($nvpstr,'=');
			
			// position of value
			$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);
	
			// getting the Key and Value values and storing in a Associative Array
			$keyval=substr($nvpstr,$intial,$keypos);
			$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
			
			// decoding the respose
			$nvpArray[urldecode($keyval)] = urldecode( $valval);
			$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
		}
			
		return $nvpArray;
  }
  
  
}