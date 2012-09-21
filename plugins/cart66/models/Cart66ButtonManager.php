<?php
class Cart66ButtonManager {

  /**
   * Return the HTML for rendering the add to cart buton for the given product id
   */
  public static function getCartButton(Cart66Product $product, $attrs) {
    $view = "<p>" . __("Could not load product information","cart66") . "</p>";
    if($product->id > 0) {

      // Set CSS style if available
      $style = isset($attrs['style']) ? 'style="' . $attrs['style'] . '"' : '';

      $price = '';
      $quantity = (isset($attrs['quantity'])) ? $attrs['quantity'] : 1;
      
      $ajax = (isset($attrs['ajax'])) ? $attrs['ajax'] : 'no';
      
      $buttonText = (isset($attrs['text'])) ? $attrs['text'] : __('Add to Cart', 'cart66');
      
      $showName = isset($attrs['show_name']) ? strtolower($attrs['show_name']) : '';
      
      $showPrice = isset($attrs['showprice']) ? strtolower($attrs['showprice']) : 'yes';
      
      $subscription = 0;
      
      if($showPrice == 'yes' || $showPrice == 'only') {
        $price = CART66_CURRENCY_SYMBOL . number_format($product->price, 2);
        
        // Check for subscription pricing
        if($product->isSubscription()) {
          if($product->isPayPalSubscription()) {
            $subscription = 1;
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Rendering button for PayPal subscription");
            $sub = new Cart66PayPalSubscription($product->id);
            $price = $sub->getPriceDescription($sub->offerTrial > 0, '(trial)');
          }
          else {
            $subscription = 2;
            if($product->price > 0) {
              $price .= ' + ' . $product->getRecurringPriceSummary();;
            }
            else {
              $price =  $product->getRecurringPriceSummary();
            }
          }
        }
        else {
          $price = $product->getPriceDescription();
        }
        
      }
      
      if($product->isSubscription()) {
        if($product->isPayPalSubscription()) {
          $subscription = 1;
        }
        else{
         $subscription = 2; 
        }
      } 
      
      $gravity_form_id = (isset($product->gravity_form_id)) ? $product->gravity_form_id : false;
      
      $data = array(
        'price' => $price,
        'is_user_price' => $product->is_user_price,
        'min_price' => $product->min_price,
        'max_price' => $product->max_price,
        'quantity' => $quantity,
        'ajax' => $ajax,
        'showPrice' => $showPrice,
        'showName' => $showName,
        'style' => $style,
        'buttonText' => $buttonText,
        'subscription' => $subscription,
        'addToCartPath' => self::getAddToCartImagePath($attrs),
        'product' => $product,
        'productOptions' => $product->getOptions(),
        'gravity_form_id' => $gravity_form_id
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
        $cartImgPath = Cart66Common::endSlashPath($cartImgPath);
        $path = $cartImgPath . 'add-to-cart.png';
      }
    }

    return $path;
  }

}