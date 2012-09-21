<?php
class Cart66LiveRates {
  public $toZip;
  public $weight;
  public $rates = array();
  protected $_toCountryCode;
  
  public function __construct() {
    $this->rates = array();
  }
  
  public function addRate($carrier, $service, $rate) {
    $rate = $this->_tweakRate($rate);
    $this->rates[] = new Cart66LiveRate($carrier, $service, $rate);
  }
  
  public function appendRates(Cart66LiveRates $liveRates) {
    $rates = $liveRates->getRates();
    foreach($rates as $rate) {
      $this->addRate($rate->getCarrier(), $rate->getService(), $rate->getRate());
    }
  }
  
  /**
   * Return an array of Cart66LiveRate objects sorted by rate after being tweaked by the rate tweaker.
   */
  public function getRates() {
    if(count($this->rates)) {
      usort($this->rates, array($this, 'sortRate'));
    }
    else {
      $this->rates = array(new Cart66LiveRate('None', 'No Shipping Services Available', false));
    }
    return $this->rates;
  }
  
  public function clearRates($carrier=null) {
    if(isset($carrier)) {
      $carrier = strtoupper($carrier);
      foreach($this->rates as $key => $rate) {
        if($rate->carrier == $carrier) {
          unset($this->rates[$key]);
        }
      }
    }
    else {
      $this->rates = array();
    }
  }
  
  /**
   * Returns A COPY of the Cart66LiverRate object of the selected service. The copy has been tweaked by the rate tweaker.
   * If no service has been selected, return the service name of the least expensive service.
   */
  public function getSelected() {
    $liveRate = $this->rates[0]; // The least expensive live rate
    foreach($this->getRates() as $r) {
      if($r->isSelected()) {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] NOT THE DEFAULT SERVICE: The selected service is: " . $r->getService());
        $liveRate = $r;
        break;
      }
    }
    return $liveRate;
  }
  
  /**
   * Set all rates to not selected except the rate with the given service name
   */
  public function setSelected($serviceName) {
    foreach($this->rates as $r) {
      $r->setSelected(false);
      $rateServiceName = $r->getService();
      if($rateServiceName == $serviceName) {
        $r->setSelected(true);
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] $rateServiceName is now SELECTED -- getSelected() returns " . $this->getSelected()->getService());
      }
    }
  }
  
  public function setToCountryCode($value) {
    $this->_toCountryCode = $value;
  }
  
  public function getToCountryCode() {
    if(empty($this->_toCountryCode)) {
      $this->_toCountryCode = Cart66Common::getHomeCountryCode();
    }
    return $this->_toCountryCode;
  }
  
  public function hasValidShippingService() {
    $rate = $this->getSelected();
    $isValid = ($rate->getRate() !== false) ? true : false;
    return $isValid;
  }
  
  /**
   * Sort live rate objects based on rate
   */
  private function sortRate(Cart66LiveRate $a, Cart66LiveRate $b) {
    if($a->getRate() == $b->getRate()) {
      return 0;
    } elseif($a->carrier == 'Local Pickup' && Cart66Setting::getValue('local_pickup_at_end') == 1) {
      return 1;
    } elseif($b->carrier == 'Local Pickup' && Cart66Setting::getValue('local_pickup_at_end') == 1) {
      return -1;
    }
    return ($a->getRate() > $b->getRate()) ? 1 : -1;
  }
  
  /**
   * Tweak rate using the tweak factor and returned tweaked rate
   */ 
  private function _tweakRate($rate) {
    $tweakedRate = $rate;
    if($tweakFactor = Cart66Setting::getValue('rate_tweak_factor')) {
      $tweakType = Cart66Setting::getValue('rate_tweak_type');
      if($tweakType == 'percentage') {
        $t = $tweakFactor/100;
        $tweakedRate = $rate + ($rate * $t);
      }
      elseif($tweakType == 'fixed') {
        $tweakedRate = $rate + $tweakFactor;
      }
      if($tweakedRate < 0) { $tweakedRate = 0; }
      $tweakedRate = number_format($tweakedRate, 2, '.', '');
    }
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] RATE TWEAKER RESULT: Rate: $rate ==> Tweaked Rate: $tweakedRate");
    return $tweakedRate;
  }
  
}


/*
$liveRates = new Cart66LiveRates();
$liveRates->addRate('TEST', 'B', 2.00);
$liveRates->addRate('TEST', 'A', 1.00);
$liveRates->addRate('TEST', 'C', 3.00);

$liveRates->setSelected('A');
$liveRates->setSelected('B');
$l = $liveRates->getSelected();
echo $l->service;
*/