<?php 
$_SESSION['Cart66Cart']->resetPromotionStatus();
$items = $_SESSION['Cart66Cart']->getItems();
$shippingMethods = $_SESSION['Cart66Cart']->getShippingMethods();
$shipping = $_SESSION['Cart66Cart']->getShippingCost();
$promotion = $_SESSION['Cart66Cart']->getPromotion();
$product = new Cart66Product();
$subtotal = $_SESSION['Cart66Cart']->getSubTotal();
$discountAmount = $_SESSION['Cart66Cart']->getDiscountAmount();
$cartPage = get_page_by_path('store/cart');
$checkoutPage = get_page_by_path('store/checkout');
$setting = new Cart66Setting();

// Try to return buyers to the last page they were on when the click to continue shopping

if(empty($_SESSION['Cart66LastPage'])) {
  // If the last page is not set, use the store url
  $_SESSION['Cart66LastPage'] = Cart66Setting::getValue('store_url') ? Cart66Setting::getValue('store_url') : get_bloginfo('url');
}

$fullMode = true;
if(isset($data['mode']) && $data['mode'] == 'read') {
  $fullMode = false;
}

$tax = 0;
if(isset($data['tax']) && $data['tax'] > 0) {
  $tax = $data['tax'];
}
else {
  // Check to see if all sales are taxed
  $tax = $_SESSION['Cart66Cart']->getTax('All Sales');
}

$cartImgPath = Cart66Setting::getValue('cart_images_url');
if($cartImgPath && stripos(strrev($cartImgPath), '/') !== 0) {
  $cartImgPath .= '/';
}
if($cartImgPath) {
  $continueShoppingImg = $cartImgPath . 'continue-shopping.png';
}

if(count($items)): ?>

<?php if(!empty($_SESSION['Cart66InventoryWarning']) && $fullMode): ?>
  <div class="Cart66Unavailable">
    <h1>Inventory Restriction</h1>
    <?php 
      echo $_SESSION['Cart66InventoryWarning'];
      unset($_SESSION['Cart66InventoryWarning']);
    ?>
    <input type="button" name="close" value="Ok" id="close" class="Cart66ButtonSecondary modalClose" />
  </div>
<?php endif; ?>


<?php if(isset($_SESSION['Cart66ZipWarning'])): ?>
  <div id="Cart66ZipWarning" class="Cart66Unavailable">
    <h2>Please Provide Your Zip Code</h2>
    <p>Before you can checkout, please provide the zip code for where we will be shipping your order.</p>
    <?php 
      unset($_SESSION['Cart66ZipWarning']);
    ?>
    <input type="button" name="close" value="Ok" id="close" class="Cart66ButtonSecondary modalClose" />
  </div>
<?php elseif(isset($_SESSION['Cart66ShippingWarning'])): ?>
  <div id="Cart66ShippingWarning" class="Cart66Unavailable">
    <h2>No Shipping Option Selected</h2>
    <p>We cannot process your order because you have not selected a shipping method.</p>
    <?php 
      unset($_SESSION['Cart66ShippingWarning']);
    ?>
    <input type="button" name="close" value="Ok" id="close" class="Cart66ButtonSecondary modalClose" />
  </div>
<?php endif; ?>

<?php if(isset($_SESSION['Cart66SubscriptionWarning'])): ?>
  <div id="Cart66SubscriptionWarning" class="Cart66Unavailable">
    <h2>Too Many Subscriptions</h2>
    <p>Only one subscription may be purchased at a time.</p>
    <?php 
      unset($_SESSION['Cart66SubscriptionWarning']);
    ?>
    <input type="button" name="close" value="Ok" id="close" class="Cart66ButtonSecondary modalClose" />
  </div>
<?php endif; ?>


<?php 
  if($accountId = Cart66Common::isLoggedIn()) {
    $account = new Cart66Account($accountId);
    if($sub = $account->getCurrentAccountSubscription()) {
      if($sub->isPayPalSubscription()) {
        ?>
        <p id="Cart66SubscriptionChangeNote">Your current subscription will be canceled when you purchase your new subscription.</p>
        <?php
      }
    }
  } 
?>


<form id='Cart66CartForm' action="" method="post">
  <input type='hidden' name='task' value='updateCart'>
  <table id='viewCartTable' cellspacing="0" cellpadding="0" border="0" style="">
    <tr>
      <th style='text-align: left;'>Product</th>
      <th style='text-align: left;' colspan="1">Quantity</th>
      <th>&nbsp;</th>
      <th style='text-align: left;'>Item&nbsp;Price</th>
      <th style='text-align: left;'>Item&nbsp;Total</th>
    </tr>
  
    <?php foreach($items as $itemIndex => $item): ?>
      <?php 
        $product->load($item->getProductId());
        $price = $item->getProductPrice() * $item->getQuantity();
      ?>
      <tr>
        <td <?php if($item->hasAttachedForms()) { echo "style='border-bottom: none;'"; } ?> >
          <?php #echo $item->getItemNumber(); ?>
          <?php echo $item->getFullDisplayName(); ?>
          <?php echo $item->getCustomField($itemIndex, $fullMode); ?>
        </td>
        <?php if($fullMode): ?>
          <?php
            $removeItemImg = WPCURL . '/plugins/cart66/images/remove-item.png';
            if($cartImgPath) {
              $removeItemImg = $cartImgPath . 'remove-item.png';
            }
          ?>
        <td style='text-align: left; <?php if($item->hasAttachedForms()) { echo " border-bottom: none;"; } ?>' colspan="2">
          
          <?php if($item->isSubscription()): ?>
            <span style="padding: 0px 1px 0px 10px; display: inline-block; width: 35px; background-color: transparent;"><?php echo $item->getQuantity() ?></span>
          <?php else: ?>
            <input type='text' name='quantity[<?php echo $itemIndex ?>]' value='<?php echo $item->getQuantity() ?>' style='width: 35px; margin-left: 5px;'/>
          <?php endif; ?>
          
          <?php $removeLink = get_permalink($cartPage->ID); ?>
          <?php $taskText = (strpos($removeLink, '?')) ? '&task=removeItem&' : '?task=removeItem&'; ?>
          <a href='<?php echo $removeLink . $taskText ?>itemIndex=<?php echo $itemIndex ?>' title='Remove item from cart'><img src='<?php echo $removeItemImg ?>' /></a>
          
        </td>
        <?php else: ?>
          <td style='text-align: left; <?php if($item->hasAttachedForms()) { echo " border-bottom: none;"; } ?>' colspan="2"><?php echo $item->getQuantity() ?></td>
        <?php endif; ?>
        <td <?php if($item->hasAttachedForms()) { echo "style='border-bottom: none;'"; } ?>><?php echo $item->getProductPriceDescription(); ?></td>
        <td <?php if($item->hasAttachedForms()) { echo "style='border-bottom: none;'"; } ?>><?php echo CURRENCY_SYMBOL ?><?php echo number_format($price, 2) ?></td>
      </tr>
      <?php if($item->hasAttachedForms()): ?>
        <tr>
          <td colspan="5">
            <a href='#' class="showEntriesLink" rel="<?php echo 'entriesFor_' . $itemIndex ?>">Show Details <?php #echo count($item->getFormEntryIds()); ?></a>
            <div id="<?php echo 'entriesFor_' . $itemIndex ?>" class="showGfFormData" style="display: none;">
              <?php echo $item->showAttachedForms($fullMode); ?>
            </div>
          </td>
        </tr>
      <?php endif;?>
    <?php endforeach; ?>
  
    <?php if($_SESSION['Cart66Cart']->requireShipping()): ?>
      
      
      <?php if(Cart66Setting::getValue('use_live_rates')): ?>
        <?php $zipStyle = "style=''"; ?>
        
        <?php if($fullMode): ?>
          <?php if(!empty($_SESSION['cart66_shipping_zip'])): ?>
            <?php $zipStyle = "style='display: none;'"; ?>
            <tr>
              <th colspan="5" align="right">
                Shipping to <?php echo $_SESSION['cart66_shipping_zip']; ?> 
                <?php
                  if(Cart66Setting::getValue('international_sales')) {
                    echo $_SESSION['cart66_shipping_country_code'];
                  }
                ?>
                (<a href="#" id="change_shipping_zip_link">change</a>)
                &nbsp;
                <?php
                  $liveRates = $_SESSION['Cart66Cart']->getUpsRates();
                  $rates = $liveRates->getRates();
                  $selectedRate = $liveRates->getSelected();
                  $shipping = $_SESSION['Cart66Cart']->getShippingCost();
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
            <th colspan="5" align="right">Enter Your Zip Code:
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
                <?php
                  $homeCountry = Cart66Setting::getValue('home_country');
                  if($homeCountry) {
                    list($homeCountryCode, $homeCountryName) = explode('~', $homeCountry);
                  }
                  else {
                    $homeCountryCode = 'US'; // Default to US if the home country code cannot be determined
                  }
                ?>
                <input type="hidden" name="shipping_country_code" value="<?php echo $homeCountryCode ?>" id="shipping_country_code">
              <?php endif; ?>
              
              <input type="submit" name="updateCart" value="Calculate Shipping" id="shipping_submit" class="Cart66ButtonSecondary" />
            </th>
          </tr>
        <?php else:  // Cart in read mode ?>
          <tr>
            <th colspan="5" align='right'>
              <?php
                $liveRates = $_SESSION['Cart66Cart']->getUpsRates();
                if($liveRates && !empty($_SESSION['cart66_shipping_zip']) && !empty($_SESSION['cart66_shipping_country_code'])) {
                  $selectedRate = $liveRates->getSelected();
                  echo "Shipping to " . $_SESSION['cart66_shipping_zip'] . " via " . $selectedRate->service;
                }
                else {
                  $cartPage = get_page_by_path('store/cart');
                  $link = get_permalink($cartPage->ID);
                  
                  if(empty($_SESSION['cart66_shipping_zip'])) {
                    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Live rate warning: No shipping zip in session");
                    $_SESSION['Cart66ZipWarning'] = true;
                  }
                  elseif(empty($_SESSION['cart66_shipping_country_code'])) {
                    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Live rate warning: No shipping country code in session");
                    $_SESSION['Cart66ShippingWarning'] = true;
                  }
                  
                  wp_redirect($link);
                  exit();
                }
              ?>
            </th>
          </tr>
        <?php endif; // End cart in read mode ?>
        
      <?php  else: ?>
        <?php if(count($shippingMethods) > 1 && $fullMode): ?>
        <tr>
          <th colspan='5' align="right">Shipping Method: &nbsp;
            <select name='shipping_method_id' id='shipping_method_id'>
              <?php foreach($shippingMethods as $name => $id): ?>
              <option value='<?php echo $id ?>' 
               <?php echo ($id == $_SESSION['Cart66Cart']->getShippingMethodId())? 'selected' : ''; ?>><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </th>
        </tr>
        <?php elseif(!$fullMode): ?>
        <tr>
          <th colspan='2' align="right">Shipping Method:</th>
          <th colspan='3' align="left">
            <?php 
              $method = new Cart66ShippingMethod($_SESSION['Cart66Cart']->getShippingMethodId());
              echo $method->name;
            ?>
          </th>
        </tr>
        <?php endif; ?>
      <?php endif; ?>
    <?php endif; ?>

    <tr>
      <?php if($fullMode): ?>
      <td class='noBorder'>&nbsp;</td>
      <td class='noBorder' colspan='2' style='text-align: left;'>
        <input type='submit' name='updateCart' value='Update Total' class="Cart66ButtonSecondary" />
      </td>
      <?php else: ?>
        <td class='noBorder' colspan='3'>&nbsp;</td>
      <?php endif; ?>
      <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;'>Subtotal:</td>
      <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;"><?php echo CURRENCY_SYMBOL ?><?php echo number_format($subtotal, 2); ?></td>
    </tr>
    
    <?php if($_SESSION['Cart66Cart']->requireShipping()): ?>
    <tr>
      <td class='noBorder' colspan='1'>&nbsp;</td>
      <td class='noBorder' colspan="2" style='text-align: center;'>&nbsp;</td>
      <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;'>Shipping:</td>
      <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;"><?php echo CURRENCY_SYMBOL ?><?php echo $shipping ?></td>
    </tr>
    <?php endif; ?>
    
    <?php if($promotion): ?>
      <tr>
        <td class='noBorder' colspan='2'>&nbsp;</td>
        <td class='noBorder' colspan="2" style='text-align: right; font-weight: bold;'>Coupon:</td>
        <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;"><?php echo $promotion->getAmountDescription(); ?></td>
      </tr>
    <?php endif; ?>
    
    
    <?php if($tax > 0): ?>
      <tr>
        <td class='noBorder' colspan='3'>&nbsp;</td>
        <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;'>Tax:</td>
        <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;"><?php echo CURRENCY_SYMBOL ?><?php echo number_format($tax, 2); ?></td>
      </tr>
    <?php endif; ?>
    
      <tr>
        <?php if($_SESSION['Cart66Cart']->getNonSubscriptionAmount() > 0): ?>
        <td class='noBorder' style="text-align: right;" colspan='1'>
          <?php if($fullMode && Cart66Common::activePromotions()): ?>
            Do you have a coupon? &nbsp;
            <input type='text' name='couponCode' value='' size="12" />
            <?php if($_SESSION['Cart66Cart']->getPromoStatus() < 0): ?>
              <div style='color: red;'><br/><?php echo $_SESSION['Cart66Cart']->getPromoMessage(); ?></div>
            <?php endif; ?>
          <?php endif; ?>&nbsp;
        </td>
        <td class='noBorder' colspan="2" valign="top">
          <?php if($fullMode && Cart66Common::activePromotions()): ?>
            <input type='submit' name='updateCart' value='Apply Coupon' class="Cart66ButtonSecondary" />
          <?php endif; ?>&nbsp;
        </td>
        <?php else: ?>
          <td class='noBorder' colspan='3'>&nbsp;</td>
        <?php endif; ?>
        <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;' valign="top">Total:</td>
        <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;" valign="top">
          <?php 
            echo CURRENCY_SYMBOL;
            echo number_format($_SESSION['Cart66Cart']->getGrandTotal() + $tax, 2);
          ?>
        </td>
      </tr>
  
  </table>
</form>

  <?php if($fullMode): ?>
  <table id="viewCartTableNav">
    <tr>
      <td style='text-align: left; vertical-align: top;'>
        <?php if($cartImgPath): ?>
          <a href='<?php echo $_SESSION['Cart66LastPage']; ?>' class="Cart66CartContinueShopping" ><img src='<?php echo $continueShoppingImg ?>' /></a>
        <?php else: ?>
          <a href='<?php echo $_SESSION['Cart66LastPage']; ?>' class="Cart66ButtonSecondary Cart66CartContinueShopping">Continue Shopping</a>
        <?php endif; ?>
      </td>
      <td style='text-align: right; vertical-align: top;'>
        <?php
          $checkoutImg = false;
          if($cartImgPath) {
            $checkoutImg = $cartImgPath . 'checkout.png';
          }
        ?>

        <?php if($checkoutImg): ?>
          <a id="Cart66CheckoutButton" href='<?php echo get_permalink($checkoutPage->ID) ?>'><img src='<?php echo $checkoutImg ?>' /></a>
        <?php else: ?>
          <a id="Cart66CheckoutButton" href='<?php echo get_permalink($checkoutPage->ID) ?>' class="Cart66ButtonPrimary">Checkout</a>
        <?php endif; ?>
      </td>
    </tr>
  </table>
  <?php endif; ?>
<?php else: ?>
  <center>
  <h3>Your Cart Is Empty</h3>
  <?php if($cartImgPath): ?>
    <p><a href='<?php echo $_SESSION['Cart66LastPage']; ?>'><img style="border: 0px;" src='<?php echo $continueShoppingImg ?>' /></a>
  <?php else: ?>
    <p><a href='<?php echo $_SESSION['Cart66LastPage']; ?>' class="Cart66ButtonSecondary">Continue Shopping</a>
  <?php endif; ?>
  </center>
  <?php
    $_SESSION['Cart66Cart']->clearPromotion();
  ?>
<?php endif; ?>

<script type="text/javascript" charset="utf-8">
//<![CDATA[
  $jq = jQuery.noConflict();

  $jq('document').ready(function() {
    $jq('#shipping_method_id').change(function() {
      $jq('#Cart66CartForm').submit();
    });
    
    $jq('#live_rates').change(function() {
      $jq('#Cart66CartForm').submit();
    });
    
    $jq('.showEntriesLink').click(function() {
      var panel = $jq(this).attr('rel');
      $jq('#' + panel).toggle();
      return false;
    });
    
    $jq('#change_shipping_zip_link').click(function() {
      $jq('#set_shipping_zip_row').toggle();
      return false;
    });
  });
  
//]]>  
</script>
