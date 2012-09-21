<?php
/*
Plugin Name: Cart66 Professional
Plugin URI: http://www.cart66.com
Description: Wordpress Shopping Cart
Version: 1.5.0.4
Author: Reality 66
Author URI: http://www.Reality66.com
Text Domain: cart66
Domain Path: /languages/

------------------------------------------------------------------------
Cart66 WordPress Ecommerce Plugin
Copyright 2011  Reality 66

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
if(!class_exists('Cart66')) {
  ob_start();
  
  // Discover plugin path and url even if symlinked
  if(!defined('CART66_PATH')) {
    $mj_plugin_file = __FILE__;
    if (isset($plugin)) {
      $mj_plugin_file = $plugin;
    }
    elseif (isset($mu_plugin)) {
      $mj_plugin_file = $mu_plugin;
    }
    elseif (isset($network_plugin)) {
      $mj_plugin_file = $network_plugin;
    }
    define('CART66_PATH', WP_PLUGIN_DIR . '/' . basename(dirname($mj_plugin_file)));
    define('CART66_URL', plugin_dir_url(CART66_PATH) . basename(dirname($mj_plugin_file)));
  }

  require_once(CART66_PATH. "/models/Cart66CartWidget.php");
  require_once(CART66_PATH. "/models/Cart66.php");
  require_once(CART66_PATH. "/models/Cart66Common.php");
  
  define("CART66_ORDER_NUMBER", false);
  define("CART66_PRO", true);
  define('CART66_VERSION_NUMBER', '1.5.0.4');
  define("WPCURL", Cart66Common::getWpContentUrl());
  define("WPURL", Cart66Common::getWpUrl());
  define("MIJIREH_CHECKOUT", 'https://secure.mijireh.com');

  
  if(CART66_PRO) {
    require_once(CART66_PATH. "/pro/models/Cart66ProCommon.php");
  }

  // IS_ADMIN is true when the dashboard or the administration panels are displayed
  if(!defined("IS_ADMIN")) {
    define("IS_ADMIN",  is_admin());
  }

  /* Uncomment this block of code for load time debugging
  $filename = CART66_PATH . "/log.txt"; 
  if(file_exists($filename) && is_writable($filename)) {
    file_put_contents($filename, "\n\n\n================= Loading Cart66 Main File [" . date('m/d/Y g:i:s a') . "] " . 
      $_SERVER['REMOTE_ADDR'] . " " . $_SERVER['REQUEST_URI'] . " =================\n\n", FILE_APPEND);
  }
  */
  
  $cart66 = new Cart66();
  load_plugin_textdomain( 'cart66', false, '/' . basename(dirname(__FILE__)) . '/languages/' );
  
  // Register activation hook to install Cart66 database tables and system code
  register_activation_hook(CART66_PATH . '/cart66.php', array($cart66, 'install'));
  
  if(CART66_PRO) {
    register_activation_hook(CART66_PATH . '/cart66.php', array($cart66, 'scheduledEvents'));
  }
  
  // Check for WordPress 3.1 auto-upgrades
  if(function_exists('register_update_hook')) {
    register_update_hook(CART66_PATH . '/cart66.php', array($cart66, 'install'));
  }

  add_action('init',  array($cart66, 'init'));
  add_action('widgets_init', array($cart66, 'registerCartWidget'));
  // Add settings link to plugin page
  add_filter('plugin_action_links', 'cart66SettingsLink',10,2);
  cart66_check_mail_plugins();
}

function cart66_check_mail_plugins() {
  $wp_mail = true;
  $start = WP_PLUGIN_DIR;
  $plugin_files = array(
    'wpmandrill.php'
  );
  $dir_start = scandir($start);

  foreach($dir_start as $key => $dir) {
    if(!is_dir($start . '/' . $dir) || $dir == '.' || $dir == '..') {
      continue;
    }
    $new_dir = scandir($start . '/' . $dir);
    foreach($new_dir as $key => $dir2) {
      include_once(ABSPATH . 'wp-admin/includes/plugin.php');
      if(in_array($dir2, $plugin_files) && is_plugin_active($dir . '/' . $dir2)) {
        $wp_mail = false;
      }
    }
  }
  define('CART66_WPMAIL', $wp_mail);
  if(CART66_WPMAIL) {
    include('wp_mail.php');
  }
}

function cart66SettingsLink($links, $file) {
  if($file == basename(CART66_PATH) . '/cart66.php') {
    $settings = '<a href="' . admin_url("admin.php?page=cart66-settings") . '">' . __('Settings', 'cart66') . '</a>';
    array_unshift($links, $settings);
  }
  return $links;
}

/**
 * Prevent the link rel="next" content from showing up in the wordpress header 
 * because it can potentially prefetch a page with a [clearcart] shortcode
 */
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');

if(CART66_PRO) {
  register_deactivation_hook(CART66_PATH . '/cart66.php', 'deactivation');
}
function deactivation() {
  require_once(CART66_PATH. "/pro/models/Cart66MembershipReminders.php");
  wp_clear_scheduled_hook('daily_subscription_reminder_emails');
  wp_clear_scheduled_hook('daily_followup_emails');
}
