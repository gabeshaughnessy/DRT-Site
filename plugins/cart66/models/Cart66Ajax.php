<?php
class Cart66Ajax {
  
  public static function resendEmailFromLog() {
    $log_id = $_POST['id'];
    $resendEmail = Cart66EmailLog::resendEmailFromLog($log_id);
    if($resendEmail) {
      $result[0] = 'Cart66Modal alert-message success';
      $result[1] = '<strong>Success</strong><br/>' . __('Email successfully resent', 'cart66') . ' <br />';
    }
    else {
      $result[0] = 'Cart66Modal alert-message alert-error';
      $result[1] = '<strong>Error</strong><br/>' . __('Email was not resent Successfully', 'cart66') . '<br>';
    }
    echo json_encode($result);
    die();
  }
  
  public function forcePluginUpdate(){
    $output = false;
    if(update_option('_site_transient_update_plugins', '') && update_option('_transient_update_plugins', '') ){
      $output = true;
    }
    echo $output;
    die();
  }
  
  public static function sendTestEmail() {
    $to = $_POST['email'];
    $status = $_POST['status'];
    if(!Cart66Common::isValidEmail($to)) {
      $result[0] = 'Cart66Modal alert-message alert-error';
      $result[1] = '<strong>Error</strong><br/>' . __('Please enter a valid email address', 'cart66') . '<br>';
    }
    else {
      if(isset($_GET['type']) && $_GET['type'] == 'reminder') {
        $sendEmail = Cart66MembershipReminders::sendTestReminderEmails($to, $_GET['id']);
      }
      else {
        $sendEmail = Cart66AdvancedNotifications::sendTestEmail($to, $status);
      }
      if($sendEmail) {
        $result[0] = 'Cart66Modal alert-message success';
        $result[1] = '<strong>Success</strong><br/>' . __('Email successfully sent to', 'cart66') . ' <br /><strong>' . $to . '</strong><br>';
      }
      else {
        $result[0] = 'Cart66Modal alert-message alert-error';
        $result[1] = '<strong>Error</strong><br/>' . __('Email not sent. There is an unknown error.', 'cart66') . '<br>';
      }
    }
    echo json_encode($result);
    die();
  }
  
  public static function ajaxReceipt() {
    if(isset($_GET['order_id'])) {
      $orderReceipt = new Cart66Order($_GET['order_id']);
      $printView = Cart66Common::getView('views/receipt_print_version.php', array('order' => $orderReceipt));
      $printView = str_replace("\n", '', $printView);
      $printView = str_replace("'", '"', $printView);
      echo $printView;
      die();
    }
  }
  
  public static function viewLoggedEmail() {
    if(isset($_POST['log_id'])) {
      $emailLog = new Cart66EmailLog($_POST['log_id']);
      echo nl2br(htmlentities($emailLog->headers . "\r\n" . $emailLog->body));
      die();
    }
  }
  
  public static function checkPages(){
    $Cart66 = new Cart66();
    echo $Cart66->cart66_page_check(true);
    die();
  }
  
  public static function shortcodeProductsTable() {
    global $wpdb;
    $prices = array();
  	$types = array(); 
  	//$options='';
    $postId = Cart66Common::postVal('id');
    $product = new Cart66Product();
    $products = $product->getModels("where id=$postId", "order by name");
    $data = array();
    foreach($products as $p) {
      if($p->itemNumber==""){
        $type='id';
      }
      else{
        $type='item';
      }

  	  $types[] = htmlspecialchars($type);

  	  if(CART66_PRO && $p->isPayPalSubscription()) {
  	    $sub = new Cart66PayPalSubscription($p->id);
  	    $subPrice = strip_tags($sub->getPriceDescription($sub->offerTrial > 0, '(trial)'));
  	    $prices[] = htmlspecialchars($subPrice);
  	    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] subscription price in dialog: $subPrice");
  	  }
  	  else {
  	    $prices[] = htmlspecialchars(strip_tags($p->getPriceDescription()));
  	  }


  	  //$options .= '<option value="'.$id.'">'.$p->name.' '.$description.'</option>';
      $data[] = array('type' => $types, 'price' => $prices, 'item' => $p->itemNumber);
    }
    echo json_encode($data);
    die();
  }
  
  public static function ajaxTaxUpdate() {
    if(isset($_POST['state']) && isset($_POST['state_text']) && isset($_POST['zip']) && isset($_POST['gateway'])) {
      $gateway = Cart66Ajax::loadAjaxGateway($_POST['gateway']);
      $gateway->setShipping(array('state_text' => $_POST['state_text'], 'state' => $_POST['state'], 'zip' => $_POST['zip']));
      $s = $gateway->getShipping();
      if($s['state'] && $s['zip']){
        $id = 1;
        $taxLocation = $gateway->getTaxLocation();
        $tax = $gateway->getTaxAmount();
        $rate = $gateway->getTaxRate();
        $total = Cart66Session::get('Cart66Cart')->getGrandTotal() + $tax;
        Cart66Session::set('Cart66Tax', $tax);
        Cart66Session::set('Cart66TaxRate', round($rate, 2));
      }
      else {
        $id = 0;
        $tax = 0;
        $rate = 0;
        $total = Cart66Session::get('Cart66Cart')->getGrandTotal() + $tax;
        Cart66Session::set('Cart66Tax', $tax);
        Cart66Session::set('Cart66TaxRate', round($rate, 2));
      }
      if(Cart66Session::get('Cart66Cart')->getTax('All Sales')) {
        $rate = $gateway->getTaxRate();
        Cart66Session::set('Cart66TaxRate', round($rate, 2));
      }
    }
    $result = array(
      'id' => $id,
      'state' => $s['state'],
      'zip' => $s['zip'],
      'tax' => CART66_CURRENCY_SYMBOL . number_format($tax, 2),
      'rate' => $rate = 0 ? '0.00' . '%' : round($rate, 2) . '%',
      'total' => CART66_CURRENCY_SYMBOL . number_format($total, 2)
    );
    echo json_encode($result);
    die();
  }
  
  public function loadAjaxGateway($gateway) {
    switch($gateway) {
      case 'Cart66ManualGateway':
        require_once(CART66_PATH . "/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'Cart66AuthorizeNet':
        require_once(CART66_PATH . "/pro/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'Cart66Eway':
        require_once(CART66_PATH . "/pro/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'Cart66Mijireh':
        require_once(CART66_PATH . "/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'Cart66MWarrior':
        require_once(CART66_PATH . "/pro/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'Cart66PayLeap':
        require_once(CART66_PATH . "/pro/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'Cart66PayPalPro':
        require_once(CART66_PATH . "/pro/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'Cart66Stripe':
        require_once(CART66_PATH . "/pro/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      default:
        break;
    }
    return $gateway;
  }
  
  public static function ajaxCartElements($args="") {

    $items = Cart66Session::get('Cart66Cart')->getItems();
    $product = new Cart66Product();
    $currencySymbol = CART66_CURRENCY_SYMBOL;
    $products = array();
    foreach($items as $itemIndex => $item) {
      $product->load($item->getProductId());
      $products[] = array(
        'productName' => $item->getFullDisplayName(),
        'productQuantity' => $item->getQuantity(),
        'productPrice' => number_format($item->getProductPrice(), 2),
        'productSubtotal' => number_format($item->getProductPrice() * $item->getQuantity(), 2),
        'currencySymbol' => $currencySymbol
      );
    }
    
    $summary = array(
      'items' => ' ' . _n('item', 'items', Cart66CartWidget::countItems(), 'cart66'), 
      'amount' => number_format(Cart66CartWidget::getSubTotal(), 2), 
      'count' => Cart66CartWidget::countItems(), 
      'currencySymbol' => $currencySymbol
    );
    
    $array = array(
      'summary' => $summary,
      'products' => $products,
      'subtotal' => number_format(Cart66Session::get('Cart66Cart')->getSubTotal(), 2),
      'shipping' => Cart66Session::get('Cart66Cart')->requireShipping() ? 1 : 0,
      'shippingAmount' => number_format(Cart66Session::get('Cart66Cart')->getShippingCost(), 2)
    );
    echo json_encode($array);
    die();
  }
  
  public static function ajaxAddToCart() {
    $message = self::addToCartFunctions();
    echo json_encode($message);
  	die();
  }
  
  public function addToCartFunctions() {
    $message = '';
    $msgId = '';
    $itemId = Cart66Common::postVal('cart66ItemId');
    $itemName = Cart66Common::postVal('itemName');
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Adding item to cart: $itemId");
    
    $options = '';
    $optionResult = '';
    if(isset($_POST['options_1'])) {
      $options = Cart66Common::postVal('options_1');
      $optionResult = Cart66Common::postVal('options_1');
    }
    if(isset($_POST['options_2'])) {
      $options .= '~' . Cart66Common::postVal('options_2');
      $optionResult .= ', ' . Cart66Common::postVal('options_2');
    }
    
    $optionResult = ($optionResult !=null) ? '(' . $optionResult . ') ' : '';
    
    $itemQuantity = '';
    if(isset($_POST['item_quantity'])) {
      $itemQuantity = ($_POST['item_quantity'] > 0) ? round($_POST['item_quantity'],0) : 1;
    }
    else{
      $itemQuantity = 1;
    }
    
    if(isset($_POST['item_user_price'])){
      $sanitizedPrice = preg_replace("/[^0-9\.]/","",$_POST['item_user_price']);
      Cart66Session::set("userPrice_$itemId",$sanitizedPrice);
    }
    
    $productUrl = null;
    if(isset($_POST['product_url'])){
      $productUrl = $_POST['product_url'];
    }
    
    if(Cart66Setting::getValue('continue_shopping') == 1){
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] continue shopping is 1");
      // force the last page to be store home
      $lastPage = Cart66Setting::getValue('store_url') ? Cart66Setting::getValue('store_url') : get_bloginfo('url');
      Cart66Session::set('Cart66LastPage', $lastPage);
    }
    else{
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] add to cart task and http referrer is set last page is being set as referrer");
      Cart66Session::set('Cart66LastPage', $productUrl);
    }
    
    $items = Cart66Session::get('Cart66Cart')->getItems();
    if($items) {
      foreach($items as $itemIndex => $item) {
        $productId = $item->getProductId();
        $productOptions = $item->getOptionInfo();
        $actualQuantity = $itemQuantity + $item->getQuantity();
        if($productId == $itemId && $productOptions == $options) {
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] starting 3");
          if(Cart66Product::confirmInventory($productId, $productOptions, $actualQuantity)) {
            Cart66Session::get('Cart66Cart')->addItem($itemId, $itemQuantity, $options, null, $productUrl, true);
            $message = __('We have successfully added', 'cart66') . " <strong>$itemQuantity</strong> $itemName $optionResult" . __('to the cart', 'cart66') . ".";
            $msgQuantityInCart = $actualQuantity;
            $msgId = 0;
            break;
          }
          else {
            $qtyAvailable = Cart66Product::checkInventoryLevelForProduct($productId, $productOptions);
            if($qtyAvailable > 0) {
              Cart66Session::get('Cart66Cart')->addItem($itemId, $qtyAvailable, $options, null, $productUrl, true);
              $message = __('The quantity for', 'cart66') . " $itemName $optionResult" . __("could not be changed to","cart66") . " <strong>$actualQuantity</strong> " . __("because we only have", "cart66") . " $qtyAvailable " . __("in stock","cart66") . ". " . __('Your cart has been updated based on our available inventory', 'cart66') . ".";
              Cart66Common::log("Quantity available ($qtyAvailable) cannot meet desired quantity ($itemQuantity) for product id: " . $item->getProductId());
              $msgQuantityInCart = $qtyAvailable;
              $msgId = -1;
              break;
            }
            else {
              Cart66Common::log("Item not added due to inventory failure");
              $soldOutLabel = Cart66Setting::getValue('label_out_of_stock') ? strtolower(Cart66Setting::getValue('label_out_of_stock')) : __('out of stock', 'cart66');
              $message = __('We could not add', 'cart66') . " <strong>$actualQty $itemName $optionResult</strong>" . __('to the cart because we are ', 'cart66') . $soldOutLabel . ".";
              $msgQuantityInCart = 0;
              $msgId = -2;
              break;
            }
          }
        }
        else {
          if(Cart66Product::confirmInventory($itemId, $options, $itemQuantity)) {
            Cart66Session::get('Cart66Cart')->addItem($itemId, $itemQuantity, $options, null, $productUrl, true);
            $message = __('We have successfully added', 'cart66') . " <strong>$itemQuantity</strong> $itemName $optionResult" . __('to the cart', 'cart66') . ".";
            $msgQuantityInCart = $itemQuantity;
            $msgId = 0;
            break;
          }
          else {
            $actualQty = Cart66Product::checkInventoryLevelForProduct($itemId, $options);
            if($actualQty > 0){
              Cart66Session::get('Cart66Cart')->addItem($itemId, $actualQty, $options, null, $productUrl, true);
              $message = __('We only added', 'cart66') . " <strong>$actualQty</strong> $itemName $optionResult" . __('to the cart because that is all we have in stock', 'cart66') . ". " . __('Your cart has been updated based on our available inventory', 'cart66') . ".";
              $msgQuantityInCart = $actualQty;
              $msgId = -1;
              break;
            }
            else {
              Cart66Common::log("Item not added due to inventory failure");
              $soldOutLabel = Cart66Setting::getValue('label_out_of_stock') ? strtolower(Cart66Setting::getValue('label_out_of_stock')) : __('out of stock', 'cart66');
              $message = __('We could not add', 'cart66') . " <strong>$actualQty $itemName $optionResult</strong>" . __('to the cart because we are ', 'cart66') . $soldOutLabel . ".";
              $msgQuantityInCart = 0;
              $msgId = -2;
              break;
            }
          }
        }
      }
    }
    else {
      if(Cart66Product::confirmInventory($itemId, $options, $itemQuantity)) {
        Cart66Session::get('Cart66Cart')->addItem($itemId, $itemQuantity, $options, null, $productUrl, true);
        $message = __('We have successfully added', 'cart66') . " <strong>$itemQuantity</strong> $itemName $optionResult" . __('to the cart', 'cart66') . ".";
        $msgQuantityInCart = $itemQuantity;
        $msgId = 0;
      }
      else {
        $actualQty = Cart66Product::checkInventoryLevelForProduct($itemId, $options);
        if($actualQty > 0){
          Cart66Session::get('Cart66Cart')->addItem($itemId, $actualQty, $options, null, $productUrl, true);
          $message = __('We only added', 'cart66') . " <strong>$actualQty</strong> $itemName $optionResult" . __('to the cart because that is all we have in stock', 'cart66') . ". " . __('Your cart has been updated based on our available inventory', 'cart66') . ".";
          $msgQuantityInCart = $actualQty;
          $msgId = -1;
        }
        else {
          Cart66Common::log("Item not added due to inventory failure");
          $soldOutLabel = Cart66Setting::getValue('label_out_of_stock') ? strtolower(Cart66Setting::getValue('label_out_of_stock')) : __('out of stock', 'cart66');
          $message = __('We could not add', 'cart66') . " <strong>$actualQty $itemName $optionResult</strong>" . __('to the cart because we are ', 'cart66') . $soldOutLabel . ".";
          $msgQuantityInCart = 0;
          $msgId = -2;
        }
      }
    }
    if(!empty($message)) {
      $msgRequestedQuantity = $itemQuantity;
      $msgProductName = $itemName;
      $msgOptions = $optionResult;
      $message = array(
        'msgId' => $msgId, 
        'msg' => $message, 
        'quantityInCart' => $msgQuantityInCart, 
        'requestedQuantity' => $msgRequestedQuantity, 
        'productName' => $msgProductName, 
        'productOptions' => $options
      );
    }
    return $message;
  }
  
  public static function promotionProductSearch() {
    global $wpdb;
    $search = Cart66Common::getVal('q');
    $product = new Cart66Product();
    $tableName = Cart66Common::getTableName('products');
    $products = $wpdb->get_results("SELECT id, name from $tableName WHERE name LIKE '%%%$search%%' ORDER BY id ASC LIMIT 10");
    $data = array();
    foreach($products as $p) {
      $data[] = array('id' => $p->id, 'name' => $p->name);
    }
    echo json_encode($data);
    die();
  }
  
  public static function loadPromotionProducts() {
    $productId = Cart66Common::postVal('productId');
    $product = new Cart66Product();
    $ids = explode(',', $productId);
    $selected = array();
    foreach($ids as $id) {
      $product->load($id);
      $selected[] = array('id' => $id, 'name' => $product->name);
    }
    echo json_encode($selected);
    die();
  }
  
  public static function saveSettings() {
    $error = '';
    foreach($_REQUEST as $key => $value) {
      if($key[0] != '_' && $key != 'action' && $key != 'submit') {
        if(is_array($value) && $key != 'admin_page_roles') {
          $value = array_filter($value, 'strlen');
          if(empty($value)) {
            $value = '';
          }
          else {
            $value = implode('~', $value);
          }
        }

        if($key == 'home_country') {
          $hc = Cart66Setting::getValue('home_country');
          if($hc != $value) {
            $method = new Cart66ShippingMethod();
            $method->clearAllLiveRates();
          }
        }
        elseif($key == 'countries') {
          if(strpos($value, '~') === false) {
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] country list value: $value");
            $value = '';
          }
          if(empty($value) && !empty($_REQUEST['international_sales'])){
            $error = "Please select at least one country to ship to.";
          }
        }
        elseif($key == 'enable_logging' && $value == '1') {
          try {
            Cart66Log::createLogFile();
          }
          catch(Cart66Exception $e) {
            $error = '<span>' . $e->getMessage() . '</span>';
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Caught Cart66 exception: " . $e->getMessage());
          }
        }
        elseif($key == 'constantcontact_list_ids') {
          
        }
        elseif($key == 'admin_page_roles') {
          $value = serialize($value);
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Saving Admin Page Roles: " . print_r($value,true));
        }

        Cart66Setting::setValue($key, trim(stripslashes($value)));

        if(CART66_PRO && $key == 'order_number') {
          $versionInfo = Cart66ProCommon::getVersionInfo();
          if(!$versionInfo) {
            Cart66Setting::setValue('order_number', '');
            $error = '<span>' . __( 'Invalid Order Number' , 'cart66' ) . '</span>';
          }
        }
      }
    }

    if($error) {
      $result[0] = 'Cart66Modal alert-message alert-error';
      $result[1] = "<strong>" . __("Warning","cart66") . "</strong><br/>$error";
    }
    else {
      $result[0] = 'Cart66Modal alert-message success';
      $result[1] = '<strong>Success</strong><br/>' . $_REQUEST['_success'] . '<br>'; 
    }

    $out = json_encode($result);
    echo $out;
    die();
  }
  
  public static function updateGravityProductQuantityField() {
    $formId = Cart66Common::getVal('formId');
    $gr = new Cart66GravityReader($formId);
    $fields = $gr->getStandardFields();
    header('Content-type: application/json');
    echo json_encode($fields);
    die();
  }
  
  public function checkInventoryOnAddToCart() {
    $result = array(true);
    $itemId = Cart66Common::postVal('cart66ItemId');
    $options = '';
    $optionsMsg = '';

    $opt1 = Cart66Common::postVal('options_1');
    $opt2 = Cart66Common::postVal('options_2');

    if(!empty($opt1)) {
      $options = $opt1;
      $optionsMsg = trim(preg_replace('/\s*([+-])[^$]*\$.*$/', '', $opt1));
    }
    if(!empty($opt2)) {
      $options .= '~' . $opt2;
      $optionsMsg .= ', ' . trim(preg_replace('/\s*([+-])[^$]*\$.*$/', '', $opt2));
    }

    $scrubbedOptions = Cart66Product::scrubVaritationsForIkey($options);
    if(!Cart66Product::confirmInventory($itemId, $scrubbedOptions)) {
      $result[0] = false;
      $p = new Cart66Product($itemId);

      $counts = $p->getInventoryNamesAndCounts();
      $out = '';

      if(count($counts)) {
        $out = '<table class="inventoryCountTableModal">';
        $out .= '<tr><td colspan="2"><strong>Currently In Stock</strong></td></tr>';
        foreach($counts as $name => $qty) {
          $out .= '<tr>';
          $out .= "<td>$name</td><td>$qty</td>";
          $out .= '</tr>';
        }
        $out .= '</table>';
      }
      $soldOutLabel = Cart66Setting::getValue('label_out_of_stock') ? strtolower(Cart66Setting::getValue('label_out_of_stock')) : __('out of stock', 'cart66');
      $result[1] = $p->name . " " . $optionsMsg . " is $soldOutLabel $out";
    }

    $result = json_encode($result);
    echo $result;
    die();
  }
  
  public static function pageSlurp() {
    require_once(CART66_PATH . "/models/Pest.php");
    require_once(CART66_PATH . "/models/PestJSON.php");
    
    $page_id = Cart66Common::postVal('page_id');
    $page = get_page($page_id);
    $slurp_url = get_permalink($page->ID);
    $html = false;
    $job_id = $slurp_url;
    
    wp_update_post(array('ID' => $page->ID, 'post_status' => 'publish'));
    $remote = wp_remote_get($slurp_url);
    if(!is_wp_error($remote) && $remote['response']['code'] == '200') {
      $html = $remote['body'];
    }
    wp_update_post(array('ID' => $page->ID, 'post_status' => 'private'));
    
    if($html) {
      $access_key = Cart66Setting::getValue('mijireh_access_key');
      $rest = new PestJSON(MIJIREH_CHECKOUT);
      $rest->setupAuth($access_key, '');
      $data = array(
        'url' => $slurp_url,
        'html' => htmlentities($html, ENT_COMPAT | 0, 'UTF-8')
      );
      
      try {
        $response = $rest->post('/api/1/slurps', $data);
        $job_id = $response['job_id'];
      }
      catch(Pest_Unauthorized $e) {
        header('Bad Request', true, 400);
        die();
      }
      
    }
    else {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] NO HTML!!!!");
    }
    
    echo $job_id;
    die;
  }
  
  public static function dismissMijirehNotice() {
    Cart66Setting::setValue('mijireh_notice', 1);
  }
  
}