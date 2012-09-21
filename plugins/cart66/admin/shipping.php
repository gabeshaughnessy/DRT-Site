<?php
$rule = new Cart66ShippingRule();
$method = new Cart66ShippingMethod();
$rate = new Cart66ShippingRate();
$product = new Cart66Product();
$tab = 1;

if($_SERVER['REQUEST_METHOD'] == "POST") {
  if($_POST['cart66-action'] == 'save rule') {
    $rule->setData($_POST['rule']);
    $rule->save();
    $rule->clear();
  }
  elseif($_POST['cart66-action'] == 'save shipping method') {
    $method->setData($_POST['shipping_method']);
    $method->save();
    $method->clear();
  }
  elseif($_POST['cart66-action'] == 'save product rate') {
    $rate->setData($_POST['rate']);
    $rate->save();
    $rate->clear();
  }
  elseif($_POST['cart66-action'] == 'save local pickup info') {
    foreach($_POST['local'] as $key => $value) {
      Cart66Setting::setValue($key, $value);
    }
    $tab = 6;
  }
  elseif($_POST['cart66-action'] == 'save ups account info') {
    foreach($_POST['ups'] as $key => $value) {
      Cart66Setting::setValue($key, $value);
    }
    $methods = (isset($_POST['ups_methods'])) ? $_POST['ups_methods'] : false;
    $codes = array();
    if(is_array($methods)) {
      foreach($methods as $methodData) {
        list($code, $name) = explode('~', $methodData);
        $m = new Cart66ShippingMethod();
        $m->code = $code;
        $m->name = $name;
        $m->carrier = 'ups';
        $m->save();
        $codes[] = $code;
      }
    }
    else {
      $codes[] = -1;
    }
    $method->pruneCarrierMethods('ups', $codes);
    $tab = 2;
  }
  elseif($_POST['cart66-action'] == 'save usps account info') {
    foreach($_POST['ups'] as $key => $value) {
      Cart66Setting::setValue($key, $value);
    }
    
    $methods = $_POST['usps_methods'];
    $codes = array();
    if(is_array($methods)) {
      foreach($methods as $methodData) {
        list($code, $name) = explode('~', $methodData);
        $m = new Cart66ShippingMethod();
        $m->code = $code;
        $m->name = $name;
        $m->carrier = 'usps';
        $m->save();
        $codes[] = $code;
      }
    }
    else {
      $codes[] = -1;
    }
    $method->pruneCarrierMethods('usps', $codes);
    $tab = 1;
  }
  elseif($_POST['cart66-action'] == 'save fedex account info') {
    foreach($_POST['fedex'] as $key => $value) {
      Cart66Setting::setValue($key, $value);
    }
    
    $methods = (isset($_POST['fedex_methods'])) ? $_POST['fedex_methods'] : false;
    $codes = array();
    if(is_array($methods)) {
      foreach($methods as $methodData) {
        list($code, $name) = explode('~', $methodData);
        $m = new Cart66ShippingMethod();
        $m->code = $code;
        $m->name = $name;
        $m->carrier = 'fedex';
        $m->save();
        $codes[] = $code;
      }
    }
    else {
      $codes[] = -1;
    }
    $method->pruneCarrierMethods('fedex', $codes);
    
    $intlMethods = (isset($_POST['fedex_methods_intl'])) ? $_POST['fedex_methods_intl'] : false;
    $intlCodes = array();
    if(is_array($intlMethods)) {
      foreach($intlMethods as $methodData) {
        list($code, $name) = explode('~', $methodData);
        $m = new Cart66ShippingMethod();
        $m->code = $code;
        $m->name = $name;
        $m->carrier = 'fedex_intl';
        $m->save();
        $intlCodes[] = $code;
      }
    }
    else {
      $intlCodes[] = -1;
    }
    $method->pruneCarrierMethods('fedex_intl', $intlCodes);
    
    $tab = 3;
  }
  elseif($_POST['cart66-action'] == 'save aupost account info') {
    foreach($_POST['aupost'] as $key => $value) {
      Cart66Setting::setValue($key, $value);
    }
    $methods = (isset($_POST['aupost_methods'])) ? $_POST['aupost_methods'] : false;
    $codes = array();
    if(is_array($methods)) {
      foreach($methods as $methodData) {
        list($code, $name) = explode('~', $methodData);
        $m = new Cart66ShippingMethod();
        $m->code = $code;
        $m->name = $name;
        $m->carrier = 'aupost';
        $m->save();
        $codes[] = $code;
      }
    }
    else {
      $codes[] = -1;
    }
    $method->pruneCarrierMethods('aupost', $codes);
    
    $intlMethods = (isset($_POST['aupost_methods_intl'])) ? $_POST['aupost_methods_intl'] : false;
    $intlCodes = array();
    if(is_array($intlMethods)) {
      foreach($intlMethods as $methodData) {
        list($code, $name) = explode('~', $methodData);
        $m = new Cart66ShippingMethod();
        $m->code = $code;
        $m->name = $name;
        $m->carrier = 'aupost_intl';
        $m->save();
        $intlCodes[] = $code;
      }
    }
    else {
      $intlCodes[] = -1;
    }
    $method->pruneCarrierMethods('aupost_intl', $intlCodes);
    
    $tab = 4;
  }
  elseif($_POST['cart66-action'] == 'save capost account info') {
    foreach($_POST['capost'] as $key => $value) {
      Cart66Setting::setValue($key, $value);
    }
    $methods = (isset($_POST['capost_methods'])) ? $_POST['capost_methods'] : false;
    $codes = array();
    if(is_array($methods)) {
      foreach($methods as $methodData) {
        list($code, $name) = explode('~', $methodData);
        $m = new Cart66ShippingMethod();
        $m->code = $code;
        $m->name = $name;
        $m->carrier = 'capost';
        $m->save();
        $codes[] = $code;
      }
    }
    else {
      $codes[] = -1;
    }
    $method->pruneCarrierMethods('capost', $codes);
    
    $intlMethods = (isset($_POST['capost_methods_intl'])) ? $_POST['capost_methods_intl'] : false;
    $intlCodes = array();
    if(is_array($intlMethods)) {
      foreach($intlMethods as $methodData) {
        list($code, $name) = explode('~', $methodData);
        $m = new Cart66ShippingMethod();
        $m->code = $code;
        $m->name = $name;
        $m->carrier = 'capost_intl';
        $m->save();
        $intlCodes[] = $code;
      }
    }
    else {
      $intlCodes[] = -1;
    }
    $method->pruneCarrierMethods('capost_intl', $intlCodes);
    
    $tab = 5;
  }
  elseif($_POST['cart66-action'] == 'enable live rates') {
    Cart66Setting::setValue('use_live_rates', 1);
  }
  elseif($_POST['cart66-action'] == 'disable live rates') {
    Cart66Setting::setValue('use_live_rates', '');
  }
  elseif($_POST['cart66-action'] == 'save rate tweak') {
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Saving a rate tweak");
    $factor = Cart66Common::postVal('rate_tweak_factor');
    if(is_numeric($factor)) {
      Cart66Setting::setValue('rate_tweak_factor', $factor);
      Cart66Setting::setValue('rate_tweak_type', Cart66Common::postVal('rate_tweak_type'));
    }
    else {
      Cart66Setting::setValue('rate_tweak_factor', '');
      Cart66Setting::setValue('rate_tweak_type', '');
    }
    $tab = 7;
  }
}
elseif(isset($_GET['task']) && $_GET['task'] == 'edit' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $rule->load($id);
}
elseif(isset($_GET['task']) && $_GET['task'] == 'edit_method' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $method->load($id);
}
elseif(isset($_GET['task']) && $_GET['task'] == 'edit_rate' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $rate->load($id);
}
elseif(isset($_GET['task']) && $_GET['task'] == 'delete' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $rule->load($id);
  $rule->deleteMe();
  $rule->clear();
}
elseif(isset($_GET['task']) && $_GET['task'] == 'delete_method' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $method->load($id);
  $method->deleteMe();
  $method->clear();
}
elseif(isset($_GET['task']) && $_GET['task'] == 'delete_rate' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $rate->load($id);
  $rate->deleteMe();
  $rate->clear();
}
?>
<h2>Cart66 Shipping</h2>
<div class='wrap'>

  <?php if(CART66_PRO): ?>
  <div style="padding: 10px; 25px; width: 580px; background-color: #EEE; border: 1px solid #CCC; -moz-border-radius: 5px; -webkit-border-radius: 5px;">
    <h3><?php _e( 'Live Shipping Rates' , 'cart66' ); ?></h3>
  
    <p><?php _e( 'Using live shipping rates overrides all other types of shipping settings.' , 'cart66' ); ?></p>
  
    <?php if(Cart66Setting::getValue('use_live_rates')): ?>
      <form action="" method="post">
        <p><?php _e( 'Live shipping rates are enabled.' , 'cart66' ); ?></p>
        <input type='hidden' name='cart66-action' value='disable live rates' />
        <input type="submit" name="submit" value="Disable Live Shipping Rates" id="submit" class="button-secondary" />
      </form>
    <?php else: ?>
      <form action="" method="post">
        <p><?php _e( 'Live shipping rates are not enabled.' , 'cart66' ); ?></p>
        <input type='hidden' name='cart66-action' value='enable live rates' />
        <input type="submit" name="submit" value="Enable Live Shipping Rates" id="submit" class="button-primary" />
      </form>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  
  <?php 
  if(CART66_PRO && Cart66Setting::getValue('use_live_rates')):
    require_once(CART66_PATH . "/pro/admin/shipping.php");  
  else: ?>

    <h3 style="clear: both;"><?php _e( 'Shipping Methods' , 'cart66' ); ?></h3>
    <p style="width: 400px;"><?php _e( 'Create the shipping methods you will offer your customers. If no shipping
    price is defined for a product, the default rates entered here will be used
    to calculate shipping costs.' , 'cart66' ); ?></p> 

    <form action="" method='post'>
      <input type='hidden' name='cart66-action' value='save shipping method' />
      <input type='hidden' name='shipping_method[id]' value='<?php echo $method->id ?>' />
    
      <ul>
        <li>
          <label class="med"><?php _e( 'Shipping method' , 'cart66' ); ?>:</label>
          <input type="text" name="shipping_method[name]" value="<?php echo $method->name ?>" />
          <span class="label_desc"><?php _e( 'ex. FedEx Ground' , 'cart66' ); ?></span>
        </li>
      
        <li>
          <label class="med"><?php _e( 'Default rate' , 'cart66' ); ?>:</label>
          <span><?php echo CART66_CURRENCY_SYMBOL ?></span>
          <input type="text" name="shipping_method[default_rate]" value="<?php echo $method->default_rate ?>" style='width: 80px;'/>
          <span class="label_desc"><?php _e( 'Rate if only one item is ordered' , 'cart66' ); ?></span>
        </li>

        <li>
          <label class="med">Default bunde rate:</label>
          <span><?php echo CART66_CURRENCY_SYMBOL ?></span>
          <input type="text" name="shipping_method[default_bundle_rate]" value="<?php echo $method->default_bundle_rate ?>" style='width: 80px;'/>
          <span class="label_desc"><?php _e( 'Rate for each additional item' , 'cart66' ); ?></span>
        </li>

        <li>
          <label class="med">&nbsp;</label>
          <?php if($method->id > 0): ?>
          <a href='?page=cart66-shipping' class='button-secondary linkButton' style=""><?php _e( 'Cancel' , 'cart66' ); ?></a>
          <?php endif; ?>
          <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='<?php _e( 'Save' , 'cart66' ); ?>' />
        </li>
      </ul>
    
    </form>
    
    <?php
    $methods = $method->getModels("where code IS NULL or code = ''", 'order by default_rate');
    if(count($methods)):
    ?>
    <table class="widefat" style='width: 600px;'>
    <thead>
      <tr>
    		<th><?php _e( 'Shipping Method' , 'cart66' ); ?></th>
    		<th><?php _e( 'Default rate' , 'cart66' ); ?></th>
    		<th><?php _e( 'Default bundle rate' , 'cart66' ); ?></th>
        <th>&nbsp;</th>
    	</tr>
    </thead>
    <tbody>
      <?php foreach($methods as $m): ?>
        <tr>
          <td><?php echo $m->name ?></td>
          <td><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($m->default_rate, 2); ?></td>
          <td><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($m->default_bundle_rate, 2); ?></td>
          <td>
           <a href='?page=cart66-shipping&task=edit_method&id=<?php echo $m->id ?>'><?php _e( 'Edit' , 'cart66' ); ?></a> | 
           <a class='delete' href='?page=cart66-shipping&task=delete_method&id=<?php echo $m->id ?>'><?php _e( 'Delete' , 'cart66' ); ?></a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    </table>
    <?php endif; ?>

    <h3 style="clear: both;"><?php _e( 'Product Shipping Prices' , 'cart66' ); ?></h3>

    <p style="width: 400px;"><?php _e( 'The shipping prices you set up here override the default shipping prices for the shipping methods above.' , 'cart66' ); ?></p>
    
    <?php
      $products = Cart66Product::loadProductsOutsideOfClass();
      if(count($method->getModels()) && count($products)): ?>
      <form action="" method='post'>
        <input type="hidden" name="cart66-action" value="save product rate" />
        <input type="hidden" name="rate[id]" value="<?php echo $rate->id ?>" id="rate-id" />
        <ul>
          <li>
            <label class="med"><?php _e( 'Product' , 'cart66' ); ?>:</label>
            <select name='rate[product_id]'>
              <?php foreach($products as $p): ?>
                <option value="<?php echo $p->id; ?>" <?php echo ($p->id == $rate->product_id) ? 'selected="selected"' : '' ?>><?php echo $p->name ?> (<?php echo $p->item_number ?>)</option>
              <?php endforeach; ?>
            </select>
          </li>
          <li>
            <label class="med"><?php _e( 'Shipping method' , 'cart66' ); ?>:</label>
            <select name='rate[shipping_method_id]'>
              <?php foreach($method->getModels("where carrier = ''", 'order by name') as $m): ?>
                <option value="<?php echo $m->id; ?>" <?php echo ($m->id == $rate->shipping_method_id) ? 'selected="selected"' : '' ?>><?php echo $m->name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
          <li>
            <label class="med"><?php _e( 'Shipping rate' , 'cart66' ); ?>:</label>
            <span><?php echo CART66_CURRENCY_SYMBOL ?></span>
            <input type="text" style="width: 80px;" name="rate[shipping_rate]" value="<?php echo $rate->shipping_rate ?>" />
          </li>
          <li>
            <label class="med">Shipping bundle rate:</label>
            <span><?php echo CART66_CURRENCY_SYMBOL ?></span>
            <input type="text" style="width: 80px;" name="rate[shipping_bundle_rate]" value="<?php echo $rate->shipping_bundle_rate ?>" />
          </li>
          <li>
            <label class="med">&nbsp;</label>
            <?php if($rate->id > 0): ?>
              <a href='?page=cart66-shipping' class='button-secondary linkButton' style="">Cancel</a>
            <?php endif; ?>
            <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' />
          </li>
        </ul>
      </form>
    <?php else: ?>
      <p style="color: red;"><?php _e( 'You must enter at least one shipping method and at least one product for these setting to appear.' , 'cart66' ); ?></p>
    <?php endif; ?>

    <?php
    $rates = $rate->getModels(null, 'order by product_id, shipping_method_id');
    if(count($rates)):
    ?>
    <table class="widefat" style='width: auto;'>
    <thead>
      <tr>
    		<th><?php _e( 'Product' , 'cart66' ); ?></th>
    		<th><?php _e( 'Shipping method' , 'cart66' ); ?></th>
    		<th><?php _e( 'Rate' , 'cart66' ); ?></th>
    		<th><?php _e( 'Bundle rate' , 'cart66' ); ?></th>
        <th>&nbsp;</th>
    	</tr>
    </thead>
    <tbody>
      <?php foreach($rates as $r): ?>
        <?php 
          $product->load($r->product_id);
          $method->load($r->shipping_method_id);
        ?>
        <tr>
          <td><?php echo $product->item_number ?> <?php echo $product->name ?></td>
          <td><?php echo $method->name ?></td>
          <td><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($r->shipping_rate, 2); ?></td>
          <td><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($r->shipping_bundle_rate, 2); ?></td>
          <td>
           <a href='?page=cart66-shipping&task=edit_rate&id=<?php echo $r->id ?>'><?php _e( 'Edit' , 'cart66' ); ?></a> | 
           <a class='delete' href='?page=cart66-shipping&task=delete_rate&id=<?php echo $r->id ?>'><?php _e( 'Delete' , 'cart66' ); ?></a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    </table>
    <?php endif; ?>
    <h3 style="clear: both;"><?php _e( 'Cart Price Shipping Rates' , 'cart66' ); ?></h3>

    <p style='width: 400px;'><?php _e( 'You can set the shipping cost based on the total cart value. For example, you 
      may want to offer free shipping on orders over $50. To do that set minimum cart amount to $50 and the
      shipping cost to $0.' , 'cart66' ); ?></p> 
    <p style='width: 400px;'><?php _e( 'You can also set up tiered shipping costs based on the cart amount. For example,
      if you want to charge $10 shipping on orders between $0 - $24.99 and $5 shipping on orders between $25 - $49.99
      and free shipping on orders $50 or more you would set that up with three shipping rules as follows.' , 'cart66' ); ?></p>
    
    <table style='width: 400px; margin-bottom: 20px;'>
      <tr>
        <th style='text-align: left;'><?php _e( 'Minimum cart amount' , 'cart66' ); ?></th>
        <th style='text-align: left;'><?php _e( 'Shipping cost' , 'cart66' ); ?></th>
      </tr>
      <tr>
        <td>$0</td>
        <td>$10</td>
      </tr>
      <tr>
        <td>$25</td>
        <td>$5</td>
      </tr>
      <tr>
        <td>$50</td>
        <td>$0</td>
      </tr>
    </table>

    <?php if(count($method->getModels())): ?>
    <form action="" method='post'>
      <input type='hidden' name='cart66-action' value='save rule' />
      <input type='hidden' name='rule[id]' value='<?php echo $rule->id ?>' />
      <ul>
        <li>
          <label for="rule-min_amount"><?php _e( 'Minimum cart amount' , 'cart66' ); ?>:</label>
          <span><?php echo CART66_CURRENCY_SYMBOL ?></span>
          <input type='text' name='rule[min_amount]' id='rule-min_amount' style='width: 80px;' value='<?php echo $rule->minAmount ?>' />
        </li>
        <li>
          <label class="med"><?php _e( 'Shipping method' , 'cart66' ); ?>:</label>
          <select name="rule[shipping_method_id]">
            <?php foreach($method->getModels(null, 'name') as $m): ?>
              <option value="<?php echo $m->id; ?>"><?php echo $m->name ?></option>
            <?php endforeach; ?>
          </select>
        </li>
        <li>
          <label class="med" for="rule-shipping_cost"><?php _e( 'Shipping cost' , 'cart66' ); ?>:</label>
          <span><?php echo CART66_CURRENCY_SYMBOL ?></span>
          <input type="text" id="rule-shipping_cost" name="rule[shipping_cost]" style='width: 80px;' value='<?php echo $rule->shippingCost ?>'>
        </li>
        <li>
          <label class="med">&nbsp;</label>
          <?php if($rule->id > 0): ?>
          <a href='?page=cart66-shipping' class='button-secondary linkButton' style=""><?php _e( 'Cancel' , 'cart66' ); ?></a>
          <?php endif; ?>
          <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' />
        </li>
      </ul>
    </form>
    <?php else: ?>
      <p style='color: red;'><?php _e( 'You must have entered at least one shipping method before you can configure these settings.' , 'cart66' ); ?></p>
    <?php endif; ?>
  
    <?php
    $rules = $rule->getModels(null, 'order by min_amount');
    if(count($rules)):
    ?>
      <table class="widefat" style='width: auto;'>
        <thead>
        	<tr>
        		<th><?php _e( 'Minimum cart amount' , 'cart66' ); ?></th>
        		<th><?php _e( 'Shipping method' , 'cart66' ); ?></th>
        		<th><?php _e( 'Shipping cost' , 'cart66' ); ?></th>
        		<th><?php _e( 'Actions' , 'cart66' ); ?></th>
        	</tr>
        </thead>
        <tbody>
          <?php foreach($rules as $rule): ?>
            <?php 
              $method = new Cart66ShippingMethod();
              $method->load($rule->shipping_method_id);
            ?>
           <tr>
             <td><?php echo CART66_CURRENCY_SYMBOL ?><?php echo $rule->min_amount ?></td>
             <td><?php echo ($method->name) ? $method->name : "<span style='color:red;'>Please select a method</span>"; ?></td>
             <td><?php echo CART66_CURRENCY_SYMBOL ?><?php echo $rule->shipping_cost ?></td>
             <td>
               <a href='?page=cart66-shipping&task=edit&id=<?php echo $rule->id ?>'><?php _e( 'Edit' , 'cart66' ); ?></a> | 
               <a class='delete' href='?page=cart66-shipping&task=delete&id=<?php echo $rule->id ?>'><?php _e( 'Delete' , 'cart66' ); ?></a>
             </td>
           </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
    
  <?php endif; ?>

  
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
    $('.what_is').click(function() {
      $('#' + $(this).attr('id') + '_answer').toggle('slow');
      return false;
    });

    $('.delete').click(function() {
      return confirm('Are you sure you want to delete this entry?');
    });
  })(jQuery);
</script>