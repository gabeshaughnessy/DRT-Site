<?php
global $wpdb;

$product = new Cart66Product();

$order = false;
if(isset($_GET['ouid'])) {
  $order = new Cart66Order();
  $order->loadByOuid($_GET['ouid']);
  if(empty($order->id)) {
    echo "<h2>This order is no longer in the system</h2>";
    exit();
  }
}

// Process Affiliate Payments
// Begin processing affiliate information
if(Cart66Session::get('ap_id')) {
  $referrer = Cart66Session::get('ap_id');
}
elseif(isset($_COOKIE['ap_id'])) {
  $referrer = $_COOKIE['ap_id'];
}

if(is_object($order) && $order->viewed == 0){
  // only process affiliate logging if this is the first time the receipt is viewed
  if (!empty($referrer)) {
    Cart66Common::awardCommission($order->id, $referrer);
  }
  // End processing affiliate information

  // Begin iDevAffiliate Tracking
  if(CART66_PRO && $url = Cart66Setting::getValue('idevaff_url')) {
    require_once(CART66_PATH . "/pro/idevaffiliate-award.php");
  }
  // End iDevAffiliate Tracking
  
  if(isset($_COOKIE['ap_id']) && $_COOKIE['ap_id']) {
    setcookie('ap_id',$referrer, time() - 3600, "/");
    unset($_COOKIE['ap_id']);
  }
  Cart66Session::drop('app_id');
}



if(isset($_GET['duid'])) {
  $duid = $_GET['duid'];
  $product = new Cart66Product();
  if($product->loadByDuid($duid)) {
    $okToDownload = true;
    if($product->download_limit > 0) {
      // Check if download limit has been exceeded
      $order_item_id = $product->loadItemIdByDuid($duid);
      if($product->countDownloadsForDuid($duid, $order_item_id) >= $product->download_limit) {
        $okToDownload = false;
      }
    }
    
    if($okToDownload) {
      $data = array(
        'duid' => $duid,
        'downloaded_on' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'],
        'order_item_id' => $product->loadItemIdByDuid($duid)
      );
      $downloadsTable = Cart66Common::getTableName('downloads');
      $wpdb->insert($downloadsTable, $data, array('%s', '%s', '%s', '%s'));
      
      $setting = new Cart66Setting();
      
      if(!empty($product->s3Bucket) && !empty($product->s3File)) {
        require_once(CART66_PATH . '/models/Cart66AmazonS3.php');
        $link = Cart66AmazonS3::prepareS3Url($product->s3Bucket, $product->s3File, '1 minute');
        wp_redirect($link);
        exit;
      }
      else {
        $dir = Cart66Setting::getValue('product_folder');
        $path = $dir . DIRECTORY_SEPARATOR . $product->download_path;
        Cart66Common::downloadFile($path);
      }
      exit();
    }
    else {
      echo '<p>' . __("You have exceeded the maximum number of downloads for this product","cart66") . '.</p>';
      $order = new Cart66Order();
      $order->loadByDuid($_GET['duid']);
      if(empty($order->id)) {
        echo "<h2>This order is no longer in the system</h2>";
        exit();
      }
      
    }
    
  }
}

if(Cart66Setting::getValue('enable_google_analytics') == 1 && Cart66Setting::getValue('use_other_analytics_plugin') == 'no'): ?>
  <script type="text/javascript">
    /* <![CDATA[ */
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', '<?php echo Cart66Setting::getValue("google_analytics_wpid") ?>']);
    _gaq.push(['_trackPageview']);
  /* ]]> */
  </script>
<?php endif; ?>

<?php  if($order !== false): ?>
<h2><?php _e( 'Order Number' , 'cart66' ); ?>: <?php echo $order->trans_id ?></h2>

<?php 
if(CART66_PRO && $order->hasAccount() == 1) {
  $logInLink = Cart66AccessManager::getLogInLink();
  $memberHomePageLink = Cart66AccessManager::getMemberHomePageLink();
  if($logInLink !== false) {
    echo '<h2>Your Account Is Ready</h2>';
    if(Cart66Common::isLoggedIn() && $memberHomePageLink !== false) {
      echo "<p><a href=\"$memberHomePageLink\">" . __("Members Home","cart66") . "</a>.</p>";
    }
    else {
      echo "<p><a href=\"$logInLink\">" . __("Log into your account","cart66") . "</a>.</p>";
    }
  }
}
?>

<?php if($order->hasAccount() == -1): ?>
  <?php if(!Cart66Common::isLoggedIn()): ?>
    <h2>Please Create Your Account</h2>
    
    <?php
      if(isset($data['errors'])) {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account creation errors: " . print_r($data, true));
        echo Cart66Common::showErrors($data['errors'], 'Your account could not be created.');
        echo Cart66Common::getJqErrorScript($data['jqErrors']);
      }
    ?>
    
    <?php 
      $account = $data['account'];
      if(!is_object($account)) {
        $account = new Cart66Account();
      }
    ?>
    <form action="" method='post' id="account_form" class="phorm2">
      <input type="hidden" name="ouid" value="<?php echo $order->ouid; ?>">
      <ul class="shortLabels">
        <li>
          <label for="account-first_name">First name:</label><input type="text" name="account[first_name]" value="<?php echo $account->firstName ?>" id="account-first_name">
        </li>
        <li>
          <label for="account-last_name">Last name:</label><input type="text" name="account[last_name]" value="<?php echo $account->lastName ?>" id="account-last_name">
        </li>
        <li>
          <label for="account-email">Email:</label><input type="text" name="account[email]" value="<?php echo $account->email ?>" id="account-email">
        </li>
        <li>
          <label for="account-username">Username:</label><input type="text" name="account[username]" value="<?php echo $account->username ?>" id="account-username">
        </li>
        <li>
          <label for="account-password">Password:</label><input type="password" name="account[password]" value="" id="account-password">
        </li>
        <li>
          <label for="account-password2">&nbsp;</label><input type="password" name="account[password2]" value="" id="account-password2">
          <p class="description">Repeat password</p>
        </li>
        <li>
          <label for="Cart66CheckoutButton" class="Cart66Hidden"><?php _e( 'Save' , 'cart66' ); ?></label>
          <input id="Cart66CheckoutButton" class="Cart66ButtonPrimary Cart66CompleteOrderButton" type="submit"  
            value="<?php _e( 'Create Account' , 'cart66' ); ?>" name="Create Account"/>
        </li>
      </ul>
    </form>
  <?php endif; ?>
<?php endif; ?>

<table border="0" cellpadding="0" cellspacing="0">
  <?php if(strlen($order->bill_last_name) > 2): ?>
  <tr>
    <td valign="top">
      <p>
        <strong><?php _e( 'Billing Information' , 'cart66' ); ?></strong><br/>
      <?php echo $order->bill_first_name ?> <?php echo $order->bill_last_name ?><br/>
      <?php echo $order->bill_address ?><br/>
      <?php if(!empty($order->bill_address2)): ?>
        <?php echo $order->bill_address2 ?><br/>
      <?php endif; ?>

      <?php if(!empty($order->bill_city)): ?>
        <?php echo $order->bill_city ?> <?php echo $order->bill_state ?>, <?php echo $order->bill_zip ?><br/>
      <?php endif; ?>
      
      <?php if(!empty($order->bill_country)): ?>
        <?php echo $order->bill_country ?><br/>
      <?php endif; ?>
      </p>
    </td>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
    <td valign="top">
      <p><strong><?php _e( 'Contact Information' , 'cart66' ); ?></strong><br/>
      <?php if(!empty($order->phone)): ?>
        <?php _e( 'Phone' , 'cart66' ); ?>: <?php echo Cart66Common::formatPhone($order->phone) ?><br/>
      <?php endif; ?>
      <?php _e( 'Email' , 'cart66' ); ?>: <?php echo $order->email ?><br/>
      <?php _e( 'Date' , 'cart66' ); ?>: <?php echo date('m/d/Y g:i a', strtotime($order->ordered_on)) ?>
      </p>
    </td>
  </tr>
  <?php endif; ?>
  <tr>
    <td>
      <?php if($order->shipping_method != 'None'): ?>
        <?php if($order->hasShippingInfo()): ?>
          
          <p><strong><?php _e( 'Shipping Information' , 'cart66' ); ?></strong><br/>
          <?php echo $order->ship_first_name ?> <?php echo $order->ship_last_name ?><br/>
          <?php echo $order->ship_address ?><br/>
      
          <?php if(!empty($order->ship_address2)): ?>
            <?php echo $order->ship_address2 ?><br/>
          <?php endif; ?>
      
          <?php if($order->ship_city != ''): ?>
            <?php echo $order->ship_city ?> <?php echo $order->ship_state ?>, <?php echo $order->ship_zip ?><br/>
          <?php endif; ?>
      
          <?php if(!empty($order->ship_country)): ?>
            <?php echo $order->ship_country ?><br/>
          <?php endif; ?>
      
        <?php endif; ?>
      
      <br/><em><?php _e( 'Delivery via' , 'cart66' ); ?>: <?php echo $order->shipping_method ?></em><br/>
      </p>
      <?php endif; ?>
    </td>
    <?php if(strlen($order->bill_last_name) > 2): ?>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    <?php else: ?>
      <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
      <td valign="top">
        <p><strong><?php _e( 'Contact Information' , 'cart66' ); ?></strong><br/>
        <?php if(!empty($order->phone)): ?>
          <?php _e( 'Phone' , 'cart66' ); ?>: <?php echo Cart66Common::formatPhone($order->phone) ?><br/>
        <?php endif; ?>
        <?php _e( 'Email' , 'cart66' ); ?>: <?php echo $order->email ?><br/>
        <?php _e( 'Date' , 'cart66' ); ?>: <?php echo date('m/d/Y g:i a', strtotime($order->ordered_on)) ?>
        </p>
      </td>
    <?php endif; ?>
  </tr>
  <?php if(CART66_PRO && Cart66Setting::getValue('enable_advanced_notifications') ==1): ?>
    <tr>
      <td colspan="3" class="receipt_tracking_numbers">
        <?php 
        $tracking = explode(',', $order->tracking_number);
        if(!empty($order->tracking_number)) {
          $i = 1;
          foreach($tracking as $key => $value) {
            $number = substr(strstr($value, '_'), 1);
            $carrier = mb_strstr($value,'_', true);
            $carrierName = Cart66AdvancedNotifications::convertCarrierNames($carrier);
            $link = Cart66AdvancedNotifications::getCarrierLink($carrier, $number); ?>
            <div id="tracking_<?php echo $i; ?>_<?php echo $carrierName; ?>" class="tracking_number">
              <span class="carrier_<?php echo $carrierName; ?>"><?php echo $carrierName ?></span><span class="tracking_text"> <?php _e("Tracking Number","cart66") ?></span><span class="tracking_divider">:</span>
              <span class="tracking_link"><a href="<?php echo $link; ?>" target="_blank" id="<?php echo $carrierName . '_' . $number; ?>"><?php echo $number ?></a></span>
            </div>
          <?php 
            $i++;
          }
        } ?>
      </td>
    </tr>
  <?php endif; ?>
</table>


<table id='viewCartTable' cellspacing="0" cellpadding="0">
  <tr>
    <th style='text-align: left;'><?php _e( 'Product' , 'cart66' ); ?></th>
    <th style='text-align: center;'><?php _e( 'Quantity' , 'cart66' ); ?></th>
    <th style='text-align: left;'><?php _e( 'Item Price' , 'cart66' ); ?></th>
    <th style='text-align: left;'><?php _e( 'Item Total' , 'cart66' ); ?></th>
  </tr>
  <?php if(Cart66Setting::getValue('enable_google_analytics') == 1 && $order->viewed == 0): ?>
    <script type="text/javascript">
      /* <![CDATA[ */
	    _gaq.push(['_addTrans',
	    
	      '<?php echo $order->trans_id; ?>',
	      '<?php echo get_bloginfo("name"); ?>',
	      '<?php echo number_format($order->total, 2, ".", ""); ?>',
	      '<?php echo number_format($order->tax, 2, ".", ""); ?>',
	      '<?php echo $order->shipping; ?>',
	      '<?php echo $order->ship_city; ?>',
	      '<?php echo $order->ship_state; ?>',
	      '<?php echo $order->ship_country; ?>'
	    ]);
	  /* ]]> */
    </script>  
  <?php endif;?>
  <?php foreach($order->getItems() as $item): ?>
    <?php 
      $product->load($item->product_id);
      $price = $item->product_price * $item->quantity;
    ?>
    <tr>
      <td>
        <b><?php echo nl2br($item->description) ?></b>
        <?php
          $product->load($item->product_id);
          if($product->isDigital()) {
            $receiptPage = get_page_by_path('store/receipt');
            $receiptPageLink = get_permalink($receiptPage);
            $receiptPageLink .= (strstr($receiptPageLink, '?')) ? '&duid=' . $item->duid : '?duid=' . $item->duid;
            echo "<br/><a href='$receiptPageLink'>Download</a>";
          }
        ?>
        
      </td>
      <td style='text-align: center;'><?php echo $item->quantity ?></td>
      <td><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($item->product_price, 2) ?></td>
      <td><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($item->product_price * $item->quantity, 2) ?></td>
    </tr>
    <?php
      if(!empty($item->form_entry_ids)) {
        $entries = explode(',', $item->form_entry_ids);
        foreach($entries as $entryId) {
          if(class_exists('RGFormsModel')) {
            if(RGFormsModel::get_lead($entryId)) {
              echo "<tr><td colspan='4'><div class='Cart66GravityFormDisplay'>" . Cart66GravityReader::displayGravityForm($entryId) . "</div></td></tr>";
            }
          }
          else {
            echo "<tr><td colspan='5' style='color: #955;'>This order requires Gravity Forms in order to view all of the order information</td></tr>";
          }
        }
      }
    ?>
    <?php if(Cart66Setting::getValue('enable_google_analytics') == 1 && $order->viewed == 0): ?>
      <script type="text/javascript">
        /* <![CDATA[ */
        _gaq.push(['_addItem',
          '<?php echo $order->trans_id; ?>',
          '<?php echo $product->item_number; ?>',
          '<?php echo nl2br($item->description) ?>',
          '', // Item Category
          '<?php echo number_format($item->product_price, 2, ".", "") ?>',
          '<?php echo $item->quantity ?>'
        ]);
        /* ]]> */
      </script>
    <?php endif; ?>
  <?php endforeach; ?>
  <?php if(Cart66Setting::getValue('enable_google_analytics') == 1 && $order->viewed == 0): ?>
    <script type="text/javascript">
    /* <![CDATA[ */
  	  _gaq.push(['_trackTrans']);
    /* ]]> */
    </script>
  <?php endif; ?>
  <tr class="noBorder">
    <td colspan='1'>&nbsp;</td>
    <td colspan="1" style='text-align: center;'>&nbsp;</td>
    <td colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Subtotal' , 'cart66' ); ?>:</td>
    <td colspan="1" style="text-align: left; font-weight: bold;"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo $order->subtotal; ?></td>
  </tr>
  
  <?php if($order->shipping_method != 'None' && $order->shipping_method != 'Download'): ?>
  <tr class="noBorder">
    <td colspan='1'>&nbsp;</td>
    <td colspan="1" style='text-align: center;'>&nbsp;</td>
    <td colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Shipping' , 'cart66' ); ?>:</td>
    <td colspan="1" style="text-align: left; font-weight: bold;"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo $order->shipping; ?></td>
  </tr>
  <?php endif; ?>
  
  <?php if($order->discount_amount > 0): ?>
    <tr class="noBorder">
      <td colspan='2'>&nbsp;</td>
      <td colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Discount' , 'cart66' ); ?>:</td>
      <td colspan="1" style="text-align: left; font-weight: bold;">-<?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($order->discount_amount, 2); ?></td>
    </tr>
  <?php endif; ?>
  
  <?php if($order->tax > 0): ?>
    <tr class="noBorder">
      <td colspan='2'>&nbsp;</td>
      <td colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Tax' , 'cart66' ); ?>:</td>
      <td colspan="1" style="text-align: left; font-weight: bold;"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($order->tax, 2); ?></td>
    </tr>
  <?php endif; ?>
  
  <tr class="noBorder">
    <td colspan='2' style='text-align: center;'>&nbsp;</td>
    <td colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Total' , 'cart66' ); ?>:</td>
    <td colspan="1" style="text-align: left; font-weight: bold;"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($order->total, 2); ?></td>
  </tr>
</table>

<p><a href='#' id="print_version"><?php _e( 'Printer Friendly Receipt' , 'cart66' ); ?></a></p>

<?php if(Cart66Setting::getValue('enable_performance_based_integration')): ?>
  <!-- Begin Performance-Based.com Affiliate Integration -->
  <img src="https://net.performance-based.com/l/298?amount=<?php echo $order->total; ?>;id=<?php echo $order->trans_id; ?>" height="1" width="1" border="0" />
  <!-- End Performance-Based.com Affiliate Integration -->
<?php endif; ?>

<!-- Begin Newsletter Signup Form -->
<?php include(CART66_PATH . '/views/newsletter-signup.php'); ?>
<!-- End Newsletter Signup Form -->

<?php
  // Erase the shopping cart from the session at the end of viewing the receipt
  Cart66Session::drop('Cart66Cart');
  Cart66Session::drop('Cart66Tax');
  Cart66Session::drop('Cart66Promotion');
  Cart66Session::drop('terms_acceptance');
?>
<?php else: ?>
  <p><?php _e( 'Receipt not available' , 'cart66' ); ?></p>
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
  <?php 
  if(Cart66Setting::getValue('enable_google_analytics') == 1): ?>
    <?php
      $url = admin_url('admin-ajax.php');
      if(Cart66Common::isHttps()) {
        $url = preg_replace('/http[s]*:/', 'https:', $url);
      }
      else {
        $url = preg_replace('/http[s]*:/', 'http:', $url);
      }
    ?>
    <?php if(Cart66Setting::getValue('use_other_analytics_plugin') == 'no'): ?>
      <script type="text/javascript">
        /* <![CDATA[ */
          (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
          })();
        /* ]]> */
      </script>
    <?php endif; ?>
  <?php endif; ?>