<?php
if($accountId = Cart66Common::isLoggedIn()) {
  $account = new Cart66Account($accountId);
  $name = $account->firstName . '&nbsp;' . $account->lastName;
  echo "<h3>You Are Logged In As $name</h3>";
  $logout = Cart66Common::appendQueryString('cart66-task=logout');
  echo "<p>If you are not $name <a href='$logout'>Log out</a></p>";
  
  if(empty($b['firstName'])) {
    $b['firstName'] = $account->billingFirstName;
    $b['lastName'] = $account->billingLastName;
  }
  
  if(empty($p['email'])) {
    $p['email'] = $account->email;
  }
}
?>

<form class="phorm2" action="" method='post'>
  <input type='hidden' name='cart66-gateway-name' value='<?php echo $gatewayName ?>'>
  <table>
    <tr>
      <td valign='top' style="">
        <ul id="billingAddress" class="shortLabels" style="width: 275px;">
          <?php if($gatewayName == 'Cart66ManualGateway'): ?>
            <li><h2>Shipping Address</h2></li>
          <?php else: ?>
            <li><h2>Billing Address</h2></li>
          <?php endif; ?>

          <li>
            <label>First name:</label>
            <input type="text" id="billing-firstName" name="billing[firstName]" value="<?php Cart66Common::showValue($b['firstName']); ?>">
          </li>

          <li>
            <label>Last name:</label>
            <input type="text" id="billing-lastName" name="billing[lastName]" value="<?php Cart66Common::showValue($b['lastName']); ?>">
          </li>

          <li>
            <label>Address:</label>
            <input type="text" id="billing-address" name="billing[address]" value="<?php Cart66Common::showValue($b['address']); ?>">
          </li>

          <li>
            <label>&nbsp;</label>
            <input type="text" id="billing-address2" name="billing[address2]" value="<?php Cart66Common::showValue($b['address2']); ?>">
          </li>

          <li>
            <label>City:</label>
            <input type="text" id="billing-city" name="billing[city]" value="<?php Cart66Common::showValue($b['city']); ?>">
          </li>

          <li><label class="short">State:</label>
            <input type="text" name="billing[state-text]" value="<?php Cart66Common::showValue($b['state']); ?>" id="billing-state-text" />
            <select style="min-width: 125px;" id="billing-state" class="required" title="State billing address" name="billing[state]"></select>
          </li>

          <li>
            <label>Zip code:</label>
            <input type="text" id="billing-zip" name="billing[zip]" value="<?php Cart66Common::showValue($b['zip']); ?>">
          </li>

          <li>
            <label class="short">Country:</label>
            <select title="country" id="billing-country" name="billing[country]">
              <?php foreach(Cart66Common::getCountries() as $code => $name): ?>
              <option value="<?php echo $code ?>"><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
        </ul>
    
    <?php if($_SESSION['Cart66Cart']->requireShipping() && $gatewayName != 'Cart66ManualGateway'): ?>
        <ul>
          <li><h2>Shipping Address</h2></li>
    
          <li>
            <label style='width: auto;'>Same as billing address:</label>
            <input type='checkbox' id='sameAsBilling' name='sameAsBilling' value='1' style='width: auto;'>
          </li>
        </ul>

        <ul id="shippingAddress" class="shortLabels" style="width: 275px; display: none;">

          <li>
            <label>First name:</label>
            <input type="text" id="shipping-firstName" name="shipping[firstName]" value="<?php Cart66Common::showValue($s['firstName']); ?>">
          </li>

          <li>
            <label>Last name:</label>
            <input type="text" id="shipping-lastName" name="shipping[lastName]" value="<?php Cart66Common::showValue($s['lastName']); ?>">
          </li>

          <li>
            <label>Address:</label>
            <input type="text" id="shipping-address" name="shipping[address]" value="<?php Cart66Common::showValue($s['address']); ?>">
          </li>

          <li>
            <label>&nbsp;</label>
            <input type="text" id="shipping-address2" name="shipping[address2]" value="<?php Cart66Common::showValue($s['address2']); ?>">
          </li>

          <li>
            <label>City:</label>
            <input type="text" id="shipping-city" name="shipping[city]" value="<?php Cart66Common::showValue($s['city']); ?>">
          </li>

          <li>
            <label class="short">State:</label>
            <input type="text" name="shipping[state-text]" value="<?php Cart66Common::showValue($s['state']); ?>" id="shipping-state-text" />
            <select style="min-width: 125px;" id="shipping-state" class="required" title="State shipping address" name="shipping[state]"></select>
          </li>

          <li>
            <label>Zip code:</label>
            <input type="text" id="shipping-zip" name="shipping[zip]" value="<?php Cart66Common::showValue($s['zip']); ?>">
          </li>

          <li>
            <label class="short">Country:</label>
            <select title="country" id="shipping-country" name="shipping[country]">
              <?php foreach(Cart66Common::getCountries() as $code => $name): ?>
              <option value="<?php echo $code ?>"><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
        </ul>
      
    <?php else: ?>
      <input type='hidden' id='sameAsBilling' name='sameAsBilling' value='1' />
    <?php endif; ?>
  
      </td>
      <td valign='top'>
  
        <ul class="shortLabels">
          <?php if($gatewayName == 'Cart66ManualGateway'): ?>
            <li><h2>Contact Information</h2></li>
          <?php else: ?>
            <li><h2>Payment Information</h2></li>
          <?php endif; ?>
        
          <?php if($gatewayName != 'Cart66ManualGateway'): ?>
          <li>
            <label>Card Type:</label>
            <select id="payment-cardType" name="payment[cardType]">
              <?php foreach($data['gateway']->getCreditCardTypes() as $name => $value): ?>
                <option value="<?php echo $value ?>"><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
        
          <li>
            <label>Card&nbsp;Number:</label>
            <input type="text" id="payment-cardNumber" name="payment[cardNumber]" value="<?php Cart66Common::showValue($p['cardNumber']); ?>">
          </li>
        
          <li>
            <label>Expiration:</label>
            <select id="payment-cardExpirationMonth" name="payment[cardExpirationMonth]">
              <option value=''></option>
              <?php 
                for($i=1; $i<=12; $i++){
                  $val = $i;
                  if(strlen($val) == 1) {
                    $val = '0' . $i;
                  }
                  echo "<option value='$val'>$val</option>\n";
                } 
              ?>
            </select>
          
          /
            <select id="payment-cardExpirationYear" name="payment[cardExpirationYear]" style="margin:0;">
              <option value=''></option>
              <?php
                $year = date('Y');
                for($i=$year; $i<=$year+12; $i++){
                  echo "<option value='$i'>$i</option>\n";
                } 
              ?>
            </select>
          
          </li>
          
          <li>
            <label>Security ID:</label>
            <input type="text" id="payment-securityId" name="payment[securityId]" style="width: 30px;" value="<?php Cart66Common::showValue($p['securityId']); ?>">
            <p class="description">Security code on back of card</p>
          </li>

          <?php endif; ?>
          <li>
            <label>Phone:</label>
            <input type="text" id="payment-phone" name="payment[phone]" value="<?php Cart66Common::showValue($p['phone']); ?>">
          </li>
          
          <li>
            <label>Email:</label>
            <input type="text" id="payment-email" name="payment[email]" value="<?php Cart66Common::showValue($p['email']); ?>">
          </li>
          
          <?php if($lists = Cart66Setting::getValue('constantcontact_list_ids')): ?>
            <li>
              <?php
                if(!$optInMessage = Cart66Setting::getValue('opt_in_message')) {
                  $optInMessage = 'Yes, I would like to subscribe to:';
                }
                echo "<p>$optInMessage</p>";
                $lists = explode('~', $lists);
                echo '<ul id="Cart66NewsletterList">';
                foreach($lists as $list) {
                  list($id, $name) = explode('::', $list);
                  echo "<li><input class=\"Cart66CheckboxList\" type=\"checkbox\" name=\"constantcontact_subscribe_ids[]\" value=\"$id\" /> $name</li>";
                }
                echo '</ul>';
              ?>
            </li>
          <?php endif; ?>
          
          <?php if(!Cart66Common::isLoggedIn()): ?>

            <?php if($_SESSION['Cart66Cart']->hasSubscriptionProducts()): ?>
              <li><label>Password:</label>
              <input type="password" id="payment-password" name="payment[password]" value="<?php Cart66Common::showValue($p['password']); ?>">
              </li>

              <li><label>&nbsp;</label>
              <input type="password" id="payment-password2" name="payment[password2]" value="<?php Cart66Common::showValue($p['password2']); ?>">
              <p class="description">Enter your password again</p>
              </li>
            <?php endif; ?>
          <?php endif; ?>

          <li>&nbsp;</li>

          <li>
            <label>&nbsp;</label>
            <?php
              $cartImgPath = Cart66Setting::getValue('cart_images_url');
              if($cartImgPath) {
                if(strpos(strrev($cartImgPath), '/') !== 0) {
                  $cartImgPath .= '/';
                }
                $completeImgPath = $cartImgPath . 'complete-order.png';
              }
            ?>
            <?php if($cartImgPath): ?>
              <input id="Cart66CheckoutButton" type="image" src='<?php echo $completeImgPath ?>' value="Complete Order" />
            <?php else: ?>
              <input id="Cart66CheckoutButton" class="Cart66ButtonPrimary" type="submit"  value="Complete Order" />
            <?php endif; ?>

            <p class="description" style="color: #757575;">Your receipt will be on the next page and also immediately emailed to you.
            <strong>We respect your&nbsp;privacy!</strong></p>
          </li>
        </ul>
              
      </td>
    </tr>
</table>

</form>