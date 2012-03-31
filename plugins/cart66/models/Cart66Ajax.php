<?php
class Cart66Ajax {
  
  public static function saveSettings() {
    $error = '';
    foreach($_REQUEST as $key => $value) {
      if($key[0] != '_' && $key != 'action' && $key != 'submit') {
        if(is_array($value)) {
          $value = implode('~', $value);
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
        }
        elseif($key == 'enable_logging') {
          try {
            Cart66Log::createLogFile();
          }
          catch(Cart66Exception $e) {
            $error = '<span style="color: red;">' . $e->getMessage() . '</span>';
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Caught Cart66 exception: " . $e->getMessage());
          }
        }
        elseif($key == 'constantcontact_list_ids') {
          
        }

        Cart66Setting::setValue($key, trim(stripslashes($value)));

        if(CART66_PRO && $key == 'order_number') {
          $versionInfo = Cart66ProCommon::getVersionInfo();
          if(!$versionInfo) {
            Cart66Setting::setValue('order_number', '');
            $error = '<span style="color: red;">Invalid Order Number</span>';
          }
        }
      }
    }

    if($error) {
      $result[0] = 'Cart66ErrorModal';
      $result[1] = "<strong style='color: red;'>Warning</strong><br/>$error";
    }
    else {
      $result[0] = 'Cart66SuccessModal';
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
  
  function checkInventoryOnAddToCart() {
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

      $result[1] = $p->name . " " . $optionsMsg . " is&nbsp;out&nbsp;of&nbsp;stock $out";
    }

    $result = json_encode($result);
    echo $result;
    die();
  }
  
}