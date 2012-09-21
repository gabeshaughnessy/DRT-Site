<?php
class Cart66AuthorizeNet extends Cart66GatewayAbstract {

  var $field_string;
  var $fields = array();
  var $response_string;
  var $response = array();
  var $gateway_url;
   
  public function __construct() {
    parent::__construct();
    
    // initialize error arrays
    $this->_errors = array();
    $this->_jqErrors = array();
    
    // some default values
    $this->addField('x_version', '3.1');
    $this->addField('x_delim_data', 'TRUE');
    $this->addField('x_delim_char', '|');  
    $this->addField('x_url', 'FALSE');
    $this->addField('x_type', 'AUTH_CAPTURE');
    $this->addField('x_method', 'CC');
    $this->addField('x_relay_response', 'FALSE');
    
    if(!(Cart66Setting::getValue('auth_username') && Cart66Setting::getValue('auth_url') && Cart66Setting::getValue('auth_trans_key'))) {
      throw new Cart66Exception('Invalid Authorize.net Configuration', 66510); 
    }
    $gatewayUrl = Cart66Setting::getValue('auth_url');
    if($gatewayUrl == 'other') {
      $gatewayUrl = Cart66Setting::getValue('auth_url_other');
      if(!$gatewayUrl) {
        throw new Cart66Exception('Invalid Gateway Configuration', 66511);
      }
    }
  }
  
  /**
   * Return an array of accepted credit card types where the keys are the display values and the values are the gateway values
   * 
   * @return array
   */
  public function getCreditCardTypes() {
    $cardTypes = array();
    $setting = new Cart66Setting();
    $cards = Cart66Setting::getValue('auth_card_types');
    if($cards) {
      $cards = explode('~', $cards);
      if(in_array('mastercard', $cards)) {
        $cardTypes['MasterCard'] = 'mastercard';
      }
      if(in_array('visa', $cards)) {
        $cardTypes['Visa'] = 'visa';
      }
      if(in_array('amex', $cards)) {
        $cardTypes['American Express'] = 'amex';
      }
      if(in_array('discover', $cards)) {
        $cardTypes['Discover'] = 'discover';
      }
    }
    return $cardTypes;
  }
   
  public function addField($field, $value) {
    $this->fields["$field"] = $value;   
  }

  public function initCheckout($total) {
    $p = $this->getPayment();
    $b = $this->getBilling();
    Cart66Common::log("Payment info for checkout: " . print_r($p, true));
    
    // Load gateway url from Cart66 settings
    $gatewayUrl = Cart66Setting::getValue('auth_url');
    if($gatewayUrl == 'other') {
      $gatewayUrl = Cart66Setting::getValue('auth_url_other');
    }
    
    $this->gateway_url = $gatewayUrl;
    $expDate = $p['cardExpirationMonth'] . '/' . $p['cardExpirationYear'];
    $this->addField('x_login', Cart66Setting::getValue('auth_username'));
    $this->addField('x_tran_key', Cart66Setting::getValue('auth_trans_key'));
    $this->addField('x_card_num', $p['cardNumber']);
    $this->addField('x_exp_date', $expDate);
    $this->addField('x_card_code', $p['securityId']);
    $this->addField('x_first_name', $b['firstName']);
    $this->addField('x_last_name', $b['lastName']);
    $this->addField('x_address', $b['address']);
    $this->addField('x_city', $b['city']);
    $this->addField('x_state', $b['state']);
    $this->addField('x_zip', $b['zip']);
    $this->addField('x_country', $b['country']);
    $this->addField('x_phone', $p['phone']);
    $this->addField('x_email', $p['email']);
    $this->addField('x_amount', $total);
  }

   function doSale() {
      // This function actually processes the payment.  This function will 
      // load the $response array with all the returned information.  
      // The response code values are:
      // 1 - Approved
      // 2 - Declined
      // 3 - Error
      
      $sale = false;
      
      if($this->fields['x_amount'] > 0) {
        // Construct the fields string to pass to authorize.net
        foreach( $this->fields as $key => $value ) {
           $this->field_string .= "$key=" . urlencode( $value ) . "&";
        }
        $items = Cart66Session::get('Cart66Cart')->getItems();
        foreach($items as $i) {
          $itemNumber = str_replace("\t", " ", substr($i->getItemNumber(), 0, 31));
          $product = new Cart66Product($i->getProductId());
          $itemName = str_replace("\t", " ", substr($product->name, 0, 31));
          $itemOptions = str_replace("\t", " ", substr($i->getOptionInfo(), 0, 29));
          if($itemOptions != '') {
            $itemOptions = '(' . $itemOptions . ')';
          }
          $itemQuantity = $i->getQuantity();
          $itemTaxable = $product->taxable == 1 ? 'Y' : 'N';
          $itemPrice = number_format($i->getProductPrice(), 2, '.', '');
          $this->field_string .= "x_line_item=" . urlencode("$itemNumber<|>$itemName<|>$itemOptions<|>$itemQuantity<|>$itemPrice<|>$itemTaxable") . "&";
        }

        // Execute the HTTPS post via CURL
        $ch = curl_init($this->gateway_url); 
        curl_setopt($ch, CURLOPT_HEADER, 0); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $this->field_string, "& " )); 
        
        // Do not worry about checking for SSL certs
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            
        $this->response_string = urldecode(curl_exec($ch)); 

        if (curl_errno($ch)) {
          $this->response['Response Reason Text'] = curl_error($ch);
        }
        else {
          curl_close ($ch);
        }


        // Load a temporary array with the values returned from authorize.net
        $temp_values = explode('|', $this->response_string);

        // Load a temporary array with the keys corresponding to the values 
        // returned from authorize.net (taken from AIM documentation)
        $temp_keys= array ( 
             "Response Code", "Response Subcode", "Response Reason Code", "Response Reason Text",
             "Approval Code", "AVS Result Code", "Transaction ID", "Invoice Number", "Description",
             "Amount", "Method", "Transaction Type", "Customer ID", "Cardholder First Name",
             "Cardholder Last Name", "Company", "Billing Address", "City", "State",
             "Zip", "Country", "Phone", "Fax", "Email", "Ship to First Name", "Ship to Last Name",
             "Ship to Company", "Ship to Address", "Ship to City", "Ship to State",
             "Ship to Zip", "Ship to Country", "Tax Amount", "Duty Amount", "Freight Amount",
             "Tax Exempt Flag", "PO Number", "MD5 Hash", "Card Code (CVV2/CVC2/CID) Response Code",
             "Cardholder Authentication Verification Value (CAVV) Response Code"
        );

        // Add additional keys for reserved fields and merchant defined fields
        for ($i=0; $i<=27; $i++) {
           array_push($temp_keys, 'Reserved Field '.$i);
        }
        $i=0;
        while (sizeof($temp_keys) < sizeof($temp_values)) {
           array_push($temp_keys, 'Merchant Defined Field '.$i);
           $i++;
        }

        // combine the keys and values arrays into the $response array.  This
        // can be done with the array_combine() function instead if you are using
        // php 5.
        for ($i=0; $i<sizeof($temp_values);$i++) {
           $this->response["$temp_keys[$i]"] = $temp_values[$i];
        }
        // $this->dump_response();
        
        // Prepare to return the transaction id for this sale.
        if(str_replace('"',"",$this->response['Response Code']) == 1) {
          $sale = str_replace('"',"",$this->response['Transaction ID']);
        }
      }
      else {
        // Process free orders without sending to the Auth.net gateway
        $this->response['Transaction ID'] = 'MT-' . Cart66Common::getRandString();
        $sale = $this->response['Transaction ID'];
      }
      
      return $sale;
   }
   
   function getResponseReasonText() {
      return $this->response['Response Reason Text'];
   }
   
   function getTransactionId() {
     return $this->response['Transaction ID'];
   }
   
   public function getTransactionResponseDescription() {
     $description['errormessage'] = $this->getResponseReasonText();
     $description['errorcode'] = $this->response['Response Reason Code'];
     $this->_logFields();
     $this->_logResponse();
     return $description;
   }
   
   protected function _logResponse() {
     $out = "Authorize.net Response Log\n";
     foreach ($this->response as $key => $value) {
       $out .= "\t$key = $value\n";
     }
     Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] $out");
   }
   
   protected function _logFields() {
     $out = "Authorize.net Field Log\n";
     foreach ($this->fields as $key => $value) {
        $out .= "\t$key = $value\n";
     }
     Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] $out");
   }

   function dumpFields() {
 
      // Used for debugging, this function will output all the field/value pairs
      // that are currently defined in the instance of the class using the
      // add_field() function.
      
      echo "<h3>authorizenet_class->dump_fields() Output:</h3>";
      echo "<table width=\"95%\" border=\"1\" cellpadding=\"2\" cellspacing=\"0\">
            <tr>
               <td bgcolor=\"black\"><b><font color=\"white\">Field Name</font></b></td>
               <td bgcolor=\"black\"><b><font color=\"white\">Value</font></b></td>
            </tr>"; 
            
      foreach ($this->fields as $key => $value) {
         echo "<tr><td>$key</td><td>".urldecode($value)."&nbsp;</td></tr>";
      }
 
      echo "</table><br>"; 
   }

   function dumpResponse() {
 
      // Used for debugging, this function will output all the response field
      // names and the values returned for the payment submission.  This should
      // be called AFTER the process() function has been called to view details
      // about authorize.net's response.
      
      echo "<h3>authorizenet_class->dump_response() Output:</h3>";
      echo "<table width=\"95%\" border=\"1\" cellpadding=\"2\" cellspacing=\"0\">
            <tr>
               <td bgcolor=\"black\"><b><font color=\"white\">Index&nbsp;</font></b></td>
               <td bgcolor=\"black\"><b><font color=\"white\">Field Name</font></b></td>
               <td bgcolor=\"black\"><b><font color=\"white\">Value</font></b></td>
            </tr>";
            
      $i = 0;
      foreach ($this->response as $key => $value) {
         echo "<tr>
                  <td valign=\"top\" align=\"center\">$i</td>
                  <td valign=\"top\">$key</td>
                  <td valign=\"top\">$value&nbsp;</td>
               </tr>";
         $i++;
      } 
      echo "</table><br>";
   }    



}
