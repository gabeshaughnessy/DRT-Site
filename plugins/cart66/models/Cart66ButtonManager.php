<?php
class Cart66ButtonManager {

  /**
   * Return the HTML for rendering the add to cart buton for the given product id
   */
  public static function getCartButton(Cart66Product $product, $attrs) {
    $view = "<p>Could not load product information</p>";
    if($product->id > 0) {

      // Set CSS style if available
      $style = isset($attrs['style']) ? 'style="' . $attrs['style'] . '"' : '';

      $price = '';
      $showPrice = isset($attrs['showprice']) ? strtolower($attrs['showprice']) : 'yes';
      if($showPrice == 'yes' || $showPrice == 'only') {
        $price = CURRENCY_SYMBOL . number_format($product->price, 2);
        
        // Check for subscription pricing
        if($product->isSubscription()) {
          if($product->isPayPalSubscription()) {
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Rendering button for PayPal subscription");
            $sub = new Cart66PayPalSubscription($product->id);
            $price = $sub->getPriceDescription($sub->offerTrial > 0, '(trial)');
          }
          else {
            if($product->price > 0) {
              $price .= ' + ' . $product->getRecurringPriceSummary();;
            }
            else {
              $price =  $product->getRecurringPriceSummary();
            }
          }
          
        }
        
      }

      $data = array(
        'price' => $price,
        'showPrice' => $showPrice,
        'style' => $style,
        'addToCartPath' => self::getAddToCartImagePath($attrs),
        'product' => $product,
        'productOptions' => $product->getOptions()
      );
      $view = Cart66Common::getView('views/cart-button.php', $data);
    }
    return $view;
  }

  /**
   * Return the image path for the add to cart button or false if no path is available
   */
  public function getAddToCartImagePath($attrs) {
    $path = false;

    if(isset($attrs['img'])) {
      // Look for custom image for this instance of the button
      $path = $attrs['img'];
    }
    else {
      // Look for common images
      $cartImgPath = Cart66Setting::getValue('cart_images_url');
      if($cartImgPath) {
        $cartImgPath = Cart66Common::scrubPath($cartImgPath);
        $path = $cartImgPath . 'add-to-cart.png';
      }
    }

    return $path;
  }

}