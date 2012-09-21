<?php echo $data['beforeWidget']; ?>
  
  <?php echo $data['beforeTitle'] . '<span id="Cart66WidgetCartTitle">' . $data['title'] . '</span>' . $data['afterTitle']; ?>
  
  
    <div id="Cart66WidgetCartContents" <?php if(!$data['numItems']): ?> style="display:none;"<?php endif; ?>>
      <a id="Cart66WidgetCartLink" href='<?php echo get_permalink($data['cartPage']->ID) ?>'>
      <span id="Cart66WidgetCartCount"><?php echo $data['numItems']; ?></span>
      <span id="Cart66WidgetCartCountText"> <?php echo _n('item', 'items', $data['numItems'], 'cart66'); ?></span> 
      <span id="Cart66WidgetCartCountDash">&ndash;</span>
      <span id="Cart66WidgetCartPrice"><?php echo CART66_CURRENCY_SYMBOL . 
        number_format($data['cartWidget']->getSubTotal() - $data['cartWidget']->getDiscountAmount(), 2); ?>
      </span></a>
  <a id="Cart66WidgetViewCart" href='<?php echo get_permalink($data['cartPage']->ID) ?>'><?php _e( 'View Cart' , 'cart66' ); ?></a>
  <span id="Cart66WidgetLinkSeparator"> | </span>
  <a id="Cart66WidgetCheckout" href='<?php echo get_permalink($data['checkoutPage']->ID) ?>'><?php _e( 'Check out' , 'cart66' ); ?></a>
    </div>
    <p id="Cart66WidgetCartEmpty"<?php if($data['numItems']): ?> style="display:none;"<?php endif; ?>><?php _e( 'Your cart is empty.' , 'cart66' ); ?></p>

<?php echo $data['afterWidget']; ?>