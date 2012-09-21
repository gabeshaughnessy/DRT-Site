<?php
$page = array(
  'post_title' => 'Store',
  'post_name' => 'store',
  'post_parent' => 0,
  'post_status' => 'publish',
  'post_type' => 'page',
  'comment_status' => 'closed',
  'ping_status' => 'closed'
);

// Create the top level store page
$p = get_page_by_path('store');
if(!$p) {
  $pageId = wp_insert_post($page);
  $parentId = $pageId;
}
else {
  $parentId = $p->ID;
}


// Insert the page to view the cart
$p = get_page_by_path('store/cart');
if(!$p) {
  $page['post_title'] = 'Cart';
  $page['post_name'] = 'cart';
  $page['post_content'] = "<h1>Your Shopping Cart</h1> \n[cart]";
  $page['post_parent'] = $parentId;
  wp_insert_post($page);
}

// Insert the checkout page
$p = get_page_by_path('store/checkout');
if(!$p) {
  $page['post_title'] = 'Checkout';
  $page['post_name'] = 'checkout';
  $page['post_content'] = "<h1>Checkout</h1>\n[cart mode=\"read\"]\n[checkout_mijireh]";
  $page['post_parent'] = $parentId;
  wp_insert_post($page);
}


// Insert the page to process Instant Payment Notifications
$p = get_page_by_path('store/ipn');
if(!$p) {
  $page['post_title'] = 'IPN';
  $page['post_name'] = 'IPN';
  $page['post_content'] = '[ipn]';
  $page['post_parent'] = $parentId;
  wp_insert_post($page);
}

// Insert the page to process Instant Payment Notifications
/*
$p = get_page_by_path('store/spreedly');
if(!$p) {
  $page['post_title'] = 'Spreedly';
  $page['post_name'] = 'spreedly';
  $page['post_content'] = '[spreedly_listener]';
  $page['post_parent'] = $parentId;
  wp_insert_post($page);
}
*/

// Insert the page to process Instant Payment Notifications
$p = get_page_by_path('store/receipt');
if(!$p) {
  $page['post_title'] = 'Receipt';
  $page['post_name'] = 'receipt';
  $page['post_content'] = "<h1>Your Receipt</h1>\n[receipt]";
  $page['post_parent'] = $parentId;
  wp_insert_post($page);
}

// Insert the page to process PayPal Express Checkout
$p = get_page_by_path('store/express');
if(!$p) {
  $page['post_title'] = 'Express';
  $page['post_name'] = 'express';
  $page['post_content'] = "[express]";
  $page['post_parent'] = $parentId;
  wp_insert_post($page);
}

// Insert the page for mijireh checkout
$p = get_page_by_path('store/mijireh-secure-checkout');
if(!$p) {
  $page['post_title'] = 'Mijireh Secure Checkout';
  $page['post_name'] = 'mijireh-secure-checkout';
  $page['post_content'] = "<h1>Checkout</h1>\n\n{{mj-checkout-form}}";
  $page['post_parent'] = $parentId;
  $page['post_status'] = 'private';
  wp_insert_post($page);
}

// Insert the page to include the default Unsubscribe shortcode for membership reminders
$p = get_page_by_path('store/unsubscribe');
if(!$p && CART66_PRO) {
  $page['post_title'] = 'Unsubscribe';
  $page['post_name'] = 'unsubscribe';
  $page['post_content'] = "[email_opt_out]";
  $page['post_parent'] = $parentId;
  $page['post_status'] = 'private';
  wp_insert_post($page);
}