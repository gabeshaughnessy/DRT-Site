<h2>PayPal Subscriptions</h2>

<div style="margin-right: 50px; margin-left: 5px;">
<?php
if(isset($data['errors'])) {
  echo Cart66Common::showErrors($data['errors'], 'Subscription could not be saved for the following reasons:');
}
?>
</div>

<div id="widgets-left" style="margin-right: 50px;">
  <div id="available-widgets">
    
    <div class="widgets-holder-wrap">
      
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>PayPal Subscription Information <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <?php if(CART66_PRO): ?>
        <form method="post" action="">
          <input type="hidden" name="cart66-action" value="save paypal subscription" id="cart66-action" />
          <input type="hidden" name="subscription[is_paypal_subscription]" value="1" id="subscription_is_paypal_subscription" />
          <input type="hidden" name="subscription[id]" value="<?php echo $data['subscription']->id ?>" id="subscription_id" />
          <ul style="margin-bottom: 15px;">
            <li>
              <label class="med" for='subscription[name]'>Subscription name:</label>
              <input class="long" type='text' name='subscription[name]' id='subscription_name' value="<?php echo htmlspecialchars($data['subscription']->name); ?>" />
            </li>
            <li>
              <label class="med" for='subscription[feature_level]'>Feature level:</label>
              <input class="long" type='text' name='subscription[feature_level]' id='subscription_feature_level' value='<?php echo $data['subscription']->featureLevel ?>' />
            </li>
            <li>
              <label class="med" for='subscription[item_number]'>Item number:</label>
              <input class="long" type='text' name='subscription[item_number]' id='subscription_item_number' value='<?php echo $data['subscription']->itemNumber ?>' />
            </li>
             <li>
                <label class="med" for='subscription[setup_fee]'>Initial payment:</label>
                <?php echo CURRENCY_SYMBOL; ?>
                <input class="med" style="width: 5em;" type='text' name='subscription[setup_fee]' id='subscription_setup_fee' value='<?php echo $data['subscription']->setupFee ?>' />
                <span class="label_desc">Payment collected at checkout <a href="#" class="what_is" id="what_is_initial_payment">more info...</a></span>
                <p id="what_is_initial_payment_answer" class="label_desc" style="max-width: 400px; display: none;">For example, this may be a setup fee. 
                  This one time fee will be charged at checkout regardless of when you decide to start collecting the recurring payments.</p>
            </li>
            <li>
              <label class="med" for='subscription[price]'>Recurring Price:</label>
              <?php echo CURRENCY_SYMBOL; ?>
              <input class="med" style="width: 5em;" type='text' name='subscription[price]' id='subscription_price' value='<?php echo $data['subscription']->price ?>' />
            </li>
            <li>
              <label class="med" for='subscription[billing_interval]'>Bill every:</label>
              <input style="width: 50px;" type='text' name='subscription[billing_interval]' id='subscription_billing_interval' value='<?php echo $data['subscription']->billingInterval ?>' />
              <select name="subscription[billing_interval_unit]">
                <option value='days'  <?php echo $data['subscription']->billingIntervalUnit == 'days' ? 'selected="selected"' : ''; ?>>Days</option>
                <option value='weeks' <?php echo $data['subscription']->billingIntervalUnit == 'weeks' ? 'selected="selected"' : ''; ?>>Weeks</option>
                <option value='months'<?php echo $data['subscription']->billingIntervalUnit == 'months' ? 'selected="selected"' : ''; ?>>Months</option>
                <option value='years' <?php echo $data['subscription']->billingIntervalUnit == 'years' ? 'selected="selected"' : ''; ?>>Years</option>
              </select>
              <span class="label_desc">How frequently would you like to charge your customer?</span>
            </li>
            <li>
              <label class="med" for='subscription[billing_cycles]'>Billing cycles:</label>
              <select name='subscription[billing_cycles]' id='subscription_billing_cycles'>
                <option value="0">Continuous</option>
                <?php for($i=1; $i<=30; $i++): ?>
                  <option value="<?php echo $i ?>" <?php echo $i == $data['subscription']->billingCycles ? 'selected="selected"' : ''; ?>>
                  <?php echo $i; ?>
                  </option>
                <?php endfor; ?>
              </select>
              <span class="label_desc"><a class="what_is" id="what_is_billing_cycles" href="#">What's this?</a></span>
              <p id="what_is_billing_cycles_answer" class="label_desc" style="display: none;">How many times do you want to bill your customer?<br/>
                Continuous billing will bill the customer until they cancel the subscription.</p>
            </li>
            
            <!-- Begin Gravity Forms Options -->
            <?php if(class_exists('RGForms')): ?>
              <li>
                <label class="med">Attach Gravity Form:</label>
                <select name='subscription[gravity_form_id]' id="subscription_gravity_form_id">
                  <option value='0'>None</option>
                  <?php
                    global $wpdb;
                    $gfIdsInUse = Cart66GravityReader::getIdsInUse();
                    $gfTitles = array();
                    $forms = Cart66Common::getTableName('rg_form', '');
                    $sql = "SELECT id, title from $forms where is_active=1 order by title";
                    $results = $wpdb->get_results($sql);
                    foreach($results as $r) {
                      $disabled = in_array($r->id, $gfIdsInUse) ? 'disabled="disabled"' : '';
                      $gfTitles[$r->id] = $r->title;
                      $selected = ($data['subscription']->gravityFormId == $r->id) ? 'selected="selected"' : '';
                      echo "<option value='$r->id' $selected $disabled>$r->title</option>";
                    }
                  ?>
                </select>
                <span class="label_desc">A Gravity Form may only be linked to one product</span>
              </li>
            <?php endif; ?>
            <!-- End Gravity Forms Options -->
            
            <li>
              <label class="med" for='subscription[offer_trial]'>Offer trial period:</label>
              <input type="checkbox" name="subscription[offer_trial]" id="subscription_offer_trial_yes" value="1"
                <?php echo $data['subscription']->offerTrial == 1 ? 'checked="checked"' : ''; ?> /> Yes
            </li>
          </ul>
          
          <ul id="trial_fields" style="margin-top: 15px; display: <?php echo $data['subscription']->offerTrial == 1 ? 'block' : 'none'; ?>;">
            <li><span style="font-weight: bold; padding: 0px 0px 0px 15px;">Set up trial period</span></li>
            <li>
              <label class="med" for='subscription[trial_period]'>Bill every:</label>
              <input class="med" style="width: 3em;" type='text' name='subscription[trial_period]' id='subscription_trial_period' value='<?php echo $data['subscription']->trialPeriod ?>' />
              <select name="subscription[trial_period_unit]" id="subscription_trial_period_unit">
                <option value='none'   <?php echo $data['subscription']->trialPeriodUnit == 'none' ? 'selected="selected"' : ''; ?>>No Trial</option>
                <option value='days'   <?php echo $data['subscription']->trialPeriodUnit == 'days' ? 'selected="selected"' : ''; ?>>Days</option>
                <option value='weeks'  <?php echo $data['subscription']->trialPeriodUnit == 'weeks' ? 'selected="selected"' : ''; ?>>Weeks</option>
                <option value='months' <?php echo $data['subscription']->trialPeriodUnit == 'months' ? 'selected="selected"' : ''; ?>>Months</option>
                <option value='years'  <?php echo $data['subscription']->trialPeriodUnit == 'years' ? 'selected="selected"' : ''; ?>>Years</option>
              </select>
              <p class="label_desc">The amount of time between billings</p>
            </li>
            <li>
              <label class="med" for='subscription[trial_price]'>Trial price:</label>
              <?php echo CURRENCY_SYMBOL; ?>
              <input class="med" style="width: 5em;" type='text' name='subscription[trial_price]' id='subscription_trial_price' 
                value='<?php echo $data['subscription']->trialPrice ?>' />
            </li>
            <li>
              <label class="med" for='subscription[trial_cycles]'>Trial cycles:</label>
              <select name='subscription[trial_cycles]' id='subscription_trial_cycles'>
                <?php for($i=1; $i<=30; $i++): ?>
                  <option value="<?php echo $i ?>" <?php echo $i == $data['subscription']->trialCycles ? 'selected="selected"' : ''; ?>>
                  <?php echo $i; ?>
                  </option>
                <?php endfor; ?>
              </select>
              <span class="label_desc"><a class="what_is" href="#" id="what_is_trial_cycles">What's this?</a></span>
              <p id="what_is_trial_cycles_answer" class="label_desc" style="width: 400px; display: none;">How many times to you want to bill your customer during the trial?
                For example, if you wanted to offer a trial price of $5.00 per month
                and wanted the trial period to last for 3 months
                you would set the following: <br/>
                Trial period: 1 Month &mdash; Trial price: $5.00 &mdash;  Trial cycles: 3.</p>
            </li>
          </ul>
          
          <ul>
            <li>
              <label class="med" for='subscription[start_recurring_number]'>Start payments in:</label>
              
              <select name="subscription[start_recurring_number]" id="subscription-start_recurring_number">
                <?php for($i=1; $i <= 31; $i++): ?>
                  <option value="<?php echo $i; ?>" <?php if($i == $data['subscription']->startRecurringNumber) { echo 'selected="selected"'; } ?>  ><?php echo $i ?></option>
                <?php endfor; ?>
              </select>
              
              <select name="subscription[start_recurring_unit]">
                <option value='days'  <?php echo $data['subscription']->startRecurringUnit == 'days' ? 'selected="selected"' : ''; ?>>Days</option>
                <option value='weeks' <?php echo $data['subscription']->startRecurringUnit == 'weeks' ? 'selected="selected"' : ''; ?>>Weeks</option>
                <option value='months'<?php echo $data['subscription']->startRecurringUnit == 'months' ? 'selected="selected"' : ''; ?>>Months</option>
                <option value='years' <?php echo $data['subscription']->startRecurringUnit == 'years' ? 'selected="selected"' : ''; ?>>Years</option>
              </select>
              <span class="label_desc"><a class="what_is" href="#" id="what_is_start_payments_in">What's this?</a></span>
              <p id="what_is_start_payments_in_answer" class="label_desc" style="max-width: 400px; display: none;">When do you want the first recurring payment to be made? The initial payment will be charged at checkout. 
                Usually, your first recurring payment is the 2nd payment you receive from the subscriber.<br/><br/>
                For example, if you are charging monthly for a service that begins immediately 
                upon checkout, collect the first monthly payment as an "initial payment" and start collecting the recurring payments in 1 month.</p>
            </li>
            <li>
              <label class="med" for='subscription[price_description]'>Price description:</label>
              <input class="long" type='text' name='subscription[price_description]' id='subscription-price_description' value="<?php echo htmlspecialchars($data['subscription']->price_description); ?>" />
              <span class="label_desc"><a class="what_is" href="#" id="what_is_price_description">What's this?</a></span>
              <p id="what_is_price_description_answer" class="label_desc" style="max-width: 400px; display:none;">(Optional) 
                If you leave this blank, Cart66 will display a price description based on the information that defines this subscription.
                You may customize that message and briefly describe how you plan to bill your customer. This will be the message that shows 
                up next to your add to cart buttons and also in the shopping cart. 
              </p>
            </li>
          </ul>
          
          <ul style="margin: 15px 0px;">
            <li>
              <label class="med" for='subscription[submit]'>&nbsp;</label>
              <?php 
                if($data['subscription']->id > 0) {
                  $url = Cart66Common::replaceQueryString('page=cart66-paypal-subscriptions');
                  echo "<a href=\"$url\" class='button-secondary linkButton'>Cancel</a>";
                }
              ?>
              <input type="submit" name="save" value="Save" id="Save" class="button-primary" style="width: 60px;"/>
            </li>
          </ul>
          
        </form>
        <?php else: ?>
          <p class="description">This feature is only available in <a href="http://cart66.com">Cart66 Professional</a></p>
        <?php endif; ?>
      </div>
    </div>
    
  </div>
</div>

<?php if(CART66_PRO && isset($data['plans']) && is_array($data['plans']) && count($data['plans'])): ?>
<h3 style="margin-top: 20px;">Your PayPal Subscription Plans</h3>
<table class="widefat" style="width: 95%">
<thead>
	<tr>
		<th>Item Number</th>
		<th>Name</th>
		<th>Level</th>
		<th>Initial Payment</th>
		<th>Price Description</th>
		<th>Additional Payments</th>
		<th>Trial</th>
		<th>Start In</th>
		<th>Actions</th>
	</tr>
</thead>
<tbody>
  <?php foreach($data['plans'] as $plan): ?>
  <tr>
    <td><?php echo $plan->itemNumber; ?></td>
    <td>
      <?php 
        echo $plan->name; 
        if($plan->gravityFormId > 0) {
          echo '<br/><em>Linked To Gravity From: ' . $gfTitles[$plan->gravityFormId] . '</em>';
        }
      ?>
    </td>
    <td><?php echo $plan->featureLevel; ?></td>
    <td><?php echo CURRENCY_SYMBOL . $plan->setupFee; ?></td>
    <td><?php echo $plan->getPriceDescription(false); ?></td>
    <td><?php echo $plan->getBillingCycleDescription(); ?></td>
    <td><?php echo $plan->getTrialPriceDescription(); ?></td>
    <td><?php echo $plan->getStartRecurringDescription(); ?></td>
    <td>
      <a href='?page=cart66-paypal-subscriptions&task=edit&id=<?php echo $plan->id ?>'>Edit</a> | 
      <a class='delete' href='?page=cart66-paypal-subscriptions&task=delete&id=<?php echo $plan->id ?>'>Delete</a>
    </td>
  </tr>
  <?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>

<script type="text/javascript">
  $cj = jQuery.noConflict();
  $cj(document).ready(function() {
    $cj('#subscription_offer_trial_yes').click(function() {
      if($cj('#subscription_offer_trial_yes').is(':checked')) {
        $cj('#trial_fields').css('display', 'block');
      }
      else {
        $cj('#trial_fields').css('display', 'none');
        $cj('#subscription_trial_price').val('');
        $cj('#subscription_trial_period').val('');
        $cj('#subscription_trial_period_unit').val('none');
      }
    });
    $cj('.delete').click(function() {
      return confirm('Are you sure you want to delete this item?');
    });
    
    $cj('.what_is').click(function() {
      $cj('#' + $cj(this).attr('id') + '_answer').toggle('slow');
      return false;
    });
  });
  
  <?php if(isset($data['jqErrors']) && is_array($data['jqErrors'])): ?>
    <?php foreach($data['jqErrors'] as $val): ?>
      $cj('#<?php echo $val ?>').addClass('Cart66ErrorField');
    <?php endforeach; ?>
  <?php endif; ?>
</script>