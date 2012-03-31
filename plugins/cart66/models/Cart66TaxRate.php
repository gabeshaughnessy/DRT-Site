<?php
class Cart66TaxRate extends Cart66ModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = Cart66Common::getTableName('tax_rates');
    parent::__construct($id);
  }
  
  public function loadByZip($zip) {
    $isLoaded = false;
    
    if($zip != null) {
      if(strpos($zip, '-') > 0) {
        // only use first part of hyphenated zip codes
        list($zip, $dummy) = explode('-', $zip);
      }
      if(is_numeric($zip)) {
        $sql = "SELECT * from $this->_tableName where zip_low <= $zip AND zip_high >= $zip";
        if($row = $this->_db->get_row($sql, ARRAY_A)) {
          $this->setData($row);
          $isLoaded = true;
        }
      }
    }
    
    return $isLoaded;
  }
  
  /**
   * First check to see if an individual state is taxed, if not check for all sales tax.
   * The individual sales tax rates take precedence over the all sales tax rate.
   */
  public function loadByState($state) {
    $isLoaded = false;
    $state = strtoupper($state);
    
    $sql = "SELECT * from $this->_tableName where state='$state'";
    if($row = $this->_db->get_row($sql, ARRAY_A)) {
      $this->setData($row);
      $isLoaded = true;
    }
    else {
      $sql = "SELECT * from $this->_tableName where state='All Sales'";
      if($row = $this->_db->get_row($sql, ARRAY_A)) {
        $this->setData($row);
        $isLoaded = true;
      }
    }
    
    return $isLoaded;
  }
  
  public function getFullStateName($state=null) {
    if(is_null($state)) {
      $state = $this->state;
    }
    
    $canada = array(
      "AB" => 'Alberta',
      "BC" => 'British Columbia',
      "MB" => 'Manitoba',
      "NB" => 'New Brunswick',
      "NF" => 'Newfoundland',
      "NT" => 'Northwest Territories',
      "NS" => 'Nova Scotia',
      "NU" => 'Nunavut',
      "ON" => 'Ontario',
      "PE" => 'Prince Edward Island',
      "PQ" => 'Quebec',
      "SK" => 'Saskatchewan',
      "YT" => 'Yukon Territory'
    );
    
    $usa = array(
      'All Sales' => 'All Sales',
      'AL' => 'Alabama',
      'AK' => 'Alaska',
      'AZ' => 'Arizona',
      'AR' => 'Arkansas',
      'CA' => 'California',
      'CO' => 'Colorado',
      'CT' => 'Connecticut',
      'DC' => 'District of Columbia',
      'DE' => 'Delaware',
      'FL' => 'Florida',
      'GA' => 'Georgia',
      'HI' => 'Hawaii',
      'ID' => 'Idaho',
      'IL' => 'Illinois',
      'IN' => 'Indiana',
      'IA' => 'Iowa',
      'KS' => 'Kansas',
      'KY' => 'Kentucky',
      'LA' => 'Louisiana',
      'ME' => 'Maine',
      'MD' => 'Maryland',
      'MA' => 'Massachusetts',
      'MI' => 'Michigan',
      'MN' => 'Minnesota',
      'MS' => 'Mississippi',
      'MO' => 'Missouri',
      'MT' => 'Montana',
      'NE' => 'Nebraska',
      'NV' => 'Nevada',
      'NH' => 'New Hampshire',
      'NJ' => 'New Jersey',
      'NM' => 'New Mexico',
      'NY' => 'New York',
      'NC' => 'North Carolina',
      'ND' => 'North Dakota',
      'OH' => 'Ohio',
      'OK' => 'Oklahoma',
      'OR' => 'Oregon',
      'PA' => 'Pennsylvania',
      'RI' => 'Rhode Island',
      'SC' => 'South Carolina',
      'SD' => 'South Dakota',
      'TN' => 'Tennessee',
      'TX' => 'Texas',
      'UT' => 'Utah',
      'VT' => 'Vermont',
      'VA' => 'Virginia',
      'WA' => 'Washington',
      'WV' => 'West Virginia',
      'WI' => 'Wisconsin',
      'WY' => 'Wyoming'
    );
    
    $allStates = array_merge($canada, $usa);
    return $allStates[$state];
    
  }
  
}