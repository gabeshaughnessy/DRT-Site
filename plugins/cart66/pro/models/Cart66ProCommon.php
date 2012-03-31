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
      $options = array('method' => 'POST', 'timeout' => 3, 'body' => $body);
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
        $raw = file_get_contents('http://cart66.com/latest-cart66');
        $raw = str_replace("\n", '', $raw);
        $matches = array();
        preg_match('/<div class="entry">(.+?)<\/div>/m', $raw, $matches);
        $raw = "<h1>Cart66</h1>$matches[1]";
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
  
  public function getLogoutUrl() {
    $url = Cart66Common::getCurrentPageUrl();
    $pgs = get_posts('numberposts=1&post_type=any&meta_key=cart66_member&meta_value=logout');
    if(count($pgs)) {
      $url = get_permalink($pgs[0]->ID);
    }
    return $url;
  }
  
}