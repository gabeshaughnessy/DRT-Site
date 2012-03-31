<?php
$rate = new Cart66TaxRate();
$setting = new Cart66Setting();
$successMessage = '';
$versionInfo = false;

if($_SERVER['REQUEST_METHOD'] == "POST") {
  if($_POST['cart66-action'] == 'save rate') {
    $data = $_POST['tax'];
    if(isset($data['zip']) && !empty($data['zip'])) {
      list($low, $high) = explode('-', $data['zip']);
      
      if(isset($low)) {
        $low = trim($low);
      }
      
      if(isset($high)) {
        $high = trim($high);
      }
      else { $high = $low; }
      
      if(is_numeric($low) && is_numeric($high)) {
        if($low > $high) {
          $x = $high;
          $high = $low;
          $low = $x;
        }
        $data['zip_low'] = $low;
        $data['zip_high'] = $high;
      }
      
    }
    $rate->setData($data);
    $rate->save();
    $rate->clear();
    $successMessage = "Tax rate saved";
  }
  elseif($_POST['cart66-action'] == 'saveOrderNumber' && CART66_PRO) {
    $orderNumber = trim(Cart66Common::postVal('order_number'));
    Cart66Setting::setValue('order_number', $orderNumber);
    $versionInfo = Cart66ProCommon::getVersionInfo();
    if($versionInfo) {
      $successMessage = "Thank you! Cart66 has been activated.";
    }
    else {
      Cart66Setting::setValue('order_number', '');
      $orderNumberFailed = true;
    }
  }
} 
elseif(isset($_GET['task']) && $_GET['task'] == 'deleteTax' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $rate->load($id);
  $rate->deleteMe();
  $rate->clear();
}

$cardTypes = Cart66Setting::getValue('auth_card_types');
if($cardTypes) {
  $cardTypes = explode('~', $cardTypes);
}
else {
  $cardTypes = array();
}

?>

<?php if(!empty($successMessage)): ?>
  
<script type='text/javascript'>
  var $j = jQuery.noConflict();

  $j(document).ready(function() {
    setTimeout("$j('#Cart66SuccessBox').hide('slow')", 2000);
  });
  
  <?php if($versionInfo): ?>
    setTimeout("$j('.unregistered').hide('slow')", 1000);
  <?php  endif; ?>
</script>
  
<div class='Cart66SuccessModal' id="Cart66SuccessBox" style=''>
  <p><strong>Success</strong><br/>
  <?php echo $successMessage ?></p>
</div>


<?php endif; ?>

<!-- Example Code Block -->
<!--
<div id="widgets-left">
  <div id="available-widgets">
    
    <div class="widgets-holder-wrap">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Example Setting <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">This is a test</p>
        <div>
          <p>This is the content area</p>
        </div>
      </div>
    </div>
    
  </div>
</div>
-->

<h2>Cart66 Settings</h2>

<div id="saveResult"></div>

<div id="widgets-left" style="margin-right: 50px;">
  <div id="available-widgets">

    <?php if(CART66_PRO && CART66_ORDER_NUMBER == false): ?>    
    <!-- Order Number -->
    <div class="widgets-holder-wrap">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Order Number <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">Please enter your Cart66 order number to get automatic upgrades and support.<br/>
          If you do not have an order number please <a href="http://www.Cart66.com">buy a license</a>.</p>
        <div>
          
          <form id="orderNumberActivation" method="post">
            <input type="hidden" name="cart66-action" value="saveOrderNumber" id="saveOrderNumber">
            <ul>
              <li>
                <label style="display: inline-block; width: 120px; text-align: right;"  for='order_number'>Order Number:</label>
                <input type='text' name='order_number' id='orderNumber' style='width: 375px;' 
                  value="<?php echo Cart66Setting::getValue('order_number'); ?>" />
              </li>
              
              <li>
                <label style="display: inline-block; width: 120px; text-align: right;" >&nbsp;</label>
                <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" />
                <?php if(!empty($orderNumberFailed)): ?>
                  <span style="color: red;">Invalid Order Number</span>
                <?php endif; ?>
              </li>
            </ul>
          </form>
          
        </div>
      </div>
    </div>
    <?php endif; ?>
    
    <!-- Main Settings -->
    <div class="widgets-holder-wrap">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Main Settings <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <div>
          <form id="orderNumberForm" class="ajaxSettingForm" action="" method='post'>
            <input type='hidden' name='action' value="save_settings" />
            <input type='hidden' name='_success' value="Your main settings have been saved.">
            <ul>
              
              <li>
                <label style="display: inline-block; width: 120px; text-align: right;" for='paypalpro_email'>Hide system pages:</label>
                <input type='radio' name='hide_system_pages' id='hide_system_pages' value="1" 
                  <?php echo Cart66Setting::getValue('hide_system_pages') == '1' ? 'checked="checked"' : '' ?>/> Yes
                <input type='radio' name='hide_system_pages' id='hide_system_pages' value="" 
                  <?php echo Cart66Setting::getValue('hide_system_pages') != '1'? 'checked="checked"' : '' ?>/> No
                <p class="label_desc" style="width: 450px;">Hiding system pages will hide all the pages that Cart66 installs 
                  from your site's navigation. Express, IPN, and Receipt will always be hidden. Selecting 'Yes' will also hide
                  Store, Cart, and Checkout which you may want to have your customers access through the Cart66 Shopping Cart widget rather than your
                  site's main navigation.</p>
              </li>
              <li>
                <label style="display: inline-block; width: 120px; text-align: right;"  for='order_number'>Home country:</label>
                <select title="country" id="home_country" name="home_country">
                  <?php 
                    $homeCountryCode = 'US';
                    $homeCountry = Cart66Setting::getValue('home_country');
                    if($homeCountry) {
                      list($homeCountryCode, $homeCountryName) = explode('~', $homeCountry);
                    }
                    
                    foreach(Cart66Common::getCountries(true) as $code => $name) {
                      $selected = ($code == $homeCountryCode) ? 'selected="selected"' : '';
                      echo "<option value=\"$code~$name\" $selected>$name</option>";
                    }
                  ?>
                </select>
                <p class="label_desc">Your home country will be the default country on your checkout form</p>
              </li>
              <li>
                <label style="display: inline-block; width: 120px; text-align: right;">Currency symbol:</label>
                <input type="text" name="currency_symbol" value="<?php echo htmlentities(Cart66Setting::getValue('currency_symbol'));  ?>" id="currency_symbol">
                <span class="description">Use the HTML entity such as &amp;pound; for &pound; British Pound Sterling or &amp;euro; for &euro; Euro</span>
              </li>
              <li>
                <label style="display: inline-block; width: 120px; text-align: right;">Currency character:</label>
                <input type="text" name="currency_symbol_text" value="<?php echo Cart66Setting::getValue('currency_symbol_text'); ?>" id="currency_symbol_text">
                <span class="description">Do NOT use the HTML entity. This is the currency character used for the email receipts.</span>
              </li>
              <li>
                <label style="display: inline-block; width: 120px; text-align: right;" for='international_sales'>International sales:</label>
                <input type='radio' name='international_sales' id='international_sales_yes' value="1" 
                  <?php echo Cart66Setting::getValue('international_sales') == '1' ? 'checked="checked"' : '' ?>/> Yes
                <input type='radio' name='international_sales' id='international_sales_no' value="" 
                  <?php echo Cart66Setting::getValue('international_sales') != '1'? 'checked="checked"' : '' ?>/> No
              </li>
              
              <li id="eligible_countries_block">
                <label style="display: inline-block; width: 120px; text-align: right;" for='countries[]'>Ship to countries:</label>
                <div style="float: none; margin: -10px 0px 20px 125px;">
                <select name="countries[]" class="multiselect" multiple="multiple">
                  <?php
                    $countryList = Cart66Setting::getValue('countries');
                    $countryList = $countryList ? explode(',', $countryList) : array();
                  ?>
                  <?php foreach(Cart66Common::getCountries(true) as $code => $country): ?>
                    <?php 
                      $selected = (in_array($code . '~' .$country, $countryList)) ? 'selected="selected"' : '';
                      if(!empty($code)):
                    ?>
                      <option value="<?php echo $code . '~' . $country; ?>" <?php echo $selected ?>><?php echo $country ?></option>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </select>
                </div>
              </li>
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auth_force_ssl]'>Use SSL:</label>
              <?php
                $force = Cart66Setting::getValue('auth_force_ssl');
                if(!$force) { $force = 'no'; }
              ?>
              <input type='radio' name='auth_force_ssl' value="yes" style='width: auto;' <?php if($force == 'yes') { echo "checked='checked'"; } ?>><label style='width: auto; padding-left: 5px;'>Yes</label>
              <input type='radio' name='auth_force_ssl' value="no" style='width: auto;' <?php if($force == 'no') { echo "checked='checked'"; } ?>><label style='width: auto; padding-left: 5px;'>No</label>
                <p style="width: 450px;" class="label_desc">Be sure use an SSL certificate if you are using a payment gateway other than PayPal Website Payments Standard or PayPal Express Checkout.</p>
              </li>
              
              <?php if(CART66_PRO): ?>
                <li><label style="display: inline-block; width: 120px; text-align: right;" for='track_inventory'>Track inventory:</label>
                <?php
                  $track = Cart66Setting::getValue('track_inventory');
                ?>
                <input type='radio' name='track_inventory' value="1" style='width: auto;' <?php if($track == '1') { echo "checked='checked'"; } ?>><label style='width: auto; padding-left: 5px;'>Yes</label>
                <input type='radio' name='track_inventory' value="0" style='width: auto;' <?php if($track == '0') { echo "checked='checked'"; } ?>><label style='width: auto; padding-left: 5px;'>No</label>
                  <p style="width: 450px;" class="label_desc">This feature uses ajax. If you have javascript errors in your theme clicking Add To Cart buttons will not add products to the cart.</p>
                </li>
              <?php endif; ?>

              <li>
                <label style="display: inline-block; width: 120px; text-align: right;" >&nbsp;</label>
                <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" />
              </li>

            </ul>
          </form>
        </div>
      </div>
    </div>
  
    <!-- Tax Rates -->
    <?php $rates = $rate->getModels(); ?>
    <div class="widgets-holder-wrap <?php echo count($rates) ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Tax Rates <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">If you would like to collect sales tax please enter the tax rate information below. 
          You may enter tax rates for zip codes or states. If you are entering zip codes, you can enter individual 
          zip codes or zip code ranges. A zip code range is entered with the low value separated from the high value
          by a dash. For example, 23000-25000. Zip code tax rates take precedence over state tax rates.
          You may also choose whether or not you want to apply taxes to shipping charges.</p>
          
        <p class="description">NOTE: If you are using PayPal Website Payments Standard you must set up the tax rate 
          information <strong>in your paypal account</strong>.</p>
          
        <div>
          <form action="" method='post'>
            <input type='hidden' name='cart66-action' value="save rate" />
            <ul>
              <li><label for='tax[state]' style='width: auto;'>State:</label>
                <select name='tax[state]' id='tax_state'>
                  <option value="">&nbsp;</option>
                  <option value="All Sales">All Sales</option>
                  <optgroup label="United States">
                    <option value="AL">Alabama</option>
                    <option value="AK">Alaska</option>
                    <option value="AZ">Arizona</option>
                    <option value="AR">Arkansas</option>
                    <option value="CA">California</option>
                    <option value="CO">Colorado</option>
                    <option value="CT">Connecticut</option>
                    <option value="DC">D. C.</option>
                    <option value="DE">Delaware</option>
                    <option value="FL">Florida</option>
                    <option value="GA">Georgia</option>
                    <option value="HI">Hawaii</option>
                    <option value="ID">Idaho</option>
                    <option value="IL">Illinois</option>
                    <option value="IN">Indiana</option>
                    <option value="IA">Iowa</option>
                    <option value="KS">Kansas</option>
                    <option value="KY">Kentucky</option>
                    <option value="LA">Louisiana</option>
                    <option value="ME">Maine</option>
                    <option value="MD">Maryland</option>
                    <option value="MA">Massachusetts</option>
                    <option value="MI">Michigan</option>
                    <option value="MN">Minnesota</option>
                    <option value="MS">Mississippi</option>
                    <option value="MO">Missouri</option>
                    <option value="MT">Montana</option>
                    <option value="NE">Nebraska</option>
                    <option value="NV">Nevada</option>
                    <option value="NH">New Hampshire</option>
                    <option value="NJ">New Jersey</option>
                    <option value="NM">New Mexico</option>
                    <option value="NY">New York</option>
                    <option value="NC">North Carolina</option>
                    <option value="ND">North Dakota</option>
                    <option value="OH">Ohio</option>
                    <option value="OK">Oklahoma</option>
                    <option value="OR">Oregon</option>
                    <option value="PA">Pennsylvania</option>
                    <option value="RI">Rhode Island</option>
                    <option value="SC">South Carolina</option>
                    <option value="SD">South Dakota</option>
                    <option value="TN">Tennessee</option>
                    <option value="TX">Texas</option>
                    <option value="UT">Utah</option>
                    <option value="VT">Vermont</option>
                    <option value="VA">Virginia</option>
                    <option value="WA">Washington</option>
                    <option value="WV">West Virginia</option>
                    <option value="WI">Wisconsin</option>
                    <option value="WY">Wyoming</option>
                  </optgroup>
                  <optgroup label="Canada">
                    <option value="AB">Alberta</option>
                    <option value="BC">British Columbia</option>
                    <option value="MB">Manitoba</option>
                    <option value="NB">New Brunswick</option>
                    <option value="NF">Newfoundland</option>
                    <option value="NT">Northwest Territories</option>
                    <option value="NS">Nova Scotia</option>
                    <option value="NU">Nunavut</option>
                    <option value="ON">Ontario</option>
                    <option value="PE">Prince Edward Island</option>
                    <option value="PQ">Quebec</option>
                    <option value="SK">Saskatchewan</option>
                    <option value="YT">Yukon Territory</option>
                  </optgroup>
                </select>
              
                <span style="width: auto; text-align: center; padding: 0px 10px;">or</span>
                <label for='tax[zip]' style='width:auto;'>Zip:</label>
                <input type='text' value="" name='tax[zip]' size="14" />
                <label for='tax[rate]' style='width:auto; padding-left: 5px;'>Rate:</label>
                <input type='text' value="" name='tax[rate]' style='width: 55px;' /> %
                <select name='tax[tax_shipping]'>
                  <option value="0">Don't tax shipping</option>
                  <option value="1">Tax shipping</option>
                </select>
                <input type='submit' name='submit' class="button-primary" style='width: 60px; margin: 10px; margin-right: 0px;' value="Save" />
              </li>
            </ul>
          </form>
          
          <?php if(count($rates)): ?>
          <table class="widefat" style='width: 350px; margin-bottom: 30px;'>
          <thead>
          	<tr>
          		<th>Location</th>
          		<th>Rate</th>
          		<th>Tax Shipping</th>
          		<th>Actions</th>
          	</tr>
          </thead>
          <tbody>
            <?php foreach($rates as $rate): ?>
             <tr>
               <td>
                 <?php 
                 if($rate->zip_low > 0) {
                   if($rate->zip_low > 0) { echo $rate->zip_low; }
                   if($rate->zip_high > $rate->zip_low) { echo '-' . $rate->zip_high; }
                 }
                 else {
                   echo $rate->getFullStateName();
                 }
                 ?>
               </td>
               <td><?php echo number_format($rate->rate,2) ?>%</td>
               <td>
                 <?php
                 echo $rate->tax_shipping > 0 ? 'yes' : 'no';
                 ?>
               </td>
               <td>
                 <a class='delete' href='?page=cart66-settings&task=deleteTax&id=<?php echo $rate->id ?>'>Delete</a>
               </td>
             </tr>
            <?php endforeach; ?>
          </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>
    </div>
    
    <!-- PayPal Settings -->
    <div class="widgets-holder-wrap <?php echo (Cart66Setting::getValue('paypal_email') || Cart66Setting::getValue('paypalpro_api_username') ) ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>PayPal Settings <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">If you have signed up for the PayPal Pro account or if you plan to use PayPal Express Checkout, 
          please configure you settings below.</p>
        <div>
          <form id="PayPalSettings" class="ajaxSettingForm" action="" method='post'>
            <input type='hidden' name='action' value="save_settings" />
            <input type='hidden' name='_success' value="Your PayPal settings have been saved.">
            <ul>
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='paypal_email'>PayPal Email:</label>
              <input type='text' name='paypal_email' id='paypal_email' style='width: 375px;' value="<?php echo Cart66Setting::getValue('paypal_email'); ?>" />
              </li>
              
              <label style="display: inline-block; width: 120px; text-align: right;" for="currency_code">Default Currency:</label>
              <select name="currency_code"  id="currency_code">
                <?php
                  $currencies = Cart66Common::getPayPalCurrencyCodes();
                  $current_lc = Cart66Setting::getValue('currency_code');
                  foreach($currencies as $name => $code) {
                    $selected = '';
                    if($code == $current_lc) {
                      $selected = 'selected="selected"';
                    }
                    echo "<option value=\"$code\" $selected>$name</option>\n";
                  }
                ?>
              </select>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='shopping_url'>Shopping URL:</label>
              <input type='text' name='shopping_url' id='paypal_email' style='width: 375px;' value="<?php echo Cart66Setting::getValue('shopping_url'); ?>" />
              <p style="margin-left: 125px;" class="description">Used when buyers click 'Continue Shopping' in the PayPal Cart.</p>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='paypal_return_url'>Return URL:</label>
              <input type='text' name='paypal_return_url' id='paypal_return_url' 
              style='width: 375px;' value="<?php echo Cart66Setting::getValue('paypal_return_url'); ?>" />
              <p style="margin-left: 125px;" class="description">Where buyers are sent after paying at PayPal.</p>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='ipn_url'>Notification URL:</label>
              <span style="padding:0px; margin:0px;">
                <?php
                $ipnPage = get_page_by_path('store/ipn');
                $ipnUrl = get_permalink($ipnPage->ID);
                echo $ipnUrl;
                ?>
              </span>
              <p style="margin-left: 125px;" class="description">Instant Payment Notification (IPN)</p></li>

              <li>
                <label style="display: inline-block; width: 120px; text-align: right;" for='paypalpro_api_username'>&nbsp;</label>
                <strong>PayPal API Settings for Express Checkout <?php if(CART66_PRO) { echo 'and Website Payments Pro'; } ?></strong>
              </li>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='paypalpro_api_username'>API Username:</label>
              <input type='text' name='paypalpro_api_username' id='paypalpro_api_username' style='width: 375px;' 
              value="<?php echo Cart66Setting::getValue('paypalpro_api_username'); ?>" />
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='paypalpro_api_password'>API Password:</label>
              <input type='text' name='paypalpro_api_password' id='paypalpro_api_password' style='width: 375px;' 
              value="<?php echo Cart66Setting::getValue('paypalpro_api_password'); ?>" />
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='paypalpro_api_signature'>API Signature:</label>
              <input type='text' name='paypalpro_api_signature' id='paypalpro_api_signature' style='width: 375px;' 
              value="<?php echo Cart66Setting::getValue('paypalpro_api_signature'); ?>" />
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;">&nbsp;</label>
                <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" /></li>
                
              <?php if(CART66_PRO): ?>
                <li><p class='label_desc' style='color: #999'>Note: The Website Payments Pro solution can only be implemented by UK, Canadian and US Merchants.
                  <a href="https://www.x.com/docs/DOC-1510">Learn more</a></p></li>
              <?php else: ?>
                <li><p class='label_desc' style='color: #999'>Note: The Website Payments Pro solution is only available in <a href="http://cart66.com">Cart66 Professional</a>
                   and can only be implemented by UK, Canadian and US Merchants.</p></li>
              <?php endif; ?>
              
            </ul>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Gateway Settings -->
    <a name="gateway"></a>
    <div class="widgets-holder-wrap <?php echo Cart66Setting::getValue('auth_url') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Payment Gateway Settings<span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <?php if(CART66_PRO): ?>
        <p class="description">These settings configure your connection to your Authorize.net AIM compatible payment gateway.</p>
        <!--
        <p class="description"><b>Authorize.net URL:</b> <em>https://secure.authorize.net/gateway/transact.dll</em></p>
        <p class="description"><b>Quantum Gateway URL:</b> <em>https://secure.quantumgateway.com/cgi/authnet_aim.php</em></p>
        -->
        <div>
          <form id="AuthorizeFormSettings" class="ajaxSettingForm" action="" method='post'>
            <input type='hidden' name='action' value="save_settings" />
            <input type='hidden' name='_success' value="Your payment gateway settings have been saved.">
            <ul>
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auth_url'>Gateway:</label>
                <select name="auth_url" id="auth_url">
                  <option value="https://secure.authorize.net/gateway/transact.dll">Authorize.net</option>
                  <option value="https://test.authorize.net/gateway/transact.dll">Authorize.net Test</option>
                  <option value="https://secure.quantumgateway.com/cgi/authnet_aim.php">Quantum Gateway</option>
                  <option value="other">Other</option>
                </select>
              </li>
              
              <li id="emulation_url_item">
                <label style="display: inline-block; width: 120px; text-align: right;" for='emulation_url'>Emulation URL:</label>
                <input type='text' name='auth_url_other' id='auth_url_other' style='width: 375px;' value="<?php echo Cart66Setting::getValue('auth_url_other'); ?>" />
                <p id="emulation_url_desc" class="description" style='margin-left: 125px;'>Autorize.net AIM emulation URL</p>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auth_username'>API Login ID:</label>
              <input type='text' name='auth_username' id='auth_username' style='width: 375px;' value="<?php echo Cart66Setting::getValue('auth_username'); ?>" />
              <p id="authnet-image" class="label_desc"><a href="http://cart66.com/system66/wp-content/uploads/authnet-api-login.jpg" target="_blank">Where can I find 
                my Authorize.net API Login ID and Transaction Key?</a></p>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auth_trans_key'>Transaction key:</label>
              <input type='text' name='auth_trans_key' id='auth_trans_key' style='width: 375px;' 
                value="<?php echo Cart66Setting::getValue('auth_trans_key'); ?>" />
              </li>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for="auth[auth_card_types]">Accept Cards:</label>
              <input type="checkbox" name="auth_card_types[]" value="mastercard" style='width: auto;' 
                <?php echo in_array('mastercard', $cardTypes) ? 'checked="checked"' : '' ?>><label style='width: auto; padding-left: 5px;'>Mastercard</label>
              <input type="checkbox" name="auth_card_types[]" value="visa" style='width: auto;'
                <?php echo in_array('visa', $cardTypes) ? 'checked="checked"' : '' ?>><label style='width: auto; padding-left: 5px;'>Visa</label>
              <input type="checkbox" name="auth_card_types[]" value="amex" style='width: auto;'
                <?php echo in_array('amex', $cardTypes) ? 'checked="checked"' : '' ?>><label style='width: auto; padding-left: 5px;'>American Express</label>
              <input type="checkbox" name="auth_card_types[]" value="discover" style='width: auto;'
                <?php echo in_array('discover', $cardTypes) ? 'checked="checked"' : '' ?>><label style='width: auto; padding-left: 5px;'>Discover</label>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" />
              </li>
            </ul>
          </form>
        </div>
        <?php else: ?>
          <div style="padding: 5px 20px;">
          <p>With <a href="http://cart66.com">Cart66 Professional</a> you can accept credit cards directly on your website using:</p>
          <ul style="padding: 2px 30px; list-style: disc;"> 
            <li>PayPal Website Payments Pro</li>
            <li>Quantum Gateway</li>
            <li>eProcessing Network</li>
            <li>Authorize.net AIM</li>
            <li>Any other gateway that implements the Authorize.net AIM interface</li>
          </ul>
          </div>
        <?php endif; ?>
      </div>
    </div>
    
    <!-- Receipt Settings -->
    <div class="widgets-holder-wrap <?php echo Cart66Setting::getValue('receipt_from_name') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Email Receipt Settings <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <div>
          <p class="description">These are the settings used for sending email receipts to your customers after they place an order.</p>
          <form id="emailReceiptForm" class="ajaxSettingForm" action="" method='post'>
            <input type='hidden' name='action' value="save_settings" />
            <input type='hidden' name='_success' value="The email receipt settings have been saved.">
            <ul>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='receipt_from_name'>From Name:</label>
              <input type='text' name='receipt_from_name' id='receipt_from_name' style='width: 375px;' 
              value="<?php echo Cart66Setting::getValue('receipt_from_name', true); ?>" />
              <p style="margin-left: 125px;" class="description">The name of the person from whom the email receipt will be sent. 
                You may want this to be your company name.</p></li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='receipt_from_address'>From Address:</label>
              <input type='text' name='receipt_from_address' id='receipt_from_address' style='width: 375px;' 
              value="<?php echo Cart66Setting::getValue('receipt_from_address'); ?>" />
              <p  style="margin-left: 125px;" class="description">The email address the email receipt will be from.</p>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='receipt_subject'>Receipt Subject:</label>
              <input type='text' name='receipt_subject' id='receipt_subject' style='width: 375px;' 
              value="<?php echo Cart66Setting::getValue('receipt_subject', true); ?>" />
              <p style="margin-left: 125px;" class="description">The subject of the email receipt</p></li>
            
              <li><label style="display: inline-block; width: 120px; text-align: right; margin-top: 0px;" for='receipt_intro'>Receipt Intro:</label>
              <br/><textarea style="width: 375px; height: 140px; margin-left: 125px; margin-top: -20px;" 
              name='receipt_intro'><?php echo Cart66Setting::getValue('receipt_intro'); ?></textarea>
              <p style="margin-left: 125px;" class="description">This text will appear at the top of the receipt email message above the list of 
                items purchased.</p></li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='receipt_copy'>Copy Receipt To:</label>
              <input type='text' name='receipt_copy' id='receipt_copy' style='width: 375px;' value="<?php echo Cart66Setting::getValue('receipt_copy'); ?>" />
              <p style="margin-left: 125px;" class="description">Use commas to separate addresses.</p>
              </li>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" /></li>
            </ul>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Password Reset Email Settings -->
    <?php if(CART66_PRO): ?>
      <div class="widgets-holder-wrap <?php echo Cart66Setting::getValue('reset_subject') ? '' : 'closed'; ?>">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3>Password Reset Email Settings <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
            <p class="description">These are the settings used for sending password reset emails your subscribers who forget their passwords.</p>
            <form id="emailResetForm" class="ajaxSettingForm" action="" method='post'>
              <input type='hidden' name='action' value="save_settings" />
              <input type='hidden' name='_success' value="The password reset email settings have been saved.">
              <ul>

                <li><label style="display: inline-block; width: 120px; text-align: right;" for='reset_from_name'>From Name:</label>
                <input type='text' name='reset_from_name' id='reset_from_name' style='width: 375px;' 
                value="<?php echo Cart66Setting::getValue('reset_from_name', true); ?>" />
                <p style="margin-left: 125px;" class="description">The name of the person from whom the email will be sent. 
                  You may want this to be your company name.</p></li>

                <li><label style="display: inline-block; width: 120px; text-align: right;" for='reset_from_address'>From Address:</label>
                <input type='text' name='reset_from_address' id='reset_from_address' style='width: 375px;' 
                value="<?php echo Cart66Setting::getValue('reset_from_address'); ?>" />
                <p  style="margin-left: 125px;" class="description">The email address the email will be from.</p>
                </li>

                <li><label style="display: inline-block; width: 120px; text-align: right;" for='reset_subject'>Email Subject:</label>
                <input type='text' name='reset_subject' id='reset_subject' style='width: 375px;' 
                value="<?php echo Cart66Setting::getValue('reset_subject', true); ?>" />
                <p style="margin-left: 125px;" class="description">The subject of the email.</p></li>

                <li><label style="display: inline-block; width: 120px; text-align: right; margin-top: 0px;" for='reset_intro'>Email Intro:</label>
                <br/><textarea style="width: 375px; height: 140px; margin-left: 125px; margin-top: -20px;" 
                name='reset_intro'><?php echo Cart66Setting::getValue('reset_intro'); ?></textarea>
                <p style="margin-left: 125px;" class="description">This text will appear at the top of the reset email message above the new password.</p></li>

                <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
                <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" /></li>
              </ul>
            </form>
          </div>
        </div>
      </div>
    
      <!-- Blog Post Access Denied Messages -->
      <div class="widgets-holder-wrap <?php echo Cart66Setting::getValue('post_not_logged_in') ? '' : 'closed'; ?>">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3>Blog Post Access Denied Messages <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
            <p class="description">These are the messages your visitors will see when attempting to access a blog post that they do not have permission to view.</p>
            <form id="postAccessSettings" class="ajaxSettingForm" action="" method='post'>
              <input type='hidden' name='action' value="save_settings" />
              <input type='hidden' name='_success' value="The blog post access denied settings have been saved.">
              <ul>

                <li><label style="display: inline-block; width: 120px; text-align: right;" for='reset_from_name'>Not logged in:</label><br/>
                <textarea style="width: 375px; height: 140px; margin-left: 125px; margin-top: -20px;" 
                id="post_not_logged_in" name="post_not_logged_in"><?php echo Cart66Setting::getValue('post_not_logged_in'); ?></textarea>
                <p style="margin-left: 125px; padding-bottom: 15px;" class="description">The message that appears when a private posted is accessed by a visitor who is not logged in.</p></li>

                <li><label style="display: inline-block; width: 120px; text-align: right;" for='reset_from_name'>Access denied:</label><br/>
                <textarea style="width: 375px; height: 140px; margin-left: 125px; margin-top: -20px;" 
                id="post_access_denied" name="post_access_denied"><?php echo Cart66Setting::getValue('post_access_denied'); ?></textarea>
                <p style="margin-left: 125px;" class="description">The message that appears when a logged in member's subscription does not allow them to view the post.</p></li>

                <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
                <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" /></li>
              </ul>
            </form>
          </div>
        </div>
      </div>
    <?php endif; ?>
    
    
    <!-- Order Status Options -->
    <div class="widgets-holder-wrap <?php echo Cart66Setting::getValue('status_options') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Status Options<span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">Define the order status options to suite your business needs. For example, you may want to have new, complete, and canceled.</p>
        <div>
          <form id="statusOptionForm" class="ajaxSettingForm" action="" method='post'>
            <input type='hidden' name='action' value="save_settings" />
            <input type='hidden' name='_success' value="The order status option settings have been saved.">
            <ul>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='status_options'>Order statuses:</label>
              <input type='text' name='status_options' id='status_options' style='width: 80%;' 
              value="<?php echo Cart66Setting::getValue('status_options'); ?>" />
              <p style="margin-left: 125px;" class="description">Separate values with commas. (ex. new,complete,cancelled)</p></li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" /></li>
            </ul>
          </form>
        </div>
      </div>
    </div>

    <!-- Digital Product Settings -->
    <div class="widgets-holder-wrap <?php echo Cart66Setting::getValue('product_folder') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Digital Product Settings <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">Enter the absolute path to where you want to store your digital products. We suggest you choose a folder that is not
          web accessible. To help you figure out the path to your digital products folder, this is the absolute path to the page you are viewing now.<br/>
          <?php echo realpath('.'); ?><br/>
          Please note you should NOT enter a web url starting with http:// Your filesystem path will start with just a / 
        </p>
        <div>
          <form id="productFolderForm" class="ajaxSettingForm" action="" method='post'>
            <input type='hidden' name='action' value="save_settings" />
            <input type='hidden' name='_success' value="The product folder setting has been saved.">
            <ul>
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='product_folder'>Product folder:</label>
              <input type='text' name='product_folder' id='product_folder' style='width: 80%;' 
              value="<?php echo Cart66Setting::getValue('product_folder'); ?>" />
              <?php
                $dir = Cart66Setting::getValue('product_folder');
                if($dir) {
                  if(!file_exists($dir)) { mkdir($dir, 0700, true); }
                  if(!file_exists($dir)) { echo "<p class='label_desc' style='color: red;'><strong>WARNING:</strong> This directory does not exist.</p>"; }
                  elseif(!is_writable($dir)) { echo "<p class='label_desc' style='color: red;'><strong>WARNING:</strong> WordPress cannot write to this folder.</p>"; }
                }
              ?>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" /></li>
            </ul>
          </form>
        </div>
      </div>
    </div>
    
    
    <!-- Store Home Page -->
    <div class="widgets-holder-wrap <?php echo Cart66Setting::getValue('store_url') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Store Home Page <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">This is the link to the page of your site that you consider to be the home page of your store.
          When a customer views the items in their shopping cart this is the link used by the "continue shopping" button.
          You might set this to be the home page of your website or, perhaps, another page within your website that you consider
          to be the home page of the store section of your website. If you do not set a value here, the home page of your website
          will be used.</p>
        <div>
          <form id="storeHomeForm" class="ajaxSettingForm" action="" method='post'>
            <input type='hidden' name='action' value="save_settings" />
            <input type='hidden' name='_success' value="The store home page setting has been saved.">            
            <ul>
              
            <li><label style="display: inline-block; width: 120px; text-align: right;" for='store_url'>Store URL:</label>
            <input type='text' name='store_url' id='store_url' style='width: 80%;' value="<?php echo Cart66Setting::getValue('store_url'); ?>" />
            </li>

            <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
            <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" /></li>
            
            </ul>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Spreedly Settings -->
    <!--
    <a href="#" name="spreedly"></a>
    <div class="widgets-holder-wrap <?php echo Cart66Setting::getValue('spreedly_shortname') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Spreedly Account Information<span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">Configure your Spreedly account information to sell subscriptions.</p>
        <div>
          <form id="spreedlyOptionForm" class="ajaxSettingForm" action="" method='post'>
            <input type='hidden' name='action' value='save_settings' />
            <input type='hidden' name='_success' value='Your Spreedly settings have been saved.'>
            <ul>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='spreedly_shortname'>Short site name:</label>
              <input type='text' name='spreedly_shortname' id='spreedly_shortname' 
              value='<?php echo Cart66Setting::getValue('spreedly_shortname'); ?>' />
              <p class="description" style='margin-left: 125px;'>Look in your spreedly account under Site Details for the short site name (Used in URLs, etc)</p>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='spreedly_apitoken'>API token:</label>
              <input type='text' name='spreedly_apitoken' id='spreedly_apitoken' style="width: 70%;"
              value='<?php echo Cart66Setting::getValue('spreedly_apitoken'); ?>' />
              <p class="description" style='margin-left: 125px;'>Look in your spreedly account under Site Details for the API Authentication Token.</p>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auto_logout_link'>Log out link:</label>
              <input type='radio' name='auto_logout_link' id='auto_logout_link' value="1" <?php if(Cart66Setting::getValue('auto_logout_link')) { echo 'checked="checked"'; } ?> /> Yes
              <input type='radio' name='auto_logout_link' id='auto_logout_link' value="" <?php if(!Cart66Setting::getValue('auto_logout_link')) { echo 'checked="checked"'; } ?> /> No
              <p class="description" style='margin-left: 125px;'>Append a logout link to your site's navigation.<br/>Note, this only works with themes that build the navigation using the wp_list_pages() function. See the documentation for other log out options when using WordPress 3.0 Menus.</p>
              
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' /></li>
            </ul>
          </form>
        </div>
      </div>
    </div>
    -->
    
    <!-- Constant Contact Settings -->
    <a href="#" name="constantcontact"></a>
    <div class="widgets-holder-wrap <?php echo Cart66Setting::getValue('constantcontact_username') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Constant Contact Settings<span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">Configure your Constant Contact account information so your buyers can opt in to your newsletter.</p>
        <div>
          <?php if(CART66_PRO): ?>
          <form id="constantcontact" class="ajaxSettingForm" action="" method='post'>
            <input type='hidden' name='action' value="save_settings" />
            <input type='hidden' name='_success' value="Your Constant Contact settings have been saved.">
            <input type='hidden' name='constantcontact_apikey' value="9a2f451c-ccd6-453f-994f-6cc8c5dc1e94">
            <ul>
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='constantcontact_username'>Username:</label>
              <input type='text' name='constantcontact_username' id='constantcontact_username' value="<?php echo Cart66Setting::getValue('constantcontact_username'); ?>" />
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='constantcontact_password'>Password:</label>
              <input type='text' name='constantcontact_password' id='constantcontact_password' value="<?php echo Cart66Setting::getValue('constantcontact_password'); ?>" />
              
              <li><label style="display: inline-block; width: 120px; text-align: right; margin-top: 0px;" for='receipt_intro'>Opt-in Message:</label>
              <br/><textarea style="width: 375px; height: 140px; margin-left: 125px; margin-top: -20px;" 
              name='opt_in_message'><?php echo Cart66Setting::getValue('opt_in_message'); ?></textarea>
              <p style="margin-left: 125px;" class="description">Provide a message to tell your buyers what your newsletter is about.<br/>For example, you might want to say something like
                "Yes! I would like to subscribe to:"</p></li>
                
              <?php
                // Show the constant contact lists
                if(Cart66Setting::getValue('constantcontact_username')) {
                  echo '<li><label style="display: inline-block; width: 120px; text-align: right;">Show lists:</label>';
                  echo '<div style="width: 600px; display: block; margin-left: 125px; margin-top: -1.25em;">';
                  echo '<input type="hidden" name="constantcontact_list_ids" value="" />';
                  $cc = new Cart66ConstantContact();
                  $lists = $cc->get_all_lists('lists', 3);
                  if(is_array($lists)) {
                    $savedListIds = array();
                    if($savedLists = Cart66Setting::getValue('constantcontact_list_ids')) {
                      $savedListIds = explode('~', $savedLists);
                    }
                    
                    foreach($lists as $list) {
                      $checked = '';
                      $val = $list['id'] . '::' . $list['Name'];
                      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] looking for: $val in " . print_r($savedListIds, true));
                      if(in_array($val, $savedListIds)) {
                        $checked = 'checked="checked"';
                      }
                      echo '<input type="checkbox" name="constantcontact_list_ids[]" value="' . $val . '" ' . $checked . '> ' . $list['Name'] . '<br />';
                    }
                  }
                  else {
                    echo '<p class="description">You do not have any lists</p>';
                  } 
                  echo '</div></li>';
                }
              ?>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" /></li>
            </ul>
          </form>
          <?php else: ?>
            <p class="description" style="font-style: normal; color: #333; width: 600px;">Constant Contact is
              an industry leader in email marketing. Constant Contact provides email marketing software that makes it easy to 
              create professional HTML email campaigns with no tech skills.</p>
            <p class="description">This feature is only available in <a href="http://cart66.com">Cart66 Professional</a>.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    
    <!-- iDevAffiliate Settings -->
    <a href="#" name="idevaffiliate"></a>
    <div class="widgets-holder-wrap <?php echo Cart66Setting::getValue('idevaff_url') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>iDevAffiliate Settings<span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">Configure your iDevAffiliate account information so Cart66 can award commissions to your affiliates.</p>
        <div>
          <?php if(CART66_PRO): ?>
          <form id="iDevAffiliateForm" class="ajaxSettingForm" action="" method='post'>
            <input type='hidden' name='action' value="save_settings" />
            <input type='hidden' name='_success' value="Your iDevAffiliate settings have been saved.">
            <ul>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='idevaff_url'>URL:</label>
              <input type='text' name='idevaff_url' id='idevaff_url' style="width: 75%;"
              value="<?php echo Cart66Setting::getValue('idevaff_url'); ?>" />
              <p class="description" style='margin-left: 125px;'>Copy and paste your iDevAffiliate "3rd Party Affiliate Call" URL. It will looks like:<br/>
                http://www.yoursite.com/idevaffiliate/sale.php?profile=72198&amp;idev_saleamt=XXX&amp;idev_ordernum=XXX<br/>
                Be sure to leave the XXX's in place and Cart66 will replace the XXX's with the appropriate values for each sale.
                <?php if(Cart66Setting::getValue('idevaff_url')): ?>
                  <br/><br/><em>Note: To disable iDevAffiliate integration, simply delete this URL and click Save.</em>
                <?php endif; ?>
              </p>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" /></li>
            </ul>
          </form>
          <?php else: ?>
            <p class="description" style="font-style: normal; color: #333; width: 600px;"><a href="http://www.idevdirect.com/14717499.html">iDevAffiliate</a> is
              The Industry Leader in self managed affiliate program software. Started in 1999, iDevAffiliate is the original in self managed affiliate software!  
              iDevAffiliate was hand coded from scratch by the same team that provides their technical support! iDevAffilaite is also the affilate software that runs
              our <a href="http://affiliates.reality66.com/idevaffiliate/">Cart66 Affilaite Program</a>.</p>
            <p class="description">This feature is only available in <a href="http://cart66.com">Cart66 Professional</a>.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
    
    <!-- Zendesk Settings -->
    <a href="#" name="zendesk"></a>
    <div class="widgets-holder-wrap <?php echo Cart66Setting::getValue('zendesk_token') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Zendesk Account Information<span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">Configure your Zendesk account information to enable remote authentication.</p>
        <div>
          <?php if(CART66_PRO): ?>
          <form id="zendeskOptionForm" class="ajaxSettingForm" action="" method='post'>
            <input type='hidden' name='action' value="save_settings" />
            <input type='hidden' name='_success' value="Your Zendesk settings have been saved.">
            <ul>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='zendesk_token'>Token:</label>
              <input type='text' name='zendesk_token' id='zendesk_token' style="width: 50%;"
              value="<?php echo Cart66Setting::getValue('zendesk_token'); ?>" />
              <p class="description" style='margin-left: 125px;'>Look in your Zendesk account under Account --> Security --> Enable Remote Authentication for the Authentication Token.</p>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='zendesk_prefix'>Prefix:</label>
              <input type='text' name='zendesk_prefix' id='zendesk_prefix' style=""
              value="<?php echo Cart66Setting::getValue('zendesk_prefix'); ?>" />
              <p class="description" style='margin-left: 125px;'>The prefix is the first part of your zendesk account URL.<br/>For example, if your Zendesk URL is http://<strong style="font-size: 14px;">mycompany</strong>.zendesk.com Then your prefix is mycompany.</p>
              
              <!--
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='zendesk_organization'>Organization:</label>
              <input type='text' name='zendesk_organization' id='zendesk_organization' 
              value="<?php echo Cart66Setting::getValue('zendesk_organization'); ?>" />
              <span class="description">Optional</span>
              <p class="description" style='margin-left: 125px;'>If you have a logical grouping of users in your current system which you want to retain in Zendesk, this can be done setting the organization parameter. If you set a value here, but do not supply the <strong>name of an existing organization</strong>, the user will be removed from his current organization (if any). If you do not set the parameter, nothing will happen.</p>
              -->
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" /></li>
            </ul>
          </form>
          <?php else: ?>
            <p class="description" style="font-style: normal; color: #333; width: 600px;"><a href="http://www.zendesk.com">Zendesk</a> is the industry leader 
              in web-based help desk software with an elegant support ticket system and a self-service customer support platform. Agile, smart, and convenient.</p>
            <p class="description">This feature is only available in <a href="http://cart66.com">Cart66 Professional</a>.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
    
    
    <!-- Customize Cart Images -->
    <div class="widgets-holder-wrap <?php echo Cart66Setting::getValue('cart_images_url') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Customize Cart Images <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">If you would like to use your own shopping cart images (Add To Cart, Checkout, etc), enter the URL to the directory where you will be storing the images. The path should be outside the plugins/cart66 directory so that they are not lost when you upgrade your Cart66 intallation to a new version.</p>
        <p class="description">For example you may want to store your custom cart images here:<br/>
        <?php echo WPCURL ?>/uploads/cart-images/</p>
        <p class="description">Be sure that your path ends in a trailing slash like the example above and that you have all of the image names below in your directory:</p>
        <ul class="description" style='list-style-type: disc; padding: 0px 0px 0px 30px;'>
          <?php
          $dir = new DirectoryIterator(dirname(__FILE__) . '/../images');
          foreach ($dir as $fileinfo) {
              if (substr($fileinfo->getFilename(), -3) == 'png') {
                  echo '<li>' . $fileinfo->getFilename() . '</li>';
              }
          }
          ?>
        </ul>
        <div>
          <form id="cartImageForm" class="ajaxSettingForm" action="" method='post'>
            <input type='hidden' name='action' value="save_settings" />
            <input type='hidden' name='_success' value="The cart images setting has been saved.">
            <ul>
              
              <li><label style="display: inline-block; width: 150px; text-align: right;" for='styles[url]'>URL to image directory:</label>
              <input type='text' name='cart_images_url' id='cart_images_url' style='width: 375px;' 
              value="<?php echo Cart66Setting::getValue('cart_images_url'); ?>" /></li>

              <li><label style="display: inline-block; width: 150px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" /></li>
            
            </ul>
          </form>
        </div>
      </div>
    </div>
  
    <!-- Customize CSS Styles -->
    <div class="widgets-holder-wrap <?php echo Cart66Setting::getValue('styles_url') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Customize Styles <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">If you would like to override the default styles, you may enter the URL to your custom style sheet.</p>
        <div>
          <form id="cssForm" class="ajaxSettingForm" action="" method='post'>
            <input type='hidden' name='action' value="save_settings" />
            <input type='hidden' name='_success' value="The custom css style setting has been saved.">
            <ul>
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='styles_url'>URL to CSS:</label>
              <input type='text' name='styles_url' id='styles_url' style='width: 375px;' value="<?php echo Cart66Setting::getValue('styles_url'); ?>" /></li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" /></li>
            </ul>
          </form>
        </div>
      </div>
    </div>
    
    
    <!-- Error Logging -->
    <div class="widgets-holder-wrap <?php echo (Cart66Setting::getValue('enable_logging') || Cart66Setting::getValue('paypal_sandbox')) ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Error Logging &amp; Debugging<span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <div>
          <form id="debuggingForm" class="ajaxSettingForm" action="" method='post'>
            <input type='hidden' name='action' value="save_settings" />
            <input type='hidden' name='_success' value="The logging and debugging settings have been saved.">
            <input type="hidden" name="enable_logging" value="" id="enable_logging" />
            <input type="hidden" name="paypal_sandbox" value="" id="paypal_sandbox" />
            <ul>
              <li>
                <label style="display: inline-block; width: 220px; text-align: right;" for='styles_url'>Enable logging:</label>
                <input type='checkbox' name='enable_logging' id='enable_logging' value="1"
                  <?php echo Cart66Setting::getValue('enable_logging') ? 'checked="checked"' : '' ?>
                />
                <span class="label_desc">Only enable logging when testing your site. The log file will grow quickly.</span>
              </li>
              
              <li>
                <label style="display: inline-block; width: 220px; text-align: right;" for='styles_url'>Use PayPal Sandbox:</label>
                <input type='checkbox' name='paypal_sandbox' id='paypal_sandbox' value="1" 
                  <?php echo Cart66Setting::getValue('paypal_sandbox') ? 'checked="checked"' : '' ?>
                />
                <span class="label_desc">Send transactions to <a href='https://developer.paypal.com'>PayPal's developer sandbox</a>.</span>
              </li>

              <li><label style="display: inline-block; width: 220px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" /></li>
              
              
            </ul>
            
          </form>
          
         
          <?php if(Cart66Log::exists()): ?>
            <ul>
              <li>
                <div style="display: block; width:350px; margin-left:124px;">
                <form action="" method="post" style="padding: 10px 100px;">
                  <input type="hidden" name="cart66-action" value="download log file" id="cart66-action" />
                  <input type="submit" value="Download Log File" class="button-secondary" />
                </form>
                </div>
              </li>
            </ul>
          <?php endif; ?>
          
          <ul>
            <li>
             <label style="display: inline-block; width: 220px; text-align: right;float:left;" for='styles_url'>Debugging Data:</label>
             <div style="display: block; width:350px; margin-left:230px;">
                  <?php
                    global $wpdb; 
                  ?>
                  Cart66 Version: <?php echo CART66_VERSION_NUMBER; ?><br>
                  WP Version: <?php echo get_bloginfo("version"); ?><br>
                  PHP Version: <?php echo phpversion(); ?><br>
                  Session Save Path: <?php echo ini_get("session.save_path"); ?><br>
                  MySQL Version: <?php echo $wpdb->db_version();?><br>
                  MySQL Mode: <?php 
                                  $mode = $wpdb->get_row("SELECT @@SESSION.sql_mode as Mode"); 
                                  if(empty($mode->Mode)){
                                      echo "Normal";
                                  }
                                  echo $mode->Mode; ?><br>
                  Table Prefix: <?php echo $wpdb->prefix; ?><br>
                  Tables: <?php 
                              $required_tables = array($wpdb->prefix."cart66_products",
                              $wpdb->prefix."cart66_downloads",
                              $wpdb->prefix."cart66_promotions",
                              $wpdb->prefix."cart66_shipping_methods",
                              $wpdb->prefix."cart66_shipping_rates",
                              $wpdb->prefix."cart66_shipping_rules",
                              $wpdb->prefix."cart66_tax_rates",
                              $wpdb->prefix."cart66_cart_settings",
                              $wpdb->prefix."cart66_orders",
                              $wpdb->prefix."cart66_order_items",
                              $wpdb->prefix."cart66_inventory",
                              $wpdb->prefix."cart66_accounts",
                              $wpdb->prefix."cart66_account_subscriptions",
                              $wpdb->prefix."cart66_pp_recurring_payments"
                              );
                              $matched_tables = $wpdb->get_results("SHOW TABLES LIKE '".$wpdb->prefix."cart66_%'","ARRAY_N");
                              if(empty($matched_tables)){
                                echo "All Tables Are Missing!";
                              }
                              else {
                                foreach($matched_tables as $key=>$table){
                                  $cart_tables[] = $table[0];
                                }

                                $diff = array_diff($required_tables,$cart_tables);
                                if(!empty($diff)){
                                  echo "Missing tables: ";
                                  foreach($diff as $key=>$table){
                                    echo "$table  ";
                                  }
                                }
                                else{
                                  echo "All Tables Present";
                                }
                              }
                          ?><br>
									Current Dir: <?php echo getcwd(); ?><br>
             </div>
           </li>
          </ul>
          
          
        </div>
      </div>
    </div>
    
    
    
  
  </div>
</div>




<script type='text/javascript'>
  $jq = jQuery.noConflict();
  
  $jq(document).ready(function() {
    $jq(".multiselect").multiselect({sortable: true});
    
    $jq('.sidebar-name').click(function() {
     $jq(this.parentNode).toggleClass("closed");
    });

    $jq('#international_sales_yes').click(function() {
     $jq('#eligible_countries_block').show();
    });

    $jq('#international_sales_no').click(function() {
     $jq('#eligible_countries_block').hide();
    });

    if($jq('#international_sales_no').attr('checked')) {
     $jq('#eligible_countries_block').hide();
    }
    
    $jq('#auth_url').change(function() {
      setGatewayDisplay();
    });
    
    <?php if($authUrl = Cart66Setting::getValue('auth_url')): ?>
        $jq('#auth_url').val('<?php echo $authUrl; ?>').attr('selected', true);
    <?php endif; ?>
    
    setGatewayDisplay();
    function setGatewayDisplay() {
      if($jq('#auth_url').val() == 'other') {
        $jq('#emulation_url_item').css('display', 'inline');
      }
      else {
        $jq('#emulation_url_item').css('display', 'none');
        
      }
      
      if($jq('#auth_url :selected').text() == 'Authorize.net' || $jq('#auth_url :selected').text() == 'Authorize.net Test') {
        $jq('#authnet-image').css('display', 'block');
      }
      else {
        $jq('#authnet-image').css('display', 'none');
      }
    }
    
  });
  
  
  
</script>
