<?php
class Cart66ShippingRule extends Cart66ModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = Cart66Common::getTableName('shipping_rules');
    parent::__construct($id);
  }
  
}