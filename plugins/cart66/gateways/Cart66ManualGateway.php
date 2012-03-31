<?php
class Cart66ManualGateway extends Cart66GatewayAbstract {

  /**
   * @var decimal
   * The total price to charge the customer. Shipping, tax, etc. all included.
   */
  protected $_total;
  
  public function setPayment($p) {
    $this->_payment = $p;
    if($p['email'] == '') {
      $this->_errors['Email address'] = "Email address is required";
      $this->_jqErrors[] = "payment-email";
    }

    if($p['phone'] == '') {
      $this->_errors['Phone'] = "Phone number is required";
      $this->_jqErrors[] = "payment-phone";
    }
  }
  
   public function getCreditCardTypes() {
     $noCards = array();
     return $noCards;
   }
   
   public function initCheckout($total) {
     $this->_total = $total;
   }
   
   public function getTransactionResponseDescription() {
     return 'Manual transaction processed: ' . $this->_total;
   }
   
   public function doSale() {
     $transId = 'MT-' . Cart66Common::getRandString();
     return $transId;
   }

}
