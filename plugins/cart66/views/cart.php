<?php 

$items = Cart66Session::get('Cart66Cart')->getItems();
$shippingMethods = Cart66Session::get('Cart66Cart')->getShippingMethods();
$shipping = Cart66Session::get('Cart66Cart')->getShippingCost();
$promotion = Cart66Session::get('Cart66Promotion');
$product = new Cart66Product();
$subtotal = Cart66Session::get('Cart66Cart')->getSubTotal();
$discountAmount = Cart66Session::get('Cart66Cart')->getDiscountAmount();
$cartPage = get_page_by_path('store/cart');
$checkoutPage = get_page_by_path('store/checkout');
$setting = new Cart66Setting();


// Try to return buyers to the last page they were on when the click to continue shopping
if(Cart66Setting::getValue('continue_shopping') == 1){
  // force the last page to be store home
  $lastPage = Cart66Setting::getValue('store_url') ? Cart66Setting::getValue('store_url') : get_bloginfo('url');
  Cart66Session::set('Cart66LastPage', $lastPage);
}
else{
  if(isset($_SERVER['HTTP_REFERER']) && isset($_POST['task']) && $_POST['task'] == "addToCart"){
    $lastPage = $_SERVER['HTTP_REFERER'];
    Cart66Session::set('Cart66LastPage', $lastPage);
  }
  if(!Cart66Session::get('Cart66LastPage')) {
    // If the last page is not set, use the store url
    $lastPage = Cart66Setting::getValue('store_url') ? Cart66Setting::getValue('store_url') : get_bloginfo('url');
    Cart66Session::set('Cart66LastPage', $lastPage);
  }
}

$fullMode = true;
if(isset($data['mode']) && $data['mode'] == 'read') {
  $fullMode = false;
}

$tax = 0;
$taxData = false;
if(isset($data['tax'])){
  $taxData = $data['tax'];
}
if(Cart66Session::get('Cart66Tax')){
  $taxData = Cart66Session::get('Cart66Tax');
}
if($taxData > 0) {
  $tax = $taxData;
}
else {
  // Check to see if all sales are taxed
  $tax = Cart66Session::get('Cart66Cart')->getTax('All Sales');
}

$cartImgPath = Cart66Setting::getValue('cart_images_url');
if($cartImgPath && stripos(strrev($cartImgPath), '/') !== 0) {
  $cartImgPath .= '/';
}
if($cartImgPath) {
  $continueShoppingImg = $cartImgPath . 'continue-shopping.png';
  $updateTotalImg = $cartImgPath . 'update-total.png';
  $calculateShippingImg = $cartImgPath . 'calculate-shipping.png';
  $applyCouponImg = $cartImgPath . 'apply-coupon.png';
}

if(count($items)): ?>

<?php if(Cart66Session::get('Cart66InventoryWarning') && $fullMode): ?>
  <?php 
    echo Cart66Session::get('Cart66InventoryWarning');
    Cart66Session::drop('Cart66InventoryWarning');
  ?>
<?php endif; ?>

<?php if(number_format(Cart66Setting::getValue('minimum_amount'), 2, '.', '') > number_format(Cart66Session::get('Cart66Cart')->getSubTotal(), 2, '.', '') && Cart66Setting::getValue('minimum_cart_amount') == 1): ?>
  <div id="minAmountMessage" class="alert-message alert-error Cart66Unavailable">
    <?php echo (Cart66Setting::getValue('minimum_amount_label')) ? Cart66Setting::getValue('minimum_amount_label') : 'You have not yet reached the required minimum amount in order to checkout.' ?>
  </div>
<?php endif;?>

<?php if(Cart66Session::get('Cart66ZipWarning')): ?>
  <div id="Cart66ZipWarning" class="alert-message alert-error Cart66Unavailable">
    <h2 class="header"><?php _e( 'Please Provide Your Zip Code' , 'cart66' ); ?></h2>
    <p><?php _e( 'Before you can checkout, please provide the zip code for where we will be shipping your order and click' , 'cart66' ); ?> "<?php _e( 'Calculate Shipping' , 'cart66' ); ?>".</p>
    <?php 
      Cart66Session::drop('Cart66ZipWarning');
    ?>
    <input type="button" name="close" value="Ok" id="close" class="Cart66ButtonSecondary modalClose" />
  </div>
<?php elseif(Cart66Session::get('Cart66ShippingWarning')): ?>
  <div id="Cart66ShippingWarning" class="alert-message alert-error Cart66Unavailable">
    <h2 class="header"><?php _e( 'No Shipping Service Selected' , 'cart66' ); ?></h2>
    <p><?php _e( 'We cannot process your order because you have not selected a shipping method. If there are no shipping services available, we may not be able to ship to your location.' , 'cart66' ); ?></p>
    <?php Cart66Session::drop('Cart66ShippingWarning'); ?>
    <input type="button" name="close" value="Ok" id="close" class="Cart66ButtonSecondary modalClose" />
  </div>
<?php elseif(Cart66Session::get('Cart66CustomFieldWarning')): ?>
  <div id="Cart66CustomFieldWarning" class="alert-message alert-error Cart66Unavailable">
    <h2 class="header"><?php _e( 'Custom Field Error' , 'cart66' ); ?></h2>
    <p><?php _e( 'We cannot process your order because you have not filled out the custom field required for these products:' , 'cart66' ); ?></p>
      <ul>
        <?php foreach(Cart66Session::get('Cart66CustomFieldWarning') as $customField): ?>
          <li><?php echo $customField; ?></li>
        <?php endforeach;?>
      </ul>
    <input type="button" name="close" value="Ok" id="close" class="Cart66ButtonSecondary modalClose" />
  </div>
<?php endif; ?>

<?php if(Cart66Session::get('Cart66SubscriptionWarning')): ?>
  <div id="Cart66SubscriptionWarning" class="alert-message alert-error Cart66Unavailable">
    <h2 class="header"><?php _e( 'Too Many Subscriptions' , 'cart66' ); ?></h2>
    <p><?php _e( 'Only one subscription may be purchased at a time.' , 'cart66' ); ?></p>
    <?php 
      Cart66Session::drop('Cart66SubscriptionWarning');
    ?>
    <input type="button" name="close" value="Ok" id="close" class="Cart66ButtonSecondary modalClose" />
  </div>
<?php endif; ?>

<?php 
  if($accountId = Cart66Common::isLoggedIn()) {
    $account = new Cart66Account($accountId);
    if($sub = $account->getCurrentAccountSubscription()) {
      if($sub->isPayPalSubscription() && Cart66Session::get('Cart66Cart')->hasPayPalSubscriptions()) {
        ?>
        <p id="Cart66SubscriptionChangeNote"><?php _e( 'Your current subscription will be canceled when you purchase your new subscription.' , 'cart66' ); ?></p>
        <?php
      }
    }
  } 
?>

<form id='Cart66CartForm' action="" method="post">
  <input type='hidden' name='task' value='updateCart' />
  <table id='viewCartTable'>
    <colgroup>
      <col class="col1" />
      <col class="col2" />
      <col class="col3" />
      <col class="col4" />
    </colgroup>
  <thead>
    <tr>
      <th><?php _e('Product','cart66') ?></th>
      <th class="cart66-align-center"><?php _e( 'Quantity' , 'cart66' ); ?></th>
      <th class="cart66-align-right"><?php _e( 'Item Price' , 'cart66' ); ?></th>
      <th class="cart66-align-right"><?php _e( 'Item Total' , 'cart66' ); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($items as $itemIndex => $item): ?>
      <?php 
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Item option info: " . $item->getOptionInfo());
        $product->load($item->getProductId());
        $price = $item->getProductPrice() * $item->getQuantity();
      ?>
      <tr>
        <td <?php if($item->hasAttachedForms()) { echo "class=\"noBottomBorder\""; } ?> >
          <?php #echo $item->getItemNumber(); ?>
          <?php if($item->getProductUrl() != '' && Cart66Setting::getValue('product_links_in_cart') == 1 && $fullMode): ?>
            <a class="product_url" href="<?php echo $item->getProductUrl(); ?>"><?php echo $item->getFullDisplayName(); ?></a>
          <?php else: ?>
            <?php echo $item->getFullDisplayName(); ?>
          <?php endif; ?>
          <?php echo $item->getCustomField($itemIndex, $fullMode); ?>
          <?php Cart66Session::drop('Cart66CustomFieldWarning'); ?>
        </td>
        <?php if($fullMode): ?>
          <?php
            $removeItemImg = CART66_URL . '/images/remove-item.png';
            if($cartImgPath) {
              $removeItemImg = $cartImgPath . 'remove-item.png';
            }
          ?>
        <td <?php if($item->hasAttachedForms()) { echo "class=\"noBottomBorder\""; } ?>>
          
          <?php if($item->isSubscription() || $item->isMembershipProduct() || $product->is_user_price==1): ?>
            <span class="subscriptionOrMembership"><?php echo $item->getQuantity() ?></span>
          <?php else: ?>
            <input type='text' name='quantity[<?php echo $itemIndex ?>]' value='<?php echo $item->getQuantity() ?>' class="itemQuantity"/>
          <?php endif; ?>
          
          <?php $removeLink = get_permalink($cartPage->ID); ?>
          <?php $taskText = (strpos($removeLink, '?')) ? '&task=removeItem&' : '?task=removeItem&'; ?>
          <a href='<?php echo $removeLink . $taskText ?>itemIndex=<?php echo $itemIndex ?>' title='Remove item from cart'><img src='<?php echo $removeItemImg ?>' alt="Remove Item" /></a>
          
        </td>
        <?php else: ?>
          <td class="cart66-align-center <?php if($item->hasAttachedForms()) { echo "noBottomBorder"; } ?>"><?php echo $item->getQuantity() ?></td>
        <?php endif; ?>
        <td class="cart66-align-right <?php if($item->hasAttachedForms()) { echo "noBottomBorder"; } ?>"><?php echo $item->getProductPriceDescription(); ?></td>
        <td class="cart66-align-right <?php if($item->hasAttachedForms()) { echo "noBottomBorder"; } ?>"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($price, 2);?></td>
      </tr>
      <?php if($item->hasAttachedForms()): ?>
        <tr>
          <td colspan="4">
            <a href='#' class="showEntriesLink" rel="<?php echo 'entriesFor_' . $itemIndex ?>"><?php _e( 'Show Details' , 'cart66' ); ?> <?php #echo count($item->getFormEntryIds()); ?></a>
            <div id="<?php echo 'entriesFor_' . $itemIndex ?>" class="showGfFormData" style="display: none;">
              <?php echo $item->showAttachedForms($fullMode); ?>
            </div>
          </td>
        </tr>
      <?php endif;?>      
    <?php endforeach; ?>
  
    <?php if(Cart66Session::get('Cart66Cart')->requireShipping()): ?>
      
      
      <?php if(CART66_PRO && Cart66Setting::getValue('use_live_rates')): ?>
        <?php $zipStyle = "style=''"; ?>
        
        <?php if($fullMode): ?>
          <?php if(Cart66Session::get('cart66_shipping_zip')): ?>
            <?php $zipStyle = "style='display: none;'"; ?>
            <tr id="shipping_to_row">
              <th colspan="4" class="alignRight">
                <?php _e( 'Shipping to' , 'cart66' ); ?> <?php echo Cart66Session::get('cart66_shipping_zip'); ?> 
                <?php
                  if(Cart66Setting::getValue('international_sales')) {
                    echo Cart66Session::get('cart66_shipping_country_code');
                  }
                ?>
                (<a href="#" id="change_shipping_zip_link">change</a>)
                &nbsp;
                <?php
                  $liveRates = Cart66Session::get('Cart66Cart')->getLiveRates();
                  $rates = $liveRates->getRates();
                  Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] LIVE RATES: " . print_r($rates, true));
                  $selectedRate = $liveRates->getSelected();
                  $shipping = Cart66Session::get('Cart66Cart')->getShippingCost();
                ?>
                <select name="live_rates" id="live_rates">
                  <?php foreach($rates as $rate): ?>
                    <option value='<?php echo $rate->service ?>' <?php if($selectedRate->service == $rate->service) { echo 'selected="selected"'; } ?>>
                      <?php 
                        if($rate->rate !== false) {
                          echo "$rate->service: \$$rate->rate";
                        }
                        else {
                          echo "$rate->service";
                        }
                      ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </th>
            </tr>
          <?php endif; ?>
        
          <tr id="set_shipping_zip_row" <?php echo $zipStyle; ?>>
            <th colspan="4" class="alignRight"><?php _e( 'Enter Your Zip Code' , 'cart66' ); ?>:
              <input type="text" name="shipping_zip" value="" id="shipping_zip" size="5" />
              
              <?php if(Cart66Setting::getValue('international_sales')): ?>
                <select name="shipping_country_code">
                  <?php
                    $customCountries = Cart66Common::getCustomCountries();
                    foreach($customCountries as $code => $name) {
                      echo "<option value='$code'>$name</option>\n";
                    }
                  ?>
                </select>
              <?php else: ?>
                <input type="hidden" name="shipping_country_code" value="<?php echo Cart66Common::getHomeCountryCode(); ?>" id="shipping_country_code">
              <?php endif; ?>
              
              <?php if($cartImgPath && Cart66Common::urlIsLIve($calculateShippingImg)): ?>
                <input class="Cart66CalculateShippingButton" type="image" src='<?php echo $calculateShippingImg ?>' value="<?php _e( 'Calculate Shipping' , 'cart66' ); ?>" name="calculateShipping" />
              <?php else: ?>
                <input type="submit" name="calculateShipping" value="<?php _e('Calculate Shipping', 'cart66'); ?>" id="shipping_submit" class="Cart66CalculateShippingButton Cart66ButtonSecondary" />
              <?php endif; ?>
            </th>
          </tr>
        <?php else:  // Cart in read mode ?>
          <tr>
            <th colspan="4" class='alignRight'>
              <?php
                $liveRates = Cart66Session::get('Cart66Cart')->getLiveRates();
                if($liveRates && Cart66Session::get('cart66_shipping_zip') && Cart66Session::get('cart66_shipping_country_code')) {
                  $selectedRate = $liveRates->getSelected();
                  echo __("Shipping to", "cart66") . " " . Cart66Session::get('cart66_shipping_zip') . " " . __("via","cart66") . " " . $selectedRate->service;
                }
              ?>
            </th>
          </tr>
        <?php endif; // End cart in read mode ?>
        
      <?php  else: ?>
        <?php if(count($shippingMethods) > 1 && $fullMode): ?>
        <tr>
          <th colspan='4' class="alignRight"><?php _e( 'Shipping Method' , 'cart66' ); ?>: &nbsp;
            <select name='shipping_method_id' id='shipping_method_id'>
              <?php foreach($shippingMethods as $name => $id): ?>
              <option value='<?php echo $id ?>' <?php echo ($id == Cart66Session::get('Cart66Cart')->getShippingMethodId())? 'selected' : ''; ?>><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </th>
        </tr>
        <?php elseif(!$fullMode): ?>
        <tr>
          <th colspan='4' class="alignRight"><?php _e( 'Shipping Method' , 'cart66' ); ?>: 
            <?php 
              $method = new Cart66ShippingMethod(Cart66Session::get('Cart66Cart')->getShippingMethodId());
              echo $method->name;
            ?>
          </th>
        </tr>
        <?php endif; ?>
      <?php endif; ?>
    <?php endif; ?>

    <tr class="subtotal">
      <?php if($fullMode): ?>
      <td>&nbsp;</td>
      <td>
        <?php if($cartImgPath && Cart66Common::urlIsLIve($updateTotalImg)): ?>
          <input class="Cart66UpdateTotalButton" type="image" src='<?php echo $updateTotalImg ?>' value="<?php _e( 'Update Total' , 'cart66' ); ?>" name="updateCart"/>
        <?php else: ?>
          <input type='submit' name='updateCart' value='<?php _e( 'Update Total' , 'cart66' ); ?>' class="Cart66UpdateTotalButton Cart66ButtonSecondary" />
        <?php endif; ?>
      </td>
      <?php else: ?>
        <td colspan='2'>&nbsp;</td>
      <?php endif; ?>
      <td class="alignRight strong"><?php _e( 'Subtotal' , 'cart66' ); ?>:</td>
      <td class='strong cart66-align-right'><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($subtotal, 2); ?></td>
    </tr>
    
    <?php if(Cart66Session::get('Cart66Cart')->requireShipping()): ?>
    <tr class="shipping">
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td class="alignRight strong"><?php _e( 'Shipping' , 'cart66' ); ?>:</td>
      <td class="strong cart66-align-right"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo $shipping ?></td>
    </tr>
    <?php endif; ?>
    
    <?php if($promotion): ?>
      <tr class="coupon">
        <td colspan="3" class="alignRight strong"><?php _e( 'Coupon' , 'cart66' ); ?> 
        <?php 
          if($promotion->name){ 
            echo "(" .$promotion->name .")"; 
          }
          else{
            echo "(" . Cart66Session::get('Cart66PromotionCode') . ")";
          }
        ?>:</td>
        <td class="strong cart66-align-right">-&nbsp;<?php echo  CART66_CURRENCY_SYMBOL;
         $promotionDiscountAmount = Cart66Session::get('Cart66Cart')->getDiscountAmount();
         echo number_format($promotionDiscountAmount,2); ?></td>
      </tr>
    <?php endif; ?>
    
    <tr class="tax-row <?php echo $tax > 0 ? 'show-tax-row' : 'hide-tax-row'; ?>">
      <td colspan='2'>&nbsp;</td>
      <?php $taxRate = isset($data['rate']) ? $data['rate'] : Cart66Session::get('Cart66TaxRate'); ?>
      <td class="alignRight strong"><span class="ajax-spin"><img src="<?php echo CART66_URL; ?>/images/ajax-spin.gif" /></span> <?php _e( 'Tax' , 'cart66' ); ?> (<span class="tax-rate"><?php echo $taxRate; ?>%</span>):</td>
      <td class="strong tax-amount cart66-align-right"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($tax, 2); ?></td>
    </tr>
    
      <tr class="total">
        <?php if(Cart66Session::get('Cart66Cart')->getNonSubscriptionAmount() > 0): ?>
        <td class="alignRight" colspan='2'>
          <?php if($fullMode && Cart66Common::activePromotions()): ?>
            <p class="haveCoupon"><?php _e( 'Do you have a coupon?' , 'cart66' ); ?></p>
          <?php if(Cart66Session::get('Cart66PromotionErrors')):
                $promoErrors = Cart66Session::get('Cart66PromotionErrors');
                    foreach($promoErrors as $type=>$error): ?>
                    <p class="promoMessage warning"><?php echo $error; ?></p>
              <?php endforeach;?>
              <?php Cart66Session::get('Cart66Cart')->clearPromotion();
                  endif; ?>
            <div id="couponCode"><input type='text' name='couponCode' value='' /></div>
            <div id="updateCart">
              <?php if($cartImgPath && Cart66Common::urlIsLIve($applyCouponImg)): ?>
                <input class="Cart66ApplyCouponButton" type="image" src='<?php echo $applyCouponImg ?>' value="<?php _e( 'Apply Coupon' , 'cart66' ); ?>" name="updateCart"/>
              <?php else: ?>
                <input type='submit' name='updateCart' value='<?php _e( 'Apply Coupon' , 'cart66' ); ?>' class="Cart66ApplyCouponButton Cart66ButtonSecondary" />
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </td>
        <?php else: ?>
          <td colspan='2'>&nbsp;</td>
        <?php endif; ?>
        <td class="alignRight strong Cart66CartTotalLabel"><span class="ajax-spin"><img src="<?php echo CART66_URL; ?>/images/ajax-spin.gif" /></span> <?php _e( 'Total' , 'cart66' ); ?>:</td>
        <td class="strong grand-total-amount cart66-align-right">
          <?php 
            echo CART66_CURRENCY_SYMBOL;
            echo number_format(Cart66Session::get('Cart66Cart')->getGrandTotal() + $tax, 2);
          ?>
        </td>
      </tr>
      </tbody>
  </table>
</form>

  <?php if($fullMode): ?>
    
  <div id="viewCartNav">
	<div id="continueShopping">
        <?php if($cartImgPath): ?>
          <a href='<?php echo Cart66Session::get('Cart66LastPage'); ?>' class="Cart66CartContinueShopping" ><img src='<?php echo $continueShoppingImg ?>' /></a>
        <?php else: ?>
          <a href='<?php echo Cart66Session::get('Cart66LastPage'); ?>' class="Cart66ButtonSecondary Cart66CartContinueShopping" title="Continue Shopping"><?php _e( 'Continue Shopping' , 'cart66' ); ?></a>
        <?php endif; ?>
	</div>

	
	  <?php	  
  	  // dont show checkout until terms are accepted (if necessary)
  	 if((Cart66Setting::getValue('require_terms') != 1) ||  
  	    (Cart66Setting::getValue('require_terms') == 1 && (isset($_POST['terms_acceptance']) || Cart66Session::get("terms_acceptance")=="accepted")) ) :  
  	    
  	    if(Cart66Setting::getValue('require_terms') == 1){
  	      Cart66Session::set("terms_acceptance","accepted",true);        
  	    }
  	    
  	?>
        <?php
          $checkoutImg = false;
          if($cartImgPath) {
            $checkoutImg = $cartImgPath . 'checkout.png';
          }
        ?>
        <?php
        if(number_format(Cart66Setting::getValue('minimum_amount'), 2, '.', '') > number_format(Cart66Session::get('Cart66Cart')->getSubTotal(), 2, '.', '') && Cart66Setting::getValue('minimum_cart_amount') == 1): ?>
        <?php else: ?>
      <div id="checkoutShopping">
        <?php if($checkoutImg): ?>
          <a id="Cart66CheckoutButton" href='<?php echo get_permalink($checkoutPage->ID) ?>'><img src='<?php echo $checkoutImg ?>' /></a>
        <?php else: ?>
          <a id="Cart66CheckoutButton" href='<?php echo get_permalink($checkoutPage->ID) ?>' class="Cart66ButtonPrimary" title="Continue to Checkout"><?php _e( 'Checkout' , 'cart66' ); ?></a>
        <?php endif; ?>
    	</div>
    	<?php endif; ?>
    <?php else: ?>
    <div id="Cart66CheckoutReplacementText">
        <?php echo Cart66Setting::getValue('cart_terms_replacement_text');  ?>
    </div>
    <?php endif; ?>
	
	
	   <?php  

    	if(CART66_PRO && Cart66Setting::getValue('require_terms') == 1 && (!isset($_POST['terms_acceptance']) && Cart66Session::get("terms_acceptance")!="accepted") ){
    	    echo Cart66Common::getView("pro/views/terms.php",array("location"=>"Cart66CartTOS"));
    	} 

    	 ?>
	
	</div>
	
	
  <?php endif; ?>
<?php else: ?>
  <div id="emptyCartMsg">
  <h3><?php _e('Your Cart Is Empty','cart66'); ?></h3>
  <?php if($cartImgPath): ?>
    <p><a href='<?php echo Cart66Session::get('Cart66LastPage'); ?>' title="Continue Shopping" class="Cart66CartContinueShopping"><img alt="Continue Shopping" class="continueShoppingImg" src='<?php echo $continueShoppingImg ?>' /></a>
  <?php else: ?>
    <p><a href='<?php echo Cart66Session::get('Cart66LastPage'); ?>' class="Cart66ButtonSecondary" title="Continue Shopping"><?php _e( 'Continue Shopping' , 'cart66' ); ?></a>
  <?php endif; ?>
  </div>
  <?php
    if($promotion){
      Cart66Session::get('Cart66Cart')->clearPromotion();
    }
    Cart66Session::drop("terms_acceptance");
  ?>
<?php endif; ?>
<script type="text/javascript">
/* <![CDATA[ */
  (function($){
    $(document).ready(function(){
      $('#shipping_method_id').change(function() {
        $('#Cart66CartForm').submit();
      });

      $('#live_rates').change(function() {
        $('#Cart66CartForm').submit();
      });

      $('.showEntriesLink').click(function() {
        var panel = $(this).attr('rel');
        $('#' + panel).toggle();
        return false;
      });

      $('#change_shipping_zip_link').click(function() {
        $('#set_shipping_zip_row').toggle();
        return false;
      });
    })
  })(jQuery);
/* ]]> */
</script>
