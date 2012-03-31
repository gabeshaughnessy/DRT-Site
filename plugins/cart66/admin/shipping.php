<?php
$rule = new Cart66ShippingRule();
$method = new Cart66ShippingMethod();
$rate = new Cart66ShippingRate();
$product = new Cart66Product();

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
  elseif($_POST['cart66-action'] == 'save ups account info') {
    foreach($_POST['ups'] as $key => $value) {
      Cart66Setting::setValue($key, $value);
    }
    $methods = $_POST['ups_methods'];
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
  }
  elseif($_POST['cart66-action'] == 'enable live rates') {
    Cart66Setting::setValue('use_live_rates', 1);
  }
  elseif($_POST['cart66-action'] == 'disable live rates') {
    Cart66Setting::setValue('use_live_rates', '');
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
<h2>Cart66 Settings</h2>
<div class='wrap'>

  <?php if(CART66_PRO): ?>
  <div style="padding: 10px; 25px; width: 600px; background-color: #EEE; border: 1px solid #CCC; -moz-border-radius: 5px; -webkit-border-radius: 5px;">
    <h3>Live Shipping Rates</h3>
  
    <p>Using live shipping rates overrides all other types of shipping settings.</p>
  
    <?php if(Cart66Setting::getValue('use_live_rates')): ?>
      <form action="" method='post'>
        <p>Live shipping rates are enabled.</p>
        <input type='hidden' name='cart66-action' value='disable live rates' />
        <input type="submit" name="submit" value="Disable Live Shipping Rates" id="submit" class="button-secondary" />
      </form>
    <?php else: ?>
      <form action="" method='post'>
        <p>Live shipping rates are not enabled.</p>
        <input type='hidden' name='cart66-action' value='enable live rates' />
        <input type="submit" name="submit" value="Enable Live Shipping Rates" id="submit" class="button-primary" />
      </form>
  
    <?php endif; ?>
  </div>
  <?php endif; ?>
  
  <?php if(CART66_PRO && Cart66Setting::getValue('use_live_rates')): ?>
  
    <h3 style="clear: both;">UPS Shipping Account Information</h3>
    <p>If you intend to use UPS real-time shipping quotes please provide your UPS account information.</p>
    <form action="" method='post'>
      <input type='hidden' name='cart66-action' value='save ups account info' />
      <ul>
        <li>
          <label class="med">Username:</label>
          <input type='text' name='ups[ups_username]' id='ups_username' value='<?php echo Cart66Setting::getValue('ups_username'); ?>' />
        </li>
        <li>
          <label class="med">Password:</label>
          <input type='text' name='ups[ups_password]' id='ups_password' value='<?php echo Cart66Setting::getValue('ups_password'); ?>' />
        </li>
        <li>
          <label class="med">API Key:</label>
          <input type='text' name='ups[ups_apikey]' id='ups_apikey' value='<?php echo Cart66Setting::getValue('ups_apikey'); ?>' />
        </li>
        <li>
          <label class="med">Account number:</label>
          <input type='text' name='ups[ups_account]' id='ups_account' value='<?php echo Cart66Setting::getValue('ups_account'); ?>' />
        </li>
        <li>
          <label class="med">Ship from zip:</label>
          <input type='text' name='ups[ups_ship_from_zip]' id='ups_ship_from_zip' value='<?php echo Cart66Setting::getValue('ups_ship_from_zip'); ?>' />
        </li>
        <li>
          <p>Select the UPS shipping methods you would like to offer to your customers.</p>
          <label class="med">&nbsp;</label> <a href="#" id="clear_all">Clear All</a> | <a href="#" id="select_all">Select All</a>
        </li>
        <li>
          <?php
            $services = Cart66ProCommon::getUpsServices();
            $methods = $method->getMethodsForCarrier('ups');
            foreach($services as $name => $code) {
              $checked = '';
              if(in_array($code, $methods)) {
                $checked = 'checked="checked"';
              }
              echo '<label class="med">&nbsp;</label>';
              echo "<input type='checkbox' class='shipping_options' name='ups_methods[]' value='$code~$name' $checked> $name<br/>";
            }
          ?>
        </li>
        <li>
          <label class="med">&nbsp;</label>
          <input type='submit' name='submit' class="button-primary" style='width: 60px; margin-top: 10px;' value='Save' />
        </li>
      </ul>
    </form>
  
  <?php else: ?>
  
    <h3 style="clear: both;">Shipping Methods</h3>

    <p style="width: 400px;">Create the shipping methods you will offer your customers. If no shipping
    price is defined for a product, the default rates entered here will be used
    to calculate shipping costs.</p> 

    <form action="" method='post'>
      <input type='hidden' name='cart66-action' value='save shipping method' />
      <input type='hidden' name='shipping_method[id]' value='<?php echo $method->id ?>' />
    
      <ul>
        <li>
          <label class="med">Shipping method:</label>
          <input type="text" name="shipping_method[name]" value="<?php echo $method->name ?>" />
          <span class="label_desc">ex. FedEx Ground</span>
        </li>
      
        <li>
          <label class="med">Default rate:</label>
          <span><?php echo CURRENCY_SYMBOL ?></span>
          <input type="text" name="shipping_method[default_rate]" value="<?php echo $method->default_rate ?>" style='width: 80px;'/>
          <span class="label_desc">Rate if only one item is ordered</span>
        </li>

        <li>
          <label class="med">Default bunde rate:</label>
          <span><?php echo CURRENCY_SYMBOL ?></span>
          <input type="text" name="shipping_method[default_bundle_rate]" value="<?php echo $method->default_bundle_rate ?>" style='width: 80px;'/>
          <span class="label_desc">Rate for each additional item</span>
        </li>

        <li>
          <label class="med">&nbsp;</label>
          <?php if($method->id > 0): ?>
          <a href='?page=cart66-shipping' class='button-secondary linkButton' style="">Cancel</a>
          <?php endif; ?>
          <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' />
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
    		<th>Shipping Method</th>
    		<th>Default rate</th>
    		<th>Default bundle rate</th>
        <th>&nbsp;</th>
    	</tr>
    </thead>
    <tbody>
      <?php foreach($methods as $m): ?>
        <tr>
          <td><?php echo $m->name ?></td>
          <td><?php echo CURRENCY_SYMBOL ?><?php echo number_format($m->default_rate, 2); ?></td>
          <td><?php echo CURRENCY_SYMBOL ?><?php echo number_format($m->default_bundle_rate, 2); ?></td>
          <td>
           <a href='?page=cart66-shipping&task=edit_method&id=<?php echo $m->id ?>'>Edit</a> | 
           <a class='delete' href='?page=cart66-shipping&task=delete_method&id=<?php echo $m->id ?>'>Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    </table>
    <?php endif; ?>

    <h3 style="clear: both;">Product Shipping Prices</h3>

    <p style="width: 400px;">The shipping prices you set up here override the default shipping prices for the shipping methods above.</p>

    <?php if(count($method->getModels()) && count($product->getModels())): ?>
      <form action="" method='post'>
        <input type="hidden" name="cart66-action" value="save product rate" />
        <input type="hidden" name="rate[id]" value="<?php echo $rate->id ?>" id="rate-id" />
        <ul>
          <li>
            <label class="med">Product:</label>
            <select name='rate[product_id]'>
              <?php foreach($product->getModels(null, 'name') as $p): ?>
                <option value="<?php echo $p->id; ?>" <?php echo ($p->id == $rate->product_id) ? 'selected="selected"' : '' ?>><?php echo $p->item_number ?> <?php echo $p->name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
          <li>
            <label class="med">Shipping method:</label>
            <select name='rate[shipping_method_id]'>
              <?php foreach($method->getModels(null, 'name') as $m): ?>
                <option value="<?php echo $m->id; ?>" <?php echo ($m->id == $rate->shipping_method_id) ? 'selected="selected"' : '' ?>><?php echo $m->name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
          <li>
            <label class="med">Shipping rate:</label>
            <span><?php echo CURRENCY_SYMBOL ?></span>
            <input type="text" style="width: 80px;" name="rate[shipping_rate]" value="<?php echo $rate->shipping_rate ?>" />
          </li>
          <li>
            <label class="med">Shipping bundle rate:</label>
            <span><?php echo CURRENCY_SYMBOL ?></span>
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
      <p style="color: red;">You must enter at least one shipping method and at least one product for these setting to appear.</p>
    <?php endif; ?>

    <?php
    $rates = $rate->getModels(null, 'order by product_id, shipping_method_id');
    if(count($rates)):
    ?>
    <table class="widefat" style='width: auto;'>
    <thead>
      <tr>
    		<th>Product</th>
    		<th>Shippig method</th>
    		<th>Rate</th>
    		<th>Bundle rate</th>
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
          <td><?php echo CURRENCY_SYMBOL ?><?php echo number_format($r->shipping_rate, 2); ?></td>
          <td><?php echo CURRENCY_SYMBOL ?><?php echo number_format($r->shipping_bundle_rate, 2); ?></td>
          <td>
           <a href='?page=cart66-shipping&task=edit_rate&id=<?php echo $r->id ?>'>Edit</a> | 
           <a class='delete' href='?page=cart66-shipping&task=delete_rate&id=<?php echo $r->id ?>'>Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    </table>
    <?php endif; ?>
    <h3 style="clear: both;">Cart Price Shipping Rates</h3>

    <p style='width: 400px;'>You can set the shipping cost based on the total cart value. For example, you 
      may want to offer free shipping on orders over $50. To do that set minimum cart amount to $50 and the
      shipping cost to $0.</p> 
    <p style='width: 400px;'>You can also set up tiered shipping costs based on the cart amount. For example,
      if you want to charge $10 shipping on orders between $0 - $24.99 and $5 shipping on orders between $25 - $49.99
      and free shipping on orders $50 or more you would set that up with three shipping rules as follows.</p>
    
    <table style='width: 400px; margin-bottom: 20px;'>
      <tr>
        <th style='text-align: left;'>Minimum cart amount</th>
        <th style='text-align: left;'>Shipping cost</th>
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
          <label for='rule[cart_amount]'>Minimum cart amount:</label>
          <span><?php echo CURRENCY_SYMBOL ?></span>
          <input type='text' name='rule[min_amount]' id='cart_amount' style='width: 80px;' value='<?php echo $rule->minAmount ?>' />
        </li>
        <li>
          <label class="med">Shipping method:</label>
          <select name='rule[shipping_method_id]'>
            <?php foreach($method->getModels(null, 'name') as $m): ?>
              <option value="<?php echo $m->id; ?>"><?php echo $m->name ?></option>
            <?php endforeach; ?>
          </select>
        </li>
        <li>
          <label class="med" for='rule[cost]'>Shipping cost:</label>
          <span><?php echo CURRENCY_SYMBOL ?></span>
          <input type='text' name='rule[shipping_cost]' style='width: 80px;' value='<?php echo $rule->shippingCost ?>'>
        </li>
        <li>
          <label class="med">&nbsp;</label>
          <?php if($rule->id > 0): ?>
          <a href='?page=cart66-shipping' class='button-secondary linkButton' style="">Cancel</a>
          <?php endif; ?>
          <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' />
        </li>
      </ul>
    </form>
    <?php else: ?>
      <p style='color: red;'>You must have entered at least one shipping method before you can configure these settings.</p>
    <?php endif; ?>
  
    <?php
    $rules = $rule->getModels(null, 'order by min_amount');
    if(count($rules)):
    ?>
      <table class="widefat" style='width: auto;'>
        <thead>
        	<tr>
        		<th>Minimum cart amount</th>
        		<th>Shipping method</th>
        		<th>Shipping cost</th>
        		<th>Actions</th>
        	</tr>
        </thead>
        <tbody>
          <?php foreach($rules as $rule): ?>
            <?php 
              $method->load($rule->shipping_method_id);
            ?>
           <tr>
             <td><?php echo CURRENCY_SYMBOL ?><?php echo $rule->min_amount ?></td>
             <td><?php echo $method->name ?></td>
             <td><?php echo CURRENCY_SYMBOL ?><?php echo $rule->shipping_cost ?></td>
             <td>
               <a href='?page=cart66-shipping&task=edit&id=<?php echo $rule->id ?>'>Edit</a> | 
               <a class='delete' href='?page=cart66-shipping&task=delete&id=<?php echo $rule->id ?>'>Delete</a>
             </td>
           </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
    
  <?php endif; ?>

  
</div>

<script type='text/javascript'>
  $jq = jQuery.noConflict();
  
  $jq(document).ready(function() {
    
    $jq('#clear_all').click(function() {
      $jq('.shipping_options').attr('checked', false);
      return false;
    });
    
    $jq('#select_all').click(function() {
      $jq('.shipping_options').attr('checked', true);
      return false;
    });
    
  });
  
  $jq('.delete').click(function() {
    return confirm('Are you sure you want to delete this entry?');
  });
</script>