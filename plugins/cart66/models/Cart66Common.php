<?php
class Cart66Common {

  /**
   * Return the string to use as the input id while keeping track of 
   * how many times a product is rendered to make sure there are no 
   * conflicting input ids.
   *
   * @param int $id - The databse id for the product
   * @return string
   */
  public static function getButtonId($id) {
    global $cart66CartButtons;

    $idSuffix = '';

    if(!is_array($cart66CartButtons)) {
      $cart66CartButtons = array();
    }

    if(in_array($id, array_keys($cart66CartButtons))) {
      $cart66CartButtons[$id] += 1;
    }
    else {
      $cart66CartButtons[$id] = 1;
    }

    if($cart66CartButtons[$id] > 1) {
      $idSuffix = '_' . $cart66CartButtons[$id];
    }

    $id .= $idSuffix;

    return $id;
  }
 
  /**
   * Strip all non numeric characters, then format the phone number.
   * 
   * Phone numbers are formatted as follows:
   *  7 digit phone numbers: 266-1789
   *  10 digit phone numbers: (804) 266-1789
   * 
   * @return string
   */
  public static function formatPhone($phone) {
  	$phone = preg_replace("/[^0-9]/", "", $phone);
  	if(strlen($phone) == 7)
  		return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
  	elseif(strlen($phone) == 10)
  		return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
  	else
  		return $phone;
  }

  public function isRegistered() {
    $setting = new Cart66Setting();
    $orderNumber = Cart66Setting::getValue('order_number');
    $isRegistered = ($orderNumber !== false) ? true : false;
    return $isRegistered;
  }
  
  public static function activePromotions() {
    $active = false;
    $promo = new Cart66Promotion();
    $promos = $promo->getModels();
    if(count($promos)) {
      $active = true;
    }
    return $active;
  }
  
  public static function showValue($value) {
    echo isset($value)? $value : '';
  }
  
  public static function getView($filename, $data=null) {

    $unregistered = '';
    if(strpos($filename, 'admin') !== false) {
      if(CART66_PRO && !self::isRegistered()) {
        $hardCoded = '';
        $settingsUrl = get_bloginfo('wpurl') . '/wp-admin/admin.php?page=cart66-settings';
        if(CART66_ORDER_NUMBER !== false) {
          $hardCoded = "<br/><br/><em>An invalid order number has be hard coded<br/> into the main cart66.php file.</em>";
        }
        $unregistered = '
          <div class="unregistered">
            This is not a registered copy of Cart66.<br/>
            Please <a href="' . $settingsUrl . '">enter your order number</a> or
            <a href="http://www.cart66.com/pricing">buy a license for your site.</a> ' . $hardCoded . '
          </div>
        ';
      }
    }

    $filename = WP_PLUGIN_DIR . "/cart66/$filename"; 
    ob_start();
    include $filename;
    $contents = ob_get_contents();
    ob_end_clean();

    return $unregistered . $contents;
  }
  
  public static function getTableName($name, $prefix='cart66_'){
      global $wpdb;
      return $wpdb->prefix . $prefix . $name;
  }
  
  public static function getTablePrefix(){
      global $wpdb;
      return $wpdb->prefix . "cart66_";
  }
  
  /**
   * If CART66_DEBUG is defined as true and a log file exists in the root of the Cart66 plugin directory, log the $data
   */
  public static function log($data) {
    if(defined('CART66_DEBUG') && CART66_DEBUG) {
      $date = date('m/d/Y g:i:s a');
      $header = "\n\n[LOG DATE: $date]\n";
      $filename = WP_PLUGIN_DIR . "/cart66/log.txt"; 
      if(file_exists($filename) && is_writable($filename)) {
        file_put_contents($filename, $header . $data, FILE_APPEND);
      }
    }
  }

  public static function getRandNum($numChars = 7) {
    $id = '';
		mt_srand((double)microtime()*1000000);
		for ($i = 0; $i < $numChars; $i++) { 
			$id .= chr(mt_rand(ord(0), ord(9)));
		}
		return $id;
	}
	
	public static function getRandString($length = 14) {
	  $string = '';
    $chrs = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    for($i=0; $i<14; $i++) {
      $loc = mt_rand(0, strlen($chrs)-1);
      $string .= $chrs[$loc];
    }
	  return $string;
	} 
  
  public static function camel2human($val) {
    $val = strtolower(preg_replace('/([A-Z])/', ' $1', $val));
    return $val;
  }
  
  /**
   * Return the account id if the visitor is logged in, otherwise false.
   * This function has nothing to do with feature levels or subscription status
   * 
   * @return int or false
   */
  public static function isLoggedIn() {
    $isLoggedIn = false;
    if(isset($_SESSION['Cart66AccountId']) && is_numeric($_SESSION['Cart66AccountId']) && $_SESSION['Cart66AccountId'] > 0) {
      $isLoggedIn = $_SESSION['Cart66AccountId'];
    }
    return $isLoggedIn;
  }

  
  public static function awardCommission($orderId, $referrer) {
    global $wpdb;
    if (!empty($referrer)) {
      $order = new Cart66Order($orderId);
      if($order->id > 0) {
        $subtractAmount = 0;
        $discount = $order->discountAmount;
        foreach($order->getItems() as $item) {
          $price = $item->product_price * $item->quantity;

          if($price > $discount) {
            $subtractAmount = $discount;
            $discount = 0;
          }
          else {
            $subtractAmount = $price;
            $discount = $discount - $price;
          }

          if($subtractAmount > 0) {
            $price = $price - $subtractAmount;
          }
          
          // Transaction if for commission is the id in th order items table
          $txn_id = $item->id;
          $sale_amount = $price;
          $item_id = $item->item_number;
          $buyer_email = $order->email;

          // Make sure commission has not already been granted for this transaction
          $aff_sales_table = $wpdb->prefix . "affiliates_sales_tbl";
          $txnCount = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM $aff_sales_table where txn_id = %s", $txn_id));
          if($txnCount < 1) {
            wp_aff_award_commission($referrer,$sale_amount,$txn_id,$item_id,$buyer_email);
          }
        }
        
      }
    }
  }
  
  /**
   * Return true if the email address is not empty and has a valid format
   * 
   * @param string $email The email address to validate
   * @return boolean Empty or invalid email addresses return false, otherwise true
   */
  public static function isValidEmail($email) {
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Checking to see if email address is valid: $email");
    $pattern = '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}\b/';
    $isValid = false;
    if(!empty($email) && strlen($email) > 3) {
      if(preg_match($pattern, $email)) {
        $isValid = true;
      }
      else {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Email validation failed because address is invalid: $email");
      }
    }
    else {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Email validation failed because address is empty: $email");
    }
    return $isValid;
  }
  
  public static function isEmailUnique($email, $exceptId=0) {
    global $wpdb;
    $accounts = self::getTableName('accounts');
    $sql = "SELECT count(*) as c from $accounts where email = %s and id != %d";
    $sql = $wpdb->prepare($sql, $email, $exceptId);
    $count = $wpdb->get_var($sql);
    $isUnique = $count == 0;
    return $isUnique;
  }
  
  
  /**
   * Configure mail for use with either standard wp_mail or when using the WP Mail SMTP plugin
   */
  public static function mail($to, $subject, $msg, $headers=null) {
    //Disable mail headers if the WP Mail SMTP plugin is in use.
    if(function_exists('wp_mail_smtp_activate')) { $headers = null; }
    return wp_mail($to, $subject, $msg, $headers);
  }
  
  public static function randomString($numChars = 7) {
		$letters = "";
		mt_srand((double)microtime()*1000000);
		for ($i = 0; $i < $numChars; $i++) { 
			$randval = chr(mt_rand(ord("a"), ord("z")));
			$letters .= $randval;
		}
		return $letters;
	}
	
	public static function isValidDate($val) {
	  $isValid = false;
		if(preg_match("/\d{1,2}\/\d{1,2}\/\d{4}/", $val)) {
			list($month, $day, $year) = split("/", $val);
			if(is_numeric($month) && is_numeric($day) && is_numeric($year) ) {
				if($month > 12 || $month < 1) {
					$isValid = false;
				}
				elseif($day > 31 || $day < 1) {
					$isValid = false;
				}
				elseif($year < 1900) {
					$isValid = false;
				}
				else {
					$isValid = true;
				}
			}
		}
		return $isValid;
	}

  /**
   * Strip slashes and escape sequences from POST values and returened the scrubbed value.
   * If the key is not set, return false.
   */
  public static function postVal($key) {
    $value = false;
    if(isset($_POST[$key])) {
      $value = $_POST[$key];
      if(is_scalar($value)) {
        // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] PostVal before cleanup: $value");
        $value = strip_tags($value);
        $value = preg_replace('/[<>\\\\\/]/', '', $value);
        // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] PostVal after cleanup: $value");
      }
    }
    return $value;
  }

  /**
   * Strip slashes and escape sequences from GET values and returened the scrubbed value.
   * If the key is not set, return false.
   */
  public static function getVal($key) {
    $value = false;
    if(isset($_GET[$key])) {
      $value = strip_tags($_GET[$key]);
      $value = preg_replace('/[<>\\\\\/]/', '', $value);
    }
    return $value;
  }
  
  public static function getCountryName($code) {
    $countries = self::getCountries(true);
    return $countries[$code];
  }

  public static function getCountries($all=false) {
    $countries = array(
       'AR'=>'Argentina',
       'AU'=>'Australia',
       'AT'=>'Austria',
       'BS'=>'Bahamas',
       'BE'=>'Belgium',
       'BR'=>'Brazil',
       'BG'=>'Bulgaria',
       'CA'=>'Canada',
       'CL'=>'Chile',
       'CN'=>'China',
       'CO'=>'Colombia',
       'CR'=>'Costa Rica',
       'HR'=>'Croatia',
       'CY'=>'Cyprus',
       'CZ'=>'Czech Republic',
       'DK'=>'Denmark',
       'EC'=>'Ecuador',
       'EE'=>'Estonia',
       'FI'=>'Finland',
       'FR'=>'France',
       'DE'=>'Germany',
       'GR'=>'Greece',
       'GP'=>'Guadeloupe',
       'HK'=>'Hong Kong',
       'HU'=>'Hungary',
       'IS'=>'Iceland',
       'IN'=>'India',
       'ID'=>'Indonesia',
       'IE'=>'Ireland',
       'IL'=>'Israel',
       'IT'=>'Italy',
       'JM'=>'Jamaica',
       'JP'=>'Japan',
       'LV'=>'Latvia',
       'LT'=>'Lithuania',
       'LU'=>'Luxembourg',
       'MY'=>'Malaysia',
       'MT'=>'Malta',
       'MX'=>'Mexico',
       'NL'=>'Netherlands',
       'NZ'=>'New Zealand',
       'NO'=>'Norway',
       'PE'=>'Peru',
       'PH'=>'Philippines',
       'PL'=>'Poland',
       'PT'=>'Portugal',
       'PR'=>'Puerto Rico',
       'RO'=>'Romania',
       'RU'=>'Russia',
       'SG'=>'Singapore',
       'SK'=>'Slovakia',
       'SI'=>'Slovenia',
       'ZA'=>'South Africa',
       'KR'=>'South Korea',
       'ES'=>'Spain',
       'VC'=>'St. Vincent',
       'SE'=>'Sweden',
       'CH'=>'Switzerland',
       'SY'=>'Syria',
       'TW'=>'Taiwan',
       'TH'=>'Thailand',
       'TT'=>'Trinidad and Tobago',
       'TR'=>'Turkey',
       'AE'=>'United Arab Emirates',
       'GB'=>'United Kingdom',
       'US'=>'United States',
       'UY'=>'Uruguay',
       'VE'=>'Venezuela');
    
    // Put home country at the top of the list
    $setting = new Cart66Setting();
    $home_country = Cart66Setting::getValue('home_country');
    if($home_country) {
      list($code, $name) = explode('~', $home_country);
      $countries = array_merge(array($code => $name), $countries);
    }
    else {
      $countries = array_merge(array('US' => 'United States'), $countries);
    }

    $customCountries = self::getCustomCountries();
    
    if($all) {
      if(is_array($customCountries)) {
        foreach($customCountries as $code => $name) {
          unset($countries[$code]);
        }
        foreach($countries as $code => $name) {
          $customCountries[$code] = $name;
        }
        $countries = $customCountries;
      }
    }
    else {
      $international = Cart66Setting::getValue('international_sales');
      if($international) {
        if($customCountries) {
          //Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Got some custom countries: " . print_r($customCountries, true));
          $countries = $customCountries;
        }
      }
      else {
        $countries = array_slice($countries, 0, 1, true); 
      }
    }
    
    
    
    return $countries;
  }

  public static function getCustomCountries() {
    $list = false;
    $setting = new Cart66Setting();
    $countries = Cart66Setting::getValue('countries');
    if($countries) {
      $countries = explode(',', $countries);
      foreach($countries as $c) {
        list($code, $name) = explode('~', $c);
        $list[$code] = $name;
      }
    }
    return $list;
  }
  
  public static function getPayPalCurrencyCodes() {
    $currencies = array(
      'United States Dollar' => 'USD',
      'Australian Dollar' => 'AUD',
      'Canadian Dollar' => 'CAD',
      'Czech Koruna' => 'CZK',
      'Danish Krone' => 'DKK',
      'Euro' => 'EUR',
      'Hong Kong Dollar' => 'HKD',
      'Hungarian Forint' => 'HUF',
      'Israeli New Sheqel' => 'ILS',
      'Japanese Yen' => 'JPY',
      'Mexican Peso' => 'MXN',
      'Norwegian Krone' => 'NOK',
      'New Zealand Dollar' => 'NZD',
      'Philippine Peso' => 'PHP',
      'Polish Zloty' => 'PLN',
      'Pound Sterling' => 'GBP',
      'Singapore Dollar' => 'SGD',
      'Swedish Krona' => 'SEK',
      'Swiss Franc' => 'CHF',
      'Taiwan New Dollar' => 'TWD',
      'Thai Baht' => 'THB'
    );
    return $currencies;
  }

  
  public static function getZones($code) {
    $zones = array('0' => '&nbsp;');
    switch ($code) {
      case 'AU':
        $zones['NSW'] = 'New South Wales';
        $zones['NT'] = 'Northern Territory';
        $zones['QLD'] = 'Queensland';
        $zones['SA'] = 'South Australia';
        $zones['TAS'] = 'Tasmania';
        $zones['VIC'] = 'Victoria';
        $zones['WA'] = 'Western Australia';
        break;
	  case 'BR':
		$zones['Acre'] = 'Acre';
		$zones['Alagoas'] = 'Alagoas';
		$zones['Amapa'] = 'Amapa';
		$zones['Amazonas'] = 'Amazonas';
		$zones['Bahia'] = 'Bahia';
		$zones['Ceara'] = 'Ceara';
		$zones['Distrito Federal'] = 'Distrito Federal';
		$zones['Espirito Santo'] = 'Espirito Santo';
		$zones['Goias'] = 'Goias';
		$zones['Maranhao'] = 'Maranhao';
		$zones['Mato Grosso'] = 'Mato Grosso';
		$zones['Mato Grosso do Sul'] = 'Mato Grosso do Sul';
		$zones['Minas Gerais'] = 'Minas Gerais';
		$zones['Para'] = 'Para';
		$zones['Paraiba'] = 'Paraiba';
		$zones['Parana'] = 'Parana';
		$zones['Pernambuco'] = 'Pernambuco';
		$zones['Piaui'] = 'Piaui';
		$zones['Rio de Janeiro'] = 'Rio de Janeiro';
		$zones['Rio Grande do Norte'] = 'Rio Grande do Norte';
		$zones['Rio Grande do Sul'] = 'Rio Grande do Sul';
		$zones['Rondonia'] = 'Rondonia';
		$zones['Roraima'] = 'Roraima';
		$zones['Santa Catarina'] = 'Santa Catarina';
		$zones['Sao Paulo'] = 'Sao Paulo';
		$zones['Sergipe'] = 'Sergipe';
		$zones['Tocantins'] = 'Tocantins';
		break;
      case 'CA':
        $zones['AB'] = 'Alberta';
        $zones['BC'] = 'British Columbia';
        $zones['MB'] = 'Manitoba';
        $zones['NB'] = 'New Brunswick';
        $zones['NF'] = 'Newfoundland';
        $zones['NT'] = 'Northwest Territories';
        $zones['NS'] = 'Nova Scotia';
        $zones['NU'] = 'Nunavut';
        $zones['ON'] = 'Ontario';
        $zones['PE'] = 'Prince Edward Island';
        $zones['PQ'] = 'Quebec';
        $zones['SK'] = 'Saskatchewan';
        $zones['YT'] = 'Yukon Territory';
        break;
      case 'US':
        $zones['AL'] = 'Alabama';
        $zones['AK'] = 'Alaska ';
        $zones['AZ'] = 'Arizona';
        $zones['AR'] = 'Arkansas';
        $zones['CA'] = 'California ';
        $zones['CO'] = 'Colorado';
        $zones['CT'] = 'Connecticut';
        $zones['DE'] = 'Delaware';
        $zones['DC'] = 'D. C.';
        $zones['FL'] = 'Florida';
        $zones['GA'] = 'Georgia ';
        $zones['HI'] = 'Hawaii';
        $zones['ID'] = 'Idaho';
        $zones['IL'] = 'Illinois';
        $zones['IN'] = 'Indiana';
        $zones['IA'] = 'Iowa';
        $zones['KS'] = 'Kansas';
        $zones['KY'] = 'Kentucky';
        $zones['LA'] = 'Louisiana';
        $zones['ME'] = 'Maine';
        $zones['MD'] = 'Maryland';
        $zones['MA'] = 'Massachusetts';
        $zones['MI'] = 'Michigan';
        $zones['MN'] = 'Minnesota';
        $zones['MS'] = 'Mississippi';
        $zones['MO'] = 'Missouri';
        $zones['MT'] = 'Montana';
        $zones['NE'] = 'Nebraska';
        $zones['NV'] = 'Nevada';
        $zones['NH'] = 'New Hampshire';
        $zones['NJ'] = 'New Jersey';
        $zones['NM'] = 'New Mexico';
        $zones['NY'] = 'New York';
        $zones['NC'] = 'North Carolina';
        $zones['ND'] = 'North Dakota';
        $zones['OH'] = 'Ohio';
        $zones['OK'] = 'Oklahoma';
        $zones['OR'] = 'Oregon';
        $zones['PA'] = 'Pennsylvania';
        $zones['RI'] = 'Rhode Island';
        $zones['SC'] = 'South Carolina';
        $zones['SD'] = 'South Dakota';
        $zones['TN'] = 'Tennessee';
        $zones['TX'] = 'Texas';
        $zones['UT'] = 'Utah';
        $zones['VT'] = 'Vermont';
        $zones['VA'] = 'Virginia';
        $zones['WA'] = 'Washington';
        $zones['WV'] = 'West Virginia';
        $zones['WI'] = 'Wisconsin';
        $zones['WY'] = 'Wyoming';
        $zones['AE'] = 'Armed Forces';
        break;
    }
    return $zones;
  }
  


  /**
   * Return a link to the "view cart" page
   */
  public static function getPageLink($path) {
    $page = get_page_by_path($path);
    $link = get_permalink($page->ID);
    if($_SERVER['SERVER_PORT'] == '443') {
      $link = str_replace('http://', 'https://', $link);
    }
    return $link;
  }

  /**
   * Make sure path ends in a trailing slash by looking for trailing slash and add if necessary
   */
  public static function scrubPath($path) {
    if(stripos(strrev($path), '/') !== 0) {
      $path .= '/';
    }
    return $path;
  }

  /**
   * Return an array of order status options
   * If no options have been set by the user, 'new' is the only returned option
   */
  public static function getOrderStatusOptions() {
    $statuses = array();
    $setting = new Cart66Setting();
    $opts = Cart66Setting::getValue('status_options');
    if(!empty($opts)) {
      $opts = explode(',', $opts);
      foreach($opts as $o) {
        $statuses[] = trim($o);
      }
    }
    if(count($statuses) == 0) {
      $statuses[] = 'new';
    }
    return $statuses;
  }

  public function getPromoMessage() {
    $promo = $_SESSION['Cart66Cart']->getPromotion();
    $promoMsg = "none";
    if($promo) {
      $promoMsg = $promo->code . ' (-' . CURRENCY_SYMBOL . number_format($_SESSION['Cart66Cart']->getDiscountAmount(), 2) . ')';
    }
    return $promoMsg;
  }

  public function showErrors($errors, $message=null) {
    $out = "<div id='cart66Errors' class='Cart66Error'>";
    if(empty($message)) {
      $out .= "<p><b>We're sorry.<br/>Your order could not be completed for the following reasons:</b></p>";
    }
    else {
      $out .= $message;
    }
    $out .= '<ul>';
    if(is_array($errors)) {
      foreach($errors as $key => $value) {
        $value = strtolower($value);
        $out .= "<li>$value</li>";
      }
    }
    else {
      $out .= "<li>$errors</li>";
    }
    $out .= "</ul></div>";
    return $out;
  }
  
  public function getJqErrorScript(array $jqErrors) {
    $script = '
<script type="text/javascript">
var $jq = jQuery.noConflict();
$jq(document).ready(function() { 
_script_here_ 
});
</script>';

    if(count($jqErrors)) {
      $lines = '';
      foreach($jqErrors as $val) {
        $lines .= "  \$jq('#$val').addClass('errorField);\n";
      }
    }
    $lines  = rtrim($lines, "\n");
    $script = str_replace('_script_here_', $lines, $script);
    return $script;
  }

  
  public static function getEmailReceiptMessage($order) {
    $product = new Cart66Product();
    
    $msg = "ORDER NUMBER: " . $order->trans_id . "\n\n";
    $hasDigital = false;
    foreach($order->getItems() as $item) {
      $product->load($item->product_id);
      if($hasDigital == false) {
        $hasDigital = $product->isDigital();
      }
      $price = $item->product_price * $item->quantity;
      // $msg .= "Item: " . $item->item_number . ' ' . $item->description . "\n";
      $msg .= "Item: " . $item->description . "\n";
      if($item->quantity > 1) {
        $msg .= "Quantity: " . $item->quantity . "\n";
      }
      $msg .= "Item Price: " . CURRENCY_SYMBOL_TEXT . number_format($item->product_price, 2) . "\n";
      $msg .= "Item Total: " . CURRENCY_SYMBOL_TEXT . number_format($item->product_price * $item->quantity, 2) . "\n\n";
      
      if($product->isGravityProduct()) {
        $msg .= Cart66GravityReader::displayGravityForm($item->form_entry_ids, true);
      }
    }

    if($order->shipping_method != 'None' && $order->shipping_method != 'Download') {
      $msg .= "Shipping: " . CURRENCY_SYMBOL_TEXT . $order->shipping . "\n";
    }

    if(!empty($order->coupon) && $order->coupon != 'none') {
      $msg .= "Coupon: " . $order->coupon . "\n";
    }

    if($order->tax > 0) {
      $msg .= "Tax: " . CURRENCY_SYMBOL_TEXT . number_format($order->tax, 2) . "\n";
    }

    $msg .= "\nTOTAL: " . CURRENCY_SYMBOL_TEXT . number_format($order->total, 2) . "\n";

    if($order->shipping_method != 'None' && $order->shipping_method != 'Download') {
      $msg .= "\n\nSHIPPING INFORMATION\n\n";

      $msg .= $order->ship_first_name . ' ' . $order->ship_last_name . "\n";
      $msg .= $order->ship_address . "\n";
      if(!empty($order->ship_address2)) {
        $msg .= $order->ship_address2 . "\n";
      }
      $msg .= $order->ship_city . ' ' . $order->ship_state . ' ' . $order->ship_zip . "\n" . $order->ship_country . "\n";

      $msg .= "\nDelivery via: " . $order->shipping_method . "\n";
    }


    $msg .= "\n\nBILLING INFORMATION\n\n";

    $msg .= $order->bill_first_name . ' ' . $order->bill_last_name . "\n";
    $msg .= $order->bill_address . "\n";
    if(!empty($order->bill_address2)) {
      $msg .= $order->bill_address2 . "\n";
    }
    $msg .= $order->bill_city . ' ' . $order->bill_state . ' ' . $order->bill_zip . "\n" . $order->bill_country . "\n";

    if(!empty($order->phone)) {
      $phone = self::formatPhone($order->phone);
      $msg .= "\nPhone: $phone\n";
    }
    
    if(!empty($order->email)) {
      $msg .= 'Email: ' . $order->email . "\n";
    }

    $receiptPage = get_page_by_path('store/receipt');
    $link = get_permalink($receiptPage->ID);
    if(strstr($link,"?")){
      $link .= '&ouid=' . $order->ouid;
    }
    else{
      $link .= '?ouid=' . $order->ouid;
    }

    if($hasDigital) {
      $msg .= "\nDOWNLOAD LINK\nClick the link below to download your order.\n$link";
    }
    else {
      $msg .= "\nVIEW RECEIPT ONLINE\nClick the link below to view your receipt online.\n$link";
    }
    
    $setting = new Cart66Setting();
    $msgIntro = Cart66Setting::getValue('receipt_intro');
    $msg = $msgIntro . " \n----------------------------------\n\n" . $msg;
    
    return $msg;
  }
  
  /**
   * Return the WP_CONTENT_URL taking into account HTTPS and the possibility that WP_CONTENT_URL may not be defined
   * 
   * @return string
   */
  public static function getWpContentUrl() {
    $wpurl = WP_CONTENT_URL;
    if(empty($wpurl)) {
      $wpurl = get_bloginfo('wpurl') . '/wp-content';
    }
    if(self::isHttps()) {
      $wpurl = str_replace('http://', 'https://', $wpurl);
    }
    return $wpurl;
  }
  
  /**
   * Return the WordPress URL taking into account HTTPS
   */
  public static function getWpUrl() {
    $wpurl = get_bloginfo('wpurl');
    if(self::isHttps()) {
      $wpurl = str_replace('http://', 'https://', $wpurl);
    }
    return $wpurl;
  }
  
  /**
   * Detect if request occurred over HTTPS and, if so, return TRUE. Otherwise return FALSE.
   * 
   * @return boolean
   */
  public static function isHttps() {
    $isHttps = false;
    if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
      $isHttps = true;
    }
    return $isHttps;
  }
  
  
  public static function getCurrentPageUrl() {
    $protocol = 'http://';
    if(self::isHttps()) {
      $protocol = 'https://';
    }
    $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return $url;
  }
  
  /**
   * Attach a string of name/value pairs to a URL for the current page
   * This function looks for the presence of a ? and appropriately appends the new parameters.
   * Return a URL for the current page with the appended params.
   * 
   * @return string
   */
  public function appendQueryString($nvPairs) {
    $url = self::getCurrentPageUrl();
    $url .= strpos($url, '?') ? '&' : '?';
    $url .= $nvPairs;
    return $url;
  }
  
  /**
   * Replace the query string for the current page url
   * 
   * @param string Name value pairs formatted as name1=value1&name2=value2
   * @return string The URL to the current page with the given query string
   */
  public function replaceQueryString($nvPairs=false) {
    $url = explode('?', self::getCurrentPageUrl());
    $url = $url[0];
    if($nvPairs) {
      $url .= '?' . $nvPairs;
    }
    return $url;
  }
  

  
  public static function serializeSimpleXML(SimpleXMLElement $xmlObj) {
    return serialize($xmlObj->asXML());
  }
  
  public static function unserializeSimpleXML($str) {
    return simplexml_load_string(unserialize($str));
  }
  
  /**
   * Return either the live or the sandbox PayPal URL based on whether or not paypal_sandbox is set.
   */
  public static function getPayPalUrl() {
    $paypalUrl='https://www.paypal.com/cgi-bin/webscr';
    if(Cart66Setting::getValue('paypal_sandbox')) {
      $paypalUrl='https://www.sandbox.paypal.com/cgi-bin/webscr';
    }
    return $paypalUrl;
  }
  
  public function curl($url, $method='GET') {
    $method = strtoupper($method);
    
    // Make sure curl is installed?
    if (!function_exists('curl_init')){ 
      throw new Cart66Exception('cURL is not installed!');
    }

    // create a new curl resource
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if($method == 'POST') {
      curl_setopt($ch, CURLOPT_POST, true);
    }
    
    $output = curl_exec($ch);

    // close the curl resource, and free system resources
    curl_close($ch);

    return $output;
  }
  
  public static function downloadFile($path) {
    
    // Validate the $path
    if(!strpos($path, '://')) {
      if($productFolder = Cart66Setting::getValue('product_folder')) {
        if(strpos($path, $productFolder) === 0) {
          // Erase and close all output buffers
          while (@ob_end_clean());

          // Get the name of the file to be downloaded
          $fileName = basename($path);
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Download file name: $fileName");

          // This is required for IE, otherwise Content-disposition is ignored
          if(ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
          }

          $bytes = 'unknown';
          if(substr($path, 0, 4) == 'http') {
            $bytes = Cart66Common::remoteFileSize($path);
          }
          else {
            $bytes = filesize($path);
          }
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Download file size: $bytes");

          ob_start();
          header("Pragma: public");
          header("Expires: 0");
          header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
          header("Cache-Control: private",false);
          header("Content-Type: application/octet-stream;");
          header("Content-Disposition: attachment; filename=\"".$fileName."\";" );
          header("Content-Transfer-Encoding: binary");
          header("Content-Length: $bytes");

          //open the file and stream download
          if($fp = fopen($path, 'rb')) {
            while(!feof($fp)) {
              //reset time limit for big files
              @set_time_limit(0);
              echo fread($fp, 1024*8);
              flush();
              ob_flush();
            }
            fclose($fp);
          }
          else {
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] fopen failed to open path: $path");
          }
        }
        else {
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Unable to download file because the requested file is not in the path defined by the product folder settings: $path");
        }
      }
      else {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Unable to download file because the product folder is not set.");
      }
    }
    
  }
  
  public static function remoteFileSize($remoteFile) {
    $ch = curl_init($remoteFile);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
    $data = curl_exec($ch);
    curl_close($ch);
    $contentLength = 'unknown';
    if ($data !== false) {
      if (preg_match('/Content-Length: (\d+)/', $data, $matches)) {
        $contentLength = (int)$matches[1];
      }
    }
    return $contentLength;
  }
  
  public static function onlyUsingPayPalStandard() {
    $onlyPayPalStandard = false;
    if(Cart66Setting::getValue('paypal_email')) {
      $onlyPayPalStandard = true;
    }
    
    if(Cart66Setting::getValue('auth_username') || Cart66Setting::getValue('paypalpro_api_username')) {
      $onlyPayPalStandard = false;
    }
    
    return $onlyPayPalStandard;
  }
  
}
