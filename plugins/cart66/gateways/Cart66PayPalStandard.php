<?php
class Cart66PayPalStandard {
  
  protected $_log;
  
  public function __construct() {
    $paypalUrl = Cart66Common::getPayPalUrl();
    Cart66Common::log("Constructing PayPal Gateway for IPN using URL: $paypalUrl");
  }
  
  /**
   * Save a PayPal IPN order from a Website Payments Pro cart sale.
   * 
   * @param array $pp Urldecoded array of IPN key value pairs
   */
  public function saveOrder($pp) {
    global $wpdb;
    
    $orderTable = Cart66Common::getTableName('orders');
    
    // Make sure the transaction id is not already in the database
    $sql = "SELECT count(*) as c from $orderTable where trans_id=%s";
    $sql = $wpdb->prepare($sql, $pp['txn_id']);
    $count = $wpdb->get_var($sql);
    if($count < 1) {
      $hasDigital = false;
      
      // Calculate subtotal
      $subtotal = 0;
      $numCartItems = ($pp['num_cart_items'] > 0) ? $pp['num_cart_items'] : 1;
      for($i=1; $i<= $numCartItems; $i++) {
        // PayPal in not consistent in the way it passes back the item amounts
        $amt = 0;
        if(isset($pp['mc_gross' . $i])) {
          $amt = $pp['mc_gross' . $i];
        }
        elseif(isset($pp['mc_gross_' . $i])) {
          $amt = $pp['mc_gross_' . $i];
        }
        $subtotal += $amt;
      }

      $statusOptions = Cart66Common::getOrderStatusOptions();
      $status = $statusOptions[0];

      $ouid = md5($pp['txn_id'] . $pp['address_street']);

      // Parse custom value
      $referrer = false;
      $deliveryMethod = $pp['custom'];
      if(strpos($deliveryMethod, '|') !== false) {
        list($deliveryMethod, $referrer, $gfData, $coupon) = explode('|', $deliveryMethod);
      }
      
      // Parse Gravity Forms ids
      $gfIds = array();
      if(!empty($gfData)) {
        $forms = explode(',', $gfData);
        foreach($forms as $f) {
          list($itemId, $formEntryId) = explode(':', $f);
          $gfIds[$itemId] = $formEntryId;
        }
      }

      // Look for discount amount
      $discount = 0;
      if(isset($pp['discount'])) {
        $discount = $pp['discount'];
      }
      
      // Look for coupon code
      $coupon_code = "none";
      if(isset($coupon) && $coupon!="") {
        $coupon_code = $coupon;
      }
      
      $data = array(
        'bill_first_name' => $pp['first_name'],
        'bill_last_name' => $pp['last_name'],
        'bill_address' => $pp['address_street'],
        'bill_city' => $pp['address_city'],
        'bill_state' => $pp['address_state'],
        'bill_zip' => $pp['address_zip'],
        'bill_country' => $pp['address_country'],
        'ship_first_name' => $pp['address_name'],
        'ship_address' => $pp['address_street'],
        'ship_city' => $pp['address_city'],
        'ship_state' => $pp['address_state'],
        'ship_zip' => $pp['address_zip'],
        'ship_country' => $pp['address_country'],
        'shipping_method' => $deliveryMethod,
        'email' => $pp['payer_email'],
        'phone' => $pp['contact_phone'],
        'shipping' => $pp['mc_handling'],
        'tax' => $pp['tax'],
        'subtotal' => $subtotal,
        'total' => $pp['mc_gross'],
        'coupon' => $coupon_code,
        'discount_amount' => $discount,
        'trans_id' => $pp['txn_id'],
        'ordered_on' => date('Y-m-d H:i:s', Cart66Common::localTs()),
        'status' => $status,
        'ouid' => $ouid
      );


      // Verify the first items in the IPN are for products managed by Cart66. It could be an IPN from some other type of transaction.
      $productsTable = Cart66Common::getTableName('products');
      $orderItemsTable = Cart66Common::getTableName('order_items');
      $sql = "SELECT id from $productsTable where item_number = '" . $pp['item_number1'] . "'";
      $productId = $wpdb->get_var($sql);
      if(!$productId) {
        throw new Exception("This is not an IPN that should be managed by Cart66");
      }
      
      // Look for the 100% coupons shipping item and move it back to a shipping costs rather than a product
      if($data['shipping'] == 0) {
        for($i=1; $i <= $numCartItems; $i++) {
          $itemNumber = strtoupper($pp['item_number' . $i]);
          if($itemNumber == 'SHIPPING') {
            $data['shipping'] = isset($pp['mc_gross_' . $i]) ? $pp['mc_gross_' . $i] : $pp['mc_gross' . $i];
          }
        }
      }
      
      $wpdb->insert($orderTable, $data);
      $orderId = $wpdb->insert_id;

      $product = new Cart66Product();
      for($i=1; $i <= $numCartItems; $i++) {
        $sql = "SELECT id from $productsTable where item_number = '" . $pp['item_number' . $i] . "'";
        $productId = $wpdb->get_var($sql);
        
        if($productId > 0) {
          $product->load($productId);

          // Decrement inventory
          $info = $pp['item_name' . $i];
          if(strpos($info, '(') > 0) {
            $start = strpos($info, '(');
            $end = strpos($info, ')');
            $length = $end - $start;
            $variation = substr($info, $start+1, $length-1);
            Cart66Common::log("PayPal Variation Information: $variation\n$info");
          }
          $qty = $pp['quantity' . $i];
          Cart66Product::decrementInventory($productId, $variation, $qty);

          if($hasDigital == false) {
            $hasDigital = $product->isDigital();
          }

          // PayPal is not consistent in the way it passes back the item amounts
          $amt = 0;
          if(isset($pp['mc_gross' . $i])) {
            $amt = $pp['mc_gross' . $i];
          }
          elseif(isset($pp['mc_gross_' . $i])) {
            $amt = $pp['mc_gross_' . $i]/$pp['quantity' . $i];
          }

          // Look for Gravity Form Entry ID
          $formEntryId = '';
          if(is_array($gfIds) && !empty($gfIds) && isset($gfIds[$i])) {
            $formEntryId = $gfIds[$i];
            if(class_exists('RGFormsModel')) {
              if($lead = RGFormsModel::get_lead($formEntryId)) {
                $lead['status'] = 'active';
                RGFormsModel::update_lead($lead);
              }
            }
          }

          $duid = md5($pp['txn_id'] . '-' . $orderId . '-' . $productId);
          $data = array(
            'order_id' => $orderId,
            'product_id' => $productId,
            'item_number' => $pp['item_number' . $i],
            'product_price' => $amt,
            'description' => $pp['item_name' . $i],
            'quantity' => $pp['quantity' . $i],
            'duid' => $duid,
            'form_entry_ids' => $formEntryId
          );
          $wpdb->insert($orderItemsTable, $data);
        }
        
      }
      
      // Handle email receipts
      if(CART66_PRO && Cart66Setting::getValue('enable_advanced_notifications') ==1) {
        $notify = new Cart66AdvancedNotifications($orderId);
        $notify->sendAdvancedEmailReceipts();
      }
      else {
        $notify = new Cart66Notifications($orderId);
        $notify->sendEmailReceipts();
      }
      
      $promotion = new Cart66Promotion();
      $promotion->loadByCode($coupon_code);
      if($promotion) {
        $promotion->updateRedemptions();
      }
      
      // Process affiliate reward if necessary
      if($referrer) {
        Cart66Common::awardCommission($orderId, $referrer);
      }
      
    } // end transaction id check
    
    
  }
  
}