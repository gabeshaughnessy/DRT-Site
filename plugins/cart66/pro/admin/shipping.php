<?php
  if(isset($_GET['dismiss_canada_post_update']) && $_GET['dismiss_canada_post_update'] == 'true') {
    Cart66Setting::setValue('capost_merchant_id', '');
  }
?>
<div id="shipping_tabs" class="wrap">
  <div class="cart66Tabbed">
  	<div id="cart66-shipping-header">
  	  <ul class="tabs" id="sidemenu">
    	  <li class="sh1"><a class="sh1 tab" href="javascript:void(0)"><?php _e('USPS Shipping', 'cart66') ?></a></li>
    	  <li class="sh2"><a class="sh2 tab" href="javascript:void(0)"><?php _e('UPS Shipping', 'cart66') ?></a></li>
    	  <li class="sh3"><a class="sh3 tab" href="javascript:void(0)"><?php _e('FedEx Shipping' , 'cart66'); ?></a></li>
    	  <li class="sh4"><a class="sh4 tab" href="javascript:void(0)"><?php _e('Australia Post Shipping', 'cart66') ?></a></li>
    	  <li class="sh5"><a class="sh5 tab" href="javascript:void(0)"><?php _e('Canada Post Shipping', 'cart66') ?></a></li>
    	  <li class="sh6"><a class="sh6 tab" href="javascript:void(0)"><?php _e('Local Pickup', 'cart66') ?></a></li>
    	  <li class="sh7"><a class="sh7 tab" href="javascript:void(0)"><?php _e('Rate Tweaker', 'cart66') ?></a></li>
    	</ul>
  	</div>
  	<div class="loading">
  	  <h2 class="left"><?php _e('loading...', 'cart66') ?></h2>
  	</div>
  	<div class="sh1 pane">
  	  <h3 style="clear: both;"><?php _e( 'USPS Shipping Account Information' , 'cart66' ); ?></h3>
      <p><?php _e( 'If you intend to use United States Postal Service real-time shipping quotes please provide your USPS account information.<br/>This feature requires a <strong>production USPS account.</strong> A test account will not work.' , 'cart66' ); ?></p>
      <form action="" method='post'>
        <input type='hidden' name='cart66-action' value='save usps account info' />
        <ul>
          <li>
            <label class="med"><?php _e( 'Webtools username' , 'cart66' ); ?>:</label>
            <input type='text' name='ups[usps_username]' id='usps_username' value='<?php echo Cart66Setting::getValue('usps_username'); ?>' />
          </li>
          <li>
            <label class="med"><?php _e( 'Ship from zip' , 'cart66' ); ?>:</label>
            <input type='text' name='ups[usps_ship_from_zip]' id='usps_ship_from_zip' value='<?php echo Cart66Setting::getValue('usps_ship_from_zip'); ?>' />
          </li>
          <li>
            <p><?php _e( 'Select the USPS shipping methods you would like to offer to your customers.' , 'cart66' ); ?></p>
            <label class="med">&nbsp;</label> <a href="#" id="usps_clear_all">Clear All</a> | <a href="#" id="usps_select_all"><?php _e( 'Select All' , 'cart66' ); ?></a>
          </li>
          <li>
            <?php
              $services = Cart66ProCommon::getUspsServices();
              $methods = $method->getServicesForCarrier('usps');
              foreach($services as $name => $code) {
                $checked = '';
                if(in_array($code, $methods)) {
                  $checked = 'checked="checked"';
                }
                echo '<label class="med">&nbsp;</label>';
                echo "<input type='checkbox' class='usps_shipping_options' name='usps_methods[]' value='$code~$name' $checked> $name<br/>";
              }
            ?>
          </li>
          <li>
            <label class="med">&nbsp;</label>
            <input type='submit' name='submit' class="button-primary" style='width: 60px; margin-top: 10px;' value='Save' />
          </li>
        </ul>
      </form>
  	</div>
  	<div class="sh2 pane">
  	  <h3 style="clear: both;"><?php _e( 'UPS Shipping Account Information' , 'cart66' ); ?></h3>
      <p><?php _e( 'If you intend to use UPS real-time shipping quotes please provide your UPS account information.' , 'cart66' ); ?></p>
      <form action="" method='post'>
        <input type='hidden' name='cart66-action' value='save ups account info' />
        <ul>
          <li>
            <label class="med"><?php _e( 'Username' , 'cart66' ); ?>:</label>
            <input type='text' name='ups[ups_username]' id='ups_username' value='<?php echo Cart66Setting::getValue('ups_username'); ?>' />
          </li>
          <li>
            <label class="med"><?php _e( 'Password' , 'cart66' ); ?>:</label>
            <input type='text' name='ups[ups_password]' id='ups_password' value='<?php echo Cart66Setting::getValue('ups_password'); ?>' />
          </li>
          <li>
            <label class="med"><?php _e( 'API Key' , 'cart66' ); ?>:</label>
            <input type='text' name='ups[ups_apikey]' id='ups_apikey' value='<?php echo Cart66Setting::getValue('ups_apikey'); ?>' />
          </li>
          <li>
            <label class="med"><?php _e( 'Account number' , 'cart66' ); ?>:</label>
            <input type='text' name='ups[ups_account]' id='ups_account' value='<?php echo Cart66Setting::getValue('ups_account'); ?>' />
          </li>
          <li>
            <label class="med"><?php _e( 'Ship from zip' , 'cart66' ); ?>:</label>
            <input type='text' name='ups[ups_ship_from_zip]' id='ups_ship_from_zip' value='<?php echo Cart66Setting::getValue('ups_ship_from_zip'); ?>' />
          </li>        
          <li>
            <label class="med"><?php _e( 'Pickup Type' , 'cart66' ); ?>:</label>
            <select name='ups[ups_pickup_code]' id='ups_pickup_code'>
              <option value="03">Drop Off</option>
              <option value="01">Daily Pickup</option>
          <!--<option value="11">Suggested Retail Rates</option>
              <option value="06">One Time Pickup</option> -->
            </select>
          </li>
          <li>
            <label class="med"><?php _e( 'Commercial Only' , 'cart66' ); ?>:</label>
            <input type="hidden" name='ups[ups_only_ship_commercial]' value='' />
            <input type='checkbox' name='ups[ups_only_ship_commercial]' id='ups_only_ship_commercial' value='1' <?php echo (Cart66Setting::getValue('ups_only_ship_commercial')) ? "checked='checked'" : ""; ?> /> <?php _e( 'Check this box if you only ship to commercial addresses' , 'cart66' ); ?>.
          </li>
          <li>
            <p><?php _e( 'Select the UPS shipping methods you would like to offer to your customers.' , 'cart66' ); ?></p>
            <label class="med">&nbsp;</label> <a href="#" id="ups_clear_all"><?php _e( 'Clear All' , 'cart66' ); ?></a> | <a href="#" id="ups_select_all"><?php _e( 'Select All' , 'cart66' ); ?></a>
          </li>
          <li>
            <?php
              $services = Cart66ProCommon::getUpsServices();
              $methods = $method->getServicesForCarrier('ups');
              foreach($services as $name => $code) {
                $checked = '';
                if(in_array($code, $methods)) {
                  $checked = 'checked="checked"';
                }
                echo '<label class="med">&nbsp;</label>';
                echo "<input type='checkbox' class='ups_shipping_options' name='ups_methods[]' value='$code~$name' $checked> $name<br/>";
              }
            ?>
          </li>
          <li>
            <label class="med">&nbsp;</label>
            <input type='submit' name='submit' class="button-primary" style='width: 60px; margin-top: 10px;' value='<?php _e( 'Save' , 'cart66' ); ?>' />
          </li>
        </ul>
      </form>
  	</div>
  	<div class="sh3 pane">
  	  <h3 style="clear: both;"><?php _e( 'FedEx Shipping Account Information' , 'cart66' ); ?></h3>
      <p><?php _e( "If you intend to use FedEx real-time shipping quotes please provide your FedEx account information. This feature requires a <strong>production FedEx</strong> account. A test account will not work." , 'cart66' ); ?></p>
      <form action="" method='post'>
        <input type='hidden' name='cart66-action' value='save fedex account info' />
        <ul>
          <li>
            <label class="med"><?php _e( 'Developer Key' , 'cart66' ); ?>:</label>
            <input type='text' name='fedex[fedex_developer_key]' id='fedex_developer_key' value='<?php echo Cart66Setting::getValue('fedex_developer_key'); ?>' />
          </li>
          <li>
            <label class="med"><?php _e( 'Password' , 'cart66' ); ?>:</label>
            <input type='text' name='fedex[fedex_password]' id='fedex_password' value='<?php echo Cart66Setting::getValue('fedex_password'); ?>' />
          </li>
          <li>
            <label class="med"><?php _e( 'Account Number' , 'cart66' ); ?>:</label>
            <input type='text' name='fedex[fedex_account_number]' id='fedex_account_number' value='<?php echo Cart66Setting::getValue('fedex_account_number'); ?>' />
          </li>
          <li>
            <label class="med"><?php _e( 'Meter Number' , 'cart66' ); ?>:</label>
            <input type='text' name='fedex[fedex_meter_number]' id='fedex_meter_number' value='<?php echo Cart66Setting::getValue('fedex_meter_number'); ?>' />
          </li>
          <li>
            <label class="med"><?php _e( 'Ship from zip' , 'cart66' ); ?>:</label>
            <input type='text' name='fedex[fedex_ship_from_zip]' id='fedex_ship_from_zip' value='<?php echo Cart66Setting::getValue('fedex_ship_from_zip'); ?>' />
          </li>        
          <li>
            <label class="med"><?php _e( 'Pickup Type' , 'cart66' ); ?>:</label>
            <select name='fedex[fedex_pickup_code]' id='fedex_pickup_code'>
              <option value="REGULAR_PICKUP"><?php _e( 'Regular Pickup' , 'cart66' ); ?></option>
              <option value="REQUEST_COURIER"><?php _e( 'Request Courier' , 'cart66' ); ?></option>
              <option value="DROP_BOX"><?php _e( 'Drop Box' , 'cart66' ); ?></option>
              <option value="STATION"><?php _e( 'Station' , 'cart66' ); ?></option>
              <option value="BUSINESS_SERVICE_CENTER"><?php _e( 'Business Service Center' , 'cart66' ); ?></option>
            </select>
          </li>
          <li>
            <label class="med"><?php _e( 'Your Location' , 'cart66' ); ?>:</label>
            <select name='fedex[fedex_location_type]' id='fedex_location_type'>
              <option value="commercial"><?php _e( 'Commercial' , 'cart66' ); ?></option>
              <option value="residential"><?php _e( 'Residential' , 'cart66' ); ?></option>
            </select>
          </li>
          <li>
            <label class="med"><?php _e( 'Commercial Only' , 'cart66' ); ?>:</label>
            <input type="hidden" name='fedex[fedex_only_ship_commercial]' value='' />
            <input type='checkbox' name='fedex[fedex_only_ship_commercial]' id='fedex_only_ship_commercial' value='1' <?php echo (Cart66Setting::getValue('fedex_only_ship_commercial')) ? "checked='checked'" : ""; ?> /> <?php _e( 'Check this box if you only ship to commercial addresses' , 'cart66' ); ?>.
          </li>
          <li>
            <label class="med"><?php _e( 'Ship Items Individually' , 'cart66' ); ?>:</label>
            <input type="hidden" name='fedex[fedex_ship_individually]' value='' />
            <input type='checkbox' name='fedex[fedex_ship_individually]' id='fedex_ship_individually' value='1' <?php echo (Cart66Setting::getValue('fedex_ship_individually')) ? "checked='checked'" : ""; ?> /> <?php _e( 'Check this box if you want to get a quote for each item shipped separately instead of combined into one package' , 'cart66' ); ?>.
          </li>
          <li>
            <p><?php _e( 'Select the FedEx shipping methods you would like to offer to your customers.' , 'cart66' ); ?></p>
            <label class="med">&nbsp;</label> <a href="#" id="fedex_clear_all"><?php _e( 'Clear All' , 'cart66' ); ?></a> | <a href="#" id="fedex_select_all"><?php _e( 'Select All' , 'cart66' ); ?></a>
          </li>
          <li>
            <?php
              $homeCountryCode = 'US';
              $setting = new Cart66Setting();
              $home = Cart66Setting::getValue('home_country');
              if($home) {
                list($homeCountryCode, $name) = explode('~', $home);
              }
              if($homeCountryCode == 'US' || $homeCountryCode == 'CA') {
                $services = Cart66ProCommon::getFedexServices();
                $methods = $method->getServicesForCarrier('fedex');
                foreach($services as $name => $code) {
                  $checked = '';
                  if(in_array($code, $methods)) {
                    $checked = 'checked="checked"';
                  }
                  echo '<label class="med">&nbsp;</label>';
                  echo "<input type='checkbox' class='fedex_shipping_options' name='fedex_methods[]' value='$code~$name' $checked> $name<br/>";
                }
              }
              $services = Cart66ProCommon::getFedexIntlServices();
              $methods = $method->getServicesForCarrier('fedex_intl');
              foreach($services as $name => $code) {
                $checked = '';
                if(in_array($code, $methods)) {
                  $checked = 'checked="checked"';
                }
                echo '<label class="med">&nbsp;</label>';
                echo "<input type='checkbox' class='fedex_shipping_options' name='fedex_methods_intl[]' value='$code~$name' $checked> $name<br/>";
              }
            ?>
          </li>
          <li>
            <label class="med">&nbsp;</label>
            <input type='submit' name='submit' class="button-primary" style='width: 60px; margin-top: 10px;' value='<?php _e( 'Save' , 'cart66' ); ?>' />
          </li>
        </ul>
      </form>
  	</div>
  	<div class="sh4 pane">
  	  <h3 style="clear: both;"><?php _e( 'Australia Post Shipping Account Information' , 'cart66' ); ?></h3>
      <p><?php _e( 'If you intend to use Australia Post real-time shipping quotes please provide your Australia Post account information.' , 'cart66' ); ?></p>
      <?php
      if($homeCountryCode !='AU'){
        echo '<h3>You must set Australia as your home country in order to use Australia Post Shipping Live Rates</h3>';
      } else {
      ?>
        <form action="" method='post'>
          <input type='hidden' name='cart66-action' value='save aupost account info' />
          <ul>
            <li>
              <label class="med"><?php _e( 'Developer Key' , 'cart66' ); ?>:</label>
              <input type='text' name='aupost[aupost_developer_key]' id='aupost_developer_key' value='<?php echo Cart66Setting::getValue('aupost_developer_key'); ?>' />
            </li>
            <li>
              <label class="med"><?php _e( 'Ship from zip' , 'cart66' ); ?>:</label>
              <input type='text' name='aupost[aupost_ship_from_zip]' id='aupost_ship_from_zip' value='<?php echo Cart66Setting::getValue('aupost_ship_from_zip'); ?>' />
            </li>
            <li>
              <label class="med"><?php _e( 'Ship Items Individually' , 'cart66' ); ?>:</label>
              <input type="hidden" name='aupost[aupost_ship_individually]' value='' />
              <input type='checkbox' name='aupost[aupost_ship_individually]' id='aupost_ship_individually' value='1' <?php echo (Cart66Setting::getValue('aupost_ship_individually')) ? "checked='checked'" : ""; ?> /> <?php _e( 'Check this box if you want to get a quote for each item shipped separately instead of combined into one package' , 'cart66' ); ?>.
            </li>
            <li>
              <p><?php _e( 'Select the Australia Post shipping methods you would like to offer to your customers.' , 'cart66' ); ?></p>
              <label class="med">&nbsp;</label> <a href="#" id="aupost_clear_all"><?php _e( 'Clear All' , 'cart66' ); ?></a> | <a href="#" id="aupost_select_all"><?php _e( 'Select All' , 'cart66' ); ?></a>
            </li>
            <li>
              <?php
                $homeCountryCode = 'AU';
                $setting = new Cart66Setting();
                $home = Cart66Setting::getValue('home_country');
                if($home) {
                  list($homeCountryCode, $name) = explode('~', $home);
                }
                $services = Cart66ProCommon::getAuPostServices();
                $methods = $method->getServicesForCarrier('aupost');
                foreach($services as $name => $code) {
                  $checked = '';
                  if(in_array($code, $methods)) {
                    $checked = 'checked="checked"';
                  }
                  echo '<label class="med">&nbsp;</label>';
                  echo "<input type='checkbox' class='aupost_shipping_options' name='aupost_methods[]' value='$code~$name' $checked> $name<br/>";
                }
                $services = Cart66ProCommon::getAuPostIntlServices();
                $methods = $method->getServicesForCarrier('aupost_intl');
                foreach($services as $name => $code) {
                  $checked = '';
                  if(in_array($code, $methods)) {
                    $checked = 'checked="checked"';
                  }
                  echo '<label class="med">&nbsp;</label>';
                  echo "<input type='checkbox' class='aupost_shipping_options' name='aupost_methods_intl[]' value='$code~$name' $checked> $name<br/>";
                }
              ?>
            </li>
            <li>
              <label class="med">&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px; margin-top: 10px;' value='<?php _e( 'Save' , 'cart66' ); ?>' />
            </li>
          </ul>
        </form>
      <?php } ?>
  	</div>
  	<div class="sh5 pane">
  	  <h3 style="clear: both;"><?php _e( 'Canada Post Shipping Account Information' , 'cart66' ); ?></h3>
      <p><?php _e( 'If you intend to use Canada Post real-time shipping quotes please provide your Canada Post account information. This also requires that you sign up for developer access. The Username and Password required below are not your username and password to sign in to your account at Canada Post. They are a special API Username and Password provided when you activate the developer program for your Canada Post account.' , 'cart66' ); ?></p>
      <?php
      if($homeCountryCode !='CA'){
        echo '<h3>You must set Canada as your home country in order to use Canada Post Shipping Live Rates</h3>';
      } else {
      ?>
        <form action="" method='post'>
          <input type='hidden' name='cart66-action' value='save capost account info' />
          <ul>
            <li>
              <label class="med"><?php _e( 'API Username' , 'cart66' ); ?>:</label>
              <input type='text' name='capost[capost_username]' id='capost_username' value='<?php echo Cart66Setting::getValue('capost_username'); ?>' />
            </li>
            <li>
              <label class="med"><?php _e( 'API Password' , 'cart66' ); ?>:</label>
              <input type='text' name='capost[capost_password]' id='capost_password' value='<?php echo Cart66Setting::getValue('capost_password'); ?>' />
            </li>
            <li>
              <label class="med"><?php _e( 'Customer Number' , 'cart66' ); ?>:</label>
              <input type='text' name='capost[capost_customer_number]' id='capost_customer_number' value='<?php echo Cart66Setting::getValue('capost_customer_number'); ?>' />
            </li>
            <li>
              <label class="med"><?php _e( 'Ship from zip' , 'cart66' ); ?>:</label>
              <input type='text' name='capost[capost_ship_from_zip]' id='capost_ship_from_zip' value='<?php echo Cart66Setting::getValue('capost_ship_from_zip'); ?>' />
            </li>        
            <li>
              <p><?php _e( 'Select the Canada Post shipping methods you would like to offer to your customers.' , 'cart66' ); ?></p>
              <label class="med">&nbsp;</label> <a href="#" id="capost_clear_all"><?php _e( 'Clear All' , 'cart66' ); ?></a> | <a href="#" id="capost_select_all"><?php _e( 'Select All' , 'cart66' ); ?></a>
            </li>
            <li>
              <?php
                $homeCountryCode = 'CA';
                $setting = new Cart66Setting();
                $home = Cart66Setting::getValue('home_country');
                if($home) {
                  list($homeCountryCode, $name) = explode('~', $home);
                }
                $services = Cart66ProCommon::getCaPostServices();
                $methods = $method->getServicesForCarrier('capost');
                foreach($services as $name => $code) {
                  $checked = '';
                  if(in_array($code, $methods)) {
                    $checked = 'checked="checked"';
                  }
                  echo '<label class="med">&nbsp;</label>';
                  echo "<input type='checkbox' class='capost_shipping_options' name='capost_methods[]' value='$code~$name' $checked> $name<br/>";
                }
                $services = Cart66ProCommon::getCaPostIntlServices();
                $methods = $method->getServicesForCarrier('capost_intl');
                foreach($services as $name => $code) {
                  $checked = '';
                  if(in_array($code, $methods)) {
                    $checked = 'checked="checked"';
                  }
                  echo '<label class="med">&nbsp;</label>';
                  echo "<input type='checkbox' class='capost_shipping_options' name='capost_methods_intl[]' value='$code~$name' $checked> $name<br/>";
                }
              ?>
            </li>
            <li>
              <label class="med">&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px; margin-top: 10px;' value='<?php _e( 'Save' , 'cart66' ); ?>' />
            </li>
          </ul>
        </form>
      <?php } ?>
  	</div>
  	<div class="sh6 pane">
  	  <h3><?php _e( 'Local Pickup Option' , 'cart66' ); ?></h3>
      <p><?php _e( 'If you intend to use UPS real-time shipping quotes please provide your UPS account information.' , 'cart66' ); ?></p>
      <form action="" method='post'>
        <input type='hidden' name='cart66-action' value='save local pickup info' />
        <input type='hidden' name='local[shipping_local_pickup]' value='' />
        <input type='hidden' name='local[local_pickup_at_end]' value='' />
        <ul>
          <li>
            <label class="med"><?php _e( 'Enable' , 'cart66' ); ?>:</label>
            <input type='checkbox' name='local[shipping_local_pickup]' id='shipping_local_pickup' value='1' <?php echo (Cart66Setting::getValue('shipping_local_pickup')) ? "checked='checked'" : ""; ?> /> <?php _e( 'Check this box if you want to enable a local pickup or "in-store" option' , 'cart66' ); ?>.
          </li>
          <li>
            <label class="med"><?php _e( 'Push to End' , 'cart66' ); ?>:</label>
            <input type='checkbox' name='local[local_pickup_at_end]' id='local_pickup_at_end' value='1' <?php echo (Cart66Setting::getValue('local_pickup_at_end')) ? "checked='checked'" : ""; ?> /> <?php _e( 'Check this box if you want to put the local pickup option at the end of the live rates' , 'cart66' ); ?>.
          </li>
          <li>
            <label class="med"><?php _e( 'Label' , 'cart66' ); ?>:</label>
            <input type='text' name='local[shipping_local_pickup_label]' id='shipping_local_pickup_label' value='<?php echo Cart66Setting::getValue('shipping_local_pickup_label'); ?>' />
          </li>
          <li>
            <label class="med"><?php _e( 'Amount' , 'cart66' ); ?>:</label>
            <input type='text' name='local[shipping_local_pickup_amount]' id='shipping_local_pickup_amount' value='<?php echo Cart66Setting::getValue('shipping_local_pickup_amount'); ?>' />
          </li>
          <li>
            <label class="med">&nbsp;</label>
            <input type='submit' name='submit' class="button-primary" style='width: 60px; margin-top: 10px;' value='<?php _e( 'Save' , 'cart66' ); ?>' />
          </li>
        </ul>
      </form>
  	</div>
  	<div class="sh7 pane">
  	  <h3><?php _e( 'Rate Tweaker' , 'cart66' ); ?></h3>

      <p style="border: 1px solid #CCC; background-color: #eee; padding: 5px; width: 590px; -moz-border-radius: 5px; -webkit-border-radius: 5px;">
        <strong><?php _e( 'Current Tweak Factor' , 'cart66' ); ?>:</strong> 
        <?php
          if(Cart66Setting::getValue('rate_tweak_factor')) {
            $type = Cart66Setting::getValue('rate_tweak_type');
            $factor = Cart66Setting::getValue('rate_tweak_factor');

            if($type == 'percentage') {
              $direction = $factor > 0 ? 'increased' : 'decreased';
              echo "All rates will be $direction by " . abs($factor) . '%';
            }
            else {
              $direction = $factor > 0 ? 'added to' : 'subtracted from';
              echo CART66_CURRENCY_SYMBOL . number_format(abs($factor), 2) . " will be $direction all rates";
            }

          }
          else {
            echo 'The calculated rates will not be tweaked.';
          }
        ?>
      </p>

      <form action="" method="post">
        <input type="hidden" name="cart66-action" value="save rate tweak" />
        <select name="rate_tweak_type" id="rate_tweak_type">
          <option value="percentage"><?php _e( 'Tweak by percentage' , 'cart66' ); ?></option>
          <option value="fixed"><?php _e( 'Tweak by fixed amount' , 'cart66' ); ?></option>
        </select>
        <span id="currency" style="display:none;">&nbsp;<?php echo CART66_CURRENCY_SYMBOL; ?></span>
        <input type="text" name="rate_tweak_factor" style="width: 5em;" />
        <span id="percentSign" style="display:none;">%</span>
        <input type='submit' name='submit' class="button-primary" style='width: 60px; margin-top: 10px; margin-left: 20px; margin-right: 20px;' value='Save' />
        <a id="whatIsRateTweaker" href="#" class='what_is'><?php _e( 'What is this?' , 'cart66' ); ?></a>
      </form>

      <div id="whatIsRateTweaker_answer" style="display: none; border: 1px solid #eee; background-color: #fff; padding: 0px 10px; width: 590px; margin-top: 10px; -moz-border-radius: 5px; -webkit-border-radius: 5px;">
        <h3><?php _e( 'How The Rate Tweaker Works' , 'cart66' ); ?></h3>
        <p><?php _e( 'The rate tweaker provides a way to adjust the live rate quotes by increasing or decreasing all of the calculated rates by a specified amount. You may choose to modify the rates by a percentage amount or by fixed amount. Enter a positive value to increase the calculated rates or negative value to reduce them. The rate tweaker will never reduce shipping rates below zero.' , 'cart66' ); ?></p>
        <p><?php _e( 'For example, if you want to increase all the calculated rates by 15% select "Tweak by percentage" and enter 15 in the text field then click "Save"' , 'cart66' ); ?></p>
        <p><?php _e( 'If you want to take $5.00 off all the shipping rates select "Tweak by fixed amount" and enter -5 in the text field then click "Save"' , 'cart66' ); ?></p>
        <p><?php _e( 'To stop using the rate tweaker, enter 0 and click "save"' , 'cart66' ); ?></p>
      </div>
  	</div>
  </div>
</div>
<script type="text/javascript">
  (function($){
    $(document).ready(function(){
      
      $('div.sh<?php echo $tab; ?>').show();
  	  
  	  $('div.loading').hide();
  	  $('div.cart66Tabbed ul.tabs li.sh<?php echo $tab; ?> a').addClass('current');  	  
      // SHIPPING TABS
      $('div.cart66Tabbed ul li a.tab').click(function(){
  	    var thisClass = this.className.slice(0,3);
  	    $('div.pane').hide();
  	    $('div.' + thisClass).fadeIn(300);
  	    $('div.cart66Tabbed ul.tabs li a').removeClass('current');
  	    $('div.cart66Tabbed ul.tabs li a.' + thisClass).addClass('current');
  	  });
      
      $('#ups_clear_all').click(function() {
        $('.ups_shipping_options').attr('checked', false);
        return false;
      });

      $('#ups_select_all').click(function() {
        $('.ups_shipping_options').attr('checked', true);
        return false;
      });

      $('#usps_clear_all').click(function() {
        $('.usps_shipping_options').attr('checked', false);
        return false;
      });

      $('#usps_select_all').click(function() {
        $('.usps_shipping_options').attr('checked', true);
        return false;
      });
      
      $('#ups_pickup_code').val("<?php echo Cart66Setting::getValue('ups_pickup_code'); ?>");
      $('#fedex_pickup_code').val("<?php echo Cart66Setting::getValue('fedex_pickup_code'); ?>");
      $('#fedex_location_type').val("<?php echo Cart66Setting::getValue('fedex_location_type'); ?>");
      
      $('#fedex_clear_all').click(function() {
        $('.fedex_shipping_options').attr('checked', false);
        return false;
      });

      $('#fedex_select_all').click(function() {
        $('.fedex_shipping_options').attr('checked', true);
        return false;
      });
      
      $('#aupost_clear_all').click(function() {
        $('.aupost_shipping_options').attr('checked', false);
        return false;
      });

      $('#aupost_select_all').click(function() {
        $('.aupost_shipping_options').attr('checked', true);
        return false;
      });
      
      $('#capost_clear_all').click(function() {
        $('.capost_shipping_options').attr('checked', false);
        return false;
      });

      $('#capost_select_all').click(function() {
        $('.capost_shipping_options').attr('checked', true);
        return false;
      });
      
      setRateTweakerSymbol();

      $('#rate_tweak_type').change(function() {
        setRateTweakerSymbol();
      });
    })
   
  })(jQuery);
  function setRateTweakerSymbol() {
    $jq = jQuery.noConflict();
    if($jq('#rate_tweak_type').val() == 'percentage') {
      $jq('#percentSign').css('display', 'inline');
      $jq('#currency').css('display', 'none');
    }
    else {
      $jq('#currency').css('display', 'inline');
      $jq('#percentSign').css('display', 'none');
    }
  }
</script>