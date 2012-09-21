<?php
$account = new Cart66Account();
$data['accounts'] = $account->getModels('where id>0', 'order by last_name', '1');
?>
<form action="<?php echo Cart66Common::replaceQueryString('page=cart66-accounts'); ?>" method='post'>
  <input type='hidden' name='cart66-action' value='save account' />
  <input type='hidden' name='account[id]' value='<?php echo $data['account']->id ?>' />
  <input type='hidden' name='plan[id]' value='<?php if(isset($data['plan'])) { echo $data['plan']->id; } ?>' />
  
  <div id="widgets-left" style="margin-right: 50px;">
    <div id="available-widgets">
    
      <div class="widgets-holder-wrap">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3><?php _e( 'Account' , 'cart66' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder" style="overflow: hidden; display: block;">
          
          <?php
            if(isset($data['errors']) && is_array($data['errors']) && count($data['errors']) > 0) {
              echo '<div style="padding: 10px;">';
              echo Cart66Common::showErrors($data['errors'], 'Unable to update account:');
              echo '</div>';
            }
          ?>
          
          <ul style="float: left;">
            <li>
              <label class="med" for="account-first_name"><?php _e( 'First name' , 'cart66' ); ?>:</label>
              <input type="text" name="account[first_name]" value="<?php echo $data['account']->firstName; ?>" id="account-first_name" />
            </li>
            <li>
              <label class="med" for="account-last_name"><?php _e( 'Last name' , 'cart66' ); ?>:</label>
              <input type="text" name="account[last_name]" value="<?php echo $data['account']->lastName; ?>" id="account-last_name" />
            </li>
            <li>
              <label class="med" for="account-email"><?php _e( 'Email' , 'cart66' ); ?>:</label>
              <input type="text" name="account[email]" value="<?php echo $data['account']->email; ?>" id="account-email" />
            </li>
            <li>
              <label class="med" for="account-username"><?php _e( 'Username' , 'cart66' ); ?>:</label>
              <input type="text" name="account[username]" value="<?php echo $data['account']->username; ?>" id="account-username" />
            </li>
            <li>
              <label class="med" for="account-password"><?php _e( 'Password' , 'cart66' ); ?>:</label>
              <input type="text" name="account[password]" value="" id="account-password" />
              <?php if($data['account']->id > 0): ?>
                <p class="label_desc"><?php _e( 'Leave blank unless changing' , 'cart66' ); ?></p>
              <?php endif; ?>
            </li>
            <?php if($data['account']->id > 0): ?>
              <li>
                <label class="med" for="account-password"><?php _e( 'Email Status' , 'cart66' ); ?>:</label> <span class="label_desc">
                <?php echo ($data['account']->opt_out) ? '<a href="?page=cart66-accounts&accountId=' . $data['account']->id . '&opt_out=0">' . __('Subscribe', 'cart66') . '</a> - ' . __('Unsubscribed', 'cart66') : __('Subscribed', 'cart66') . ' - <a href="?page=cart66-accounts&accountId=' . $data['account']->id . '&opt_out=1">' . __('Unsubscribe', 'cart66') . '</a>'; ?></span>
              </li>
            <?php endif; ?>
          </ul>
          
          <ul style="float: left;">
            <li>
              <label class="med" for="plan-product_id"><?php _e( 'Product' , 'cart66' ); ?>:</label>
              <select style="width: 375px;" name="plan[product_id]" id="plan-product_id">
                <option value="">Select a Membership or Subscription Product</option>
                <?php
                  $membership_products =  Cart66Product::getMembershipProducts();
                  $subscription_products = Cart66Product::getSubscriptionProducts(" WHERE spreedly_subscription_id=NULL OR spreedly_subscription_id=0");
                  $products_than_need_an_account = array_merge($membership_products,$subscription_products);
                  if(!empty($products_than_need_an_account)){
                    foreach($products_than_need_an_account as $member_product) : ?>
                    <option value="<?php echo $member_product->id; ?>"><?php echo $member_product->name; ?> (<?php echo $member_product->feature_level; ?>)</option>
                    <?php endforeach; 
                  }
                  if($sub = $data['account']->getCurrentAccountSubscription(true)): ?>
                    <?php if($sub->isSpreedlySubscription()): ?>
                      <option value="spreedly_subscription" selected="selected"><?php _e('Spreedly Account', 'cart66'); ?></option>
                    <?php endif; ?>
                  <?php endif; ?>
              </select>
            </li>
            <li>
              <label class="med" for="plan-active_until"><?php _e( 'Active until' , 'cart66' ); ?>:</label>
              <input type="text" id="plan-active_until" name="plan[active_until]" value="<?php echo $data['activeUntil']; ?>" id="plan-active_until" /> 
              <input type="hidden" name="plan[lifetime]" value="" />
              <input type="checkbox" id="plan-lifetime" name="plan[lifetime]" value="1" <?php echo $data['plan']->lifetime == 1 ? 'checked="checked"' : '' ?>> <?php _e( 'Lifetime' , 'cart66' ); ?>
              <p class="label_desc" style="margin-bottom: 20px;"><?php _e( 'Enter dates like mm/dd/YYYY or time spans like +3 months.<br/>If this is a lifetime membership just click the Lifetime checkbox.' , 'cart66' ); ?></p>
            </li>
            <li><label class="med" for='account-notes'><?php _e( 'Notes' , 'cart66' ); ?>:</label><br/>
            <textarea style="width: 375px; height: 140px; margin-left: 130px; margin-top: -20px;" 
            id="account-notes" name="account[notes]"><?php echo $data['account']->notes; ?></textarea>
            <p style="margin-left: 130px;" class="description"><?php _e( 'Notes about this account - not viewable by customer.' , 'cart66' ); ?></p></li>
          </ul>
          
          <ul style="clear: both;">
            <li>
              <label class="med" for="submit">&nbsp;</label>
              <?php if($data['account']->id > 0): ?>
                <a href='?page=cart66-accounts' class='button-secondary linkButton' style=""><?php _e( 'Cancel' , 'cart66' ); ?></a>
              <?php endif; ?>
              <input type="submit" name="Save" value="<?php _e('Save', 'cart66'); ?>" id="submit" class="button-primary" style='width: 60px;' />
            </li>
          </ul>
          
        </div>
      </div>
      
    </div>
  </div>
</form>
<?php if(isset($data['accounts']) && is_array($data['accounts'])): ?>
  <table class="widefat Cart66HighlightTable" style="width: 95%" id="accounts_table">
    <tr>
      <thead>
      	<tr>
      		<th><?php _e( 'ID', 'cart66' ); ?></th>
    			<th><?php _e( 'Name' , 'cart66' ); ?></th>
          <th><?php _e( 'Username' , 'cart66' ); ?></th>
          <th><?php _e( 'Email' , 'cart66' ); ?></th>
          <th><?php _e( 'Subscription Name' , 'cart66' ); ?></th>
          <th><?php _e( 'Feature Level' , 'cart66' ); ?></th>
          <th><?php _e( 'Active Until' , 'cart66' ); ?></th>
          <th><?php _e( 'Type' , 'cart66' ); ?></th>
          <th><?php _e( 'Notes' , 'cart66' ); ?></th>
          <th><?php _e( 'Actions' , 'cart66' ); ?></th>
      	</tr>
      </thead>
      <tfoot>
      	<tr>
      		<th><?php _e( 'ID', 'cart66' ); ?></th>
    			<th><?php _e( 'Name' , 'cart66' ); ?></th>
          <th><?php _e( 'Username' , 'cart66' ); ?></th>
          <th><?php _e( 'Email' , 'cart66' ); ?></th>
          <th><?php _e( 'Subscription Name' , 'cart66' ); ?></th>
          <th><?php _e( 'Feature Level' , 'cart66' ); ?></th>
          <th><?php _e( 'Active Until' , 'cart66' ); ?></th>
          <th><?php _e( 'Type' , 'cart66' ); ?></th>
          <th><?php _e( 'Notes' , 'cart66' ); ?></th>
          <th><?php _e( 'Actions' , 'cart66' ); ?></th>
      	</tr>
      </tfoot>
    </tr>
  </table>

  <script type="text/javascript">
    (function($){
      $(document).ready(function(){
        $('#accounts_table').dataTable({
          "bProcessing": true,
          "bServerSide": true,
          "bPagination": true,
          "iDisplayLength": 30,
          "aLengthMenu": [[30, 60, 150, -1], [30, 60, 150, "All"]],
          "sPaginationType": "bootstrap",
          "bAutoWidth": false,
  				"sAjaxSource": ajaxurl + "?action=accounts_table",
  				"aaSorting": [[0,'desc']],
          "aoColumns": [
            { "bVisible": false },
            { "bsortable": true, "fnRender": function(oObj) { return '<a href="?page=cart66-accounts&accountId=' + oObj.aData[0] + '">' + oObj.aData[1] + '</a>'} }, 
            null,
            { "fnRender": function(oObj) { return '<a href="mailto:' + oObj.aData[3] + '">' + oObj.aData[3] + '</a>' } },
            null, null, null, null,
            { "bVisible": false },
            null,
          ],
          "aoColumnDefs": [
  				  { "bSearchable": false, "bSortable": false, "fnRender": function(oObj) { return oObj.aData[8] != "" ? '<a href="?page=cart66-accounts&accountId=' + oObj.aData[0] + '"><?php _e( "Edit" , "cart66" ); ?></a> | <a class="delete" href="?page=cart66-accounts&accountId=' + oObj.aData[0] + '&cart66-action=delete_account"><?php _e( "Delete" , "cart66" ); ?></a> | <a href="#" class="Cart66ViewAccountNote" rel="note_' + oObj.aData[0] + '"><?php _e( "Notes" , "cart66" ); ?></a><div class="Cart66AccountNote" id="note_' + oObj.aData[0] + '"><a href="#" class="Cart66CloseNoteView" rel="note_' + oObj.aData[0] + '" alt="Close Notes Window"><img src="<?php echo CART66_URL ?>/images/window-close.png" /></a><h3>' + oObj.aData[1] + '</h3><p>' + oObj.aData[8] + '</p></div>' : '<a href="?page=cart66-accounts&accountId=' + oObj.aData[0] + '"><?php _e( "Edit" , "cart66" ); ?></a> | <a class="delete" href="?page=cart66-accounts&accountId=' + oObj.aData[0] + '&cart66-action=delete_account"><?php _e( "Delete" , "cart66" ); ?></a>'; },"aTargets": [ 9 ]}
  				],
          "oLanguage": { 
            "sZeroRecords": "<?php _e('No matching Accounts found', 'cart66'); ?>", 
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
        $('.Cart66ViewAccountNote').live('click', function () {
          var id = $(this).attr('rel');
          $('#' + id).show();
          return false;
        });
        $('.Cart66CloseNoteView').live('click', function () {
          var id = $(this).attr('rel');
          $('#' + id).hide();
          return false;
        });
        
        $('.delete').live('click', function() {
          return confirm('Are you sure you want to permanently delete this account?');
        });

        $("#plan-feature_level").keydown(function(e) {
          if (e.keyCode == 32) {
            $(this).val($(this).val() + ""); // append '-' to input
            return false; // return false to prevent space from being added
          }
        }).change(function(e) {
            $(this).val(function (i, v) { return v.replace(/ /g, ""); }); 
        });
     
        <?php if(isset($_POST['plan']['product_id']) && !empty($_POST['plan']['product_id']) && !isset($data['just_saved'])) : ?>          
          $("#plan-product_id").val('<?php echo $_POST['plan']['product_id']; ?>');
        <?php endif; ?>
        <?php if(isset($data['plan']->product_id)) : ?>
          $("#plan-product_id").val('<?php echo $data['plan']->product_id; ?>');
        <?php endif; ?>
        <?php if(is_object($sub) && $sub->isSpreedlySubscription()): ?>
          $("#plan-product_id").val('spreedly_subscription');
        <?php endif; ?>
        
        $('#plan-lifetime').click(function() {
          if($('#plan-lifetime').attr('checked') == true) {
            $('#plan-active_until').val('');
          }
        });

        $('#Cart66AccountSearchField').quicksearch('table tbody tr', {
          'onAfter': function () {
             var rowCount = $('tr:visible').length; 
             $('.Cart66AccountRowCount').html((rowCount-2) + " Accounts Found");
          }
        });
        
        $("#plan-active_until").datepicker();
      })
    })(jQuery);
  </script> 
<?php endif; ?>