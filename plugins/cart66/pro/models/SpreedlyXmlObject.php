<?php

abstract class SpreedlyXmlObject {
  /**
   * @var SimpleXMLElement
   * The data loaded for the Spreedly subscriber
   */
  protected $_data;
  
  public function getData() {
    return $this->_data;
  }
  
  public function setData(SimpleXMLElement $data) {
    $this->_data = $data;
  }
  
  public function __get($key) {
    $value = false;
    $key = SpreedlyCommon::camelToDash($key);
    $funcName = "_get" . ucwords($key);
    if(method_exists($this, $funcName)) {
      $value = $this->{$funcName}();
    }
    elseif(get_class($this->_data) == 'SimpleXMLElement') {
      $value = $this->_data->$key;
    }
    return $value;
  }
  
  /**
   * Convert the SimpleXmlElement to an assoc array.
   * If the prune parameter is true, the array will not include keys for empty values
   * 
   * @param boolean $prune
   * @return array
   */
  public function toArray($prune=false, $omit=null) {
    $data = array();
    foreach($this->_data as $key => $value) {
      $value = (string)$value;
      if($prune) {
        if(!empty($value)) {
          $data[$key] = $value;
        }
      }
      else {
        $data[$key] = $value;
      }
    }
    
    if(isset($omit) && is_array($omit)) {
      foreach($omit as $key) {
        unset($data[$key]);
      }
    }
    
    return $data;
  }
  
  /**
   * Populate the protected $_data SimpleXmlElement with the passed in array
   * Warning: no validation is done so you can end up with some crazy SimpleXmlElement objects
   * if you aren't careful passing in valid arrays.
   * 
   * The array keys may be camelCaseWords or dash-type-words. If they are camelCaseWords, they 
   * will be converted to dash-type-words for the SimpleXmlElement.
   * 
   * @param array $data
   * @param string $wrapper
   * @return void
   */
  public function hydrate(array $data, $wrapper) {
    $wrapper = SpreedlyCommon::camelToDash($wrapper);
    $xml = '<' . $wrapper . '/>';
    $xml = new SimpleXmlElement($xml);
    foreach($data as $key => $value) {
      $key = SpreedlyCommon::camelToDash($key);
      $xml->addChild($key, $value);
    }
    $this->setData($xml);
  }
  
}