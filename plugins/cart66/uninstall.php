<?php
global $wpdb;

if( !defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
  exit();

define("CART66_PATH", plugin_dir_path( __FILE__ ) ); // e.g. /var/www/example.com/wordpress/wp-content/plugins/cart66
require_once(CART66_PATH . "/models/Cart66Common.php");
require_once(CART66_PATH . "/models/Cart66Setting.php");

if(Cart66Setting::getValue('uninstall_db')) {
  global $wpdb;
  $prefix = $wpdb->prefix . "cart66_";
  $sqlFile = dirname( __FILE__ ) . "/sql/uninstall.sql";
  $sql = str_replace('[prefix]', $prefix, file_get_contents($sqlFile));
  $queries = explode(";\n", $sql);
  foreach($queries as $sql) {
    if(strlen($sql) > 5) {
      $wpdb->query($sql);
    }
  }
}