<?php
class Cart66LiveRates {
  public $toZip;
  public $toCountryCode;
  public $weight;
  public $rates = array();
  
  public function __construct() {
    $this->rates = array();
  }
  
  public function addRate($service, $rate) {
    $this->rates[] = new Cart66LiveRate($service, $rate);
  }
  
  /**
   * Return an array or Cart66LiveRate objects sorted by rate
   */
  public function getRates() {
    usort($this->rates, array($this, 'sortRate'));
    return $this->rates;
  }
  
  public function clearRates() {
    $this->rates = array();
  }
  
  /**
   * Return the Cart66LiverRate object of the  selected service.
   * If no service has been selected, return the service name of the least expensive service.
   */
  public function getSelected() {
    $rates = $this->getRates();
    $liveRate = $rates[0]; // The least expensive live rate
    foreach($rates as $r) {
      if($r->isSelected) {
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
    $rates = $this->getRates();
    foreach($rates as $r) {
      $r->isSelected = false;
      if($r->service == $serviceName) {
        $r->isSelected = true;
      }
    }
  }
  
  /**
   * Sort live rate objects based on rate
   */
  private function sortRate(Cart66LiveRate $a, Cart66LiveRate $b) {
    if($a->rate == $b->rate) {
      return 0;
    }
    return ($a->rate > $b->rate) ? 1 : -1;
  }
  
}



class Cart66LiveRate {
  public $service;
  public $rate;
  public $isSelected;
  
  public function __construct($service='', $rate='', $isSelected=false) {
    $this->service = $service;
    $this->rate = $rate;
    $this->isSelected = $isSelected;
  }
  
}



/*
$liveRates = new Cart66LiveRates();
$liveRates->addRate('B', 2.00);
$liveRates->addRate('A', 1.00);
$liveRates->addRate('C', 3.00);

$liveRates->setSelected('A');
$liveRates->setSelected('B');
$l = $liveRates->getSelected();
echo $l->service;
*/