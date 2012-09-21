<?php
/**
 * Exception error codes
 * 66101 - Product file upload failed
 * 66102 - Product save failed 
 * 66201 - Cart66ConstantContact failed to initialize
 * 66301 - Promotion save failed
 * 66302 - Reminder save failed
 * 66303 - Order fulfillment save failed
 * 66400 - Cart66 Bad Request - Used for invalid state and general errors
 * 66500 - Standard Checkout Error Messages
 * 66501 - Invalid PayPal Express Configuration
 * 66502 - Invalid PayPal Pro Configuration
 * 66503 - PayPal Express Error Message
 * 66504 - Invalid PayPal Standard Configuration
 * 66505 - Invalid Stripe Configuration
 * 66510 - Invalid Authorize.net Configuration
 * 66511 - Invalid Gateway Configuration
 * 66512 - Invalid Mijireh Configuration
 * 66520 - Invalid PayLeap Configuration
 * 66530 - Invalid eWay Configuration
 * 66540 - Invalid Merchant Warrior Configuration
 * 66600 - Invalid cURL configuration
 */ 
class Cart66Exception extends Exception {
  
  public static function exceptionMessages($errorCode, $errorMessage, $reasons=null) {
    $exception = array(
      'errorCode' => $errorCode,
      'errorMessage' => $errorMessage
    );
    
    switch ($errorCode) {
      case 66301:
      case 66302:
      case 66303:
      case 66500:
      case 66503:
        $reason = array();
        foreach($reasons as $k => $v) {
          $reason[$k] = strtolower($v);
        }
        $exception['exception'] = $reason;
        break;
      case 66400:
        $exception['exception'] = __('Cart66 is not able to perform the requested action', 'cart66');
        break;
      case 66501:
        $exception['exception'] = __('In order to use PayPal Express Checkout you must enter your PayPal API username, password and signature in the Cart66 Settings Panel or use the WordPress page editor to replace the gateway shortcode on your store/checkout page with the checkout shortcode for the gateway you intend to use.', 'cart66');
        break;
      case 66502:
        $exception['exception'] = __('In order to use PayPal Pro Checkout you must enter your PayPal API username, password and signature in the Cart66 Settings Panel or use the WordPress page editor to replace the gateway shortcode on your store/checkout page with the checkout shortcode for the gateway you intend to use.', 'cart66');
        break;
      case 66503;
        $exception['exception'] = __('In order to use Stripe Checkout you must enter your Stripe API Key in the Cart66 Settings Panel or use the WordPress page editor to replace the gateway shortcode on your store/checkout page with the checkout shortcode for the gateway you intend to use.', 'cart66');
        break;
      case 66504:
        $exception['exception'] = __('In order to use PayPal Standard Checkout you must enter your PayPal Email Address in the Cart66 Settings Panel or use the WordPress page editor to replace the gateway shortcode on your store/checkout page with the checkout shortcode for the gateway you intend to use.', 'cart66');
        break;
      case 66505;
        $exception['exception'] = __('In order to use Stripe Checkout you must enter your Stripe API Key in the Cart66 Settings Panel', 'cart66');
        break;
      case 66510:
        $exception['exception'] = __('In order to use the Authorize.net Gateway, you must enter your API Login ID and Transaction Key in the Cart66 Settings Panel or use the WordPress page editor to replace the gateway shortcode on your store/checkout page with the checkout shortcode for the gateway you intend to use.', 'cart66');
        break;
      case 66511:
        $exception['exception'] = __('In order to use the Authorize.net AIM Emulation gateway, you must enter your emulation URL in the Cart66 Settings Panel or use the WordPress page editor to replace the gateway shortcode on your store/checkout page with the checkout shortcode for the gateway you intend to use.', 'cart66');
        break;
      case 66512:
        $exception['exception'] = __('In order to use Mijireh, you must enter your Mijireh access key in the Cart66 Settings Panel or use the WordPress page editor to replace the gateway shortcode on your store/checkout page with the checkout shortcode for the gateway you intend to use.', 'cart66');
        break;
      case 66520:
        $exception['exception'] = __('In order to use the PayLeap Gateway, you must enter your API Username and Transaction Key in the Cart66 Settings Panel or use the WordPress page editor to replace the gateway shortcode on your store/checkout page with the checkout shortcode for the gateway you intend to use.', 'cart66');
        break;
      case 66530:
        $exception['exception'] = __('In order to use the eWay Gateway, you must enter your Customer ID in the Cart66 Settings Panel or use the WordPress page editor to replace the gateway shortcode on your store/checkout page with the checkout shortcode for the gateway you intend to use.', 'cart66');
        break;
      case 66540:
        $exception['exception'] = __('In order to use the Merchant Warrior Gateway, you must enter your Merchant Warrior API Passphrase, MerchantUUID and API Key in the Cart66 Settings Panel or use the WordPress page editor to replace the gateway shortcode on your store/checkout page with the checkout shortcode for the gateway you intend to use.', 'cart66');
        break;
      case 66600:
        $exception['exception'] = __('cURL is not correctly compiled or installed on your PHP server. cURL is an essential part of Cart66 and is a required function.<br />Please check with your hosting provider to make sure it is enabled and correctly compiled with OpenSSL.', 'cart66');
        break;
      default:
        $exception['exception'] = __("Unfortunately there has been an error with the shopping cart.  Please contact the site Administrator for more information.<br />Error Code: $errorCode $errorMessage","cart66");
    }
    
    return $exception;
  }

}