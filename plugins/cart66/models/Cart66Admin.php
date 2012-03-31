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
    
    if(class_exists('Cart66PayPalSubscription')) {
      $ppsub = new Cart66PayPalSubscription();
      $data['ppsubs'] = $ppsub->getModels('where id>0', 'order by name');
    }
    
    $data['subscriptions'] = $subscriptions;
    $view = Cart66Common::getView('admin/products.php', $data);
    echo $view; 
  }
  
  public function settingsPage() {
    $view = Cart66Common::getView('admin/settings.php');
    echo $view;
  }
  
  public function ordersPage() {
    if($_SERVER['REQUEST_METHOD'] == 'GET' && Cart66Common::getVal('task') == 'view') {
      $order = new Cart66Order($_GET['id']);
      $view = Cart66Common::getView('admin/order-view.php', array('order'=>$order)); 
    }
    elseif($_SERVER['REQUEST_METHOD'] == 'GET' && Cart66Common::getVal('task') == 'delete') {
      $order = new Cart66Order($_GET['id']);
      $order->deleteMe();
      $view = Cart66Common::getView('admin/orders.php'); 
    }
    elseif($_SERVER['REQUEST_METHOD'] == 'POST' && Cart66Common::postVal('task') == 'update order status') {
      $order = new Cart66Order($_POST['order_id']);
      $order->updateStatus(Cart66Common::postVal('status'));
      $view = Cart66Common::getView('admin/orders.php');
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

      $data['plans'] = $sub->getModels('where is_paypal_subscription>0', 'order by name');
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
        // Look in query string for account id
        $account = new Cart66Account();
        $account->load($_REQUEST['accountId']);
        $data['plan'] = $account->getCurrentAccountSubscription(true); // Return even if plan is expired
        $data['activeUntil'] = date('m/d/Y', strtotime($data['plan']->activeUntil));
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
          $errors = $account->validate();

          $sub = new Cart66AccountSubscription($planData['id']);
          $sub->setData($planData);

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
          $errors = $account->validate();
          if(count($errors) == 0) {
            $account->save();
            $sub = new Cart66AccountSubscription();
            $planData['account_id'] = $account->id;
            $sub->setData($planData);
            $sub->billingFirstName = $account->firstName;
            $sub->billingLastName = $account->lastName;
            $sub->billingInterval = 'Manual';
            $sub->save();
            $account->clear();
          }
        }

      }

      $data['url'] = Cart66Common::replaceQueryString('page=cart66-accounts');
      $data['account'] = $account;
      $data['accounts'] = $account->getModels('where id>0', 'order by last_name');
    }
    
    
    $view = Cart66Common::getView('admin/accounts.php', $data);
    echo $view;
  }
}