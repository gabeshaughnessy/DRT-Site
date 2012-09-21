<?php
$product = new Cart66Product();
$adminUrl = get_bloginfo('wpurl') . '/wp-admin/admin.php';
$errorMessage = false;

if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['cart66-action'] == 'save product') {
  try {
    $product->handleFileUpload();
    $product->setData($_POST['product']);
    $product->save();
    $product->clear();
  }
  catch(Cart66Exception $e) {
    $errorCode = $e->getCode();
    if($errorCode == 66102) {
      // Product save failed
      $errors = $product->getErrors();
      $errorMessage = Cart66Common::showErrors($errors, "<p><b>" . __("The product could not be saved for the following reasons","cart66") . ":</b></p>");
    }
    elseif($errorCode == 66101) {
      // File upload failed
      $errors = $product->getErrors();
      $errorMessage = Cart66Common::showErrors($errors, "<p><b>" . __("The file upload failed","cart66") . ":</b></p>");
    }
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Product save failed ($errorCode): " . strip_tags($errorMessage));
  }
  
}
elseif(isset($_GET['task']) && $_GET['task'] == 'edit' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $product->load($id);
}
elseif(isset($_GET['task']) && $_GET['task'] == 'delete' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $product->load($id);
  $product->deleteMe();
  $product->clear();
}
elseif(isset($_GET['task']) && $_GET['task'] == 'xdownload' && isset($_GET['id']) && $_GET['id'] > 0) {
  // Load the product
  $id = Cart66Common::getVal('id');
  $product->load($id);
  
  // Delete the download file
  $setting = new Cart66Setting();
  $dir = Cart66Setting::getValue('product_folder');
  $path = $dir . DIRECTORY_SEPARATOR . $product->download_path;
  unlink($path);
  
  // Clear the name of the download file from the object and database
  $product->download_path = '';
  $product->save();
}
$data['products'] = $product->getNonSubscriptionProducts('where id>0', null, '1');
$data['spreedly'] = $product->getSpreedlyProducts(null, null, '1');
?>

<?php if($errorMessage): ?>
<div style="margin: 30px 50px 10px 5px;"><?php echo $errorMessage ?></div>
<?php endif; ?>



<h2><?php _e('Cart66 Products', 'cart66'); ?></h2>

<form action="" method="post" enctype="multipart/form-data" id="products-form">
  <input type="hidden" name="cart66-action" value="save product" />
  <input type="hidden" name="product[id]" value="<?php echo $product->id ?>" />
  <div id="widgets-left" style="margin-right: 50px;">
    <div id="available-widgets">
    
      <div class="widgets-holder-wrap">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3><?php _e( 'Product' , 'cart66' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
            <ul>
              <li>
                <label class="long" for="product-name"><?php _e( 'Product name' , 'cart66' ); ?>:</label>
                <input class="long" type="text" name='product[name]' id='product-name' value="<?php echo htmlspecialchars($product->name); ?>" />
              </li>
              <li>
                <label class="long" for='product-item_number'><?php _e( 'Item number' , 'cart66' ); ?>:</label>
                <input type='text' name='product[item_number]' id='product-item_number' value='<?php echo $product->itemNumber ?>' />
                <span class="label_desc"><?php _e( 'Unique item number required.' , 'cart66' ); ?></span>
              </li>
              
              <?php if(CART66_PRO && class_exists('RGForms')): ?>
                <li class="">
                  <label for="product-gravity_form_id" class="long"><?php _e( 'Attach Gravity Form' , 'cart66' ); ?>:</label>
                  <select name='product[gravity_form_id]' id="product-gravity_form_id">
                    <option value='0'><?php _e( 'None' , 'cart66' ); ?></option>
                    <?php
                      global $wpdb;
                      require_once(CART66_PATH . "/pro/models/Cart66GravityReader.php");
                      $gfIdsInUse = Cart66GravityReader::getIdsInUse();
                      $gfTitles = array();
                      $forms = Cart66Common::getTableName('rg_form', '');
                      $sql = "SELECT id, title from $forms where is_active=1 order by title";
                      $results = $wpdb->get_results($sql);
                      foreach($results as $r) {
                        $disabled = ( in_array($r->id, $gfIdsInUse) && $r->id != $product->gravityFormId) ? 'disabled="disabled"' : '';
                        $gfTitles[$r->id] = $r->title;
                        $selected = ($product->gravityFormId == $r->id) ? 'selected="selected"' : '';
                        echo "<option value='$r->id' $selected $disabled>$r->title</option>";
                      }
                    ?>
                  </select>
                  <span class="label_desc"><?php _e( 'A Gravity Form may only be linked to one product' , 'cart66' ); ?></span>
                </li>
                
                <li class="gravity_field" <?php if($product->gravityFormId < 1) { echo 'style="display:none;"'; } ?>>
                  <label class="long"><?php _e( 'Gravity Forms pricing' , 'cart66' ); ?>:</label>
                  <select name="product[gravity_form_pricing]" id="product-gravity_form_pricing">
                    <option value='0'><?php _e( 'No' , 'cart66' ); ?></option>
                    <option value='1' <?php echo ($product->gravity_form_pricing == 1)? 'selected="selected"' : '' ?>><?php _e( 'Yes' , 'cart66' ); ?></option>
                  </select>
                  <?php echo isset($gravityError) ? $gravityError : ''; ?>
                  <span class="label_desc"><?php _e( 'Use the Gravity Form pricing fields instead of the pricing fields here.' , 'cart66' ); ?></span>
                </li>
                
                <li id="gravity_qty_field_element" class="gravity_field" <?php if($product->gravityFormId < 1) { echo 'style="display:none;"'; } ?>>
                  <label class="long"><?php _e( 'Quantity field' , 'cart66' ); ?>:</label>
                  <select name="product[gravity_form_qty_id]" id="product-gravity_form_qty_id">
                    <option value='0'><?php _e( 'None' , 'cart66' ); ?></option>
                    <?php
                      try {
                        $gr = new Cart66GravityReader($product->gravityFormId);
                        $fields = $gr->getStandardFields();
                        foreach($fields as $id => $label) {
                          $id = str_replace("'", "", $id);
                          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Gravity Form Fields :: $id => $label");
                          $selected = ($product->gravityFormQtyId == $id) ? 'selected="selected"' : '';
                          echo "<option value='$id' $selected>$label</option>\n";
                        }
                      }
                      catch(Cart66Exception $e) {
                        $exception = Cart66Exception::exceptionMessages($e->getCode(), $e->getMessage());
                        $gravityError = Cart66Common::getView('views/error-messages.php', $exception);
                      }
                    ?>
                  </select>
                  <?php echo isset($gravityError) ? $gravityError : ''; ?>
                  <span class="label_desc"><?php _e( 'Use one of the Gravity Form fields as the quantity for your product.' , 'cart66' ); ?></span>
                </li>
              <?php endif; ?>
              
              <li class="native_price" <?php if($product->gravity_form_pricing == 1) echo 'style="display:none;"'; ?>>
                <label class="long" for="product-price" id="price_label"><?php _e( 'Price' , 'cart66' ); ?>:</label>
                <?php echo CART66_CURRENCY_SYMBOL ?><input type='text' style="width: 75px;" id="product-price" name='product[price]' value='<?php echo $product->price ?>'>
                <span class="label_desc" id="price-description"></span>
              </li>
              <li class="native_price" <?php if($product->gravity_form_pricing == 1) echo 'style="display:none;"'; ?>>
                <label class="long" for="product-price_description" id="price_description_label"><?php _e( 'Price description' , 'cart66' ); ?>:</label>
                <input type='text' style="width: 275px;" id="product-price_description" name='product[price_description]' value='<?php echo $product->priceDescription ?>'>
                <span class="label_desc" id="price_description"><?php _e( 'If you would like to customize the display of the price' , 'cart66' ); ?></span>
              </li>
              <li class="isUserPrice native_price" <?php if($product->gravity_form_pricing == 1) echo 'style="display:none;"'; ?>>
                <label class="long" for="product-is_user_price" id="is_user_price"><?php _e( 'User defined price' , 'cart66' ); ?>:</label>
                <select id="product-is_user_price" name='product[is_user_price]'>
                  <option value='1' <?php echo ($product->is_user_price == 1)? 'selected="selected"' : '' ?>><?php _e( 'Yes' , 'cart66' ); ?></option>
                  <option value='0' <?php echo ($product->is_user_price == 0)? 'selected="selected"' : '' ?>><?php _e( 'No' , 'cart66' ); ?></option>
                </select><span class="label_desc"><?php _e( 'Allow the customer to specify a price.' , 'cart66' ); ?></span>
              </li>
              
              <li class="userPriceSettings" style="<?php echo ($product->is_user_price == 1)? 'display:block;' : 'display:none;' ?>">
                <label class="long" for="product-min_price"><?php _e( 'Min price' , 'cart66' ); ?>:</label>
                <?php echo CART66_CURRENCY_SYMBOL ?><input type="text" style="width: 75px;" id="product-min_price" name='product[min_price]' value='<?php echo $product->minPrice ?>' />
                <label class="short" for="product-max_price"><?php _e( 'Max price' , 'cart66' ); ?>:</label>
                <?php echo CART66_CURRENCY_SYMBOL ?><input type="text" style="width: 75px;" id="product-max_price" name='product[max_price]' value='<?php echo $product->maxPrice ?>' />
                <span class="label_desc" id="is_user_price_description"><?php _e( 'Set to $0.00 for no limit ' , 'cart66' ); ?></span>
              </li>
              
              <li>
                <label class="long" for="product-taxable"><?php _e( 'Taxed' , 'cart66' ); ?>:</label>
                <select id="product-taxable" name='product[taxable]'>
                  <option value='1' <?php echo ($product->taxable == 1)? 'selected="selected"' : '' ?>><?php _e( 'Yes' , 'cart66' ); ?></option>
                  <option value='0' <?php echo ($product->taxable == 0)? 'selected="selected"' : '' ?>><?php _e( 'No' , 'cart66' ); ?></option>
                </select>
                <p class="label_desc">
                  <?php _e( 'Do you want to collect sales tax when this item is purchased?' , 'cart66' ); ?><br/>
                  <?php _e( 'For subscriptions, tax is only collected on the one time fee.' , 'cart66' ); ?>
                </p>
              </li>
              <li>
                <label class="long" for="product-shipped"><?php _e('Shipped', 'cart66'); ?>:</label>
                <select id="product-shipped" name='product[shipped]'>
                  <option value='1' <?php echo ($product->shipped === '1')? 'selected="selected"' : '' ?>><?php _e( 'Yes' , 'cart66' ); ?></option>
                  <option value='0' <?php echo ($product->shipped === '0')? 'selected="selected"' : '' ?>><?php _e( 'No' , 'cart66' ); ?></option>
                </select>
                <span class="label_desc"><?php _e( 'Does this product require shipping' , 'cart66' ); ?>?</span>
              </li>
              <li>
                <label class="long" for="product-weight"><?php _e( 'Weight' , 'cart66' ); ?>:</label>
                <input type="text" name="product[weight]" value="<?php echo $product->weight ?>" size="6" id="product-weight" /> lbs 
                <p class="label_desc"><?php _e( 'Shipping weight in pounds. Used for live rates calculations. Weightless items ship free.<br/>
                  If using live rates and you want an item to have free shipping you can enter 0 for the weight.' , 'cart66' ); ?></p>
              </li>
              <li class="nonSubscription">
                <label class="long" for="product-min_qty"><?php _e( 'Min quantity' , 'cart66' ); ?>:</label>
                <input type="text" style="width: 50px;" id="product-min_qty" name='product[min_quantity]' value='<?php echo $product->minQuantity ?>' />
                <label class="short" for="product-max_qty"><?php _e( 'Max quantity' , 'cart66' ); ?>:</label>
                <input type="text" style="width: 50px;" id="product-max_qty" name='product[max_quantity]' value='<?php echo $product->maxQuantity ?>' />
                <p class="label_desc"><?php _e( 'Limit the quantity that can be added to the cart. Set to 0 for unlimited.<br/>
                  If you are selling digital products you may want to limit the quantity of the product to 1.' , 'cart66' ); ?></p>
              </li>
              
              <?php if(CART66_PRO && Cart66Setting::getValue('spreedly_shortname')): ?>
              <li>
                <label for="product-spreedly_subscription_id" class="long"><?php _e( 'Attach Spreedly subscription' , 'cart66' ); ?>:</label>
                <select name="product[spreedly_subscription_id]" id="product-spreedly_subscription_id">
                  <?php foreach($data['subscriptions'] as $id => $name): ?>
                    <?php
                      $selected = ($id == $product->spreedlySubscriptionId) ? 'selected="selected"' : '';
                    ?>
                  <option value="<?php echo $id ?>" <?php echo $selected ?>><?php echo $name ?></option>
                  <?php endforeach; ?>
                </select>
              </li>
              <?php endif; ?>
              
              <?php if(CART66_PRO): ?>
                </ul>
                <ul id="membershipProductFields">
                  <li>
                    <label for="product-membership_products" class="long"><?php _e( 'Membership Product' , 'cart66' ); ?>:</label>
                    <select name="product[is_membership_product]" id="product-membership_product">
                      <option value='0' <?php echo $product->isMembershipProduct == 0 ? 'selected="selected"' : ''; ?> ><?php _e( 'No' , 'cart66' ); ?></option>
                      <option value='1' <?php echo $product->isMembershipProduct == 1 ? 'selected="selected"' : ''; ?> ><?php _e( 'Yes' , 'cart66' ); ?></option>
                    </select>
                    <p class="label_desc">
                      <?php _e( 'Should purchasing this product create a membership account?' , 'cart66' ); ?><br/>
                      <?php _e( 'If this is a spreedly subscription it already creates a membership account so leave this set to \'No\'.' , 'cart66' ); ?>
                    </p>
                  </li>
                  <li class="member_product_attrs">
                    <label class="long" for="product-feature_level"><?php _e( 'Feature level' , 'cart66' ); ?>:</label>
                    <input type="text" name="product[feature_level]" value="<?php echo $product->featureLevel; ?>" id="product-feature_level">
                    <span class="label_desc"><?php _e( 'Enter the feature level. No spaces are allowed.' , 'cart66' ); ?></span>
                  </li>
                  <li class="member_product_attrs">
                    <label for="product-billing_interval" class="long" for="membership_duration"><?php _e( 'Duration' , 'cart66' ); ?>:</label>
                    <input type="text" name="product[billing_interval]" value="<?php echo $product->billingInterval > 0 ? $product->billingInterval : ''; ?>" id="product-billing_interval" style="width: 5em;" />
                    <select name="product[billing_interval_unit]" id="product-billing_interval_unit">
                      <option value="days"   <?php echo $product->billingIntervalUnit == 'days' ? 'selected="selected"' : ''; ?> ><?php _e('Days', 'cart66'); ?></option>
                      <option value="weeks"  <?php echo $product->billingIntervalUnit == 'weeks' ? 'selected="selected"' : ''; ?> ><?php _e('Weeks', 'cart66'); ?></option>
                      <option value="months" <?php echo $product->billingIntervalUnit == 'months' ? 'selected="selected"' : ''; ?> ><?php _e('Months', 'cart66'); ?></option>
                      <option value="years"  <?php echo $product->billingIntervalUnit == 'years' ? 'selected="selected"' : ''; ?> ><?php _e('Years', 'cart66'); ?></option>
                    </select>
                  
                    <span style="padding: 0px 10px;"><?php _e( 'or' , 'cart66' ); ?></span>
                    <input type="checkbox" value="1" name="product[lifetime_membership]" id="product-lifetime_membership"  <?php echo $product->lifetimeMembership == 1 ? 'checked="checked"' : ''; ?>> <?php _e( 'Lifetime' , 'cart66' ); ?>
                  </li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
    
      <div class="widgets-holder-wrap <?php echo (strlen($product->download_path) || strlen($product->s3_file) ) ? '' : 'closed'; ?>">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3><?php _e( 'Digital Product Options' , 'cart66' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
            <h4 style="padding-left: 10px;"><?php _e( 'Limit The Number Of Times This File Can Be Downloaded' , 'cart66' ); ?></h4>
            <ul>
              <li>
                <label class="med" for='product-download_limit'><?php _e( 'Download limit' , 'cart66' ); ?>:</label>
                <input style="width: 35px;" type='text' name='product[download_limit]' id='product-download_limit' value='<?php echo $product->download_limit ?>' />
                <span class="label_desc"><?php _e( 'Max number of times customer may download product. Enter 0 for no limit.' , 'cart66' ); ?></span>
              </li>
            </ul>
            
            <?php if(Cart66Setting::getValue('amazons3_id')): ?>              
              <h4 style="padding-left: 10px;"><?php _e( 'Deliver Digital Products With Amazon S3' , 'cart66' ); ?></h4>
              <ul>
                <li>
                  <label for="product-s3_bucket" class="med bucketNameLabel"><?php _e( 'Bucket' , 'cart66' ); ?>:</label>
                  <input class="long" type='text' name='product[s3_bucket]' id='product-s3_bucket' value="<?php echo $product->s3_bucket ?>" />
                  <p class="label_desc"><?php _e( 'The Amazon S3 bucket name that is holding the digital file.' , 'cart66' ); ?></p>
                  <div>
                    <ul class="cart66S3BucketRestrictions" style="margin-left:140px;color:#ff0000;"></ul>
                  </div>
                </li>
                <li>
                  <label class="med" for='product-s3_file'><?php _e( 'File' , 'cart66' ); ?>:</label>
                  <input class="long" type='text' name='product[s3_file]' id='product-s3_file' value="<?php echo $product->s3_file ?>" />
                  <p class="label_desc"><?php _e( 'The Amazon S3 file name of your digital product.' , 'cart66' ); ?></p>
                </li>
              </ul>
              <p style="width: 600px; padding: 0px 10px;"><a href="#" id="amazons3ForceDownload"><?php _e( 'How do I force the file to download rather than being displayed in the browser?' , 'cart66' ); ?></a></p>
              <p id="amazons3ForceDownloadAnswer" style="width: 600px; padding: 10px; display: none;"><?php _e( 'If you want your digital product to download rather than display in the web browser, log into your Amazon S3 account and click on the file that you want to force to download and enter the following Meta Data in the file\'s properties:<br/>
                Key = Content-Type | Value = application/octet-stream<br/>
                Key = Content-Disposition | Value = attachment' , 'cart66' ); ?><br/><br/>
                <img src="<?php echo CART66_URL; ?>/admin/images/s3-force-download-help.png" /></p>
            <?php  endif; ?>
            
            <?php
              $setting = new Cart66Setting();
              $dir = Cart66Setting::getValue('product_folder');
              if($dir) {
                if(!file_exists($dir)) echo "<p style='color: red;'>" . __("<strong>WARNING:</strong> The digital products folder does not exist. Please update your <strong>Digital Product Settings</strong> on the <a href='?page=cart66-settings'>settings page</a>.","cart66") . "<br/>$dir</p>";
                elseif(!is_writable($dir)) echo "<p style='color: red;'>" . __("<strong>WARNING:</strong> WordPress cannot write to your digital products folder. Please make your digital products file writeable or change your digital products folder in the <strong>Digital Product Settings</strong> on the <a href='?page=cart66-settings'>settings page</a>.","cart66") . "<br/>$dir</p>";
              }
              else {
                echo "<p style='color: red;'>" . 
                __("Before you can upload your digital product, please specify a folder for your digital products in the<br/>
                <strong>Digital Product Settings</strong> on the <a href='?page=cart66-settings'>settings page</a>.","cart66") . "</p>";
              }
            ?>
            <h4 style="padding-left: 10px;"><?php _e( 'Deliver Digital Products From Your Server' , 'cart66' ); ?></h4>
            <ul>
              <li>
                <label class="med" for='product-upload'><?php _e( 'Upload product' , 'cart66' ); ?>:</label>
                <input class="long" type='file' name='product[upload]' id='product-upload' value='' />
                <p class="label_desc"><?php _e( 'If you FTP your product to your product folder, enter the name of the file you uploaded in the field below.' , 'cart66' ); ?></p>
              </li>
              <li>
                <label class="med" for='product-download_path'><em><?php _e( 'or' , 'cart66' ); ?></em> <?php _e( 'File name' , 'cart66' ); ?>:</label>
                <input class="long" type='text' name='product[download_path]' id='product-download_path' value='<?php echo $product->download_path ?>' />
                <?php
                  if(!empty($product->download_path)) {
                    $file = $dir . DIRECTORY_SEPARATOR . $product->download_path;
                    if(file_exists($file)) {
                      echo "<p class='label_desc'><a href='?page=cart66-products&task=xdownload&id=" . $product->id . "'>" . __("Delete this file from the server","cart66") . "</a></p>";
                    }
                    else {
                      echo "<p class='label_desc' style='color: red;'>" . __("<strong>WARNING:</strong> This file is not in your products folder","cart66");
                    }
                  }
                  
                ?>
              </li>
            </ul>
            
            <div class="description" style="width: 600px; margin-left: 10px;">
            <p><strong><?php _e( 'NOTE: If you are delivering large digital files, please consider using Amazon S3.' , 'cart66' ); ?></strong></p>
            <p><a href="#" id="viewLocalDeliverInfo"><?php _e( 'View local delivery information' , 'cart66' ); ?></a></p>
            <p id="localDeliveryInfo" style="display:none;"><?php _e( 'There are several settings built into PHP that affect the size of the files you can upload. These settings are set by your web host and can usually be configured for your specific needs.Please contact your web hosting company if you need help change any of the settings below.
              <br/><br/>
              If you need to upload a file larger than what is allowed via this form, you can FTP the file to the products folder' , 'cart66' ); ?> 
              <?php echo $dir ?> <?php _e( 'then enter the name of the file in the "File name" field above.' , 'cart66' ); ?>
              <br/><br/>
              <?php _e( 'Max Upload Filesize' , 'cart66' ); ?>: <?php echo ini_get('upload_max_filesize');?>B<br/><?php _e( 'Max Postsize' , 'cart66' ); ?>: <?php echo ini_get('post_max_size');?>B</p>
            </div>
          </div>
        </div>
      </div>
    
      <div class="widgets-holder-wrap <?php echo strlen($product->options_1) ? '' : 'closed'; ?>">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3><?php _e( 'Product Variations' , 'cart66' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
            <ul>
              <li>
                <label class="med" for="product-options_1"><?php _e( 'Option Group 1' , 'cart66' ); ?>:</label>
                <input style="width: 80%" type="text" name="product[options_1]" id="product-options_1" value="<?php echo htmlentities($product->options_1); ?>" />
                <p class="label_desc"><?php _e( 'Small, Medium +$2.00, Large +$4.00' , 'cart66' ); ?></p>
              </li>
              <li>
                <label class="med" for="product-options_2"><?php _e( 'Option Group 2' , 'cart66' ); ?>:</label>
                <input style="width: 80%" type="text" name='product[options_2]' id="product-options_2" value="<?php echo htmlentities($product->options_2); ?>" />
                <p class="label_desc"><?php _e( 'Red, White, Blue' , 'cart66' ); ?></p>
              </li>
              <li>
                <label class="med" for="product-custom"><?php _e( 'Custom field' , 'cart66' ); ?>:</label>
                <select name='product[custom]' id="product-custom">
                  <option value="none"><?php _e( 'No custom field' , 'cart66' ); ?></option>
                  <option value="single" <?php echo ($product->custom == 'single')? 'selected' : '' ?>><?php _e( 'Single line text field' , 'cart66' ); ?></option>
                  <option value="multi" <?php echo ($product->custom == 'multi')? 'selected' : '' ?>><?php _e( 'Multi line text field' , 'cart66' ); ?></option>
                </select>
                <input type="hidden" name="product[custom_required]" value="" />
                <input type="checkbox" name="product[custom_required]" value="1" id="product-custom_required" <?php echo $product->custom_required == 1 ? ' checked="checked"' : ''; ?>>
                <label for="product-custom_required"><?php _e('Required', 'cart66'); ?></label>
                <p class="label_desc"><?php _e( 'Include a free form text area so your buyer can provide custom information such as a name to engrave on the product.' , 'cart66' ); ?></p>
              </li>
              <li>
                <label class="med" for="product-custom_desc"><?php _e( 'Instructions' , 'cart66' ); ?>:</label>
                <input style="width: 80%" type='text' name='product[custom_desc]' id="product-custom_desc" value='<?php echo $product->custom_desc ?>' />
                <p class="label_desc"><?php _e( 'Tell your buyer what to enter into the custom text field. (Ex. Please enter the name you want to engrave)' , 'cart66' ); ?></p>
              </li>
            </ul>
          </div>
        </div>
      </div>
      
      <div style="padding: 0px;">
        <?php if($product->id > 0): ?>
        <a href='?page=cart66-products' class='button-secondary linkButton' style=""><?php _e( 'Cancel' , 'cart66' ); ?></a>
        <?php endif; ?>
        <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='<?php _e('Save', 'cart66'); ?>' />
      </div>
  
    </div>
  </div>

</form>
<div class="wrap">
  <?php if(isset($data['products']) && is_array($data['products'])): ?>
    <h3 style="margin-top: 20px;"><?php _e( 'Your Products' , 'cart66' ); ?></h3>
    <table class="promo-rows widefat Cart66HighlightTable" id="products_table">
      <tr>
        <thead>
        	<tr>
        	  <th><?php _e('ID', 'cart66'); ?></th>
      			<th><?php _e('Item Number', 'cart66'); ?></th>
      			<th><?php _e('Product Name', 'cart66'); ?></th>
      			<th><?php _e('Price', 'cart66'); ?></th>
      			<th><?php _e('Taxed', 'cart66'); ?></th>
      			<th><?php _e('Shipped', 'cart66'); ?></th>
      			<th><?php _e('Actions', 'cart66'); ?></th>
        	</tr>
        </thead>
        <tfoot>
        	<tr>
        		<th><?php _e('ID', 'cart66'); ?></th>
      			<th><?php _e('Item Number', 'cart66'); ?></th>
      			<th><?php _e('Product Name', 'cart66'); ?></th>
      			<th><?php _e('Price', 'cart66'); ?></th>
      			<th><?php _e('Taxed', 'cart66'); ?></th>
      			<th><?php _e('Shipped', 'cart66'); ?></th>
      			<th><?php _e('Actions', 'cart66'); ?></th>
        	</tr>
        </tfoot>
      </tr>
    </table>
  <?php endif; ?>
  <?php if(isset($data['spreedly']) && is_array($data['spreedly']) && count($data['spreedly']) > 0): ?>
    <h3 style="margin-top: 50px;"><?php _e( 'Your Spreedly Subscription Products' , 'cart66' ); ?></h3>
    <table class="widefat Cart66HighlightTable" id="spreedly_table">
      <tr>
        <thead>
        	<tr>
        	  <th><?php _e('ID', 'cart66'); ?></th>
      			<th><?php _e('Item Number', 'cart66'); ?></th>
      			<th><?php _e('Product Name', 'cart66'); ?></th>
      			<th><?php _e('Price', 'cart66'); ?></th>
      			<th><?php _e('Taxed', 'cart66'); ?></th>
      			<th><?php _e('Shipped', 'cart66'); ?></th>
      			<th><?php _e('Actions', 'cart66'); ?></th>
        	</tr>
        </thead>
        <tfoot>
        	<tr>
        		<th><?php _e('ID', 'cart66'); ?></th>
      			<th><?php _e('Item Number', 'cart66'); ?></th>
      			<th><?php _e('Product Name', 'cart66'); ?></th>
      			<th><?php _e('Price', 'cart66'); ?></th>
      			<th><?php _e('Taxed', 'cart66'); ?></th>
      			<th><?php _e('Shipped', 'cart66'); ?></th>
      			<th><?php _e('Actions', 'cart66'); ?></th>
        	</tr>
        </tfoot>
      </tr>
    </table>
  <?php endif; ?>
</div>
<script type="text/javascript">
  (function($){
    $(document).ready(function() {
      
      // Hide Gravity Forms quantity field when using Gravity Forms pricing
      /*
      if($('#product-gravity_form_pricing').val() == 1) {
        $('#product-gravity_form_qty_id').val(0);
        $('#gravity_qty_field_element').hide('slow');
      }
      */
      
      $('#products_table').dataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bPagination": true,
        "iDisplayLength": 30,
        "aLengthMenu": [[30, 60, 150, -1], [30, 60, 150, "All"]],
        "sPaginationType": "bootstrap",
        "bAutoWidth": false,
				"sAjaxSource": ajaxurl + "?action=products_table",
        "aoColumns": [
          null, 
          { "bsortable": true, "fnRender": function(oObj) { return '<a href="?page=cart66-products&task=edit&id=' + oObj.aData[0] + '">' + oObj.aData[1] + '</a>'}},
          null, null, 
          { "bSearchable": false }, 
          { "bSearchable": false },
          { "bSearchable": false, "bSortable": false, "fnRender": function(oObj) { return '<a href="?page=cart66-products&task=edit&id=' + oObj.aData[0] + '"><?php _e( "Edit" , "cart66" ); ?></a> | <a class="delete" href="?page=cart66-products&task=delete&id=' + oObj.aData[0] + '"><?php _e( "Delete" , "cart66" ); ?></a>' }
        }],
        "oLanguage": { 
          "sZeroRecords": "<?php _e('No matching Products found', 'cart66'); ?>", 
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
      });
      $('#spreedly_table').dataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bPagination": true,
        "iDisplayLength": 30,
        "aLengthMenu": [[30, 60, 150, -1], [30, 60, 150, "All"]],
        "sPaginationType": "bootstrap",
        "bAutoWidth": false,
				"sAjaxSource": ajaxurl + "?action=spreedly_table",
        "aoColumns": [
          null, 
          { "bsortable": true, "fnRender": function(oObj) { return '<a href="?page=cart66-products&task=edit&id=' + oObj.aData[0] + '">' + oObj.aData[1] + '</a>'}},
          null, null, 
          { "bSearchable": false }, 
          { "bSearchable": false },
          { "bSearchable": false, "bSortable": false, "fnRender": function(oObj) { return '<a href="?page=cart66-products&task=edit&id=' + oObj.aData[0] + '"><?php _e( "Edit" , "cart66" ); ?></a> | <a class="delete" href="?page=cart66-products&task=delete&id=' + oObj.aData[0] + '"><?php _e( "Delete" , "cart66" ); ?></a>' }
        }],
        "oLanguage": { 
          "sZeroRecords": "<?php _e('No matching Spreedly Subscriptions found', 'cart66'); ?>", 
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
      });
      
      $('#product-item_number').keyup(function() {
        $('span.keyup-error').remove();
        var inputVal = $(this).val();
        var characterReg = /"/;
        if(characterReg.test(inputVal)) {
          $(this).after('<span class="keyup-error"><?php _e("No quotes allowed", "cart66"); ?></span>');
        }
      });
      
      $('#products-form').submit(function() {
        if($('span.error').length > 0){
          alert('There are errors on this page!');
          return false;
        }
      });
      
      toggleSubscriptionText();
      toggleMembershipProductAttrs();

      $('.sidebar-name').click(function() {
        $(this.parentNode).toggleClass("closed");
      });

      $("#product-feature_level").keydown(function(e) {
        if (e.keyCode == 32) {
          $(this).val($(this).val() + ""); // append '-' to input
          return false; // return false to prevent space from being added
        }
      }).change(function(e) {
          $(this).val(function (i, v) { return v.replace(/ /g, ""); }); 
      });

      $("#product-spreedly_subscription_id").change(function(){
        if($(this).val() != 0){
          $(".userPriceSettings, .isUserPrice").hide('slow');
          $("#product-is_user_price").val("0");
          
          $("#membershipProductFields").hide('slow');
          $("#product-membership_product").val("0");
          $("#product-feature_level").val('');
          $("#product-billing_interval").val('');
          $("#product-lifetime_membership").attr('checked', false);
          toggleMembershipProductAttrs();
        }
        else{
          if($('#product-gravity_form_pricing').val() != 1) {
            $(".isUserPrice").show('slow');

            if($(".isUserPrice").val() == 1){
              $(".userPriceSettings").show('slow');
            }
          }
          
          $("#membershipProductFields").show('slow');
        }
      })

      $("#product-is_user_price").change(function(){
        if($(this).val() == 1){
          $(".userPriceSettings").show();
        }
        if($(this).val() == 0){
          $(".userPriceSettings").hide();
        }
      })

      $('.delete').live('click', function() {
        return confirm('Are you sure you want to delete this item?');
      });

      // Ajax to populate gravity_form_qty_id when gravity_form_id changes
      $('#product-gravity_form_id').change(function() {
        var gravityFormId = $('#product-gravity_form_id').val();
        console.debug('changing gravity form selection to: ' + gravityFormId);
        $.get(ajaxurl, { 'action': 'update_gravity_product_quantity_field', 'formId': gravityFormId}, function(myOptions) {
          $('#product-gravity_form_qty_id >option').remove();
          $('#product-gravity_form_qty_id').append( new Option('None', 0) );
          $.each(myOptions, function(val, text) {
              $('#product-gravity_form_qty_id').append( new Option(text,val) );
          });
        });
        
        if(gravityFormId > 0) {
          $('.gravity_field').show();
        }
        else {
          $('.gravity_field').hide();
          $('.native_price').show('slow');
          $('#product-gravity_form_qty_id').val(0);
          $('#product-gravity_form_pricing').val(0);
        }
      });
      
      // Toggle native pricing fields based on whether or not Gravity Forms pricing is activated
      $('#product-gravity_form_pricing').change(function() {
        if($(this).val() == 1) {
          $('.native_price').hide('slow');
          // $('#gravity_qty_field_element').hide('slow');
        }
        else {
          // $('#gravity_qty_field_element').show('slow');
          $('#product-gravity_form_qty_id').val(0);
          $('.native_price').show('slow');
        }
      });

      $('#spreedly_subscription_id').change(function() {
        toggleSubscriptionText();
      });

      $('#paypal_subscription_id').change(function() {
        toggleSubscriptionText();
      });

      $('#Cart66AccountSearchField').quicksearch('table tbody tr');

      $('#product-membership_product').change(function() {
        toggleMembershipProductAttrs();
      });

      $('#product-lifetime_membership').click(function() {
        toggleLifeTime();
      });

      $('#viewLocalDeliverInfo').click(function() {
        $('#localDeliveryInfo').toggle();
        return false;
      });

      $('#amazons3ForceDownload').click(function() {
        $('#amazons3ForceDownloadAnswer').toggle();
        return false;
      });

      <?php if(Cart66Setting::getValue('amazons3_id')): ?>
      validateS3BucketName();  
      <?php endif; ?>
      $("#product-s3_bucket, #product-s3_file").blur(function(){
         validateS3BucketName();        
      })
    })
    function toggleLifeTime() {
      if($('#product-lifetime_membership').attr('checked')) {
        $('#product-billing_interval').val('');
        $('#product-billing_interval').attr('disabled', true);
        $('#product-billing_interval_unit').val('days');
        $('#product-billing_interval_unit').attr('disabled', true);
      }
      else {
        $('#product-billing_interval').attr('disabled', false);
        $('#product-billing_interval_unit').attr('disabled', false);
      }
    }

    function toggleMembershipProductAttrs() {
      if($('#product-membership_product').val() == '1') {
        $('.member_product_attrs').show();
        $(".nonSubscription").hide();
      }
      else {
        $('.member_product_attrs').hide();
        $(".nonSubscription").show();
      }
      
    }

    function toggleSubscriptionText() {
      if(isSubscriptionProduct()) {
        $('#price_label').text('One Time Fee:');
        $('#price_description').text('One time fee charged when subscription is purchased. This could be a setup fee.');
        $('#subscriptionVariationDesc').show();
        $('.nonSubscription').hide();
        $('#membershipProductFields').hide();
        $('#product-membership_product').val(0);
        $('#product-feature_level').val('');
        $('#product-billing_interval').val('');
        $('#product-billing_interval_unit').val('days');
        $('#product-lifetime_membership').removeAttr('checked');
      }
      else {
        $('#price_label').text('Price:');
        $('#price_description').text('');
        $('#subscriptionVariationDesc').hide();
        $('.nonSubscription').show();
        $('#membershipProductFields').show();
      }
    }

    function isSubscriptionProduct() {
      var spreedlySubId = $('#spreedly_subscription_id').val();
      var paypalSubId = $('#paypal_subscription_id').val();

      if(spreedlySubId > 0 || paypalSubId > 0) {
        return true;
      }
      return false;
    }

    function bucketError(message){
      $(".bucketNameLabel").css('color','#ff0000');
      // check for existing message
      if($(".cart66S3BucketRestrictions").html().indexOf(message) == -1){
        $(".cart66S3BucketRestrictions").append("<li>" + message + "</li>");
      }
    }

    function validateS3BucketName(){
      var rawBucket = $("#product-s3_bucket").val();

      // clear errors
      $(".cart66S3BucketRestrictions li").remove();
      $(".bucketNameLabel").css('color','#000');

      // no underscores
      if(rawBucket.indexOf('_') != -1){
        bucketError("Bucket names should NOT contain underscores (_).");
      }

      // not empty if there's a file name
      // proper length
      if(rawBucket == "" && $("#product-s3_file").val() != ""){
        bucketError("If you have a file name, you'll need a bucket.");
      } 
      else if(rawBucket.length > 0 && (rawBucket.length < 3 || rawBucket.length > 63) ){
        bucketError("Bucket names should be between 3 and 63 characters long.")
      }

      // dont end with a dash
      if(rawBucket.substring(rawBucket.length-1,rawBucket.length) == "-"){
        bucketError("Bucket names should NOT end with a dash.");
      }

      // dont have dashes next to periods
      if(rawBucket.indexOf('.-') != -1 || rawBucket.indexOf('-.') != -1){
        bucketError("Dashes cannot appear next to periods. For example, “my-.bucket.com” and “my.-bucket” are invalid names.");
      }

      // no uppercase characters allowed
      // only letters, numbers, periods or dashes
      i=0;
      while(i <= rawBucket.length-1){
        if (rawBucket.charCodeAt(i) > 64 && rawBucket.charCodeAt(i) < 90) {
        	bucketError("Bucket names should NOT contain UPPERCASE letters.");
        }
        if (rawBucket != "" && !rawBucket.charAt(i).match(/[a-z0-9\.\-]/g) ){
          bucketError("Bucket names may only contain lower case letters, numbers, periods or hyphens.");
        }
        i++;
      }

      // must start with letter or number
      if(rawBucket != "" && !rawBucket.substring(0,1).match(/[a-z0-9]/g) ){
        bucketError("Bucket names must begin with a number or a lower-case letter.");
      }

      // cannot be an ip address
      if(rawBucket != "" && rawBucket.match(/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/g) ){
        bucketError("Bucket names cannot be an IP address");
      }

    }
  })(jQuery);
</script>
