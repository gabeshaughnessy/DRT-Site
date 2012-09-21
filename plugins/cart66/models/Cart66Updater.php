<?php
class Cart66Updater {
  
  protected $_version;
  protected $_orderNumber;
  protected $_motherShipUrl = 'http://www.cart66.com/cart66-version.php';
  
  public function __construct() {
    $setting = new Cart66Setting();
    $this->_version = Cart66Setting::getValue('version');
    $this->_orderNumber = Cart66Setting::getValue('order_number');
  }
  
  /**
   * Check the currently running version against the version of the latest release.
   * 
   * @return mixed The new version number if there is a new version, otherwise false.
   */
  public static function newVersion() {
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
  
  public function getCallHomeUrl() {
    return $this->_motherShipUrl;
  }
  
  public function getOrderNumber() {
    return $this->_orderNumber;
  }

}