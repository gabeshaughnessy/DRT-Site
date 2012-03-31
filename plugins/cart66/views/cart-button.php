<?php 
  // Only render the ajax code for tracking inventory if inventory tracking is enabled
  $setting = new Cart66Setting();
  $trackInventory = Cart66Setting::getValue('track_inventory');
  $id = Cart66Common::getButtonId($data['product']->id); 
?>

<?php if($data['showPrice'] == 'only'): ?>
  <p class="Cart66Price" $style>Price: <?php echo $data['price'] ?></p>
<?php else: ?>
  <form id='cartButtonForm_<?php echo $id ?>' class="Cart66CartButton" method="post" action="<?php echo Cart66Common::getPageLink('store/cart'); ?>" <?php echo $data['style']; ?>>
    <input type='hidden' name='task' id="task_<?php echo $id ?>" value='addToCart' />
    <input type='hidden' name='cart66ItemId' value='<?php echo $data['product']->id; ?>' />
    
    <?php if($data['showPrice'] == 'yes'): ?> 
      <span class="Cart66Price">Price: <?php echo $data['price'] ?></span>
    <?php endif; ?>
    
    <?php if($data['product']->isAvailable()): ?>
      <?php echo $data['productOptions'] ?>
    
      <?php if($data['product']->recurring_interval > 0 && !CART66PRO): ?>
          <div class='Cart66ProRequired'><a href='http://www.cart66.com'>Cart66 Professional</a> is required to sell subscriptions</div>
      <?php else: ?>
        <?php if($data['addToCartPath']): ?> 
          <input type='image' value='Add To Cart' src='<?php echo $data['addToCartPath'] ?>' class='purAddToCart' name='addToCart_<?php echo $id ?>' id='addToCart_<?php echo $id ?>'/>
        <?php else: ?>
          <input type='submit' value='Add To Cart' class='Cart66ButtonPrimary purAddToCart' name='addToCart_<?php echo $id ?>' id='addToCart_<?php echo $id ?>' />
        <?php endif; ?>
      <?php endif; ?>
    
    <?php else: ?>
      <span class='Cart66OutOfStock'>Out of stock</span>
    <?php endif; ?>
    
    <?php if($trackInventory): ?>
      <input type="hidden" name="action" value="check_inventory_on_add_to_cart" />
      <div id="stock_message_box_<?php echo $id ?>" class="Cart66Unavailable" style="display: none;">
        <h2>We're Sorry</h2>
        <p id="stock_message_<?php echo $id ?>"></p>
        <input type="button" name="close" value="Ok" id="close" class="Cart66ButtonSecondary modalClose" />
      </div>
    <?php endif; ?>

  </form>
<?php endif; ?>



<?php if($trackInventory): ?>

  <?php if(is_user_logged_in()): ?>
    <div class="Cart66AjaxWarning">Inventory tracking will not work because your site has javascript errors. 
      <a href="http://www.cart66.com/jquery-errors/">Possible solutions</a></div>
  <?php endif; ?>

<script type="text/javascript">
//<![CDATA[

jQuery(document).ready(function($) {
  $('.Cart66AjaxWarning').hide();

  $('#addToCart_<?php echo $id ?>').click(function() {
    $('#task_<?php echo $id ?>').val('ajax');
    var mydata = getCartButtonFormData('cartButtonForm_<?php echo $id ?>');
    <?php
      $url = admin_url('admin-ajax.php');
      if(Cart66Common::isHttps()) {
        $url = preg_replace('/http[s]*:/', 'https:', $url);
      }
      else {
        $url = preg_replace('/http[s]*:/', 'http:', $url);
      }
    ?>
    $.ajax({
        type: "POST",
        url: '<?php echo $url; ?>',
        data: mydata,
        dataType: 'json',
        success: function(result) {
          if(result[0]) {
            $('#task_<?php echo $id ?>').val('addToCart');
            $('#cartButtonForm_<?php echo $id ?>').submit();
          }
          else {
            $('#stock_message_box_<?php echo $id ?>').fadeIn(300);
            $('#stock_message_<?php echo $id ?>').html(result[1]);
          }
        },
        error: function(xhr,err){
            alert("readyState: "+xhr.readyState+"\nstatus: "+xhr.status);
            //alert("responseText: "+xhr.responseText);
            //alert('<?php echo $url ?>?' + mydata);
        }
    });
    return false;
  });
  
});

//]]>
</script>

<?php endif; ?>
