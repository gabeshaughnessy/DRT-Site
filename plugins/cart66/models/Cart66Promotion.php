<?php
class Cart66Promotion extends Cart66ModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = Cart66Common::getTableName('promotions');
    parent::__construct($id);
  }
  
  public function getAmountDescription() {
    $amount = 'not set';
    if($this->id > 0) {
      if($this->type == 'dollar') {
        $amount = CURRENCY_SYMBOL . number_format($this->amount, 2, '.', ',') . ' off';
      }
      elseif($this->type == 'percentage') {
        $amount = number_format($this->amount, 0) . '% off';
      }
    }
    return $amount;
  }
  
  public function getMinOrderDescription() {
    $min = $this->minOrder;
    if($min > 0) {
      $min = CURRENCY_SYMBOL . $min;
    }
    else {
      $min = "Apply to all orders";
    }
    return $min;
  }
  
  public function save() {
    $this->_data['code'] = strtoupper($this->_data['code']);
    parent::save();
  }
  
  public function loadByCode($code) {
    $loaded = false;
    $sql = "SELECT * from $this->_tableName where code = %s";
    $sql = $this->_db->prepare($sql, $code);
    if($data = $this->_db->get_row($sql, ARRAY_A)) {
      $this->setData($data);
      $loaded = true;
    }
    return $loaded;
  }
  
  public function discountTotal($total) {
    if($total >= $this->minOrder) {
      if($this->type == 'dollar') {
        $total = $total - $this->amount;
      }
      elseif($this->type == 'percentage') {
        $total = $total * ((100 - $this->amount)/100);
      }
    }
    // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Calculated discount total: $total");
    return $total;
  }
  
}