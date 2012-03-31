<h2> Inventory Tracking</h2>

<?php
// Get a list of all products
$product = new Cart66Product();
$products = $product->getModels('where id>0', 'order by name');
$save = false;

if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['cart66-task'] == 'save-inventory-form') {
  $save = true;
  ?>
  <script type="text/javascript">
  var $jq = jQuery.noConflict();
  $jq(document).ready(function() {
    setTimeout('$jq("#Cart66SuccessBox").fadeOut(800);', 1000);
  });
  </script>
  <div id='Cart66SuccessBox' style='width: 300px;'><p class='Cart66Success'>Inventory updated</p></div>
  <?php
}

$setting = new Cart66Setting();
$track = Cart66Setting::getValue('track_inventory');
$wpurl = get_bloginfo('wpurl');

if(CART66_PRO) {
  require_once(WP_PLUGIN_DIR. "/cart66/pro/admin/inventory.php");
}
else {
  echo '<p class="description">This feature is only available in <a href="http://cart66.com">Cart66 Professional</a></p>';
}