<h2><?php _e('Cart66 Inventory Tracking', 'cart66'); ?></h2>

<?php
// Get a list of all products
$product = new Cart66Product();
$products = $product->getModels('where id>0', 'order by name', '1');
$save = false;

if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['cart66-task'] == 'save-inventory-form') {
  $save = true;
  
  $product->updateInventoryFromPost($_REQUEST);
  
  ?>
  <script type="text/javascript">
    (function($){
      $(document).ready(function(){
        $("#Cart66SuccessBox").show().delay(1000).fadeOut('slow'); 
      })
    })(jQuery);
  </script> 
  <div id='Cart66SuccessBox' style='width: 300px;'><p class='alert-message success'><?php _e( 'Inventory updated' , 'cart66' ); ?></p></div>
  <?php
}

$setting = new Cart66Setting();
$track = Cart66Setting::getValue('track_inventory');
$wpurl = get_bloginfo('wpurl');

if(CART66_PRO) {
  require_once(CART66_PATH . "/pro/admin/inventory.php");
}
else { ?>
 <p class="description"><?php _e('Account functionality is only available in', 'cart66'); ?> <a href='http://cart66.com'><?php _e('Cart66 Professional', 'cart66'); ?></a></p>
<?php }