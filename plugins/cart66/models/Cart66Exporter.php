<?php
class Cart66Exporter {
  
  public static function exportOrders($startDate, $endDate) {
    global $wpdb;
    $start = date('Y-m-d 00:00:00', strtotime($startDate));
    $end = date('Y-m-d 00:00:00', strtotime($endDate . ' + 1 day'));
    
    $orders = Cart66Common::getTableName('orders');
    $items = Cart66Common::getTableName('order_items');
    
    $orderHeaders = array(
      'id' => __('Order ID'),
      'trans_id' => __('Order Number'),
      'ordered_on' => __('Date'),
      'bill_first_name' => __('Billing First Name'),
      'bill_last_name' => __('Billing Last Name'),
      'bill_address' => __('Billing Address'),
      'bill_address2' => __('Billing Address 2'),
      'bill_city' => __('Billing City'),
      'bill_state' => __('Billing State'),
      'bill_country' => __('Billing Country'),
      'bill_zip' => __('Billing Zip Code'),
      'ship_first_name' => __('Shipping First Name'),
      'ship_last_name' => __('Shipping Last Name'),
      'ship_address' => __('Shipping Address'),
      'ship_address2' => __('Shipping Address 2'),
      'ship_city' => __('Shipping City'),
      'ship_state' => __('Shipping State'),
      'ship_country' => __('Shipping Country'),
      'ship_zip' => __('Shipping Zip Code'),
      'phone' => __('Phone'),
      'email' => __('Email'),
      'coupon' => __('Coupon'),
      'discount_amount' => __('Discount Amount'),
      'shipping' => __('Shipping Cost'),
      'subtotal' => __('Subtotal'),
      'tax' => __('Tax'),
      'total' => __('Total'),
      'ip' => __('IP Address'),
      'shipping_method' => __('Delivery Method')
    );
    
    $orderColHeaders = implode(',', $orderHeaders);
    $orderColSql = implode(',', array_keys($orderHeaders));
    $out  = $orderColHeaders . ",Form Data,Item Number,Description,Quantity,Product Price\n";
    
    $sql = "SELECT $orderColSql from $orders where ordered_on >= %s AND ordered_on < %s order by ordered_on";
    $sql = $wpdb->prepare($sql, $start, $end);
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] SQL: $sql");
    $selectedOrders = $wpdb->get_results($sql, ARRAY_A);
    
    foreach($selectedOrders as $o) {
      $itemRowPrefix = '"' . $o['id'] . '","' . $o['trans_id'] . '",' . str_repeat(',', count($o)-3);
      $orderId = $o['id'];
      $sql = "SELECT form_entry_ids, item_number, description, quantity, product_price FROM $items where order_id = $orderId";
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Item query: $sql");
      $selectedItems = $wpdb->get_results($sql, ARRAY_A);
      $out .= '"' . implode('","', $o) . '"';
      $printItemRowPrefix = false;
      if(!empty($selectedItems)) {
        foreach($selectedItems as $i) {
          if($printItemRowPrefix) {
            $out .= $itemRowPrefix;
          }

          if($i['form_entry_ids'] && CART66_PRO){
            $GReader = new Cart66GravityReader();
            $i['form_entry_ids'] = $GReader->displayGravityForm($i['form_entry_ids'],true);
            $i['form_entry_ids'] = str_replace("\"","''",$i['form_entry_ids']);
          }

          $i['description'] = str_replace(","," -",$i['description']);

          $out .= ',"' . implode('","', $i) . '"';
          $out .= "\n";
          $printItemRowPrefix = true;
        }
      }
      else {
        $out .= "\n";
      }
      
    }
    
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Report\n$out");
    return $out;
  }
  
}