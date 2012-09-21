<?php
class Cart66Promotion extends Cart66ModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = Cart66Common::getTableName('promotions');
    parent::__construct($id);
  }
  
  public function getAmountDescription() {
    $amount = 'not set';
    if($this->id > 0) {
      if($this->type == 'dollar') {
        $amount = CART66_CURRENCY_SYMBOL . number_format($this->amount, 2, '.', ',') . ' off';
        if($this->apply_to == "shipping"){
          $amount .= " shipping";
        }
      }
      elseif($this->type == 'percentage') {
        $amount = number_format($this->amount, 0) . '% off';
      }
    }
    return $amount;
  }
  
  // If a minimium order is set it will display that amount, otherwise apply to all orders
  public function getMinOrderDescription() {
    $min = $this->minOrder;
    if($min > 0) {
      $min = CART66_CURRENCY_SYMBOL . $min;
    }
    else {
      $min = __('Apply to All Orders', 'cart66');
    }
    return $min;
  }
  
  // If a minimium order is set it will display that amount, otherwise apply to all orders
  public function getMaxOrderDescription() { // CHANGE AND COMBINE WITH ABOVE
    $max = $this->maxOrder;
    if($max > 0) {
      $max = CART66_CURRENCY_SYMBOL . $min;
    }
    else {
      $max = __('Apply to All Orders', 'cart66');
    }
    return $max;
  }
  
  public function getCodeAt($position=1){
    $codes = $this->code;
    if(substr($codes,0,1) == ","){
      $codes = substr($codes, 1, strlen($codes) - 1);
    }
    //remove suffix comma
    if(substr($codes,-1,1) == ","){
      $codes = substr($codes, 0, strlen($codes) - 1);
    }
     
    $codes = explode(",",$codes);
    if(isset($codes[$position-1])){
      $output = $codes[$position-1];
    }
    else{
      $output = $codes;
    }
    
    return $output;
  }
  
  public function getCode($trim=15,$allValid=false){
     $codes = $this->code;
     //remove prefix comma
     if(substr($codes,0,1) == ","){
       $codes = substr($codes, 1, strlen($codes) - 1);
     }
     //remove suffix comma
     if(substr($codes,-1,1) == ","){
       $codes = substr($codes, 0, strlen($codes) - 1);
     }
     
     //trim for display
     if($trim>0){
       $codes = substr($codes,0,$trim);
     }
     
     if(Cart66Session::get('Cart66PromotionCode') && !$allValid){
       $output = Cart66Session::get('Cart66PromotionCode');
     }
     else{
       $output = $codes;
     }
     
     return $output;
  }
  
  // Displays the promotion effective dates in the admin page
  public function effectiveDates() {
    $from = $this->effective_from;
    $to = $this->effective_to;
    
    if(empty($from) || $from == "0000-00-00 00:00:00") {
      $from = __('No Start Date', 'cart66');
    } else {
      $from = date("m/d/Y h:i a",strtotime($from));
    }
    if(empty($to) || $to == "0000-00-00 00:00:00") {
      $to = '<br />' . __('No End Date', 'cart66');
    } else {
      $to = '<br />' . date("m/d/Y h:i a",strtotime($to));
    }
    if((empty($this->effective_to) || $this->effective_to == "0000-00-00 00:00:00") && (empty($this->effective_from) || $this->effective_from == "0000-00-00 00:00:00")) {
      $from = __('Ongoing', 'cart66');
      $to = ' ';
    }
    $effective = $from . $to;
    return $effective;
  }
  
  public function getItemPriceAfterPromotion($product_id){
    $product = new Cart66Product($product_id);
    $productId = $product_id;
    
    $output = $product->price;
    if($this->apply_to == 'products'){
      if($this->isEligibleProduct($productId)){
        // product is eligible
        $productDiscountedPrice = $product->price - $this->amount;
        $productDiscountedPrice = ($productDiscountedPrice < 0) ? 0 : $productDiscountedPrice;
        $output = $productDiscountedPrice;
      }
            
    }
    
    return $output;
  }
  
  public function updateRedemptions($amount=1){
      
      $this->redemptions = $this->redemptions + $amount;
      $this->save();
  }
  
  
  
  public function save() {

    $errors = $this->validate();
   
    
    if(count($errors) == 0) {
      $this->_data['code'] = $this->cleanCodes($this->_data['code']);
      
      $this->min_order = ($this->min_order == "") ? null : $this->min_order;
      $this->max_order = ($this->max_order == "") ? null : $this->max_order;
      $this->effective_from = (!empty($this->effective_from) && $this->effective_from != "0000-00-00 00:00:00") ? date("Y-m-d H:i:s",strtotime($this->effective_from)) : null;
      $this->effective_to =  (!empty($this->effective_to) && $this->effective_to != "0000-00-00 00:00:00") ? date("Y-m-d H:i:s",strtotime($this->effective_to)) : null;
      

      $promotionId = parent::save();
    }
    if(count($errors)) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] " . get_class($this) . " save errors: " . print_r($errors, true));
      $this->setErrors($errors);
      $errors = print_r($errors, true);
      throw new Cart66Exception('Promotion save failed: ' . $errors, 66301);
    }
    return $promotionId;
  }
  
  public function cleanCodes($code=""){
    $userCode = str_replace(" ","",$code);
    $lastCharacter = $userCode[strlen($userCode)-1];
    $firstCharacter = $userCode[0];
    if($firstCharacter == "," && $lastCharacter == ","){
      // already clean
      $cleanedCode = $userCode;
    }
    else{
      $cleanedCode = strtoupper("," . $code . ",");
    }
    
    return $cleanedCode;
  }
  
  public function getErrors(){
    return $this->_errors;
  }
  
  public function validateCustomerPromotion($code){
    $this->_errors = false;
    
    $customerPromo = $this->loadByCode($code);
    
    if(!$customerPromo){
      // Promotion doesnt exist
      $this->_errors[] = __('That promotion is not valid.','cart66');
      return false;
    }
    
    if(!$this->isEnabled()){
      $this->_errors[] = __('That promotion is not enabled.','cart66');
    }  
    
    if(!$this->minAmountMet()){
      $this->_errors[] = __('The minimum required order total for that promotion has not been met.','cart66');
    }
    
    if(!$this->maxAmountMet()){
      $this->_errors[] = __('The maximum required order total for that promotion has not been met.','cart66');
    }
    
    if(!$this->minQuantityMet()){
      $this->_errors[] = __('The minimum quantity required for that promotion has not been met.','cart66');
    }
    
    if(!$this->maxQuantityMet()){
      $this->_errors[] = __('The maximum quantity allowed for that promotion has been exceeded.','cart66');
    }
    
    if(!$this->isEffective()){
      $this->_errors[] = __('This promotion is not currently active.','cart66');
    }  
    
    if(!$this->withinMaximumRedemptions()){
      $this->_errors[] = __('This promotion has been redeemed the maximum number of times allowed.','cart66');
    }
    
    if(!$this->eligibleProductsInCart()){
      $this->_errors[] = __('Your cart does not contain any eligible products for this promotion.','cart66');
    }
      
    if(empty($this->_errors)){
      $output = $this;
    }
    else{
      //$this->_errors[] = __('The promotion: ' . $code . ' could not be applied.','cart66');
      $output = false;
    }  
      
    
    return $output;
  }
  
  public function validate() {
    $errors = array();
    
    // Verify that the promotion code is present
    
    if(empty($this->name)) {
      $errors['name'] = __('Promotion Name is required', 'cart66');
    }
    
    if(empty($this->code)) {
      $errors['code'] = __('Promotion Code is required', 'cart66');
    }
    
    if(($this->effective_from != null && $this->effective_from != "0000-00-00 00:00:00") && 
       ($this->effective_to != null && $this->effective_to != "0000-00-00 00:00:00") && 
       strtotime($this->effective_to) <= strtotime($this->effective_from)) {
      $errors['effective'] = __(' The Effective Dates are invalid, the ending date must be after the starting date.' . $this->effective_to, 'cart66');
    }
    
    // Verify that no other promotions have the same promotion code
    if(empty($errors)) {
      
      $codes = explode(",",$this->code);
      foreach($codes as $code){
        $exists = $this->codeExists($code,$this->id);
        //echo "$code: $exists"; 
        if($exists && $code!=""){
            $errors['code'] = __('The Promotion Code(s) must be unique. The code "'.strtoupper($code).'" is already in use.', 'cart66');         
        }
      }
      
      /*
      $sql = "SELECT count(*) from $this->_tableName where code = %s and id != %d";
      $sql = $this->_db->prepare($sql, $this->code, $this->id);
      $count = $this->_db->get_var($sql);
      if($count > 0) {
        $errors['code'] = __('The Promotion Code(s) must be unique', 'cart66');
      }
      */
    }

    return $errors;
  }
  
  public function codeExists($code,$self=""){
    $exists = false;
    if($self){$self = " and id != '$self'"; }
    $sql = "SELECT * from $this->_tableName where code LIKE %s $self";
    $sql = $this->_db->prepare($sql, "%," . $code . ",%");
    if($data = $this->_db->get_row($sql, ARRAY_A)) {
      $exists = $data['id'];
    }
    return $exists;
  }
  
  public function loadByCode($code) {
    $loaded = false;
    $sql = "SELECT * from $this->_tableName where code LIKE %s";
    $sql = $this->_db->prepare($sql, "%," . $code . ",%");
    if($data = $this->_db->get_row($sql, ARRAY_A)) {
      $this->setData($data);
      $loaded = true;
    }
    //Cart66Session::set('Cart66PromotionCode',$code);
    return $loaded;
  }
  
  /**
   * The following functions validate that the applied promotion is valid
   * Return true if the promotion is valid, otherwise false.
   * 
   * @return boolean
   */
  
  // Check to see if the promotion is enabled
  public function isEnabled() {
    $isEnabled = false;
    if($this->enable == 1) {
      $isEnabled = true;
    }
    return $isEnabled;
  }
  
  // Check to see if the promotion is within the effective dates set
  public function isEffective() {
    $isEffective = false;
    $startPromo = strtotime($this->effective_from);
    $endPromo = strtotime($this->effective_to);
    $date = Cart66Common::localTs();
    if(empty($this->effective_from) || $this->effective_from == "0000-00-00 00:00:00") {
      $startPromo = strtotime("-1 year");
    } 
    if(empty($this->effective_to) || $this->effective_to == "0000-00-00 00:00:00") {
      $endPromo = strtotime("+1 year");
    }
    if($date < $endPromo && $date > $startPromo) {
      $isEffective = true;
    }
    return $isEffective;
  }
  
  // Check to see if the promotion is within the number of redemptions allowed
  public function withinMaximumRedemptions() {
    $withinMaximumRedemptions = false;
    if($this->redemptions < $this->maximum_redemptions || $this->maximum_redemptions == 0) {
      $withinMaximumRedemptions = true;
    }
    return $withinMaximumRedemptions;
  }
  

  
  // Check to see if the products in the cart are eligible for the promotion
  public function eligibleProductsInCart() {
    $eligibleProductsInCart = false;
    $cart66Cart = Cart66Session::get('Cart66Cart');
    $cartProducts = $cart66Cart->getProductsAndIds();
    $products = explode(',', $this->products);
    if(empty($this->products)) {
      $eligibleProductsInCart = true;
    }
    else {
      foreach($cartProducts as $cp) {
        if($this->exclude_from_products == 0) {
          if(in_array($cp, $products)) {
            $eligibleProductsInCart = true;
          }
        }
        elseif($this->exclude_from_products == 1) {
          if(!in_array($cp, $products)) {
            $eligibleProductsInCart = true;
          }
        }
      }
    }
    return $eligibleProductsInCart;
  }
  
  public function isEligibleProduct($productId){
    $eligible = in_array($productId,explode(',', $this->products));
    return $eligible;
  }
  
  public function getEligibleProductsInCart() {
    $eligibleProductsInCart = false;
    $cart66Cart = Cart66Session::get('Cart66Cart');
    $cartProducts = $cart66Cart->getProductsAndIds();
    foreach($products as $p) {
      if($this->isEligibleProduct($p)) {
        $eligibleProductsInCart[] = $p; 
      }
    }
    return $eligibleProductsInCart;
  }
  
  // Check to see if the amount in the cart meets the minimum order set in the promotion
  public function minAmountMet() {
    $minAmountMet = false;
    if($this->min_order <= Cart66Session::get('Cart66Cart')->getNonSubscriptionAmount()) { 
      //_promotion and getNonSubscriptionAmount are cart functions
      $minAmountMet = true;
    }
    return $minAmountMet;
  }
  
  // Check to see if the amount in the cart meets the maximum order set in the promotion
  public function maxAmountMet() {
    $maxAmountMet = false;
    if($this->max_order >= Cart66Session::get('Cart66Cart')->getNonSubscriptionAmount() || $this->max_order == null || $this->max_order == '0.00') { 
      //_promotion and getNonSubscriptionAmount are cart functions
      $maxAmountMet = true;
    }
    return $maxAmountMet;
  }
  
  public function minQuantityMet(){
    $minQuantityMet = false;

    $eligibleProducts = explode(',', $this->products);
    $cartProducts = Cart66Session::get('Cart66Cart')->getItems();

    if(empty($eligibleProducts) || empty($eligibleProducts[0])){
      // no specifc products
      $cartQuantity = 0;
      foreach ($cartProducts as $item) {    
        $cartQuantity = $cartQuantity + $item->getQuantity();
      }
      if($cartQuantity >= $this->min_quantity){
        $minQuantityMet = true;
      }
    }
    else{
      // specific products
      $allItemsHaveQuantityMet = true;
      foreach ($cartProducts as $item) {
        if($this->isEligibleProduct($item->getProductId())){    
          if($item->getQuantity() < $this->min_quantity){
            $allItemsHaveQuantityMet = false;
          }
        }
      }
      
      if($allItemsHaveQuantityMet){
        $minQuantityMet = true;
      }
    }
    return $minQuantityMet;
  }
  
  public function maxQuantityMet(){
    $maxQuantityMet = false;

    $eligibleProducts = explode(',', $this->products);
    $cartProducts = Cart66Session::get('Cart66Cart')->getItems();

    if(empty($eligibleProducts) || empty($eligibleProducts[0])){
      // no specifc products
      $cartQuantity = 0;
      foreach ($cartProducts as $item) {    
        $cartQuantity = $cartQuantity + $item->getQuantity();
      }
      if($cartQuantity <= $this->max_quantity){
        $maxQuantityMet = true;
      }
    }
    else{
      // specific products
      $allItemsHaveQuantityMet = true;
      foreach ($cartProducts as $item) {
        if($this->isEligibleProduct($item->getProductId())){    
          if($item->getQuantity() > $this->max_quantity){
            $allItemsHaveQuantityMet = false;
          }
        }
      }
      
      if($allItemsHaveQuantityMet){
        $maxQuantityMet = true;
      }
    }
    
    if($this->max_quantity == 0){
      $maxQuantityMet = true;
    }
    
    return $maxQuantityMet;
  }
  
  public function discountTotal($total) {
    
      if($this->type == 'dollar') {
        $total = $total - $this->amount;
      }
      elseif($this->type == 'percentage') {
        $total = $total * ((100 - $this->amount)/100);
      }
    
    // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Calculated discount total: $total");
    return $total;
  }
  
  public function getAmount($percentTarget=""){
    if($percentTarget==""){
      $output = $this->amount;
    }
    else{
      
      if($this->type=="percentage"){
        $output = $percentTarget * (($this->amount)/100);
      }
      
      if($this->type=="dollar"){
        $output = $this->amount;
      }
      
    }
    return $output;
  }
 
  
  public function getDiscountAmount($cartObject=null){
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Running getDiscountAmount()");
    $discount = 0; 
    if(!$cartObject){
     $cartObject = Cart66Session::get('Cart66Cart');
    }
 
    if($this->apply_to == "products" && !empty($cartObject)) {
     // coupon applies to products
      $products = explode(',', $this->products);
      $cartItems = $cartObject->getItems();
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] The number of items in the cart: " . count($cartItems));
      $usedThisOrder = 0;
        
      if(empty($this->products)) {
        // all products
          
        // apply coupon to every item in the cart
        $counter = 0;
        foreach($cartItems as $item) {
          $basePrice = $item->getBaseProductPrice();
          $stayPositivePrice =  $this->stayPositive($basePrice,$this->getAmount($basePrice));
          $quantity = $item->getQuantity();
          for($i=1; $i <= $quantity; $i++) {
            if(empty($this->max_uses_per_order)){
              $discount += $stayPositivePrice;
              Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Max uses per order is empty. Discount: $discount");
            }
            elseif($counter < ($this->max_uses_per_order)) {
              $discount+= $stayPositivePrice;
              Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Max uses per order is NOT empty. Discount: $discount :: Stay Positive: $stayPositivePrice");
            }
            $counter++;
          }
        }
      }
      else{
        // coupon applies to specific products
        foreach($cartItems as $item) {
          if($this->exclude_from_products == 0) {
            if(in_array($item->getProductId(), $products)) {
              // add up discount
              $itemQuantity = $item->getQuantity();

              if($this->max_uses_per_order > 0){
                $usesRemaining = $this->max_uses_per_order - $usedThisOrder;
                $allowedQuantity = ($usesRemaining <= $itemQuantity) ? $usesRemaining : $itemQuantity;
              }
              else{
                $allowedQuantity = $itemQuantity;
              }

              $productDiscount = $this->getAmount($item->getBaseProductPrice());
              $discount += $allowedQuantity * $this->stayPositive($item->getBaseProductPrice(),$productDiscount);
            }
          }
          elseif($this->exclude_from_products == 1) {
            if(!in_array($item->getProductId(), $products)) {
              // add up discount
              $itemQuantity = $item->getQuantity();

              if($this->max_uses_per_order > 0){
                $usesRemaining = $this->max_uses_per_order - $usedThisOrder;
                $allowedQuantity = ($usesRemaining <= $itemQuantity) ? $usesRemaining : $itemQuantity;
              }
              else{
                $allowedQuantity = $itemQuantity;
              }

              $productDiscount = $this->getAmount($item->getBaseProductPrice());
              $discount += $allowedQuantity * $this->stayPositive($item->getBaseProductPrice(),$productDiscount);
            }
          }
        }
      }
  
    }
     
    if($this->apply_to == "shipping"){
      $shipping = $cartObject->getShippingCost();
      $discount = (($shipping - $this->getAmount($shipping)) < 0) ? $shipping : $this->getAmount($shipping);
    }
     
    if($this->apply_to == "total"){
      $shipping = $cartObject->getShippingCost();
      $products = $cartObject->getSubTotal();
      $discount = $this->getAmount($shipping + $products);
    }
     
    // format 
    $discount = number_format($discount, 2, '.', '');
     
    return $discount;
  }
  
  public function resetPromotionStatus() {
    $promotion = new Cart66Promotion();
    $this->getPromotion();
    if(is_a($this->_promotion, 'Cart66Promotion')) {
      $cartProducts = $this->getProductsAndIds();
      $products = explode(',', $this->_promotion->products);
      $applyPromotion = false;
      foreach($products as $p) {
        if (in_array($p, $cartProducts)) {
          $applyPromotion = true;
        } elseif(empty($this->_promotion->products)){
          $applyPromotion = true;
        }
      }
      if($applyPromotion == true) {
        $this->_promoStatus = 1;
      } else {
        $this->_promoStatus = -9;
        $this->_promotion = null;
      }
    }
    if(is_a($this->_promotion, 'Cart66Promotion')) {
      if($this->_promotion->minOrder > $this->getSubTotal()) {
        // Order total not high enough for promotion to apply
        $this->_promoStatus = -2;
        $this->_promotion = null;
      }
      else {
        $this->_promoStatus = 1;
      }
    }
  }
  
  public function stayPositive($deductFrom, $discount=null) {
    if($discount==null) { $discount = $this->getAmount(); }
    $amount = (($deductFrom - $discount) < 0) ? $deductFrom : $discount;
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Stay Positive Amount: $amount :: Deduct From: $deductFrom :: Discount: $discount");
    return $amount;
  }
  
  public function getAutoApplyPromotions(){
    $autoPromotions = $this->getModels("WHERE auto_apply='1' ");
    return $autoPromotions;
  }
  
  public function getPromoStatus() {
    return $this->_promoStatus;
  }
  
  
}