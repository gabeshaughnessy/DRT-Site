<?php echo $data['beforeWidget']; ?>
  
  <?php echo $data['beforeTitle'] . '<span id="Cart66WidgetCartTitle">' . $data['title'] . '</span>' . $data['afterTitle']; ?>
  
  <?php if($data['numItems']): ?>
    <div id="Cart66WidgetCartContents">
      <a id="Cart66WidgetCartLink" href='<?php echo get_permalink($data['cartPage']->ID) ?>'>
      <span id="Cart66WidgetCartCount"><?php echo $data['numItems']; ?></span>
      <span id="Cart66WidgetCartCountText"><?php echo $data['numItems'] > 1 ? ' items' : ' item' ?></span> 
      <span id="Cart66WidgetCartCountDash">&ndash;</span>
      <span id="Cart66WidgetCartPrice"><?php echo CURRENCY_SYMBOL . 
        number_format($data['cartWidget']->getSubTotal() - $data['cartWidget']->getDiscountAmount(), 2); ?>
      </span></a>
  <a id="Cart66WidgetViewCart" href='<?php echo get_permalink($data['cartPage']->ID) ?>'>View Cart</a>
  <span id="Cart66WidgetLinkSeparator"> | </span>
  <a id="Cart66WidgetCheckout" href='<?php echo get_permalink($data['checkoutPage']->ID) ?>'>Check out</a>
    </div>
  <?php else: ?>
    <p id="Cart66WidgetCartEmpty">Your cart is empty.</p>
  <?php endif; ?>

<?php echo $data['afterWidget']; ?>