<?php
class Cart66Setting {
  
  private $_settings_tabs = array();
  
  public function __construct($tabs=null) {
    if($tabs) {
      return $this->registerSettings($tabs);
    }
  }
  
  /*
   * Registers the general settings via the Settings API,
   * appends the setting to the tabs array of the object.
  */
  public function registerSettings($tabs) {
    foreach($tabs as $tab_key => $tab_caption) {
      $this->_settings_tabs[$tab_key . '_settings'] = $tab_caption;
    }
    
    foreach($this->_settings_tabs as $key => $value) {
      register_setting($key, $key);
      add_settings_section('section_' . $key, $value['title'], array($this, 'section_' . $key), $key);
    }
  }
  
  /*
   * The following methods provide content
   * for the respective sections, used as callbacks
   * with add_settings_section
  */
  public function section_main_settings() {
    $successMessage = '';
    $versionInfo = false;
    $orderNumberFailed = '';
    if($_SERVER['REQUEST_METHOD'] == "POST") {
      if($_POST['cart66-action'] == 'saveOrderNumber' && CART66_PRO) {
        $orderNumber = trim(Cart66Common::postVal('order_number'));
        Cart66Setting::setValue('order_number', $orderNumber);
        $versionInfo = Cart66ProCommon::getVersionInfo();
        if($versionInfo) {
          $successMessage = __("Thank you! Cart66 has been activated","cart66");
        }
        else {
          Cart66Setting::setValue('order_number', '');
          $orderNumberFailed = true;
        }
      }
    }
    $data = array(
      'success_message' => $successMessage,
      'version_info' => $versionInfo,
      'order_number_failed' => $orderNumberFailed
    );
    echo Cart66Common::getView('admin/settings/main.php', $data, false);
  }
  
  public function section_tax_settings() {
    $rate = new Cart66TaxRate();
    $successMessage = '';
    $errorMessage = '';
    if($_SERVER['REQUEST_METHOD'] == "POST") {
      if($_POST['cart66-action'] == 'save rate') {
        $data = $_POST['tax'];
        if((isset($data['state']) && empty($data['state'])) && (isset($data['zip']) && empty($data['zip']))) {
          $errorMessage = __('You must choose a state or enter a Zipcode', 'cart66');
        }
        elseif(isset($data['rate']) && empty($data['rate'])) {
          $errorMessage = __('Please provide a tax rate', 'cart66');
        }
        else {
          if(isset($data['zip']) && !empty($data['zip'])) {
            $zipCodes = explode('-', $data['zip']);
            if(count($zipCodes) > 1) {
              list($low, $high) = $zipCodes;
            }
            if(isset($low)) {
              $low = trim($low);
            }
            else {
              $low = $data['zip'];
            }

            if(isset($high)) {
              $high = trim($high);
            }
            else {
              $high = $low; 
            }

            if(is_numeric($low) && is_numeric($high)) {
              if($low > $high) {
                $x = $high;
                $high = $low;
                $low = $x;
              }
              $data['zip_low'] = $low;
              $data['zip_high'] = $high;
            }

          }
          $rate->setData($data);
          $rate->save();
          $rate->clear();
          $successMessage = __("Tax rate saved","cart66");
        }
        
      }
    }
    elseif(isset($_GET['task']) && $_GET['task'] == 'deleteTax' && isset($_GET['id']) && $_GET['id'] > 0) {
      $id = Cart66Common::getVal('id');
      $rate->load($id);
      $rate->deleteMe();
      $rate->clear();
    }
    $data = array(
      'rate' => $rate,
      'success_message' => $successMessage,
      'error_message' => $errorMessage
    );
    echo Cart66Common::getView('admin/settings/tax.php', $data, false);
  }
  
  public function section_cart_checkout_settings() {
    echo Cart66Common::getView('admin/settings/cart-checkout.php', null, false);
  }
  
  public function section_gateways_settings() {
    echo Cart66Common::getView('admin/settings/gateways.php', null, false);
  }
  
  public function section_notifications_settings() {
    $tab = 'notifications-email_receipt_settings';
    $data = array('tab' => $tab);
    if(CART66_PRO) {
      $reminder = new Cart66MembershipReminders();
      $orderFulfillment = new Cart66OrderFulfillment();
      $errorMessage = '';
      $successMessage = '';
      if($_SERVER['REQUEST_METHOD'] == "POST") {
        if($_POST['cart66-action'] == 'email log settings') {
          foreach($_POST['emailLog'] as $key => $value) {
            Cart66Setting::setValue($key, $value);
          }
          $tab = 'notifications-email_log_settings';
        }
        if($_POST['cart66-action'] == 'save reminder') {
          try {
            $reminder->load($_POST['reminder']['id']);
            $reminder->setData($_POST['reminder']);
            $reminder->save();
            $reminder->clear();
          }
          catch(Cart66Exception $e) {
            $errorCode = $e->getCode();
            // Reminder save failed
            if($errorCode == 66302) {
              $errors = $reminder->getErrors();
              $exception = Cart66Exception::exceptionMessages($e->getCode(), __("The reminder could not be saved for the following reasons","cart66"), $errors);
              $errorMessage = Cart66Common::getView('views/error-messages.php', $exception);
            }
          }
          $tab = 'notifications-reminder_settings';
        }
        if($_POST['cart66-action'] == 'save order fulfillment') {
          try {
            $orderFulfillment->load($_POST['fulfillment']['id']);
            $orderFulfillment->setData($_POST['fulfillment']);
            $orderFulfillment->save();
            $orderFulfillment->clear();
          }
          catch(Cart66Exception $e) {
            $errorCode = $e->getCode();
            if($errorCode == 66303) {
              $errors = $orderFulfillment->getErrors();
              $exception = Cart66Exception::exceptionMessages($e->getCode(), __("The order fulfillment could not be saved for the following reasons","cart66"), $errors);
              $errorMessage = Cart66Common::getView('views/error-messages.php', $exception);
            }
          }
          $tab = 'notifications-fulfillment_settings';
        }
        if($_POST['cart66-action'] == 'advanced notifications') {
          Cart66Setting::setValue('enable_advanced_notifications', $_POST['enable_advanced_notifications']);
          $successMessage = __('Your notification settings have been saved.', 'cart66');
          $tab = 'notifications-advanced_notifications';
        }
      }
      elseif($_SERVER['REQUEST_METHOD'] == "GET") {
        if(isset($_GET['task']) && $_GET['task'] == 'edit_reminder' && isset($_GET['id']) && $_GET['id'] > 0) {
          $id = Cart66Common::getVal('id');
          $reminder->load($id);
          $tab = 'notifications-reminder_settings';
        }
        elseif(isset($_GET['task']) && $_GET['task'] == 'delete_reminder' && isset($_GET['id']) && $_GET['id'] > 0) {
          $id = Cart66Common::getVal('id');
          $reminder->load($id);
          $reminder->deleteMe();
          $reminder->clear();
          $tab = 'notifications-reminder_settings';
        }
        elseif(isset($_GET['task']) && $_GET['task'] == 'cancel_reminder') {
          $tab = 'notifications-reminder_settings';
        }
        elseif(isset($_GET['task']) && $_GET['task'] == 'edit_fulfillment' && isset($_GET['id']) && $_GET['id'] > 0) {
          $id = Cart66Common::getVal('id');
          $orderFulfillment->load($id);
          $tab = 'notifications-fulfillment_settings';
        }
        elseif(isset($_GET['task']) && $_GET['task'] == 'delete_fulfillment' && isset($_GET['id']) && $_GET['id'] > 0) {
          $id = Cart66Common::getVal('id');
          $orderFulfillment->load($id);
          $orderFulfillment->deleteMe();
          $orderFulfillment->clear();
          $tab = 'notifications-fulfillment_settings';
        }
        elseif(isset($_GET['task']) && $_GET['task'] == 'cancel_fulfillment') {
          $tab = 'notifications-fulfillment_settings';
        }
      }

      $data = array(
        'reminder' => $reminder,
        'order_fulfillment' => $orderFulfillment,
        'tab' => $tab,
        'error_message' => $errorMessage,
        'success_message' => $successMessage
      );
    }
    echo Cart66Common::getView('admin/settings/notifications.php', $data, false);
  }
  
  public function section_integrations_settings() {
    echo Cart66Common::getView('admin/settings/integrations.php', null, false);
  }
  
  public function section_debug_settings() {
    $tab = 'debug-error_logging';
    if((isset($_GET['cart66_curl_test']) && $_GET['cart66_curl_test'] == 'run') || (isset($_POST['cart66-action']) && $_POST['cart66-action'] == 'clear log file')) {
      $tab = 'debug-debug_data';
    }
    elseif(isset($_POST['cart66-action']) && $_POST['cart66-action'] == 'check subscription reminders') {
      Cart66MembershipReminders::dailySubscriptionEmailReminderCheck();
      $tab = 'debug-debug_data';
    }
    elseif(isset($_POST['cart66-action']) && $_POST['cart66-action'] == 'check followup emails') {
      Cart66AdvancedNotifications::dailyFollowupEmailCheck();
      $tab = 'debug-debug_data';
    }
    elseif(isset($_GET['sessions']) && $_GET['sessions'] == 'repair') {
      $tab = 'debug-session_settings';
    }
    $data = array(
      'tab' => $tab
    );
    echo Cart66Common::getView('admin/settings/debug.php', $data, false);
  }
  
  public function getSettingsTabs() {
    return $this->_settings_tabs;
  }
  
  public static function setValue($key, $value) {
    global $wpdb;
    $settingsTable = Cart66Common::getTableName('cart_settings');
    
    if(!empty($key)) {
      $dbKey = $wpdb->get_var("SELECT `key` from $settingsTable where `key`='$key'");
      if($dbKey) {
        if(!empty($value)) {
          $wpdb->update($settingsTable, 
            array('key'=>$key, 'value'=>$value),
            array('key'=>$key),
            array('%s', '%s'),
            array('%s')
          );
        }
        else {
          $wpdb->query("DELETE from $settingsTable where `key`='$key'");
        }
      }
      else {
        if(!empty($value)) {
          $wpdb->insert($settingsTable, 
            array('key'=>$key, 'value'=>$value),
            array('%s', '%s')
          );
        }
      }
    }
    
  }
  
  public static function getValue($key, $entities=false) {
    global $wpdb;
    $settingsTable = Cart66Common::getTableName('cart_settings');
    $value = $wpdb->get_var("SELECT `value` from $settingsTable where `key`='$key'");
    
    if(!empty($value) && $entities) {
      $value = htmlentities($value);
    }
    
    return empty($value) ? false : $value;
  }
  
  public static function validateDebugValue($value, $expected){
    if($value != $expected){
      // test failed
      $output = "<span class='failedDebug'>" . $value . "</span>";
    }
    else{
      $output = $value;
    }
    return $output;
  }
  
}