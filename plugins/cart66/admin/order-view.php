<h2>Order Receipt</h2>
<?php
$order = $data['order'];
$successMessage = '';
if(isset($data['resend']) && $data['resend'] == true) {
  $successMessage = __("Email Receipt Successfully Resent","cart66");
}
?>
<?php if(!empty($successMessage)): ?>

<script type="text/javascript">
  (function($){
    $(document).ready(function(){
      $("#Cart66SuccessBox").fadeIn(1500).delay(4000).fadeOut(1500);
    })    
  })(jQuery);
</script> 
  
<div class='Cart66Modal alert-message success' id="Cart66SuccessBox" style='display:none;'>
  <p><strong><?php _e( 'Success' , 'cart66' ); ?></strong><br/>
  <?php echo $successMessage ?></p>
</div>

<?php endif; ?>
<div class='wrap'>

  <?php if(strlen($order->authorization) > 1): ?>
    <p><?php _e( 'Gateway transaction id', 'cart66');?>: <?php echo $order->authorization; ?></p>
  <?php endif; ?>
  
  <div id="order" style="width: 600px; padding: 10px 10px 10px 10px; border: 1px solid #CCCCCC; background-color: #FFF;">
    <h3 style="float: right;"><?php _e( 'Order Number' , 'cart66' ); ?>: <?php echo $order->trans_id ?></h3>
    
    <h3><?php echo $order->first_name ?> <?php echo $order->last_name ?></h3>
    <h3>Date: <?php echo date('n/j/Y g:i a', strtotime($order->ordered_on)); ?></h3>

    <table border="0" cellspacing="0" cellpadding="0" style="width: 100%;" id="Cart66OrderViewTable">
      <tr>
        <th colspan="2" style="text-align: left;"><?php _e( 'Product' , 'cart66' ); ?></th>
        <th style="text-align: center;"><?php _e( 'Quantity' , 'cart66' ); ?></th>
        <th style="text-align: right;"><?php _e( 'Price' , 'cart66' ); ?></th>
        <th style="text-align: right;"><?php _e( 'Total' , 'cart66' ); ?></th>
      </tr>
      <?php foreach($order->getItems() as $item): ?>
      <tr>
        <td colspan='2'><?php echo $item->item_number ?>
          <?php echo nl2br($item->description); ?>
        </td>
        <td style="text-align: center;"><?php echo $item->quantity ?></td>

        <td style="text-align: right;"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($item->product_price, 2); ?></td>
        <td style="text-align: right;"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($item->product_price * $item->quantity, 2) ?></td>
      </tr>
      <?php
        if(!empty($item->form_entry_ids)) {
          $entries = explode(',', $item->form_entry_ids);
          $wpurl = get_bloginfo('wpurl');
          foreach($entries as $entryId) {
            if(class_exists('RGFormsModel')) {
              if(RGFormsModel::get_lead($entryId)) {
                $formId = Cart66GravityReader::getGravityFormIdForEntry($entryId);
                echo "<tr><td colspan='5'>" . Cart66GravityReader::displayGravityForm($entryId) . "</td></tr>";
                echo "<tr><td colspan='5' align='right' style='padding-bottom: 5px !important; '><a style='font-size: 10px;' href='" . $wpurl . "/wp-admin/admin.php?page=gf_entries&view=entry&id=" . $formId. "&lid=" . $entryId . "'>View Gravity Forms Entry</a></td></tr>";
              }
            }
            else {
              echo "<tr><td colspan='5' style='color: #955;'>" . __("This order requires Gravity Forms in order to view all of the order information","cart66") . "</td></tr>";
            }
            
          }
        }
      ?>
      <?php endforeach; ?>
      
      <tr>
        <td colspan='4'>&nbsp;</td>
      </tr>
      
      <tr>
        <td colspan="4" style="text-align: right;"><strong><?php _e( 'Subtotal' , 'cart66' ); ?>:</strong></td>
        <td style="text-align: right;"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($order->subtotal, 2); ?></td>
      </tr>
      
      <?php if($order->discount_amount > 0): ?>
        <tr>
          <td colspan="4" style="text-align: right;"><strong><?php _e( 'Discount' , 'cart66' ); ?>:</strong></td>
          <td style="text-align: right;">-<?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($order->discountAmount, 2); ?></td>
        </tr>
      <?php endif; ?>

      <?php if($order->shipping_method != 'None'): ?>
      <tr>
        <td colspan="4" style="text-align: right;"><strong><?php _e( 'Shipping' , 'cart66' ); ?>:</strong></td>
        <td style="text-align: right;"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($order->shipping, 2); ?></td>
      </tr>
      <?php endif; ?>
      
      <?php if($order->tax > 0): ?>
        <tr>
          <td colspan="4" style="text-align: right;"><strong><?php _e( 'Tax' , 'cart66' ); ?>:</strong></td>
          <td style="text-align: right;"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($order->tax, 2); ?></td>
        </tr>
      <?php endif; ?>
      
      <?php if(!empty($order->coupon) && $order->coupon != 'none'): ?>
        <tr>
          <td colspan='4'>&nbsp;</td>
        </tr>
        <tr>
          <td colspan="2" style="text-align: right; background-color: #EEE;"><strong><?php _e( 'Coupon' , 'cart66' ); ?>:</strong></td>
          <td colspan="2" style="text-align: right; background-color: #EEE;"><?php echo $order->coupon ?></td>
        </tr>
        <tr>
          <td colspan='4'>&nbsp;</td>
        </tr>
      <?php endif; ?>
      
      <tr>
        <td colspan="4" style="text-align: right;"><strong><?php _e( 'Total' , 'cart66' ); ?>:</strong></td>
        <td style="text-align: right;"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($order->total, 2); ?></td>
      </tr>
      
    </table>
    
    <hr style="bgcolor: #FFFFFF; border:none; border-top: 1px dotted #CCCCCC; margin-top: 15px; " />

    <table border="0" cellspacing="0" cellpadding="5" style="width:100%;">
      <tr>
        <th style="text-align: left;"><?php _e( 'Billing Information' , 'cart66' ); ?></th>
        <th style="text-align: left;"><?php _e( 'Contact Information' , 'cart66' ); ?></th>
      </tr>
      <tr>
        <td valign="top">
          <?php echo $order->bill_first_name ?> <?php echo $order->bill_last_name ?><br/>
          <?php echo $order->bill_address ?><br/>
          <?php if(!empty($order->bill_address2)): ?>
            <?php echo $order->bill_address2 ?><br/>
          <?php endif; ?>
          <?php echo $order->bill_city ?> <?php echo $order->bill_state ?> <?php echo $order->bill_zip ?><br />
          <?php echo $order->bill_country ?>
        </td>
        <td valign="top">
          <?php _e( 'Email' , 'cart66' ); ?>: <?php echo $order->email ?><br/>
          <?php _e( 'Phone' , 'cart66' ); ?>: <?php echo Cart66Common::formatPhone($order->phone) ?><br/>
        </td>
      </tr>
      <?php if($order->shipping_method != 'None' && $order->hasShippingInfo()): ?>
        
        <tr>
          <th style="text-align: left;"><br/><?php _e( 'Shipping Information' , 'cart66' ); ?></th>
          <th style="text-align: left;">&nbsp;</th>
        </tr>
        <tr>
          <td valign="top">
            <?php echo $order->ship_first_name ?> <?php echo $order->ship_last_name ?><br/>
            <?php echo $order->ship_address ?><br/>
            <?php if(!empty($order->ship_address2)): ?>
              <?php echo $order->ship_address2 ?><br/>
            <?php endif; ?>
            <?php echo $order->ship_city ?> <?php echo $order->ship_state ?> <?php echo $order->ship_zip ?><br/>
            <?php echo $order->ship_country ?><br/>
            <br/><em><?php _e( 'Delivery via' , 'cart66' ); ?>: <?php echo $order->shipping_method ?></em></br>
            <?php
            $hasDigital = false;
            $product = new Cart66Product();
            foreach($order->getItems() as $downloadItem) {
              if($product->loadByDuid($downloadItem->duid) && $product->isDigital()) {
                $hasDigital = true;
              }
            }
            ?>
            <?php if($hasDigital): ?>
              <br /><?php _e('Downloads', 'cart66'); ?>:<br />
              <?php
              $product = new Cart66Product();
              foreach($order->getItems() as $downloadItem) {
                if($product->loadByDuid($downloadItem->duid) && $product->isDigital()) {
                  $order_item_id = $product->loadItemIdByDuid($downloadItem->duid);
                  $downloadTimes = $product->countDownloadsForDuid($downloadItem->duid, $order_item_id); ?>
                    <em><?php echo $product->name; ?>: <?php echo $downloadTimes; ?> <?php _e('out of', 'cart66'); ?> <?php echo ($product->download_limit == 0) ? __('unlimited', 'cart66') : $product->download_limit; ?></em>
                    <form id="ResetDownloads" action="" method='post' class="remove_tracking">
                      <input type='hidden' name='task' value='reset download amount'>
                      <input type='hidden' name='order_id' value='<?php echo $order->id; ?>'>
                      <input type='hidden' name='order_item_id' value='<?php echo $order_item_id; ?>'>
                      <input type='hidden' name='duid' value='<?php echo $downloadItem->duid; ?>'>
                      <input type='submit' class="remove_tracking" value="<?php _e( 'Reset Downloads' , 'cart66' ); ?>" />
                    </form><br />
                <?php }
              }
              ?>
              <br />
            <?php endif; ?>
          </td>
          <td>&nbsp;</td>
        </tr>
        
      <?php endif; ?>
      <?php if(CART66_PRO && Cart66Setting::getValue('enable_advanced_notifications') ==1): ?>
        <tr>
          <td><br/>
            <?php 
            $tracking = explode(',', $order->tracking_number);
            if(!empty($order->tracking_number)) {
              foreach($tracking as $key => $value) {
                $number = substr(strstr($value, '_'), 1);
                $carrier = mb_strstr($value,'_', true);
                $carrierName = Cart66AdvancedNotifications::convertCarrierNames($carrier);
                $link = Cart66AdvancedNotifications::getCarrierLink($carrier, $number); ?>
                <?php echo $carrierName ?> <?php _e("Tracking Number","cart66") ?>: 
                <a href="<?php echo $link; ?>" target="_blank" id="' . $carrier . '_' . $number . '"><?php echo $number ?></a> | 
                <form id="<?php echo $carrier; ?>_<?php echo $number; ?>_form" class="remove_tracking" action="" method="post">
                  <input type='hidden' name='remove' value='<?php echo $carrier; ?>_<?php echo $number; ?>' />
                  <input type="submit" value="<?php _e( 'Remove' , 'cart66' ); ?>" class="delete remove_tracking" />
                </form><br />
              <?php }
            } ?>
          </td>
        </tr>
        <?php if($order->tracking_number != null): ?>
          <tr>
            <td>
              <form id="remove_all_form" class="remove_tracking" action="" method="post">
                <input type='hidden' name='remove' value='all' />
                <input type="submit" id="remove_submit" value="<?php _e( 'Remove All Tracking Numbers' , 'cart66' ); ?>" class="delete remove_tracking" />
              </form>
            </td>
          </tr>
        <?php endif; ?>
      <?php endif; ?>
    </table>
</div>
<p style="display:inline-block;"><a href="<?php 
    
    $receiptPage = get_page_by_path('store/receipt');
    $link = get_permalink($receiptPage->ID);
    
    if(strstr($link,"?")){
      $link .= '&ouid=';
    }
    else{
      $link .= '?ouid=';
    }

    echo $link.$order->ouid ;
    
  ?>" target="_blank"><?php _e( 'View Receipt Online' , 'cart66' ); ?></a> | 
  <a href='#' id="print_version"><?php _e( 'Printer Friendly Receipt' , 'cart66' ); ?></a></p>

  <?php if(CART66_PRO): ?> | 
    <form id="EmailReceipt" action="" method='post' class="remove_tracking">
      <input type='hidden' name='task' value='resend email receipt'>
      <input type='hidden' name='order_id' value='<?php echo $order->id; ?>'>
      <input type='submit' class="remove_tracking" value="<?php _e( 'Resend Email Receipt' , 'cart66' ); ?>" />
    </form>
  <?php endif; ?>
  
  <?php if($order->account_id): ?>
    | <a href="?page=cart66-accounts&amp;accountId=<?php echo $order->account_id; ?>"><?php _e('View Account', 'cart66'); ?></a>
  <?php endif; ?>

  <?php
    if($order !== false) {
      $printView = Cart66Common::getView('views/receipt_print_version.php', array('order' => $order));
      $printView = str_replace("\n", '', $printView);
      $printView = str_replace("'", '"', $printView);
      ?>
      <script type="text/javascript">
      /* <![CDATA[ */
        (function($){
          $(document).ready(function(){
            $('#print_version').click(function() {
              myWindow = window.open('','Your_Receipt','resizable=yes,scrollbars=yes,width=550,height=700');
              myWindow.document.open("text/html","replace");
              myWindow.document.write(decodeURIComponent('<?php echo rawurlencode($printView); ?>' + ''));
              myWindow.document.close();
              return false;
            });
          })
        })(jQuery);
      /* ]]> */
      </script> 
    <?php
    }
  ?>

<div class="wrap" style="margin-top: 10px;">
  <form class="phorm" action="" method='post'>
    <input type='hidden' name='task' value='update order status' />
    <input type='hidden' name='order_id' value="<?php echo $order->id ?>">
    <textarea style="width: 620px; height: 140px;" id="order-notes" name="notes"><?php echo $order->notes ?></textarea>
    <p class="description"><?php _e( 'Notes about this order - not viewable by customer.' , 'cart66' ); ?></p><br />
    <?php if(CART66_PRO && Cart66Setting::getValue('enable_advanced_notifications') ==1): ?>
      <div style="max-width:440px;display:inline-block;">
        <div id="1_input" style="margin-bottom:4px;" class="clonedInput">
          <?php _e( 'Tracking Number' , 'cart66' ); ?>:
          <input type="text" id="1_tracking_number" name="1_tracking_number" />
          <?php _e( 'Carrier' , 'cart66' ); ?>: 
          <select id="1_carrier" name="1_carrier">
            <option value=""></option>
            <option value="FedEx"><?php _e( 'FedEx' , 'cart66' ); ?></option>
            <option value="UPS"><?php _e( 'UPS' , 'cart66' ); ?></option>
            <option value="USPS"><?php _e( 'USPS' , 'cart66' ); ?></option>
            <option value="DHL"><?php _e( 'DHL' , 'cart66' ); ?></option>
            <option value="CaPost"><?php _e( 'Canada Post' , 'cart66' ); ?></option>
            <option value="AuPost"><?php _e( 'Australia Post' , 'cart66' ); ?></option>
          </select>
        </div>
      </div>
      <div style="display:inline-block;">
        <input type="button" id="btnAdd" value="<?php _e( '+' , 'cart66' ); ?>" class="button-secondary" />
        <input type="button" id="btnDel" value="<?php _e( '-' , 'cart66' ); ?>" class="button-secondary" />
      </div>
    <?php endif; ?>
    <br />
    <label style='width: auto;'><?php _e( 'Order Status' , 'cart66' ); ?>:</label>
    <select name="status" id='status' style=''>
      <?php
        $setting = new Cart66Setting();
        $opts = explode(',', Cart66Setting::getValue('status_options'));
        foreach($opts as $o):
          $o_name = trim($o);
          $o = trim(strtolower($o)); 
      ?>
      <option value='<?php echo $o ?>' <?php if($o == $order->status) { echo 'selected="selected"'; } ?>><?php echo ucwords($o_name); ?></option>
      <?php endforeach; ?>
    </select>
    <?php if(CART66_PRO && Cart66Setting::getValue('enable_advanced_notifications') ==1): ?>
      <input type='checkbox' name='send_email_status_update' id='send_email_status_update' value="1"
        <?php echo Cart66Setting::getValue('send_email_status_update') ? 'checked="checked"' : '' ?>
      /> <?php _e( 'Send Email Status Update' , 'cart66' ); ?><br /><br />
    <?php endif; ?>
    <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='<?php _e( 'Update' , 'cart66' ); ?>' />
  </form>
</div>

<div class="wrap" style='float: left; clear: both;'>
  <p><a href='?page=cart66_admin'>&lt;&lt;&nbsp;&nbsp;<?php _e( 'Back To Orders' , 'cart66' ); ?></a></p>
</div>
<script type="text/javascript">
  (function($){
    $(document).ready(function(){
      var num = $('.clonedInput').length;
      $('#btnAdd').click(function() {
        var num = $('.clonedInput').length;
        var newNum = new Number(num + 1);
        var newElem = $('#' + num + '_input').clone().attr('id', newNum + '_input');
        newElem.children(':first').attr('id', newNum + '_tracking_number').attr('name', newNum + '_tracking_number').val('');
        newElem.children(':nth-child(2)').attr('id', newNum + '_carrier').attr('name', newNum + '_carrier');
        newElem.children(':nth-child(3)').attr('id', newNum + '_tracking').attr('name', newNum + '_tracking').val('');
        $('#' + num + '_input').after(newElem);
        $('#btnDel').removeAttr('disabled');
        console.log($('.carrier').length);
      });

      $('#btnDel').click(function() {
        var num = $('.clonedInput').length;
        $('#' + num + '_input').remove();
        $('#btnAdd').removeAttr('disabled');
        if (num-1 == 1) {
          $('#btnDel').attr('disabled','disabled');
        }
      });
      $('#btnDel').attr('disabled','disabled');
    })
  })(jQuery);
</script>
