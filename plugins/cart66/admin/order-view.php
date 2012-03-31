<h2>Order Receipt</h2>
<?php
$order = $data['order'];
?>
<div class='wrap'>
  <div id="order" style="width: 600px; padding: 10px 10px 10px 10px; border: 1px solid #CCCCCC; background-color: #FFF;">
    <h3 style="float: right;">Order Number: <?php echo $order->trans_id ?></h3>

    <h3><?php echo $order->first_name ?> <?php echo $order->last_name ?></h3>
    <h3>Date: <?php echo date('n/j/Y g:i a', strtotime($order->ordered_on)); ?></h3>

    <table border="0" cellspacing="0" cellpadding="0" style="width: 100%;" id="Cart66OrderViewTable">
      <tr>
        <th colspan="2" style="text-align: left;">Product</th>
        <th style="text-align: center;">Quantity</th>
        <th style="text-align: right;">Price</th>
        <th style="text-align: right;">Total</th>
      </tr>
      <?php foreach($order->getItems() as $item): ?>
      <tr>
        <td colspan='2'><?php echo $item->item_number ?>
          <?php echo nl2br($item->description); ?>
        </td>
        <td style="text-align: center;"><?php echo $item->quantity ?></td>

        <td style="text-align: right;"><?php echo CURRENCY_SYMBOL ?><?php echo number_format($item->product_price, 2); ?></td>
        <td style="text-align: right;"><?php echo CURRENCY_SYMBOL ?><?php echo number_format($item->product_price * $item->quantity, 2) ?></td>
      </tr>
      <?php
        if(!empty($item->form_entry_ids)) {
          $entries = explode(',', $item->form_entry_ids);
          $wpurl = get_bloginfo('wpurl');
          foreach($entries as $entryId) {
            if(class_exists(RGFormsModel)) {
              if(RGFormsModel::get_lead($entryId)) {
                $formId = Cart66GravityReader::getGravityFormIdForEntry($entryId);
                echo "<tr><td colspan='5'>" . Cart66GravityReader::displayGravityForm($entryId) . "</td></tr>";
                echo "<tr><td colspan='5' align='right' style='padding-bottom: 5px !important; '><a style='font-size: 10px;' href='" . $wpurl . "/wp-admin/admin.php?page=gf_entries&view=entry&id=" . $formId. "&lid=" . $entryId . "'>View Gravity Forms Entry</a></td></tr>";
              }
            }
            else {
              echo "<tr><td colspan='5' style='color: #955;'>This order requires Gravity Forms in order to view all of the order information</td></tr>";
            }
            
          }
        }
      ?>
      <?php endforeach; ?>
      
      <tr>
        <td colspan='4'>&nbsp;</td>
      </tr>
      
      <tr>
        <td colspan="3" style="text-align: right;"><strong>Sub Total</strong></td>
        <td style="text-align: right;"><?php echo CURRENCY_SYMBOL ?><?php echo number_format($order->subtotal, 2); ?></td>
      </tr>
      
      <?php if($order->discount_amount > 0): ?>
        <tr>
          <td colspan="3" style="text-align: right;"><strong>Discount</strong></td>
          <td style="text-align: right;">-<?php echo CURRENCY_SYMBOL ?><?php echo number_format($order->discountAmount, 2); ?></td>
        </tr>
      <?php endif; ?>

      <?php if($order->shipping_method != 'None'): ?>
      <tr>
        <td colspan="3" style="text-align: right;"><strong>Shipping</strong></td>
        <td style="text-align: right;"><?php echo CURRENCY_SYMBOL ?><?php echo number_format($order->shipping, 2); ?></td>
      </tr>
      <?php endif; ?>
      
      <?php if($order->tax > 0): ?>
        <tr>
          <td colspan="3" style="text-align: right;"><strong>Tax</strong></td>
          <td style="text-align: right;"><?php echo CURRENCY_SYMBOL ?><?php echo number_format($order->tax, 2); ?></td>
        </tr>
      <?php endif; ?>
      
      <?php if(!empty($order->coupon) && $order->coupon != 'none'): ?>
        <tr>
          <td colspan='4'>&nbsp;</td>
        </tr>
        <tr>
          <td colspan="2" style="text-align: right; background-color: #EEE;"><strong>Coupon</strong></td>
          <td colspan="2" style="text-align: right; background-color: #EEE;"><?php echo $order->coupon ?></td>
        </tr>
        <tr>
          <td colspan='4'>&nbsp;</td>
        </tr>
      <?php endif; ?>
      
      <tr>
        <td colspan="3" style="text-align: right;"><strong>Total</strong></td>
        <td style="text-align: right;"><?php echo CURRENCY_SYMBOL ?><?php echo number_format($order->total, 2); ?></td>
      </tr>
      
    </table>
    
    <hr style="bgcolor: #FFFFFF; border:none; border-top: 1px dotted #CCCCCC; margin-top: 15px; " />

    <table border="0" cellspacing="0" cellpadding="5" style="width:100%;">
      <tr>
        <th style="text-align: left;">Billing Information</th>
        <th style="text-align: left;">Contact Information</th>
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
          Email: <?php echo $order->email ?><br/>
          Phone: <?php echo Cart66Common::formatPhone($order->phone) ?><br/>
        </td>
      </tr>
      <?php if($order->shipping_method != 'None' && $order->hasShippingInfo()): ?>
        
        <tr>
          <th style="text-align: left;"><br/>Shipping Information</th>
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
            <br/><em>Delivery via: <?php echo $order->shipping_method ?></em></br>
          </td>
          <td>&nbsp;</td>
        </tr>
        
      <?php endif; ?>
    </table>
</div>
<p><a href="<?php 
    
    $receiptPage = get_page_by_path('store/receipt');
    $link = get_permalink($receiptPage->ID);
    
    if(strstr($link,"?")){
      $link .= '&ouid=';
    }
    else{
      $link .= '?ouid=';
    }

    echo $link.$order->ouid ;
  
  ?>" target="_blank">View Receipt Online</a></p>
<div class="wrap" style="margin-top: 30px;">
  <form class="phorm" action="" method='post'>
    <input type='hidden' name='task' value='update order status' />
    <input type='hidden' name='order_id' value="<?php echo $order->id ?>">
    <label style='width: auto;'>Order Status:</label>
    <select name="status" id='status' style=''>
      <?php
        $setting = new Cart66Setting();
        $opts = explode(',', Cart66Setting::getValue('status_options'));
        foreach($opts as $o):
          $o = trim($o);
      ?>
      <option value='<?php echo $o ?>' <?php if($o == $order->status) { echo 'selected="selected"'; } ?>><?php echo $o ?></option>
      <?php endforeach; ?>
    </select>
    <input type='submit' name='submit' class="button-secondary" style='width: 60px;' value='Update' />
  </form>
</div>

<div class="wrap" style='float: left; clear: both;'>
  <p><a href='?page=cart66_admin'>&lt;&lt;&nbsp;&nbsp;Back To Orders</a></p>
</div>
  
