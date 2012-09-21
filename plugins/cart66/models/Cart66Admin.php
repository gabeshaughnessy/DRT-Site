<?php

class Cart66Admin {
  
  public function productsPage() {
    $data = array();
    $subscriptions = array('0' => 'None');
    
    if(class_exists('SpreedlySubscription')) {
      $spreedlySubscriptions = SpreedlySubscription::getSubscriptions();
      foreach($spreedlySubscriptions as $s) {
        $subs[(int)$s->id] = (string)$s->name;
      }
      if(count($subs)) {
        asort($subs);
        foreach($subs as $id => $name) {
          $subscriptions[$id] = $name;
        }
      }
    }
    else {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not loading Spreedly data because Spreedly class has not been loaded");
    }
    
    $data['subscriptions'] = $subscriptions;
    $view = Cart66Common::getView('admin/products.php', $data);
    echo $view;
  }
  
  public function settingsPage() {
    $tabs = array(
      'main' => array('tab' => 'Main', 'title' => ''),
      'tax' => array('tab' => 'Tax', 'title' => ''),
      'cart_checkout' => array('tab' => 'Cart & Checkout', 'title' => ''),
      'gateways' => array('tab' => 'Gateways', 'title' => ''),
      'notifications' => array('tab' => 'Notifications', 'title' => ''),
      'integrations' => array('tab' => 'Integrations', 'title' => ''),
      'debug' => array('tab' => 'Debug', 'title' => '')
    );
    $setting = new Cart66Setting($tabs);
    $data = array(
      'setting' => $setting
    );
    $view = Cart66Common::getView('admin/settings.php', $data);
    echo $view;
  }
  
  public function notificationsPage() {
    $view = Cart66Common::getView('admin/notifications.php');
    echo $view;
  }
  
  public function ordersPage() {
    if($_SERVER['REQUEST_METHOD'] == 'GET' && Cart66Common::getVal('task') == 'view') {
      $order = new Cart66Order($_GET['id']);
      $view = Cart66Common::getView('admin/order-view.php', array('order'=>$order)); 
    }
    elseif($_SERVER['REQUEST_METHOD'] == 'POST' && Cart66Common::postVal('task') == 'resend email receipt') {
      if(CART66_PRO && Cart66Setting::getValue('enable_advanced_notifications') == 1) {
        $notify = new Cart66AdvancedNotifications($_POST['order_id']);
        $notify->sendAdvancedEmailReceipts(false);
      }
      else {
        $notify = new Cart66Notifications($_POST['order_id']);
        $notify->sendEmailReceipts();
      }
      $order = new Cart66Order($_POST['order_id']);
      $view = Cart66Common::getView('admin/order-view.php', array('order'=>$order, 'resend'=>true));
    }
    elseif($_SERVER['REQUEST_METHOD'] == 'POST' && Cart66Common::postVal('task') == 'reset download amount') {
      $product = new Cart66Product();
      $product->resetDownloadsForDuid($_POST['duid'], $_POST['order_item_id']);
      $order = new Cart66Order($_POST['order_id']);
      $view = Cart66Common::getView('admin/order-view.php', array('order'=>$order));
    }
    elseif($_SERVER['REQUEST_METHOD'] == 'GET' && Cart66Common::getVal('task') == 'delete') {
      $order = new Cart66Order($_GET['id']);
      $order->deleteMe();
      $view = Cart66Common::getView('admin/orders.php'); 
    }
    elseif($_SERVER['REQUEST_METHOD'] == 'POST' && Cart66Common::postVal('remove') && Cart66Common::postVal('remove') != 'all') {
      $order = new Cart66Order($_GET['id']);
      Cart66AdvancedNotifications::removeTrackingNumber($order);
      $order = new Cart66Order($_GET['id']);
      $view = Cart66Common::getView('admin/order-view.php', array('order'=>$order));
    }
    elseif($_SERVER['REQUEST_METHOD'] == 'POST' && Cart66Common::postVal('remove') == 'all') {
      $order = new Cart66Order($_GET['id']);
      $order->updateTracking(null);
      $order = new Cart66Order($_GET['id']);
      $view = Cart66Common::getView('admin/order-view.php', array('order'=>$order));
    }
    elseif($_SERVER['REQUEST_METHOD'] == 'POST' && Cart66Common::postVal('task') == 'update order status') {
      $order = new Cart66Order($_POST['order_id']);
      //$order->updateStatus(Cart66Common::postVal('status'));
      //$order->updateNotes($_POST['notes']);
      $data = array(
        'status' => Cart66Common::postVal('status'),
        'notes' => Cart66Common::postVal('notes')
      );
      $order->setData($data);
      $order->save();
      if(Cart66Common::postVal('send_email_status_update') && CART66_PRO) {
        Cart66AdvancedNotifications::addTrackingNumbers($order);
        $status = Cart66Common::postVal('status');
        if(Cart66Setting::getValue('status_options') != null) {
          $notify = new Cart66AdvancedNotifications($_POST['order_id']);
          $notify->sendStatusUpdateEmail($status);
        }
      }
      elseif(CART66_PRO) {
        Cart66AdvancedNotifications::addTrackingNumbers($order);
      }
      $view = Cart66Common::getView('admin/orders.php');
      //$order = new Cart66Order($_POST['order_id']);
      //$view = Cart66Common::getView('admin/order-view.php', array('order'=>$order));
    }
    else {
      $view = Cart66Common::getView('admin/orders.php'); 
    }

    echo $view;
  }

  public function inventoryPage() {
    $view = Cart66Common::getView('admin/inventory.php');
    echo $view; 
  }

  public function promotionsPage() {
    $view = Cart66Common::getView('admin/promotions.php');
    echo $view;
  }

  public function shippingPage() {
    $view = Cart66Common::getView('admin/shipping.php');
    echo $view;
  }

  public function reportsPage() {
    $view = Cart66Common::getView('admin/reports.php');
    echo $view;
  }
  
  public function Cart66Help() {
    $setting = new Cart66Setting();
    define('HELP_URL', "http://www.cart66.com/cart66-help/?order_number=".Cart66Setting::getValue('order_number'));
    $view = Cart66Common::getView('admin/help.php');
    echo $view;
  }
  
  public function paypalSubscriptions() {
    $data = array();
    if(CART66_PRO) {
      $sub = new Cart66PayPalSubscription();
      $data['subscription'] = $sub;

      if($_SERVER['REQUEST_METHOD'] == 'POST' && Cart66Common::postVal('cart66-action') == 'save paypal subscription') {
        $subData = Cart66Common::postVal('subscription');
        $sub->setData($subData);
        $errors = $sub->validate();
        if(count($errors) == 0) {
          $sub->save();
          $sub->clear();
          $data['subscription'] = $sub;
        }
        else {
          $data['errors'] = $sub->getErrors();
          $data['jqErrors'] = $sub->getJqErrors();
        }
      }
      else {
        if(Cart66Common::getVal('task') == 'edit' && isset($_GET['id'])) {
          $sub->load(Cart66Common::getVal('id'));
          $data['subscription'] = $sub;
        }
        elseif(Cart66Common::getVal('task') == 'delete' && isset($_GET['id'])) {
          $sub->load(Cart66Common::getVal('id'));
          $sub->deleteMe();
          $sub->clear();
          $data['subscription'] = $sub;
        }
      }

      $data['plans'] = $sub->getModels('where is_paypal_subscription>0', 'order by name', '1');
      $view = Cart66Common::getView('pro/admin/paypal-subscriptions.php', $data);
      echo $view;
    }
    else {
      echo '<h2>PayPal Subscriptions</h2><p class="description">This feature is only available in <a href="http://cart66.com">Cart66 Professional</a>.</p>';
    }
    
  }
  
  public function accountsPage() {
    $data = array();
    if(CART66_PRO) {
      $data['plan'] = new Cart66AccountSubscription();
      $data['activeUntil'] = '';
      $account = new Cart66Account();

      if(isset($_REQUEST['cart66-action']) && $_REQUEST['cart66-action'] == 'delete_account') {
        // Look for delete request
        if(isset($_REQUEST['accountId']) && is_numeric($_REQUEST['accountId'])) {
          $account = new Cart66Account($_REQUEST['accountId']);
          $account->deleteMe();
          $account->clear();
        }
      }
      elseif(isset($_REQUEST['accountId']) && is_numeric($_REQUEST['accountId'])) {
        if(isset($_REQUEST['opt_out'])) {
          $account = new Cart66Account();
          $account->load($_REQUEST['accountId']);
          $data = array(
            'opt_out' => $_REQUEST['opt_out']
          );
          $account->setData($data);
          $account->save();
          $account->clear();
        }
        // Look in query string for account id
        $account = new Cart66Account();
        $account->load($_REQUEST['accountId']);
        $id = $account->getCurrentAccountSubscriptionId(true);
        $data['plan'] = new Cart66AccountSubscription($id); // Return even if plan is expired
        if(date('Y', strtotime($data['plan']->activeUntil)) <= 1970) {
          $data['activeUntil'] = '';
        }
        else {
          $data['activeUntil'] = date('m/d/Y', strtotime($data['plan']->activeUntil));
        }
      }

      if($_SERVER['REQUEST_METHOD'] == 'POST' && Cart66Common::postVal('cart66-action') == 'save account') {
        $acctData = $_POST['account'];

        // Format or unset password
        if(empty($acctData['password'])) {
          unset($acctData['password']);
        }
        else {
          $acctData['password'] = md5($acctData['password']);
        }

        // Strip HTML tags on notes field
        $acctData['notes'] = strip_tags($acctData['notes'], '<a><strong><em>');

        $planData = $_POST['plan'];
        $planData['active_until'] = date('Y-m-d 00:00:00', strtotime($planData['active_until']));

        // Updating an existing account
        if($acctData['id'] > 0) {
          $account = new Cart66Account($acctData['id']);
          $account->setData($acctData);
          $account_errors = $account->validate();
          
          $sub = new Cart66AccountSubscription($planData['id']);
          if($planData['product_id'] != 'spreedly_subscription') {
            $sub->setData($planData);
            $subscription_product = new Cart66Product($sub->product_id);
            $sub->subscription_plan_name = $subscription_product->name;
            $sub->feature_level = $subscription_product->feature_level;
            $sub->subscriber_token = '';
          }
          else {
            unset($planData['product_id']);
            $sub->setData($planData);
          }
          $subscription_errors = $sub->validate();
          $errors = array_merge($account_errors, $subscription_errors);

          if(count($errors) == 0) {
            $account->save();
            $sub->save();
            $account->clear();
            $sub->clear();
          }
          else {
            $data['errors'] = $errors;
            $data['plan'] = $sub;
            $data['activeUntil'] = date('m/d/Y', strtotime($sub->activeUntil));
          }
        }
        else {
          // Creating a new account
          $account = new Cart66Account();
          $account->setData($acctData);
          $account_errors = $account->validate();
          
          if(count($account_errors) == 0){
            $sub = new Cart66AccountSubscription();
            $sub->setData($planData); 
            $subscription_errors = $sub->validate();
            
            if(count($subscription_errors) == 0){
              $account->save();

              $sub->billingFirstName = $account->firstName;
              $sub->billingLastName = $account->lastName;
              $sub->billingInterval = 'Manual';
              $sub->account_id = $account->id;
              $subscription_product = new Cart66Product($sub->product_id);
              $sub->subscription_plan_name = $subscription_product->name;
              $sub->feature_level = $subscription_product->feature_level;
              $sub->save();
              $account->clear();
              $data['just_saved'] = true;
            }
            else{
              $data['errors'] = $subscription_errors;
            }
            
          }
          else{
            $data['errors'] = $account_errors;
          }
          
        }

      }

      $data['url'] = Cart66Common::replaceQueryString('page=cart66-accounts');
      $data['account'] = $account;
    }
    
    $view = Cart66Common::getView('admin/accounts.php', $data);
    echo $view;
  }
}