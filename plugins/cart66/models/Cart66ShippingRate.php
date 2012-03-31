<?php
class Cart66ShippingRate extends Cart66ModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = Cart66Common::getTableName('shipping_rates');
    parent::__construct($id);
  }
  
}