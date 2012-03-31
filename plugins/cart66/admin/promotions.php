<?php
$promo = new Cart66Promotion();
if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['cart66-action'] == 'save promotion') {
  $promo->setData($_POST['promo']);
  $promo->save();
  $promo->clear();
}
elseif(isset($_GET['task']) && $_GET['task'] == 'edit' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $promo->load($id);
}
elseif(isset($_GET['task']) && $_GET['task'] == 'delete' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $promo->load($id);
  $promo->deleteMe();
  $promo->clear();
}
?>
<h2>Cart66 Promotions</h2>
<div class='wrap'>
  <p style='width: 400px;'>You may create promotion codes (coupon codes) to reduce the total cost 
    of your customer's purchase by either a specific money amount (i.e. <?php echo CURRENCY_SYMBOL ?>10) or a percentage (i.e. 10%). 
    You may also set a minimum order amount that must be reached before the promotion code may be 
    used. For example, you may create a promotion for 10% off all orders over <?php echo CURRENCY_SYMBOL ?>50.</p>
  <p style='width: 400px;'>NOTE: Promotion code discounts do not affect shipping costs.</p>
  
  <form action="" method='post'>
    <input type='hidden' name='cart66-action' value='save promotion' />
    <input type='hidden' name='promo[id]' value='<?php echo $promo->id ?>' />
    
    <ul>
      <li>
        <label class="med" for='promo[code]'>Promotion code:</label>
        <input type='text' name='promo[code]' id='promo_code' style='width: 225px;' value='<?php echo $promo->code ?>' />
      </li>
      <li>
        <label class="med" for='promo[type]'>Type of promotion:</label>
        <select name='promo[type]' id='promo_type'>
          <option value='dollar' <?php if($promo->type == 'dollar') { echo 'selected'; } ?>>Money Amount</option>
          <option value='percentage' <?php if($promo->type == 'percentage') { echo 'selected'; } ?>>Percentage</option>
        </select>
      </li>
      <li>
        <label class="med" for='promo[amount]'>Amount:</label>
        <span id="dollarSign"><?php echo CURRENCY_SYMBOL ?></span>
        <input type='text' style="width: 75px;" name='promo[amount]' value='<?php echo $promo->amount ?>'> 
        <span id="percentSign">%</span>
      </li>
      <li>
        <label class="med" for='promo[min_order]'>Minimum order:</label>
        <?php echo CURRENCY_SYMBOL ?> <input type='text' style="width: 75px;" name='promo[min_order]' value='<?php echo $promo->minOrder ?>'>
        <p class='label_desc'>Leave blank to apply this promotion to all orders.</p>
      </li>
      <li>
        <label class="med">&nbsp;</label>
        <?php if($promo->id > 0): ?>
          <a href='?page=cart66-promotions' class='button-secondary linkButton' style="">Cancel</a>
        <?php endif; ?>
        <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' />
      </li>
    </ul>
    
  </form>
  
  <?php
  $promos = $promo->getModels();
  if(count($promos)):
  ?>
  <table class="widefat" style="margin-top: 20px;">
  <thead>
  	<tr>
  		<th>Promotion code</th>
  		<th>Amount</th>
  		<th>Minimum Order</th>
  		<th>Actions</th>
  	</tr>
  </thead>
  <tfoot>
      <tr>
    		<th>Promotion code</th>
    		<th>Amount</th>
    		<th>Minimum Order</th>
    		<th>Actions</th>
    	</tr>
  </tfoot>
  <tbody>
    <?php foreach($promos as $p): ?>
     <tr>
       <td><?php echo $p->code ?></td>
       <td><?php echo $p->getAmountDescription() ?></td>
       <td><?php echo $p->getMinOrderDescription() ?></td>
       <td>
         <a href='?page=cart66-promotions&task=edit&id=<?php echo $p->id ?>'>Edit</a> | 
         <a class='delete' href='?page=cart66-promotions&task=delete&id=<?php echo $p->id ?>'>Delete</a>
       </td>
     </tr>
    <?php endforeach; ?>
  </tbody>
  </table>
  <?php endif; ?>
</div>
  
<script type="text/javascript" charset="utf-8">
//<![CDATA[

  $jq = jQuery.noConflict();

  $jq('document').ready(function() {
    setPromoSign();
  });
  
  $jq('.delete').click(function() {
    return confirm('Are you sure you want to delete this item?');
  });

  $jq('#promo_type').change(function () {
    setPromoSign();
  }); 

  function setPromoSign() {
    var v = $jq('#promo_type').val();
    if(v == 'percentage') {
      $jq('#dollarSign').hide();
      $jq('#percentSign').show();
    }
    else {
      $jq('#dollarSign').show();
      $jq('#percentSign').hide();
    }
  }

//]]>
</script>
