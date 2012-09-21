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
    if($promo->getOne()) {
      $active = true;
    }
    return $active;
  }
  
  public static function showValue($value) {
    echo isset($value)? $value : '';
  }
  
  public static function getView($filename, $data=null, $notices=true) {
    $notice = '';
    if(strpos($filename, 'admin') !== false) {
      $mijireh_notice = Cart66Setting::getValue('mijireh_notice');
      if($mijireh_notice != 1) {
        if($notices) {
          $notice = '<div id="mijireh_notice" class="mijireh-info alert-message info" data-alert="alert">
          <a href="#" id="mijireh_dismiss" class="close">&times;</a>
            <div class="mijireh-logo"><img src="' . CART66_URL . '/images/mijireh-checkout-logo.png" alt="Mijireh Checkout Logo"></div>
            <div class="mijireh-blurb">
            <h2>Cart66 now supports Mijireh Checkout!</h2>
            <p>Accept credit cards on a fully PCI compliant ecommerce platform</p>
            <ul>
              <li>No need for SSL certificates or a dedicated IP addresss</li>
              <li>No need to pay for for quarterly security scans</li>
              <li>No need to give up control of your design</li>
            </ul>
            <h1><a href="http://mijireh.com" target="_new">Start now for FREE!</a></h1>
            </div>
          </div>';
        }
      }
      
      if(CART66_PRO && !self::isRegistered()) {
        $hardCoded = '';
        $settingsUrl = get_bloginfo('wpurl') . '/wp-admin/admin.php?page=cart66-settings';
        if(CART66_ORDER_NUMBER !== false) {
          $hardCoded = "<br/><br/><em>An invalid order number has be hard coded<br/> into the main cart66.php file.</em>";
        }
        if($notices) {
          $notice .= '
            <div class="unregistered alert-message alert-error">
              <p>This is not a registered copy of Cart66.<br/>
              Please <a href="' . $settingsUrl . '">enter your order number</a> or
              <a href="http://www.cart66.com/pricing">buy a license for your site.</a> ' . $hardCoded . '
              </p>
            </div>
          ';
        }
      }
      
    }

    $customView = false;
    $themeDirectory = get_stylesheet_directory();
    $approvedOverrideFiles = array(
      "views/cart.php",
      "views/cart-button.php",
      "views/checkout.php",
      "views/account-login.php",
      "views/cart-sidebar.php",
      "views/cart-sidebar-advanced.php",
      "views/receipt.php",
      "views/receipt_print_version.php",
      "views/paypal-express.php",
      "pro/views/terms.php",
      "pro/views/emails/default-email-followup.php",
      "pro/views/emails/default-email-fulfillment.php",
      "pro/views/emails/default-email-receipt.php",
      "pro/views/emails/default-email-reminder.php",
      "pro/views/emails/default-email-status.php",
      "pro/views/emails/email-products.php",
      "pro/views/emails/email-receipt.php",
    );
    $overrideDirectory = $themeDirectory."/cart66-templates";
    $userViewFile = $overrideDirectory."/$filename";
    
    //Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Override: $overrideDirectory\nUser view file: $userViewFile");
    
    if(file_exists($userViewFile) && in_array($filename,$approvedOverrideFiles)) {
      // File exists, make sure it's not empty
      if(filesize($userViewFile)>10) {
        // It's not empty
        $customView = true;
        $customViewPath = $userViewFile;
      }
      else{
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] User file was empty: $userViewFile");
      }
    }
    else{
      // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] File exists: ".var_export(file_exists($userViewFile),true)."\n");
      // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Approved Override: ".var_export(in_array($filename,$approvedOverrideFiles),true));
    }
  
    // Check for override and confirm we have a registered plugin
    if($customView && CART66_PRO && self::isRegistered()) {
      // override is present
      $filename = $customViewPath;
    }
    else {
      // no override, render standard view
      $filename = CART66_PATH . "/$filename";
    }
    
    ob_start();
    include $filename;
    $contents = ob_get_contents();
    ob_end_clean();
    
    return $notice . $contents;
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
      $tz = '- Server time zone ' . date('T');
      $date = date('m/d/Y g:i:s a', self::localTs());
      $header = strpos($_SERVER['REQUEST_URI'], 'wp-admin') ? "\n\n======= ADMIN REQUEST =======\n[LOG DATE: $date $tz]\n" : "\n\n[LOG DATE: $date $tz]\n";
      $filename = CART66_PATH . "/log.txt"; 
      if(file_exists($filename) && is_writable($filename)) {
        file_put_contents($filename, $header . $data, FILE_APPEND);
      }
    }
    
  }
  
  public static function clearLog(){
    $filename = CART66_PATH . "/log.txt"; 
    if(file_exists($filename) && is_writable($filename)) {
      file_put_contents($filename, '');
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
    for($i=0; $i<$length; $i++) {
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
    if(Cart66Session::get('Cart66AccountId') && is_numeric(Cart66Session::get('Cart66AccountId')) && Cart66Session::get('Cart66AccountId') > 0) {
      $isLoggedIn = Cart66Session::get('Cart66AccountId');
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
          $txn_id = $order->trans_id;
          $sale_amount = $price;
          $item_id = $item->item_number;
          $buyer_email = $order->email;
          
          // Affiliate Royale
          do_action('wafp_award_commission', $referrer, $sale_amount, $txn_id, $item_id, $buyer_email); 

          if(function_exists('wp_aff_award_commission')) {
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
      $value = self::deepTagClean($_POST[$key]);
    }
    return $value;
  }
  
  public static function deepTagClean(&$data) {
    if(is_array($data)) {
      foreach($data as $key => $value) {
        if(is_array($value)) {
          $data[$key] = self::deepTagClean($value);
        }
        else {
          $value = strip_tags($value);
          $data[$key] = preg_replace('/[<>\\\\]/', '', $value);
        }
      }
    }
    else {
      $data= strip_tags($data);
      $data = preg_replace('/[<>\\\\]/', '', $data);;
    }
    return $data;
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
  
  /**
   * Get home country code from cart settings or return US if no setting exists
   * 
   * @return string
   */
  public static function getHomeCountryCode() {
    if($homeCountry = Cart66Setting::getValue('home_country')) {
      list($homeCountryCode, $dummy) = explode('~', $homeCountry); 
    }
    else {
      $homeCountryCode = 'US';
    }
    return $homeCountryCode;
  }
  
  public static function getCountryName($code) {
    $countries = self::getCountries(true);
    return $countries[$code];
  }

  public static function getLocaleCode() {
    $localeCode = false;
    $localeCodes = array('AU', 'AT', 'BE', 'CA', 'CH', 'CN', 'DE', 'ES', 'GB', 'FR', 'IT', 'NL', 'PL', 'US');
    if(in_array(self::getHomeCountryCode(), $localeCodes)) {
      $localeCode = self::getHomeCountryCode();
    }
    return $localeCode;
  }
  
  public static function getCountries($all=false) {
    $countries = array(
      'AF'=>'Afghanistan',
      'AX'=>'Åland Islands',
      'AL'=>'Albania',
      'DZ'=>'Algeria',
      'AD'=>'Andorra',
      'AO'=>'Angola',
      'AI'=>'Anguilla',
      'AG'=>'Antigua and Barbuda',
      'AR'=>'Argentina',
      'AM'=>'Armenia',
      'AW'=>'Aruba',
      'AU'=>'Australia',
      'AT'=>'Austria',
      'AZ'=>'Azerbaijan',
      'BS'=>'Bahamas',
      'BH'=>'Bahrain',
      'BD'=>'Bangladesh',
      'BB'=>'Barbados',
      'BY'=>'Belarus',
      'BE'=>'Belgium',
      'BZ'=>'Belize',
      'BJ'=>'Benin',
      'BM'=>'Bermuda',
      'BT'=>'Bhutan',
      'BO'=>'Bolivia',
      'BQ'=>'Bonaire',
      'BA'=>'Bosnia-Herzegovina',
      'BW'=>'Botswana',
      'BV'=>'Bouvet Island',
      'BR'=>'Brazil',
      'IO'=>'British Indian Ocean Territory',
      'BN'=>'Brunei',
      'BF'=>'Burkina Faso',
      'BI'=>'Burundi',
      'BG'=>'Bulgaria',
      'KH'=>'Cambodia',
      'CM'=>'Cameroon',
      'CA'=>'Canada',
      'CV'=>'Cape Verde',
      'KY'=>'Cayman Islands',
      'CF'=>'Central African Republic',
      'TD'=>'Chad',
      'CL'=>'Chile',
      'CN'=>'China',
      'CX'=>'Christmas Island',
      'CC'=>'Cocos Islands',
      'CO'=>'Colombia',
      'KM'=>'Comoros',
      'CD'=>'Democratic Republic of Congo',
      'CG'=>'Congo',
      'CK'=>'Cook Islands',
      'CR'=>'Costa Rica',
      'CI'=>'Côte d\'Ivoire',
      'HR'=>'Croatia',
      'CU'=>'Cuba',
      'CW'=>'Curaçao',
      'CY'=>'Cyprus',
      'CZ'=>'Czech Republic',
      'DK'=>'Denmark',
      'DJ'=>'Djibouti',
      'DM'=>'Dominica',
      'DO'=>'Dominican Republic',
      'EC'=>'Ecuador',
      'EG'=>'Egypt',
      'SV'=>'El Salvador',
      'GQ'=>'Equatorial Guinea',
      'ER'=>'Eritrea',
      'EE'=>'Estonia',
      'ET'=>'Ethiopia',
      'FK'=>'Falkland Islands',
      'FO'=>'Faroe Islands',
      'FJ'=>'Fiji',
      'FI'=>'Finland',
      'FR'=>'France',
      'GF'=>'French Guiana',
      'PF'=>'French Polynesia',
      'TF'=>'French Southern Territories',
      'GA'=>'Gabon',
      'GM'=>'Gambia',
      'GE'=>'Georgia',
      'DE'=>'Germany',
      'GH'=>'Ghana',
      'GI'=>'Gibraltar',
      'GR'=>'Greece',
      'GL'=>'Greenland',
      'GD'=>'Grenada',
      'GP'=>'Guadeloupe',
      'GU'=>'Guam',
      'GT'=>'Guatemala',
      'GN'=>'Guinea',
      'GW'=>'Guinea-Bissau',
      'GY'=>'Guyana',
      'HT'=>'Haiti',
      'VA'=>'Holy See',
      'HN'=>'Honduras',
      'HK'=>'Hong Kong',
      'HU'=>'Hungary',
      'IS'=>'Iceland',
      'IN'=>'India',
      'ID'=>'Indonesia',
      'IR'=>'Iran',
      'IQ'=>'Iraq',
      'IE'=>'Ireland',
      'IM'=>'Isle of Man',
      'IL'=>'Israel',
      'IT'=>'Italy',
      'JM'=>'Jamaica',
      'JP'=>'Japan',
      'JE'=>'Jersey',
      'JO'=>'Jordan',
      'KZ'=>'Kazakhstan',
      'KE'=>'Kenya',
      'KI'=>'Kiribati',
      'KW'=>'Kuwait',
      'KG'=>'Kyrgyzstan',
      'LA'=>'Laos',
      'LV'=>'Latvia',
      'LB'=>'Lebanon',
      'LS'=>'Lesotho',
      'LR'=>'Liberia',
      'LY'=>'Libya',
      'LI'=>'Liechtenstein',
      'LT'=>'Lithuania',
      'LU'=>'Luxembourg',
      'MO'=>'Macao',
      'MK'=>'Macedonia',
      'MG'=>'Madagascar',
      'MW'=>'Malawi',
      'MY'=>'Malaysia',
      'MV'=>'Maldives',
      'ML'=>'Mali',
      'MT'=>'Malta',
      'MH'=>'Marshall Islands',
      'MR'=>'Mauritania',
      'MU'=>'Mauritius',
      'YT'=>'Mayotte',
      'MX'=>'Mexico',
      'FM'=>'Micronesia',
      'MD'=>'Moldova',
      'MC'=>'Monaco',
      'MN'=>'Mongolia',
      'ME'=>'Montenegro',
      'MA'=>'Morocco',
      'MZ'=>'Mozambique',
      'MM'=>'Myanmar',
      'NA'=>'Namibia',
      'NR'=>'Nauru',
      'NP'=>'Nepal',
      'NL'=>'Netherlands',
      'NZ'=>'New Zealand',
      'NI'=>'Nicaragua',
      'NE'=>'Niger',
      'NG'=>'Nigeria',
      'NU'=>'Niue',
      'NF'=>'Norfolk Island',
      'KP'=>'North Korea',
      'NO'=>'Norway',
      'OM'=>'Oman',
      'PW'=>'Palau',
      'PS'=>'Palestinian Territories',
      'PK'=>'Pakistan',
      'PA'=>'Panama',
      'PG'=>'Papua New Guinea',
      'PY'=>'Paraguay',
      'PE'=>'Peru',
      'PH'=>'Philippines',
      'PN'=>'Pitcairn',
      'PL'=>'Poland',
      'PT'=>'Portugal',
      'PR'=>'Puerto Rico',
      'QA'=>'Qatar',
      'RE'=>'Réunion',
      'RO'=>'Romania',
      'RU'=>'Russia',
      'RW'=>'Rwanda',
      'BL'=>'Saint Barthélemy',
      'SH'=>'Saint Helena, Ascension and Tristan da Cunha',
      'KN'=>'Saint Kitts and Nevis',
      'LC'=>'Saint Lucia',
      'MF'=>'Saint Martin',
      'PM'=>'Saint Pierre and Miquelon',
      'VC'=>'Saint Vincent and the Grenadines',
      'WS'=>'Samoa',
      'SM'=>'San Marino',
      'ST'=>'Sao Tome and Principe',
      'SA'=>'Saudi Arabia',
      'SN'=>'Senegal',
      'RS'=>'Serbia',
      'SC'=>'Seychelles',
      'SL'=>'Sierra Leone',
      'SG'=>'Singapore',
      'SX'=>'Sint Maarten',
      'SK'=>'Slovakia',
      'SI'=>'Slovenia',
      'LK'=>'Sri Lanka',
      'SB'=>'Solomon Islands',
      'SO'=>'Somalia',
      'ZA'=>'South Africa',
      'GS'=>'South Georgia and South Sandwich Islands',
      'KR'=>'South Korea',
      'SS'=>'South Sudan',
      'ES'=>'Spain',
      'VC'=>'St. Vincent',
      'SD'=>'Sudan',
      'SR'=>'Suriname',
      'SJ'=>'Svalbard and Jan Mayen',
      'SZ'=>'Swaziland',
      'SE'=>'Sweden',
      'CH'=>'Switzerland',
      'SY'=>'Syria',
      'TW'=>'Taiwan',
      'TJ'=>'Tajikistan',
      'TZ'=>'Tanzania',
      'TH'=>'Thailand',
      'TL'=>'Timor-Leste',
      'TG'=>'Togo',
      'TK'=>'Tokelau',
      'TO'=>'Tonga',
      'TT'=>'Trinidad and Tobago',
      'TN'=>'Tunisia',
      'TR'=>'Turkey',
      'TM'=>'Turkmenistan',
      'TC'=>'Turks and Caicos Islands',
      'TV'=>'Tuvalu',
      'UG'=>'Uganda',
      'UA'=>'Ukraine',
      'AE'=>'United Arab Emirates',
      'GB'=>'United Kingdom',
      'US'=>'United States',
      'UM'=>'United States Minor Outlying Islands',
      'UY'=>'Uruguay',
      'UZ'=>'Uzbekistan',
      'VU'=>'Vanuatu',
      'VN'=>'Vietnam',
      'VG'=>'British Virgin Islands',
      'VI'=>'US Virgin Islands',
      'VE'=>'Venezuela',
      'YE'=>'Yemen',
      'ZM'=>'Zambia',
      'ZW'=>'Zimbabwe'
    );
    
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
      'Malaysian Ringgit' => 'MYR',
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

  
  public static function getZones($code='all') {
    $setting = new Cart66Setting();
    $zones = array();
    
    $au = array();
    $au['0'] = '';
    $au['ACT'] = 'Australian Capital Territory';
    $au['NSW'] = 'New South Wales';
    $au['NT'] = 'Northern Territory';
    $au['QLD'] = 'Queensland';
    $au['SA'] = 'South Australia';
    $au['TAS'] = 'Tasmania';
    $au['VIC'] = 'Victoria';
    $au['WA'] = 'Western Australia';
    $zones['AU'] = $au;
    
    $br = array();
    $br['0'] = '';
    $br['Acre'] = 'Acre';
    $br['Alagoas'] = 'Alagoas';
    $br['Amapa'] = 'Amapa';
    $br['Amazonas'] = 'Amazonas';
    $br['Bahia'] = 'Bahia';
    $br['Ceara'] = 'Ceara';
    $br['Distrito Federal'] = 'Distrito Federal';
    $br['Espirito Santo'] = 'Espirito Santo';
    $br['Goias'] = 'Goias';
    $br['Maranhao'] = 'Maranhao';
    $br['Mato Grosso'] = 'Mato Grosso';
    $br['Mato Grosso do Sul'] = 'Mato Grosso do Sul';
    $br['Minas Gerais'] = 'Minas Gerais';
    $br['Para'] = 'Para';
    $br['Paraiba'] = 'Paraiba';
    $br['Parana'] = 'Parana';
    $br['Pernambuco'] = 'Pernambuco';
    $br['Piaui'] = 'Piaui';
    $br['Rio de Janeiro'] = 'Rio de Janeiro';
    $br['Rio Grande do Norte'] = 'Rio Grande do Norte';
    $br['Rio Grande do Sul'] = 'Rio Grande do Sul';
    $br['Rondonia'] = 'Rondonia';
    $br['Roraima'] = 'Roraima';
    $br['Santa Catarina'] = 'Santa Catarina';
    $br['Sao Paulo'] = 'Sao Paulo';
    $br['Sergipe'] = 'Sergipe';
    $br['Tocantins'] = 'Tocantins';
    $zones['BR'] = $br;
    
    $ca = array();
    $ca['0'] = '';
    $ca['AB'] = 'Alberta';
    $ca['BC'] = 'British Columbia';
    $ca['MB'] = 'Manitoba';
    $ca['NB'] = 'New Brunswick';
    $ca['NF'] = 'Newfoundland';
    $ca['NT'] = 'Northwest Territories';
    $ca['NS'] = 'Nova Scotia';
    $ca['NU'] = 'Nunavut';
    $ca['ON'] = 'Ontario';
    $ca['PE'] = 'Prince Edward Island';
    $ca['PQ'] = 'Quebec';
    $ca['SK'] = 'Saskatchewan';
    $ca['YT'] = 'Yukon Territory';
    $zones['CA'] = $ca;
    
    $my['0'] = '';
    $my['KUL'] = 'Kuala Lumpur (Federal Territory)';
    $my['LBN'] = 'Labuan (Federal Territory)';
    $my['PJY'] = 'Putrajaya (Federal Territory)';
    $my['JHR'] = 'Johor';
    $my['KDH'] = 'Kedah';
    $my['KTN'] = 'Kelantan';
    $my['MLK'] = 'Melaka';
    $my['NSN'] = 'Negeri Sembilan';
    $my['PHG'] = 'Pahang';
    $my['PRK'] = 'Perak';
    $my['PLS'] = 'Perlis';
    $my['PNG'] = 'Penang';
    $my['SBH'] = 'Sabah';
    $my['SWK'] = 'Sarawak';
    $my['SGR'] = 'Selangor';
    $my['TRG'] = 'Terengganu';
    $zones['MY'] = $my;
    
    $us = array();
    $us['0'] = '';
    $us['AL'] = 'Alabama';
    $us['AK'] = 'Alaska ';
    $us['AZ'] = 'Arizona';
    $us['AR'] = 'Arkansas';
    $us['CA'] = 'California ';
    $us['CO'] = 'Colorado';
    $us['CT'] = 'Connecticut';
    $us['DE'] = 'Delaware';
    $us['DC'] = 'D. C.';
    $us['FL'] = 'Florida';
    $us['GA'] = 'Georgia ';
    $us['HI'] = 'Hawaii';
    $us['ID'] = 'Idaho';
    $us['IL'] = 'Illinois';
    $us['IN'] = 'Indiana';
    $us['IA'] = 'Iowa';
    $us['KS'] = 'Kansas';
    $us['KY'] = 'Kentucky';
    $us['LA'] = 'Louisiana';
    $us['ME'] = 'Maine';
    $us['MD'] = 'Maryland';
    $us['MA'] = 'Massachusetts';
    $us['MI'] = 'Michigan';
    $us['MN'] = 'Minnesota';
    $us['MS'] = 'Mississippi';
    $us['MO'] = 'Missouri';
    $us['MT'] = 'Montana';
    $us['NE'] = 'Nebraska';
    $us['NV'] = 'Nevada';
    $us['NH'] = 'New Hampshire';
    $us['NJ'] = 'New Jersey';
    $us['NM'] = 'New Mexico';
    $us['NY'] = 'New York';
    $us['NC'] = 'North Carolina';
    $us['ND'] = 'North Dakota';
    $us['OH'] = 'Ohio';
    $us['OK'] = 'Oklahoma';
    $us['OR'] = 'Oregon';
    $us['PA'] = 'Pennsylvania';
    $us['RI'] = 'Rhode Island';
    $us['SC'] = 'South Carolina';
    $us['SD'] = 'South Dakota';
    $us['TN'] = 'Tennessee';
    $us['TX'] = 'Texas';
    $us['UT'] = 'Utah';
    $us['VT'] = 'Vermont';
    $us['VA'] = 'Virginia';
    $us['WA'] = 'Washington';
    $us['WV'] = 'West Virginia';
    $us['WI'] = 'Wisconsin';
    $us['WY'] = 'Wyoming';
    $us['AA'] = 'Armed Forces (AA)';
    $us['AE'] = 'Armed Forces (AE)';
    $us['AP'] = 'Armed Forces (AP)';
    
    if($setting->getValue('include_us_territories') == 1){
      $us['AS'] = 'American Samoa';
      $us['GU'] = 'Guam';
      $us['MP'] = 'Northern Mariana Islands';
      $us['PR'] = 'Puerto Rico';
      $us['VI'] = 'Virgin Islands';
      $us['FM'] = 'Federated States of Micronesia';
      $us['MH'] = 'Marshall Islands';
      $us['PW'] = 'Palua';
    }
    
    $zones['US'] = $us;
    
    switch ($code) {
      case 'AU':
        $zones = $zones['AU'];
        break;
      case 'BR':
        $zones = $zones['BR'];
        break;
      case 'CA':
        $zones = $zones['CA'];
        break;
      case 'MY':
        $zones = $zones['MY'];
        break;
      case 'US':
        $zones = $zones['US'];
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
  public static function endSlashPath($path) {
    if(stripos(strrev($path), '/') !== 0) {
      $path .= '/';
    }
    return $path;
  }
  
  public static function localTs($timestamp=null) {
    $timestamp = isset($timestamp) ? $timestamp : time();
    if(date('T') == 'UTC') {
      $timestamp += (get_option( 'gmt_offset' ) * 3600 );
    }
    return $timestamp;
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
    $promo = Cart66Session::get('Cart66Promotion');
    $promoMsg = "none";
    if($promo) {
      $promoMsg = $promo->getCode() . ' (-' . CART66_CURRENCY_SYMBOL . number_format(Cart66Session::get('Cart66Promotion')->getDiscountAmount(Cart66Session::get('Cart66Cart')), 2) . ')';
    }
    return $promoMsg;
  }
  
  //increment the number of redemptions
  public function updatePromoRedemptions() {
    $promotion = Cart66Session::get('Cart66Promotion');
    $promotion->updateRedemptions();
  }
  
  public function showErrors($errors, $message=null) {
    $out = "<div id='cart66Errors' class='Cart66Error'>";
    if(empty($message)) {
      $out .= "<p><b>" . __("We're sorry.<br/>Your order could not be completed for the following reasons","cart66") . ":</b></p>";
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
  (function($){
    $(document).ready(function(){
      _script_here_
    })
  })(jQuery);
</script>';

    if(count($jqErrors)) {
      $lines = '';
      foreach($jqErrors as $val) {
        $lines .= "  \$('#$val').addClass('errorField');\n";
      }
    }
    $lines  = rtrim($lines, "\n");
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] jq error script lines: $lines");
    $script = str_replace('_script_here_', $lines, $script);
    return $script;
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
    if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || 
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) {
      $isHttps = true;
    }
    return $isHttps;
  }
  
  
  public static function getCurrentPageUrl() {
    $protocol = 'http://';
    if(self::isHttps()) {
      $protocol = 'https://';
    }
    $url = $protocol . $_SERVER['HTTP_HOST'] . htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8');
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
  
  public function appendWurlQueryString($nvPairs) {
    $url = home_url();
    $url .= strpos($url, '?') ? '&' : '/?';
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
          header('Content-Disposition: attachment; filename="' . $fileName . '"');
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
  
  /**
   * Convert an array into XML
   * 
   * Example use: echo arrayToXml($products,'products');
   * 
   * @param array $array       - The array you wish to convert into a XML structure.
   * @param string $name       - The name you wish to enclose the array in, the 'parent' tag for XML.
   * @param string $space      - The xml namespace
   * @param bool $standalone   - This will add a document header to identify this solely as a XML document.
   * @param bool $beginning    - INTERNAL USE... DO NOT USE!
   * @param int $nested        - INTERNAL USE... DO NOT USE! The nest level for pretty formatting
   * @return Gives a string output in a XML structure
  */
  public static function arrayToXml($array, $name, $space='', $standalone=false, $beginning=true, $nested=0) {
    $output = '';
    if ($beginning) {
      if($standalone) header("content-type:text/xml;charset=utf-8");
      if(!isset($output)) { $output = ''; }
      if($standalone) $output .= '<'.'?'.'xml version="1.0" encoding="UTF-8"'.'?'.'>' . "\n";
      if(!empty($space)) {
        $output .= '<' . $name . ' xmlns="' . $space . '">' . "\n";
      }
      else {
        $output .= '<' . $name . '>' . "\n";
      }
      $nested = 0;
    }

    // This is required because XML standards do not allow a tag to start with a number or symbol, you can change this value to whatever you like:
    $ArrayNumberPrefix = 'ARRAY_NUMBER_';

     foreach ($array as $root=>$child) {
      if (is_array($child)) {
        $output .= str_repeat(" ", (2 * $nested)) . '  <' . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . '>' . "\n";
        $nested++;
        $output .= self::arrayToXml($child,NULL,NULL,NULL,FALSE, $nested);
        $nested--;
        $tag = is_string($root) ? $root : $ArrayNumberPrefix . $root;
        $tag = array_shift(explode(' ', $tag));
        $output .= str_repeat(" ", (2 * $nested)) . '  </' . $tag . '>' . "\n";
      }
      else {
        if(!isset($output)) { $output = ''; }
        $tag = is_string($root) ? $root : $ArrayNumberPrefix . $root;
        $tag = array_shift(explode(' ', $tag));
        $output .= str_repeat(" ", (2 * $nested)) . '  <' . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . '>' . $child . '</' . $tag . '>' . "\n";
      }
    }
    
    $name = array_shift(explode(' ', $name));
    if ($beginning) $output .= '</' . $name . '>';

    return $output;
  }
  
  public static function testResult($passed, $msg='') {
    $trace = debug_backtrace();
    $func = $trace[1]['function'];
    $line = $trace[0]['line'];
    $file = $trace[1]['file'];
    $out = $passed ? "<font color=\"green\">SUCCESS: $func</font>\n" : "<font color=\"red\">FAILED: $func (Line: $line)\nFile: $file</font>\n";
    if(!empty($msg)) { $out .= $msg . "\n"; }
    echo $out . "\n";
  }
  
  public static function showReportData(){
    global $wpdb;
    $orders = Cart66Common::getTableName('orders');
    $reportData = array();
    
    $sql = "SELECT sum(`total`) from $orders";
    $lifetimeTotal = $wpdb->get_var($sql);
    $reportData[] = array("Total Sales","total_sales",$lifetimeTotal);
    
    $sql = "SELECT count('id') from $orders";
    $totalOrders = $wpdb->get_var($sql);
    $reportData[] = array("Total Orders","total_orders",$totalOrders);
    
    $sql = "SELECT ordered_on from $orders order by id asc LIMIT 1";
    $firstSaleDate = $wpdb->get_var($sql);
    $reportData[] = array("First Sale","first_sale",$firstSaleDate);
    
    $sql = "SELECT ordered_on from $orders order by id desc LIMIT 1";
    $lastSaleDate = $wpdb->get_var($sql);
    $reportData[] = array("Last Sale","last_sale",$lastSaleDate);
    
    $postTypes = get_post_types('','names');
    foreach($postTypes as $postType){
      if(!in_array($postType,array("post","page","attachment","nav_menu_item","revision"))){
        $customPostTypes[] = $postType;
      }
    }
    $customPostTypes = (empty($customPostTypes)) ? "none" : implode(',',$customPostTypes);
    $reportData[] = array("Custom Post Types","custom_post_types",$customPostTypes);
    
    $output = "First Sale: " . $firstSaleDate . "<br>";
    $output .= "Last Sale: " . $lastSaleDate . "<br>";
    $output .= "Total Orders: " . $totalOrders . "<br>";
    $output .= "Total Sales: " . $lifetimeTotal . "<br>";
    $output .= "Custom Post Types: " . $customPostTypes . "<br>";
    $output .= "WordPress Version: " . get_bloginfo("version") . "<br>";
    $output .= (CART66_PRO) ? "Cart66 Version: Pro " . Cart66Setting::getValue('version') . "<br>" : "Cart66 Version: " .Cart66Setting::getValue('version') . "<br>";
    $output .= "PHP Version: " . phpversion() . "<br>";
    
    
    //$output .= ": " . "" . "<br>";
    
    return $output;
  }
  public static function getElapsedTime($datestamp) {
    $output = false;
    if(!empty($datestamp) && $datestamp != '0000-00-00 00:00:00') {
      $totaldelay = Cart66Common::localTs() - strtotime($datestamp);
      if(Cart66Common::localTs() == strtotime($datestamp)) {
        $output = __('Now', 'cart66');
      }
      elseif($days=floor($totaldelay/86400)) {
        $totaldelay = $totaldelay % 86400;
        $output = date('m/d/Y', strtotime($datestamp));
      }
      elseif($hours=floor($totaldelay/3600)) {
        $totaldelay = $totaldelay % 3600;
        $output = $hours . ' ' . _n('hour', 'hours', $hours, 'cart66') . __(" ago","cart66");
      }
      elseif($minutes=floor($totaldelay/60)) {
        $totaldelay = $totaldelay % 60;
        $output = $minutes . ' ' . _n('minute', 'minutes', $minutes, 'cart66') . __(" ago","cart66");
      }
      elseif($seconds=floor($totaldelay/1)) {
        $totaldelay = $totaldelay % 1;
        $output = $seconds . ' ' . _n('second', 'seconds', $seconds, 'cart66') . __(" ago","cart66");
      }
    }
    return $output;
  }
  
  public static function getTimeLeft($datestamp) {
    $output = false;
    if(!empty($datestamp) && $datestamp != '0000-00-00 00:00:00') {
      $timeleft = strtotime($datestamp) - Cart66Common::localTs();
      if(Cart66Common::localTs() == strtotime($datestamp)) {
        $output = __('Now', 'cart66');
      }
      elseif($days=floor($timeleft/86400)) {
        $timeleft = $timeleft % 86400;
        $output = date('m/d/Y', strtotime($datestamp));
      }
      elseif($hours=floor($timeleft/3600)) {
        $timeleft = $timeleft % 3600;
        $output = $hours . ' ' . _n('hour', 'hours', $hours, 'cart66') . __(" to go","cart66");
      }
      elseif($minutes=floor($timeleft/60)) {
        $timeleft = $timeleft % 60;
        $output = $minutes . ' ' . _n('minute', 'minutes', $minutes, 'cart66') . __(" to go","cart66");
      }
      elseif($seconds=floor($timeleft/1)) {
        $timeleft = $timeleft % 1;
        $output = $seconds . ' ' . _n('second', 'seconds', $seconds, 'cart66') . __(" to go","cart66");
      }
    }
    return $output;
  }
  
  public static function cart66UserCan($role) {
    $access = false;
    $pageRoles = Cart66Setting::getValue('admin_page_roles');
    $pageRoles = unserialize($pageRoles);
    if(current_user_can($pageRoles[$role])) {
      $access = true;
    }
    return $access;
  }

  public static function getPageRoles($role) {
    $pageRoles = Cart66Setting::getValue('admin_page_roles');
    $pageRoles = unserialize($pageRoles);
    return $pageRoles[$role];
  }
  
  public static function urlIsLive($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_exec($ch);
    $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($response_code == '200') ? true : false;
  }
  
  public static function displayVersionInfo() {
    if(CART66_PRO) {
      echo '<meta name="generator" content="Cart66 Professional ' . Cart66Setting::getValue('version') . '" />' . "\n";
    }
    else {
      echo '<meta name="generator" content="Cart66 Lite ' . Cart66Setting::getValue('version') . '" />' . "\n";
    }
  }
  
  public static function removeCart66Meta() {
    remove_action('wp_head', array('Cart66Common','displayVersionInfo'));
  }
  
  /**
   * Return true if the current page is the mijireh checkout page, otherwise return false.
   * 
   * @return boolean
   */
  public static function isSlurpPage() {
    global $post;
    $isSlurp = false;
    if(isset($post) && is_object($post)) {
      $content = $post->post_content;
      if(strpos($content, '{{mj-checkout-form}}') !== false) {
        $isSlurp = true;
      }
    }
    else {
      // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Check Slurp Page Failed: " . print_r($post, 1));
    }
    return $isSlurp;
  }
  
  /**
   * Return an array just like explode, but trim the values of the array.
   * 
   * This allows for spaces in CSV strings. The following two strings would return the same array
   * option1, option2, option3
   * option1,option2,option3
   * 
   * @return array
   */
  public static function trimmedExplode($split_on, $string) {
    $values = array();
    $arr = explode($split_on, $string);
    foreach($arr as $val) {
      $values[] = trim($val);
    }
    return $values;
  }
  
  public function sessionType() {
    $type = Cart66Setting::getValue('session_type');
    if(!$type) {
      $type = 'database';
    }
    return $type;
  }
  
  // Remove all non-numeric characters except for the decimal
  public function cleanNumber($string) {
    $number = preg_replace('/[^\d\.]/', '', $string);
    return $number;
  }
  
  public function verifyCartPages($outputType = 'full'){
    $requiredPages = array(
      "Store" => "store",
      "Cart" => "store/cart",
      "Checkout" => "store/checkout",
      "Express" => "store/express",
      "IPN" => "store/ipn",
      "Receipt" => "store/receipt"
    );
    $output = '';
    $success = array();
    $error = array();
    foreach($requiredPages as $page => $slug){
       $wordpress_page = get_page_by_path($slug);
       if($wordpress_page && !in_array($wordpress_page->post_status, array('trash','draft','private'))){
         $success[] = "<li class='Cart66PageSuccess'><strong>$page page found</strong></li>";
       }
       else{
         $error[] = "<li class='Cart66PageError'><strong>$page page not found!</strong> The page should be located at " . get_bloginfo('url') . "/$slug</li>";
       }
    }
    
    switch($outputType){
      case "success":
        $output = $success;
      break;
      case "error":
        $output = $error;
      break;
      default:
        $output = array_merge($error, $success);
    }
    
    return implode(" ", $output);
    
  }
  
  
}
