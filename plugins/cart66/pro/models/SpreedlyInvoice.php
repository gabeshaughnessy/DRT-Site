<?php
class SpreedlyInvoice {
  
  protected $_invoiceToken;
  public $invoiceData;
  public $subscriber;
  
  public function __construct($token=null) {
    if(!empty($token)) {
      $this->setToken($token);
    }
  }
  
  public function setToken($token) {
    $this->_invoiceToken = $token;
  }
  
  public function getToken() {
    return $this->_invoiceToken;
  }
  
  /**
   * Create an invoice and set the invoice token.
   * Set the public variable $this->subscriber to the subscriber returned when creating
   * the invoice. Return the XML response and the response code in an object format:
   *   $result->response
   *   $result->code
   * 
   * @return object
   */
  public function create(SpreedlySubscriber $subscriber, $planId) {
    $invoice = array(
      'subscription-plan-id' => $planId,
      'subscriber' => $subscriber->toArray(true, array('token', 'feature-level'))
    );
    return $this->createFromArray($invoice);
  }
  
  /**
   * Create an invoice and set the invoice token based on the given invoice array.
   * Set the public variable $this->subscriber to the subscriber returned when creating
   * the invoice. 
   * 
   * $invoice = array(
   *   'subscription-plan-id' => $planId,
   *   'subscriber' => $subscriberDataArray
   * );
   * 
   * Return the XML response and the response code in an object format:
   *   $result->response
   *   $result->code
   */
  public function createFromArray(array $invoice) {
    $xml = Cart66Common::arrayToXml($invoice, 'invoice');
    $result = SpreedlyCommon::curlRequest('/invoices.xml', 'post', $xml);

    if($result->code == '201') {
      $invoice = new SimpleXMLElement($result->response);
      $this->_invoiceToken = $invoice->token;
      $this->invoiceData = $invoice;
      $this->subscriber = new SpreedlySubscriber();
      $this->subscriber->setData($invoice->subscriber);
      $this->subscriber->updateLocalAccount();
    }
    else {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Spreedly Invoice: Creating invoice failed: \n" . $result->code . "\n" . $result->response);
      throw new SpreedlyException('Spreedly Invoice: Creating invoice failed.', $result->code);
    }
    
    return $result;
  }
  
  /**
   * Pay a spreedly invoice. The invoice token is required for payment.
   * Returns the paid invoice token.
   * 
   * @param mixed $paymentMethod Either "on-file" or a SpreedlyCreditCard object
   * @param string $invoiceToken 
   * @return string The invoice token that was paid.
   * @throws SpreedlyException on failure
   */
  public function pay($paymentMethod, $invoiceToken=null) {
    $payment = array('account-type' => 'on-file');
    if(get_class($paymentMethod) == 'SpreedlyCreditCard') {
      if(!$paymentMethod->validate()) {
        $errorDetails = print_r($paymentMethod->getErrors(), true);
        throw new SpreedlyException('Spreedly Payment: Invalid credit card data trying to be used to pay a spreedly invoice: ' . $errorDetails, 66001);
      }
      
      $cardData = $paymentMethod->getCardData();
      $payment = array(
        'account-type' => 'credit-card',
        'credit-card' => $cardData
      );
    }
    
    // Set invoice token if provided
    if(isset($invoiceToken)) { $this->setToken($invoiceToken); }
    
    // Make sure there is an invoice token before trying to process the payment
    if(empty($this->_invoiceToken)) {
      throw new SpreedlyException('Spreedly Payment: Trying to pay spreedly invoice without a valid invoice token', 66002);
    }
    
    $xml = Cart66Common::arrayToXml($payment, 'payment');
    $result = SpreedlyCommon::curlRequest('/invoices/' . $this->_invoiceToken. '/pay.xml', "put", $xml);
    $responseCode = $result->code;
    if(!empty($responseCode)) {
      if($responseCode != 200) {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Spreedly Invoice Payment: Failed to pay invoice. 
          Code: " . $responseCode . "\nResponse: " . $result->response . "\n Payment XML:\n$xml");
        $errorResponse = $result->response;
        throw new SpreedlyException("Spreedly Payment: Failed to pay spreedly invoice. \n\n$errorResponse", $responseCode);
      }
      
      try {
        $invoice = new SimpleXMLElement($result->response);
        $this->_invoiceToken = $invoice->token;
      }
      catch(Exception $e) {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] SpreedlyInvoice pay(): 
          Unable to create SimpleXmlElement from result response: " . $result->response);
      }
    }
  }
  
}