<?php
class Cart66ShippingMethod extends Cart66ModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = Cart66Common::getTableName('shipping_methods');
    parent::__construct($id);
  }

  public function deleteMe() {
    if($this->id > 0) {
      $ratesTable = Cart66Common::getTableName('shipping_rates');
      $sql = "DELETE from $ratesTable where shipping_method_id=" . $this->id;
      $this->_db->query($sql);
      parent::deleteMe();
    }
  }
  
  /**
   * Return an array of services selected for the carrier where then key is the service name and the value is the service code
   * 
   * @return array
   */
  public function getServicesForCarrier($carrier) {
    $services = array();
    $shippingMethods = Cart66Common::getTableName('shipping_methods');
    $sql = "SELECT name, code from $shippingMethods where carrier='$carrier'";
    $results = $this->_db->get_results($sql);
    foreach($results as $s) {
      $services[$s->name] = $s->code;
    }
    return $services;
  }
  
  /**
   * Only save shipping methods if the carrier code combo does not exist.
   */
  public function save() {
    $save = true;
    
    $shippingMethods = Cart66Common::getTableName('shipping_methods');
    if(!empty($this->carrier) && !empty($this->code)) {
      $sql = "SELECT id from $shippingMethods where carrier=%s and code=%s";
      $sql = $this->_db->prepare($sql, $this->carrier, $this->code);
      $id = $this->_db->get_var($sql);
      $save = $id === NULL;
    }
    
    if($save) {
      parent::save();
    }
  }
  
  /**
   * Delete all methods for the given carrier if the carrier code is not in the given array
   */
  public function pruneCarrierMethods($carrier, array $codes) {
    $codes = array_map(array($this->_db, 'escape'), $codes);
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Codes array map: " . print_r($codes, true));
    $codes = implode("','", $codes);
    $shippingMethods = $this->_tableName;
    // $sql = "DELETE from $shippingMethods where carrier='$carrier' and code NOT IN ($codes)";
    $sql = "DELETE from $shippingMethods where carrier=%s and code NOT IN ('$codes')";
    $sql = $this->_db->prepare($sql, $carrier);
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Pruning shipping methods: $sql");
    $this->_db->query($sql);
  }
  
  public function isLiveMethod($id=null) {
    $id = empty($id) ? $this->id : $id;
    $m = new Cart66ShippingMethod($id);
    return !empty($m->code);
  }
  
  public function clearAllLiveRates() {
    $shippingMethods = $this->_tableName;
    $sql = "DELETE from $shippingMethods where carrier != ''";
    $this->_db->query($sql);
  }
  
}
