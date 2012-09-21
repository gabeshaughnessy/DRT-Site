<?php
class Cart66ProCommon {
  
  public static function checkUpdate(){
    if(IS_ADMIN) {
      $pluginName = "cart66/cart66.php";
      $option = function_exists('get_transient') ? get_transient("update_plugins") : get_option("update_plugins");
      $option = self::getUpdatePluginsOption($option);
      
      if(function_exists('set_transient')) {
        Cart66Common::log('Setting Transient Value: ' . print_r($option->response[$pluginName], true));
        set_transient("update_plugins", $option);
      }
    }
  }
  
  public static function getUpdatePluginsOption($option) {
    $pluginName = "cart66/cart66.php";
    $versionInfo = Cart66ProCommon::getVersionInfo();
    if(is_array($versionInfo)) {

      $cart66Option = isset($option->response[$pluginName]) ? $option->response[$pluginName] : '';
      if(empty($cart66Option)) {
        $option->response[$pluginName] = new stdClass();
      }

      $setting = new Cart66Setting();
      $orderNumber = Cart66Setting::getValue('order_number');
      $currentVersion = Cart66Setting::getValue('version');
      if(version_compare($currentVersion, $versionInfo['version'], '<')) {
        $newVersion = $versionInfo['version'];
        Cart66Common::log("New Version Available: $currentVersion < $newVersion");
        $option->response[$pluginName]->url = "http://www.cart66.com";
        $option->response[$pluginName]->slug = "cart66";
        $option->response[$pluginName]->package = str_replace("{KEY}", $orderNumber, $versionInfo["url"]);
        $option->response[$pluginName]->new_version = $versionInfo["version"];
        $option->response[$pluginName]->id = "0";
      }
      else {
        unset($option->response[$pluginName]);
      }
    }
    return $option;
  }
  
  public static function getVersionInfo() {
    $callback = "http://cart66.com";
    $versionInfo = false;
    $setting = new Cart66Setting();
    $orderNumber = Cart66Setting::getValue('order_number');
    if($orderNumber) {
      $body = 'key=$orderNumber';
      $options = array('method' => 'POST', 'timeout' => 30, 'body' => $body);
      $options['headers'] = array(
          'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option('blog_charset'),
          'Content-Length' => strlen($body),
          'User-Agent' => 'WordPress/' . get_bloginfo("version"),
          'Referer' => get_bloginfo("url")
      );
      $callBackLink = $callback . "/cart66-version.php?" . self::getRemoteRequestParams();
      Cart66Common::log("Callback link: $callBackLink");
      $raw = wp_remote_request($callBackLink, $options);
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Version info from remote request: " . print_r($raw, 1));
      if (!is_wp_error($raw) && 200 == $raw['response']['code']) {
        $info = explode("~", $raw['body']);
        $versionInfo = array("isValidKey" => $info[0], "version" => $info[1], "url" => $info[2]);
      }
    }
    return $versionInfo;      
  }
  
  public static function getRemoteRequestParams() {
    $params = false;
    $setting = new Cart66Setting();
    $orderNumber = Cart66Setting::getValue('order_number');
    if(!$orderNumber) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Order number not available");
    }
    $version = Cart66Setting::getValue('version');
    if(!$version) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Version number not available");
    }
    if($orderNumber && $version) {
      global $wpdb;
      $versionName = 'pro';
      $params = sprintf("task=getLatestVersion&pn=Cart66&key=%s&v=%s&vnm=%s&wp=%s&php=%s&mysql=%s&ws=%s", 
        urlencode($orderNumber), 
        urlencode($version), 
        urlencode($versionName),
        urlencode(get_bloginfo("version")), 
        urlencode(phpversion()), 
        urlencode($wpdb->db_version()),
        urlencode(get_bloginfo("url"))
      );
    }
    return $params;
  }
  
  public static function showChangelog() {
    if($_REQUEST["plugin"] == "cart66") {
      $setting = new Cart66Setting();
      $orderNumber = Cart66Setting::getValue('order_number');

      if($orderNumber) {
        $url = 'http://cart66.com/latest-cart66/';
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);

        $data = curl_exec($ch);

        $raw = "<style type='text/css'>";
        $raw .= "h1,h2,h3,li,p {font-size:20px; font-family:'Lucida Grande',Lucida,'Lucida Sans',Arial,sans-serif;}";
        $raw .= "h2 {font-size:16px;}";
        $raw .= "h3 {font-size:16px;}";
        $raw .= "li,p {font-size:13px;line-height:150%; margin-bottom:6px;}";
        $raw .= "#update {margin:10px; padding: 20px; border:1px solid #dedede;}";
        $raw .= "</style>";
        $raw .= "<div id='update'>";
        if(!curl_errno($ch)){ 
          $data = str_replace("\n", '', $data);
          $matches = array();
          preg_match('/<div class="entry">(.+?)<\/div>/m', $data, $matches);
          $raw .= "<h1>" . __("What's new in Cart66", "cart66") . "</h1>$matches[0]";
          $raw .= "</div>";
        } else { 
          $raw .= "<h1>" . __("Error", "cart66") . "</h1>";
          $raw .= "<div class='entry'><p>";
          $raw .= __("There was an error while trying to display the changelog.", "cart66") . "</p><p>" . __("Please visit", "cart66") . " <a href='$url'>$url</a> " . __("to see the latest changes.", "cart66");
          $raw .= "</p></div></div>";
        } 
        curl_close($ch);

        echo $raw;
      }

      exit;
    }
  }

  public static function getUpsServices() {
    
    $usaServices = array(
      'UPS Next Day Air' => '01',
      'UPS Second Day Air' => '02',
      'UPS Ground' => '03',
      'UPS Worldwide Express' => '07',
      'UPS Worldwide Expedited' => '08',
      'UPS Standard' => '11',
      'UPS Three-Day Select' => '12',
      'UPS Next Day Air Early A.M.' => '14',
      'UPS Worldwide Express Plus' => '54',
      'UPS Second Day Air A.M.' => '59',
      'UPS Saver' => '65'
    );
    
    $internationalServices = array(
      'UPS Express' =>	'01',
      'UPS Expedited' =>	'02',
      'UPS Worldwide Express' =>	'07',
      'UPS Worldwide Expedited' =>	'08',
      'UPS Standard' =>	'11',
      'UPS Three-Day Select' =>	'12',
      'UPS Saver' =>	'13',
      'UPS Express Early A.M.' =>	'14',
      'UPS Worldwide Express Plus' =>	'54',
      'UPS Saver' =>	'65'
    );
    
    $homeCountryCode = 'US';
    $setting = new Cart66Setting();
    $home = Cart66Setting::getValue('home_country');
    if($home) {
      list($homeCountryCode, $name) = explode('~', $home);
    }
    
    $services = $homeCountryCode == 'US' ? $usaServices : $internationalServices;
    
    return $services;
  }
  
  public static function getUspsServices() {
    $usaServices = array(
      'USPS First-Class Mail' => 'First-Class Mail Parcel',
      'USPS Express Mail' => 'Express Mail',
      'USPS Priority Mail' => 'Priority Mail',
      'USPS Parcel Post' => 'Parcel Post',
      'USPS Media Mail' => 'Media Mail',
      'USPS Express Mail International' => 'Express Mail International',
      'USPS Priority Mail International' => 'Priority Mail International',
      'USPS First-Class Mail International' => 'First-Class Mail International Parcel'
    );
    
    return $usaServices;
  }
  
  public static function getFedexServices() {
    $usaServices = array(
      'FedEx First Overnight' => 'FIRST_OVERNIGHT',
      'FedEx Priority Overnight' => 'PRIORITY_OVERNIGHT',
      'FedEx Standard Overnight' => 'STANDARD_OVERNIGHT',
      'FedEx 2Day A.M.' => 'FEDEX_2_DAY_AM',
      'FedEx 2Day' => 'FEDEX_2_DAY',
      'FedEx Express Saver' => 'FEDEX_EXPRESS_SAVER',
      'FedEx Ground' => 'FEDEX_GROUND',
      'FedEx Home Delivery' => 'GROUND_HOME_DELIVERY'
    );
        
    return $usaServices;
  }
  
  public static function getFedexIntlServices() {
        
    $internationalServices = array(
      //'FedEx International Next Flight' => 'INTERNATIONAL_NEXT_FLIGHT',
      'FedEx International First' => 'INTERNATIONAL_FIRST',
      'FedEx International Priority' => 'INTERNATIONAL_PRIORITY',
      'FedEx International Economy' => 'INTERNATIONAL_ECONOMY'
      //'FedEx International Ground' => 'INTERNATIONAL_GROUND'
    );
    
    return $internationalServices;
  }
  
  public static function getAuPostServices() {
    $auServices = array(
      'Australia Post Regular Parcel' => 'AUS_PARCEL_REGULAR',
      'Australia Post Express Post Parcel' => 'AUS_PARCEL_EXPRESS',
      'Australia Post Express Post Platinum Parcel' => 'AUS_PARCEL_PLATINUM'
    );
        
    return $auServices;
  }
  
  public static function getAuPostIntlServices() {
        
    $internationalServices = array(
      'Australia Post Express Courier International Merchandise' => 'INTL_SERVICE_ECI_M',
      'Australia Post Express Courier International Documents' => 'INTL_SERVICE_ECI_D',
      'Australia Post Express Post International' => 'INTL_SERVICE_EPI',
      'Australia Post Pack and Track International' => 'INTL_SERVICE_PTI',
      'Australia Post Registered Post International' => 'INTL_SERVICE_RPI',
      'Australia Post Air Mail' => 'INTL_SERVICE_AIR_MAIL',
      'Australia Post Sea Mail' => 'INTL_SERVICE_SEA_MAIL'
    );
    
    return $internationalServices;
  }
  
  public static function getCaPostServices() {
    $caServices = array(
      'Canada Post Priority' => 'Priority',
      'Canada Post XpressPost' => 'Xpresspost',
      'Canada Post Expedited Parcel' => 'Expedited Parcel',
      'Canada Post Regular Parcel' => 'Regular Parcel'
    );
        
    return $caServices;
  }
  
  public static function getCaPostIntlServices() {
    $internationalServices = array(
      'Canada Post Expedited Parcel USA' => 'Expedited Parcel USA',
      'Canada Post Priority Worldwide Envelope USA' => 'Priority Worldwide envelope USA',
      'Canada Post Priority Worldwide Packet USA' => 'Priority Worldwide pak USA',
      'Canada Post Small Packet USA Air' => 'Small Packet USA Air',
      'Canada Post Small Packet USA Surface' => 'Small Packet USA Surface',
      'Canada Post XpressPost USA' => 'Xpresspost USA',
      'Canada Post Expedited Parcel International' => 'Expedited Parcel INTL',
      'Canada Post Priority Worldwide Envelope International' => 'Priority Worldwide envelope INTL',
      'Canada Post Priority Worldwide Packet International' => 'Priority Worldwide pak INTL',
      'Canada Post Small Packet International Air' => 'Small Packet International Air',
      'Canada Post Small Packet International Surface' => 'Small Packet International Surface',
      'Canada Post XpressPost International' => 'Xpresspost International'
    );
        
    return $internationalServices;
  }
  
  public function getLogoutUrl() {
    $url = Cart66Common::getCurrentPageUrl();
    $pgs = get_posts('numberposts=1&post_type=any&meta_key=cart66_member&meta_value=logout');
    if(count($pgs)) {
      $url = get_permalink($pgs[0]->ID);
    }
    return $url;
  }
  
  public function generateUnsubscribeLink($accountId) {
    $url = false;
    if($unsubscribeLink = get_page_by_path('store/unsubscribe')) {
      $account = new Cart66Account();
      $account->load($accountId);
      $url = get_permalink($unsubscribeLink->ID) . '?cart66-task=opt_out&e=' . urlencode(base64_encode($account->email)) . '&t=' . Cart66ProCommon::generateEmailToken($account->id);
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] $url");
    }
    return $url;
  }
  
  public function generateEmailToken($accountId) {
    $account = new Cart66Account();
    $account->load($accountId);
    $token = md5($account->id . $account->email . $account->created_at . '*c66*(#)');
    return $token;
  }
  
  public function verifyEmailToken($token, $email) {
    $verify = 0;
    $account = new Cart66Account();
    $account->loadByEmail($email);
    if($account->opt_out == 1) {
      $verify = -1;
    }
    else {
      $md5 = md5($account->id . $account->email . $account->created_at . '*c66*(#)');
      if($token == $md5) {
        $verify = 1;
      }
      else {
        $verify = -2;
      }
    }
    return $verify;
  }
  
  public function unsubscribeEmailToken($token, $email) {
    $unsubscribe = false;
    $account = new Cart66Account();
    $accounts = $account->getModels();
    foreach($accounts as $a) {
      $md5 = md5($a->id . $a->email . $a->created_at . '*c66*(#)');
      if($token == $md5) {
        $a->setData(array('opt_out' => 1));
        $a->save();
        $a->clear();
        $unsubscribe = true;
      }
    }
    return $unsubscribe;
  }
  
}