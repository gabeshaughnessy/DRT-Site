<?php 
class Cart66Ups {
  protected $AccessLicenseNumber;  
  protected $UserId;  
  protected $Password;
  protected $shipperNumber;
  protected $credentials;
  protected $dimensionsUnits = "IN";
  protected $weightUnits = "LBS";
  protected $fromZip;

  public function __construct() {
    $setting = new Cart66Setting();
    $this->UserID = Cart66Setting::getValue('ups_username');
    $this->Password = Cart66Setting::getValue('ups_password');
    $this->AccessLicenseNumber = Cart66Setting::getValue('ups_apikey');
    $this->shipperNumber = Cart66Setting::getValue('ups_account');
    $this->fromZip = Cart66Setting::getValue('ups_ship_from_zip');
    $this->credentials = 1;
  }
  
  public function setDimensionsUnits($unit){
    $this->dimensionsUnits = $unit;
  }
  
  public function setWeightUnits($unit){
    $this->weightUnits = $unit;
  }

  /**
   * Return the monetary value of the shipping rate or false on failure.
   */
  public function getRate($PostalCode, $dest_zip, $dest_country_code, $service, $weight, $length=0, $width=0, $height=0) {
    $setting= new Cart66Setting();
    $countryCode = array_shift(explode('~', Cart66Setting::getValue('home_country')));
    $pickupCode = (Cart66Setting::getValue('ups_pickup_code')) ? Cart66Setting::getValue('ups_pickup_code') : "03";
    $ResidentialAddressIndicator = (Cart66Setting::getValue('ups_only_ship_commercial')) ? "" : "
    <ResidentialAddressIndicator/>";
    
    if ($this->credentials != 1) {
      print 'Please set your credentials with the setCredentials function';
      die();
    }
    
    $data ="<?xml version=\"1.0\"?>  
      <AccessRequest xml:lang=\"en-US\">  
        <AccessLicenseNumber>" . urlencode(trim($this->AccessLicenseNumber)) ."</AccessLicenseNumber>  
        <UserId>" . urlencode($this->UserID) . "</UserId>  
        <Password>" . urlencode($this->Password) . "</Password>  
      </AccessRequest>  
      <?xml version=\"1.0\"?>  
      <RatingServiceSelectionRequest xml:lang=\"en-US\">  
        <Request>  
          <TransactionReference>  
            <CustomerContext>Rating and Service</CustomerContext>  
            <XpciVersion>1.0001</XpciVersion>  
          </TransactionReference>  
          <RequestAction>Rate</RequestAction>  
          <RequestOption>Rate</RequestOption>  
        </Request>  
        <PickupType>  
          <Code>$pickupCode</Code>  
        </PickupType>  
        <Shipment>  
          <Shipper>  
            <Address>  
            <PostalCode>$PostalCode</PostalCode>  
            <CountryCode>$countryCode</CountryCode>  
            </Address>  
            <ShipperNumber>$this->shipperNumber</ShipperNumber>  
          </Shipper>  
          <ShipTo>  
            <Address>  
            <PostalCode>$dest_zip</PostalCode>  
            <CountryCode>$dest_country_code</CountryCode>$ResidentialAddressIndicator  
            </Address>  
          </ShipTo>  
          <ShipFrom>  
            <Address>  
            <PostalCode>$PostalCode</PostalCode>  
            <CountryCode>$countryCode</CountryCode>  
            </Address>  
          </ShipFrom>  
          <Service>  
            <Code>$service</Code>  
          </Service>  
          <Package>  
            <PackagingType>  
            <Code>02</Code>  
            </PackagingType>  
            <Dimensions>  
              <UnitOfMeasurement>  
                <Code>$this->dimensionsUnits</Code>  
              </UnitOfMeasurement>  
              <Length>$length</Length>  
              <Width>$width</Width>  
              <Height>$height</Height>  
            </Dimensions>  
            <PackageWeight>  
              <UnitOfMeasurement>  
                <Code>$this->weightUnits</Code>  
              </UnitOfMeasurement>  
              <Weight>$weight</Weight>  
            </PackageWeight>  
          </Package>  
      </Shipment>  
      </RatingServiceSelectionRequest>";  
    $ch = curl_init("https://onlinetools.ups.com/ups.app/xml/Rate");  
    curl_setopt($ch, CURLOPT_HEADER, 1);  
    curl_setopt($ch,CURLOPT_POST,1);  
    curl_setopt($ch,CURLOPT_TIMEOUT, 60);  
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);  
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);  
    curl_setopt($ch,CURLOPT_POSTFIELDS,$data);  
    $result = curl_exec ($ch); 
    $xml = substr($result, strpos($result, '<RatingServiceSelectionResponse'));
    
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] UPS XML REQUEST: \n$data");
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] UPS XML RESULT: \n$xml");
    
    try{
      $xml = new SimpleXmlElement($xml);
    }
    catch(Exception $e){
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Cart66 Exception caught when trying to get UPS XML Response: " . $e->getMessage() . " \n");
      $rate = false;
    }
    
     
    $responseDescription = $xml->Response->ResponseStatusDescription;
    $errorDescription = $xml->Response->Error->ErrorDescription;
    // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Response Description: (Service: $service) $responseDescription $errorDescription");
    if($responseDescription == "Failure") {
      $rate = false;
    }
    else {
      //$rate = $xml->RatedShipment->RatedPackage->TotalCharges->MonetaryValue;
      $rate = $xml->RatedShipment->TotalCharges->MonetaryValue; 
    }
    
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] RATE ===> $rate");
    return $rate;
  }  

  /**
   * Return an array where the keys are the service names and the values are the prices
   */
  public function getAllRates($toZip, $toCountryCode, $weight) {
    $rates = array();
    $method = new Cart66ShippingMethod();
    $upsServices = $method->getServicesForCarrier('ups');
    
    /*
    $shippingMethods = Cart66Common::getTableName('shipping_methods');
    $sql = "SELECT name, code from $shippingMethods where carrier = 'ups'";'
    $results = $wpdb->get_results($sql);
    */
    
    foreach($upsServices as $service => $code) {
      $rate = $this->getRate($this->fromZip, $toZip, $toCountryCode, $code, $weight);
      if($rate !== FALSE) {
        $rates[$service] = number_format((float) $rate, 2, '.', '');
      }
      Cart66Common::log("LIVE RATE REMOTE RESULT ==> ZIP: $toZip Service: $service $code) Rate: $rate");
    }
    
    return $rates;
  }

}