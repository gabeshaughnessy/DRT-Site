<?php

class Cart66CartItem {
  private $_productId;
  private $_quantity;
  private $_optionInfo;
  private $_priceDifference;
  private $_customFieldInfo;
  private $_productUrl;
  private $_formEntryIds;
  
  public function __construct($productId=0, $qty=1, $optionInfo='', $priceDifference=0, $productUrl='') {
    $this->_productId = $productId;
    $this->_quantity = $qty;
    $this->_optionInfo = $optionInfo;
    $this->_priceDifference = $priceDifference;
    $this->_productUrl = $productUrl;
    $this->_formEntryIds = array();
    // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] New Cart Item Option Info: $optionInfo");
  }
  
  public function getPriceDifference($optionInfo){
    $output = 0;
    if(strlen($optionInfo) > 1){
      $product = new Cart66Product($this->_productId);
      $output = $product->getOptionPrice($optionInfo);
    }
    return $output;
  }
  
  
  public function setProductId($id) {
    if(is_numeric($id) && $id > 0) {
      $this->_productId = $id;
    }
  }
  
  public function getProductId() {
    return $this->_productId;
  }
  
  public function getProduct() {
    $product = new Cart66Product($this->_productId);
    return $product;
  }
  
  public function setOptionInfo($value) {
    $this->_optionInfo = $value;
  }
  
  public function getOptionInfo() {
    $options = $this->_optionInfo;
    if($this->isSubscription()) {
      //$option .= $this->getPriceDescription();
      //Cart66Common::log("This is a subscription product: $options " . $this->getItemNumber());
    }
    return $options;
  }
  
  public function setQuantity($qty) {
    if(is_numeric($qty) && $qty >= 0) {
      $qty = ceil($qty);
      $product = new Cart66Product($this->_productId);
      
      if($product->isSubscription() || $product->isMembershipProduct()) {
        // Subscriptions may only have a quantity of 1
        $qty = 1;
      }
      elseif($product->is_user_price == 1){
        $qty = 1;
      }
      else {
        if($product->maxQuantity > 0) {
          // Only limit quantity when max is set to a value greater than zero
          if($product->maxQuantity < $qty) {
            $qty = $product->maxQuantity;
          }
        }
        if($product->minQuantity > 0) {
          // Only limit quantity when min is set to a value greater than zero
          if($product->minQuantity > $qty) {
            $qty = $product->minQuantity;
          }
        }
        if($product->gravity_form_id > 0) {
          // Set quantity to zero because this is a gravity forms product with no entries
          if(count($this->_formEntryIds) == 0) {
            $qty = 0;
          }
          else {
            if($product->gravity_form_qty_id > 0) {
              // update gravity form entry for quanity to keep cart and gform in sync
              $gr = new Cart66GravityReader();
              $entryId = $this->_formEntryIds[0];
              $qtyFieldId = $product->gravity_form_qty_id;
              $gr->updateQuantity($entryId, $qtyFieldId, $qty);
            }
          }
        }
        
      }
      
      $this->_quantity = $qty;
    }
  }
  
  public function setCustomFieldInfo($info) {
    $info = stripslashes($info);
    $this->_customFieldInfo = $info;
  }
  
  public function getQuantity() {
    return $this->_quantity;
  }
  
  public function getCustomField($itemIndex, $fullMode=true) {
    $out = '';
    if($this->_productId > 0) {
      $p = new Cart66Product();
      $p->load($this->_productId);
      $errorClass = '';
      if(is_array(Cart66Session::get('Cart66CustomFieldWarning')) && in_array($p->name, Cart66Session::get('Cart66CustomFieldWarning'))) {
        $errorClass = ' Cart66ErrorField';
      }
      if($p->custom == 'single') {
        $desc = $p->custom_desc;
        $value = $this->_customFieldInfo;
        if($fullMode) {
          $buttonValue = empty($value) ? 'Save' : 'Update';
          $showCustomForm = empty($value) ? '' : 'none';
          $change = empty($value) ? '' : "<a href='' onclick='' id='change_$itemIndex'>Change</a>";
          $out = "
          <script type='text/javascript'>
            (function($){
              $(document).ready(function(){
                $('#change_$itemIndex').click(function() {
            		  $('#customForm_$itemIndex').toggle();
            		  return false;
            		});
              })
            })(jQuery);
          </script>
          <br/><p class=\"Cart66CustomFieldDesc\">$desc:<br/><strong>$value</strong> $change</p>
          <div id='customForm_$itemIndex' style='display: $showCustomForm;'>
          <input type=\"text\" name=\"customFieldInfo[$itemIndex]\" value=\"$value\" class=\"Cart66CustomTextField" . $errorClass . "\" id=\"custom_field_info_$itemIndex\" />
          <input type=\"submit\" value=\"$buttonValue\" /></div>";
        }
        else {
          if(empty($value)) {
            $cartPage = get_page_by_path('store/cart');
            $viewCartLink = get_permalink($cartPage->ID);
            $value = "<a href='$viewCartLink'>Click here to enter your information</a>";
          }
          $out = "<br/><p class=\"Cart66CustomFieldDesc\">$desc:<br/><strong>$value</strong></p>";
          
        }
      }
      elseif($p->custom == 'multi') {
        $desc = $p->custom_desc;
        $value = $this->_customFieldInfo;
        if($fullMode) {
          $buttonValue = empty($value) ? 'Save' : 'Update';
          $showCustomForm = empty($value) ? '' : 'none';
          $change = empty($value) ? '' : "<a href='' onclick='' id='change_$itemIndex'>Change</a>";
          $brValue = nl2br($value);
          $out = "
          <script type='text/javascript'>
            (function($){
              $(document).ready(function(){
                $('#change_$itemIndex').click(function() {
            		  $('#customForm_$itemIndex').toggle();
            		  return false;
            		});
              })
            })(jQuery);
          </script> 
          <br/><p class=\"Cart66CustomFieldDesc\">$desc:<br/><strong>$brValue</strong><br/>$change</p>
          <div id='customForm_$itemIndex' style='display: $showCustomForm;'>
          <textarea name=\"customFieldInfo[$itemIndex]\" class=\"Cart66CustomTextarea\" id=\"custom_field_info_$itemIndex\" />$value</textarea>
          <br/><input type=\"submit\" value=\"$buttonValue\" /></div>";
        }
        else {
          if(empty($value)) {
            $cartPage = get_page_by_path('store/cart');
            $viewCartLink = get_permalink($cartPage->ID);
            $value = "<a href='$viewCartLink'>Click here to enter your information</a>";
          }
          $value = nl2br($value);
          $out = "<br/><p class=\"Cart66CustomFieldDesc\">$desc:<br/><strong>$value</strong></p>";
        }
      }
    }
    return $out;
  }
  
  /**
   * Return the value of the custom field info or false if the value is empty
   */
  public function getCustomFieldInfo() {
    $info = false;
    if(!empty($this->_customFieldInfo)) {
      $info = $this->_customFieldInfo;
    }
    return $info;
  }
  
  /**
   * Return the value of the custom field description or false if the value is empty
   */
  public function getCustomFieldDesc() {
    $desc = false;
    if($this->_productId > 0) {
      $p = new Cart66Product();
      $p->load($this->_productId);
      if(strlen($p->custom_desc) > 0) {
        $desc = $p->custom_desc;
      }
    }
    return $desc;
  }
  
  /**
   * Return the price for the product + the price difference applied by selected product options.
   * If the product is a subscription this price includes both the one time fee and the first 
   * subscription payment if the subscription start date is today.
   * 
   * @param boolean $includeFirstSubscription
   * @return float Price of product
   */
  public function getProductPrice() {
    if($this->_productId < 1) {
      return false;
    }
    
    $product = new Cart66Product($this->_productId);
    if($this->isPayPalSubscription()) {
      $price = $product->getCheckoutPrice();
    }
    elseif($this->isSpreedlySubscription()) {
      $price = $product->getCheckoutPrice();
    }
    elseif($product->is_user_price == 1 || $product->gravity_form_pricing) {
      $session_var_name = "userPrice_$this->_productId";
      
      if($product->gravity_form_pricing) {
        $session_var_name .= '_' . $this->getFirstFormEntryId();
      }
      
      //Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Looking for product price in session variable: " . $session_var_name . "\n" . 
      //  print_r(Cart66Session::dump(), true));
        
      if(Cart66Session::get($session_var_name)){
        // using a user-defined price
        $userPrice = Cart66Session::get($session_var_name);
        
        if($product->min_price > 0 && $userPrice < $product->min_price){
          $userPrice = $product->min_price;
        }
        
        if($product->max_price > 0 && $userPrice > $product->max_price){
          $userPrice = $product->max_price;
        }
        
        $price = $userPrice;
      }
      else{
        $price = $product->price;
      }
      
    }
    else {
      $price = $product->price + $this->_priceDifference;
    }
    
    return $price;
  }
  
  public function getBaseProductPrice(){
    $product = new Cart66Product($this->_productId);
    if($product->is_paypal_subscription) {
      $price = $product->setup_fee;
    }
    else {
      $price = $product->price + $this->_priceDifference;
    }
    
    if(CART66_PRO && !empty($product->gravity_form_id) && $product->gravity_form_pricing == 1){
      // gravity form price
      $price = Cart66GravityReader::getPrice($this->getFirstFormEntryId()) / $this->getQuantity();
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Gravity Form product price: $price");
    }
    
    return $price;
  }
  
  public function getProductPriceDescription() {
    if($this->_productId > 0) {
      $product = new Cart66Product($this->_productId);
      if($product->isPayPalSubscription()) {
        $product = new Cart66PayPalSubscription($product->id);
        $priceDescription = $product->getPriceDescription($product->offerTrial > 0, '(trial)');
      }
      elseif($product->isSpreedlySubscription()) {
        $product = new Cart66Product($product->id);
        $priceDescription =  $product->getPriceDescription();
      }
      elseif($product->is_user_price == 1 || $product->gravity_form_pricing) {
        $session_var_name = "userPrice_$this->_productId";

        if($product->gravity_form_pricing) {
          $session_var_name .= '_' . $this->getFirstFormEntryId();
        }
        
        if(Cart66Session::get($session_var_name)) {
          $userPrice = Cart66Session::get($session_var_name);
          if($product->min_price > 0 && $userPrice < $product->min_price){
            $userPrice = $product->min_price;
          }
          if($product->max_price > 0 && $userPrice > $product->max_price){
            $userPrice = $product->max_price;
          }
          $priceDescription = CART66_CURRENCY_SYMBOL.number_format($userPrice,2);
        }
        else{
          $priceDescription = $product->price;
        }
      }
      else {
        $priceDescription = $product->getPriceDescription($this->_priceDifference);
      }
    }
    return $priceDescription;
  }
  
  public function getItemNumber() {
    if($this->_productId > 0) {
      $p = new Cart66Product();
      $p->load($this->_productId);
      return $p->item_number;
    }
    return false;
  }
  
  public function getWeight() {
    if($this->_productId > 0) {
      $p = new Cart66Product();
      $p->load($this->_productId);
      return $p->weight;
    }
    return false;
  }
  
  public function getFormEntryIds() {
    return $this->_formEntryIds;
  }

  /**
   * Return the first form entry id or false if there are not ids
   */
  public function getFirstFormEntryId() {
    $id = false;
    if(is_array($this->_formEntryIds)) {
      $id = reset($this->_formEntryIds);
    }
    return $id;
  }
  
  public function getFullDisplayName() {
    $product = new Cart66Product($this->_productId);
    $fullName = $product->name;
    $optionInfo = $this->getOptionInfo();
    if(strlen($optionInfo) >= 1) {
      $options = split(',', $optionInfo);
      $options = implode(', ', $options);
      $fullName .= " ($options)";
    }
    return $fullName;
  }
  
  public function getProductUrl() {
    return $this->_productUrl;
  }
  
  public function isEqual(Cart66CartItem $item) {
    $isEqual = true;
    if($this->_productId != $item->getProductId()) {
      $isEqual = false;
    }
    if($this->_optionInfo != $item->getOptionInfo()) {
      $isEqual = false;
    }
    return $isEqual;
  }
  
  public function isDigital() {
    $product = new Cart66Product($this->_productId);
    return $product->isDigital();
  }
  
  public function isShipped() {
    $product = new Cart66Product($this->_productId);
    return $product->isShipped();
  }
  
  public function isMembershipProduct() {
    $product = new Cart66Product($this->_productId);
    return $product->isMembershipProduct();
  }
  
  public function isSubscription() {
    $product = new Cart66Product($this->_productId);
    return $product->isSubscription();
  }
  
  public function isPayPalSubscription() {
    $product = new Cart66Product($this->_productId);
    return $product->isPayPalSubscription();
  }
  
  public function isSpreedlySubscription() {
    $product = new Cart66Product($this->_productId);
    return $product->isSpreedlySubscription();
  }
  
  public function getPayPalSubscription() {
    $sub = false;
    if($this->isPayPalSubscription()) {
      $product = new Cart66Product($this->_productId);
      $sub = $product->getPayPalSubscription();
    }
    return $sub;
  }
  
  /**
   * Return the spreedly subscription id if the product is a spreedly subscription product. 
   * Otherwise return false.
   */
  public function getSpreedlySubscriptionId() {
    $subId = false;
    $product = new Cart66Product($this->_productId);
    if($product->isSpreedlySubscription()) {
      $subId = $product->spreedlySubscriptionId;
    }
    return $subId;
  }
  
  /**
   * Return the spreedly subscription product id if the product is a spreedly subscription product. 
   * Otherwise return false.
   */
  public function getSpreedlyProductId() {
    $productId = false;
    $product = new Cart66Product($this->_productId);
    if($product->isSpreedlySubscription()) {
      $productId = $this->_productId;
    }
    return $productId;
  }
  
  /**
   * Return the PayPal subscription id if the product is a PayPal subscription product. 
   * Otherwise return false.
   */
  public function getPayPalSubscriptionId() {
    $subId = false;
    $product = new Cart66Product($this->_productId);
    if($product->isPayPalSubscription()) {
      $subId = $product->id; // Note: Products and PayPal subscriptions share the same database table
    }
    return $subId;
  }
  
  public function addFormEntryId($id) {
    if(!is_array($this->_formEntryIds)) {
      $this->_formEntryIds = array();
    }
    if(!in_array($id, $this->_formEntryIds)) {
      $this->_formEntryIds[] = $id;
    }
  }
  
  public function showAttachedForms($fullMode) {
    $out = '';
    if(is_array($this->_formEntryIds)) {
      foreach($this->_formEntryIds as $entryId) {
        /*
        $removeLink = '';
        if($fullMode) {
          $removeLink = str_replace( '%7E', '~', $_SERVER['REQUEST_URI']);
          $removeLink .= strpos($removeLink, '?') ? '&' : '?';
          $removeLink .= 'cart66-task=remove-attached-form&entry=' . $entryId;
          $removeLink = '<a class="Cart66RemoveFormLink" href="' . $removeLink . '">remove</a>';
        }
        */
        $out .= "<div class='Cart66GravityFormDisplay'>" . Cart66GravityReader::displayGravityForm($entryId) . "</div>";
      }
    }
    return $out;
  }
  
  public function detachFormEntry($lead_id) {
    $entries = $this->getFormEntryIds();
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Start to detach gravity forms: " . print_r($entries, true));
    
    if(in_array($lead_id, $entries)) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Lead id is in the list of entries: " . $lead_id);
      if(class_exists('RGForms')) {
        if(!class_exists('RGFormsModel')) {
          RGForms::init();
        }
        
        if(class_exists('RGFormsModel')) {
          global $wpdb;
          $lead_table = RGFormsModel::get_lead_table_name();
          $lead_notes_table = RGFormsModel::get_lead_notes_table_name();
          $lead_detail_table = RGFormsModel::get_lead_details_table_name();
          $lead_detail_long_table = RGFormsModel::get_lead_details_long_table_name();

          //Delete from detail long
          $sql = $wpdb->prepare(" DELETE FROM $lead_detail_long_table
                                  WHERE lead_detail_id IN(
                                      SELECT id FROM $lead_detail_table WHERE lead_id=%d
                                  )", $lead_id);
          $wpdb->query($sql);

          //Delete from lead details
          $sql = $wpdb->prepare("DELETE FROM $lead_detail_table WHERE lead_id=%d", $lead_id);
          $wpdb->query($sql);

          //Delete from lead notes
          $sql = $wpdb->prepare("DELETE FROM $lead_notes_table WHERE lead_id=%d", $lead_id);
          $wpdb->query($sql);

          //Delete from lead
          $sql = $wpdb->prepare("DELETE FROM $lead_table WHERE id=%d", $lead_id);
          $wpdb->query($sql);

          // Remove entry from array
          $entries = array_values(array_diff($entries, array($lead_id))); 
          $this->_formEntryIds = $entries;
          $qty = $this->getQuantity();
          $this->setQuantity($qty - 1);
        }
        
      }
    }
  }
  
  public function detachAllForms() {
    $entries = $this->getFormEntryIds();
    if(is_array($entries)) {
      foreach($entries as $id) {
        $this->detachFormEntry($id);
      }
    }
  }
  
  public function hasAttachedForms() {
    $hasForms = false;
    if(is_array($this->_formEntryIds) && count($this->_formEntryIds) > 0) {
      $hasForms = true;
    }
    return $hasForms;
  }
  
}