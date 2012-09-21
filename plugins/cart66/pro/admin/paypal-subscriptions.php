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
        <h3><?php _e( 'PayPal Subscription Information' , 'cart66' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <?php if(CART66_PRO): ?>
        <form method="post" action="">
          <input type="hidden" name="cart66-action" value="save paypal subscription" id="cart66-action" />
          <input type="hidden" name="subscription[is_paypal_subscription]" value="1" id="subscription_is_paypal_subscription" />
          <input type="hidden" name="subscription[id]" value="<?php echo $data['subscription']->id ?>" id="subscription_id" />
          <ul style="margin-bottom: 15px;">
            <li>
              <label class="med" for="subscription-name"><?php _e( 'Subscription name' , 'cart66' ); ?>:</label>
              <input class="long" type='text' name='subscription[name]' id='subscription-name' value="<?php echo htmlspecialchars($data['subscription']->name); ?>" />
            </li>
            <li>
              <label class="med" for="subscription-feature_level"><?php _e( 'Feature level' , 'cart66' ); ?>:</label>
              <input class="long" type='text' name='subscription[feature_level]' id='subscription-feature_level' value='<?php echo $data['subscription']->featureLevel ?>' />
            </li>
            <li>
              <label class="med" for="subscription-item_number"><?php _e( 'Item number' , 'cart66' ); ?>:</label>
              <input class="long" type='text' name='subscription[item_number]' id='subscription-item_number' value='<?php echo $data['subscription']->itemNumber ?>' />
            </li>
             <li>
                <label class="med" for="subscription-setup_fee"><?php _e( 'Initial payment' , 'cart66' ); ?>:</label>
                <?php echo CART66_CURRENCY_SYMBOL; ?>
                <input class="med" style="width: 5em;" type='text' name="subscription[setup_fee]" id="subscription-setup_fee" value='<?php echo $data['subscription']->setupFee ?>' />
                <span class="label_desc"><?php _e( 'Payment collected at checkout <a href="#" class="what_is" id="what_is_initial_payment">more info...</a>' , 'cart66' ); ?></span>
                <p id="what_is_initial_payment_answer" class="label_desc" style="max-width: 400px; display: none;">F<?php _e( 'or example, this may be a setup fee. This one time fee will be charged at checkout regardless of when you decide to start collecting the recurring payments.' , 'cart66' ); ?></p>
            </li>
            <li>
              <label class="med" for="subscription-price"><?php _e( 'Recurring Price' , 'cart66' ); ?>:</label>
              <?php echo CART66_CURRENCY_SYMBOL; ?>
              <input class="med" style="width: 5em;" type="text" name="subscription[price]" id="subscription-price" value="<?php echo $data['subscription']->price ?>" />
            </li>
            <li>
              <label class="med" for="subscription-billing_interval"><?php _e( 'Bill every' , 'cart66' ); ?>:</label>
              <input style="width: 50px;" type="text" name="subscription[billing_interval]" id="subscription-billing_interval" value="<?php echo $data['subscription']->billingInterval ?>" />
              <select name="subscription[billing_interval_unit]">
                <option value="days"  <?php echo $data['subscription']->billingIntervalUnit == 'days' ? 'selected="selected"' : ''; ?>><?php _e( 'Days' , 'cart66' ); ?></option>
                <option value="weeks" <?php echo $data['subscription']->billingIntervalUnit == 'weeks' ? 'selected="selected"' : ''; ?>><?php _e( 'Weeks' , 'cart66' ); ?></option>
                <option value="months"<?php echo $data['subscription']->billingIntervalUnit == 'months' ? 'selected="selected"' : ''; ?>><?php _e( 'Months' , 'cart66' ); ?></option>
                <option value="years" <?php echo $data['subscription']->billingIntervalUnit == 'years' ? 'selected="selected"' : ''; ?>><?php _e( 'Years' , 'cart66' ); ?></option>
              </select>
              <span class="label_desc"><?php _e( 'How frequently would you like to charge your customer?' , 'cart66' ); ?></span>
            </li>
            <li>
              <label class="med" for="subscription-billing_cycles"><?php _e( 'Billing cycles' , 'cart66' ); ?>:</label>
              <select name="subscription[billing_cycles]" id="subscription-billing_cycles">
                <option value="0"><?php _e( 'Continuous' , 'cart66' ); ?></option>
                <?php for($i=1; $i<=30; $i++): ?>
                  <option value="<?php echo $i ?>" <?php echo $i == $data['subscription']->billingCycles ? 'selected="selected"' : ''; ?>>
                  <?php echo $i; ?>
                  </option>
                <?php endfor; ?>
              </select>
              <span class="label_desc"><a class="what_is" id="what_is_billing_cycles" href="#"><?php _e( 'What\'s this?' , 'cart66' ); ?></a></span>
              <p id="what_is_billing_cycles_answer" class="label_desc" style="display: none;"><?php _e( 'How many times do you want to bill your customer?<br/>Continuous billing will bill the customer until they cancel the subscription.' , 'cart66' ); ?></p>
            </li>
            
            <!-- Begin Gravity Forms Options -->
            <?php if(class_exists('RGForms')): ?>
              <li>
                <label class="med" for="subscription-gravity_form_id"><?php _e( 'Attach Gravity Form' , 'cart66' ); ?>:</label>
                <select name='subscription[gravity_form_id]' id="subscription-gravity_form_id">
                  <option value='0'><?php _e( 'None' , 'cart66' ); ?></option>
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
                <span class="label_desc"><?php _e( 'A Gravity Form may only be linked to one product' , 'cart66' ); ?></span>
              </li>
            <?php endif; ?>
            <!-- End Gravity Forms Options -->
            
            <li>
              <label class="med" for="subscription-offer_trial"><?php _e( 'Offer trial period' , 'cart66' ); ?>:</label>
              <input type="checkbox" name="subscription[offer_trial]" id="subscription-offer_trial" value="1"
                <?php echo $data['subscription']->offerTrial == 1 ? 'checked="checked"' : ''; ?> /> <?php _e( 'Yes' , 'cart66' ); ?>
            </li>
          </ul>
          
          <ul id="trial_fields" style="margin-top: 15px; display: <?php echo $data['subscription']->offerTrial == 1 ? 'block' : 'none'; ?>;">
            <li><span style="font-weight: bold; padding: 0px 0px 0px 15px;"><?php _e( 'Set up trial period' , 'cart66' ); ?></span></li>
            <li>
              <label class="med" for="subscription-trial_period"><?php _e( 'Bill every' , 'cart66' ); ?>:</label>
              <input class="med" style="width: 3em;" type='text' name="subscription[trial_period]" id="subscription-trial_period" value="<?php echo $data['subscription']->trialPeriod ?>" />
              <select name="subscription[trial_period_unit]" id="subscription_trial_period_unit">
                <option value='none'   <?php echo $data['subscription']->trialPeriodUnit == 'none' ? 'selected="selected"' : ''; ?>><?php _e( 'No Trial' , 'cart66' ); ?></option>
                <option value='days'   <?php echo $data['subscription']->trialPeriodUnit == 'days' ? 'selected="selected"' : ''; ?>><?php _e( 'Days' , 'cart66' ); ?></option>
                <option value='weeks'  <?php echo $data['subscription']->trialPeriodUnit == 'weeks' ? 'selected="selected"' : ''; ?>><?php _e( 'Weeks' , 'cart66' ); ?></option>
                <option value='months' <?php echo $data['subscription']->trialPeriodUnit == 'months' ? 'selected="selected"' : ''; ?>><?php _e( 'Months' , 'cart66' ); ?></option>
                <option value='years'  <?php echo $data['subscription']->trialPeriodUnit == 'years' ? 'selected="selected"' : ''; ?>><?php _e( 'Years' , 'cart66' ); ?></option>
              </select>
              <p class="label_desc"><?php _e( 'The amount of time between billings' , 'cart66' ); ?></p>
            </li>
            <li>
              <label class="med" for="subscription-trial_price"><?php _e( 'Trial price' , 'cart66' ); ?>:</label>
              <?php echo CART66_CURRENCY_SYMBOL; ?>
              <input class="med" style="width: 5em;" type="text" name="subscription[trial_price]" id="subscription-trial_price" 
                value="<?php echo $data['subscription']->trialPrice ?>" />
            </li>
            <li>
              <label class="med" for="subscription-trial_cycles"><?php _e( 'Trial cycles' , 'cart66' ); ?>:</label>
              <select name='subscription[trial_cycles]' id="subscription-trial_cycles">
                <?php for($i=1; $i<=30; $i++): ?>
                  <option value="<?php echo $i ?>" <?php echo $i == $data['subscription']->trialCycles ? 'selected="selected"' : ''; ?>>
                  <?php echo $i; ?>
                  </option>
                <?php endfor; ?>
              </select>
              <span class="label_desc"><a class="what_is" href="#" id="what_is_trial_cycles"><?php _e( 'What\'s this?' , 'cart66' ); ?></a></span>
              <p id="what_is_trial_cycles_answer" class="label_desc" style="width: 400px; display: none;"><?php _e( 'How many times to you want to bill your customer during the trial? For example, if you wanted to offer a trial price of $5.00 per month and wanted the trial period to last for 3 months you would set the following: <br/> Trial period: 1 Month &mdash; Trial price: $5.00 &mdash;  Trial cycles: 3.' , 'cart66' ); ?></p>
            </li>
          </ul>
          
          <ul>
            <li>
              <label class="med" for="subscription-start_recurring_number"><?php _e( 'Start payments in' , 'cart66' ); ?>:</label>
              
              <select name="subscription[start_recurring_number]" id="subscription-start_recurring_number">
                <?php for($i=1; $i <= 31; $i++): ?>
                  <option value="<?php echo $i; ?>" <?php if($i == $data['subscription']->startRecurringNumber) { echo 'selected="selected"'; } ?>  ><?php echo $i ?></option>
                <?php endfor; ?>
                <option value="45" <?php if($data['subscription']->startRecurringNumber == "45") { echo 'selected="selected"'; } ?>  >45</option>
                <option value="60" <?php if($data['subscription']->startRecurringNumber == "60") { echo 'selected="selected"'; } ?>  >60</option>
              </select>
              
              <select name="subscription[start_recurring_unit]">
                <option value='days'  <?php echo $data['subscription']->startRecurringUnit == 'days' ? 'selected="selected"' : ''; ?>><?php _e( 'Days' , 'cart66' ); ?></option>
                <option value='weeks' <?php echo $data['subscription']->startRecurringUnit == 'weeks' ? 'selected="selected"' : ''; ?>><?php _e( 'Weeks' , 'cart66' ); ?></option>
                <option value='months'<?php echo $data['subscription']->startRecurringUnit == 'months' ? 'selected="selected"' : ''; ?>><?php _e( 'Months' , 'cart66' ); ?></option>
                <option value='years' <?php echo $data['subscription']->startRecurringUnit == 'years' ? 'selected="selected"' : ''; ?>><?php _e( 'Years' , 'cart66' ); ?></option>
              </select>
              <span class="label_desc"><a class="what_is" href="#" id="what_is_start_payments_in"><?php _e( 'What\'s this?' , 'cart66' ); ?></a></span>
              <p id="what_is_start_payments_in_answer" class="label_desc" style="max-width: 400px; display: none;"><?php _e( 'When do you want the first recurring payment to be made? The initial payment will be charged at checkout. Usually, your first recurring payment is the 2nd payment you receive from the subscriber.<br/><br/> For example, if you are charging monthly for a service that begins immediately upon checkout, collect the first monthly payment as an "initial payment" and start collecting the recurring payments in 1 month.' , 'cart66' ); ?></p>
            </li>
            <li>
              <label class="med" for="subscription-price_description"><?php _e( 'Price description' , 'cart66' ); ?>:</label>
              <input class="long" type='text' name="subscription[price_description]" id="subscription-price_description" value="<?php echo htmlspecialchars($data['subscription']->price_description); ?>" />
              <span class="label_desc"><a class="what_is" href="#" id="what_is_price_description"><?php _e( 'What\'s this?' , 'cart66' ); ?></a></span>
              <p id="what_is_price_description_answer" class="label_desc" style="max-width: 400px; display:none;"><?php _e( '(Optional) If you leave this blank, Cart66 will display a price description based on the information that defines this subscription. You may customize that message and briefly describe how you plan to bill your customer. This will be the message that showsup next to your add to cart buttons and also in the shopping cart.' , 'cart66' ); ?> 
              </p>
            </li>
          </ul>
          
          <ul style="margin: 15px 0px;">
            <li>
              <label class="med">&nbsp;</label>
              <?php 
                if($data['subscription']->id > 0) {
                  $url = Cart66Common::replaceQueryString('page=cart66-paypal-subscriptions');
                  echo "<a href=\"$url\" class='button-secondary linkButton'>Cancel</a>";
                }
              ?>
              <input type="submit" name="save" value="<?php _e('Save', 'cart66'); ?>" id="Save" class="button-primary" style="width: 60px;"/>
            </li>
          </ul>
          
        </form>
        <?php else: ?>
          <p class="description"><?php _e( 'This feature is only available in <a href="http://cart66.com">Cart66 Professional</a>' , 'cart66' ); ?></p>
        <?php endif; ?>
      </div>
    </div>
    
  </div>
</div>

<?php if(CART66_PRO && isset($data['plans']) && is_array($data['plans']) && count($data['plans'])): ?>
  <h3 style="margin-top: 20px;"><?php _e( 'Your PayPal Subscription Plans' , 'cart66' ); ?></h3>
  <table class="widefat Cart66HighlightTable" id="paypal_subscriptions_table">
    <tr>
      <thead>
      	<tr>
      	  <th><?php _e( 'ID' , 'cart66' ); ?></th>
      	  <th><?php _e( 'Item Number' , 'cart66' ); ?></th>
      		<th><?php _e( 'Name' , 'cart66' ); ?></th>
      		<th><?php _e( 'Level' , 'cart66' ); ?></th>
      		<th><?php _e( 'Initial Payment' , 'cart66' ); ?></th>
      		<th><?php _e( 'Price Description' , 'cart66' ); ?></th>
      		<th><?php _e( 'Additional Payments' , 'cart66' ); ?></th>
      		<th><?php _e( 'Trial' , 'cart66' ); ?></th>
      		<th><?php _e( 'Start In' , 'cart66' ); ?></th>
      		<th><?php _e( 'Actions' , 'cart66' ); ?></th>
      	</tr>
      </thead>
      <tfoot>
      	<tr>
      	  <th><?php _e( 'ID' , 'cart66' ); ?></th>
      		<th><?php _e( 'Item Number' , 'cart66' ); ?></th>
      		<th><?php _e( 'Name' , 'cart66' ); ?></th>
      		<th><?php _e( 'Level' , 'cart66' ); ?></th>
      		<th><?php _e( 'Initial Payment' , 'cart66' ); ?></th>
      		<th><?php _e( 'Price Description' , 'cart66' ); ?></th>
      		<th><?php _e( 'Additional Payments' , 'cart66' ); ?></th>
      		<th><?php _e( 'Trial' , 'cart66' ); ?></th>
      		<th><?php _e( 'Start In' , 'cart66' ); ?></th>
      		<th><?php _e( 'Actions' , 'cart66' ); ?></th>
      	</tr>
      </tfoot>
    </tr>
  </table>
<?php endif; ?>
<script type="text/javascript">
  (function($){
    $(document).ready(function(){
      
      $('#paypal_subscriptions_table').dataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bPagination": true,
        "iDisplayLength": 30,
        "aLengthMenu": [[30, 60, 150, -1], [30, 60, 150, "All"]],
        "sPaginationType": "bootstrap",
        "bAutoWidth": false,
				"sAjaxSource": ajaxurl + "?action=paypal_subscriptions_table",
        "aoColumns": [
          { "bVisible": false },
          null, null, null, null, null,null,null,null,
        //  null, 
        //  { "bsortable": true, "fnRender": function(oObj) { return '<a href="?page=cart66-products&task=edit&id=' + oObj.aData[0] + '">' + oObj.aData[1] + '</a>'}},
        //  null, null, 
        //  { "bSearchable": false }, 
        //  { "bSearchable": false },
          { "bSearchable": false, "bSortable": false, "fnRender": function(oObj) { return '<a href="?page=cart66-paypal-subscriptions&task=edit&id=' + oObj.aData[0] + '"><?php _e( "Edit" , "cart66" ); ?></a> | <a class="delete" href="?page=cart66-paypal-subscriptions&task=delete&id=' + oObj.aData[0] + '"><?php _e( "Delete" , "cart66" ); ?></a>' }
        }],
        "oLanguage": { 
          "sZeroRecords": "<?php _e('No matching PayPal Subscriptions found', 'cart66'); ?>", 
          "sSearch": "<?php _e('Search', 'cart66'); ?>:", 
          "sInfo": "<?php _e('Showing', 'cart66'); ?> _START_ <?php _e('to', 'cart66'); ?> _END_ <?php _e('of', 'cart66'); ?> _TOTAL_ <?php _e('entries', 'cart66'); ?>", 
          "sInfoEmpty": "<?php _e('Showing 0 to 0 of 0 entries', 'cart66'); ?>", 
          "oPaginate": {
            "sNext": "<?php _e('Next', 'cart66'); ?>", 
            "sPrevious": "<?php _e('Previous', 'cart66'); ?>", 
            "sLast": "<?php _e('Last', 'cart66'); ?>", 
            "sFirst": "<?php _e('First', 'cart66'); ?>"
          }, 
          "sInfoFiltered": "(<?php _e('filtered from', 'cart66'); ?> _MAX_ <?php _e('total entries', 'cart66'); ?>)", 
          "sLengthMenu": "<?php _e('Show', 'cart66'); ?> _MENU_ <?php _e('entries', 'cart66'); ?>", 
          "sLoadingRecords": "<?php _e('Loading', 'cart66'); ?>...", 
          "sProcessing": "<?php _e('Processing', 'cart66'); ?>..." 
        }
      }).css('width', '');
      $("#subscription-feature_level").keydown(function(e) {
        if (e.keyCode == 32) {
          $(this).val($(this).val() + ""); // append '-' to input
          return false; // return false to prevent space from being added
        }
      }).change(function(e) {
          $(this).val(function (i, v) { return v.replace(/ /g, ""); }); 
      });

      $('#subscription-offer_trial').click(function() {
        if($('#subscription-offer_trial').is(':checked')) {
          $('#trial_fields').css('display', 'block');
        }
        else {
          $('#trial_fields').css('display', 'none');
          $('#subscription_trial_price').val('');
          $('#subscription_trial_period').val('');
          $('#subscription_trial_period_unit').val('none');
        }
      });
      $('.delete').live('click', function() {
        return confirm('Are you sure you want to delete this item?');
      });

      $('.what_is').click(function() {
        $('#' + $(this).attr('id') + '_answer').toggle('slow');
        return false;
      });
    })
    <?php if(isset($data['jqErrors']) && is_array($data['jqErrors'])): ?>
      <?php foreach($data['jqErrors'] as $val): ?>
        $('#<?php echo $val ?>').addClass('Cart66ErrorField');
      <?php endforeach; ?>
    <?php endif; ?>
  })(jQuery);
</script> 