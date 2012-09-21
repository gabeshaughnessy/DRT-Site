<?php
class SpreedlyCreditCard {
  
  private $_cardData;
  private $_errors;
  
  public function __construct() {
    $this->_errors = array();
    $this->resetCardData();
  }
  
  public function __set($key, $value) {
    $key = SpreedlyCommon::camelToDash($key);
    if(array_key_exists($key, $this->_cardData)) {
      if($key == 'number') {
        $value = preg_replace('/\D/', '', $value);
      }
      elseif($key == 'month') {
        if(!is_numeric($value) || $value < 1 || $value > 12) {
          $value = ''; // Do not allow invalid month numbers to be set
        }
      }
      elseif($key == 'year') {
        $thisYear = date('Y');
        if(!is_numeric($value) || $value < $thisYear) {
          $value = ''; // Do not allow invalid or past year numbers to be set
        }
      }
      $this->_cardData[$key] = $value;
    }
  }
  
  public function __get($key) {
    $value = false;
    if(array_key_exists($key, $this->_cardData)) {
      $value = $this->_cardData[$key];
    }
    return $value;
  }
  
  public function __isset($key) {  
    return isset($this->_cardData[$key]);  
  }
  
  public function getErrors() {
    return $this->_errors;
  }
  
  public function getCardData() {
    return $this->_cardData;
  }
  
  public function resetCardData() {
    $this->_cardData = array(
      'number' => '',
      'card-type' => '',
      'verification-value' => '',
      'month' => '',
      'year' => '',
      'first-name' => '',
      'last-name' => '',
      'address1' => '',
      'address2' => '',
      'city' => '',
      'state' => '',
      'zip' => '',
      'country' => '',
      'phone-number' => ''
    );
  }
  
  public function validate() {
    $isValid = true;
    $this->_errors = array();
    $this->validateExpDate();
    $this->validateCardNumber();
    $this->validateCardType();
    $this->validateCardName();
    if(count($this->_errors)) {
      $isValid = false;
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] SpreedlyCreditCard validation failed: " . print_r($this->_cardData, true));
    }
    return $isValid;
  }
  
  public function validateExpDate() {
    $isValid = true;
    $thisYear = date('Y');
    if($this->_cardData['month'] < 1 || empty($this->_cardData['month'])) {
      $isValid = false;
      $this->_errors[] = 'Invalid credit card month';
    }
    elseif($this->_cardData['year'] < $thisYear) {
      $isValid = false;
      $this->_errors[] = 'Invalid credit card year';
    }
    else {
      $today = strtotime('now', Cart66Common::localTs());
      $expDate = strtotime($this->_cardData['month'] . '/28/' . $this->_cardData['year']);
      if($today >= $expDate) {
        $isValid = false;
        $this->_errors[] = "Credit card has expired";
      }
    }
    
    return $isValid;
  }
  
  public function validateCardNumber() {
    $isValid = true;
    if(strlen($this->_cardData['number']) < 13) {
      $isValid = false;
      $this->_errors[] = 'Credit card number is invalid';
    }
    return $isValid;
  }
  
  public function validateCardType() {
    $isValid = true;
    $validTypes = array('visa', 'master', 'discover', 'american_express');
    if(!in_array($this->_cardData['card-type'], $validTypes)) {
      $isValid = false;
      $this->_errors[] = 'Invalid card type';
    }
    return $isValid;
  }
  
  public function validateCardName() {
    $isValid = true;
    if(empty($this->_cardData['first-name']) || empty($this->_cardData['last-name'])) {
      $isValid = false;
      $this->_errors[] = 'Invalid name on credit card';
    }
    return $isValid;
  }
  
  public function validateCardAddress() {
    $isValid = true;
    if(empty($this->_cardData['address1'])) {
      $isValid = false;
      $this->_errors[] = 'Credit card address1 cannot be blank';
    }
    
    if(empty($this->_cardData['city'])) {
      $isValid = false;
      $this->_errors[] = 'Credit card city cannot be blank';
    }
    
    if(empty($this->_cardData['zip'])) {
      $isValid = false;
      $this->_errors[] = 'Credit card zip cannot be blank';
    }
    
    if(empty($this->_cardData['country'])) {
      $isValid = false;
      $this->_errors[] = 'Credit card country cannot be blank';
    }
    
    if(empty($this->_cardData['phone'])) {
      $isValid = false;
      $this->_errors[] = 'Credit card phone cannot be blank';
    }
    return $isValid;
  }
  
  public function hydrateFromCheckout() {
    $this->resetCardData();
    $this->number = $_POST['payment']['cardNumber'];
    $adjustedCardType = strtolower($_POST['payment']['cardType']);
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Original Spreedly cardType: " . $adjustedCardType);
    if($adjustedCardType == "amex"){ 
      $adjustedCardType = "american_express"; 
    }
    if($adjustedCardType == "mastercard"){ 
      $adjustedCardType = "master"; 
    }
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Adjusted Spreedly cardType: ". $adjustedCardType);
    $this->cardType = $adjustedCardType;
    $this->verificationValue = $_POST['payment']['securityId'];
    $this->month = $_POST['payment']['cardExpirationMonth'];
    $this->year = $_POST['payment']['cardExpirationYear'];
    $this->firstName = $_POST['billing']['firstName'];
    $this->lastName = $_POST['billing']['lastName'];
    $this->address1 = $_POST['billing']['address'];
    $this->address2 = $_POST['billing']['address2'];
    $this->city = $_POST['billing']['city'];
    $this->state = $_POST['billing']['state'];
    $this->zip = $_POST['billing']['zip'];
    $this->country = $_POST['billing']['country'];
    $this->phoneNumber = $_POST['payment']['phone'];
  }
  
  /**
   * This is a debugging function to load dummy credit card data for testing
   */
  public function loadDummyData($state='valid') {
    switch($state) {
      case 'unauthorized':
        $number = '4012888888881881';
        break;
      case 'gateway_unavailable':
        $number = '4111111111111111';
        break;
      default: 
        $number = '4222222222222';
    }
    
    $this->_cardData = array(
      'number' => $number,
      'card-type' => 'visa',
      'verification-value' => '123',
      'month' => '1',
      'year' => '2020',
      'first-name' => 'Test',
      'last-name' => 'Card',
      'address1' => '1234 Test Drive',
      'address2' => '',
      'city' => 'Richmond',
      'state' => 'VA',
      'zip' => '23116',
      'country' => 'USA',
      'phone-number' => '804-266-1789'
    );
  }
  
}