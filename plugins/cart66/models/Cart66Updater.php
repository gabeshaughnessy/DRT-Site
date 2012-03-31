<?php
class Cart66Updater {
  
  protected $_version;
  protected $_orderNumber;
  protected $_motherShipUrl = 'http://www.cart66.com/cart66-latest.php';
  
  public function __construct() {
    $setting = new Cart66Setting();
    $this->_version = Cart66Setting::getValue('version');
    $this->_orderNumber = Cart66Setting::getValue('order_number');
  }
  
  /**
   * Check the currently running versoin against the version of the latest release.
   * 
   * @return mixed The new version number if there is a new version, otherwise false.
   */
  public function newVersion() {
    $setting = new Cart66Setting();
    $orderNumber = Cart66Setting::getValue('orderNumber');

    $versionCheck = $this->_motherShipUrl . "?task=getLatestVersion&id=$this->_orderNumber";
    $newVersion = false;
    
    $latest = @file_get_contents($versionCheck);
    if(!empty($latest)) {
      if($latest != $this->_version) {
        $newVersion = $latest;
      }
    }
    
    return $newVersion;
  }
  
  public function getCallHomeUrl() {
    return $this->_motherShipUrl;
  }
  
  public function getOrderNumber() {
    return $this->_orderNumber;
  }

}